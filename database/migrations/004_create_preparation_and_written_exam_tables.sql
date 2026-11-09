-- Migration 004: Preparation, Printing, and Written Exam Tables

CREATE TABLE IF NOT EXISTS exam_rules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    rule_type VARCHAR(50) NOT NULL, -- tata_tertib_peserta, tata_tertib_pengawas, tata_tertib_penguji, juknis, sop_distribusi, sop_pengumpulan, sop_susulan
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS exam_document_submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    teacher_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    grade VARCHAR(20) NOT NULL,
    doc_type VARCHAR(50) NOT NULL, -- kisi_kisi, naskah_soal, kunci_jawaban, dokumen_nilai
    submission_date DATE,
    status VARCHAR(50) DEFAULT 'belum_diserahkan', -- belum_diserahkan, sudah_diserahkan, sedang_diperiksa, perlu_revisi, selesai
    notes TEXT,
    reviewer_id INTEGER,
    review_notes TEXT,
    reviewed_at DATETIME,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS exam_printing_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    grade VARCHAR(20) NOT NULL,
    participant_count INTEGER NOT NULL DEFAULT 0,
    main_copies INTEGER NOT NULL DEFAULT 0,
    reserve_copies INTEGER NOT NULL DEFAULT 2, -- configurable cadangan
    total_copies INTEGER NOT NULL DEFAULT 0,
    printed_copies INTEGER NOT NULL DEFAULT 0,
    unprinted_copies INTEGER NOT NULL DEFAULT 0,
    status VARCHAR(50) DEFAULT 'Belum Dicetak', -- Belum Dicetak, Sedang Dicetak, Selesai Dicetak, Terverifikasi
    pic_name VARCHAR(150),
    print_date DATE,
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS written_exam_schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    exam_date DATE NOT NULL,
    day_name VARCHAR(20) NOT NULL,
    session VARCHAR(20) NOT NULL DEFAULT '1',
    start_time VARCHAR(10) NOT NULL,
    end_time VARCHAR(10) NOT NULL,
    subject_id INTEGER NOT NULL,
    grade VARCHAR(20) NOT NULL,
    notes TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS written_exam_supervisors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    schedule_id INTEGER NOT NULL,
    room_id INTEGER NOT NULL,
    supervisor1_id INTEGER,
    supervisor2_id INTEGER,
    supervisor1_name_snapshot VARCHAR(150),
    supervisor2_name_snapshot VARCHAR(150),
    created_at DATETIME NOT NULL,
    FOREIGN KEY (schedule_id) REFERENCES written_exam_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES exam_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor1_id) REFERENCES teachers(id) ON DELETE SET NULL,
    FOREIGN KEY (supervisor2_id) REFERENCES teachers(id) ON DELETE SET NULL,
    UNIQUE(schedule_id, room_id)
);

CREATE TABLE IF NOT EXISTS written_exam_attendances (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    schedule_id INTEGER NOT NULL,
    room_id INTEGER NOT NULL,
    participant_id INTEGER NOT NULL,
    status VARCHAR(50) DEFAULT 'hadir', -- hadir, tidak_hadir, izin, sakit, terlambat, susulan
    check_in_time VARCHAR(10),
    notes TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES written_exam_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES exam_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES exam_participants(id) ON DELETE CASCADE,
    UNIQUE(schedule_id, room_id, participant_id)
);

CREATE TABLE IF NOT EXISTS written_exam_minutes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    schedule_id INTEGER NOT NULL,
    room_id INTEGER NOT NULL,
    present_count INTEGER DEFAULT 0,
    absent_count INTEGER DEFAULT 0,
    late_count INTEGER DEFAULT 0,
    notes TEXT,
    supervisor_signature VARCHAR(150),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES written_exam_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES exam_rooms(id) ON DELETE CASCADE,
    UNIQUE(schedule_id, room_id)
);

CREATE TABLE IF NOT EXISTS written_exam_incidents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    schedule_id INTEGER NOT NULL,
    room_id INTEGER NOT NULL,
    incident_time VARCHAR(10),
    participant_id INTEGER,
    incident_description TEXT NOT NULL,
    follow_up TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES written_exam_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES exam_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES exam_participants(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_doc_sub_event ON exam_document_submissions(exam_event_id);
CREATE INDEX IF NOT EXISTS idx_schedules_event ON written_exam_schedules(exam_event_id);
CREATE INDEX IF NOT EXISTS idx_attendances_sched_part ON written_exam_attendances(schedule_id, participant_id);
