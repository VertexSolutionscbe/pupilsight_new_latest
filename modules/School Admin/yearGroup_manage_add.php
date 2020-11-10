<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/yearGroup_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    
    $page->breadcrumbs
        ->add(__('Manage Class'), 'yearGroup_manage.php')
        ->add(__('Add Class'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/School Admin/yearGroup_manage_edit.php&pupilsightYearGroupID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }

    $sql = 'SELECT sequenceNumber FROM pupilsightYearGroup WHERE pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ORDER BY pupilsightYearGroupID DESC LIMIT 0,1 ';
    $result = $connection2->query($sql);
    $sqNoData = $result->fetch();
    if(!empty($sqNoData)){
        $newSqNo = $sqNoData['sequenceNumber'] + 1;
    } else {
        $newSqNo = 1;
    }
    

    $form = Form::create('yearGroup', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/yearGroup_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
            $row->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->required()->placeholder();

    $row = $form->addRow();
        $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
        $row->addTextField('name')->required();

    $row = $form->addRow();
        $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique.'));
        $row->addTextField('nameShort')->required();

    $row = $form->addRow();
        $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
        // $row->addSequenceNumber('sequenceNumber', 'pupilsightYearGroup')->required()->maxLength(3);
        $row->addTextField('sequenceNumber')->required()->maxLength(3)->setValue($newSqNo);

    $row = $form->addRow()->setClass('hiddencol');
        $row->addLabel('pupilsightPersonIDHOY', __('Head of Year'));
        $row->addSelectStaff('pupilsightPersonIDHOY')->placeholder();
        
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}

?>

<script>
    $(document).on('change', '#pupilsightSchoolYearID', function() {
        var id = $(this).val();
        var type = 'getSequenceNoByAcademicYear';
        if (id != "") {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: id, type: type},
                async: true,
                success: function(response) {
                    $("#sequenceNumber").val('');
                    $("#sequenceNumber").val(response);
                }
            });
        } else {
            alert('Please Select Academic Year');
        }
    });
</script>