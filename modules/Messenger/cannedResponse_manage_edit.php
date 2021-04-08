<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

$page->breadcrumbs
    ->add(__('Manage Canned Responses'), 'cannedResponse_manage.php')
    ->add(__('Edit Canned Response'));

if (isActionAccessible($guid, $connection2, '/modules/Messenger/cannedResponse_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightMessengerCannedResponseID = $_GET['pupilsightMessengerCannedResponseID'];
    if ($pupilsightMessengerCannedResponseID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightMessengerCannedResponseID' => $pupilsightMessengerCannedResponseID);
            $sql = 'SELECT * FROM pupilsightMessengerCannedResponse WHERE pupilsightMessengerCannedResponseID=:pupilsightMessengerCannedResponseID';
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
            
            $form = Form::create('canneResponse', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/cannedResponse_manage_editProcess.php?pupilsightMessengerCannedResponseID='.$pupilsightMessengerCannedResponseID);
                
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

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}

?>

<script type='text/javascript'>
	$(document).ready(function() {
		$("#bodyedButtonPreview").trigger('click');
	});
</script>
