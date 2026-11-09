<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\ExcelExport;

class AnswerSheetController
{
    // 1. Answer Sheet Receipts (Penerimaan Lembar Jawaban)
    public function receipts(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $statusFilter = (string)$request->input('status', '');
        $dateFilter = (string)$request->input('date', '');

        $sql = "
            SELECT asr.*, s.name as subject_name, s.code as subject_code, er.room_number, er.name as room_name
            FROM answer_sheet_receipts asr
            JOIN subjects s ON asr.subject_id = s.id
            JOIN exam_rooms er ON asr.room_id = er.id
            WHERE asr.exam_event_id = ?
        ";
        $params = [$event['id']];

        if (!empty($statusFilter)) {
            $sql .= " AND asr.status = ?";
            $params[] = $statusFilter;
        }

        if (!empty($dateFilter)) {
            $sql .= " AND asr.receipt_date = ?";
            $params[] = $dateFilter;
        }

        $sql .= " ORDER BY asr.receipt_date DESC, asr.session DESC, er.room_number ASC, asr.id DESC";
        $receipts = Database::fetchAll($sql, $params);

        $schedules = Database::fetchAll("
            SELECT ws.*, s.name as subject_name, s.code as subject_code
            FROM written_exam_schedules ws
            JOIN subjects s ON ws.subject_id = s.id
            WHERE ws.exam_event_id = ?
            ORDER BY ws.exam_date ASC, ws.session ASC
        ", [$event['id']]);

        $rooms = Database::fetchAll("SELECT * FROM exam_rooms WHERE exam_event_id = ? ORDER BY room_number ASC", [$event['id']]);
        $classes = Database::fetchAll("SELECT * FROM classes ORDER BY grade ASC, name ASC");

        View::render('answer_sheets/receipts', [
            'title' => 'Penerimaan Lembar Jawaban Ujian',
            'event' => $event,
            'receipts' => $receipts,
            'schedules' => $schedules,
            'rooms' => $rooms,
            'classes' => $classes,
            'statusFilter' => $statusFilter,
            'dateFilter' => $dateFilter
        ]);
    }

    public function storeReceipt(Request $request): void
    {
        $event = active_event();
        $scheduleId = (int)$request->input('schedule_id');
        $roomId = (int)$request->input('room_id');
        $className = trim((string)$request->input('class_name'));
        $receivedCount = (int)$request->input('received_count', 0);
        $unusedCount = (int)$request->input('unused_count', 0);
        $damagedCount = (int)$request->input('damaged_count', 0);
        $handoverOfficer = trim((string)$request->input('handover_officer', ''));
        $receivingOfficer = trim((string)$request->input('receiving_officer', Auth::user()['full_name'] ?? 'Petugas Panitia'));
        $notes = trim((string)$request->input('notes', ''));

        $schedule = Database::fetch("SELECT * FROM written_exam_schedules WHERE id = ?", [$scheduleId]);
        $room = Database::fetch("SELECT * FROM exam_rooms WHERE id = ?", [$roomId]);

        if (!$schedule || !$room || empty($className)) {
            flash('error', 'Data jadwal, ruang, atau kelas tidak lengkap.');
            Response::back();
        }

        // Calculate actual present students of this class in this room for this schedule
        $presentCount = (int)Database::fetchColumn("
            SELECT COUNT(*) FROM written_exam_attendances wea
            JOIN exam_participants ep ON wea.participant_id = ep.id
            WHERE wea.schedule_id = ? AND wea.room_id = ?
            AND ep.class_name_snapshot = ?
            AND wea.status IN ('hadir', 'terlambat')
        ", [$scheduleId, $roomId, $className]);

        // If attendance was not filled yet, calculate total students in room from that class as baseline
        if ($presentCount === 0) {
            $presentCount = (int)Database::fetchColumn("
                SELECT COUNT(*) FROM exam_room_assignments era
                JOIN exam_participants ep ON era.participant_id = ep.id
                WHERE era.exam_room_id = ? AND ep.class_name_snapshot = ?
            ", [$roomId, $className]);
        }

        $discrepancy = $presentCount - $receivedCount;

        $status = 'diterima_lengkap';
        if ($discrepancy > 0) {
            $status = 'ada_selisih';
        } elseif ($receivedCount === 0) {
            $status = 'belum_diterima';
        }

        $id = Database::insert('answer_sheet_receipts', [
            'exam_event_id' => $event['id'],
            'receipt_date' => $schedule['exam_date'],
            'session' => $schedule['session'],
            'room_id' => $roomId,
            'subject_id' => $schedule['subject_id'],
            'class_name' => $className,
            'present_count' => $presentCount,
            'received_count' => $receivedCount,
            'unused_count' => $unusedCount,
            'damaged_count' => $damagedCount,
            'discrepancy' => $discrepancy,
            'handover_officer' => $handoverOfficer,
            'receiving_officer' => $receivingOfficer,
            'status' => $status,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($discrepancy != 0) {
            flash('warning', "Peringatan Selisih Lembar Jawaban: Peserta hadir {$presentCount} orang, tetapi lembar diterima {$receivedCount} lembar (Selisih: {$discrepancy} lembar belum diterima). Status ditandai 'Ada Selisih'.");
        } else {
            flash('success', "Penerimaan lembar jawaban kelas {$className} ({$receivedCount} lembar) berhasil diverifikasi dan dicatat.");
        }

        Response::redirect(url('/answer-sheets/receipts'));
    }

    // 2. Packing Workflow & 3 Modes
    public function packing(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $batches = Database::fetchAll("
            SELECT b.*, s.name as subject_name, s.code as subject_code,
                   (SELECT COUNT(*) FROM answer_sheet_packing_items WHERE batch_id = b.id) as item_count
            FROM answer_sheet_packing_batches b
            LEFT JOIN subjects s ON b.subject_id = s.id
            WHERE b.exam_event_id = ?
            ORDER BY b.id DESC
        ", [$event['id']]);

        // Unpacked receipts available for packing
        $unpackedReceipts = Database::fetchAll("
            SELECT asr.*, s.name as subject_name, s.code as subject_code, er.room_number
            FROM answer_sheet_receipts asr
            JOIN subjects s ON asr.subject_id = s.id
            JOIN exam_rooms er ON asr.room_id = er.id
            WHERE asr.exam_event_id = ? AND asr.is_packed = 0
            ORDER BY asr.class_name ASC, s.code ASC
        ", [$event['id']]);

        $classes = Database::fetchAll("SELECT * FROM classes ORDER BY grade ASC, name ASC");
        $subjects = Database::fetchAll("SELECT * FROM subjects ORDER BY code ASC");

        View::render('answer_sheets/packing', [
            'title' => 'Pengemasan (Packing) Lembar Jawaban - 3 Mode',
            'event' => $event,
            'batches' => $batches,
            'unpackedReceipts' => $unpackedReceipts,
            'classes' => $classes,
            'subjects' => $subjects
        ]);
    }

    /**
     * Create packing batch supporting the 3 Modes:
     * Mode 1: per_kelas (One envelope for origin class containing breakdown of subjects)
     * Mode 2: per_mapel (One envelope for subject containing breakdown of origin classes)
     * Mode 3: per_kelas_mapel (Dedicated envelope for specific class + subject)
     */
    public function createPackingBatch(Request $request): void
    {
        $event = active_event();
        $mode = (string)$request->input('packing_mode', 'per_kelas');
        $className = trim((string)$request->input('class_name', ''));
        $subjectId = $request->input('subject_id') ? (int)$request->input('subject_id') : null;
        $envelopeCount = (int)$request->input('envelope_count', 1);
        $packingOfficer = trim((string)$request->input('packing_officer', Auth::user()['full_name'] ?? 'Petugas Packing'));
        $receiptIds = $request->input('receipt_ids') ?? [];

        if (empty($receiptIds)) {
            // Auto-gather unpacked receipts matching mode criteria
            $sql = "SELECT id, received_count, discrepancy, status FROM answer_sheet_receipts WHERE exam_event_id = ? AND is_packed = 0";
            $p = [$event['id']];
            if ($mode === 'per_kelas' && !empty($className)) {
                $sql .= " AND class_name = ?";
                $p[] = $className;
            } elseif ($mode === 'per_mapel' && !empty($subjectId)) {
                $sql .= " AND subject_id = ?";
                $p[] = $subjectId;
            } elseif ($mode === 'per_kelas_mapel' && !empty($className) && !empty($subjectId)) {
                $sql .= " AND class_name = ? AND subject_id = ?";
                $p[] = $className;
                $p[] = $subjectId;
            }
            $targetReceipts = Database::fetchAll($sql, $p);
        } else {
            $placeholders = implode(',', array_fill(0, count($receiptIds), '?'));
            $targetReceipts = Database::fetchAll(
                "SELECT id, received_count, discrepancy, status FROM answer_sheet_receipts WHERE id IN ({$placeholders})",
                $receiptIds
            );
        }

        if (empty($targetReceipts)) {
            flash('error', 'Tidak ada data tanda terima lembar jawaban yang memenuhi kriteria pengemasan.');
            Response::back();
        }

        // Skenario F Check: Check if any receipt has discrepancy
        $hasDiscrepancy = false;
        $totalSheets = 0;
        foreach ($targetReceipts as $rec) {
            $totalSheets += (int)$rec['received_count'];
            if ((int)$rec['discrepancy'] !== 0 || $rec['status'] === 'ada_selisih') {
                $hasDiscrepancy = true;
            }
        }

        $batchCode = 'PACK-' . strtoupper($mode) . '-' . time() . '-' . rand(10, 99);
        $batchStatus = $hasDiscrepancy ? 'Ada Selisih' : 'Terverifikasi';

        Database::beginTransaction();
        try {
            $batchId = Database::insert('answer_sheet_packing_batches', [
                'exam_event_id' => $event['id'],
                'batch_code' => $batchCode,
                'packing_date' => date('Y-m-d'),
                'packing_mode' => $mode,
                'class_name' => $className ?: null,
                'subject_id' => $subjectId ?: null,
                'total_sheets' => $totalSheets,
                'envelope_count' => $envelopeCount,
                'packing_officer' => $packingOfficer,
                'status' => $batchStatus,
                'notes' => $hasDiscrepancy ? 'Perhatian: Berkas memiliki selisih fisik lembar jawaban.' : 'Lengkap dan siap diserahkan.',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            foreach ($targetReceipts as $rec) {
                Database::insert('answer_sheet_packing_items', [
                    'batch_id' => $batchId,
                    'receipt_id' => $rec['id'],
                    'sheet_count' => $rec['received_count'],
                    'notes' => null
                ]);

                Database::update('answer_sheet_receipts', ['is_packed' => 1], 'id = :where_id', ['where_id' => $rec['id']]);
            }

            // Create Envelope Label Record
            $school = school_setting();
            $labelPayload = [
                'school_name' => $school['name'],
                'exam_name' => $event['name'],
                'academic_year' => $event['academic_year'],
                'batch_code' => $batchCode,
                'mode' => $mode,
                'class_name' => $className,
                'subject_name' => $subjectId ? Database::fetchColumn("SELECT name FROM subjects WHERE id = ?", [$subjectId]) : 'Semua Mapel',
                'total_sheets' => $totalSheets,
                'envelope_count' => $envelopeCount,
                'created_at' => date('Y-m-d H:i:s')
            ];

            for ($env = 1; $env <= $envelopeCount; $env++) {
                Database::insert('answer_sheet_envelope_labels', [
                    'batch_id' => $batchId,
                    'envelope_number' => $env,
                    'total_envelopes' => $envelopeCount,
                    'label_data_json' => json_encode($labelPayload, JSON_UNESCAPED_UNICODE),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            Database::commit();

            if ($hasDiscrepancy) {
                flash('warning', "Batch packing {$batchCode} berhasil dibuat ({$totalSheets} lembar), namun DITEMUKAN SELISIH pada data lembar jawaban fisik!");
            } else {
                flash('success', "Batch packing {$batchCode} berhasil dibuat lengkap ({$totalSheets} lembar dalam {$envelopeCount} amplop).");
            }
        } catch (\Throwable $e) {
            Database::rollBack();
            flash('error', 'Gagal memproses packing: ' . $e->getMessage());
        }

        Response::redirect(url('/answer-sheets/packing'));
    }

    // 3. Rekap Penyerahan Lembar Jawaban (Section 13.H)
    public function handoverRecap(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        // Summary aggregations
        $stats = Database::fetch("
            SELECT
                SUM(present_count) as total_present,
                SUM(received_count) as total_received,
                SUM(unused_count) as total_unused,
                SUM(damaged_count) as total_damaged,
                SUM(discrepancy) as total_discrepancy
            FROM answer_sheet_receipts
            WHERE exam_event_id = ?
        ", [$event['id']]) ?: [];

        $totalPacked = (int)Database::fetchColumn("
            SELECT SUM(total_sheets) FROM answer_sheet_packing_batches WHERE exam_event_id = ?
        ", [$event['id']]);

        $batches = Database::fetchAll("
            SELECT b.*, s.name as subject_name
            FROM answer_sheet_packing_batches b
            LEFT JOIN subjects s ON b.subject_id = s.id
            WHERE b.exam_event_id = ?
            ORDER BY b.id DESC
        ", [$event['id']]);

        View::render('answer_sheets/handover_recap', [
            'title' => 'Rekapitulasi Penyerahan Lembar Jawaban',
            'event' => $event,
            'stats' => $stats,
            'totalPacked' => $totalPacked,
            'batches' => $batches
        ]);
    }
}
