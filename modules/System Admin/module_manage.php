<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Domain\DataSet;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\System\ModuleGateway;

require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/module_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Modules'));

    $returns = array();
    $returns['warning0'] = __("Uninstall was successful. You will still need to remove the module's files yourself.");
    $returns['error5'] = __('Install failed because either the module name was not given or the manifest file was invalid.');
    $returns['error6'] = __('Install failed because a module with the same name is already installed.');
    $returns['warning1'] = __('Install failed, but module was added to the system and set non-active.');
    $returns['warning2'] = __('Install was successful, but module could not be activated.');
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, $returns);
    }

    if (!empty($_SESSION[$guid]['moduleInstallError'])) {
        echo "<div class='alert alert-danger'>";
        echo __('The following SQL statements caused errors:').' '.$_SESSION[$guid]['moduleInstallError'];
        echo '</div>';
        $_SESSION[$guid]['moduleInstallError'] = null;
    }

    echo "<div class='message'>";
    echo sprintf(__('To install a module, upload the module folder to %1$s on your server and then refresh this page. After refresh, the module should appear in the list below: use the install button in the Actions column to set it up.'), '<b><u>'.$_SESSION[$guid]['absolutePath'].'/modules/</u></b>');
    echo '</div>';

    echo '<h2>';
    echo __('Installed');
    echo '</h2>';

    //Get list of modules in /modules directory
    // closed by bikash $moduleFolders = glob($_SESSION[$guid]['absolutePath'].'/modules/*', GLOB_ONLYDIR);
    $moduleFolders = glob('/xampp/htdocs/pupilsight/modules/*', GLOB_ONLYDIR);
    $moduleGateway = $container->get(ModuleGateway::class);
    $criteria = $moduleGateway->newQueryCriteria()
        ->sortBy('name')
        ->fromPOST();

    $modules = $moduleGateway->queryModules($criteria);
    $moduleNames = $moduleGateway->getAllModuleNames();
    $orphans = array();

    // Build a set of module data, flagging orphaned modules that do not appear to be in the modules folder.
    // Also checks for available updates by comparing version numbers for Additional modules.
    $modules->transform(function (&$module) use ($guid, $version, &$orphans, &$moduleFolders) {
        if (array_search($_SESSION[$guid]['absolutePath'].'/modules/'.$module['name'], $moduleFolders) === false) {
            $module['orphaned'] = true;
            $orphans[] = $module;
            return;
        }

        $module['status'] = __('Installed');
        $module['name'] = $module['type'] == 'Core' ? __($module['name']) : __($module['name'], $module['name']);
        $module['versionDisplay'] = $module['type'] == 'Core' ? 'v'.$version : 'v'.$module['version'];
        
        if ($module['type'] == 'Additional') {
            $versionFromFile = getModuleVersion($module['name'], $guid);
            if (version_compare($versionFromFile, $module['version'], '>')) {
                $module['status'] = '<b>'.__('Update').' '.__('Available').'</b><br/>';
                $module['update'] = true;
            }
        }
    });

    // Build a set of uninstalled modules by checking the $modules DataSet.
    // Validates the manifest file and grabs the module details from there.
    $uninstalledModules = array_reduce($moduleFolders, function($group, $modulePath) use ($guid, &$moduleNames) {
        // closed by bikash $moduleName = substr($modulePath, strlen($_SESSION[$guid]['absolutePath'].'/modules/'));
        
        $moduleName = substr($modulePath, strlen('/xampp/htdocs/pupilsight/modules/'));
        if (!in_array($moduleName, $moduleNames)) {
            $module = getModuleManifest($moduleName, $guid);
            $module['status'] = __('Not Installed');
            $module['versionDisplay'] = !empty($module['version']) ? 'v'.$module['version'] : '';
            
            if (!$module || !$module['manifestOK']) {
                $module['name'] = $moduleName;
                $module['status'] = __('Error');
                $module['description'] = __('Module error due to incorrect manifest file or folder name.');
            }
            $group[] = $module;
        }

        return $group;
    }, array());


    // INSTALLED MODULES
    $table = DataTable::createPaginated('moduleManage', $criteria);

    $table->modifyRows(function ($module, $row) {
        if (!empty($module['orphaned'])) return '';
        if (!empty($module['update'])) $row->addClass('current');
        if ($module['active'] == 'N') $row->addClass('error');
        return $row;
    });

    $table->addMetaData('filterOptions', [
        'type:core'       => __('Type').': '.__('Core'),
        'type:additional' => __('Type').': '.__('Additional'),
        'active:Y' => __('Active').': '.__('Yes'),
        'active:N' => __('Active').': '.__('No'),
    ]);

    $table->addColumn('name', __('Name'));
    $table->addColumn('status', __('Status'))->notSortable();
    $table->addColumn('description', __('Description'))->translatable();
    $table->addColumn('type', __('Type'))->translatable();
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', 'active'));
    $table->addColumn('versionDisplay', __('Version'))->sortable(['version']);
    
    $table->addActionColumn()
        ->addParam('pupilsightModuleID')
        ->format(function ($row, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/System Admin/module_manage_edit.php');

            if ($row['type'] != 'Core') {
                $actions->addAction('uninstall', __('Uninstall'))
                        ->setIcon('garbage')
                        ->setURL('/modules/System Admin/module_manage_uninstall.php');

                $actions->addAction('update', __('Update'))
                        ->setIcon('delivery2')
                        ->setURL('/modules/System Admin/module_manage_update.php');
            }
        });

    echo $table->render($modules);

    // UNINSTALLED MODULES
    if (!empty($uninstalledModules)) {
        echo '<h2>';
        echo __('Not Installed');
        echo '</h2>';
        
        $table = DataTable::create('moduleInstall');

        $table->modifyRows(function ($module, $row) {
            $row->addClass($module['manifestOK'] == false ? 'error' : 'warning');
            return $row;
        });

        $table->addColumn('name', __('Name'));
        $table->addColumn('status', __('Status'))->notSortable();
        $table->addColumn('description', __('Description'));
        $table->addColumn('versionDisplay', __('Version'));
        
        $table->addActionColumn()
            ->addParam('name')
            ->format(function ($row, $actions) {
                if ($row['manifestOK']) {
                    $actions->addAction('install', __('Install'))
                            ->setIcon('page_new')
                            ->directLink()
                            ->setURL('/modules/System Admin/module_manage_installProcess.php');
                }
            });

        echo $table->render(new DataSet($uninstalledModules));
    }

    // ORPHANED MODULES
    if ($orphans) {
        echo '<h2>';
        echo __('Orphaned Modules');
        echo '</h2>';

        echo '<p>';
        echo __('These modules are installed in the database, but are missing from within the file system.');
        echo '</p>';

        $table = DataTable::create('moduleOrphans');

        $table->addColumn('name', __('Name'));

        $table->addActionColumn()
            ->addParam('pupilsightModuleID')
            ->format(function ($row, $actions) {
                if ($row['type'] != 'Core') {
                    $actions->addAction('uninstall', __('Remove Record'))
                        ->setIcon('garbage')
                        ->addParam('orphaned', 'true')
                        ->setURL('/modules/System Admin/module_manage_uninstall.php');
                }
            });

        echo $table->render(new DataSet($orphans));
    }
}
