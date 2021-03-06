<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_manage_edit.php")==FALSE) {
	//Acess denied
	print "<div class='alert alert-danger'>" ;
		print __("You do not have access to this action.") ;
	print "</div>" ;
}
else {
	//Get action with highest precendence
	$highestAction=getHighestGroupedAction($guid, $_GET["q"], $connection2) ;
	if ($highestAction==FALSE) {
		print "<div class='alert alert-danger'>" ;
		print __("The highest grouped action cannot be determined.") ;
		print "</div>" ;
	}
	else {
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        $updateReturn = isset($_GET["updateReturn"]) ? $_GET["updateReturn"] : '';

        $page->breadcrumbs
            ->add(__('Manage Messages'), 'messenger_manage.php', ['search' => $search])
            ->add(__('Edit Message'));

		$updateReturnMessage="" ;
		$class="error" ;
		if (!($updateReturn=="")) {
			if ($updateReturn=="fail0") {
				$updateReturnMessage=__("Your request failed because you do not have access to this action.") ;
			}
			else if ($updateReturn=="fail1") {
				$updateReturnMessage=__("Your request failed because your inputs were invalid.") ;
			}
			else if ($updateReturn=="fail2") {
				$updateReturnMessage=__("Your request failed due to a database error.") ;
			}
			else if ($updateReturn=="fail3") {
				$updateReturnMessage=__("Your request failed because your inputs were invalid.") ;
			}
			else if ($updateReturn=="fail4") {
				$updateReturnMessage=__("Your request failed because your inputs were invalid.") ;
			}
			else if ($updateReturn=="success0") {
				$updateReturnMessage=__("Your request was completed successfully.") ;
				$class="alert alert-success" ;
			}
			print "<div class='$class'>" ;
				print $updateReturnMessage;
			print "</div>" ;
		}

		//Check if school year specified
		$pupilsightMessengerID=$_GET["pupilsightMessengerID"] ;
		if ($pupilsightMessengerID=="") {
			print "<div class='alert alert-danger'>" ;
				print __("You have not specified one or more required parameters.") ;
			print "</div>" ;
		}
		else {
			try {
				if ($highestAction=="Manage Messages_all") {
					$data=array("pupilsightMessengerID"=>$pupilsightMessengerID);
					$sql="SELECT pupilsightMessenger.*, title, surname, preferredName FROM pupilsightMessenger JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightMessengerID=:pupilsightMessengerID" ;
				}
				else {
					$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "pupilsightPersonID"=>$_SESSION[$guid]["pupilsightPersonID"]);
					$sql="SELECT pupilsightMessenger.*, title, surname, preferredName FROM pupilsightMessenger JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightMessengerID=:pupilsightMessengerID AND pupilsightMessenger.pupilsightPersonID=:pupilsightPersonID" ;
				}
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) {
				print "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
			}


			if ($result->rowCount()!=1) {
				print "<div class='alert alert-danger'>" ;
					print __("The specified record cannot be found.") ;
				print "</div>" ;
			}
			else {
				//Let's go!
				$values=$result->fetch() ;
				echo '<div class="alert alert-warning">';
					echo '<b><u>'.__('Note').'</u></b>: '.__('Changes made here do not apply to emails and SMS messages (which have already been sent), but only to message wall messages.');
				echo '</div>';

				$form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/messenger_manage_editProcess.php');

				$form->addHiddenValue('address', $_SESSION[$guid]['address']);
				$form->addHiddenValue('pupilsightMessengerID', $values['pupilsightMessengerID']);

				$form->addRow()->addHeading(__('Delivery Mode'));
				//Delivery by email
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_byEmail")) {
					$row = $form->addRow();
						$row->addLabel('email', __('Email'))->description(__('Deliver this message to user\'s primary email account?'));
						if ($values["email"]=="Y") {
							$row->addContent("
							<i class='mdi mdi-check mdi-24px' title='" . __('Sent by email.') . "'></i>")->addClass('right');
						}
						else {
							$row->addContent("
							<i class='mdi mdi-close mdi-24px' title='" . __('Not sent by email.') . "'></i>")->addClass('right') ;
						}
				}

				//Delivery by message wall
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_byMessageWall")) {
					$row = $form->addRow();
						$row->addLabel('messageWall', __('Message Wall'))->description(__('Place this message on user\'s message wall?'));
						$row->addYesNoRadio('messageWall')->checked('N')->required();

					$form->toggleVisibilityByClass('messageWall')->onRadio('messageWall')->when('Y');

					$row = $form->addRow()->addClass('messageWall');
				        $row->addLabel('date1', __('Publication Dates'))->description(__('Select up to three individual dates.'));
						$col = $row->addColumn('date1')->addClass('stacked');
						$col->addDate('date1')->setValue(dateConvertBack($guid, $values['messageWall_date1']))->required();
						$col->addDate('date2')->setValue(dateConvertBack($guid, $values['messageWall_date2']));
						$col->addDate('date3')->setValue(dateConvertBack($guid, $values['messageWall_date3']));
				}

				//Delivery by SMS
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_bySMS")) {
					$smsUsername=getSettingByScope( $connection2, "Messenger", "smsUsername" ) ;
					$smsPassword=getSettingByScope( $connection2, "Messenger", "smsPassword" ) ;
					$smsURL=getSettingByScope( $connection2, "Messenger", "smsURL" ) ;
					$smsURLCredit=getSettingByScope( $connection2, "Messenger", "smsURLCredit" ) ;
					if ($smsUsername == "" OR $smsPassword == "" OR $smsURL == "") {
						$form->addRow()->addAlert(sprintf(__('SMS NOT CONFIGURED. Please contact %1$s for help.'), "<a href='mailto:" . $_SESSION[$guid]["organisationAdministratorEmail"] . "'>" . $_SESSION[$guid]["organisationAdministratorName"] . "</a>"), 'error');
					}
					else {
						$row = $form->addRow();
							$row->addLabel('sms', __('SMS'))->description(__('Deliver this message to user\'s mobile phone?'));
							if ($values["sms"]=="Y") {
								$row->addContent("
								<i class='mdi mdi-check mdi-24px' title='" . __('Sent by email.') . "'></i>")->addClass('right');
							}
							else {
								$row->addContent("
								<i class='mdi mdi-close mdi-24px' title='" . __('Not sent by email.') . "'></i>")->addClass('right') ;
							}
					}
				}

				//MESSAGE DETAILS
				$form->addRow()->addHeading(__('Message Details'));

				$row = $form->addRow();
					$row->addLabel('subject', __('Subject'));
					$row->addTextField('subject')->maxLength(60)->required();

				$row = $form->addRow();
			        $col = $row->addColumn('body');
			        $col->addLabel('body', __('Body'));
			        $col->addEditor('body', $guid)->required()->setRows(20)->showMedia(true);

				//READ RECEIPTS
				if (!isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_readReceipts")) {
					$form->addHiddenValue('emailReceipt', 'N');
				}
				else {
					$form->addRow()->addHeading(__('Email Read Receipts'));

					$row = $form->addRow();
						$row->addLabel('emailReceipt', __('Enable Read Receipts'))->description(__('Each email recipient will receive a personalised confirmation link.'));
						if ($values["emailReceipt"]=="Y") {
							$row->addContent("
							<i class='mdi mdi-check mdi-24px' title='" . __('Sent by email.') . "'></i>")->addClass('right');
							$row = $form->addRow()->addClass('emailReceipt');
							$row->addLabel('emailReceiptText', __('Link Text'))->description(__('Confirmation link text to display to recipient.'));
							$row->addTextArea('emailReceiptText')->setRows(3)->required()->setValue(__('By clicking on this link I confirm that I have read, and agree to, the text contained within this email, and give consent for my child to participate.'))->readonly();
						}
						else {
							//$row->addContent("<i class='mdi mdi-close px-2 cross-times' title='" . __('Not sent by email.') . "'></i>")->addClass('right') ;
							$row->addYesNoRadio('emailReceipt')->checked('N')->required();

							$form->toggleVisibilityByClass('emailReceipt')->onRadio('emailReceipt')->when('Y');

							$row = $form->addRow()->addClass('emailReceipt');
							$row->addLabel('emailReceiptText', __('Link Text'))->description(__('Confirmation link text to display to recipient.'));
							$row->addTextArea('emailReceiptText')->setRows(4)->required()->setValue(__('By clicking on this link I confirm that I have read, and agree to, the text contained within this email, and give consent for my child to participate.'));
						}

					
				}

				//TARGETS
				$form->addRow()->addHeading(__('Targets'));
				$roleCategory = getRoleCategory($_SESSION[$guid]["pupilsightRoleIDCurrent"], $connection2);

				//Get existing TARGETS
				try {
					$dataTarget=array("pupilsightMessengerID"=>$pupilsightMessengerID, "is_display" => "Y");
					$sqlTarget="SELECT * FROM pupilsightMessengerTarget WHERE pupilsightMessengerID=:pupilsightMessengerID AND is_display=:is_display ORDER BY type" ;
					$resultTarget=$connection2->prepare($sqlTarget);
					$resultTarget->execute($dataTarget);
				}
				catch(PDOException $e) {
					echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>" ;
				}

				$targets = $resultTarget->fetchAll();

				//Role
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_role")) {
					$selected = array_reduce($targets, function($group, $item) {
						if ($item['type'] == 'Role') $group[] = $item['id'];
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';
					$row = $form->addRow();
						$row->addLabel('role', __('Role'))->description(__('Users of a certain type.'));
						$row->addYesNoRadio('role')->checked($checked)->required();

					$form->toggleVisibilityByClass('role')->onRadio('role')->when('Y');

					$data = array();
					$sql = 'SELECT pupilsightRoleID AS value, CONCAT(name," (",category,")") AS name FROM pupilsightRole ORDER BY name';
					$row = $form->addRow()->addClass('role hiddenReveal');
						$row->addLabel('roles[]', __('Select Roles'));
						$row->addSelect('roles[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->placeholder()->selected($selected);

					//Role Category
					$selected = array_reduce($targets, function($group, $item) {
						if ($item['type'] == 'Role Category') $group[] = $item['id'];
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';
					$row = $form->addRow();
						$row->addLabel('roleCategory', __('Role Category'))->description(__('Users of a certain type.'));
						$row->addYesNoRadio('roleCategory')->checked($checked)->required();

					$form->toggleVisibilityByClass('roleCategory')->onRadio('roleCategory')->when('Y');

					$data = array();
					$sql = 'SELECT DISTINCT category AS value, category AS name FROM pupilsightRole ORDER BY category';
					$row = $form->addRow()->addClass('roleCategory hiddenReveal');
						$row->addLabel('roleCategories[]', __('Select Role Categories'));
						$row->addSelect('roleCategories[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(4)->required()->placeholder()->selected($selected);
				} else if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_postQuickWall.php")) {
                    // Handle the edge case where a user can post a Quick Wall message but doesn't have access to the Role target
                    $row = $form->addRow();
						$row->addLabel('roleCategoryLabel', __('Role Category'))->description(__('Users of a certain type.'));
                        $row->addYesNoRadio('roleCategoryLabel')->checked('Y')->readonly()->disabled();

                    $form->addHiddenValue('role', 'N');
                    $form->addHiddenValue('roleCategory', 'Y');
                    foreach ($targets as $target) {
                        if ($target['type'] == 'Role Category') {
                            $form->addHiddenValue('roleCategories[]', $target['id']);
                        }
                    }
                }

				//Year group
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_yearGroups_any")) {
					$selectedByRole = array('staff' => 'N', 'students' => 'N', 'parents' => 'N',);
					$selected = array_reduce($targets, function($group, $item) use (&$selectedByRole) {
						if ($item['type'] == 'Year Group') {
							$group[] = $item['id'];
							$selectedByRole['staff'] = $item['staff'];
							$selectedByRole['students'] = $item['students'];
							$selectedByRole['parents'] = $item['parents'];
						}
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';
					$row = $form->addRow();
						$row->addLabel('yearGroup', __('Class'))->description(__('Students in year; staff by tutors and courses taught.'));
						$row->addYesNoRadio('yearGroup')->checked($checked)->required();

					$form->toggleVisibilityByClass('yearGroup')->onRadio('yearGroup')->when('Y');

					$data = array();
					$sql = 'SELECT pupilsightYearGroupID AS value, name FROM pupilsightYearGroup ORDER BY sequenceNumber';
					$row = $form->addRow()->addClass('yearGroup hiddenReveal');
						$row->addLabel('yearGroups[]', __('Select Year Groups'));
						$row->addSelect('yearGroups[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->placeholder()->selected($selected);

					$row = $form->addRow()->addClass('yearGroup hiddenReveal');
						$row->addLabel('yearGroupsStaff', __('Include Staff?'));
						$row->addYesNo('yearGroupsStaff')->selected($selectedByRole['staff']);

					$row = $form->addRow()->addClass('yearGroup hiddenReveal');
						$row->addLabel('yearGroupsStudents', __('Include Students?'));
							$row->addYesNo('yearGroupsStudents')->selected($selectedByRole['students']);

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_yearGroups_parents")) {
						$row = $form->addRow()->addClass('yearGroup hiddenReveal');
							$row->addLabel('yearGroupsParents', __('Include Parents?'));
							$row->addYesNo('yearGroupsParents')->selected($selectedByRole['parents']);
					}
				}

				//Roll group
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_rollGroups_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_rollGroups_any")) {
					$selectedByRole = array('staff' => 'N', 'students' => 'N', 'parents' => 'N',);
					$selected = array_reduce($targets, function($group, $item) use (&$selectedByRole) {
						if ($item['type'] == 'Roll Group') {
							$group[] = $item['pupilsightMappingID'];
							$selectedByRole['staff'] = $item['staff'];
							$selectedByRole['students'] = $item['students'];
							$selectedByRole['parents'] = $item['parents'];
						}
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';
					$row = $form->addRow();
						$row->addLabel('rollGroup', __('Sections'))->description(__('Tutees and tutors.'));
						$row->addYesNoRadio('rollGroup')->checked($checked)->required();

					$form->toggleVisibilityByClass('rollGroup')->onRadio('rollGroup')->when('Y');

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_rollGroups_any")) {
						$data=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"]);
						//$sql="SELECT pupilsightRollGroupID AS value, name FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name" ;

						// $sql = "SELECT a.pupilsightRollGroupID as value, d.name as sectionname, a.pupilsightProgramID, b.pupilsightYearGroupID , b.name as name1, CONCAT(b.name,' ',d.name) as name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightProgram AS c ON a.pupilsightProgramID = c.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS d ON a.pupilsightRollGroupID = d.pupilsightRollGroupID WHERE a.pupilsightSchoolYearID =:pupilsightSchoolYearID ORDER BY a.pupilsightMappingID";

						$sql = "SELECT a.pupilsightMappingID as value, d.name as sectionname, a.pupilsightProgramID, b.pupilsightYearGroupID , b.name as name1, CONCAT(b.name,' ',d.name) as name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightProgram AS c ON a.pupilsightProgramID = c.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS d ON a.pupilsightRollGroupID = d.pupilsightRollGroupID WHERE a.pupilsightSchoolYearID =:pupilsightSchoolYearID ORDER BY a.pupilsightMappingID";
					}
					else {
						if ($roleCategory == "Staff") {
							$data=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightPersonID1"=>$_SESSION[$guid]["pupilsightPersonID"], "pupilsightPersonID2"=>$_SESSION[$guid]["pupilsightPersonID"], "pupilsightPersonID3"=>$_SESSION[$guid]["pupilsightPersonID"], "pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"]);
							$sql="SELECT pupilsightRollGroupID AS value, name FROM pupilsightRollGroup WHERE (pupilsightPersonIDTutor=:pupilsightPersonID1 OR pupilsightPersonIDTutor2=:pupilsightPersonID2 OR pupilsightPersonIDTutor3=:pupilsightPersonID3) AND pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name" ;
						}
						else if ($roleCategory == "Student") {
							$data=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightPersonID"=>$_SESSION[$guid]["pupilsightPersonID"], );
							$sql="SELECT pupilsightRollGroupID AS value, name FROM pupilsightRollGroup JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name" ;
						}
					}
					$row = $form->addRow()->addClass('rollGroup hiddenReveal');
						$row->addLabel('rollGroups[]', __('Select Sections'));
						$row->addSelect('rollGroups[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->placeholder()->selected($selected);

					$row = $form->addRow()->addClass('rollGroup hiddenReveal');
						$row->addLabel('rollGroupsStaff', __('Include Staff?'));
						$row->addYesNo('rollGroupsStaff')->selected($selectedByRole['staff']);

					$row = $form->addRow()->addClass('rollGroup hiddenReveal');
						$row->addLabel('rollGroupsStudents', __('Include Students?'));
						$row->addYesNo('rollGroupsStudents')->selected($selectedByRole['students']);

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_rollGroups_parents")) {
						$row = $form->addRow()->addClass('rollGroup hiddenReveal');
							$row->addLabel('rollGroupsParents', __('Include Parents?'));
							$row->addYesNo('rollGroupsParents')->selected($selectedByRole['parents']);
					}
				}

				// Course
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_any")) {
					$selectedByRole = array('staff' => 'N', 'students' => 'N', 'parents' => 'N',);
					$selected = array_reduce($targets, function($group, $item) use (&$selectedByRole) {
						if ($item['type'] == 'Course') {
							$group[] = $item['id'];
							$selectedByRole['staff'] = $item['staff'];
							$selectedByRole['students'] = $item['students'];
							$selectedByRole['parents'] = $item['parents'];
						}
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';
					$row = $form->addRow();
						$row->addLabel('course', __('Course'))->description(__('Members of a course of study.'));
						$row->addYesNoRadio('course')->checked($checked)->required();

					$form->toggleVisibilityByClass('course')->onRadio('course')->when('Y');

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_any")) {
						$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
						$sql = "SELECT pupilsightCourseID as value, nameShort as name FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";
					} else {
						$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
						$sql = "SELECT pupilsightCourse.pupilsightCourseID as value, pupilsightCourse.nameShort as name 
                                FROM pupilsightCourse 
                                JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) 
                                JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                                WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND NOT role LIKE '%- Left' GROUP BY pupilsightCourse.pupilsightCourseID ORDER BY name";
					}

					$row = $form->addRow()->addClass('course hiddenReveal');
						$row->addLabel('courses[]', __('Select Courses'));
						$row->addSelect('courses[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->selected($selected);

					$row = $form->addRow()->addClass('course hiddenReveal');
						$row->addLabel('coursesStaff', __('Include Staff?'));
						$row->addYesNo('coursesStaff')->selected($selectedByRole['staff']);

					$row = $form->addRow()->addClass('course hiddenReveal');
						$row->addLabel('coursesStudents', __('Include Students?'));
						$row->addYesNo('coursesStudents')->selected($selectedByRole['students']);

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_parents")) {
						$row = $form->addRow()->addClass('course hiddenReveal');
							$row->addLabel('coursesParents', __('Include Parents?'));
							$row->addYesNo('coursesParents')->selected($selectedByRole['parents']);
					}
				}

				// Class
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_any")) {
					$selectedByRole = array('staff' => 'N', 'students' => 'N', 'parents' => 'N',);
					$selected = array_reduce($targets, function($group, $item) use (&$selectedByRole) {
						if ($item['type'] == 'Class') {
							$group[] = $item['id'];
							$selectedByRole['staff'] = $item['staff'];
							$selectedByRole['students'] = $item['students'];
							$selectedByRole['parents'] = $item['parents'];
						}
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';
					$row = $form->addRow();
						$row->addLabel('class', __('Class'))->description(__('Members of a class within a course.'));
						$row->addYesNoRadio('class')->checked($checked)->required();

					$form->toggleVisibilityByClass('class')->onRadio('class')->when('Y');

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_any")) {
						$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
						$sql = "SELECT pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as name FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";
					} else {
						$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
						$sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as name 
                            FROM pupilsightCourse 
                            JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) 
                            JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) 
                            WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND NOT role LIKE '%- Left' ORDER BY pupilsightCourseClass.name";
					}

					$row = $form->addRow()->addClass('class hiddenReveal');
						$row->addLabel('classes[]', __('Select Classes'));
						$row->addSelect('classes[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->selected($selected);

					$row = $form->addRow()->addClass('class hiddenReveal');
						$row->addLabel('classesStaff', __('Include Staff?'));
						$row->addYesNo('classesStaff')->selected($selectedByRole['staff']);

					$row = $form->addRow()->addClass('class hiddenReveal');
						$row->addLabel('classesStudents', __('Include Students?'));
						$row->addYesNo('classesStudents')->selected($selectedByRole['students']);

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_parents")) {
						$row = $form->addRow()->addClass('class hiddenReveal');
							$row->addLabel('classesParents', __('Include Parents?'));
							$row->addYesNo('classesParents')->selected($selectedByRole['parents']);
					}
				}

				//Activities
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_activities_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_activities_any")) {
					$selectedByRole = array('staff' => 'N', 'students' => 'N', 'parents' => 'N',);
					$selected = array_reduce($targets, function($group, $item) use (&$selectedByRole) {
						if ($item['type'] == 'Activity') {
							$group[] = $item['id'];
							$selectedByRole['staff'] = $item['staff'];
							$selectedByRole['students'] = $item['students'];
							$selectedByRole['parents'] = $item['parents'];
						}
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';
					$row = $form->addRow();
						$row->addLabel('activity', __('Activity'))->description(__('Members of an activity.'));
						$row->addYesNoRadio('activity')->checked($checked)->required();

					$form->toggleVisibilityByClass('activity')->onRadio('activity')->when('Y');

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_activities_any")) {
						$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
						$sql = "SELECT pupilsightActivityID as value, name FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name";
					} else {
						$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
						if ($roleCategory == "Staff") {
							$sql = "SELECT pupilsightActivity.pupilsightActivityID as value, name FROM pupilsightActivity JOIN pupilsightActivityStaff ON (pupilsightActivityStaff.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name";
						} else if ($roleCategory == "Student") {
							$sql = "SELECT pupilsightActivity.pupilsightActivityID as value, name FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND status='Accepted' AND active='Y' ORDER BY name";
						}
					}
					$row = $form->addRow()->addClass('activity hiddenReveal');
						$row->addLabel('activities[]', __('Select Activities'));
						$row->addSelect('activities[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->selected($selected);

					$row = $form->addRow()->addClass('activity hiddenReveal');
						$row->addLabel('activitiesStaff', __('Include Staff?'));
						$row->addYesNo('activitiesStaff')->selected($selectedByRole['staff']);

					$row = $form->addRow()->addClass('activity hiddenReveal');
						$row->addLabel('activitiesStudents', __('Include Students?'));
						$row->addYesNo('activitiesStudents')->selected($selectedByRole['students']);

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_activities_parents")) {
						$row = $form->addRow()->addClass('activity hiddenReveal');
							$row->addLabel('activitiesParents', __('Include Parents?'));
							$row->addYesNo('activitiesParents')->selected($selectedByRole['parents']);
					}
				}

				// Applicants
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_applicants")) {
					$selected = array_reduce($targets, function($group, $item) {
						if ($item['type'] == 'Applicants') $group[] = $item['id'];
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';
					$row = $form->addRow();
						$row->addLabel('applicants', __('Applicants'))->description(__('Applicants from a given year.'))->description(__('Does not apply to the message wall.'));
						$row->addYesNoRadio('applicants')->checked($checked)->required();

					$form->toggleVisibilityByClass('applicants')->onRadio('applicants')->when('Y');

					$sql = "SELECT pupilsightSchoolYearID as value, name FROM pupilsightSchoolYear ORDER BY sequenceNumber DESC";
					$row = $form->addRow()->addClass('applicants hiddenReveal');
						$row->addLabel('applicantList[]', __('Select Years'));
						$row->addSelect('applicantList[]')->fromQuery($pdo, $sql)->selectMultiple()->setSize(6)->required()->selected($selected);
				}

				// Houses
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_houses_all") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_houses_my")) {
					$selected = array_reduce($targets, function($group, $item) {
						if ($item['type'] == 'Houses') $group[] = $item['id'];
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';
					$row = $form->addRow();
						$row->addLabel('houses', __('Houses'))->description(__('Houses for competitions, etc.'));
						$row->addYesNoRadio('houses')->checked($checked)->required();

					$form->toggleVisibilityByClass('houses')->onRadio('houses')->when('Y');

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_houses_all")) {
						$data = array();
						$sql = "SELECT pupilsightHouseID as value, name FROM pupilsightHouse ORDER BY name";
					} else if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_houses_my")) {
						$dataSelect = array('pupilsightPersonID'=>$_SESSION[$guid]['pupilsightPersonID']);
						$sql = "SELECT pupilsightHouse.pupilsightHouseID as value, name FROM pupilsightHouse JOIN pupilsightPerson ON (pupilsightHouse.pupilsightHouseID=pupilsightPerson.pupilsightHouseID) WHERE pupilsightPersonID=:pupilsightPersonID ORDER BY name";
					}
					$row = $form->addRow()->addClass('houses hiddenReveal');
						$row->addLabel('houseList[]', __('Select Houses'));
						$row->addSelect('houseList[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->selected($selected);
				}

				// Transport
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_transport_any")) {
					$selectedByRole = array('staff' => 'Y', 'students' => 'Y', 'parents' => 'N');
					$selected = array_reduce($targets, function($group, $item) use (&$selectedByRole) {
						if ($item['type'] == 'Transport') {
							$group[] = $item['id'];
							$selectedByRole['staff'] = $item['staff'];
							$selectedByRole['students'] = $item['students'];
							$selectedByRole['parents'] = $item['parents'];
						}
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';
					$row = $form->addRow();
						$row->addLabel('transport', __('Transport'))->description(__('Applies to all staff and students who have transport set.'));
						$row->addYesNoRadio('transport')->checked($checked)->required();

					$form->toggleVisibilityByClass('transport')->onRadio('transport')->when('Y');

					$sql = "SELECT DISTINCT transport as value, transport as name FROM pupilsightPerson WHERE status='Full' AND NOT transport='' ORDER BY transport";
					$row = $form->addRow()->addClass('transport hiddenReveal');
						$row->addLabel('transports[]', __('Select Transport'));
						$row->addSelect('transports[]')->fromQuery($pdo, $sql)->selectMultiple()->setSize(6)->required()->selected($selected);

					$row = $form->addRow()->addClass('transport hiddenReveal');
						$row->addLabel('transportStaff', __('Include Staff?'));
						$row->addYesNo('transportStaff')->selected($selectedByRole['staff']);

					$row = $form->addRow()->addClass('transport hiddenReveal');
						$row->addLabel('transportStudents', __('Include Students?'));
						$row->addYesNo('transportStudents')->selected($selectedByRole['students']);

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_transport_parents")) {
						$row = $form->addRow()->addClass('transport hiddenReveal');
							$row->addLabel('transportParents', __('Include Parents?'));
							$row->addYesNo('transportParents')->selected($selectedByRole['parents']);
					}
				}

				// Attendance Status / Absentees
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_attendance")) {
					$selectedByRole = array('students' => 'N', 'parents' => 'N',);
					$selected = array_reduce($targets, function($group, $item) use (&$selectedByRole) {
						if ($item['type'] == 'Attendance') {
							$group[] = $item['id'];
							$selectedByRole['students'] = $item['students'];
							$selectedByRole['parents'] = $item['parents'];
						}
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';$row = $form->addRow();
						$row->addLabel('attendance', __('Attendance Status'))->description(__('Students matching the given attendance status.'));
						$row->addYesNoRadio('attendance')->checked($checked)->required();

					$form->toggleVisibilityByClass('attendance')->onRadio('attendance')->when('Y');

					$sql = "SELECT name, pupilsightRoleIDAll FROM pupilsightAttendanceCode WHERE active = 'Y' ORDER BY direction DESC, sequenceNumber ASC, name";
					$result = $pdo->executeQuery(array(), $sql);

					// Filter the attendance codes by allowed roles (if any)
					$currentRole = $_SESSION[$guid]['pupilsightRoleIDCurrent'];
					$attendanceCodes = ($result->rowCount() > 0)? $result->fetchAll() : array();
					$attendanceCodes = array_filter($attendanceCodes, function($item) use ($currentRole) {
						if (!empty($item['pupilsightRoleIDAll'])) {
							$rolesAllowed = array_map('trim', explode(',', $item['pupilsightRoleIDAll']));
							return in_array($currentRole, $rolesAllowed);
						} else {
							return true;
						}
					});
					$attendanceCodes = array_column($attendanceCodes, 'name');

					$row = $form->addRow()->addClass('attendance hiddenReveal');
						$row->addLabel('attendanceStatus[]', __('Select Attendance Status'));
						$row->addSelect('attendanceStatus[]')->fromArray($attendanceCodes)->selectMultiple()->setSize(6)->required()->selected($selected);

					$row = $form->addRow()->addClass('attendance hiddenReveal');
						$row->addLabel('attendanceStudents', __('Include Students?'));
						$row->addYesNo('attendanceStudents')->selected($selectedByRole['students']);

					$row = $form->addRow()->addClass('attendance hiddenReveal');
						$row->addLabel('attendanceParents', __('Include Parents?'));
						$row->addYesNo('attendanceParents')->selected($selectedByRole['parents']);
				}

				// Group
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_any")) {
					$selectedByRole = array('staff' => 'N', 'students' => 'N', 'parents' => 'N',);
					$selected = array_reduce($targets, function($group, $item) use (&$selectedByRole) {
						if ($item['type'] == 'Group') {
							$group[] = $item['id'];
							$selectedByRole['staff'] = $item['staff'];
							$selectedByRole['students'] = $item['students'];
							$selectedByRole['parents'] = $item['parents'];
						}
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';
					$row = $form->addRow();
						$row->addLabel('group', __('Group'))->description(__('Members of a Messenger module group.'));
						$row->addYesNoRadio('group')->checked($checked)->required();

					$form->toggleVisibilityByClass('messageGroup')->onRadio('group')->when('Y');

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_any")) {
						$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
						$sql = "SELECT pupilsightGroup.pupilsightGroupID as value, pupilsightGroup.name FROM pupilsightGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";
					} else {
						$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
						$sql = "(SELECT pupilsightGroup.pupilsightGroupID as value, pupilsightGroup.name FROM pupilsightGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonIDOwner=:pupilsightPersonID ORDER BY name)
							UNION
							(SELECT pupilsightGroup.pupilsightGroupID as value, pupilsightGroup.name FROM pupilsightGroup JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightPersonID=:pupilsightPersonID2)
							ORDER BY name
							";
					}

					$row = $form->addRow()->addClass('messageGroup hiddenReveal');
						$row->addLabel('groups[]', __('Select Groups'));
						$row->addSelect('groups[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->selected($selected);;

					$row = $form->addRow()->addClass('messageGroup hiddenReveal');
						$row->addLabel('groupsStaff', __('Include Staff?'));
						$row->addYesNo('groupsStaff')->selected($selectedByRole['staff']);

					$row = $form->addRow()->addClass('messageGroup hiddenReveal');
						$row->addLabel('groupsStudents', __('Include Students?'));
						$row->addYesNo('groupsStudents')->selected($selectedByRole['students']);

					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_parents")) {
						$row = $form->addRow()->addClass('messageGroup hiddenReveal');
							$row->addLabel('groupsParents', __('Include Parents?'))->description('Parents who are members, and parents of student members.');
							$row->addYesNo('groupsParents')->selected($selectedByRole['parents']);
					}
				}

				//Advance Search for students

				$row = $form->addRow();
				$row->addLabel('AdvanceSearch', __('Advance Search'))->description(__('Advance Students Search.'));
				$row->addYesNoRadio('advancestudents')->checked('N')->required();

				$form->toggleVisibilityByClass('messageAdvStudents')->onRadio('advancestudents')->when('Y');
				$check_role = 'SELECT role.name FROM pupilsightPerson as p LEFT JOIN pupilsightRole as role ON p.pupilsightRoleIDAll = role.pupilsightRoleID 
			WHERE p.pupilsightPersonID ="' . $_SESSION[$guid]['pupilsightPersonID'] . '" AND role.name="Administrator"';
				$check_role = $connection2->query($check_role);
				$role = $check_role->fetch();
				$sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
				$resultval = $connection2->query($sqlq);
				$rowdata = $resultval->fetchAll();
				$academic = array();
				$ayear = '';
				if (!empty($rowdata)) {
					$ayear = $rowdata[0]['name'];
					foreach ($rowdata as $dt) {
						$academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
					}
				}
				$academic1 = array('' => 'Select Year');
				$academic = $academic1 + $academic;

				$row = $form->addRow()->addClass('messageAdvStudents');;
				$col = $row->addLabel('Class wise students', __('Class wise students'));

				$col = $row->addColumn()->setClass('newdes noEdit');
				$col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
				$col->addSelect('pupilsightSchoolYearID')->fromArray($academic);

				$col = $row->addColumn()->setClass('newdes');
				$col->addLabel('pupilsightProgramID', __('Program'));
				$col->addSelect('pupilsightProgramID')->setId("pupilsightProgramIDA")->placeholder();

				$col = $row->addColumn()->setClass('newdes');
				$col->addLabel('pupilsightYearGroupID', __('Class'))->addClass('dte');
				$col->addSelect('pupilsightYearGroupID')->setId("pupilsightYearGroupIDA")->addClass("pupilsightRollGroupIDP1 staticwidth")->selectMultiple();

				$col = $row->addColumn()->setClass('newdes');
				$col->addLabel('pupilsightPersonID', __('Students'))->addClass('dte');
				if (isset($pupilsightPersonID)) {
					$col->addSelect('pupilsightPersonID')->selected($pupilsightPersonID)->selectMultiple()->addClass("staticwidth");
				} else {
					$col->addSelect('pupilsightPersonID')->selectMultiple()->addClass("staticwidth");
				}

				// Individuals
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_individuals")) {
					$selected = array_reduce($targets, function($group, $item) {
						if ($item['type'] == 'Individuals') $group[] = $item['id'];
						return $group;
					}, array());
					$checked = !empty($selected)? 'Y' : 'N';$row = $form->addRow();
						$row->addLabel('individuals', __('Individuals'))->description(__('Individuals from the whole school.'));
						$row->addYesNoRadio('individuals')->checked($checked)->required();

					$form->toggleVisibilityByClass('individuals')->onRadio('individuals')->when('Y');

					$sql = "SELECT pupilsightRole.category, pupilsightPersonID, preferredName, surname, username FROM pupilsightPerson JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary) WHERE status='Full' ORDER BY surname, preferredName";
					$result = $pdo->executeQuery(array(), $sql);

					// Build a set of individuals by ID => formatted name
					$individuals = ($result->rowCount() > 0)? $result->fetchAll() : array();
					$individuals = array_reduce($individuals, function($group, $item){
						$group[$item['pupilsightPersonID']] = formatName("", $item['preferredName'], $item['surname'], 'Student', true) . ' ('.$item['username'].', '.__($item['category']).')';
						return $group;
					}, array());

					$row = $form->addRow()->addClass('individuals hiddenReveal');
						$row->addLabel('individualList[]', __('Select Individuals'));
						$row->addSelect('individualList[]')->setId('individualList')->fromArray($individuals)->selectMultiple()->setSize(6)->required()->selected($selected);
				}

				$form->loadAllValuesFrom($values);

				$row = $form->addRow();
					$row->addFooter();
					$row->addSubmit();

				echo $form->getOutput();
			}
		}
	}
}
?>

<style>
	#individualList {
		width: 500px;
	}

	.staticwidth {
		width: 220px;
	}
</style>

<script type='text/javascript'>
	$(document).ready(function() {
		$("#bodyedButtonPreview").trigger('click');
		
		$('#individualList').select2({
			minimumInputLength: 3
		});
	});
</script>

<script>
	$(document).on('change', '#pupilsightSchoolYearID', function() {
		var val = $(this).val();
		var type = "getPrograms1";
		if (val != "") {
			$.ajax({
				url: 'ajax_data.php',
				type: 'post',
				data: {
					val: val,
					type: type
				},
				async: true,
				success: function(response) {
					$("#pupilsightProgramIDA").html();
					$("#pupilsightProgramIDA").html(response);

				}
			});
		}
	});
</script>
<script type="text/javascript">
	$(document).on('change', '#pupilsightProgramIDA', function() {
		var val = $(this).val();
		var type = "getClass";
		if (val != "") {
			$.ajax({
				url: 'ajax_data.php',
				type: 'post',
				data: {
					val: val,
					type: type
				},
				async: true,
				success: function(response) {
					$("#pupilsightYearGroupIDA").html();
					$("#pupilsightYearGroupIDA").html(response);

				}
			});
		}
	});
</script>
<script type="text/javascript">
	$(document).on('change', '#pupilsightYearGroupIDA', function() {
		//var id = $("#pupilsightRollGroupID").val();
		var yid = $('#pupilsightSchoolYearID').val();
		var pid = $('#pupilsightProgramIDA').val();
		var cid = $(this).val();
		if (cid != "") {
			var type = 'getStudentClassAndSection';
			$.ajax({
				url: 'ajax_data.php',
				type: 'post',
				data: {
					val: cid,
					type: type,
					yid: yid,
					pid: pid
				},
				async: true,
				success: function(response) {
					$("#pupilsightPersonID").html('');
					$("#pupilsightPersonID").append(response);
				}
			});
		}
	});
</script>
<script type='text/javascript'>
	$(document).ready(function() {
		$('#pupilsightYearGroupIDA').select2();
	});
</script>
<script type='text/javascript'>
	$(document).ready(function() {
		$('#pupilsightPersonID').select2();
	});
</script>