<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Contracts\Comms\SMS;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Helper\HelperGateway;
use Pupilsight\Services\Format;

use Pupilsight\Domain\Messenger\GroupGateway;
use Pupilsight\Tables\DataTable;
?>
<style>
	#individualList {
		width: 500px;
	}

	.staticwidth {
		width: 220px;
	}
</style>
<?php
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('New Message'));

if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php") == FALSE) {
	//Acess denied
	print "<div class='alert alert-danger'>";
	print __("You do not have access to this action.");
	print "</div>";
} else {
	if ($_SESSION[$guid]["email"] == "") {
		print "<div class='alert alert-danger'>";
		print __("You do not have a personal email address set in Pupilsight, and so cannot send out emails.");
		print "</div>";
	} else {
		//Proceed!
		if (isset($_GET["addReturn"])) {
			$addReturn = $_GET["addReturn"];
		} else {
			$addReturn = "";
		}
		$addReturnMessage = "";
		$class = "error";
		if (!($addReturn == "")) {
			if ($addReturn == "fail0") {
				$addReturnMessage = __("Your request failed because you do not have access to this action.");
			} else if ($addReturn == "fail2") {
				$addReturnMessage = __("Your request failed due to a database error.");
			} else if ($addReturn == "fail3") {
				$addReturnMessage = __("Your request failed because your inputs were invalid.");
			} else if ($addReturn == "fail4") {
				$addReturnMessage = __("Your request was completed successfully, but some or all messages could not be delivered.");
			} else if ($addReturn == "fail5") {
				$addReturnMessage = __("Your request failed due to an attachment error.");
			} else if ($addReturn == "success0") {
				$addReturnMessage = __("Your request was completed successfully: not all messages may arrive at their destination, but an attempt has been made to get them all out.");
				if (is_numeric($_GET["emailCount"])) {
					$addReturnMessage .= " " . sprintf(__('%1$s email(s) were dispatched.'), $_GET["emailCount"]);
				}
				if (is_numeric($_GET["smsCount"]) and is_numeric($_GET["smsBatchCount"])) {
					$addReturnMessage .= " " . sprintf(__('%1$s SMS(es) were dispatched in %2$s batch(es).'), $_GET["smsCount"], $_GET["smsBatchCount"]);
				}

				$class = "alert alert-success";
			}
			print "<div class='$class'>";
			print $addReturnMessage;
			print "</div>";
		}

		print "<div class='alert alert-warning'>";
		print sprintf(__('Each family in Pupilsight must have one parent who is contact priority 1, and who must be enabled to receive email and SMS messages from %1$s. As a result, when targetting parents, you can be fairly certain that messages should get through to each family.'), $_SESSION[$guid]["organisationNameShort"]);
		print "</div>";

		//start of sms counter
		$karixsmscountvalue = getsmsBalance($connection2, 'Messenger', 'Karix');
		$gupshupsmscountvalue = getsmsBalance($connection2, 'Messenger', 'Gupshup');
		$totalsms = gettotalsmsBalance($connection2);

		$totalsmsused = $gupshupsmscountvalue + $karixsmscountvalue;
		if ($totalsmsused > $totalsms) {
			$extrasmsused = $totalsmsused - $totalsms;
			echo "<span class='badge bg-red-lt'>Extra SMS USED TILL DATE $extrasmsused </span>";
		} else {
			$totalsmsbalance = $totalsms - $totalsmsused;
			echo "<span class='badge bg-green-lt'>Balance $totalsmsbalance </span>";
		}
		echo "<span class='badge bg-blue-lt' style='margin-left: 10px;'>TOTAL SMS USED TILL DATE $totalsmsused </span>";
		//end of sms counter


		$form = Form::create('action', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/messenger_postProcess.php');
		$setting = getSettingByScope($connection2, 'System', 'mailerSMTPUsername', true);
		$form->addHiddenValue('address', $_SESSION[$guid]['address']);
		$form->addHiddenValue('from', $setting['value']);
		//DELIVERY MODE
		$form->addRow()->addHeading(__('Delivery Mode'));
		//Delivery by email
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_byEmail")) {
			$row = $form->addRow();
			$row->addLabel('email', __('Email'))->description(__('Deliver this message to user\'s primary email account?'));
			$row->addYesNoRadio('email')->checked('N')->required()->setID('emailradio');

			$form->toggleVisibilityByClass('email')->onRadio('email')->when('Y');

			$from = array($_SESSION[$guid]["email"] => $_SESSION[$guid]["email"]);
			if ($_SESSION[$guid]["emailAlternate"] != "") {
				$from[$_SESSION[$guid]["emailAlternate"]] = $_SESSION[$guid]["emailAlternate"];
			}
			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_fromSchool") and $_SESSION[$guid]["organisationEmail"] != "") {
				$from[$_SESSION[$guid]["organisationEmail"]] = $_SESSION[$guid]["organisationEmail"];
			}
			/*$row = $form->addRow()->addClass('email');
				$row->addLabel('from', __('Email From'));
				$row->addSelect('from')->fromArray($from)->required();*/

			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_fromSchool")) {
				$row = $form->addRow()->addClass('email')->setID('replyemail');
				$row->addLabel('emailReplyTo', __('Reply To'));
				$row->addEmail('emailReplyTo');

				$row = $form->addRow()->addClass('email')->setID('bccemail');
				$row->addLabel('emailbcc', __('BCC'));
				$row->addEmail('emailbcc');
			}
		}

		//Delivery by message wall
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_byMessageWall")) {
			$row = $form->addRow();
			$row->addLabel('messageWall', __('Message Wall'))->description(__('Place this message on user\'s message wall?'));
			$row->addYesNoRadio('messageWall')->checked('N')->required()->setID('messageWallradio');

			$form->toggleVisibilityByClass('messageWall')->onRadio('messageWall')->when('Y');

			$row = $form->addRow()->addClass('messageWall')->setID('publicationdate');
			$row->addLabel('date1', __('Publication Dates'))->description(__('Select up to three individual dates.'));
			$col = $row->addColumn('date1')->addClass('stacked');
			$col->addDate('date1')->setValue(dateConvertBack($guid, date('Y-m-d')))->required();
			/*$col->addDate('date2');
			$col->addDate('date3');*/
		}

		//Delivery by SMS
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_bySMS")) {
			$smsGateway = getSettingByScope($connection2, 'Messenger', 'smsGateway');
			$smsUsername = getSettingByScope($connection2, 'Messenger', 'smsUsername');

			if (empty($smsGateway) || empty($smsUsername)) {
				$row = $form->addRow()->addClass('sms');
				$row->addLabel('sms', __('SMS'))->description(__('Deliver this message to user\'s mobile phone?'));
				/*$row->addAlert(sprintf(__('SMS NOT CONFIGURED. Please contact %1$s for help.'), "<a href='mailto:" . $_SESSION[$guid]["organisationAdministratorEmail"] . "'>" . $_SESSION[$guid]["organisationAdministratorName"] . "</a>"), 'message');*/
				$row->addAlert(sprintf(__('SMS NOT CONFIGURED.'), ""), 'message');
			} else {
				$row = $form->addRow();
				$row->addLabel('sms', __('SMS'))->description(__('Deliver this message to user\'s mobile phone?'));
				$row->addYesNoRadio('sms')->checked('N')->required()->setID('smsradio');

				$form->toggleVisibilityByClass('sms')->onRadio('sms')->when('Y');

				$row = $form->addRow()->addClass('sms')->setID('copysmshide');
				$row->addLabel('copysms', __('Copy SMS to'));
				$row->addTextField('copysms')->maxLength(12)->addClass('numfield')->placeholder('include country code');

				$smsAlert = __('SMS messages are sent to local and overseas numbers, but not all countries are supported. Please see the SMS Gateway provider\'s documentation or error log to see which countries are not supported. The subject does not get sent, and all HTML tags are removed. Each message, to each recipient, will incur a charge (dependent on your SMS gateway provider). Messages over 140 characters will get broken into smaller messages, and will cost more.');

				$sms = $container->get(SMS::class);

				if ($smsCredits = $sms->getCreditBalance()) {
					$smsAlert .= "<br/><br/><b>" . sprintf(__('Current balance: %1$s credit(s).'), $smsCredits) . "</u></b>";
				}

				$form->addRow()->addAlert($smsAlert, 'error')->addClass('sms');
			}
		}


		//MESSAGE DETAILS
		$form->addRow()->addHeading(__('Message Details'));
		$signature = getEmailSignature($guid, $connection2, $_SESSION[$guid]["pupilsightPersonID"]);

		$cannedResponse = isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", 'New Message_cannedResponse');
		if ($cannedResponse) {
			try {
				$dataSelect = array();
				$sqlSelect = "SELECT * FROM pupilsightMessengerCannedResponse ORDER BY subject";
				$resultSelect = $connection2->prepare($sqlSelect);
				$resultSelect->execute($dataSelect);
			} catch (PDOException $e) {
			}
			if ($resultSelect->rowCount() > 0) {
				$cannedResponses = $resultSelect->fetchAll();

				//Set up JS to deal with canned response selection
				print "<script type=\"text/javascript\">";
				print "$(document).ready(function(){";
				print "$(\"#cannedResponse\").change(function(){";
				print "if (confirm(\"Are you sure you want to insert these records.\")==1) {";
				print "if ($('#cannedResponse').val()==\"\" ) {";
				print "$('#subject').val('');";
				print "tinyMCE.execCommand('mceRemoveEditor', false, 'body') ;";
				print "tinyMCE.execCommand('mceRemoveEditor', false, 'body1') ;";
				print "$('#body').val('" . addSlashes($signature) . "');";
				print "$('#body1').val('" . addSlashes($signature) . "');";
				print "tinyMCE.execCommand('mceAddEditor', false, 'body') ;";
				print "tinyMCE.execCommand('mceAddEditor', false, 'body1') ;";
				print "}";
				foreach ($cannedResponses as $rowSelect) {
					print "if ($('#cannedResponse').val()==\"" . $rowSelect["pupilsightMessengerCannedResponseID"] . "\" ) {";
					print "$('#subject').val('" . htmlPrep($rowSelect["subject"]) . "');";
					print "tinyMCE.execCommand('mceRemoveEditor', false, 'body') ;";
					print "tinyMCE.execCommand('mceRemoveEditor', false, 'body1') ;";
					print "
											$.get('./modules/Messenger/messenger_post_ajax.php?pupilsightMessengerCannedResponseID=" . $rowSelect["pupilsightMessengerCannedResponseID"] . "', function(response) {
												 var result = response;
												$('#body').val(result + '" . addSlashes($signature) . "');
												$('#body1').val(result + '" . addSlashes($signature) . "');
												tinyMCE.execCommand('mceAddEditor', false, 'body') ;
												tinyMCE.execCommand('mceAddEditor', false, 'body1') ;
											});
										";
					print "}";
				}
				print "}";
				print "else {";
				print "$('#cannedResponse').val('')";
				print "}";
				print "});";
				print "});";
				print "</script>";

				$cans = array();
				foreach ($cannedResponses as $rowSelect) {
					$cans[$rowSelect["pupilsightMessengerCannedResponseID"]] = $rowSelect["subject"];
				}
				$row = $form->addRow();
				$row->addLabel('cannedResponse', __('Canned Response'));
				$row->addSelect('cannedResponse')->fromArray($cans)->placeholder();
			}
		}

		$form->toggleVisibilityByClass('email1')->onRadio('email')->when('Y');
		$row = $form->addRow()->addClass('email1')->setID('subjecthide');
		$row->addLabel('subject', __('Subject'));
		$row->addTextField('subject')->maxLength(200)->required();

		$display_fields = array();
		$data = array("categorystatus" => 1);
		$sql = "SELECT categoryname FROM messagewall_category_master WHERE status=:categorystatus";
		$result = $connection2->prepare($sql);
		$result->execute($data);
		if ($result->rowCount() > 0) {
			while ($rowEmail = $result->fetch()) {
				$display_fields[$rowEmail['categoryname']] = $rowEmail['categoryname'];
			}
		}
		$display_fields1 = array('' => 'Select Category');
		$display_fields = $display_fields1 + $display_fields;

		//$form->toggleVisibilityByClass('sms3')->onRadio('sms')->when('N');
		$row = $form->addRow()->addClass('sms3')->setID('categoryhide');
		$row->addLabel('category', __('Category'));
		$row->addSelect('category')->fromArray($display_fields)->selected($values['category']);

		//echo "<span type='text' id='count'>Character Count</span>";

		//$form->toggleVisibilityByClass('sms1')->onRadio('sms')->when('N');
		$row = $form->addRow()->addClass('sms1')->setID('bodyhide');
		$col = $row->addColumn('body');
		$col->addLabel('body', __('Body'));
		$col->addEditor('body', $guid)->required()->setRows(20)->showMedia(true)->setValue($signature);

		//$form->toggleVisibilityByClass('sms')->onRadio('sms')->when('Y');
		$row = $form->addRow()->addClass('sms')->setID('body1hide');
		$col = $row->addColumn('body');
		$col->addLabel('body', __('Body'));
		$col->addEditor('body1', $guid)->required()->setRows(20)->setValue($signature);

		$row = $form->addRow()->addAlert(__('For SMS message 160 Characters per message '))->setClass('right')
			->append('<span id="count" title="countchars"></span>');

		//READ RECEIPTS
		if (!isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_readReceipts")) {
			$form->addHiddenValue('emailReceipt', 'N');
		} else {
			$form->addRow()->addHeading(__('Email Read Receipts'));
			$form->addRow()->addContent(__('With read receipts enabled, the text [confirmLink] can be included in a message to add a unique, login-free read receipt link. If [confirmLink] is not included, the link will be appended to the end of the message.'));

			$row = $form->addRow();
			$row->addLabel('emailReceipt', __('Enable Read Receipts'))->description(__('Each email recipient will receive a personalised confirmation link.'));
			$row->addYesNoRadio('emailReceipt')->checked('N')->required();

			$form->toggleVisibilityByClass('emailReceipt')->onRadio('emailReceipt')->when('Y');

			$row = $form->addRow()->addClass('emailReceipt');
			$row->addLabel('emailReceiptText', __('Link Text'))->description(__('Confirmation link text to display to recipient.'));
			$row->addTextArea('emailReceiptText')->setRows(4)->required()->setValue(__('By clicking on this link I confirm that I have read, and agree to, the text contained within this email, and give consent for my child to participate.'));
		}


		//TARGETS
		$form->addRow()->addHeading(__('Targets'));
		$roleCategory = getRoleCategory($_SESSION[$guid]["pupilsightRoleIDCurrent"], $connection2);
		//Role
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_role")) {
			$row = $form->addRow();
			$row->addLabel('role', __('Role'))->description(__('Users of a certain type.'));
			$row->addYesNoRadio('role')->checked('N')->required();

			$form->toggleVisibilityByClass('role')->onRadio('role')->when('Y');

			$data = array();
			$sql = 'SELECT pupilsightRoleID AS value, CONCAT(name," (",category,")") AS name FROM pupilsightRole ORDER BY name';
			$row = $form->addRow()->addClass('role hiddenReveal');
			$row->addLabel('roles[]', __('Select Roles'));
			$row->addSelect('roles[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->placeholder();

			//Role Category
			$row = $form->addRow();
			$row->addLabel('roleCategory', __('Role Category'))->description(__('Users of a certain type.'));
			$row->addYesNoRadio('roleCategory')->checked('N')->required();

			$form->toggleVisibilityByClass('roleCategory')->onRadio('roleCategory')->when('Y');

			$data = array();
			$sql = 'SELECT DISTINCT category AS value, category AS name FROM pupilsightRole ORDER BY category';
			$row = $form->addRow()->addClass('roleCategory hiddenReveal');
			$row->addLabel('roleCategories[]', __('Select Role Categories'));
			$row->addSelect('roleCategories[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(4)->required()->placeholder();
		}

		//Year group
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_yearGroups_any")) {
			$row = $form->addRow();
			$row->addLabel('yearGroup', __('Year Group'))->description(__('Students in year; staff by tutors and courses taught.'));
			$row->addYesNoRadio('yearGroup')->checked('N')->required();

			$form->toggleVisibilityByClass('yearGroup')->onRadio('yearGroup')->when('Y');

			$data = array(pupilsightSchoolYearID => $_SESSION[$guid]["pupilsightSchoolYearID"]);
			//$sql = 'SELECT pupilsightYearGroupID AS value, name FROM pupilsightYearGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY sequenceNumber';
			//$sql = 'SELECT a.pupilsightProgramID ,b.pupilsightYearGroupID as value, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightSchoolYearID =:pupilsightSchoolYearID GROUP BY a.pupilsightYearGroupID';
			$sql = "SELECT  a.pupilsightProgramID, b.pupilsightYearGroupID as value, b.name as name1, CONCAT(c.name,' ',b.name) as name FROM pupilsightProgramClassSectionMapping AS a 
LEFT JOIN pupilsightProgram AS c ON a.pupilsightProgramID = c.pupilsightProgramID 
LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID 
WHERE a.pupilsightSchoolYearID =:pupilsightSchoolYearID GROUP BY a.pupilsightYearGroupID";
			$row = $form->addRow()->addClass('yearGroup hiddenReveal');
			$row->addLabel('yearGroups[]', __('Select Year Groups'));
			$row->addSelect('yearGroups[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->placeholder();

			$row = $form->addRow()->addClass('yearGroup hiddenReveal');
			$row->addLabel('yearGroupsStaff', __('Include Staff?'));
			$row->addYesNo('yearGroupsStaff')->selected('Y');

			$row = $form->addRow()->addClass('yearGroup hiddenReveal');
			$row->addLabel('yearGroupsStudents', __('Include Students?'));
			$row->addYesNo('yearGroupsStudents')->selected('Y');

			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_yearGroups_parents")) {
				$row = $form->addRow()->addClass('yearGroup hiddenReveal');
				$row->addLabel('yearGroupsParents', __('Include Parents?'));
				$row->addYesNo('yearGroupsParents')->selected('N');
			}
		}

		//Roll group
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_rollGroups_my") or isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_rollGroups_any")) {
			$row = $form->addRow();
			$row->addLabel('rollGroup', __('Roll Group'))->description(__('Tutees and tutors.'));
			$row->addYesNoRadio('rollGroup')->checked('N')->required();

			$form->toggleVisibilityByClass('rollGroup')->onRadio('rollGroup')->when('Y');

			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_rollGroups_any")) {
				$data = array("pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"]);
				//$sql = "SELECT pupilsightRollGroup.pupilsightRollGroupID AS value, pupilsightRollGroup.name FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";
				$sql = "SELECT a.pupilsightMappingID as value, d.name as sectionname, a.pupilsightProgramID, b.pupilsightYearGroupID , b.name as name1, CONCAT(b.name,' ',d.name) as name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightProgram AS c ON a.pupilsightProgramID = c.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS d ON a.pupilsightRollGroupID = d.pupilsightRollGroupID WHERE a.pupilsightSchoolYearID =:pupilsightSchoolYearID ORDER BY a.pupilsightMappingID";
			} else {
				if ($roleCategory == "Staff") {
					$data = array("pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightPersonID1" => $_SESSION[$guid]["pupilsightPersonID"], "pupilsightPersonID2" => $_SESSION[$guid]["pupilsightPersonID"], "pupilsightPersonID3" => $_SESSION[$guid]["pupilsightPersonID"], "pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"]);
					$sql = "SELECT pupilsightRollGroup.pupilsightRollGroupID AS value, pupilsightRollGroup.name FROM pupilsightRollGroup WHERE (pupilsightPersonIDTutor=:pupilsightPersonID1 OR pupilsightPersonIDTutor2=:pupilsightPersonID2 OR pupilsightPersonIDTutor3=:pupilsightPersonID3) AND pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";
				} else if ($roleCategory == "Student") {
					$data = array("pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightPersonID" => $_SESSION[$guid]["pupilsightPersonID"],);
					$sql = "SELECT pupilsightRollGroup.pupilsightRollGroupID AS value, pupilsightRollGroup.name FROM pupilsightRollGroup JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";
				}
			}
			$row = $form->addRow()->addClass('rollGroup hiddenReveal');
			$row->addLabel('rollGroups[]', __('Select Roll Groups'));
			$row->addSelect('rollGroups[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->placeholder();

			$row = $form->addRow()->addClass('rollGroup hiddenReveal');
			$row->addLabel('rollGroupsStaff', __('Include Staff?'));
			$row->addYesNo('rollGroupsStaff')->selected('Y');

			$row = $form->addRow()->addClass('rollGroup hiddenReveal');
			$row->addLabel('rollGroupsStudents', __('Include Students?'));
			$row->addYesNo('rollGroupsStudents')->selected('Y');

			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_rollGroups_parents")) {
				$row = $form->addRow()->addClass('rollGroup hiddenReveal');
				$row->addLabel('rollGroupsParents', __('Include Parents?'));
				$row->addYesNo('rollGroupsParents')->selected('N');
			}
		}

		// Course
		/*if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_any")) {
            $row = $form->addRow();
				$row->addLabel('course', __('Course'))->description(__('Members of a course of study.'));
				$row->addYesNoRadio('course')->checked('N')->required();

			$form->toggleVisibilityByClass('course')->onRadio('course')->when('Y');

            if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_any")) {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = "SELECT pupilsightCourse.pupilsightCourseID as value, pupilsightCourse.nameShort as name FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";
            } else {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT pupilsightCourse.pupilsightCourseID as value, pupilsightCourse.nameShort as name FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND NOT role LIKE '%- Left' GROUP BY pupilsightCourse.pupilsightCourseID ORDER BY name";
            }

			$row = $form->addRow()->addClass('course hiddenReveal');
				$row->addLabel('courses[]', __('Select Courses'));
				$row->addSelect('courses[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required();

			$row = $form->addRow()->addClass('course hiddenReveal');
		        $row->addLabel('coursesStaff', __('Include Staff?'));
				$row->addYesNo('coursesStaff')->selected('Y');

			$row = $form->addRow()->addClass('course hiddenReveal');
		        $row->addLabel('coursesStudents', __('Include Students?'));
				$row->addYesNo('coursesStudents')->selected('Y');

			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_parents")) {
				$row = $form->addRow()->addClass('course hiddenReveal');
			        $row->addLabel('coursesParents', __('Include Parents?'));
					$row->addYesNo('coursesParents')->selected('N');
			}
        }*/

		// Class
		/*if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_any")) {
            $row = $form->addRow();
				$row->addLabel('class', __('Class'))->description(__('Members of a class within a course.'));
				$row->addYesNoRadio('class')->checked('N')->required();

			$form->toggleVisibilityByClass('class')->onRadio('class')->when('Y');

            if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_any")) {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as name FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";
            } else {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as name FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND NOT role LIKE '%- Left' ORDER BY name";
            }
            print_r($data);
            print_r($sql);

			$row = $form->addRow()->addClass('class hiddenReveal');
				$row->addLabel('classes[]', __('Select Classes'));
				$row->addSelect('classes[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required();

			$row = $form->addRow()->addClass('class hiddenReveal');
		        $row->addLabel('classesStaff', __('Include Staff?'));
				$row->addYesNo('classesStaff')->selected('Y');

			$row = $form->addRow()->addClass('class hiddenReveal');
		        $row->addLabel('classesStudents', __('Include Students?'));
				$row->addYesNo('classesStudents')->selected('Y');

			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_parents")) {
				$row = $form->addRow()->addClass('class hiddenReveal');
			        $row->addLabel('classesParents', __('Include Parents?'));
					$row->addYesNo('classesParents')->selected('N');
			}
        }*/


		// Activities
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_activities_my") or isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_activities_any")) {
			$row = $form->addRow();
			$row->addLabel('activity', __('Activity'))->description(__('Members of an activity.'));
			$row->addYesNoRadio('activity')->checked('N')->required();

			$form->toggleVisibilityByClass('activity')->onRadio('activity')->when('Y');

			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_activities_any")) {
				$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
				$sql = "SELECT pupilsightActivity.pupilsightActivityID as value, name FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name";
			} else {
				$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
				if ($roleCategory == "Staff") {
					$sql = "SELECT pupilsightActivity.pupilsightActivityID as value, pupilsightActivity.name FROM pupilsightActivity JOIN pupilsightActivityStaff ON (pupilsightActivityStaff.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name";
				} else if ($roleCategory == "Student") {
					$sql = "SELECT pupilsightActivity.pupilsightActivityID as value, pupilsightActivity.name FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND status='Accepted' AND active='Y' ORDER BY name";
				}
			}
			$row = $form->addRow()->addClass('activity hiddenReveal');
			$row->addLabel('activities[]', __('Select Activities'));
			$row->addSelect('activities[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required();

			$row = $form->addRow()->addClass('activity hiddenReveal');
			$row->addLabel('activitiesStaff', __('Include Staff?'));
			$row->addYesNo('activitiesStaff')->selected('Y');

			$row = $form->addRow()->addClass('activity hiddenReveal');
			$row->addLabel('activitiesStudents', __('Include Students?'));
			$row->addYesNo('activitiesStudents')->selected('Y');

			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_activities_parents")) {
				$row = $form->addRow()->addClass('activity hiddenReveal');
				$row->addLabel('activitiesParents', __('Include Parents?'));
				$row->addYesNo('activitiesParents')->selected('N');
			}
		}

		// Applicants
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_applicants")) {
			$row = $form->addRow();
			$row->addLabel('applicants', __('Applicants'))->description(__('Applicants from a given year.'))->description(__('Does not apply to the message wall.'));
			$row->addYesNoRadio('applicants')->checked('N')->required();

			$form->toggleVisibilityByClass('applicants')->onRadio('applicants')->when('Y');

			$sql = "SELECT pupilsightSchoolYearID as value, name FROM pupilsightSchoolYear ORDER BY sequenceNumber DESC";
			$row = $form->addRow()->addClass('applicants hiddenReveal');
			$row->addLabel('applicantList[]', __('Select Years'));
			$row->addSelect('applicantList[]')->fromQuery($pdo, $sql)->selectMultiple()->setSize(6)->required();
		}

		// Houses
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_houses_all") or isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_houses_my")) {
			$row = $form->addRow();
			$row->addLabel('houses', __('Houses'))->description(__('Houses for competitions, etc.'));
			$row->addYesNoRadio('houses')->checked('N')->required();

			$form->toggleVisibilityByClass('houses')->onRadio('houses')->when('Y');

			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_houses_all")) {
				$data = array();
				$sql = "SELECT pupilsightHouse.pupilsightHouseID as value, pupilsightHouse.name FROM pupilsightHouse ORDER BY name";
			} else if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_houses_my")) {
				$dataSelect = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
				$sql = "SELECT pupilsightHouse.pupilsightHouseID as value, pupilsightHouse.name FROM pupilsightHouse JOIN pupilsightPerson ON (pupilsightHouse.pupilsightHouseID=pupilsightPerson.pupilsightHouseID) WHERE pupilsightPersonID=:pupilsightPersonID ORDER BY name";
			}
			$row = $form->addRow()->addClass('houses hiddenReveal');
			$row->addLabel('houseList[]', __('Select Houses'));
			$row->addSelect('houseList[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required();
		}

		// Transport
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_transport_any")) {
			$row = $form->addRow();
			$row->addLabel('transport', __('Transport'))->description(__('Applies to all staff and students who have transport set.'));
			$row->addYesNoRadio('transport')->checked('N')->required();

			$form->toggleVisibilityByClass('transport')->onRadio('transport')->when('Y');

			$sql = "SELECT DISTINCT transport as value, transport as name FROM pupilsightPerson WHERE status='Full' AND NOT transport='' ORDER BY transport";
			$row = $form->addRow()->addClass('transport hiddenReveal');
			$row->addLabel('transports[]', __('Select Transport'));
			$row->addSelect('transports[]')->fromQuery($pdo, $sql)->selectMultiple()->setSize(6)->required();

			$row = $form->addRow()->addClass('transport hiddenReveal');
			$row->addLabel('transportStaff', __('Include Staff?'));
			$row->addYesNo('transportStaff')->selected('Y');

			$row = $form->addRow()->addClass('transport hiddenReveal');
			$row->addLabel('transportStudents', __('Include Students?'));
			$row->addYesNo('transportStudents')->selected('Y');

			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_transport_parents")) {
				$row = $form->addRow()->addClass('transport hiddenReveal');
				$row->addLabel('transportParents', __('Include Parents?'));
				$row->addYesNo('transportParents')->selected('N');
			}
		}

		// Attendance Status / Absentees
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_attendance")) {
			$row = $form->addRow();
			$row->addLabel('attendance', __('Attendance Status'))->description(__('Students matching the given attendance status.'));
			$row->addYesNoRadio('attendance')->checked('N')->required();

			$form->toggleVisibilityByClass('attendance')->onRadio('attendance')->when('Y');

			$sql = "SELECT name, pupilsightRoleIDAll FROM pupilsightAttendanceCode WHERE active = 'Y' ORDER BY direction DESC, sequenceNumber ASC, name";
			$result = $pdo->executeQuery(array(), $sql);

			// Filter the attendance codes by allowed roles (if any)
			$currentRole = $_SESSION[$guid]['pupilsightRoleIDCurrent'];
			$attendanceCodes = ($result->rowCount() > 0) ? $result->fetchAll() : array();
			$attendanceCodes = array_filter($attendanceCodes, function ($item) use ($currentRole) {
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
			$row->addSelect('attendanceStatus[]')->fromArray($attendanceCodes)->selectMultiple()->setSize(6)->required()->selected('Absent');

			$row = $form->addRow()->addClass('attendance hiddenReveal');
			$row->addLabel('attendanceDate', __('Date'));
			$row->addDate('attendanceDate')->required()->setValue(date($_SESSION[$guid]['i18n']['dateFormatPHP']));

			$row = $form->addRow()->addClass('attendance hiddenReveal');
			$row->addLabel('attendanceStudents', __('Include Students?'));
			$row->addYesNo('attendanceStudents')->selected('N');

			$row = $form->addRow()->addClass('attendance hiddenReveal');
			$row->addLabel('attendanceParents', __('Include Parents?'));
			$row->addYesNo('attendanceParents')->selected('Y');
		}

		// Group
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_my") or isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_any")) {
			$row = $form->addRow();
			$row->addLabel('group', __('Group'))->description(__('Members of a Messenger module group.'));
			$row->addYesNoRadio('group')->checked('N')->required();

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
			$row->addSelect('groups[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required();

			$row = $form->addRow()->addClass('messageGroup hiddenReveal');
			$row->addLabel('groupsStaff', __('Include Staff?'));
			$row->addYesNo('groupsStaff')->selected('Y');

			$row = $form->addRow()->addClass('messageGroup hiddenReveal');
			$row->addLabel('groupsStudents', __('Include Students?'));
			$row->addYesNo('groupsStudents')->selected('Y');

			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_parents")) {
				$row = $form->addRow()->addClass('messageGroup hiddenReveal');
				$row->addLabel('groupsParents', __('Include Parents?'))->description('Parents who are members, and parents of student members.');
				$row->addYesNo('groupsParents')->selected('N');
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
		$col->addSelect('pupilsightPersonID')->selected($pupilsightPersonID)->selectMultiple()->addClass("staticwidth");




		// Individuals
		if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_individuals")) {
			$row = $form->addRow();
			$row->addLabel('individuals', __('Individuals'))->description(__('Individuals from the whole school.'));
			$row->addYesNoRadio('individuals')->checked('N')->required();

			$form->toggleVisibilityByClass('individuals')->onRadio('individuals')->when('Y');
			//$data= array();
			$sql = "SELECT pupilsightRole.category, pupilsightPersonID, preferredName, surname, username FROM pupilsightPerson JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary) WHERE status='Full' ORDER BY surname, preferredName";
			//$sql = "SELECT ppr.category, pp2.pupilsightPersonID as value, pp2.preferredName,pp2.surname,pp2.username, psp.name as programname,pyg.name as classname, prg.name as sectionname, pp2.officialName,CONCAT(pp2.preferredName,' ',pyg.name,' ',prg.name) as name FROM pupilsightPerson as pp2 left join pupilsightStudentEnrolment as pse ON pp2.pupilsightPersonID=pse.pupilsightPersonID left join pupilsightProgram as psp ON pse.pupilsightProgramID=psp.pupilsightProgramID left join pupilsightYearGroup as pyg ON pse.pupilsightYearGroupID=pyg.pupilsightYearGroupID left join pupilsightRollGroup as prg ON pse.pupilsightRollGroupID=prg.pupilsightRollGroupID left join pupilsightRole as ppr ON ppr.pupilsightRoleID=pp2.pupilsightRoleIDPrimary WHERE status='Full'";
			$result = $pdo->executeQuery(array(), $sql);

			// Build a set of individuals by ID => formatted name
			$individuals = ($result->rowCount() > 0) ? $result->fetchAll() : array();
			$individuals = array_reduce($individuals, function ($group, $item) {
				$group[$item['pupilsightPersonID']] = formatName("" . $item['preferredName'], $item['surname'], 'Student', true) . ' (' . $item['username'] . ', ' . __($item['category']) . ')';
				return $group;
			}, array());
			/*$row = $form->addRow()->addClass('individuals hiddenReveal');
            $row->addLabel('individualList[]', __('Select Individuals'));
            $row->addSelect('individualList[]')->fromQuery($pdo, $sql, $data)->setId('individualList')->selectMultiple()->required()->placeholder();*/

			$row = $form->addRow()->addClass('individuals hiddenReveal');
			$row->addLabel('individualList[]', __('Select Individuals'));
			$row->addSelect('individualList[]')->setId('individualList')->fromArray($individuals)->selectMultiple()->setSize(6)->required();

			echo "<script>\nvar js_array = " . json_encode($individuals) . ";</script>";
		}
		$row = $form->addRow();
		$row->addFooter();
		$row->addSubmit();

		echo $form->getOutput();
	}
}
?>
<script type='text/javascript'>
	$(document).ready(function() {
		$('#individualList').select2({
			minimumInputLength: 3
		});
	});
