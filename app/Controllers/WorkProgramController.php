<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\DocxGenerator;

class WorkProgramController
{
    public function index(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $school = school_setting();

        // Find or create default work program for this event
        $program = Database::fetch("SELECT * FROM committee_work_programs WHERE exam_event_id = ?", [$event['id']]);

        if (!$program) {
            $defaultTitle = "PROGRAM KERJA DAN RENCANA ANGGARAN BIAYA (RAB)\nPENYELENGGARAAN " . strtoupper($event['name']);
            $defaultForeword = "Puji syukur kami panjatkan ke hadirat Tuhan Yang Maha Esa atas limpahan rahmat dan karunia-Nya sehingga Program Kerja dan Rencana Anggaran Biaya penyelenggaraan " . $event['name'] . " Tahun Pelajaran " . $event['academic_year'] . " dapat diselesaikan dengan baik.\n\nProgram kerja ini disusun sebagai pedoman operasional bagi seluruh panitia dalam menjalankan tugas dan tanggung jawab demi terlaksananya ujian yang tertib, lancar, jujur, dan berintegritas.\n\nKami menyampaikan terima kasih kepada Kepala Sekolah, Dewan Guru, dan segenap pihak yang telah mendukung penyusunan program kerja ini.";
            $defaultBg = "Evaluasi hasil belajar merupakan bagian integral dari proses pendidikan untuk mengukur ketercapaian kompetensi peserta didik sesuai kurikulum yang berlaku. Penyelenggaraan " . $event['name'] . " memerlukan perencanaan yang sistematis, transparan, dan akuntabel agar seluruh tahapan dari persiapan naskah, penggandaan, pelaksanaan pengawasan, hingga penyerahan lembar jawaban berjalan sesuai standar operasional prosedur.";
            $defaultLegal = "1. Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional.\n2. Peraturan Pemerintah Nomor 4 Tahun 2022 tentang Standar Nasional Pendidikan.\n3. Peraturan Menteri Pendidikan, Kebudayaan, Riset, dan Teknologi tentang Penilaian Hasil Belajar.\n4. Kalender Pendidikan " . $school['name'] . " Tahun Pelajaran " . $event['academic_year'] . ".\n5. Surat Keputusan Kepala Sekolah tentang Pembentukan Panitia Ujian.";
            $defaultObj = "1. Terlaksananya kegiatan ujian secara tertib, aman, dan lancar.\n2. Mengukur capaian kompetensi akademik dan keterampilan peserta didik secara objektif.\n3. Menyediakan administrasi kepanitiaan, distribusi naskah, dan pengemasan lembar jawaban yang akurat dan terverifikasi.";
            $defaultTheme = "Mewujudkan Asesmen dan Ujian Sekolah yang Jujur, Mandiri, dan Berkarakter.";
            $defaultTarget = "Seluruh peserta didik " . $school['name'] . " yang terdaftar aktif pada Tahun Pelajaran " . $event['academic_year'] . ".";
            $defaultFacilities = "Persiapan naskah soal, format daftar hadir peserta, daftar hadir pengawas, berita acara ruang, amplop naskah berlabel per ruang, amplop lembar jawaban per kelas dan per mapel, kartu peserta ber-QR, name tag panitia, nomor meja, dan nomor ruangan.";
            $defaultSupervision = "Pengawasan ruang dilakukan secara silang oleh guru mata pelajaran dan pengawas yang ditugaskan dengan rasio 1-2 pengawas per ruang ujian.";
            $defaultDoc = "Pencatatan berita acara pelaksanaan, catatan kejadian khusus, presensi panitia, presensi pengawas, dokumentasi foto kegiatan, dan laporan pertanggungjawaban akhir 18 bab.";
            $defaultClosing = "Demikian program kerja ini disusun sebagai acuan kerja panitia. Keberhasilan pelaksanaan ujian sangat bergantung pada kerja sama dan dedikasi seluruh pihak terkait.";

            $pId = Database::insert('committee_work_programs', [
                'exam_event_id' => $event['id'],
                'title' => $defaultTitle,
                'academic_year' => $event['academic_year'],
                'foreword' => $defaultForeword,
                'background' => $defaultBg,
                'legal_basis' => $defaultLegal,
                'objectives' => $defaultObj,
                'theme' => $defaultTheme,
                'target_description' => $defaultTarget,
                'facilities_plan' => $defaultFacilities,
                'supervision_plan' => $defaultSupervision,
                'documentation_plan' => $defaultDoc,
                'closing_notes' => $defaultClosing,
                'approval_date' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Seed default activity items
            $defaultActivities = [
                ['Rapat Pembentukan dan Koordinasi Panitia', date('Y-m-d', strtotime('-14 days')), date('Y-m-d', strtotime('-14 days')), 'Ketua Panitia', 'Penyusunan SK dan pembagian tugas'],
                ['Pengumpulan Kisi-kisi dan Naskah Soal', date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('-7 days')), 'Seksi Naskah', 'Pemeriksaan dan telaah soal'],
                ['Penggandaan dan Pengepakan Naskah Soal', date('Y-m-d', strtotime('-6 days')), date('Y-m-d', strtotime('-2 days')), 'Seksi Penggandaan', 'Penggandaan utama + cadangan & label per ruang'],
                ['Penataan Ruang dan Nomor Tempat Duduk', date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day')), 'Seksi Perlengkapan', 'Pemasangan nomor ruang dan denah meja'],
                ['Pelaksanaan Ujian Tertulis dan Praktik', $event['start_date'], $event['end_date'], 'Panitia & Pengawas', 'Pelaksanaan sesuai jadwal dan SOP'],
                ['Penerimaan dan Packing Lembar Jawaban', $event['start_date'], date('Y-m-d', strtotime($event['end_date'] . ' +1 day')), 'Seksi Lembar Jawaban', 'Pengecekan fisik dan packing 3 mode'],
                ['Penyusunan dan Pengesahan Laporan Akhir', date('Y-m-d', strtotime($event['end_date'] . ' +2 days')), date('Y-m-d', strtotime($event['end_date'] . ' +5 days')), 'Sekretaris', 'Laporan 18 bab ke Kepala Sekolah']
            ];

            foreach ($defaultActivities as $idx => $act) {
                Database::insert('committee_work_program_items', [
                    'work_program_id' => $pId,
                    'item_order' => $idx + 1,
                    'activity_name' => $act[0],
                    'start_date' => $act[1],
                    'end_date' => $act[2],
                    'pic_name' => $act[3],
                    'notes' => $act[4]
                ]);
            }

            // Seed default budget items (RAB)
            $defaultBudgets = [
                ['Penggandaan Naskah Soal dan Lembar Jawaban', 1500, 'Lembar', 300, 450000, 'Penggandaan'],
                ['Amplop Coklat Tali Tebal (Naskah & LJ)', 50, 'Buah', 3500, 175000, 'Perlengkapan'],
                ['Kertas Label Amplop dan Stiker Penomoran Meja', 5, 'Pak', 25000, 125000, 'Perlengkapan'],
                ['Name Tag Panitia dan Pengawas', 30, 'Pcs', 5000, 150000, 'Perlengkapan'],
                ['Konsumsi Rapat Koordinasi Panitia', 25, 'Kotak', 25000, 625000, 'Konsumsi'],
                ['Konsumsi Harian Panitia dan Pengawas (Pelaksanaan)', 5, 'Hari', 350000, 1750000, 'Konsumsi'],
                ['Penyusunan, Penjilidan, dan Penggandaan Laporan Akhir', 3, 'Buku', 50000, 150000, 'Pelaporan'],
            ];

            foreach ($defaultBudgets as $idx => $b) {
                Database::insert('committee_budgets', [
                    'work_program_id' => $pId,
                    'item_order' => $idx + 1,
                    'description' => $b[0],
                    'volume' => $b[1],
                    'unit' => $b[2],
                    'unit_price' => $b[3],
                    'total_amount' => $b[4],
                    'category' => $b[5]
                ]);
            }

            $program = Database::fetch("SELECT * FROM committee_work_programs WHERE id = ?", [$pId]);
        }

        $activities = Database::fetchAll("SELECT * FROM committee_work_program_items WHERE work_program_id = ? ORDER BY item_order ASC, id ASC", [$program['id']]);
        $budgets = Database::fetchAll("SELECT * FROM committee_budgets WHERE work_program_id = ? ORDER BY item_order ASC, id ASC", [$program['id']]);

        // Connected schedules and committee
        $committeeMembers = Database::fetchAll("
            SELECT cm.*, t.name as teacher_name, t.nip as teacher_nip
            FROM committee_members cm
            JOIN committees c ON cm.committee_id = c.id
            LEFT JOIN teachers t ON cm.teacher_id = t.id
            WHERE c.exam_event_id = ?
            ORDER BY cm.order_index ASC
        ", [$event['id']]);

        $writtenSchedules = Database::fetchAll("
            SELECT ws.*, s.name as subject_name, s.code as subject_code
            FROM written_exam_schedules ws
            JOIN subjects s ON ws.subject_id = s.id
            WHERE ws.exam_event_id = ?
            ORDER BY ws.exam_date ASC, ws.session ASC
        ", [$event['id']]);

        $practicalSchedules = Database::fetchAll("
            SELECT ps.*, s.name as subject_name
            FROM practical_exam_schedules ps
            JOIN subjects s ON ps.subject_id = s.id
            WHERE ps.exam_event_id = ?
            ORDER BY ps.exam_date ASC
        ", [$event['id']]);

        View::render('work_program/index', [
            'title' => 'Program Kerja Panitia & RAB',
            'event' => $event,
            'school' => $school,
            'program' => $program,
            'activities' => $activities,
            'budgets' => $budgets,
            'committeeMembers' => $committeeMembers,
            'writtenSchedules' => $writtenSchedules,
            'practicalSchedules' => $practicalSchedules
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->input('id');
        $program = Database::fetch("SELECT * FROM committee_work_programs WHERE id = ?", [$id]);
        if (!$program) {
            flash('error', 'Program kerja tidak ditemukan.');
            Response::back();
        }

        $data = [
            'title' => trim((string)$request->input('title')),
            'academic_year' => trim((string)$request->input('academic_year')),
            'foreword' => trim((string)$request->input('foreword')),
            'background' => trim((string)$request->input('background')),
            'legal_basis' => trim((string)$request->input('legal_basis')),
            'objectives' => trim((string)$request->input('objectives')),
            'theme' => trim((string)$request->input('theme')),
            'target_description' => trim((string)$request->input('target_description')),
            'facilities_plan' => trim((string)$request->input('facilities_plan')),
            'supervision_plan' => trim((string)$request->input('supervision_plan')),
            'documentation_plan' => trim((string)$request->input('documentation_plan')),
            'closing_notes' => trim((string)$request->input('closing_notes')),
            'approval_date' => (string)$request->input('approval_date'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        Database::beginTransaction();
        try {
            Database::update('committee_work_programs', $data, 'id = :where_id', ['where_id' => $id]);

            // Save dynamic Activities
            Database::delete('committee_work_program_items', 'work_program_id = ?', [$id]);
            $activities = $request->input('activities') ?? [];
            $order = 1;
            foreach ($activities as $act) {
                if (empty($act['name'])) continue;
                Database::insert('committee_work_program_items', [
                    'work_program_id' => $id,
                    'item_order' => $order++,
                    'activity_name' => trim((string)$act['name']),
                    'start_date' => !empty($act['start_date']) ? $act['start_date'] : null,
                    'end_date' => !empty($act['end_date']) ? $act['end_date'] : null,
                    'pic_name' => trim((string)($act['pic'] ?? '')),
                    'notes' => trim((string)($act['notes'] ?? ''))
                ]);
            }

            // Save dynamic Budgets (RAB)
            Database::delete('committee_budgets', 'work_program_id = ?', [$id]);
            $budgets = $request->input('budgets') ?? [];
            $bOrder = 1;
            foreach ($budgets as $b) {
                if (empty($b['description'])) continue;
                $volume = (float)($b['volume'] ?? 1);
                $price = (float)($b['unit_price'] ?? 0);
                $total = $volume * $price;

                Database::insert('committee_budgets', [
                    'work_program_id' => $id,
                    'item_order' => $bOrder++,
                    'description' => trim((string)$b['description']),
                    'volume' => $volume,
                    'unit' => trim((string)($b['unit'] ?? 'Pcs')),
                    'unit_price' => $price,
                    'total_amount' => $total,
                    'category' => 'Operasional'
                ]);
            }

            Database::commit();
            Auth::logAudit('UPDATE', 'committee_work_programs', $id, $program, $data);
            flash('success', 'Program Kerja dan RAB Panitia berhasil disimpan.');
        } catch (\Throwable $e) {
            Database::rollBack();
            flash('error', 'Gagal menyimpan perubahan: ' . $e->getMessage());
        }

        Response::redirect(url('/work-program'));
    }

    public function print(Request $request): void
    {
        $event = active_event();
        $program = Database::fetch("SELECT * FROM committee_work_programs WHERE exam_event_id = ?", [$event['id']]);
        if (!$program) die("Program kerja belum dibuat.");

        $school = school_setting();
        $activities = Database::fetchAll("SELECT * FROM committee_work_program_items WHERE work_program_id = ? ORDER BY item_order ASC", [$program['id']]);
        $budgets = Database::fetchAll("SELECT * FROM committee_budgets WHERE work_program_id = ? ORDER BY item_order ASC", [$program['id']]);

        $committeeMembers = Database::fetchAll("
            SELECT cm.*, t.name as teacher_name, t.nip as teacher_nip
            FROM committee_members cm
            JOIN committees c ON cm.committee_id = c.id
            LEFT JOIN teachers t ON cm.teacher_id = t.id
            WHERE c.exam_event_id = ?
            ORDER BY cm.order_index ASC
        ", [$event['id']]);

        $writtenSchedules = Database::fetchAll("
            SELECT ws.*, s.name as subject_name, s.code as subject_code
            FROM written_exam_schedules ws
            JOIN subjects s ON ws.subject_id = s.id
            WHERE ws.exam_event_id = ?
            ORDER BY ws.exam_date ASC, ws.session ASC
        ", [$event['id']]);

        $practicalSchedules = Database::fetchAll("
            SELECT ps.*, s.name as subject_name
            FROM practical_exam_schedules ps
            JOIN subjects s ON ps.subject_id = s.id
            WHERE ps.exam_event_id = ?
            ORDER BY ps.exam_date ASC
        ", [$event['id']]);

        View::render('work_program/print', [
            'title' => 'Program Kerja Panitia ' . $event['name'],
            'event' => $event,
            'school' => $school,
            'program' => $program,
            'activities' => $activities,
            'budgets' => $budgets,
            'committeeMembers' => $committeeMembers,
            'writtenSchedules' => $writtenSchedules,
            'practicalSchedules' => $practicalSchedules,
            'hideKop' => true // Program kerja has its own formal Cover
        ], 'layouts/print');
    }

    public function exportDocx(Request $request): void
    {
        $event = active_event();
        $program = Database::fetch("SELECT * FROM committee_work_programs WHERE exam_event_id = ?", [$event['id']]);
        if (!$program) die("Program kerja belum dibuat.");

        $school = school_setting();
        $activities = Database::fetchAll("SELECT * FROM committee_work_program_items WHERE work_program_id = ? ORDER BY item_order ASC", [$program['id']]);
        $budgets = Database::fetchAll("SELECT * FROM committee_budgets WHERE work_program_id = ? ORDER BY item_order ASC", [$program['id']]);

        $docx = new DocxGenerator("Program Kerja " . $event['name']);

        // 1. Cover
        $docx->addTitle("PROGRAM KERJA DAN RENCANA ANGGARAN BIAYA (RAB)");
        $docx->addParagraph("PENYELENGGARAAN " . strtoupper($event['name']), ['align' => 'center', 'bold' => true]);
        $docx->addParagraph("TAHUN PELAJARAN " . $event['academic_year'], ['align' => 'center', 'bold' => true]);
        $docx->addParagraph("\n\n\n\n\n");
        $docx->addParagraph($school['name'], ['align' => 'center', 'bold' => true]);
        $docx->addParagraph($school['address'], ['align' => 'center']);
        $docx->addParagraph($school['signature_city'] . " - " . date('Y'), ['align' => 'center', 'bold' => true]);
        $docx->addPageBreak();

        // 2. Kata Pengantar
        $docx->addHeading("KATA PENGANTAR", 1);
        $docx->addParagraph($program['foreword']);
        $docx->addParagraph("\n" . $school['signature_city'] . ", " . format_date_id($program['approval_date']), ['align' => 'right']);
        $docx->addParagraph("Ketua Panitia,\n\n\n" . ($school['committee_head'] ?: 'Panitia Ujian'), ['align' => 'right', 'bold' => true]);
        $docx->addPageBreak();

        // 3. Bab Pendahuluan & Latar Belakang
        $docx->addHeading("BAB I. PENDAHULUAN", 1);
        $docx->addHeading("A. Latar Belakang", 2);
        $docx->addParagraph($program['background']);

        $docx->addHeading("B. Dasar Hukum", 2);
        $docx->addParagraph($program['legal_basis']);

        $docx->addHeading("C. Maksud dan Tujuan", 2);
        $docx->addParagraph($program['objectives']);

        $docx->addHeading("D. Nama dan Tema Kegiatan", 2);
        $docx->addParagraph("Nama Kegiatan: " . $event['name']);
        $docx->addParagraph("Tema Kegiatan: " . $program['theme']);

        $docx->addHeading("E. Waktu dan Tempat Pelaksanaan", 2);
        $docx->addParagraph("Waktu: " . format_date_id($event['start_date']) . " s.d. " . format_date_id($event['end_date']));
        $docx->addParagraph("Tempat: Kampus " . $school['name']);

        $docx->addHeading("F. Sasaran / Peserta", 2);
        $docx->addParagraph($program['target_description']);
        $docx->addPageBreak();

        // 4. Rencana Kerja & Tahapan
        $docx->addHeading("BAB II. RENCANA KERJA DAN TAHAPAN PERSIAPAN", 1);
        $actHeaders = ['No', 'Nama Kegiatan', 'Mulai', 'Selesai', 'Penanggung Jawab', 'Keterangan'];
        $actRows = [];
        foreach ($activities as $idx => $act) {
            $actRows[] = [
                $idx + 1,
                $act['activity_name'],
                format_date_id($act['start_date']),
                format_date_id($act['end_date']),
                $act['pic_name'],
                $act['notes']
            ];
        }
        $docx->addTable($actHeaders, $actRows);
        $docx->addPageBreak();

        // 5. Rencana Anggaran Biaya (RAB)
        $docx->addHeading("BAB III. RENCANA ANGGARAN BIAYA (RAB)", 1);
        $rabHeaders = ['No', 'Uraian Kebutuhan Anggaran', 'Volume', 'Satuan', 'Harga Satuan', 'Jumlah'];
        $rabRows = [];
        $grandTotal = 0;
        foreach ($budgets as $idx => $b) {
            $grandTotal += (float)$b['total_amount'];
            $rabRows[] = [
                $idx + 1,
                $b['description'],
                $b['volume'],
                $b['unit'],
                rupiah($b['unit_price']),
                rupiah($b['total_amount'])
            ];
        }
        $rabRows[] = ['', 'TOTAL KESELURUHAN RENCANA ANGGARAN', '', '', '', rupiah($grandTotal)];
        $docx->addTable($rabHeaders, $rabRows);
        $docx->addPageBreak();

        // 6. Penutup dan Lembar Pengesahan
        $docx->addHeading("BAB IV. PENUTUP DAN LEMBAR PENGESAHAN", 1);
        $docx->addParagraph($program['closing_notes']);

        $docx->addParagraph("\n\nLEMBAR PENGESAHAN", ['align' => 'center', 'bold' => true]);
        $docx->addParagraph("Disahkan di: " . $school['signature_city'], ['align' => 'center']);
        $docx->addParagraph("Pada tanggal: " . format_date_id($program['approval_date']), ['align' => 'center']);

        $sigHeaders = ['Mengetahui, Kepala Sekolah', 'Ketua Panitia Ujian'];
        $sigRows = [
            ["\n\n\n\n" . $school['principal_name'] . "\nNIP. " . $school['principal_nip'], "\n\n\n\n" . ($school['committee_head'] ?: 'Ketua Panitia') . "\nNIP. -"]
        ];
        $docx->addTable($sigHeaders, $sigRows);

        $filename = 'program_kerja_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $event['name']) . '.docx';
        $docx->output($filename);
    }
}
