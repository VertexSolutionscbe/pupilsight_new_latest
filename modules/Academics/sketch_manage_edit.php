<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $id = $_GET['id'];
    try {
        $data = array('id' => $id);
        $sql = 'SELECT * FROM examinationReportTemplateSketch WHERE id=:id';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='error'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() != 1) {
        echo "<div class='error'>";
        echo __('The specified record cannot be found.');
        echo '</div>';
    } else {
        //Let's go!
        $values = $result->fetch();
        //Proceed!
    // print_r($values);die();

        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

        $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        $program = array();
        $program2 = array();
        $program1 = array('' => 'Select Program');
        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program = $program1 + $program2;

        $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $values['pupilsightProgramID'] . '" GROUP BY a.pupilsightYearGroupID';
        $result = $connection2->query($sql);
        $classData = $result->fetchAll();

        $classes = array();
        $classes2 = array();
        $classes1 = array('' => 'Select Class');
        foreach ($classData as $dt) {
            $classes2[$dt['pupilsightYearGroupID']] = $dt['name'];
        }
        $classes = $classes1 + $classes2;

        $page->breadcrumbs->add(__('Manage Sketch'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        echo '<h3>';
        echo __('Edit Sketch');
        echo '</h3>';

        $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/sketch_manage_editProcess.php');
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
        $form->addHiddenValue('id', $id); 
    
        $row = $form->addRow();        
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('sketch_name', __('Name'));
            $col->addTextField('sketch_name')->addClass('txtfield')->required()->setValue($values['sketch_name']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('sketch_code', __('Skill Code'));
            $col->addTextField('sketch_code')->addClass('txtfield')->required()->setValue($values['sketch_code']);

        $row = $form->addRow();         

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Program'));
            $col->addSelect('pupilsightProgramID')->fromArray($program)->required()->placeholder('Select Program')->selected($values['pupilsightProgramID']);

            $classid = explode(',', $values['class_ids']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('class_ids', __('Class'));
            $col->addSelect('class_ids')->setId('pupilsightYearGroupID')->fromArray($classes)->selectMultiple()->required()->placeholder('Select Class')->selected($classid);
            
            $col->addContent('<input type="hidden" name="pupilsightSchoolYearID" value='.$values['pupilsightSchoolYearID'].'>');    
            
        $row = $form->addRow()->setID('lastseatdiv');
            $row->addFooter();
            $row->addSubmit();

            echo $form->getOutput();
    
    }
}

?>

<style>
    .text-xxs {
        display:none;
    }

    #pupilsightYearGroupID {
        margin: 23px 41px 0 0px;
    }
    
</style>