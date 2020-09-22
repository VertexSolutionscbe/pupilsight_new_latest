<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Domain\System\I18nGateway;

if (isActionAccessible($guid, $connection2, '/modules/System Admin/i18n_manage.php') == false) {
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
    $pupilsighti18nID = isset($_GET['pupilsighti18nID'])? $_GET['pupilsighti18nID'] : '';
    $mode = isset($_GET['mode'])? $_GET['mode'] : 'install';

    if (empty($pupilsighti18nID)) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        
        $i18nGateway = $container->get(I18nGateway::class);

        $i18n = $i18nGateway->getI18nByID($pupilsighti18nID);

        if (empty($i18n)) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {

            $form = Form::create('install', $_SESSION[$guid]['absoluteURL'].'/modules/System Admin/i18n_manage_installProcess.php');
            $form->addHiddenValue('address', $_GET['q']);
            $form->addHiddenValue('pupilsighti18nID', $pupilsighti18nID);

            $row = $form->addRow();
                $col = $row->addColumn();
                $col->addContent( ($mode == 'update'? __('Update') : __('Install')).' '.$i18n['name'])->wrap('<strong style="font-size: 18px;">', '</strong><br/><br/>');
                $col->addContent(sprintf(__('This action will download the required files and place them in the %1$s folder on your server.'), '<b>'.$_SESSION[$guid]['absolutePath'].'/i18n/'.'</b>').' '.__('Are you sure you want to continue?'));

            $form->addRow()->addConfirmSubmit();

            echo $form->getOutput();
        }
    }
}
