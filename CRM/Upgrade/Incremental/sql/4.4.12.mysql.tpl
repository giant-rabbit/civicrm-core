{* file to handle db changes in 4.4.12 during upgrade *}

ALTER TABLE `civicrm_report_instance`
  ADD COLUMN `owner_id` int(10) unsigned DEFAULT NULL COMMENT 'Report Owned By. FK to civicrm_contact id.' AFTER `is_reserved`,
  ADD CONSTRAINT `FK_civicrm_report_instance_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL;
