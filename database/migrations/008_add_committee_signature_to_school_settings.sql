-- Migration 008: Add committee_signature_path to school_settings
ALTER TABLE school_settings ADD COLUMN committee_signature_path VARCHAR(255);
