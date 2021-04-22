<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Staff/jobOpenings_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Job Openings'), 'jobOpenings_manage.php')
        ->add(__('Add Job Opening'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/jobOpenings_manage_edit.php&pupilsightStaffJobOpeningID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/jobOpenings_manage_addProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    //$types = array(__('Basic') => array ('Teaching' => __('Teaching'), 'Support' => __('Support')));
    $sql = "SELECT pupilsightRoleID as value, name FROM pupilsightRole WHERE category='Staff' ORDER BY name";
    $result = $pdo->executeQuery(array(), $sql);
    // $types[__('System Roles')] = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_KEY_PAIR) : array();
    $types = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_KEY_PAIR) : array();
    $row = $form->addRow();
        $row->addLabel('type', __('Type'));
        $row->addSelect('type')->fromArray($types)->placeholder()->required();

    $row = $form->addRow();
        $row->addLabel('jobTitle', __('Job Title'));
        $row->addTextField('jobTitle')->maxlength(100)->required();

    $row = $form->addRow();
        $row->addLabel('dateOpen', __('Opening Date'));
        $row->addDate('dateOpen')->required();

    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
        $row->addYesNo('active')->required();

    $jobOpeningDescriptionTemplate = getSettingByScope($connection2, 'Staff', 'jobOpeningDescriptionTemplate');
    $row = $form->addRow();
        $column = $row->addColumn();
        $column->addLabel('description', __('Description'));
        $column->addEditor('description', $guid)->setRows(20)->showMedia()->setValue($jobOpeningDescriptionTemplate)->required();

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();
}
?>
<script type='text/javascript'>
	$(document).ready(function() {
        $("#descriptionedButtonHTML").hide();
        $("#descriptionedButtonPreview").hide();
		$("#descriptionedButtonPreview").trigger('click');
	});
</script>