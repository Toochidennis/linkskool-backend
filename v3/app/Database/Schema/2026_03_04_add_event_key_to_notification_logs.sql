ALTER TABLE `email_logs`
    ADD COLUMN `event_key` varchar(191) DEFAULT NULL AFTER `subject`,
    ADD INDEX `idx_email_logs_event_key_recipient` (`event_key`, `recipient`(100));

ALTER TABLE `notifications`
    ADD COLUMN `event_key` varchar(191) DEFAULT NULL AFTER `body`,
    ADD INDEX `idx_notifications_event_key_token` (`event_key`, `recipient_token`(100));
