<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/firstAidRecord_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $page->breadcrumbs
            ->add(__('First Aid Records'), 'firstAidRecord.php')
            ->add(__('Add'));

        $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'] ?? '';
        $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'] ?? '';

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
    
        $editLink = '';
        $editID = '';
        if (isset($_GET['editID'])) {
            $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/firstAidRecord_edit.php&pupilsightFirstAidID='.$_GET['editID'].'&pupilsightRollGroupID='.$pupilsightRollGroupID.'&pupilsightYearGroupID='.$pupilsightYearGroupID;
            $editID = $_GET['editID'];
        }
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], $editLink, array('warning1' => __('Your request was successful, but some data was not properly saved.'), 'success1' => __('Your request was completed successfully. You can now add extra information below if you wish.')));
        }
        echo '<input type="hidden" id="pupilsightSchoolYearID" value="'.$_SESSION[$guid]['pupilsightSchoolYearID'].'">';

        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/firstAidRecord_addProcess.php?pupilsightRollGroupID='.$pupilsightRollGroupID.'&pupilsightYearGroupID='.$pupilsightYearGroupID);

        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        $row = $form->addRow();
            $row->addLabel('pupilsightProgramID', __('Program'));
            $row->addSelect('pupilsightProgramID')->fromArray($program)->placeholder('Select Program');
    
    
        $row = $form->addRow();
            $row->addLabel('pupilsightYearGroupID', __('Class'));
            $row->addSelect('pupilsightYearGroupID')->placeholder('Select Class');
    
            
        $row = $form->addRow();
            $row->addLabel('pupilsightRollGroupID', __('Section'));
            $row->addSelect('pupilsightRollGroupID')->placeholder('Select Section'); 

        $row = $form->addRow();
            $row->addLabel('pupilsightPersonID', __('Patient'));
            $row->addSelect('pupilsightPersonID')->placeholder('Select Student')->required();

        $row = $form->addRow();
            $row->addLabel('name', __('First Aider'));
            $row->addTextField('name')->setValue(Format::name('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Student'))->required()->readonly();

        $row = $form->addRow();
            $row->addLabel('date', __('Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
            $row->addDate('date')->setValue(date($_SESSION[$guid]['i18n']['dateFormatPHP']))->required();

        $row = $form->addRow();
            $row->addLabel('timeIn', __('Time In'))->description("Format: hh:mm (24hr)");
            $row->addTime('timeIn')->setValue(date("H:i"))->required();

        $row = $form->addRow();
            $column = $row->addColumn();
            $column->addLabel('description', __('Description'));
            $column->addTextArea('description')->setRows(8)->setClass('fullWidth');

        $row = $form->addRow();
            $column = $row->addColumn();
            $column->addLabel('actionTaken', __('Action Taken'));
            $column->addTextArea('actionTaken')->setRows(8)->setClass('fullWidth');

        $row = $form->addRow();
            $column = $row->addColumn();
            $column->addLabel('followUp', __('Follow Up'));
            $column->addTextArea('followUp')->setRows(8)->setClass('fullWidth');

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();
    }
}
?>

<script>

    $(document).ready(function(){
        $("#pupilsightPersonID").select2();
    });

</script>