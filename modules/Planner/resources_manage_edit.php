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
    ->add(__('Edit Resource'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/resources_manage_edit.php') == false) {
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
        //Proceed!
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $pupilsightResourceID = $_GET['pupilsightResourceID'];
        if ($pupilsightResourceID == 'Y') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Manage Resources_all') {
                    $data = array('pupilsightResourceID' => $pupilsightResourceID);
                    $sql = 'SELECT pupilsightResource.*, surname, preferredName, title FROM pupilsightResource JOIN pupilsightPerson ON (pupilsightResource.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) AND pupilsightResourceID=:pupilsightResourceID ORDER BY timestamp DESC';
                } elseif ($highestAction == 'Manage Resources_my') {
                    $data = array('pupilsightResourceID' => $pupilsightResourceID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = 'SELECT pupilsightResource.*, surname, preferredName, title FROM pupilsightResource JOIN pupilsightPerson ON (pupilsightResource.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightResource.pupilsightPersonID=:pupilsightPersonID AND pupilsightResourceID=:pupilsightResourceID ORDER BY timestamp DESC';
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                //Let's go!
                $values = $result->fetch();
                $values['pupilsightYearGroupID'] = explode(',', $values['pupilsightYearGroupIDList']);

                $search = (isset($_GET['search']))? $_GET['search'] : null;

                if (!empty($search)) {
                    echo "<div class='linkTop'>";
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/resources_manage.php&search='.$search."'>".__('Back to Search Results').'</a>';
                    echo '</div>';
                }

                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/resources_manage_editProcess.php?pupilsightResourceID='.$pupilsightResourceID.'&search='.$search);
                $form->setFactory(DatabaseFormFactory::create($pdo));

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('type', $values['type']);

                $form->addRow()->addHeading(__('Resource Contents'));

                if ($values['type'] == 'File') {
                    // File
                    $row = $form->addRow()->addClass('resourceFile');
                        $row->addLabel('file', __('File'));
                        $row->addFileUpload('file')
                            ->required()
                            ->setAttachment('content', $_SESSION[$guid]['absoluteURL'], $values['content']);
                } else if ($values['type'] == 'HTML') {
                    // HTML
                    $row = $form->addRow()->addClass('resourceHTML');
                        $column = $row->addColumn()->setClass('');
                        $column->addLabel('html', __('HTML'));
                        $column->addEditor('html', $guid)->required()->setValue($values['content']);
                } else if ($values['type'] == 'Link') {
                    // Link
                    $row = $form->addRow()->addClass('resourceLink');
                        $row->addLabel('link', __('Link'));
                        $row->addURL('link')->maxLength(255)->required()->setValue($values['content']);
                }

                $form->addRow()->addHeading(__('Resource Details'));

                $row = $form->addRow();
                    $row->addLabel('name', __('Name'));
                    $row->addTextField('name')->required()->maxLength(60);

                $categories = getSettingByScope($connection2, 'Resources', 'categories');
                $row = $form->addRow();
                    $row->addLabel('category', __('Category'));
                    $row->addSelect('category')->fromString($categories)->required()->placeholder();

                $purposesGeneral = getSettingByScope($connection2, 'Resources', 'purposesGeneral');
                $purposesRestricted = getSettingByScope($connection2, 'Resources', 'purposesRestricted');
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
                    $row->addCheckboxYearGroup('pupilsightYearGroupID')->addCheckAllNone();

                $row = $form->addRow();
                    $row->addLabel('description', __('Description'));
                    $row->addTextArea('description')->setRows(8);

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

                $form->loadAllValuesFrom($values);

                echo $form->getOutput();
            }
        }
    }
}
