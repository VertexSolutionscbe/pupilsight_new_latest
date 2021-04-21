<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Staff/jobOpenings_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Job Openings'), 'jobOpenings_manage.php')
        ->add(__('Edit Job Opening'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightStaffJobOpeningID = $_GET['pupilsightStaffJobOpeningID'];
    if ($pupilsightStaffJobOpeningID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightStaffJobOpeningID' => $pupilsightStaffJobOpeningID);
            $sql = 'SELECT * FROM pupilsightStaffJobOpening WHERE pupilsightStaffJobOpeningID=:pupilsightStaffJobOpeningID';
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

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/jobOpenings_manage_editProcess.php?pupilsightStaffJobOpeningID=$pupilsightStaffJobOpeningID");

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

            $row = $form->addRow();
                $column = $row->addColumn();
                $column->addLabel('description', __('Description'));
                $column->addEditor('description', $guid)->setRows(20)->showMedia()->required();

            $form->loadAllValuesFrom($values);

            $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
?>
<script type='text/javascript'>
	$(document).ready(function() {
		$("#descriptionedButtonPreview").trigger('click');
	});
</script>
