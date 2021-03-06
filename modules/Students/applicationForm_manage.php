<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\ApplicationFormGateway;
use Pupilsight\Domain\User\FamilyGateway;

if (isActionAccessible($guid, $connection2, '/modules/Students/applicationForm_manage.php') == false) {
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

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    if ($pupilsightSchoolYearID != $_SESSION[$guid]['pupilsightSchoolYearID']) {
        try {
            $data = array('pupilsightSchoolYearID' => $_GET['pupilsightSchoolYearID']);
            $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        if ($result->rowcount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    }

    if ($pupilsightSchoolYearID != '') {
        echo '<h2>';
        echo $pupilsightSchoolYearName;
        echo '</h2>';

        echo "<div class='linkTop'>";
            //Print year picker
            if (getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/applicationForm_manage.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
        echo ' | ';
        if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/applicationForm_manage.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
        } else {
            echo __('Next Year').' ';
        }
        echo '</div>';

        $search = isset($_GET['search']) ? $_GET['search']  : '';
        $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID']) ? $_GET['pupilsightYearGroupID']  : '';

        $familyGateway = $container->get(FamilyGateway::class);
        $applicationGateway = $container->get(ApplicationFormGateway::class);

        $criteria = $applicationGateway->newQueryCriteria()
            ->searchBy($applicationGateway->getSearchableColumns(), $search)
            ->sortBy('pupilsightApplicationForm.status')
            ->sortBy('pupilsightApplicationForm.priority', 'DESC')
            ->sortBy('pupilsightApplicationForm.timestamp', 'DESC')
            ->filterBy('yearGroup', $pupilsightYearGroupID)
            ->fromPOST();

        echo '<h4>';
        echo __('Search');
        echo '</h2>';

        $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setClass('noIntBorder fullWidth');
        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/applicationForm_manage.php');
        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        $row = $form->addRow();
            $row->addLabel('search', __('Search For'))->description(__('Application ID, preferred, surname, payment transaction ID'));
            $row->addTextField('search')->setValue($search);

        $row = $form->addRow();
            $row->addLabel('pupilsightYearGroupID', __('Year Group'));
            $row->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID)->placeholder();

        $row = $form->addRow();
            $row->addSearchSubmit($pupilsight->session, __('Clear Search'), array('pupilsightSchoolYearID'));

        echo $form->getOutput();

        echo '<h4>';
        echo __('View');
        echo '</h2>';

        $applications = $applicationGateway->queryApplicationFormsBySchoolYear($criteria, $pupilsightSchoolYearID);

        $familyIDs = $applications->getColumn('pupilsightFamilyID');
        $adults = $familyGateway->selectAdultsByFamily($familyIDs)->fetchGrouped();
        $applications->joinColumn('pupilsightFamilyID', 'adults', $adults);

        // DATA TABLE
        $table = DataTable::createPaginated('applications', $criteria);

        $table->addHeaderAction('add', __('Add'))
            ->setURL('/modules/Students/applicationForm_manage_add.php')
            ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->addParam('search', $criteria->getSearchText(true))
            ->displayLabel();

        $table->modifyRows(function ($application, $row) {
            if ($application['status'] == 'Accepted') $row->addClass('current');
            if ($application['status'] == 'Rejected') $row->addClass('error');
            if ($application['status'] == 'Withdrawn') $row->addClass('error');
            return $row;
        });

        $table->addMetaData('filterOptions', [
            'status:pending'      => __('Status').': '.__('Pending'),
            'status:accepted'     => __('Status').': '.__('Accepted'),
            'status:rejected'     => __('Status').': '.__('Rejected'),
            'status:waiting list' => __('Status').': '.__('Waiting List'),

            'paid:y'         => __('Paid').': '.__('Yes'),
            'paid:n'         => __('Paid').': '.__('No'),
            'paid:exemption' => __('Paid').': '.__('Exemption'),

            'rollGroup:y'         => __('Form Group').': '.__('Yes'),
            'rollGroup:n'         => __('Form Group').': '.__('No'),
        ]);

        $table->addColumn('pupilsightApplicationFormID', __('ID'))
              ->format(Format::using('number', ['pupilsightApplicationFormID']));

        $table->addColumn('student', __('Student'))
            ->description(__('Application Date'))
            ->sortable(['surname', 'preferredName'])
            ->format(function ($application) use ($applicationGateway, $guid) {
                $output = '';

                // Add a list of linked sibling appplications as an icon with hover-over text
                $linkedApplications = $applicationGateway->selectLinkedApplicationsByID($application['pupilsightApplicationFormID']);
                if ($linkedApplications->rowCount() > 0) {
                    $siblings = array_map(function($sibling) {
                        return '- ' . Format::name('', $sibling['preferredName'], $sibling['surname'], 'Student', true).' ('.$sibling['status'].')';
                    }, $linkedApplications->fetchAll());
                    $output .= "<img title='" . __('Sibling Applications') .'<br/>' . implode('<br/>', $siblings). "' src='./themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/attendance.png'/ style='float: right;   width:20px; height:20px;margin-left:4px;'>";
                }
                
                $output .= '<strong>'.Format::name('', $application['preferredName'], $application['surname'], 'Student', true, true) . '</strong><br/>';
                $output .= '<small><i>'.Format::date($application['timestamp']).'</i></small>';

                return $output;
            });
            
        $table->addColumn('dob', __('Birth Year'))
            ->description(__('Entry Year'))
            ->format(function($application) {
                return substr($application['dob'], 0, 4).'<br/><span style="font-style: italic; font-size: 85%">'.$application['yearGroup'].'</span>';
            });

        $table->addColumn('parents', __('Parents'))
            ->sortable(false)
            ->format(function($application) {
                $parentsText = '';
                if (empty($application['pupilsightFamilyID'])) {
                    $application['adults'] = array();
                    if (!empty($application['parent1surname']) && !empty($application['parent1preferredName'])) {
                        $application['adults'][] = array('title' => $application['parent1title'], 'preferredName' => $application['parent1preferredName'], 'surname' => $application['parent1surname'], 'email' => $application['parent1email']);
                    }
                    if (!empty($application['parent2surname']) && !empty($application['parent2preferredName'])) {
                        $application['adults'][] = array('title' => $application['parent2title'],'preferredName' => $application['parent2preferredName'],'surname' => $application['parent2surname'],'email' => $application['parent2email']);
                    }
                }

                foreach ($application['adults'] as $parent) {
                    $name = Format::name($parent['title'], $parent['preferredName'], $parent['surname'], 'Parent');
                    $link = !empty($parent['email'])? 'mailto:'.$parent['email'] : '';
                    $parentsText .= Format::link($link, $name).'<br/>';
                }

                return $parentsText;
            });

        $table->addColumn('schoolName1', __('Last School'))
            ->format(function($application) {
                $school = $application['schoolName1'];
                if ($application['schoolDate2'] > $application['schoolDate1'] && !empty($application['schoolName2'])) {
                    $school = $application['schoolName2'];
                }
                return Format::truncate($school, 20);
            });

        $table->addColumn('status', __('Status'))
            ->description(__('Milestones'))
            ->format(function($application) {
                $statusText = '<strong>'.$application['status'].'</strong>';
                if ($application['status'] == 'Pending') {
                    $statusText .= '<br/><span style="font-style: italic; font-size: 85%">'.str_replace(',', '<br/>', $application['milestones']).'</span>';
                }
                return $statusText;
            });

        $table->addColumn('priority', __('Priority'));

        if ($criteria->hasFilter('paid')) {
            $table->addColumn('paymentMade', __('Payment Made'))->format(function($application) {
                return $application['paymentMade'] == 'Exemption'
                    ? __('Exemption')
                    : Format::yesNo($application['paymentMade']);
            });
        }

        $table->addActionColumn()
        ->setClass('padding_appl_action')
            ->width('72px')
            ->addParam('pupilsightApplicationFormID')
            ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->addParam('search', $criteria->getSearchText(true))
            ->format(function ($application, $actions) use ($guid, $connection2) {
                if ($application['status'] == 'Pending' or $application['status'] == 'Waiting List') {
                    $actions->addAction('accept', __('Accept'))
                        ->setIcon('iconTick')
                        ->setURL('/modules/Students/applicationForm_manage_accept.php');

                    $actions->addAction('reject', __('Reject'))
                        ->setIcon('iconCross')
                        ->setURL('/modules/Students/applicationForm_manage_reject.php')
                        ->append('<br/><div style="height:8px;"></div>');
                }

                $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Students/applicationForm_manage_edit.php');

                if (isActionAccessible($guid, $connection2, '/modules/Students/applicationForm_manage_delete.php')) {
                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Students/applicationForm_manage_delete.php');
                }
            });

        echo $table->render($applications);
    }
}
