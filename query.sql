ALTER TABLE `sanskar`.`users`   
	CHANGE `father_name` `father_name` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `mother_name` `mother_name` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `father_volunteering` `father_volunteering` TINYINT(1) DEFAULT 0 NULL,
	CHANGE `mother_volunteering` `mother_volunteering` TINYINT(1) DEFAULT 0 NULL,
	CHANGE `is_hsnc_member` `is_hsnc_member` TINYINT(1) DEFAULT 0 NULL;


ALTER TABLE `sanskar`.`students`   
	ADD COLUMN `is_new_student` TINYINT(1) DEFAULT 1 NULL AFTER `school_name`;

ALTER TABLE `sanskar`.`weekly_updates`   
	ADD COLUMN `name` VARCHAR(255) NULL AFTER `media`;



---------------------------------------------
-- 2024-06-17 12:00:00
-- Querys
---------------------------------------------


ALTER TABLE `students` CHANGE `is_new_student` `is_new_student` TINYINT(1) NULL DEFAULT NULL;
-- ea-php82 /usr/local/bin/composer require maatwebsite/excel