<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Students/leaveApply.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Leave History'), 'leaveHistory.php')
        ->add(__('Apply Leave'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/leaveReason_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    
    echo '<h2>';
    echo __('Apply Leave');
    echo '</h2>';


    $sqlp = 'SELECT id, name FROM pupilsightLeaveReason ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Leave Reason');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['id']] = $dt['name'];
    }
    $reason = $program1 + $program2;

    $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];

    $roleID = $_SESSION[$guid]['pupilsightRoleIDPrimary'];

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/leaveApplyProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    if($roleID == '003'){
        $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
    } else {

        $sql = 'SELECT a.pupilsightPersonID2, b.officialName FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID2 = b.pupilsightPersonID WHERE a.pupilsightPersonID1 = '.$_SESSION[$guid]['pupilsightPersonID'].' ';
        $result = $connection2->query($sql);
        $childData = $result->fetchAll();

        $child = array();
        $child2 = array();
        $child1 = array('' => 'Select Student');
        foreach ($childData as $dt) {
            $child2[$dt['pupilsightPersonID2']] = $dt['officialName'];
        }
        $child = $child1 + $child2;

        $row = $form->addRow();
            $row->addLabel('pupilsightPersonID', __('Student'));
            $row->addSelect('pupilsightPersonID')->fromArray($child)->required();
    }

    

    $row = $form->addRow();
        $row->addLabel('pupilsightLeaveReasonID', __('Leave Reason'));
        $row->addSelect('pupilsightLeaveReasonID')->fromArray($reason)->required();

    $row = $form->addRow();
        $row->addLabel('from_date', __('From Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
        $row->addDate('from_date')->required()->readonly();

    $row = $form->addRow();
        $row->addLabel('to_date', __('To Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
        $row->addDate('to_date')->required()->readonly();

    $row = $form->addRow();
        $row->addLabel('remarks', __('Remarks'));
        $row->addTextArea('remarks');

    // $row = $form->addRow();
    //     $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
    //     $row->addSequenceNumber('sequenceNumber', 'pupilsightLeave Reason')->required()->maxLength(3);

        
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
?>
<script>
    $("#from_date").datepicker({
        minDate: 0,
        onClose: function (selectedDate) {
            $("#to_date").datepicker("option", "minDate", selectedDate);
        }
    });

    $("#to_date").datepicker({
        minDate: 0,
    });
</script>
