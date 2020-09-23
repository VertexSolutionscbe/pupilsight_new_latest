<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/systemCheck.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('System Check'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $versionDB = getSettingByScope($connection2, 'System', 'version');

    $trueIcon = "<img title='" . __('Yes'). "' src='".$_SESSION[$guid]["absoluteURL"]."/themes/".$_SESSION[$guid]["pupilsightThemeName"]."/img/iconTick.png' style='width:20px;height:20px;margin-right:10px' />";
    $falseIcon = "<img title='" . __('No'). "' src='".$_SESSION[$guid]["absoluteURL"]."/themes/".$_SESSION[$guid]["pupilsightThemeName"]."/img/iconCross.png' style='width:20px;height:20px;margin-right:10px' />";

    $versionTitle = __('%s Version');
    $versionMessage = __('%s requires %s version %s or higher');

    $phpVersion = phpversion();
    $apacheVersion = function_exists('apache_get_version')? apache_get_version() : false;
    $mysqlVersion = $pdo->selectOne("SELECT VERSION()");
    $mysqlCollation = $pdo->selectOne("SELECT COLLATION('pupilsight')");

    $phpRequirement = $pupilsight->getSystemRequirement('php');
    $mysqlRequirement = $pupilsight->getSystemRequirement('mysql');
    $apacheRequirement = $pupilsight->getSystemRequirement('apache');
    $extensions = $pupilsight->getSystemRequirement('extensions');
    $settings = $pupilsight->getSystemRequirement('settings');

    // File Check
    $fileCount = 0;
    $publicWriteCount = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($_SESSION[$guid]["absolutePath"])) as $filename)
    {
        if (pathinfo($filename, PATHINFO_EXTENSION) != 'php') continue;
        if (strpos(pathinfo($filename, PATHINFO_DIRNAME), '/uploads') !== false) continue;
        if (fileperms($filename) & 0x0002) $publicWriteCount++;
        $fileCount++;
    }

    $form = Form::create('systemCheck', "")->setClass('smallIntBorder w-full');

    $form->addRow()->addHeading(__('System Requirements'));

    $row = $form->addRow();
        $row->addLabel('phpVersionLabel', sprintf($versionTitle, 'PHP'))->description(sprintf($versionMessage, __('Pupilsight').' v'.$version, 'PHP', $phpRequirement));
        $row->addTextField('phpVersion')->setValue($phpVersion)->readonly();
        $row->addContent((version_compare($phpVersion, $phpRequirement, '>='))? $trueIcon : $falseIcon);

    $row = $form->addRow();
        $row->addLabel('mysqlVersionLabel', sprintf($versionTitle, 'MySQL'))->description(sprintf($versionMessage, __('Pupilsight').' v'.$version, 'MySQL', $mysqlRequirement));
        $row->addTextField('mysqlVersion')->setValue($mysqlVersion)->readonly();
        $row->addContent((version_compare($mysqlVersion, $mysqlRequirement, '>='))? $trueIcon : $falseIcon);

    $row = $form->addRow();
        $row->addLabel('mysqlCollationLabel', __('MySQL Collation'))->description(sprintf( __('Database collation should be set to %s'), 'utf8_general_ci'));
        $row->addTextField('mysqlCollation')->setValue($mysqlCollation)->readonly();
        $row->addContent(($mysqlCollation == 'utf8_general_ci')? $trueIcon : $falseIcon);

    $row = $form->addRow();
        $row->addLabel('pdoSupportLabel', __('MySQL PDO Support'));
        $row->addTextField('pdoSupport')->setValue((@extension_loaded('pdo_mysql'))? __('Installed') : __('Not Installed'))->readonly();
        $row->addContent((@extension_loaded('pdo') && extension_loaded('pdo_mysql'))? $trueIcon : $falseIcon);

    // APACHE MODULES
    if ($apacheVersion !== false) {
        $form->addRow()->addHeading(__('Apache Modules'));

        $apacheModules = @apache_get_modules();
        foreach ($apacheRequirement as $moduleName) {
            $active = @in_array($moduleName, $apacheModules);
            $row = $form->addRow();
                $row->addLabel('moduleLabel', $moduleName);
                $row->addTextField('module')->setValue(($active)? __('Enabled') : __('N/A'))->readonly();
                $row->addContent(($active)? $trueIcon : $falseIcon);
        }
    }

    // PHP EXTENSIONS
    if (!empty($extensions) && is_array($extensions)) {
        $form->addRow()
            ->addHeading(__('PHP Extensions'))
            ->append(__('Pupilsight requires you to enable the PHP extensions in the following list. The process to do so depends on your server setup.'));

        foreach ($extensions as $extension) {
            $installed = @extension_loaded($extension);
            $row = $form->addRow();
                $row->addLabel('extensionLabel', $extension);
                $row->addTextField('extension')->setValue(($installed)? __('Installed') : __('Not Installed'))->readonly();
                $row->addContent(($installed)? $trueIcon : $falseIcon);
        }
    }

    // PHP SETTINGS
    if (!empty($settings) && is_array($settings)) {
        $form->addRow()
            ->addHeading(__('PHP Settings'))
            ->append(sprintf(__('Configuration values can be set in your system %s file. On shared host, use %s to set php settings.'), '<code>php.ini</code>', '.htaccess'));

        foreach ($settings as $settingDetails) { 
            if (!is_array($settingDetails) || count($settingDetails) != 3) continue;
            list($setting, $operator, $compare) = $settingDetails;
            $value = @ini_get($setting);

            $isValid = ($operator == '==' && $value == $compare) 
                || ($operator == '>=' && $value >= $compare) 
                || ($operator == '<=' && $value <= $compare) 
                || ($operator == '>' && $value > $compare) 
                || ($operator == '<' && $value < $compare);

            $row = $form->addRow();
                $row->addLabel('settingLabel', '<b>'.$setting.'</b> <small>'.$operator.' '.$compare.'</small>');
                $row->addTextField('setting')->setValue($value)->readonly();
                $row->addContent($isValid? $trueIcon : $falseIcon);
        }
    }

    // FILE PERMS
    $form->addRow()->addHeading(__('File Permissions'));

    $row = $form->addRow();
        $row->addLabel('systemWriteLabel', __('System not publicly writeable'));
        $row->addTextField('systemWrite')->setValue(sprintf(__('%s files checked (%s publicly writeable)'), $fileCount, $publicWriteCount))->readonly();
        $row->addContent($publicWriteCount == 0? $trueIcon : $falseIcon);

    $row = $form->addRow();
        $row->addLabel('uploadsFolderLabel', __('Uploads folder server writeable'));
        $row->addTextField('uploadsFolder')->setValue($_SESSION[$guid]['absoluteURL'].'/uploads')->readonly();
        $row->addContent(is_writable($_SESSION[$guid]['absolutePath'].'/uploads')? $trueIcon : $falseIcon);


    echo $form->getOutput();


    // CLEAR CACHE
    $form = Form::create('clearCache', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/systemCheck_clearCacheProcess.php');
    $form->addClass('mt-10');

    $form->addRow()->addHeading(__('System Data'));

    $row = $form->addRow()->addSubmit(__('Clear Cache'));

    echo $form->getOutput();
}
