<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\System\I18nGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/i18n_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Languages'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array(
            'error3' => __('Failed to download and install the required files.').' '.sprintf(__('To install a language manually, upload the language folder to %1$s on your server and then refresh this page. After refreshing, the language should appear in the list below.'), '<b><u>'.$_SESSION[$guid]['absolutePath'].'/i18n/</u></b>')
            )
        );
    }

    // Update any existing languages that may have been installed manually
    i18nCheckAndUpdateVersion($container, $version);

    echo '<h2>';
    echo __('Installed');
    echo '</h2>';
    
    $i18nGateway = $container->get(I18nGateway::class);

    // CRITERIA
    $criteria = $i18nGateway->newQueryCriteria()
        ->sortBy('code')
        ->pageSize(0)
        ->fromArray($_POST);


    $languages = $i18nGateway->queryI18n($criteria, 'Y');

    $languages->transform(function(&$i18n) use ($guid)  {
        $i18n['isInstalled'] = i18nFileExists($_SESSION[$guid]['absolutePath'], $i18n['code']);
    });

    $form = Form::create('i18n_manage', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/i18n_manageProcess.php');

    $form->setClass('fullWidth');
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->setClass('w-full blank');

    // DATA TABLE
    $table = $form->addRow()->addDataTable('i18n', $criteria)->withData($languages);

    $table->addMetaData('hidePagination', true);

    $table->modifyRows(function ($i18n, $row){
        if (!$i18n['isInstalled']) return null;
        if ($i18n['active'] == 'N') $row->addClass('error');

        return $row;
    });

    $table->addColumn('name', __('Name'))->width('50%');
    $table->addColumn('code', __('Code'))->width('10%');
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', 'active'));
    $table->addColumn('default', __('Default'))
        ->notSortable()
        ->format(function($i18n) use ($form) {
            if ($i18n['active'] == 'Y') {
                $checked = ($i18n['systemDefault'] == 'Y')? $i18n['pupilsighti18nID'] : '';

                return $form->getFactory()
                    ->createRadio('pupilsighti18nID')
                    ->addClass('inline right')
                    ->fromArray(array($i18n['pupilsighti18nID'] => ''))
                    ->checked($checked)
                    ->getOutput();
            }

            return '';
        });

    $table->addActionColumn()
        ->addParam('pupilsighti18nID')
        ->format(function ($i18n, $actions) use ($version) {
            
            if (version_compare($version, $i18n['version'], '>')) {
                $actions->addAction('update', __('Update'))
                    ->setIcon('delivery2')
                    ->modalWindow(650, 135)
                    ->addParam('mode', 'update')
                    ->setURL('/modules/System Admin/i18n_manage_install.php');
            }
        });

    $table = $form->addRow()->addTable()->setClass('smallIntBorder fullWidth standardForm');
    $table->addRow()->addSubmit();

    $installedCount = array_reduce($languages->toArray(), function ($count, $i18n) {
        return ($i18n['isInstalled'])? $count + 1 : $count;
    }, 0);

    if ($installedCount == 0) {
        echo '<div class="message">';
        echo __('There are no language files installed. Your system is currently using the default language.').' '.__('Use the list below to install a new language.');
        echo '</div><br/>';
    } else {
        echo $form->getOutput();
    }


    echo '<h2>';
    echo __('Not Installed');
    echo '</h2>';

    echo '<p>';
    echo __('Inactive languages are not yet ready for use within the system as they are still under development. They cannot be set to default, nor selected by users.');
    echo '</p>';

    $languages = $i18nGateway->queryI18n($criteria, 'N');

    $languages->transform(function(&$i18n) use ($guid)  {
        $i18n['isInstalled'] = i18nFileExists($_SESSION[$guid]['absolutePath'], $i18n['code']);
    });

    // DATA TABLE
    $table = DataTable::createPaginated('i18n', $criteria);

    $table->addMetaData('hidePagination', true);

    $table->modifyRows(function ($i18n, $row) use ($guid) {
        // if ($i18n['isInstalled']) return null;
        if ($i18n['active'] == 'N') $row->addClass('error');
        return $row;
    });

    // COLUMNS
    $table->addColumn('name', __('Name'))->width('50%');
    $table->addColumn('code', __('Code'))->width('10%');
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', 'active'));

    $table->addActionColumn()
        ->addParam('pupilsighti18nID')
        ->format(function ($i18n, $actions) {
            if ($i18n['active'] == 'Y') {
                $actions->addAction('install', __('Install'))
                    ->setIcon('page_new')
                    ->modalWindow(650, 135)
                    ->addParam('mode', 'install')
                    ->setURL('/modules/System Admin/i18n_manage_install.php');
            }
        });

    echo $table->render($languages);
}
