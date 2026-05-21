CREATE TABLE IF NOT EXISTS classroom_course_lesson_assignments (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    lesson_id       INT UNSIGNED NOT NULL,
    instructions    TEXT NULL,
    due_date        DATE NULL,
    submission_type ENUM('file','text','link','mixed') NOT NULL DEFAULT 'file',
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ccla_lesson FOREIGN KEY (lesson_id) REFERENCES classroom_course_lessons (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS classroom_course_lesson_files (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    lesson_id  INT UNSIGNED NOT NULL,
    type       ENUM('material','assignment','certificate') NOT NULL,
    url        VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cclf_lesson FOREIGN KEY (lesson_id) REFERENCES classroom_course_lessons (id) ON DELETE CASCADE
);

-- Columns to drop from classroom_course_lessons once data is migrated:
-- ALTER TABLE classroom_course_lessons
--     DROP COLUMN material_url,
--     DROP COLUMN assignment_url,
--     DROP COLUMN certificate_url,
--     DROP COLUMN assignment_instructions,
--     DROP COLUMN assignment_due_date,
--     DROP COLUMN assignment_submission_type;
