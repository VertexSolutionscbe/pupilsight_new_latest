<?php
include_once 'cms/w2f/adminLib.php';
$adminlib = new adminlib();
//echo $_REQUEST['camp_status'];

if($_REQUEST['camp_status']=="active")
{
$activecamp_cnt = $adminlib->activecampaign_cnt();
echo $activecamp_cnt['active_camp_cnt'];
}
else if($_REQUEST['camp_status']=="all")
{

 $overallcamp_cnt = $adminlib->overallcampaign_cnt();
echo $overallcamp_cnt['overall_camp_cnt']; 
}
else
{
	echo "";
}

?>