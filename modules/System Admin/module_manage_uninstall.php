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

if (isActionAccessible($guid, $connection2, '/modules/System Admin/module_manage_uninstall.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Modules'), 'module_manage.php')
        ->add(__('Uninstall Module'));

    if (isset($_GET['deleteReturn'])) {
        $deleteReturn = $_GET['deleteReturn'];
    } else {
        $deleteReturn = '';
    }
    $deleteReturnMessage = '';
    $class = 'error';
    if (!($deleteReturn == '')) {
        if ($deleteReturn == 'fail0') {
            $deleteReturnMessage = __('Your request failed because you do not have access to this action.');
        } elseif ($deleteReturn == 'fail1') {
            $deleteReturnMessage = __('Your request failed because your inputs were invalid.');
        } elseif ($deleteReturn == 'fail2') {
            $deleteReturnMessage = __('Your request failed because your inputs were invalid.');
        } elseif ($deleteReturn == 'fail3') {
            $deleteReturnMessage = __('Uninstall encountered a partial fail: the module may or may not still work.');
        }
        echo "<div class='$class'>";
        echo $deleteReturnMessage;
        echo '</div>';
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
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch(); 
            
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/module_manage_uninstallProcess.php?pupilsightModuleID=$pupilsightModuleID&orphaned=$orphaned", false, false);

            $manifestFile = $_SESSION[$guid]['absolutePath'].'/modules/'.$values['name'].'/manifest.php';
            if (file_exists($manifestFile)) {
                include $manifestFile;
            } else if (!$orphaned) {
                $form->addRow()->addAlert(__('An error has occurred.').' '.__('Module error due to incorrect manifest file or folder name.'), 'error');
            }

            if (!empty($moduleTables)) {
                $moduleTables = array_map('trim', $moduleTables);
                $moduleTables = array_reduce($moduleTables, function($group, $moduleTable) {
                    $tokens = preg_split('/ +/', $moduleTable);

                    if ($tokens === false || empty($tokens[0]) || empty($tokens[1]) || empty($tokens[2])) return $group;
                    if (strtoupper($tokens[0]) == 'CREATE' && (strtoupper($tokens[1]) == 'TABLE' || strtoupper($tokens[1]) == 'VIEW')) {
                        $type = ucfirst(strtolower($tokens[1]));
                        $name = str_replace('`', '', $tokens[2]);
                        $group[$type.'-'.$name] = '<b>'.__($type).'</b>: '.$name;
                    }
        
                    return $group;
                }, array());

                $row = $form->addRow();
                    $row->addLabel('remove', __('Remove Data'))->description(__('Would you like to remove the following tables and views from your database?'));
                    $row->addCheckbox('remove')->fromArray($moduleTables)->checkAll()->addCheckAllNone();
            }

            $form->addRow()->addConfirmSubmit();
            
            echo $form->getOutput();
        }
    }
}
