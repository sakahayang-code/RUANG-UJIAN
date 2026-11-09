<?php

namespace Database\Seeders;

use App\Core\Database;

class InitialSeeder
{
    public static function run(): void
    {
        $pdo = Database::getConnection();

        // 1. Seed Roles
        $roles = [
            ['super_admin', 'Super Admin', 'Akses penuh ke seluruh sistem dan konfigurasi'],
            ['kepala_sekolah', 'Kepala Sekolah', 'Pemantauan dashboard, monitoring dokumen, dan laporan akhir kegiatan'],
            ['ketua_panitia', 'Ketua Panitia', 'Pemantauan, program kerja, persetujuan administrasi dan laporan'],
            ['sekretaris', 'Sekretaris', 'Pengelolaan surat, dokumen, daftar hadir, dan laporan'],
            ['bendahara', 'Bendahara', 'Pengelolaan anggaran biaya dan administrasi keuangan ujian'],
            ['seksi_naskah', 'Seksi Naskah', 'Pengelolaan naskah soal, penggandaan, dan pengemasan LJU'],
            ['seksi_tempat', 'Seksi Tempat', 'Pengelolaan ruang, denah tempat duduk, dan label pintu'],
            ['pengawas', 'Pengawas Ruang', 'Akses jadwal, berita acara, dan presensi ruang yang ditugaskan'],
            ['admin', 'Admin', 'Manajemen master data, kegiatan, pengguna, dan pengaturan'],
            ['operator', 'Operator', 'Input dan pengelolaan data operasional harian ujian'],
            ['penguji', 'Penguji Praktik', 'Akses jadwal praktik, presensi, berita acara, dan dokumentasi praktik'],
        ];

        foreach ($roles as $r) {
            $existing = Database::fetch("SELECT id FROM roles WHERE name = ?", [$r[0]]);
            if (!$existing) {
                Database::insert('roles', [
                    'name' => $r[0],
                    'display_name' => $r[1],
                    'description' => $r[2]
                ]);
            }
        }

        // 2. Seed Permissions
        $permissions = [
            ['dashboard.view', 'Melihat Dashboard', 'dashboard', 'Melihat ringkasan statistik dan notifikasi'],
            ['school.manage', 'Kelola Identitas Sekolah', 'school', 'Mengubah profil sekolah dan kop surat'],
            ['users.manage', 'Kelola Pengguna & Hak Akses', 'users', 'Manajemen akun pengguna dan hak akses role'],
            ['master.manage', 'Kelola Master Data', 'master', 'Kelola siswa, kelas, guru, dan mata pelajaran'],
            ['events.manage', 'Kelola Kegiatan Ujian', 'events', 'Membuat dan mengelola kegiatan ujian multi-tahun'],
            ['committee.manage', 'Kelola Kepanitiaan & Surat', 'committee', 'Struktur, SK, surat undangan, dan surat tugas'],
            ['work_program.manage', 'Kelola Program Kerja & RAB', 'committee', 'Penyusunan program kerja 20 bab dan RAB'],
            ['preparation.manage', 'Kelola Persiapan Dokumen', 'preparation', 'Checklist kisi-kisi, naskah, dan status dokumen nilai'],
            ['written_exam.manage', 'Kelola Ujian Tertulis', 'written', 'Jadwal, pembagian ruang, tempat duduk, pengawas'],
            ['written_exam.supervise', 'Pengawasan Ruang Ujian', 'written', 'Mengisi presensi dan berita acara ruang ujian'],
            ['practical_exam.manage', 'Kelola Ujian Praktik', 'practical', 'Jadwal, kelompok, penguji, stasiun praktik'],
            ['practical_exam.examine', 'Pengujian Praktik', 'practical', 'Mengisi presensi, berita acara, upload media praktik'],
            ['attendance.manage', 'Kelola Administrasi Kehadiran', 'attendance', 'Daftar hadir panitia, pengawas, dan penguji'],
            ['distribution.manage', 'Kelola Distribusi Naskah', 'distribution', 'Pencatatan pengambilan & pengembalian naskah soal'],
            ['answer_sheets.manage', 'Kelola Penerimaan & Packing', 'answer_sheets', 'Penerimaan, verifikasi, packing 3 mode, label'],
            ['print.manage', 'Cetak Perlengkapan Ujian', 'print', 'Kartu peserta, name tag, label naskah, label LJ'],
            ['reports.manage', 'Laporan Akhir & Berita Acara', 'reports', 'Pembuatan laporan akhir 18 bab dan arsip'],
            ['backup.manage', 'Backup & Restore Database', 'system', 'Backup berkas database SQLite dan pemulihan'],
        ];

        foreach ($permissions as $p) {
            $existing = Database::fetch("SELECT id FROM permissions WHERE code = ?", [$p[0]]);
            if (!$existing) {
                Database::insert('permissions', [
                    'code' => $p[0],
                    'name' => $p[1],
                    'category' => $p[2],
                    'description' => $p[3]
                ]);
            }
        }

        // 3. Map Role Permissions
        $rolePermMap = [
            'admin' => ['dashboard.view', 'school.manage', 'users.manage', 'master.manage', 'events.manage', 'committee.manage', 'work_program.manage', 'preparation.manage', 'written_exam.manage', 'practical_exam.manage', 'attendance.manage', 'distribution.manage', 'answer_sheets.manage', 'print.manage', 'reports.manage', 'backup.manage'],
            'ketua_panitia' => ['dashboard.view', 'events.manage', 'committee.manage', 'work_program.manage', 'preparation.manage', 'written_exam.manage', 'practical_exam.manage', 'attendance.manage', 'distribution.manage', 'answer_sheets.manage', 'print.manage', 'reports.manage'],
            'sekretaris' => ['dashboard.view', 'committee.manage', 'work_program.manage', 'preparation.manage', 'written_exam.manage', 'practical_exam.manage', 'attendance.manage', 'distribution.manage', 'answer_sheets.manage', 'print.manage', 'reports.manage'],
            'operator' => ['dashboard.view', 'master.manage', 'events.manage', 'preparation.manage', 'written_exam.manage', 'practical_exam.manage', 'attendance.manage', 'distribution.manage', 'answer_sheets.manage', 'print.manage'],
            'pengawas' => ['dashboard.view', 'written_exam.supervise'],
            'penguji' => ['dashboard.view', 'practical_exam.examine'],
            'kepala_sekolah' => ['dashboard.view', 'reports.manage'],
        ];

        foreach ($rolePermMap as $roleName => $permCodes) {
            $role = Database::fetch("SELECT id FROM roles WHERE name = ?", [$roleName]);
            if ($role) {
                foreach ($permCodes as $code) {
                    $perm = Database::fetch("SELECT id FROM permissions WHERE code = ?", [$code]);
                    if ($perm) {
                        Database::query("INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)", [$role['id'], $perm['id']]);
                    }
                }
            }
        }

        // 4. Seed Default Users
        $defaultPassword = password_hash('password123', PASSWORD_BCRYPT);
        $users = [
            ['admin', $defaultPassword, 'Administrator Sistem', 'admin@ruangujian.sch.id', '081234567890', 'super_admin'],
            ['ketua', $defaultPassword, 'Drs. H. Mulyadi, M.Pd.', 'ketua@ruangujian.sch.id', '081234567891', 'ketua_panitia'],
            ['sekretaris', $defaultPassword, 'Siti Nurhaliza, S.Pd.', 'sekretaris@ruangujian.sch.id', '081234567892', 'sekretaris'],
            ['operator', $defaultPassword, 'Rian Hidayat, S.Kom.', 'operator@ruangujian.sch.id', '081234567893', 'operator'],
            ['pengawas1', $defaultPassword, 'Agus Triyono, S.Pd.', 'agus@ruangujian.sch.id', '081234567894', 'pengawas'],
            ['penguji1', $defaultPassword, 'Dewi Lestari, S.Si.', 'dewi@ruangujian.sch.id', '081234567895', 'penguji'],
            ['kepsek', $defaultPassword, 'Dr. H. Ahmad Sudrajat, M.Pd.', 'kepsek@ruangujian.sch.id', '081234567896', 'kepala_sekolah'],
        ];

        foreach ($users as $u) {
            $existing = Database::fetch("SELECT id FROM users WHERE username = ?", [$u[0]]);
            if (!$existing) {
                Database::insert('users', [
                    'username' => $u[0],
                    'password' => $u[1],
                    'full_name' => $u[2],
                    'email' => $u[3],
                    'phone' => $u[4],
                    'role' => $u[5],
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // 5. Seed School Settings
        $school = Database::fetch("SELECT id FROM school_settings LIMIT 1");
        if (!$school) {
            Database::insert('school_settings', [
                'name' => 'SMP NEGERI 1 BOGOR',
                'npsn' => '20212345',
                'address' => 'Jl. Ir. H. Juanda No. 16, Pabaton, Kecamatan Bogor Tengah',
                'village' => 'Pabaton',
                'district' => 'Bogor Tengah',
                'regency' => 'Kota Bogor',
                'province' => 'Jawa Barat',
                'postal_code' => '16121',
                'phone' => '(0251) 8321234',
                'email' => 'smpn1bogor@kotabogor.sch.id',
                'website' => 'https://smpn1bogor.sch.id',
                'principal_name' => 'Dr. H. Ahmad Sudrajat, M.Pd.',
                'principal_nip' => '197508152000031002',
                'academic_year' => '2026/2027',
                'committee_head' => 'Drs. H. Mulyadi, M.Pd.',
                'committee_secretary' => 'Siti Nurhaliza, S.Pd.',
                'letterhead_text' => "PEMERINTAH DAERAH KOTA BOGOR\nDINAS PENDIDIKAN\nSMP NEGERI 1 BOGOR",
                'letter_number_format' => '{no}/PAN-UJIAN/{bln_romawi}/{thn}',
                'date_format' => 'd F Y',
                'signature_city' => 'Bogor',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // 6. Seed Academic Years
        $academicYears = [
            ['2025/2026', 'Genap', 0],
            ['2026/2027', 'Ganjil', 1],
            ['2026/2027', 'Genap', 0]
        ];
        foreach ($academicYears as $ay) {
            $existing = Database::fetch("SELECT id FROM academic_years WHERE name = ? AND semester = ?", [$ay[0], $ay[1]]);
            if (!$existing) {
                Database::insert('academic_years', [
                    'name' => $ay[0],
                    'semester' => $ay[1],
                    'is_active' => $ay[2],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // 7. Seed Exam Types
        $types = [
            ['PTS', 'Penilaian Tengah Semester', 'Ujian evaluasi capaian tengah semester'],
            ['PAS', 'Penilaian Akhir Semester', 'Ujian akhir semester ganjil'],
            ['PAT', 'Penilaian Akhir Tahun', 'Ujian akhir tahun pelajaran kenaikan kelas'],
            ['US', 'Ujian Sekolah', 'Ujian penentu kelulusan tingkat akhir'],
            ['PRAKTIK', 'Ujian Praktik', 'Ujian unjuk kerja dan kecakapan praktik']
        ];
        foreach ($types as $t) {
            $existing = Database::fetch("SELECT id FROM exam_types WHERE code = ?", [$t[0]]);
            if (!$existing) {
                Database::insert('exam_types', [
                    'code' => $t[0],
                    'name' => $t[1],
                    'description' => $t[2]
                ]);
            }
        }

        // 8. Seed Letter Templates
        $templates = [
            [
                'undangan_rapat',
                'Undangan Rapat Panitia Ujian',
                "Dengan hormat,\nSehubungan dengan persiapan pelaksanaan kegiatan {nama_ujian} Tahun Pelajaran {tahun_pelajaran}, kami mengundang Bapak/Ibu Panitia untuk menghadiri rapat koordinasi yang akan dilaksanakan pada:\n\nHari/Tanggal : {hari_tanggal}\nWaktu : {waktu}\nTempat : {tempat}\nAgenda : {agenda}\n\nMengingat pentingnya agenda tersebut, kami mengharapkan kehadiran Bapak/Ibu tepat pada waktunya. Atas perhatian dan kerja samanya, kami ucapkan terima kasih."
            ],
            [
                'tugas_kisi_kisi',
                'Surat Tugas Penyusun Kisi-kisi',
                "Kepala Sekolah dengan ini menugaskan kepada guru-guru yang tercantum dalam lampiran surat ini untuk menyusun kisi-kisi naskah soal {nama_ujian} Tahun Pelajaran {tahun_pelajaran}.\nTugas ini hendaknya dilaksanakan dengan penuh tanggung jawab sesuai ketentuan dan jadwal yang ditetapkan."
            ],
            [
                'tugas_soal',
                'Surat Tugas Penyusun Naskah Soal',
                "Kepala Sekolah dengan ini menugaskan kepada guru-guru yang tercantum dalam lampiran ini untuk menyusun dan menyerahkan naskah soal {nama_ujian} beserta kelengkapannya secara tepat waktu dan menjaga kerahasiaan naskah ujian."
            ],
            [
                'tugas_pengawas',
                'Surat Tugas Pengawas Ruang Ujian',
                "Kepala Sekolah menugaskan guru-guru yang terdaftar dalam jadwal terlampir untuk melaksanakan pengawasan ruang pada kegiatan {nama_ujian} Tahun Pelajaran {tahun_pelajaran} dengan menjunjung tinggi kejujuran dan tata tertib pengawasan."
            ],
            [
                'tugas_penguji',
                'Surat Tugas Penguji Ujian Praktik',
                "Kepala Sekolah menugaskan guru-guru yang tercantum untuk melaksanakan pengujian pada Ujian Praktik Tahun Pelajaran {tahun_pelajaran} sesuai jadwal, materi uji, dan stasiun yang telah ditentukan."
            ],
        ];

        foreach ($templates as $tmpl) {
            $existing = Database::fetch("SELECT id FROM letter_templates WHERE code = ?", [$tmpl[0]]);
            if (!$existing) {
                Database::insert('letter_templates', [
                    'code' => $tmpl[0],
                    'name' => $tmpl[1],
                    'template_body' => $tmpl[2],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // 9. Seed Default Active Exam Event
        $existingEvent = Database::fetch("SELECT id FROM exam_events WHERE is_active = 1 LIMIT 1");
        if (!$existingEvent) {
            $eventId = Database::insert('exam_events', [
                'name' => 'Penilaian Akhir Semester (PAS) Ganjil 2026/2027',
                'academic_year' => '2026/2027',
                'semester' => 'Ganjil',
                'exam_type' => 'PAS',
                'start_date' => date('Y-m-d', strtotime('+7 days')),
                'end_date' => date('Y-m-d', strtotime('+14 days')),
                'implementation_type' => 'Kertas / Manual',
                'status' => 'aktif',
                'is_active' => 1,
                'notes' => 'Kegiatan Penilaian Akhir Semester Ganjil Tingkat VII, VIII, IX',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $eventId = $existingEvent['id'];
        }

        // 10. Seed Classes if empty
        $classCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM classes");
        if ($classCount === 0) {
            $demoClasses = [
                ['VII A', '7'], ['VII B', '7'],
                ['VIII A', '8'], ['VIII B', '8'],
                ['IX A', '9'], ['IX B', '9']
            ];
            foreach ($demoClasses as $dc) {
                Database::insert('classes', [
                    'name' => $dc[0],
                    'grade' => $dc[1],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // 11. Seed Subjects if empty
        $subjectCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM subjects");
        if ($subjectCount === 0) {
            $demoSubjects = [
                ['MAT', 'Matematika', 'Semua', 'written'],
                ['BIN', 'Bahasa Indonesia', 'Semua', 'written'],
                ['BIG', 'Bahasa Inggris', 'Semua', 'both'],
                ['IPA', 'Ilmu Pengetahuan Alam', 'Semua', 'both'],
                ['IPS', 'Ilmu Pengetahuan Sosial', 'Semua', 'written'],
                ['PAI', 'Pendidikan Agama Islam', 'Semua', 'both'],
                ['PJOK', 'Pendidikan Jasmani & Olahraga', 'Semua', 'practical']
            ];
            foreach ($demoSubjects as $ds) {
                Database::insert('subjects', [
                    'code' => $ds[0],
                    'name' => $ds[1],
                    'grade' => $ds[2],
                    'exam_type' => $ds[3],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // 12. Seed Teachers if empty
        $teacherCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM teachers");
        if ($teacherCount === 0) {
            $demoTeachers = [
                ['Budi Santoso, S.Pd.', '198001012005011001', 'Guru Matematika', '081234567890', 'aktif'],
                ['Siti Aminah, S.Kom.', '198502022008012002', 'Guru Informatika', '081234567891', 'aktif'],
                ['Drs. Bambang Wahyudi', '197203031998021003', 'Guru Bahasa Indonesia', '081234567892', 'aktif'],
                ['Ratna Dewi, S.Pd.', '198804042010012004', 'Guru Bahasa Inggris', '081234567893', 'aktif'],
                ['Ahmad Fauzi, M.Si.', '198305052006041005', 'Guru IPA', '081234567894', 'aktif'],
                ['Endang Sulistyo, S.Pd.', '198606062009022006', 'Guru IPS', '081234567895', 'aktif']
            ];
            foreach ($demoTeachers as $dt) {
                Database::insert('teachers', [
                    'name' => $dt[0],
                    'nip' => $dt[1],
                    'position' => $dt[2],
                    'phone' => $dt[3],
                    'status' => $dt[4],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // 13. Seed Committee for event if empty
        $comm = Database::fetch("SELECT id FROM committees WHERE exam_event_id = ?", [$eventId]);
        if (!$comm) {
            $commId = Database::insert('committees', [
                'exam_event_id' => $eventId,
                'title' => 'Panitia Pelaksana PAS Ganjil 2026/2027',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $commId = $comm['id'];
        }

        $commCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM committee_members WHERE committee_id = ?", [$commId]);
        if ($commCount === 0) {
            $demoCommittees = [
                ['Penanggung Jawab', 'Dr. H. Ahmad Sudrajat, M.Pd.', '197508152000031002', 'Pimpinan', 1],
                ['Ketua Panitia', 'Budi Santoso, S.Pd.', '198001012005011001', 'Ketua', 2],
                ['Sekretaris', 'Siti Aminah, S.Kom.', '198502022008012002', 'Sekretariat', 3],
                ['Bendahara', 'Ratna Dewi, S.Pd.', '198804042010012004', 'Keuangan', 4],
                ['Seksi Naskah', 'Ahmad Fauzi, M.Si.', '198305052006041005', 'Penggandaan & LJU', 5],
                ['Seksi Tempat & Ruang', 'Endang Sulistyo, S.Pd.', '198606062009022006', 'Ruang & Denah', 6]
            ];
            foreach ($demoCommittees as $dcm) {
                Database::insert('committee_members', [
                    'committee_id' => $commId,
                    'role_title' => $dcm[0],
                    'custom_name' => $dcm[1],
                    'custom_nip' => $dcm[2],
                    'section_name' => $dcm[3],
                    'order_index' => $dcm[4],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }
}
