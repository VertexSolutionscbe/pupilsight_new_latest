<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/permission_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Permissions'));

    $returns = array();
    $returns['error3'] = sprintf(__('Your PHP environment cannot handle all of the fields in this form (the current limit is %1$s). Ask your web host or system administrator to increase the value of the max_input_vars in php.ini.'), ini_get('max_input_vars'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, $returns);
    }

    echo '<h2>';
    echo __('Filter');
    echo '</h2>';

    $pupilsightModuleID = isset($_GET['pupilsightModuleID'])? $_GET['pupilsightModuleID'] : '';
    $pupilsightRoleID = isset($_GET['pupilsightRoleID'])? $_GET['pupilsightRoleID'] : '';

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/permission_manage.php');

    $sql = "SELECT pupilsightModuleID as value, name FROM pupilsightModule WHERE active='Y' ORDER BY name";
    $row = $form->addRow();
        $row->addLabel('pupilsightModuleID', __('Module'));
        $row->addSelect('pupilsightModuleID')->fromQuery($pdo, $sql)->selected($pupilsightModuleID)->placeholder();

    $sql = "SELECT pupilsightRoleID as value, name FROM pupilsightRole ORDER BY type, nameShort";
    $row = $form->addRow();
        $row->addLabel('pupilsightRoleID', __('Role'));
        $row->addSelect('pupilsightRoleID')->fromQuery($pdo, $sql)->selected($pupilsightRoleID)->placeholder();

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session, __('Clear Filters'));

    echo $form->getOutput();

    try {
        if (!empty($pupilsightModuleID)) {
            $dataModules = array('pupilsightModuleID' => $pupilsightModuleID);
            $sqlModules = "SELECT * FROM pupilsightModule WHERE pupilsightModuleID=:pupilsightModuleID AND active='Y'";
        } else {
            $dataModules = array();
            $sqlModules = "SELECT * FROM pupilsightModule WHERE active='Y' ORDER BY name";
        }

        $resultModules = $connection2->prepare($sqlModules);
        $resultModules->execute($dataModules);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    try {
        if (!empty($pupilsightRoleID)) {
            $dataRoles = array('pupilsightRoleID' => $pupilsightRoleID);
            $sqlRoles = 'SELECT pupilsightRoleID, nameShort, category, name FROM pupilsightRole WHERE pupilsightRoleID=:pupilsightRoleID';
        } else {
            $dataRoles = array();
            $sqlRoles = 'SELECT pupilsightRoleID, nameShort, category, name FROM pupilsightRole ORDER BY type, nameShort';
        }
        $resultRoles = $connection2->prepare($sqlRoles);
        $resultRoles->execute($dataRoles);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    try {
        $dataPermissions = array();
        $sqlPermissions = 'SELECT pupilsightRoleID, pupilsightActionID FROM pupilsightPermission';
        $resultPermissions = $connection2->prepare($sqlPermissions);
        $resultPermissions->execute($dataPermissions);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($resultRoles->rowCount() < 1 or $resultModules->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('Your request failed due to a database error.');
        echo '</div>';
    } else {
        //Fill role and permission arrays
        $roleArray = ($resultRoles->rowCount() > 0)? $resultRoles->fetchAll() : array();
        $permissionsArray = ($resultPermissions->rowCount() > 0)? $resultPermissions->fetchAll() : array();
        $totalCount = 0;

        $form = Form::create('permissions', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/permission_manageProcess.php');
        $form->setClass('w-full blank');
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('pupilsightModuleID', $pupilsightModuleID);
        $form->addHiddenValue('pupilsightRoleID', $pupilsightRoleID);

        while ($rowModules = $resultModules->fetch()) {
            $form->addRow()->addHeading(__($rowModules['name']));
            $table = $form->addRow()->addTable()->setClass('mini rowHighlight columnHighlight fullWidth');

            try {
                $dataActions = array('pupilsightModuleID' => $rowModules['pupilsightModuleID']);
                $sqlActions = 'SELECT * FROM pupilsightAction WHERE pupilsightModuleID=:pupilsightModuleID ORDER BY name';
                $resultActions = $connection2->prepare($sqlActions);
                $resultActions->execute($dataActions);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultActions->rowCount() > 0) {
                $row = $table->addHeaderRow();
                $row->addContent(__('Action'))->wrap('<div style="width: 350px;">', '</div>');

                // Add headings for each Role
                foreach ($roleArray as $role) {
                    $row->addContent(__($role['nameShort']))->wrap('<span title="'.htmlPrep(__($role['name'])).'">', '</span>');
                }

                while ($rowActions = $resultActions->fetch()) {
                    $row = $table->addRow();

                    // Add names and hover-over descriptions for each Action
                    if ($rowModules['type'] == 'Core') {
                        $row->addContent(__($rowActions['name']))->wrap('<span title="'.htmlPrep(__($rowActions['description'])).'">', '</span>');
                    } else {
                        $row->addContent(__($rowActions['name']), $rowModules['name'])->wrap('<span title="'.htmlPrep(__($rowActions['description'], $rowModules['name'])).'">', '</span>');
                    }

                    foreach ($roleArray as $role) {
                        $checked = false;

                        // Check to see if the current action is turned on
                        foreach ($permissionsArray as $permission) {
                            if ($permission['pupilsightRoleID'] == $role['pupilsightRoleID'] && $permission['pupilsightActionID'] == $rowActions['pupilsightActionID']) {
                                $checked = true;
                            }
                        }

                        $readonly = ($rowActions['categoryPermission'.$role['category']] == 'N');
                        $checked = !$readonly && $checked;

                        $name = 'permission['.$rowActions['pupilsightActionID'].']['.$role['pupilsightRoleID'].']';
                        $row->addCheckbox($name)->setDisabled($readonly)->checked($checked)->setClass('');

                        ++$totalCount;
                    }
                }
            }
        }

        $form->addHiddenValue('totalCount', $totalCount);

        $max_input_vars = ini_get('max_input_vars');
        $total_vars = $totalCount + 10;
        $total_vars_rounded = (ceil($total_vars / 1000) * 1000) + 1000;

        if ($total_vars > $max_input_vars) {
            $row = $form->addRow();
            $row->addAlert('php.ini max_input_vars='.$max_input_vars.'<br />')
                ->append(__('Number of inputs on this page').'='.$total_vars.'<br/>')
                ->append(sprintf(__('This form is very large and data will be truncated unless you edit php.ini. Add the line <i>max_input_vars=%1$s</i> to your php.ini file on your server.'), $total_vars_rounded));
        } else {
            $row = $form->addRow();
            $row->addSubmit();
        }

        echo $form->getOutput();
    }
}
