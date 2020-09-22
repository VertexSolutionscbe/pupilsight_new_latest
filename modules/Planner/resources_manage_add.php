<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs
    ->add(__('Manage Resources'), 'resources_manage.php')
    ->add(__('Add Resource'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/resources_manage_add.php') == false) {
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
        $search = (isset($_GET['search']))? $_GET['search'] : null;

        $editLink = '';
        if (isset($_GET['editID'])) {
            $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/resources_manage_edit.php&pupilsightResourceID='.$_GET['editID'].'&search='.$_GET['search'];
        }
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], $editLink, null);
        }

        if ($search != '') {
            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/resources_manage.php&search='.$search."'>".__('Back to Search Results').'</a>';
            echo '</div>';
        }

        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/resources_manage_addProcess.php?search='.$search);
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        $form->addRow()->addHeading(__('Resource Contents'));

        $types = array('File' => __('File'), 'HTML' => __('HTML'), 'Link' => __('Link'));
        $row = $form->addRow();
            $row->addLabel('type', __('Type'));
            $row->addSelect('type')->fromArray($types)->required()->placeholder();

        // File
        $form->toggleVisibilityByClass('resourceFile')->onSelect('type')->when('File');
        $row = $form->addRow()->addClass('resourceFile');
            $row->addLabel('file', __('File'));
            $row->addFileUpload('file')->required();

        // HTML
        $form->toggleVisibilityByClass('resourceHTML')->onSelect('type')->when('HTML');
        $row = $form->addRow()->addClass('resourceHTML');
            $column = $row->addColumn()->setClass('');
            $column->addLabel('html', __('HTML'));
            $column->addEditor('html', $guid)->required();

        // Link
        $form->toggleVisibilityByClass('resourceLink')->onSelect('type')->when('Link');
        $row = $form->addRow()->addClass('resourceLink');
            $row->addLabel('link', __('Link'));
            $row->addURL('link')->maxLength(255)->required();

        $form->addRow()->addHeading(__('Resource Details'));

        $row = $form->addRow();
            $row->addLabel('name', __('Name'));
            $row->addTextField('name')->required()->maxLength(60);

        $categories = getSettingByScope($connection2, 'Resources', 'categories');
        $row = $form->addRow();
            $row->addLabel('category', __('Category'));
            $row->addSelect('category')->fromString($categories)->required()->placeholder();

        $purposesGeneral = getSettingByScope($connection2, 'Resources', 'purposesGeneral');
        $purposesRestricted = ($highestAction == 'Manage Resources_all')? getSettingByScope($connection2, 'Resources', 'purposesRestricted') : '';
        $row = $form->addRow();
            $row->addLabel('purpose', __('Purpose'));
            $row->addSelect('purpose')->fromString($purposesGeneral)->fromString($purposesRestricted)->placeholder();

        $sql = "SELECT tag as value, CONCAT(tag, ' <i>(', count, ')</i>') as name FROM pupilsightResourceTag WHERE count>0 ORDER BY tag";
        $row = $form->addRow()->addClass('tags');
            $column = $row->addColumn();
            $column->addLabel('tags', __('Tags'))->description(__('Use lots of tags!'));
            $column->addFinder('tags')
                ->fromQuery($pdo, $sql)
                ->required()
                ->setParameter('hintText', __('Type a tag...'))
                ->setParameter('allowFreeTagging', true);

        $row = $form->addRow();
            $row->addLabel('pupilsightYearGroupID', __('Year Groups'))->description(__('Students year groups which may participate'));
            $row->addCheckboxYearGroup('pupilsightYearGroupID')->checkAll()->addCheckAllNone();

        $row = $form->addRow();
            $row->addLabel('description', __('Description'));
            $row->addTextArea('description')->setRows(8);

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();
    }
}
