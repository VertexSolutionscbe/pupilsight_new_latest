<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/daysOfWeek_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Alert Levels'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    try {
        $data = array();
        $sql = 'SELECT * FROM pupilsightAlertLevel ORDER BY sequenceNumber';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    //Let's go!
    $form = Form::create('alertLevelSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/alertLevelSettingsProcess.php' );

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $count = 0;
    while ($rowSQL = $result->fetch()) {
        $row = $form->addRow()->addHeading(__($rowSQL['name']));

        $form->addHiddenValue('pupilsightAlertLevelID'.$count, $rowSQL['pupilsightAlertLevelID']);

        $row = $form->addRow();
        	$row->addLabel('name'.$count, __('Name'));
    		$row->addTextField('name'.$count)
            ->setValue($rowSQL['name'])
            ->maxLength(50)
            ->required();

        $row = $form->addRow();
        	$row->addLabel('nameShort'.$count, __('Short Name'));
    		$row->addTextField('nameShort'.$count)
            ->setValue($rowSQL['nameShort'])
            ->maxLength(4)
            ->required();

        $row = $form->addRow();
        	$row->addLabel('color'.$count, __('Font/Border Color'))->description(__('RGB Hex value, without leading #.'));
    		$row->addTextField('color'.$count)
                ->setValue($rowSQL['color'])
                ->maxLength(6)
                ->required();

        $row = $form->addRow();
        	$row->addLabel('colorBG'.$count, __('Background Color'))->description(__('RGB Hex value, without leading #.'));
    		$row->addTextField('colorBG'.$count)
                ->setValue($rowSQL['colorBG'])
                ->maxLength(6)
                ->required();

        $row = $form->addRow();
        	$row->addLabel('sequenceNumber'.$count, __('Sequence Number'));
    		$row->addTextField('sequenceNumber'.$count)
            ->setValue($rowSQL['sequenceNumber'])
            ->maxLength(4)
            ->readonly()
            ->required();

        $row = $form->addRow();
        	$row->addLabel('description'.$count, __('Description'));
            $row->addTextArea('description'.$count)->setValue($rowSQL['description']);

        $count++;
    }

    $form->addHiddenValue('count', $count);

    $row = $form->addRow();
		$row->addFooter();
		$row->addSubmit();

	echo $form->getOutput();

}
