-- Migration 007: Add letterhead_image_path to school_settings
ALTER TABLE school_settings ADD COLUMN letterhead_image_path VARCHAR(255);
