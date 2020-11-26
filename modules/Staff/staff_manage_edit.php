<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        $search = $_GET['search'] ?? '';
        $allStaff = $_GET['allStaff'] ?? '';

        $page->breadcrumbs
            // ->add(__('Manage Staff'), 'staff_manage.php', ['search' => $search, 'allStaff' => $allStaff])
            ->add(__('Manage Staff'), 'staff_view.php')
            ->add(__('Edit Staff'), 'staff_manage_edit.php');

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified

        if(!empty($_GET['pupilsightPersonID'])){
            $persId = $_GET['pupilsightPersonID'];

            $sqls = "SELECT pupilsightStaffID FROM pupilsightStaff WHERE pupilsightPersonID = ".$persId." ";
            $results = $connection2->query($sqls);
            $stfdata = $results->fetch();
            $pupilsightStaffID = $stfdata['pupilsightStaffID'];
        } else {
            $pupilsightStaffID = $_GET['pupilsightStaffID'];
        }


        //$pupilsightStaffID = $_GET['pupilsightStaffID'];
        if ($pupilsightStaffID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                $data = array('pupilsightStaffID' => $pupilsightStaffID);
                $sql = 'SELECT pupilsightStaff.*, title, surname, preferredName, initials, dateStart, dateEnd FROM pupilsightStaff JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStaffID=:pupilsightStaffID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified record cannot be found.');
                echo '</div>';
            } else {
                //Let's go!
                $values = $result->fetch();
                $pupilsightPersonID = $values['pupilsightPersonID'];

                if ($search != '' or $allStaff != '') {
                    echo "<div class='linkTop'>";
                    echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Staff/staff_manage.php&search=$search&allStaff=$allStaff'>".__('Back to Search Results').'</a>';
                    echo '</div>';
                }
                echo '<h3>'.__('General Information').'</h3>';

                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/staff_manage_editProcess.php?pupilsightStaffID='.$values['pupilsightStaffID']."&search=$search&allStaff=$allStaff");

                $form->setFactory(DatabaseFormFactory::create($pdo));

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('pupilsightPersonID', $values['pupilsightPersonID']);
                $form->addHiddenValue('signature_path', $values['signature_path']);

                $form->addRow()->addHeading(__('Basic Information'));

                $row = $form->addRow();
                    $row->addLabel('pupilsightPersonName', __('Person'))->description(__('Must be unique.'));
                    $row->addTextField('pupilsightPersonName')->readOnly()->setValue(Format::name($values['title'], $values['preferredName'], $values['surname'], 'Staff', false, true));

                $row = $form->addRow();
                    $row->addLabel('initials', __('Initials'))->description(__('Must be unique if set.'));
                    $row->addTextField('initials')->maxlength(4);

                $types = array(__('Basic') => array ('Teaching' => __('Teaching'), 'Support' => __('Support')));
                $sql = "SELECT name as value, name FROM pupilsightRole WHERE category='Staff' ORDER BY name";
                $result = $pdo->executeQuery(array(), $sql);
                $types[__('System Roles')] = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_KEY_PAIR) : array();
                $row = $form->addRow();
                    $row->addLabel('type', __('Type'));
                    $row->addSelect('type')->fromArray($types)->placeholder()->required();

                $row = $form->addRow();
                    $row->addLabel('jobTitle', __('Job Title'));
                    $row->addTextField('jobTitle')->maxlength(100);

                $row = $form->addRow();
                    $row->addLabel('dateStart', __('Start Date'))->description(__("Users's first day at school."));
                    $row->addDate('dateStart');

                $row = $form->addRow();
                    $row->addLabel('dateEnd', __('End Date'))->description(__("Users's last day at school."));
                    $row->addDate('dateEnd');

                $form->addRow()->addHeading(__('First Aid'));

                $row = $form->addRow();
                    $row->addLabel('firstAidQualified', __('First Aid Qualified?'));
                    $row->addYesNo('firstAidQualified')->placeHolder();

                $form->toggleVisibilityByClass('firstAid')->onSelect('firstAidQualified')->when('Y');

                $row = $form->addRow()->addClass('firstAid');
                    $row->addLabel('firstAidExpiry', __('First Aid Expiry'));
                    $row->addDate('firstAidExpiry');

                $form->addRow()->addHeading(__('Biography'));

                $row = $form->addRow();
                    $row->addLabel('countryOfOrigin', __('Country Of Origin'));
                    $row->addSelectCountry('countryOfOrigin')->placeHolder();

                $row = $form->addRow();
                    $row->addLabel('qualifications', __('Qualifications'));
                    $row->addTextField('qualifications')->maxlength(80);

                $row = $form->addRow();
                    $row->addLabel('biographicalGrouping', __('Grouping'))->description(__('Used to group staff when creating a staff directory.'));
                    $row->addTextField('biographicalGrouping')->maxlength(100);

                $row = $form->addRow();
                    $row->addLabel('biographicalGroupingPriority', __('Grouping Priority'))->description(__('Higher numbers move teachers up the order within their grouping.'));
                    $row->addNumber('biographicalGroupingPriority')->decimalPlaces(0)->maximum(99)->maxLength(2)->setValue('0');

                $row = $form->addRow();
                    $row->addLabel('biography', __('Biography'));
                    $row->addTextArea('biography')->setRows(10);

                $row = $form->addRow("Principle?");
                    $row->addLabel('is_principle', __('Principle?'));
                    $row->addCheckBox('is_principle')->setValue('1');    
            
                $row = $form->addRow("Signature");
                    $row->addLabel('file', __('Signature'));
                    $row->addFileUpload('file')
                    ->accepts('.jpg,.jpeg,.gif,.png')
                    ->setMaxUpload(false);    

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

                $form->loadAllValuesFrom($values);

                echo $form->getOutput();

                echo '<h3>'.__('Facilities').'</h3>';
                try {
                    $data = array('pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightPersonID2' => $pupilsightPersonID, 'pupilsightPersonID3' => $pupilsightPersonID, 'pupilsightPersonID4' => $pupilsightPersonID, 'pupilsightPersonID5' => $pupilsightPersonID, 'pupilsightPersonID6' => $pupilsightPersonID, 'pupilsightSchoolYearID1' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = '(SELECT pupilsightSpace.*, pupilsightSpacePersonID, usageType, NULL AS \'exception\' FROM pupilsightSpacePerson JOIN pupilsightSpace ON (pupilsightSpacePerson.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE pupilsightPersonID=:pupilsightPersonID1)
                    UNION
                    (SELECT DISTINCT pupilsightSpace.*, NULL AS pupilsightSpacePersonID, \'Roll Group\' AS usageType, NULL AS \'exception\' FROM pupilsightRollGroup JOIN pupilsightSpace ON (pupilsightRollGroup.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE (pupilsightPersonIDTutor=:pupilsightPersonID2 OR pupilsightPersonIDTutor2=:pupilsightPersonID3 OR pupilsightPersonIDTutor3=:pupilsightPersonID4) AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID1)
                    UNION
                    (SELECT DISTINCT pupilsightSpace.*, NULL AS pupilsightSpacePersonID, \'Timetable\' AS usageType, pupilsightTTDayRowClassException.pupilsightPersonID AS \'exception\' FROM pupilsightSpace JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) LEFT JOIN pupilsightTTDayRowClassException ON (pupilsightTTDayRowClassException.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID AND (pupilsightTTDayRowClassException.pupilsightPersonID=:pupilsightPersonID6 OR pupilsightTTDayRowClassException.pupilsightPersonID IS NULL)) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID5)
                    ORDER BY name';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                echo "<div class='linkTop'>";
                // echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/staff_manage_edit_facility_add.php&pupilsightPersonID=$pupilsightPersonID&pupilsightStaffID=$pupilsightStaffID&search=$search'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";


                echo "<div style='height:50px;'><div class='float-right mb-2'>";  
                echo "&nbsp;&nbsp;<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/staff_manage_edit_facility_add.php&pupilsightPersonID=$pupilsightPersonID&pupilsightStaffID=$pupilsightStaffID&search=$search' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>";

                echo '</div>';

                if ($result->rowCount() < 1) {
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
                    echo __('Usage').'<br/>';
                    echo '</th>';
                    echo '<th>';
                    echo __('Actions');
                    echo '</th>';
                    echo '</tr>';

                    $count = 0;
                    $rowNum = 'odd';
                    while ($row = $result->fetch()) {
                        if ($row['exception'] == null) {
                            if ($count % 2 == 0) {
                                $rowNum = 'even';
                            } else {
                                $rowNum = 'odd';
                            }
                            ++$count;

                            echo "<tr class=$rowNum>";
                            echo '<td>';
                            echo $row['name'];
                            echo '</td>';
                            echo '<td>';
                            echo $row['usageType'];
                            echo '</td>';
                            echo '<td>';
                            if ($row['usageType'] != 'Roll Group' and $row['usageType'] != 'Timetable') {
                                echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/staff_manage_edit_facility_delete.php&pupilsightSpacePersonID='.$row['pupilsightSpacePersonID']."&pupilsightStaffID=$pupilsightStaffID&search=$search&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    echo '</table>';
                }


                if ($highestAction == 'Manage Staff_confidential') {
                    echo '<h3>'.__('Contracts').'</h3>';
                    try {
                        $data = array('pupilsightStaffID' => $pupilsightStaffID);
                        $sql = 'SELECT * FROM pupilsightStaffContract WHERE pupilsightStaffID=:pupilsightStaffID ORDER BY dateStart DESC';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    echo "<div class='linkTop'>";
                    // echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/staff_manage_edit_contract_add.php&pupilsightStaffID=$pupilsightStaffID&search=$search'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";

                    echo "<div style='height:50px;'><div class='float-right mb-2'>";  
                    echo "&nbsp;&nbsp;<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/staff_manage_edit_contract_add.php&pupilsightStaffID=$pupilsightStaffID&search=$search' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>";
                    echo '</div>';

                    if ($result->rowCount() < 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('There are no records to display.');
                        echo '</div>';
                    } else {
                        echo "<table cellspacing='0' style='width: 100%'>";
                        echo "<tr class='head'>";
                        echo '<th>';
                        echo __('Title');
                        echo '</th>';
                        echo '<th>';
                        echo __('Status').'<br/>';
                        echo '</th>';
                        echo '<th>';
                        echo __('Dates');
                        echo '</th>';
                        echo '<th>';
                        echo __('Actions');
                        echo '</th>';
                        echo '</tr>';

                        $count = 0;
                        $rowNum = 'odd';
                        while ($row = $result->fetch()) {
                            if ($count % 2 == 0) {
                                $rowNum = 'even';
                            } else {
                                $rowNum = 'odd';
                            }
                            ++$count;

                            echo "<tr class=$rowNum>";
                            echo '<td>';
                            echo $row['title'];
                            echo '</td>';
                            echo '<td>';
                            echo $row['status'];
                            echo '</td>';
                            echo '<td>';
                            if ($row['dateEnd'] == '') {
                                echo dateConvertBack($guid, $row['dateStart']);
                            } else {
                                echo dateConvertBack($guid, $row['dateStart']).' - '.dateConvertBack($guid, $row['dateEnd']);
                            }
                            echo '</td>';
                            echo '<td>';
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/staff_manage_edit_contract_edit.php&pupilsightStaffContractID='.$row['pupilsightStaffContractID']."&pupilsightStaffID=$pupilsightStaffID&search=$search'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                }
            }
        }
    }
}
