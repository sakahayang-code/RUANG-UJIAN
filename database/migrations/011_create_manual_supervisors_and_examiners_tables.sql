-- Migration 011: Create master_supervisors and master_examiners tables for manual management
CREATE TABLE IF NOT EXISTS master_supervisors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER,
    name VARCHAR(150) NOT NULL,
    nip VARCHAR(50),
    school_origin VARCHAR(150) DEFAULT 'Internal Sekolah',
    subject VARCHAR(100),
    phone VARCHAR(50),
    room_assignment VARCHAR(50),
    role VARCHAR(50) DEFAULT 'Pengawas Ruang',
    status VARCHAR(50) DEFAULT 'Aktif',
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_master_supervisors_event ON master_supervisors(exam_event_id);

CREATE TABLE IF NOT EXISTS master_examiners (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER,
    name VARCHAR(150) NOT NULL,
    nip VARCHAR(50),
    subject_name VARCHAR(100) NOT NULL,
    grade VARCHAR(50) DEFAULT 'Semua Tingkat',
    school_origin VARCHAR(150) DEFAULT 'Internal Sekolah',
    examiner_role VARCHAR(100) DEFAULT 'Penguji Utama',
    phone VARCHAR(50),
    location VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Aktif',
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_master_examiners_event ON master_examiners(exam_event_id);
