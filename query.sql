ALTER TABLE `sanskar`.`users`   
	CHANGE `father_name` `father_name` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `mother_name` `mother_name` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `father_volunteering` `father_volunteering` TINYINT(1) DEFAULT 0 NULL,
	CHANGE `mother_volunteering` `mother_volunteering` TINYINT(1) DEFAULT 0 NULL,
	CHANGE `is_hsnc_member` `is_hsnc_member` TINYINT(1) DEFAULT 0 NULL;
