<?php
/*
Pupilsight, Flexible & Open School System

*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/System Admin/module_manage_update.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Modules'), 'module_manage.php')
        ->add(__('Update Module'));

    $return = null;
    if (isset($_GET['return'])) {
        $return = $_GET['return'];
    }
    $returns = array();
    $returns['warning1'] = __('Some aspects of your request failed, but others were successful. The elements that failed are shown below:');
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, $returns);
    }
    if (isset($_SESSION[$guid]['moduleUpdateError'])) {
        if ($_SESSION[$guid]['moduleUpdateError'] != '') {
            echo "<div class='alert alert-danger'>";
            echo __('The following SQL statements caused errors:').' '.$_SESSION[$guid]['moduleUpdateError'];
            echo '</div>';
        }
        $_SESSION[$guid]['moduleUpdateError'] = null;
    }

    //Check if school year specified
    $pupilsightModuleID = $_GET['pupilsightModuleID'];
    if ($pupilsightModuleID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightModuleID' => $pupilsightModuleID);
            $sql = 'SELECT * FROM pupilsightModule WHERE pupilsightModuleID=:pupilsightModuleID';
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

            $versionDB = $values['version'];
            if (file_exists($_SESSION[$guid]['absolutePath'].'/modules/'.$values['name'].'/version.php')) {
                include $_SESSION[$guid]['absolutePath'].'/modules/'.$values['name'].'/version.php';
            }
            @$versionCode = $moduleVersion;

            echo '<p>';
            echo sprintf(__('This page allows you to semi-automatically update the %1$s module to a new version. You need to take care of the file updates, and based on the new files, Pupilsight will do the database upgrades.'), htmlPrep($values['name']));
            echo '</p>';

            if ($return == 'success0') {
                echo '<p>';
                echo '<b>'.__('You seem to be all up to date, good work!').'</b>';
                echo '</p>';
            } elseif ($versionDB > $versionCode or $versionCode == '') {
                //Error
                echo "<div class='alert alert-danger'>";
                echo __('An error has occurred determining the version of the system you are using.');
                echo '</div>';
            } elseif ($versionDB == $versionCode) {
                //Instructions on how to update
                echo '<h3>';
                echo __('Update Instructions');
                echo '</h3>';
                echo '<ol>';
                echo '<li>'.sprintf(__('You are currently using %1$s v%2$s.'),  htmlPrep($values['name']), $versionCode).'</i></li>';
                echo '<li>'.sprintf(__('Check %1$s for a newer version of this module.'), "<a target='_blank' href='http://pupilsight.in/extend'>pupilsight.in</a>").'</li>';
                echo '<li>'.__('Download the latest version, and unzip it on your computer.').'</li>';
                echo '<li>'.__('Use an FTP client to upload the new files to your server\'s modules folder.').'</li>';
                echo '<li>'.__('Reload this page and follow the instructions to update your database to the latest version.').'</li>';
                echo '</ol>';
            } elseif ($versionDB < $versionCode) {
                //Time to update
                echo '<h3>';
                echo __('Database Update');
                echo '</h3>';
                echo '<p>';
                echo sprintf(__('It seems that you have updated your %1$s module code to a new version, and are ready to update your database from v%2$s to v%3$s. <b>Click "Submit" below to continue. This operation cannot be undone: backup your entire database prior to running the update!'), htmlPrep($values['name']), $versionDB, $versionCode).'</b>';
                echo '</p>'; 
                
                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/module_manage_updateProcess.php?&pupilsightModuleID='.$pupilsightModuleID);
                
                $form->addHiddenValue('versionDB', $versionDB);
                $form->addHiddenValue('versionCode', $versionCode);
                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                $form->addRow()->addSubmit();
                echo $form->getOutput(); 
            }
        }
    }
}
