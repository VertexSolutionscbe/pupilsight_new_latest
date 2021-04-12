<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
      //  ->add(__('Manage Families'), 'family_manage.php')
        ->add(__('Edit Family'));        

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightFamilyID = $_GET['pupilsightFamilyID'];
    $childid= $_GET['child_id'];
    $search = null;
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
    }
    if ($pupilsightFamilyID == '') {
        echo '<h1>';
        echo __('Edit Family');
        echo '</h1>';
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFamilyID' => $pupilsightFamilyID);
            $sql = 'SELECT * FROM pupilsightFamily WHERE pupilsightFamilyID=:pupilsightFamilyID';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            // $data1 = array('pupilsightPersonID' => $childid);
            // $sqlp = 'SELECT * FROM pupilsightFamilyRelationship WHERE pupilsightPersonID2=:pupilsightPersonID';
            // $resultp = $connection2->prepare($sqlp);
            // $resultp->execute($data1);

            $sqlf = 'SELECT pupilsightFamilyID FROM pupilsightFamilyChild WHERE pupilsightPersonID= ' . $childid . ' ';
			$resultfc = $connection2->query($sqlf);
			$fdata = $resultfc->fetch();
			$pupilsightFamilyID = $fdata['pupilsightFamilyID'];

			
			$data = array('pupilsightFamilyID' => $pupilsightFamilyID);
			$sqlp = 'SELECT * FROM pupilsightFamilyRelationship WHERE pupilsightFamilyID=:pupilsightFamilyID GROUP BY pupilsightPersonID1';
			$resultp = $connection2->prepare($sqlp);
			$resultp->execute($data);
            
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo '<h1>';
            echo 'Edit Family';
            echo '</h1>';
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();
            $parents = $resultp->fetchAll();
            $kount = count($parents);

            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/User Admin/family_manage.php&search=$search'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }
            echo "<div style='height:50px;'><div class='float-left mb-2'><a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/student_edit.php&pupilsightPersonID=".$childid."&search=' class='btn btn-white '>Student</a>";  


            if(!empty($parents)){
                foreach($parents as $par){
                    echo "&nbsp;&nbsp;<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/parent_edit.php&pupilsightPersonID=".$par['pupilsightPersonID1']."&child_id=".$childid."&search=' class='btn btn-white'>".$par['relationship']."</a>"; 
                }
                echo "&nbsp;&nbsp;<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/family_manage_edit.php&pupilsightFamilyID=".$parents[0]['pupilsightFamilyID']."&child_id=".$childid."&search=' class='btn btn-primary active'>Family</a>";
            }

            if($kount == 0){
				echo "&nbsp;&nbsp;<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Students/parent_add.php&studentid=" . $childid . " ' class='btn btn-primary'>Add Parent</a>";
			}

			if($kount == 1){
				echo "&nbsp;&nbsp;<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Students/parent2_add.php&studentid=" . $childid . " ' class='btn btn-primary'>Add Parent</a>";
			}

			echo "</div><div class='float-none'></div></div>";
            

            $form = Form::create('action1', $_SESSION[$guid]['absoluteURL'].'/modules/User Admin'."/family_manage_editProcess.php?pupilsightFamilyID=$pupilsightFamilyID&child_id=$childid&search=$search");
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addRow()->addHeading(__('General Information'));

            $row = $form->addRow();
                $row->addLabel('name', __('Family Name'));
                $row->addTextField('name')->maxLength(100)->required();

            $row = $form->addRow();
        		$row->addLabel('status', __('Marital Status'));
        		$row->addSelectMaritalStatus('status')->required();

            $row = $form->addRow();
                $row->addLabel('languageHomePrimary', __('Home Language - Primary'));
                $row->addSelectLanguage('languageHomePrimary');

            $row = $form->addRow();
                $row->addLabel('languageHomeSecondary', __('Home Language - Secondary'));
                $row->addSelectLanguage('languageHomeSecondary');

            $row = $form->addRow();
                $row->addLabel('nameAddress', __('Address Name'))->description(__('Formal name to address parents with.'));
                $row->addTextField('nameAddress')->maxLength(100)->required();

            $row = $form->addRow();
                $row->addLabel('homeAddress', __('Home Address'))->description(__('Unit, Building, Street'));
                $row->addTextArea('homeAddress')->maxLength(255)->setRows(2);

            $row = $form->addRow();
                $row->addLabel('homeAddressDistrict', __('Home Address (District)'))->description(__('County, State, District'));
                $row->addTextFieldDistrict('homeAddressDistrict');

            $row = $form->addRow();
                $row->addLabel('homeAddressCountry', __('Home Address (Country)'));
                $row->addSelectCountry('homeAddressCountry');

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();


            //Get children and prep array
            try {
                $dataChildren = array('pupilsightFamilyID' => $pupilsightFamilyID);
                $sqlChildren = 'SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID ORDER BY surname, preferredName';
                $resultChildren = $connection2->prepare($sqlChildren);
                $resultChildren->execute($dataChildren);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            $children = array();
            $count = 0;
            while ($rowChildren = $resultChildren->fetch()) {
                $children[$count]['image_240'] = $rowChildren['image_240'];
                $children[$count]['pupilsightPersonID'] = $rowChildren['pupilsightPersonID'];
                $children[$count]['preferredName'] = $rowChildren['preferredName'];
                $children[$count]['surname'] = $rowChildren['surname'];
                $children[$count]['status'] = $rowChildren['status'];
                $children[$count]['comment'] = $rowChildren['comment'];
                ++$count;
            }
            //Get adults and prep array
            try {
                $dataAdults = array('pupilsightFamilyID' => $pupilsightFamilyID);
                $sqlAdults = 'SELECT * FROM pupilsightFamilyAdult, pupilsightPerson WHERE (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) AND pupilsightFamilyID=:pupilsightFamilyID ORDER BY contactPriority, surname, preferredName';
                $resultAdults = $connection2->prepare($sqlAdults);
                $resultAdults->execute($dataAdults);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            $adults = array();
            $count = 0;
            while ($rowAdults = $resultAdults->fetch()) {
                $adults[$count]['image_240'] = $rowAdults['image_240'];
                $adults[$count]['pupilsightPersonID'] = $rowAdults['pupilsightPersonID'];
                $adults[$count]['title'] = $rowAdults['title'];
                $adults[$count]['preferredName'] = $rowAdults['preferredName'];
                $adults[$count]['surname'] = $rowAdults['surname'];
                $adults[$count]['status'] = $rowAdults['status'];
                $adults[$count]['comment'] = $rowAdults['comment'];
                $adults[$count]['childDataAccess'] = $rowAdults['childDataAccess'];
                $adults[$count]['contactPriority'] = $rowAdults['contactPriority'];
                $adults[$count]['contactCall'] = $rowAdults['contactCall'];
                $adults[$count]['contactSMS'] = $rowAdults['contactSMS'];
                $adults[$count]['contactEmail'] = $rowAdults['contactEmail'];
                $adults[$count]['contactMail'] = $rowAdults['contactMail'];
                ++$count;
            }

            //Get relationships and prep array
            try {
                $dataRelationships = array('pupilsightFamilyID' => $pupilsightFamilyID);
                $sqlRelationships = 'SELECT * FROM pupilsightFamilyRelationship WHERE pupilsightFamilyID=:pupilsightFamilyID';
                $resultRelationships = $connection2->prepare($sqlRelationships);
                $resultRelationships->execute($dataRelationships);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            $relationships = array();
            $count = 0;
            while ($rowRelationships = $resultRelationships->fetch()) {
                $relationships[$rowRelationships['pupilsightPersonID1']][$rowRelationships['pupilsightPersonID2']] = $rowRelationships['relationship'];
                ++$count;
            }

            echo '<h3>';
            echo __('Relationships');
            echo '</h3>';
            echo '<p>';
            echo __('Use the table below to show how each child is related to each adult in the family.');
            echo '</p>';
            if ($resultChildren->rowCount() < 1 or $resultAdults->rowCount() < 1) {
                echo "<div class='alert alert-danger'>".__('There are not enough people in this family to form relationships.').'</div>';
            } else {

                $form = Form::create('action2', $_SESSION[$guid]['absoluteURL'].'/modules/User Admin'."/family_manage_edit_relationshipsProcess.php?pupilsightFamilyID=".$pupilsightFamilyID."&child_id=".$childid."&search=$search");

                $form->setFactory(DatabaseFormFactory::create($pdo));
                $form->setClass('colorOddEven fullWidth');

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                $row = $form->addRow()->addClass('head break');
                    $row->addContent(__('Adults'));
                    foreach ($children as $child) {
                        $row->addContent(formatName('', $child['preferredName'], $child['surname'], 'Student'));
                    }

                $count = 0;
                foreach ($adults as $adult) {
                    ++$count;
                    $row = $form->addRow();
                        $row->addContent(formatName($adult['title'], $adult['preferredName'], $adult['surname'], 'Parent'));
                        foreach ($children as $child) {
                            $form->addHiddenValue('pupilsightPersonID1[]', $adult['pupilsightPersonID']);
                            $form->addHiddenValue('pupilsightPersonID2[]', $child['pupilsightPersonID']);
                            $relationshipSet = (isset($relationships[$adult['pupilsightPersonID']][$child['pupilsightPersonID']]) ? $relationships[$adult['pupilsightPersonID']][$child['pupilsightPersonID']] : null);
                            $row->addSelectRelationship('relationships['.$adult['pupilsightPersonID'].']['.$child['pupilsightPersonID'].']')->setClass('smallWidth floatNone')->selected($relationshipSet);
                        }
                }

                $row = $form->addRow();
                    $row->addSubmit();

                $form->loadAllValuesFrom($values);

                echo $form->getOutput();
            }

            echo '<h3>';
            echo __('View Children');
            echo '</h3>';

            if ($resultChildren->rowCount() < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                echo "<table cellspacing='0' style='width: 100%'>";
                echo "<tr class='head'>";
                echo '<th>';
                echo __('Photo');
                echo '</th>';
                echo '<th>';
                echo __('Name');
                echo '</th>';
                echo '<th>';
                echo __('Status');
                echo '</th>';
                echo '<th>';
                echo __('Roll Group');
                echo '</th>';
                echo '<th>';
                echo __('Comment');
                echo '</th>';
                echo '<th>';
                echo __('Actions');
                echo '</th>';
                echo '</tr>';

                $count = 0;
                $rowNum = 'odd';
                foreach ($children as $child) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$count;

                    //COLOR ROW BY STATUS!
                    echo "<tr class=$rowNum>";
                    echo '<td>';
                    echo getUserPhoto($guid, $child['image_240'], 75);
                    echo '</td>';
                    echo '<td>';
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin/user_manage_edit.php&pupilsightPersonID='.$child['pupilsightPersonID']."'>".formatName('', $child['preferredName'], $child['surname'], 'Student').'</a>';
                    echo '</td>';
                    echo '<td>';
                    echo $child['status'];
                    echo '</td>';
                    echo '<td>';
                    try {
                        $dataDetail = array('pupilsightPersonID' => $child['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                        $sqlDetail = 'SELECT * FROM pupilsightRollGroup JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID';
                        $resultDetail = $connection2->prepare($sqlDetail);
                        $resultDetail->execute($dataDetail);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($resultDetail->rowCount() == 1) {
                        $rowDetail = $resultDetail->fetch();
                        echo $rowDetail['name'];
                    }
                    echo '</td>';
                    echo '<td>';
                    echo nl2brr($child['comment']);
                    echo '</td>';
                    echo '<td>';
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin'."/family_manage_edit_editChild.php&pupilsightFamilyID=$pupilsightFamilyID&pupilsightPersonID=".$child['pupilsightPersonID']."&search=$search'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                    echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/User Admin'."/family_manage_edit_deleteChild.php&pupilsightFamilyID=$pupilsightFamilyID&pupilsightPersonID=".$child['pupilsightPersonID']."&search=$search&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a>";
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin'.'/user_manage_password.php&pupilsightPersonID='.$child['pupilsightPersonID']."&search=$search'><img title='".__('Change Password')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/key.png'/></a>";
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }

            $form = Form::create('action3', $_SESSION[$guid]['absoluteURL'].'/modules/User Admin'."/family_manage_edit_addChildProcess.php?pupilsightFamilyID=".$pupilsightFamilyID."&child_id=".$childid."&search=$search");
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addRow()->addHeading(__('Add Child'));

            $row = $form->addRow();
                $row->addLabel('pupilsightPersonID', __('Child\'s Name'));
                $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'], array('allStudents' => true, 'byName' => true, 'byRoll' => true, 'showRoll' => true))->placeholder()->required();

            $row = $form->addRow();
                $row->addLabel('comment', __('Comment'));
                $row->addTextArea('comment')->setRows(8);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();

            echo '<h3>';
            echo __('View Adults');
            echo '</h3>';
            echo "<div class='alert alert-warning'>";
            echo __('Logic exists to try and ensure that there is always one and only one parent with Contact Priority set to 1. This may result in values being set which are not exactly what you chose.');
            echo '</div>';

            
            if ($resultAdults->rowCount() < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                echo "<table cellspacing='0' style='width: 100%'>";
                echo "<tr class='head'>";
                echo '<th>';
                echo __('Name');
                echo '</th>';
                echo '<th>';
                echo __('Status');
                echo '</th>';
                echo '<th>';
                echo __('Comment');
                echo '</th>';
                echo "<th style='max-width: 50px; padding-left: 1px; padding-right: 1px; height: 100px'>";
                echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);'>".__('Data Access').'</div>';
                echo '</th>';
                echo "<th style='max-width: 50px; padding-left: 1px; padding-right: 1px'>";
                echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);'>".__('Contact Priority').'</div>';
                echo '</th>';
                echo "<th style='max-width: 50px; padding-left: 1px; padding-right: 1px'>";
                echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);'>".__('Contact By Phone').'</div>';
                echo '</th>';
                echo "<th style='max-width: 50px; padding-left: 1px; padding-right: 1px'>";
                echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);'>".__('Contact By SMS').'</div>';
                echo '</th>';
                echo "<th style='max-width: 50px; padding-left: 1px; padding-right: 1px'>";
                echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);'>".__('Contact By Email').'</div>';
                echo '</th>';
                echo "<th style='max-width: 50px; padding-left: 1px; padding-right: 1px'>";
                echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);'>".__('Contact By Mail').'</div>';
                echo '</th>';
                echo '<th>';
                echo __('Actions');
                echo '</th>';
                echo '</tr>';

                $count = 0;
                $rowNum = 'odd';
                foreach ($adults as $adult) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$count;

                    //COLOR ROW BY STATUS!
                    echo "<tr class=$rowNum>";
                    echo '<td>';
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin/user_manage_edit.php&pupilsightPersonID='.$adult['pupilsightPersonID']."'>".formatName($adult['title'], $adult['preferredName'], $adult['surname'], 'Parent').'</a>';
                    echo '</td>';
                    echo '<td>';
                    echo $adult['status'];
                    echo '</td>';
                    echo '<td>';
                    echo nl2brr($adult['comment']);
                    echo '</td>';
                    echo "<td style='padding-left: 1px; padding-right: 1px'>";
                    echo $adult['childDataAccess'];
                    echo '</td>';
                    echo "<td style='padding-left: 1px; padding-right: 1px'>";
                    echo $adult['contactPriority'];
                    echo '</td>';
                    echo "<td style='padding-left: 1px; padding-right: 1px'>";
                    echo $adult['contactCall'];
                    echo '</td>';
                    echo "<td style='padding-left: 1px; padding-right: 1px'>";
                    echo $adult['contactSMS'];
                    echo '</td>';
                    echo "<td style='padding-left: 1px; padding-right: 1px'>";
                    echo $adult['contactEmail'];
                    echo '</td>';
                    echo "<td style='padding-left: 1px; padding-right: 1px'>";
                    echo $adult['contactMail'];
                    echo '</td>';
                    echo '<td>';
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin'."/family_manage_edit_editAdult.php&pupilsightFamilyID=$pupilsightFamilyID&pupilsightPersonID=".$adult['pupilsightPersonID']."&search=$search'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                    echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/User Admin'."/family_manage_edit_deleteAdult.php&pupilsightFamilyID=$pupilsightFamilyID&pupilsightPersonID=".$adult['pupilsightPersonID']."&search=$search&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a>";
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin'.'/user_manage_password.php&pupilsightPersonID='.$adult['pupilsightPersonID']."&search=$search'><img title='".__('Change Password')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/key.png'/></a>";
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }

            $form = Form::create('action4', $_SESSION[$guid]['absoluteURL'].'/modules/User Admin'."/family_manage_edit_addAdultProcess.php?pupilsightFamilyID=".$pupilsightFamilyID."&child_id=".$childid."&search=$search");

            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addRow()->addHeading(__('Add Adult'));

            $adults = array();
            try {
                $dataSelect = array();
                $sqlSelect = "SELECT status, pupilsightPersonID, preferredName, surname, username FROM pupilsightPerson WHERE status='Full' OR status='Expected' ORDER BY surname, preferredName";
                $resultSelect = $connection2->prepare($sqlSelect);
                $resultSelect->execute($dataSelect);
            } catch (PDOException $e) { }
            while ($rowSelect = $resultSelect->fetch()) {
                $expected = (($rowSelect['status'] == 'Expected') ? ' ('.__('Expected').')' : '');
                $adults[$rowSelect['pupilsightPersonID']] = formatName('', htmlPrep($rowSelect['preferredName']), htmlPrep($rowSelect['surname']), 'Parent', true, true).' ('.$rowSelect['username'].')'.$expected;
            }
            $row = $form->addRow();
                $row->addLabel('pupilsightPersonID2', __('Adult\'s Name'));
                $row->addSelect('pupilsightPersonID2')->fromArray($adults)->placeHolder()->required();

            $row = $form->addRow();
                $row->addLabel('comment2', __('Comment'))->description(__('Data displayed in full Student Profile'));
                $row->addTextArea('comment2')->setRows(8);

            $row = $form->addRow();
                $row->addLabel('childDataAccess', __('Data Access?'))->description(__('Access data on family\'s children?'));
                $row->addYesNo('childDataAccess')->required();

            $priorities = array(
                '1' => __('1'),
                '2' => __('2'),
                '3' => __('3')
            );
            $row = $form->addRow();
                $row->addLabel('contactPriority', __('Contact Priority'))->description(__('The order in which school should contact family members.'));
                $row->addSelect('contactPriority')->fromArray($priorities)->required();

            $row = $form->addRow()->addClass('contact');
                $row->addLabel('contactCall', __('Call?'))->description(__('Receive non-emergency phone calls from school?'));
                $row->addYesNo('contactCall')->required();

            $row = $form->addRow()->addClass('contact');
                $row->addLabel('contactSMS', __('SMS?'))->description(__('Receive non-emergency SMS messages from school?'));
                $row->addYesNo('contactSMS')->required();

            $row = $form->addRow()->addClass('contact');
                $row->addLabel('contactEmail', __('Email?'))->description(__('Receive non-emergency emails from school?'));
                $row->addYesNo('contactEmail')->required();

            $row = $form->addRow()->addClass('contact');
                $row->addLabel('contactMail', __('Mail?'))->description(__('Receive postage mail from school?'));
                $row->addYesNo('contactMail')->required();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();

            echo "<script type=\"text/javascript\">
                $(document).ready(function(){
                    $(\"#contactCall\").attr(\"disabled\", \"disabled\");
                    $(\"#contactSMS\").attr(\"disabled\", \"disabled\");
                    $(\"#contactEmail\").attr(\"disabled\", \"disabled\");
                    $(\"#contactMail\").attr(\"disabled\", \"disabled\");
                    $(\"#contactPriority\").change(function(){
                        if ($('#contactPriority').val()==\"1\" ) {
                            $(\"#contactCall\").attr(\"disabled\", \"disabled\");
                            $(\"#contactCall\").val(\"Y\");
                            $(\"#contactSMS\").attr(\"disabled\", \"disabled\");
                            $(\"#contactSMS\").val(\"Y\");
                            $(\"#contactEmail\").attr(\"disabled\", \"disabled\");
                            $(\"#contactEmail\").val(\"Y\");
                            $(\"#contactMail\").attr(\"disabled\", \"disabled\");
                            $(\"#contactMail\").val(\"Y\");
                        }
                        else {
                            $(\"#contactCall\").removeAttr(\"disabled\");
                            $(\"#contactSMS\").removeAttr(\"disabled\");
                            $(\"#contactEmail\").removeAttr(\"disabled\");
                            $(\"#contactMail\").removeAttr(\"disabled\");
                        }
                     });
                });
            </script>";
        }
    }
}
?>
<script>
    $(document).ready(function(){
        $("#pupilsightPersonID").select2();
        $("#pupilsightPersonID2").select2();
    });
</script>