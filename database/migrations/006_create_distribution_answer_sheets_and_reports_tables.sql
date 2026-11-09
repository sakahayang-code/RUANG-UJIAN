-- Migration 006: Paper Distribution, Answer Sheet Receipts & Packing, Incidents, and Final Reports

CREATE TABLE IF NOT EXISTS exam_paper_handover_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    handover_type VARCHAR(50) NOT NULL, -- pengambilan, pengembalian
    exam_date DATE NOT NULL,
    session VARCHAR(20) NOT NULL,
    room_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    grade VARCHAR(20) NOT NULL,
    participant_count INTEGER NOT NULL DEFAULT 0,
    required_count INTEGER NOT NULL DEFAULT 0,
    taken_or_returned_count INTEGER NOT NULL DEFAULT 0,
    discrepancy INTEGER DEFAULT 0,
    handover_time VARCHAR(10) NOT NULL,
    officer_name VARCHAR(150) NOT NULL,
    supervisor_name VARCHAR(150) NOT NULL,
    condition_status VARCHAR(100) DEFAULT 'Baik / Lengkap', -- Baik / Lengkap, Rusak, Kurang, dll.
    notes TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES exam_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS answer_sheet_receipts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    receipt_date DATE NOT NULL,
    session VARCHAR(20) NOT NULL,
    room_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    present_count INTEGER NOT NULL DEFAULT 0,
    received_count INTEGER NOT NULL DEFAULT 0,
    unused_count INTEGER NOT NULL DEFAULT 0,
    damaged_count INTEGER NOT NULL DEFAULT 0,
    discrepancy INTEGER NOT NULL DEFAULT 0, -- present_count - received_count
    handover_officer VARCHAR(150),
    receiving_officer VARCHAR(150),
    status VARCHAR(50) DEFAULT 'belum_diterima', -- belum_diterima, diterima_sebagian, diterima_lengkap, ada_selisih, perlu_pemeriksaan
    is_packed INTEGER DEFAULT 0,
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES exam_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS answer_sheet_packing_batches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    batch_code VARCHAR(100) NOT NULL UNIQUE,
    packing_date DATE NOT NULL,
    packing_mode VARCHAR(50) NOT NULL, -- per_kelas, per_mapel, per_kelas_mapel
    class_name VARCHAR(100),
    subject_id INTEGER,
    total_sheets INTEGER NOT NULL DEFAULT 0,
    envelope_count INTEGER NOT NULL DEFAULT 1,
    packing_officer VARCHAR(150),
    receiving_officer VARCHAR(150),
    status VARCHAR(50) DEFAULT 'Tersimpan', -- Tersimpan, Terverifikasi, Diserahkan
    notes TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS answer_sheet_packing_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    batch_id INTEGER NOT NULL,
    receipt_id INTEGER NOT NULL,
    sheet_count INTEGER NOT NULL,
    notes TEXT,
    FOREIGN KEY (batch_id) REFERENCES answer_sheet_packing_batches(id) ON DELETE CASCADE,
    FOREIGN KEY (receipt_id) REFERENCES answer_sheet_receipts(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS answer_sheet_envelope_labels (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    batch_id INTEGER NOT NULL,
    envelope_number INTEGER NOT NULL DEFAULT 1,
    total_envelopes INTEGER NOT NULL DEFAULT 1,
    label_data_json TEXT NOT NULL,
    printed_at DATETIME,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (batch_id) REFERENCES answer_sheet_packing_batches(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS answer_sheet_handover_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    batch_id INTEGER NOT NULL,
    handover_date DATE NOT NULL,
    sender_officer VARCHAR(150) NOT NULL,
    receiver_officer VARCHAR(150) NOT NULL,
    total_envelopes INTEGER NOT NULL DEFAULT 1,
    total_sheets INTEGER NOT NULL DEFAULT 0,
    notes TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES answer_sheet_packing_batches(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS exam_incidents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    category VARCHAR(50) NOT NULL, -- tertulis, praktik, kepanitiaan, naskah, lembar_jawaban, lainnya
    incident_date DATE NOT NULL,
    incident_time VARCHAR(10),
    location VARCHAR(150),
    description TEXT NOT NULL,
    follow_up TEXT,
    reporter_name VARCHAR(150),
    status VARCHAR(50) DEFAULT 'Selesai',
    created_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS exam_reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL UNIQUE,
    report_title VARCHAR(255) NOT NULL,
    academic_year VARCHAR(50),
    foreword TEXT,
    background TEXT,
    legal_basis TEXT,
    objectives TEXT,
    committee_summary TEXT,
    preparation_summary TEXT,
    written_summary TEXT,
    practical_summary TEXT,
    attendance_summary TEXT,
    distribution_summary TEXT,
    answer_sheet_summary TEXT,
    obstacles_solutions TEXT,
    evaluation TEXT,
    closing TEXT,
    approval_date DATE,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS exam_report_attachments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    created_at DATETIME NOT NULL,
    FOREIGN KEY (report_id) REFERENCES exam_reports(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS print_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_event_id INTEGER NOT NULL,
    doc_type VARCHAR(100) NOT NULL,
    item_reference VARCHAR(150),
    printed_by INTEGER,
    printed_at DATETIME NOT NULL,
    FOREIGN KEY (exam_event_id) REFERENCES exam_events(id) ON DELETE CASCADE,
    FOREIGN KEY (printed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_handover_event ON exam_paper_handover_records(exam_event_id);
CREATE INDEX IF NOT EXISTS idx_receipts_event ON answer_sheet_receipts(exam_event_id);
CREATE INDEX IF NOT EXISTS idx_packing_batch_event ON answer_sheet_packing_batches(exam_event_id);
