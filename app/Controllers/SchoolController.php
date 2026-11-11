<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class SchoolController
{
    public function index(Request $request): void
    {
        $school = school_setting();
        View::render('school/index', [
            'title' => 'Pengaturan Identitas Sekolah',
            'school' => $school
        ]);
    }

    public function update(Request $request): void
    {
        $school = school_setting();
        $id = $school['id'] ?? 1;

        $name = trim((string)$request->input('name'));
        if (empty($name)) {
            flash('error', 'Nama sekolah tidak boleh kosong.');
            Response::back();
        }

        $data = [
            'name' => $name,
            'npsn' => trim((string)$request->input('npsn')),
            'address' => trim((string)$request->input('address')),
            'village' => trim((string)$request->input('village')),
            'district' => trim((string)$request->input('district')),
            'regency' => trim((string)$request->input('regency')),
            'province' => trim((string)$request->input('province')),
            'postal_code' => trim((string)$request->input('postal_code')),
            'phone' => trim((string)$request->input('phone')),
            'email' => trim((string)$request->input('email')),
            'website' => trim((string)$request->input('website')),
            'principal_name' => trim((string)$request->input('principal_name')),
            'principal_nip' => trim((string)$request->input('principal_nip')),
            'academic_year' => trim((string)$request->input('academic_year')),
            'committee_head' => trim((string)$request->input('committee_head')),
            'committee_secretary' => trim((string)$request->input('committee_secretary')),
            'letterhead_text' => trim((string)$request->input('letterhead_text')),
            'letter_number_format' => trim((string)$request->input('letter_number_format')),
            'date_format' => trim((string)$request->input('date_format', 'd F Y')),
            'signature_city' => trim((string)$request->input('signature_city', 'Bogor')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Handle Remove Logo
        if ($request->input('remove_logo') === '1') {
            if (!empty($school['logo_path'])) {
                $oldLogo = dirname(__DIR__, 2) . '/storage/uploads/' . $school['logo_path'];
                if (file_exists($oldLogo)) {
                    @unlink($oldLogo);
                }
            }
            $data['logo_path'] = null;
        }

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            $mime = mime_content_type($file['tmp_name']);
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
                flash('error', 'Format logo tidak valid. Hanya menerima format JPG, PNG, atau WEBP.');
                Response::back();
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                flash('error', 'Ukuran logo tidak boleh melebihi 5 MB.');
                Response::back();
            }

            // Remove previous logo if exists
            if (!empty($school['logo_path'])) {
                $oldLogo = dirname(__DIR__, 2) . '/storage/uploads/' . $school['logo_path'];
                if (file_exists($oldLogo)) {
                    @unlink($oldLogo);
                }
            }

            $newFileName = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $targetDir = dirname(__DIR__, 2) . '/storage/uploads/logos';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $destination = $targetDir . '/' . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $data['logo_path'] = 'logos/' . $newFileName;
            }
        }

        // Handle Remove Letterhead Image
        if ($request->input('remove_letterhead_image') === '1') {
            if (!empty($school['letterhead_image_path'])) {
                $oldLetterhead = dirname(__DIR__, 2) . '/storage/uploads/' . $school['letterhead_image_path'];
                if (file_exists($oldLetterhead)) {
                    @unlink($oldLetterhead);
                }
            }
            $data['letterhead_image_path'] = null;
        }

        // Handle Letterhead Image Upload
        if ($request->hasFile('letterhead_image')) {
            $file = $request->file('letterhead_image');
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'text/plain', 'text/xml'];
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
            $mime = mime_content_type($file['tmp_name']);
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
                flash('error', 'Format kop surat tidak valid. Hanya menerima berkas PNG, JPG, WEBP, atau SVG.');
                Response::back();
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                flash('error', 'Ukuran berkas kop surat tidak boleh melebihi 5 MB.');
                Response::back();
            }

            // Remove previous letterhead image if exists
            if (!empty($school['letterhead_image_path'])) {
                $oldLetterhead = dirname(__DIR__, 2) . '/storage/uploads/' . $school['letterhead_image_path'];
                if (file_exists($oldLetterhead)) {
                    @unlink($oldLetterhead);
                }
            }

            $newFileName = 'kop_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $targetDir = dirname(__DIR__, 2) . '/storage/uploads/letterheads';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $destination = $targetDir . '/' . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $data['letterhead_image_path'] = 'letterheads/' . $newFileName;
            }
        }

        Database::update('school_settings', $data, 'id = :where_id', ['where_id' => $id]);
        Auth::logAudit('UPDATE', 'school_settings', $id, $school, $data);

        flash('success', 'Identitas dan pengaturan sekolah berhasil diperbarui.');
        Response::redirect(url('/school'));
    }
}
