<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Students/studentEnrolment_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $pupilsightStudentEnrolmentID = $_GET['pupilsightStudentEnrolmentID'] ?? '';
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Student Enrolment'), 'studentEnrolment_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Edit Student Enrolment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    if ($pupilsightStudentEnrolmentID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightStudentEnrolmentID' => $pupilsightStudentEnrolmentID);
            // $sql = 'SELECT pupilsightRollGroup.pupilsightRollGroupID, pupilsightYearGroup.pupilsightYearGroupID,pupilsightStudentEnrolmentID, surname, preferredName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, dateStart, dateEnd, pupilsightPerson.pupilsightPersonID, rollOrder, pupilsightProgramID FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightYearGroup, pupilsightRollGroup WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) AND (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) AND (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID ORDER BY surname, preferredName';

            $sql = 'SELECT pupilsightRollGroup.pupilsightRollGroupID, pupilsightYearGroup.pupilsightYearGroupID,pupilsightStudentEnrolmentID, surname, preferredName, officialName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, dateStart, dateEnd, pupilsightPerson.pupilsightPersonID, rollOrder, pupilsightProgramID FROM pupilsightPerson LEFT JOIN pupilsightStudentEnrolment ON pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID LEFT JOIN pupilsightYearGroup ON pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID LEFT JOIN  pupilsightRollGroup ON pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID ORDER BY surname, preferredName';
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

            $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
            $resultp = $connection2->query($sqlp);
            $rowdataprog = $resultp->fetchAll();
    
            $program=array();  
            $program2=array();  
            $program1=array(''=>'Select Program');
            foreach ($rowdataprog as $dt) {
                $program2[$dt['pupilsightProgramID']] = $dt['name'];
            }
            $program= $program1 + $program2; 
            
            $sqls = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightYearGroupID = "' . $values['pupilsightYearGroupID'] . '" GROUP BY a.pupilsightRollGroupID';
            $results = $connection2->query($sqls);
            $sectiondata = $results->fetchAll();
            
            $sections=array();  
            $sections2=array();  
            $sections1=array(''=>'Select Section');
            foreach ($sectiondata as $st) {
                $sections2[$st['pupilsightRollGroupID']] = $st['name'];
            }
            $sections= $sections1 + $sections2; 

            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/studentEnrolment_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            $form = Form::create('studentEnrolmentAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/studentEnrolment_manage_editProcess.php?pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightStudentEnrolmentID', $pupilsightStudentEnrolmentID);
            $form->addHiddenValue('pupilsightPersonID', $values['pupilsightPersonID']);
            $form->addHiddenValue('pupilsightRollGroupIDOriginal', $values['pupilsightRollGroupID']);
            $form->addHiddenValue('rollOrder', '');

            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $pdo->executeQuery($data, $sql);

            $schoolYearName = ($result->rowCount() == 1)? $result->fetchColumn(0) : $_SESSION[$guid]['pupilsightSchoolYearName'];

            $row = $form->addRow();
                $row->addLabel('yearName', __('School Year'));
                $row->addTextField('yearName')->readOnly()->maxLength(20)->setValue($schoolYearName);

            $row = $form->addRow();
                $row->addLabel('studentName', __('Student'));
                $row->addTextField('studentName')->readOnly()->setValue($values['officialName']);

            $row = $form->addRow();
                $row->addLabel('pupilsightProgramID', __('Program'));
                $row->addSelect('pupilsightProgramID')->fromArray($program)->selected($values['pupilsightProgramID'])->required()->placeholder();    

            $row = $form->addRow();
                $row->addLabel('pupilsightYearGroupID', __('Class'));
                $row->addSelectYearGroup('pupilsightYearGroupID')->required();

            $row = $form->addRow();
                $row->addLabel('pupilsightRollGroupID', __('Section'));
                $row->addSelect('pupilsightRollGroupID')->fromArray($sections);

            // $row = $form->addRow();
            //     $row->addLabel('rollOrder', __('Roll Order'));
            //     $row->addNumber('rollOrder')->maxLength(2);

            // Check to see if any class mappings exists -- otherwise this feature is inactive, hide it
            $sql = "SELECT COUNT(*) FROM pupilsightCourseClassMap";
            $resultClassMap = $pdo->executeQuery(array(), $sql);
            $classMapCount = ($resultClassMap->rowCount() > 0)? $resultClassMap->fetchColumn(0) : 0;

            if ($classMapCount > 0) {
                $autoEnrolDefault = getSettingByScope($connection2, 'Timetable Admin', 'autoEnrolCourses');
                $row = $form->addRow();
                    $row->addLabel('autoEnrolStudent', __('Auto-Enrol Courses?'))
                        ->description(__('Should this student be automatically enroled in courses for their Roll Group?'))
                        ->description(__('This will replace any auto-enroled courses if the student Roll Group has changed.'));
                    $row->addYesNo('autoEnrolStudent')->selected($autoEnrolDefault);
            }

            $schoolHistory = '';

            if ($values['dateStart'] != '') {
                $schoolHistory .= '<li><u>'.__('Start Date').'</u>: '.dateConvertBack($guid, $values['dateStart']).'</li>';
            }

            $dataSelect = array('pupilsightPersonID' => $values['pupilsightPersonID']);
            $sqlSelect = 'SELECT pupilsightRollGroup.name AS rollGroup, pupilsightSchoolYear.name AS schoolYear FROM pupilsightStudentEnrolment JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightPersonID=:pupilsightPersonID ORDER BY pupilsightStudentEnrolment.pupilsightSchoolYearID';
            $resultSelect = $pdo->executeQuery($dataSelect, $sqlSelect);

            while ($resultSelect && $rowSelect = $resultSelect->fetch()) {
                $schoolHistory .= '<li><u>'.$rowSelect['schoolYear'].'</u>: '.$rowSelect['rollGroup'].'</li>';
            }

            if ($values['dateEnd'] != '') {
                $schoolHistory .= '<li><u>'.__('End Date').'</u>: '.dateConvertBack($guid, $values['dateEnd']).'</li>';
            }

            $row = $form->addRow();
                $row->addLabel('schoolHistory', __('School History'));
                $row->addContent('<ul class="list-none w-full sm:max-w-xs text-xs m-0">'.$schoolHistory.'</ul>');

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
