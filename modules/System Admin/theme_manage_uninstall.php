<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

$orphaned = '';
if (isset($_GET['orphaned'])) {
    if ($_GET['orphaned'] == 'true') {
        $orphaned = 'true';
    }
}

if (isActionAccessible($guid, $connection2, '/modules/System Admin/theme_manage_uninstall.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Themes'), 'theme_manage.php')
        ->add(__('Uninstall Theme'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightThemeID = $_GET['pupilsightThemeID'];
    if ($pupilsightThemeID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightThemeID' => $pupilsightThemeID);
            $sql = "SELECT * FROM pupilsightTheme WHERE pupilsightThemeID=:pupilsightThemeID AND active='N'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo 'The specified theme cannot be found or is active and so cannot be removed.';
            echo '</div>';
        } else {
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/theme_manage_uninstallProcess.php?pupilsightThemeID=$pupilsightThemeID&orphaned=$orphaned");
            echo $form->getOutput();
        }
    }
}
