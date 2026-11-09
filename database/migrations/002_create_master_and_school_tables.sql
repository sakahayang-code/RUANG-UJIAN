-- Migration 002: School Identity and Master Data Tables

CREATE TABLE IF NOT EXISTS school_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(200) NOT NULL,
    npsn VARCHAR(50),
    address TEXT,
    village VARCHAR(100),
    district VARCHAR(100),
    regency VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    phone VARCHAR(50),
    email VARCHAR(100),
    website VARCHAR(150),
    logo_path VARCHAR(255),
    principal_name VARCHAR(150),
    principal_nip VARCHAR(50),
    academic_year VARCHAR(50),
    committee_head VARCHAR(150),
    committee_secretary VARCHAR(150),
    letterhead_text TEXT,
    letter_number_format VARCHAR(100) DEFAULT '{no}/PAN-UJIAN/{bln_romawi}/{thn}',
    date_format VARCHAR(50) DEFAULT 'd F Y',
    signature_city VARCHAR(100) DEFAULT 'Jakarta',
    created_at DATETIME NOT NULL,
    updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS academic_years (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL,
    semester VARCHAR(20) DEFAULT 'Ganjil',
    is_active INTEGER DEFAULT 0,
    created_at DATETIME NOT NULL,
    UNIQUE(name, semester)
);

CREATE TABLE IF NOT EXISTS teachers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(150) NOT NULL,
    nip VARCHAR(50),
    nuptk VARCHAR(50),
    position VARCHAR(100) DEFAULT 'Guru Mapel',
    phone VARCHAR(50),
    status VARCHAR(50) DEFAULT 'Aktif',
    created_at DATETIME NOT NULL,
    updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    grade VARCHAR(20) NOT NULL,
    homeroom_teacher_id INTEGER,
    student_count INTEGER DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (homeroom_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nis VARCHAR(50),
    nisn VARCHAR(50),
    name VARCHAR(150) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    grade VARCHAR(20) NOT NULL,
    class_id INTEGER NOT NULL,
    status VARCHAR(50) DEFAULT 'Aktif',
    photo_path VARCHAR(255),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    deleted_at DATETIME,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    grade VARCHAR(50) NOT NULL,
    exam_type VARCHAR(50) NOT NULL DEFAULT 'written', -- written, practical, both
    created_at DATETIME NOT NULL,
    updated_at DATETIME
);

CREATE INDEX IF NOT EXISTS idx_students_class_id ON students(class_id);
CREATE INDEX IF NOT EXISTS idx_students_nis ON students(nis);
CREATE INDEX IF NOT EXISTS idx_students_nisn ON students(nisn);
CREATE INDEX IF NOT EXISTS idx_classes_grade ON classes(grade);
CREATE INDEX IF NOT EXISTS idx_subjects_code ON subjects(code);
