{* file to handle db changes in 5.11.alpha1 during upgrade *}
ALTER TABLE civicrm_action_log CHANGE COLUMN reference_date reference_date datetime;
