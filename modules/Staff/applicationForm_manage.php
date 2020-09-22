<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffApplicationFormGateway;

if (isActionAccessible($guid, $connection2, '/modules/Staff/applicationForm_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Applications'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $search = isset($_GET['search'])? $_GET['search'] : '';

    $applicationGateway = $container->get(StaffApplicationFormGateway::class);

    // CRITERIA
    $criteria = $applicationGateway->newQueryCriteria()
        ->searchBy($applicationGateway->getSearchableColumns(), $search)
        ->sortBy('pupilsightStaffApplicationForm.status')
        ->sortBy(['priority', 'timestamp'], 'DESC')
        ->fromPOST();

    echo '<h4>';
    echo __('Search');
    echo '</h2>';

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/applicationForm_manage.php");

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(__('Application ID, preferred, surname'));
        $row->addTextField('search')->setValue($criteria->getSearchText())->maxLength(20);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    echo '<h4>';
    echo __('View');
    echo '</h2>';

    $applications = $applicationGateway->queryApplications($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('applicationsManage', $criteria);

    $table->modifyRows(function($application, $row) {
        // Highlight rows based on status
        if ($application['status'] == 'Accepted') {
            $row->addClass('current');
        } else if ($application['status'] == 'Rejected' || $application['status'] == 'Withdrawn') {
            $row->addClass('error');
        }
        return $row;
    });

    // COLUMNS
    $table->addColumn('pupilsightStaffApplicationFormID', __('ID'))
        
        ->format(Format::using('number', 'pupilsightStaffApplicationFormID'));

    $table->addColumn('person', __('Applicant'))
        ->description(__('Application Date'))
        ->sortable(['surname', 'preferredName'])
        ->format(function($row) {
            if (!empty($row['pupilsightPersonID'])) {
                $output = Format::name('', $row['preferredName'], $row['surname'], 'Staff', true, true);
            } else {
                $output = Format::name('', $row['applicationPreferredName'], $row['applicationSurname'], 'Staff', true, true);
            }
            return $output.'<br/><span class="small emphasis">'.Format::dateTime($row['timestamp']).'</span>';
        });

    $table->addColumn('jobTitle', __('Position'));
    
    $table->addColumn('status', __('Status'))
        
        ->description(__('Milestones'))
        ->format(function($row) {
            $output = '<strong>'.$row['status'].'</strong>';
            if ($row['status'] == 'Pending') {
                $output .= '<br/><span class="small emphasis">'.trim(str_replace(',', '<br/>', $row['milestones'])).'</span>';
            }
            return $output;
        });

    $table->addColumn('priority', __('Priority'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightStaffApplicationFormID')
        ->addParam('search', $criteria->getSearchText(true))
        ->format(function ($row, $actions) use ($guid) {
            if ($row['status'] == 'Pending' || $row['status'] == 'Waiting List') {
                $actions->addAction('accept', __('Accept'))
                        ->setIcon('iconTick')
                        ->setURL('/modules/Staff/applicationForm_manage_accept.php');

                $actions->addAction('reject', __('Reject'))
                        ->setIcon('iconCross')
                        ->append('<br/>')
                        ->setURL('/modules/Staff/applicationForm_manage_reject.php');
            }

            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Staff/applicationForm_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Staff/applicationForm_manage_delete.php');
        });

    echo $table->render($applications);
}
