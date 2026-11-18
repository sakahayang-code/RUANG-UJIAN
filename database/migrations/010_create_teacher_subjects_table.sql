CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    teacher_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE(teacher_id, subject_id)
);

CREATE INDEX IF NOT EXISTS idx_teacher_subjects_teacher ON teacher_subjects(teacher_id);
CREATE INDEX IF NOT EXISTS idx_teacher_subjects_subject ON teacher_subjects(subject_id);

-- Seed initial teacher-subject assignments from existing document submissions
INSERT OR IGNORE INTO teacher_subjects (teacher_id, subject_id, created_at)
SELECT DISTINCT teacher_id, subject_id, datetime('now')
FROM exam_document_submissions
WHERE teacher_id IS NOT NULL AND subject_id IS NOT NULL;
