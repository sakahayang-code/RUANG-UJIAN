-- Migration 005: Practical Exam and Administrative Attendance Tables

CREATE TABLE IF NOT EXISTS practical_exam_schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    grade VARCHAR(20) NOT NULL,
    exam_date DATE NOT NULL,
    start_time VARCHAR(10) NOT NULL,
    end_time VARCHAR(10) NOT NULL,
    location VARCHAR(150) NOT NULL, -- Lab Komputer, Lab IPA, Lapangan, Bengkel, dll.
    station VARCHAR(100), -- Stasiun 1, Pos A, dll.
    method VARCHAR(50) DEFAULT 'individu', -- individu, kelompok, rotasi
    notes TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS practical_exam_groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    practical_schedule_id INTEGER NOT NULL,
    group_name VARCHAR(100) NOT NULL,
    order_index INTEGER DEFAULT 0,
    FOREIGN KEY (practical_schedule_id) REFERENCES practical_exam_schedules(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS practical_exam_group_members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    group_id INTEGER NOT NULL,
    participant_id INTEGER NOT NULL,
    FOREIGN KEY (group_id) REFERENCES practical_exam_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES exam_participants(id) ON DELETE CASCADE,
    UNIQUE(group_id, participant_id)
);

CREATE TABLE IF NOT EXISTS practical_exam_assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    practical_schedule_id INTEGER NOT NULL,
    examiner_id INTEGER NOT NULL,
    examiner_role VARCHAR(100) DEFAULT 'Penguji Utama',
    examiner_name_snapshot VARCHAR(150),
    created_at DATETIME NOT NULL,
    FOREIGN KEY (practical_schedule_id) REFERENCES practical_exam_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (examiner_id) REFERENCES teachers(id) ON DELETE CASCADE,
    UNIQUE(practical_schedule_id, examiner_id)
);

CREATE TABLE IF NOT EXISTS practical_exam_attendances (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    practical_schedule_id INTEGER NOT NULL,
    participant_id INTEGER NOT NULL,
    status VARCHAR(50) DEFAULT 'hadir', -- hadir, tidak_hadir, izin, sakit
    check_in_time VARCHAR(10),
    notes TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (practical_schedule_id) REFERENCES practical_exam_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES exam_participants(id) ON DELETE CASCADE,
    UNIQUE(practical_schedule_id, participant_id)
);

CREATE TABLE IF NOT EXISTS practical_exam_documents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    practical_schedule_id INTEGER NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL, -- image, video, pdf
    file_size INTEGER,
    caption VARCHAR(255),
    uploaded_by INTEGER,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (practical_schedule_id) REFERENCES practical_exam_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS practical_exam_minutes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    practical_schedule_id INTEGER NOT NULL UNIQUE,
    present_count INTEGER DEFAULT 0,
    absent_count INTEGER DEFAULT 0,
    incident_notes TEXT,
    examiner_notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (practical_schedule_id) REFERENCES practical_exam_schedules(id) ON DELETE CASCADE
);

-- Administrative Attendance Tables
CREATE TABLE IF NOT EXISTS committee_attendances (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    attendance_date DATE NOT NULL,
    committee_member_id INTEGER NOT NULL,
    check_in_time VARCHAR(10),
    check_out_time VARCHAR(10),
    status VARCHAR(50) DEFAULT 'hadir', -- hadir, tidak_hadir, izin, sakit, terlambat
    notes TEXT,
    digital_signed INTEGER DEFAULT 0,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (committee_member_id) REFERENCES committee_members(id) ON DELETE CASCADE,
    UNIQUE(exam_event_id, attendance_date, committee_member_id)
);

CREATE TABLE IF NOT EXISTS supervisor_attendances (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    written_supervisor_id INTEGER NOT NULL,
    attendance_date DATE NOT NULL,
    session VARCHAR(20) NOT NULL,
    status VARCHAR(50) DEFAULT 'hadir', -- hadir, tidak_hadir, izin, sakit, terlambat
    check_in_time VARCHAR(10),
    check_out_time VARCHAR(10),
    digital_signed INTEGER DEFAULT 0,
    notes TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (written_supervisor_id) REFERENCES written_exam_supervisors(id) ON DELETE CASCADE,
    UNIQUE(exam_event_id, written_supervisor_id, attendance_date, session)
);

CREATE TABLE IF NOT EXISTS practical_examiner_attendances (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    practical_assignment_id INTEGER NOT NULL,
    attendance_date DATE NOT NULL,
    session VARCHAR(20) DEFAULT '1',
    status VARCHAR(50) DEFAULT 'hadir', -- hadir, tidak_hadir, izin, sakit, terlambat
    check_in_time VARCHAR(10),
    check_out_time VARCHAR(10),
    digital_signed INTEGER DEFAULT 0,
    notes TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (practical_assignment_id) REFERENCES practical_exam_assignments(id) ON DELETE CASCADE,
    UNIQUE(exam_event_id, practical_assignment_id, attendance_date, session)
);

CREATE INDEX IF NOT EXISTS idx_prac_sched_event ON practical_exam_schedules(exam_event_id);
CREATE INDEX IF NOT EXISTS idx_comm_att_event_date ON committee_attendances(exam_event_id, attendance_date);
CREATE INDEX IF NOT EXISTS idx_sup_att_event_date ON supervisor_attendances(exam_event_id, attendance_date);
