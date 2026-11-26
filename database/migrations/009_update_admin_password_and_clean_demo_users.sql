-- Migration 009: Ensure admin has updated password and remove demo users
UPDATE users 
SET username = 'admin',
    password = '$2y$10$RfuZ2lrITfa7tKtj8G2bee/ignAZ9jPd3rpaiwm0HrMPY0WzkHTOe',
    full_name = 'Administrator Sistem',
    email = 'admin@ruangujian.sch.id',
    role = 'super_admin',
    is_active = 1,
    deleted_at = NULL,
    updated_at = datetime('now')
WHERE id = 1 OR LOWER(username) = 'admin';

DELETE FROM users WHERE LOWER(username) IN ('ketua', 'sekretaris', 'operator', 'pengawas1', 'penguji1', 'kepsek');
