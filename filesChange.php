Files Changes

database.php - line no :  10
DBQuery.php  - line no :  10
home.php 	 - line no :  30
config.php 	 - db name and password changes


DB Changes

UPDATE `pupilsightsetting` SET `value` = 'http://localhost/newcode/pupilsight_new' WHERE `pupilsightsetting`.`pupilsightSettingID` = 00001;
UPDATE `pupilsightsetting` SET `value` = 'F:\\suhail\\Office\\xampp\\htdocs\\newcode\\pupilsight_new' WHERE `pupilsightsetting`.`pupilsightSettingID` = 00009;
UPDATE `wp_options` SET `option_value` = 'http://localhost/newcode/pupilsight_new/wp' WHERE `wp_options`.`option_id` = 1;
