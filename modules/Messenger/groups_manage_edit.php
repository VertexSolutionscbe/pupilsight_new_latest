<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Messenger\GroupGateway;

$page->breadcrumbs
    ->add(__('Manage Groups'), 'groups_manage.php')
    ->add(__('Edit Group'));

if (isActionAccessible($guid, $connection2, '/modules/Messenger/groups_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightGroupID = (isset($_GET['pupilsightGroupID']))? $_GET['pupilsightGroupID'] : null;

    //Check if school year specified
    if ($pupilsightGroupID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $groupGateway = $container->get(GroupGateway::class);
        
        $highestAction = getHighestGroupedAction($guid, '/modules/Messenger/groups_manage.php', $connection2);
        if ($highestAction == 'Manage Groups_all') {
            $result = $groupGateway->selectGroupByID($pupilsightGroupID);
        } else {
            $result = $groupGateway->selectGroupByIDAndOwner($pupilsightGroupID, $_SESSION[$guid]['pupilsightPersonID']);
        }

        if ($result->isEmpty()) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            $form = Form::create('groups', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/groups_manage_editProcess.php?pupilsightGroupID=$pupilsightGroupID");
            $form->setFactory(DatabaseFormFactory::create($pdo));

			$form->addHiddenValue('address', $_SESSION[$guid]['address']);
			
            $row = $form->addRow();
                $row->addLabel('name', __('Name'));
                $row->addTextField('name')->required();

            $row = $form->addRow();
                $row->addLabel('members', __('Add Members'));
                $row->addSelectUsers('members', $_SESSION[$guid]['pupilsightSchoolYearID'], ['includeStudents' => true])
                    ->selectMultiple();
            	
			$row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();
                
            $form->loadAllValuesFrom($values);
				
            echo $form->getOutput();

            echo '<h2>';
            echo __('Current Members');
            echo '</h2>';

            $criteria = $groupGateway->newQueryCriteria()
                ->sortBy(['surname', 'preferredName'])
                ->fromPOST();

            $members = $groupGateway->queryGroupMembers($criteria, $pupilsightGroupID);

            $table = DataTable::createPaginated('groupsManage', $criteria);

            $table->addColumn('name', __('Name'))
                ->sortable(['surname', 'preferredName'])
                ->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', true]));

            $table->addColumn('email', __('Email'))->sortable();

            $table->addActionColumn()
                ->addParam('pupilsightGroupID')
                ->addParam('pupilsightPersonID')
                ->format(function ($person, $actions) {
                    $actions->addAction('delete', __('Delete'))
                            ->setURL('/modules/Messenger/groups_manage_edit_delete.php');
                });

            echo $table->render($members);
        }
    }
}
