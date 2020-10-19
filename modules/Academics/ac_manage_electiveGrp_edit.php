<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_electiveGrp_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $id = $_GET['id'];
    // print_r($id) ;die();

    try {
        $data = array('id' => $id);
        $sql = 'SELECT * FROM  ac_elective_group WHERE id=:id';
        $result = $connection2->prepare($sql);
        $result->execute($data);

        $sqlsec = 'SELECT * FROM ac_elective_group_section WHERE ac_elective_group_id = "' . $id . '" ';
        $resultsqlsec = $connection2->query($sqlsec);
        $rowdatasqlsec = $resultsqlsec->fetch();
    } catch (PDOException $e) {
        echo "<div class='error'>" . $e->getMessage() . '</div>';
    }

    if ($result->rowCount() != 1) {
        echo "<div class='error'>";
        echo __('The specified record cannot be found.');
        echo '</div>';
    } else {
        //Let's go!
        $values = $result->fetch();
        // $sectionID = $values['pupilsightMappingID'];
        //Proceed!
        // print_r($sectionID);die();


        $page->breadcrumbs->add(__('Manage Elective Group'), 'manage_elective_group.php')->add(__('Edit Elective Group'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/ac_manage_electiveGrp_editProcess.php');
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('id', $id);
        $form->addHiddenValue('pupilsightSchoolYearID', $values['pupilsightSchoolYearID']);
        $form->addHiddenValue('pupilsightProgramID', $values['pupilsightProgramID']);
        $form->addHiddenValue('pupilsightYearGroupID', $values['pupilsightYearGroupID']);

        $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('elective_name', __('Elective Group Name'));
        $col->addTextField('elective_name')->addClass('txtfield')->required()->setValue($values['name']);

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('order_no', __('Order Number'));
        $col->addTextField('order_no')->addClass('txtfield')->required()->setValue($values['order_no']);

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('max_selection', __('Max Selection'));
        $col->addNumber('max_selection')->addClass('txtfield')->required()->setValue($values['max_selection']);

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('min_selection', __('Min Selection'));
        $col->addNumber('min_selection')->addClass('txtfield')->required()->setValue($values['min_selection']);

        if (!empty($rowdatasqlsec)) {
            $chk = 'checked';
            $stl = '';
            $stl1 = 'style="display:none;"';
        } else {
            $chk = '';
            $stl = 'style="display:none;"';
            $stl1 = '';
        }

        $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addContent('<input type= "checkbox" class="enableLinkbychkBox" ' . $chk . '>  Section Specified &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="enableLink" ' . $stl . ' href="fullscreen.php?q=/modules/Academics/section_specify.php&pid=' . $values['pupilsightProgramID'] . '&cid=' . $values['pupilsightYearGroupID'] . '&eid=' . $id . '" class="thickbox  btn btn-primary">Select Section</a><a id="disableLink" ' . $stl1 . ' class="btn btn-primary">Select Section</a>');

        $col = $row->addColumn()->setClass('newdes');
        $col->addContent('<a id="" href="fullscreen.php?q=/modules/Academics/subject_specify.php&pid=' . $values['pupilsightProgramID'] . '&cid=' . $values['pupilsightYearGroupID'] . '&eid=' . $id . '" class="thickbox  btn btn-primary">Assign Subject</a>');


        // $row = $form->addRow();
        // $col = $row->addColumn()->setClass('newdes');
        // $col->addContent('<input type= "checkbox"> Applicable to Specialization <button class="btn btn-primary">Select Specification</button> ');


        // $row->addButton('Add Seats')->addData('class', 'addSeats')->addClass('submt');
        $col->addLabel('', __(''))->addClass('dte');
        $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit();

        echo $form->getOutput();
    }
}
