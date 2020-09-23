<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    // print_r('<pre>');
    // print_r($_POST);die();
    $pupilsightTTDayID = $_GET['pupilsightTTDayID'] ?? '';
    $pupilsightTTID = $_GET['pupilsightTTID'] ?? '';
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'] ?? '';
    $pupilsightProgramID = $_GET['pupilsightProgramID']??'';
    $pupilsightYearGroupID = $_GET['pupilsightYearGroupID']??'';
    //  print_r($_POST);die();
    if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '' or $pupilsightTTColumnRowID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
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
            //Let's go!
            $values = $result->fetch();

            $urlParams = ['pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTID' => $pupilsightTTID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID];

            $page->breadcrumbs
                ->add(__('Manage Timetables'), 'tt.php', $urlParams)
                ->add(__('Edit Timetable'), 'tt_edit.php', $urlParams)
                ->add(__('Edit Timetable Day'), 'tt_edit_day_edit.php', $urlParams)
                ->add(__('Classes in Period'), 'tt_edit_day_edit_class.php', $urlParams)
                ->add(__('Add Class to Period'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/tt_edit_day_edit_class_addProcess.php?&pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID");

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

            $classes = array();
            $sujects = array();
            $subject1 = array();
            $subject2 = array(''=>'Select Subject');
            $sqls = 'SELECT a.pupilsightDepartmentID,b.name FROM assign_core_subjects_toclass as a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID WHERE a.pupilsightProgramID = "'.$pupilsightProgramID.'" AND a.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'"';
            $resultSub = $connection2->query($sqls);
            $resultSub->execute();           
            $subject = $resultSub->fetchAll();
            foreach($subject as $sub){
               $subject1[$sub['pupilsightDepartmentID']] = $sub['name'];
            }
            $subject=$subject1+$subject2;       

            $years = explode(',', $values['pupilsightYearGroupIDList']);
            try {
                $dataSelect = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                if (count($years) > 0) {
                    $sqlSelectWhere = ' AND (';
                    for ($i = 0; $i < count($years); ++$i) {
                        if ($i > 0) {
                            $sqlSelectWhere = $sqlSelectWhere.' OR ';
                        }
                        $dataSelect[$years[$i]] = '%'.$years[$i].'%';
                        $sqlSelectWhere = $sqlSelectWhere.'(pupilsightYearGroupIDList LIKE :'.$years[$i].')';
                    }
                    $sqlSelectWhere = $sqlSelectWhere.')';
                }
                $sqlSelect = "SELECT pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID $sqlSelectWhere ORDER BY course, class";
                $resultSelect = $connection2->prepare($sqlSelect);
                $resultSelect->execute($dataSelect);
            } catch (PDOException $e) {}
            while ($rowSelect = $resultSelect->fetch()) {
                try {
                    $dataUnique = array('pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightCourseClassID' => $rowSelect['pupilsightCourseClassID']);
                    $sqlUnique = 'SELECT * FROM pupilsightTTDayRowClass WHERE pupilsightTTDayID=:pupilsightTTDayID AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightCourseClassID=:pupilsightCourseClassID';
                    $resultUnique = $connection2->prepare($sqlUnique);
                    $resultUnique->execute($dataUnique);
                } catch (PDOException $e) {}
                if ($resultUnique->rowCount() < 1) {
                    $classes[$rowSelect['pupilsightCourseClassID']] = htmlPrep($rowSelect['course']).'.'.htmlPrep($rowSelect['class']);
                }
            }
         
            $row = $form->addRow();
                $row->addLabel('pupilsightDepartmentID', __('Subject'));
                $row->addSelect('pupilsightDepartmentID')->fromArray($subject)->required()->placeholder();
            $row = $form->addRow();
                $row->addLabel('pupilsightStaffID', __('Staff'))->setId($pupilsightStaffID);
                $row->addSelect('pupilsightStaffID')->fromArray($staff)->required()->placeholder()->selectMultiple();



            $locations = array() ;
            try {
                $dataSelect = array();
                $sqlSelect = 'SELECT * FROM pupilsightSpace ORDER BY name';
                $resultSelect = $connection2->prepare($sqlSelect);
                $resultSelect->execute($dataSelect);
            } catch (PDOException $e) {}
            while ($rowSelect = $resultSelect->fetch()) {
                try {
                    $dataUnique = array('pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightSpaceID' => $rowSelect['pupilsightSpaceID']);
                    $sqlUnique = 'SELECT * FROM pupilsightTTDayRowClass WHERE pupilsightTTDayID=:pupilsightTTDayID AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightSpaceID=:pupilsightSpaceID';
                    $resultUnique = $connection2->prepare($sqlUnique);
                    $resultUnique->execute($dataUnique);
                } catch (PDOException $e) {}
                if ($resultUnique->rowCount() < 1) {
                    $locations[$rowSelect['pupilsightSpaceID']] = htmlPrep($rowSelect['name']);
                }
            }
            $row = $form->addRow();
                $row->addLabel('pupilsightSpaceID', __('Location'));
                $row->addSelect('pupilsightSpaceID')->fromArray($locations)->placeholder();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
} ?>
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
