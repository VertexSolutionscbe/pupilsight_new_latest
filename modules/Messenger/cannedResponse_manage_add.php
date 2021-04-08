<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

$page->breadcrumbs
	->add(__('Manage Canned Responses'), 'cannedResponse_manage.php')
	->add(__('Add Canned Response'));

if (isActionAccessible($guid, $connection2, '/modules/Messenger/cannedResponse_manage_add.php') == false) {
	//Acess denied
	echo "<div class='alert alert-danger'>";
	echo __('You do not have access to this action.');
	echo '</div>';
} else {
	//Proceed!
	$editLink = '';
	if (isset($_GET['editID'])) {
		$editLink = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Messenger/cannedResponse_manage_edit.php&pupilsightMessengerCannedResponseID=' . $_GET['editID'];
	}
	if (isset($_GET['return'])) {
		returnProcess($guid, $_GET['return'], $editLink, null);
	}

	$form = Form::create('canneResponse', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/cannedResponse_manage_addProcess.php');

	$form->addHiddenValue('address', $_SESSION[$guid]['address']);

	$row = $form->addRow();
	$row->addLabel('subject', __('Subject'))->description(__('Must be unique.'));
	$row->addTextField('subject')->required()->maxLength(200);

	$row = $form->addRow();
	$col = $row->addColumn('body');
	$col->addLabel('body', __('Body'));
	$col->addEditor('body', $guid)->required()->setRows(20)->showMedia(true);

	$row = $form->addRow();
	$row->addFooter();
	$row->addSubmit();

	echo $form->getOutput();
}

?>

<script type='text/javascript'>
	$(document).ready(function() {
		$("#bodyedButtonPreview").trigger('click');
	});
</script>