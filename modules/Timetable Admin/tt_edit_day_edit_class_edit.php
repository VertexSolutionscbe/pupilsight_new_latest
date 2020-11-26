<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightTTDayID = $_GET['pupilsightTTDayID'] ?? '';
    $pupilsightTTID = $_GET['pupilsightTTID'] ?? '';
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'] ?? '';
    // $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
    $pupilsightProgramID = $_GET['pupilsightProgramID'];
    $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
    if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '' ) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID);
            $sql = 'SELECT pupilsightTTDayRowClass.pupilsightTTDayRowClassID, pupilsightTTDayRowClass.pupilsightDepartmentID,pupilsightTTDayRowClass.pupilsightStaffID,pupilsightPerson.officialName,pupilsightTTDayRowClass.pupilsightSpaceID FROM pupilsightTTDayRowClass 
            -- JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
            LEFT JOIN pupilsightStaff on pupilsightTTDayRowClass.pupilsightStaffID=pupilsightStaff.pupilsightStaffID
            LEFT JOIN pupilsightDepartment ON pupilsightTTDayRowClass.pupilsightDepartmentID = pupilsightDepartment.pupilsightDepartmentID LEFT JOIN pupilsightPerson  ON pupilsightStaff.pupilsightPersonID = pupilsightPerson.pupilsightPersonID
             WHERE pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightTTDayID=:pupilsightTTDayID AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID 
             ';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            // $row = $result->fetch();
            $rowdep = $result->fetch();
            // print_r($rowdep);
            $pupilsightDepartmentID =  $rowdep['pupilsightDepartmentID'];

            $pupilsightSpaceID = $rowdep['pupilsightSpaceID'];
            $pupilsightTTDayRowClassID = $rowdep['pupilsightTTDayRowClassID'];

            $sqlst = 'SELECT a.pupilsightStaffID,a.pupilsightDepartmentID ,c.officialName as sname FROM assignstaff_tosubject AS a LEFT JOIN pupilsightStaff AS b ON a.pupilsightStaffID = b.pupilsightStaffID LEFT JOIN pupilsightPerson AS c ON b.pupilsightPersonID = c.pupilsightPersonID  WHERE a.pupilsightDepartmentID = "'.$pupilsightDepartmentID.'"';
            $resultSt = $connection2->query($sqlst);
            $resultSt->execute();
            $staffListData = $resultSt->fetchAll();

            if(!empty($staffListData)){
                $staffList = array();
                $staffList2=array();
                $staffList1=array(''=>'Select Staff');
                foreach ($staffListData as $dt) {
                    $staffList2[$dt['pupilsightStaffID']] = $dt['sname'];
                }
                $staffList= $staffList1 + $staffList2;
            }
            //print_r($staffList);die();
            try {
                $data = array('pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTID' => $pupilsightTTID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID);
                $sql = 'SELECT pupilsightTT.name AS ttName, pupilsightTTDay.name AS dayName, pupilsightTTColumnRow.name AS rowName, pupilsightYearGroupIDList FROM pupilsightTT JOIN pupilsightTTDay ON (pupilsightTT.pupilsightTTID=pupilsightTTDay.pupilsightTTID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumn.pupilsightTTColumnID=pupilsightTTColumnRow.pupilsightTTColumnID) WHERE pupilsightTTDay.pupilsightTTDayID=:pupilsightTTDayID AND pupilsightTT.pupilsightTTID=:pupilsightTTID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID';
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
                $values = $result->fetch();
                // print_r($values);

                $urlParams = ['pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupID'=>$pupilsightYearGroupID,'pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTID' => $pupilsightTTID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID];

                $page->breadcrumbs
                    ->add(__('Manage Timetables'), 'tt.php', $urlParams)
                    ->add(__('Edit Timetable'), 'tt_edit.php', $urlParams)
                    ->add(__('Edit Timetable Day'), 'tt_edit_day_edit.php', $urlParams)
                    ->add(__('Classes in Period'), 'tt_edit_day_edit_class.php', $urlParams)
                    ->add(__('Edit Class in Period'));

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }
                $sujects = array();
                $subject1 = array();

                $sqls = 'SELECT a.pupilsightDepartmentID,b.name FROM assign_core_subjects_toclass as a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID WHERE a.pupilsightProgramID = "'.$pupilsightProgramID.'" AND a.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'"';
                $resultSub = $connection2->query($sqls);
                $resultSub->execute();
                $subject = $resultSub->fetchAll();
                foreach($subject as $sub){
                    $subject1[$sub['pupilsightDepartmentID']] = $sub['name'];
                }
                $subject=$subject1;




                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/tt_edit_day_edit_class_editProcess.php?&pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID&pupilsightTTDayRowClassID=$pupilsightTTDayRowClassID&pupilsightCourseClassID=$pupilsightCourseClassID");

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('pupilsightTTID', $pupilsightTTID);
                $form->addHiddenValue('pupilsightProgramID', $pupilsightProgramID);
                $form->addHiddenValue('pupilsightYearGroupID', $pupilsightYearGroupID);
                $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

                $row = $form->addRow();
                $row->addLabel('ttName', __('Timetable'));
                $row->addTextField('ttName')->maxLength(20)->required()->readonly()->setValue($values['ttName']);

                $row = $form->addRow();
                $row->addLabel('dayName', __('Day'));
                $row->addTextField('dayName')->maxLength(20)->required()->readonly()->setValue($values['dayName']);

                $row = $form->addRow();
                $row->addLabel('rowName', __('Period'));
                $row->addTextField('rowName')->maxLength(20)->required()->readonly()->setValue($values['rowName']);

                $row = $form->addRow();
                $row->addLabel('pupilsightDepartmentID', __('Subject'));
                $row->addSelect('pupilsightDepartmentID')->fromArray($subject)->required()->placeholder()->selected($rowdep['pupilsightDepartmentID']);

                $row = $form->addRow();
                $row->addLabel('pupilsightStaffID', __('Staff'));
                $pupilsightStaffIDs = explode(',', $rowdep['pupilsightStaffID']);
                $checked = array_filter(array_keys($staffList), function ($item) use ($pupilsightStaffIDs) {
                    return in_array($item, $pupilsightStaffIDs);
                });
                $row->addSelect('pupilsightStaffID')->fromArray($staffList)->selected($pupilsightStaffIDs)->selectMultiple();



                $locations = array() ;
                try {
                    $dataSelect = array();
                    $sqlSelect = 'SELECT * FROM pupilsightSpace ORDER BY name';
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                }
                while ($rowSelect = $resultSelect->fetch()) {
                    try {
                        $dataUnique = array('pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightSpaceID' => $rowSelect['pupilsightSpaceID']);
                        $sqlUnique = 'SELECT * FROM pupilsightTTDayRowClass WHERE pupilsightTTDayID=:pupilsightTTDayID AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightSpaceID=:pupilsightSpaceID';
                        $resultUnique = $connection2->prepare($sqlUnique);
                        $resultUnique->execute($dataUnique);
                    } catch (PDOException $e) {
                    }
                    if ($resultUnique->rowCount() < 1 || $rowSelect['pupilsightSpaceID'] == $pupilsightSpaceID) {
                        $locations[$rowSelect['pupilsightSpaceID']] = htmlPrep($rowSelect['name']);
                    }
                }

                $row = $form->addRow();
                $row->addLabel('pupilsightSpaceID', __('Location'));
                $row->addSelect('pupilsightSpaceID')->selected($pupilsightSpaceID)->fromArray($locations)->placeholder()->setValue($rowdep['pupilsightSpaceID']);

                $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

                echo $form->getOutput();
            }
        }
    }
}
?>
<script>
    $(document).on('change','#pupilsightDepartmentID',function(){
        var val = $(this).val();
        var type = 'Classwisesubject';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function(response) {
                    $("#pupilsightStaffID").html();
                    $("#pupilsightStaffID").html(response);
                }
            });
        }
    });
</script>