</script>

<script type="text/javascript">
	$("#body1").keyup(function() {
		//$("#count").text("Characters left: " + (500 - $(this).val().length));
		$("#count").text("Characters Count : " + $(this).val().length);
	});
</script>
<script>
	$('input[type=radio][name=email]').change(function() {
		if (this.value == 'Y') {
			//alert("yes");
			$("input[name=sms][value='N']").prop("checked", true);
			$("input[name=messageWall][value='N']").prop("checked", true);
			$("[id=publicationdate").hide();
			$("[id=body1hide").hide();
			$("[id=bodyhide").show();
			$("[id=categoryhide").show();
			$("[id=copysmshide").hide();
		} else if (this.value == 'N') {
			//alert("no");
		}
	});
</script>
<script>
	$('input[type=radio][name=sms]').change(function() {
		if (this.value == 'Y') {
			//alert("yes");
			$("input[name=email][value='N']").prop("checked", true);
			$("input[name=messageWall][value='N']").prop("checked", true);

			$("[id=replyemail").hide();
			$("[id=bccemail").hide();
			$("[id=publicationdate").hide();
			$("[id=subjecthide").hide();
			$("[id=bodyhide").hide();
			$("[id=categoryhide").hide();
		} else if (this.value == 'N') {
			//alert("no");
		}
	});
</script>
<script>
	$('input[type=radio][name=messageWall]').change(function() {
		if (this.value == 'Y') {
			//alert("yes");
			$("input[name=sms][value='N']").prop("checked", true);
			$("input[name=email][value='N']").prop("checked", true);
			$("[id=replyemail").hide();
			$("[id=bccemail").hide();
			$("[id=body1hide").hide();
			$("[id=copysmshide").hide();
			$("[id=bodyhide").show();
			$("[id=categoryhide").show();
		} else if (this.value == 'N') {
			//alert("no");
		}
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