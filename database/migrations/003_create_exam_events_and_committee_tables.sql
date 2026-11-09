-- Migration 003: Exam Events, Rooms, Participants Snapshot, and Committee Tables

CREATE TABLE IF NOT EXISTS exam_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS exam_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(200) NOT NULL,
    academic_year VARCHAR(50) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    exam_type VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    implementation_type VARCHAR(50) DEFAULT 'Kertas / Manual',
    status VARCHAR(50) DEFAULT 'draft', -- draft, aktif, berjalan, selesai, diarsipkan
    is_active INTEGER DEFAULT 0,
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS exam_participants (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    participant_number VARCHAR(50) NOT NULL,
    student_name_snapshot VARCHAR(150) NOT NULL,
    nis_snapshot VARCHAR(50),
    nisn_snapshot VARCHAR(50),
    class_name_snapshot VARCHAR(100) NOT NULL,
    grade_snapshot VARCHAR(20) NOT NULL,
    gender_snapshot VARCHAR(10),
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT,
    UNIQUE(exam_event_id, student_id),
    UNIQUE(exam_event_id, participant_number)
);

CREATE TABLE IF NOT EXISTS exam_rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    capacity INTEGER NOT NULL DEFAULT 20,
    rows_count INTEGER DEFAULT 5,
    cols_count INTEGER DEFAULT 4,
    notes TEXT,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    UNIQUE(exam_event_id, room_number)
);

CREATE TABLE IF NOT EXISTS exam_room_assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    exam_room_id INTEGER NOT NULL,
    participant_id INTEGER NOT NULL,
    seat_number INTEGER NOT NULL,
    row_num INTEGER DEFAULT 1,
    col_num INTEGER DEFAULT 1,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_room_id) REFERENCES exam_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES exam_participants(id) ON DELETE CASCADE,
    UNIQUE(exam_event_id, participant_id),
    UNIQUE(exam_room_id, seat_number)
);

CREATE TABLE IF NOT EXISTS committees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS committee_members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    committee_id INTEGER NOT NULL,
    teacher_id INTEGER,
    custom_name VARCHAR(150),
    custom_nip VARCHAR(50),
    role_title VARCHAR(100) NOT NULL, -- Pelindung, Penanggung Jawab, Ketua, Wakil Ketua, Sekretaris, Bendahara, Seksi, Anggota
    section_name VARCHAR(100), -- Seksi Penggandaan, Seksi Konsumsi, Seksi Distribusi, dll.
    phone VARCHAR(50),
    order_index INTEGER DEFAULT 0,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (committee_id) REFERENCES committees(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS committee_documents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    doc_type VARCHAR(50) NOT NULL, -- sk_panitia, program_kerja, surat_tugas, dll.
    doc_number VARCHAR(100),
    doc_date DATE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    file_size INTEGER,
    version INTEGER DEFAULT 1,
    status VARCHAR(50) DEFAULT 'Draft',
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS committee_document_versions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    document_id INTEGER NOT NULL,
    version INTEGER NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_by INTEGER,
    notes TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (document_id) REFERENCES committee_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS letter_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    template_body TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS committee_letters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    letter_type VARCHAR(50) NOT NULL, -- undangan_rapat, tugas_kisi_kisi, tugas_soal, tugas_pengawas, tugas_penguji, tugas_panitia
    letter_number VARCHAR(100) NOT NULL,
    letter_date DATE NOT NULL,
    subject VARCHAR(200) NOT NULL,
    meeting_date DATE,
    meeting_time VARCHAR(50),
    meeting_place VARCHAR(150),
    meeting_agenda TEXT,
    signer_name VARCHAR(150),
    signer_nip VARCHAR(50),
    signer_title VARCHAR(100),
    target_recipients TEXT,
    content TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS committee_work_programs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    academic_year VARCHAR(50),
    foreword TEXT,
    background TEXT,
    legal_basis TEXT,
    objectives TEXT,
    theme VARCHAR(255),
    target_description TEXT,
    facilities_plan TEXT,
    supervision_plan TEXT,
    documentation_plan TEXT,
    closing_notes TEXT,
    approval_date DATE,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS committee_work_program_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    work_program_id INTEGER NOT NULL,
    item_order INTEGER DEFAULT 0,
    activity_name VARCHAR(255) NOT NULL,
    start_date DATE,
    end_date DATE,
    pic_name VARCHAR(150),
    notes TEXT,
    FOREIGN KEY (work_program_id) REFERENCES committee_work_programs(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS committee_budgets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    work_program_id INTEGER NOT NULL,
    item_order INTEGER DEFAULT 0,
    description VARCHAR(255) NOT NULL,
    volume REAL NOT NULL DEFAULT 1,
    unit VARCHAR(50) NOT NULL,
    unit_price REAL NOT NULL DEFAULT 0,
    total_amount REAL NOT NULL DEFAULT 0,
    category VARCHAR(100) DEFAULT 'Operasional',
    FOREIGN KEY (work_program_id) REFERENCES committee_work_programs(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_participants_event ON exam_participants(exam_event_id);
CREATE INDEX IF NOT EXISTS idx_room_assignments_room ON exam_room_assignments(exam_room_id);
CREATE INDEX IF NOT EXISTS idx_committee_members_committee ON committee_members(committee_id);
