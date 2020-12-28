<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

$page->breadcrumbs->add(__('Manage Messages'));

if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_manage.php")==FALSE) {
	//Acess denied
	print "<div class='alert alert-danger'>" ;
		print __("You do not have access to this action.") ;
	print "</div>" ;
}
else {
	//Get action with highest precendence
	$highestAction=getHighestGroupedAction($guid, $_GET["q"], $connection2);
	if ($highestAction==FALSE) {
		print "<div class='alert alert-danger'>" ;
		print __("The highest grouped action cannot be determined.") ;
		print "</div>" ;
	}
	else {
		//Proceed!
		if (isset($_GET["deleteReturn"])) { $deleteReturn=$_GET["deleteReturn"] ; } else { $deleteReturn="" ; }
		$deleteReturnMessage="" ;
		$class="error" ;
		if (!($deleteReturn=="")) {
			if ($deleteReturn=="success0") {
				$deleteReturnMessage=__("Your request was completed successfully.") ;
				$class="alert alert-success" ;
			}
			print "<div class='$class'>" ;
				print $deleteReturnMessage;
			print "</div>" ;
		}

		print "<h2>" ;
		print __("Search") ;
		print "</h2>" ;

		$search = isset($_GET['search'])? $_GET['search'] : '';

		$form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
		$form->setClass('noIntBorder fullWidth');

		$form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/messenger_manage.php');

		$row = $form->addRow();
			$row->addLabel('search', __('Search In'))->description(__('Subject, body.'));
			$row->addTextField('search')->setValue($search);

		$row = $form->addRow()
				->addClass('right_align');
			$row->addSearchSubmit($pupilsight->session, __('Clear Search'));

		echo $form->getOutput();

		print "<h2>" ;
		print __("Messages") ;
		print "</h2>" ;

		//Set pagination variable
		$page=1 ; if (isset($_GET["page"])) { $page=$_GET["page"] ; }
		if ((!is_numeric($page)) OR $page<1) {
			$page=1 ;
		}

		try {
			if ($highestAction=="Manage Messages_all") {
				if ($search=="") {
					$data=array();
					$sql="SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category FROM pupilsightMessenger JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) ORDER BY timestamp DESC" ;
				}
				else {
					$data=array("search1"=>"%$search%", "search2"=>"%$search%");
					$sql="SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category FROM pupilsightMessenger JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE (subject LIKE :search1 OR body LIKE :search2) ORDER BY timestamp DESC" ;
				}
			}
			else {
				if ($search=="") {
					$data=array("pupilsightPersonID"=>$_SESSION[$guid]["pupilsightPersonID"]);
					$sql="SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category FROM pupilsightMessenger JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessenger.pupilsightPersonID=:pupilsightPersonID ORDER BY timestamp DESC" ;
				}
				else {
					$data=array("pupilsightPersonID"=>$_SESSION[$guid]["pupilsightPersonID"], "search1"=>"%$search%", "search2"=>"%$search%");
					$sql="SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category FROM pupilsightMessenger JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessenger.pupilsightPersonID=:pupilsightPersonID AND (subject LIKE :search1 OR body LIKE :search2) ORDER BY timestamp DESC" ;
				}
			}
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) {
			print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
		}

		$sqlPage=$sql ." LIMIT " . $_SESSION[$guid]["pagination"] . " OFFSET " . (($page-1)*$_SESSION[$guid]["pagination"]) ;

		if (isActionAccessible($guid, $connection2,"/modules/Messenger/messenger_post.php")==TRUE OR isActionAccessible($guid, $connection2,"/modules/Messenger/messenger_postQuickWall.php")==TRUE) {
			print "<div class='linkTop'>" ;
				if (isActionAccessible($guid, $connection2,"/modules/Messenger/messenger_post.php")==TRUE) {
					print "<a style='width: auto!important;' class = 'fw-btn-fill btn-gradient-yellow addbtncss' href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/messenger_post.php'>" .  __('Compose Message') . "<i style='margin-left: 5px' class='mdi mdi-plus-circle-outline' title='".__('Add')."' ></i></a>" ;
				}
				if (isActionAccessible($guid, $connection2,"/modules/Messenger/messenger_postQuickWall.php")==TRUE) {
					if (isActionAccessible($guid, $connection2,"/modules/Messenger/messenger_post.php")==TRUE) {
						print "  " ;
					}
					print "<a style='width: auto!important;' class = 'fw-btn-fill btn-gradient-yellow addbtncss' href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/messenger_postQuickWall.php'>" .  __('Compose Quick Wall Message') . "<i style='margin-left: 5px' class='mdi mdi-plus-circle-outline' title='".__('Add')."' ></i></a>" ;
				}
			print "</div>" ;
		}

		if ($result->rowCount()<1) {
			print "<div class='alert alert-danger'>" ;
			print __("There are no records to display.") ;
			print "</div>" ;
		}
		else {
			if ($result->rowCount()>$_SESSION[$guid]["pagination"]) {
				printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]["pagination"], "top", "&search=$search") ;
			}

			print "<table cellspacing='0' class='table display data-table text-nowrap' style='width: 100%'>" ;
			 print '<thead>';
				print "<tr class='head'>" ;
				print "<th>" ;
						print __("SL NO") ;
					print "</th>" ;
					print "<th>" ;
						print __("Subject") ;
					print "</th>" ;
					print "<th style='width: 100px'>" ;
						print __('Date Sent'). "<br/>" ;
						print "<span style='font-style: italic; font-size: 85%'>" . __('Dates Published') . "</span>" ;
					print "</th>" ;
					print "<th>" ;
						print __("Author") ;
					print "</th>" ;
					print "<th>" ;
						print __("Recipients") ;
					print "</th>" ;
					print "<th>" ;
						print __("Email") ;
					print "</th>" ;
					print "<th>" ;
						print __("Wall") ;
					print "</th>" ;
					print "<th>" ;
						print __("SMS") ;
					print "</th>" ;
					print "<th style='width: 120px'>" ;
						print __("Actions") ;
					print "</th>" ;
				print "</tr>" ;
				 print '</thead>';

				$count=0;
				$rowNum="odd" ;
				try {
					$resultPage=$connection2->prepare($sqlPage);
					$resultPage->execute($data);
				}
				catch(PDOException $e) {
					print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
				}
				$i = 1;
				while ($row=$resultPage->fetch()) {
					if ($count%2==0) {
						$rowNum="even" ;
					}
					else {
						$rowNum="odd" ;
					}


					//COLOR ROW BY STATUS!
					print "<tr class=$rowNum>" ;
					print "<td>" ;
					print "<b>" . $i++ . "</b><br/>" ;
				print "</td>" ;
						print "<td>" ;
							print "<b>" . $row["subject"] . "</b><br/>" ;
						print "</td>" ;
						print "<td>" ;
						echo date('d/m/Y H:s',strtotime($row["timestamp"]))."<br/>";
							//print dateConvertBack($guid, substr($row["timestamp"],0,10)) . "<br/>" ;
							if ($row["messageWall"]=="Y") {
								print "<span style='font-style: italic; font-size: 85%'>" ;
									if ($row["messageWall_date1"]!="") {
										print dateConvertBack($guid, $row["messageWall_date1"]) . "<br/>" ;
									}
									if ($row["messageWall_date2"]!="") {
										print dateConvertBack($guid, $row["messageWall_date2"]) . "<br/>" ;
									}
									if ($row["messageWall_date3"]!="") {
										print dateConvertBack($guid, $row["messageWall_date3"]) . "<br/>" ;
									}
								print "</span>" ;
							}
						print "</td>" ;
						print "<td>" ;
							print formatName($row["title"], $row["preferredName"], $row["surname"], $row["category"]) ;
						print "</td>" ;
						print "<td>" ;
							try {
								$dataTargets=array("pupilsightMessengerID"=>$row["pupilsightMessengerID"]);
								$sqlTargets="SELECT type, id FROM pupilsightMessengerTarget WHERE pupilsightMessengerID=:pupilsightMessengerID ORDER BY type, id" ;
								$resultTargets=$connection2->prepare($sqlTargets);
								$resultTargets->execute($dataTargets);
							}
							catch(PDOException $e) {
								print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
							}
							$targets="" ;
							while ($rowTargets=$resultTargets->fetch()) {
								if ($rowTargets["type"]=="Activity") {
									try {
										$dataTarget=array("pupilsightActivityID"=>$rowTargets["id"]);
										$sqlTarget="SELECT name FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID" ;
										$resultTarget=$connection2->prepare($sqlTarget);
										$resultTarget->execute($dataTarget);
									}
									catch(PDOException $e) {
										print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
									}
									if ($resultTarget->rowCount()==1) {
										$rowTarget=$resultTarget->fetch() ;
										$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . $rowTarget["name"] . "<br/>" ;
									}
								}
								else if ($rowTargets["type"]=="Class") {
									try {
										$dataTarget=array("pupilsightCourseClassID"=>$rowTargets["id"]);
										$sqlTarget="SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID" ;
										$resultTarget=$connection2->prepare($sqlTarget);
										$resultTarget->execute($dataTarget);
									}
									catch(PDOException $e) {
										print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
									}
									if ($resultTarget->rowCount()==1) {
										$rowTarget=$resultTarget->fetch() ;
										$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . $rowTarget["course"] . "." . $rowTarget["class"] . "<br/>" ;
									}
								}
								else if ($rowTargets["type"]=="Course") {
									try {
										$dataTarget=array("pupilsightCourseID"=>$rowTargets["id"]);
										$sqlTarget="SELECT name FROM pupilsightCourse WHERE pupilsightCourseID=:pupilsightCourseID" ;
										$resultTarget=$connection2->prepare($sqlTarget);
										$resultTarget->execute($dataTarget);
									}
									catch(PDOException $e) {
										print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
									}
									if ($resultTarget->rowCount()==1) {
										$rowTarget=$resultTarget->fetch() ;
										$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . $rowTarget["name"] . "<br/>" ;
									}
								}
								else if ($rowTargets["type"]=="Role") {
									try {
										$dataTarget=array("pupilsightRoleID"=>$rowTargets["id"]);
										$sqlTarget="SELECT name FROM pupilsightRole WHERE pupilsightRoleID=:pupilsightRoleID" ;
										$resultTarget=$connection2->prepare($sqlTarget);
										$resultTarget->execute($dataTarget);
									}
									catch(PDOException $e) {
										print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
									}
									if ($resultTarget->rowCount()==1) {
										$rowTarget=$resultTarget->fetch() ;
										$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . __($rowTarget["name"]) . "<br/>" ;
									}
								}
								else if ($rowTargets["type"]=="Role Category") {
									$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . __($rowTargets["id"]) . "<br/>" ;
								}
								else if ($rowTargets["type"]=="Roll Group") {
									try {
										$dataTarget=array("pupilsightRollGroupID"=>$rowTargets["id"]);
										$sqlTarget="SELECT name FROM pupilsightRollGroup WHERE pupilsightRollGroupID=:pupilsightRollGroupID" ;
										$resultTarget=$connection2->prepare($sqlTarget);
										$resultTarget->execute($dataTarget);
									}
									catch(PDOException $e) {
										print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
									}
									if ($resultTarget->rowCount()==1) {
										$rowTarget=$resultTarget->fetch() ;
										$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . $rowTarget["name"] . "<br/>" ;
									}
								}
								else if ($rowTargets["type"]=="Year Group") {
									try {
										$dataTarget=array("pupilsightYearGroupID"=>$rowTargets["id"]);
										$sqlTarget="SELECT name FROM pupilsightYearGroup WHERE pupilsightYearGroupID=:pupilsightYearGroupID" ;
										$resultTarget=$connection2->prepare($sqlTarget);
										$resultTarget->execute($dataTarget);
									}
									catch(PDOException $e) {
										print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
									}
									if ($resultTarget->rowCount()==1) {
										$rowTarget=$resultTarget->fetch() ;
										$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . __($rowTarget["name"]) . "<br/>" ;
									}
								}
								else if ($rowTargets["type"]=="Applicants") {
									try {
										$dataTarget=array("pupilsightSchoolYearID"=>$rowTargets["id"]);
										$sqlTarget="SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID" ;
										$resultTarget=$connection2->prepare($sqlTarget);
										$resultTarget->execute($dataTarget);
									}
									catch(PDOException $e) {
										print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
									}
									if ($resultTarget->rowCount()==1) {
										$rowTarget=$resultTarget->fetch() ;
										$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . $rowTarget["name"] . "<br/>" ;
									}
								}
								else if ($rowTargets["type"]=="Houses") {
									try {
										$dataTarget=array("pupilsightHouseID"=>$rowTargets["id"]);
										$sqlTarget="SELECT name FROM pupilsightHouse WHERE pupilsightHouseID=:pupilsightHouseID" ;
										$resultTarget=$connection2->prepare($sqlTarget);
										$resultTarget->execute($dataTarget);
									}
									catch(PDOException $e) {
										print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
									}
									if ($resultTarget->rowCount()==1) {
										$rowTarget=$resultTarget->fetch() ;
										$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . $rowTarget["name"] . "<br/>" ;
									}
								}
                                else if ($rowTargets["type"]=="Transport") {
									$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . __($rowTargets["id"]) . "<br/>" ;
								}
                                else if ($rowTargets["type"]=="Attendance") {
                                  $targets.="<b>" . __($rowTargets["type"]) . "</b> - " . __($rowTargets["id"]) . "<br/>" ;
                                }
								else if ($rowTargets["type"]=="Individuals") {
									try {
										$dataTarget=array("pupilsightPersonID"=>$rowTargets["id"]);
										$sqlTarget="SELECT preferredName, surname FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID" ;
										$resultTarget=$connection2->prepare($sqlTarget);
										$resultTarget->execute($dataTarget);
									}
									catch(PDOException $e) {
										print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
									}
									if ($resultTarget->rowCount()==1) {
										$rowTarget=$resultTarget->fetch() ;
										$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . formatName("", $rowTarget["preferredName"], $rowTarget["surname"], "Student", true) . "<br/>" ;
									}
								}
								else if ($rowTargets["type"]=="Group") {
									try {
										$dataTarget=array("pupilsightGroupID"=>$rowTargets["id"]);
										$sqlTarget="SELECT name FROM pupilsightGroup WHERE pupilsightGroupID=:pupilsightGroupID" ;
										$resultTarget=$connection2->prepare($sqlTarget);
										$resultTarget->execute($dataTarget);
									}
									catch(PDOException $e) {
										print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
									}
									if ($resultTarget->rowCount()==1) {
										$rowTarget=$resultTarget->fetch() ;
										$targets.="<b>" . __($rowTargets["type"]) . "</b> - " . $rowTarget["name"] . "<br/>" ;
									}
								}
							}
							print $targets ;
						print "</td>" ;
						print "<td>" ;
							if ($row["email"]=="Y") {
								print "<i class='mdi mdi-check mdi-24px' title='" . __('Sent by email.') . "'></i>
								 " ;
							}
							else {
								print "<i class=' mdi mdi-close mdi-24px' title='" . __('Not sent by email.') . "'></i> " ;
							}
						print "</td>" ;
						print "<td>" ;
							if ($row["messageWall"]=="Y") {
								print "<i class='mdi mdi-check mdi-24px' title='" . __('Sent by message wall.') . "'></i>" ;
							}
							else {
								print "
								<i class=' mdi mdi-close mdi-24px' title='" . __('Not sent by message wall.') . "'></i>" ;
							}
						print "</td>" ;
						print "<td>" ;
							if ($row["sms"]=="Y") {
								print "
								<i class='mdi mdi-check mdi-24px' title='" . __('Sent by sms.') . "'></i>
								" ;
							}
							else {
								print "
								<i class='mdi mdi-close mdi-24px' title='" . __('Not sent by sms.') . "'></i>" ;
							}
						print "</td>" ;
						print "<td>" ;
							print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/messenger_manage_edit.php&pupilsightMessengerID=" . $row["pupilsightMessengerID"] . "&sidebar=true&search=$search'><i title='" . __('Edit') . "' class='mdi mdi-pencil-box-outline mdi-24px px-2'></i></a> " ;
							print "<a class='thickbox' href='" . $_SESSION[$guid]["absoluteURL"] . "/fullscreen.php?q=/modules/" . $_SESSION[$guid]["module"] . "/messenger_manage_delete.php&pupilsightMessengerID=" . $row["pupilsightMessengerID"] . "&sidebar=true&search=$search&width=650&height=135'><i title='" . __('Delete') . "' class='mdi mdi-trash-can-outline mdi-24px px-2'></i></a> " ;
							print "<script type='text/javascript'>" ;
								print "$(document).ready(function(){" ;
									print "\$(\".comment-$count\").hide();" ;
									print "\$(\".show_hide-$count\").fadeIn(1000);" ;
									print "\$(\".show_hide-$count\").click(function(){" ;
									print "\$(\".comment-$count\").fadeToggle(1000);" ;
									print "});" ;
								print "});" ;
							print "</script>" ;
							if (is_null($row["emailReceipt"]) == false) {
								print "<a href='".$_SESSION[$guid]["absoluteURL"]."/index.php?q=/modules/Messenger/messenger_manage_report.php&pupilsightMessengerID=".$row['pupilsightMessengerID']."&sidebar=true&search=$search'><i title='" . __('View Send Report') . "' class='fas fa-dot-circle px-2'></i></a>" ;
							}
							if ($row["smsReport"]!="" OR $row["emailReport"]!="") {
								print "<a title='" . __('Show Comment') . "' class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/page_down.png' alt='" . __('Show Comment') . "' onclick='return false;' /></a>" ;
							}
						print "</td>" ;
					print "</tr>" ;
					if ($row["smsReport"]!="" OR $row["emailReport"]!="") {
						print "<tr class='comment-$count' id='comment-$count'>" ;
							print "<td style='background-color: #fff' colspan=8>" ;
								if ($row["emailReport"]!="") {
									print "<b><u>Email Report</u></b><br/>" ;
									$emails=explode("),",$row["emailReport"]) ;
									$emails=array_unique($emails) ;
									$emails=msort($emails) ;
									foreach ($emails AS $email) {
										print $email . ")<br/>" ;
									}
								}
								if ($row["smsReport"]!="") {
									print "<b><u>SMS Report</u></b><br/>" ;
									$smss=explode("),",$row["smsReport"]) ;
									$smss=array_unique($smss) ;
									$smss=msort($smss) ;
									foreach ($smss AS $sms) {
										print $sms . ")<br/>" ;
									}
								}
							print "</td>" ;
						print "</tr>" ;

					}

					$count++ ;
				}
			print "</table>" ;

			if ($result->rowCount()>$_SESSION[$guid]["pagination"]) {
				printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]["pagination"], "bottom", "&search=$search") ;
			}
		}
	}
}
?>
