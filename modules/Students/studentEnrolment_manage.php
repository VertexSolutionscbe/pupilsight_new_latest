<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\StudentGateway;

if (isActionAccessible($guid, $connection2, '/modules/Students/studentEnrolment_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Student Enrolment'));

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
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/studentEnrolment_manage.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
        echo ' | ';
        if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/studentEnrolment_manage.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
        } else {
            echo __('Next Year').' ';
        }
        echo '</div>';

        $search = isset($_GET['search'])? $_GET['search'] : '';

        $studentGateway = $container->get(StudentGateway::class);

        $criteria = $studentGateway->newQueryCriteria()
            ->searchBy($studentGateway->getSearchableColumns(), $search)
            ->sortBy(['surname', 'preferredName'])
            ->pageSize(5000)
            ->fromPOST();

        echo '<h3>';
        echo __('Search');
        echo '</h3>';

        $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php','get');
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/studentEnrolment_manage.php');
        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        $row = $form->addRow();
            $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
            $row->addTextField('search')->setValue($criteria->getSearchText());

        $row = $form->addRow();
            $row->addSearchSubmit($pupilsight->session, __('Clear Search'), array('pupilsightSchoolYearID'));

        echo $form->getOutput();

        echo '<h3>';
        echo __('View');
        echo '</h3>';
        echo '<p>';
        echo __("Students highlighted in red are marked as 'Full' but have either not reached their start date, or have exceeded their end date.");
        echo '<p>';

        $students = $studentGateway->queryStudentEnrolmentBySchoolYear($criteria, $pupilsightSchoolYearID);

        // DATA TABLE
        $table = DataTable::createPaginated('students', $criteria);

        echo "<a style='display:none' id='submitBulkStudentEnrolment' href='fullscreen.php?q=/modules/Students/studentEnrolment_manage_bulk_add.php&pupilsightSchoolYearID=".$pupilsightSchoolYearID."&width=800'  class='thickbox '>Bulk Add</a>"; 
        echo "<div style='height:50px;'><div class='float-right mb-2'><a  id='addBulkStudentEnrolment' data-type='student' class='btn btn-primary'>Bulk Add</a>&nbsp;&nbsp;<a   class='btn btn-primary' href='index.php?q=/modules/Students/studentEnrolment_manage_add.php&pupilsightSchoolYearID=".$pupilsightSchoolYearID."&search=".$criteria->getSearchText(true)."'>Add</a></div><div class='float-none'></div></div>&nbsp;&nbsp;";  
       
        // $table->addHeaderAction('add', __('Add'))
        //     ->setURL('/modules/Students/studentEnrolment_manage_add.php')
        //     ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
        //     ->addParam('search', $criteria->getSearchText(true))
        //     ->displayLabel();
    
        $table->modifyRows($studentGateway->getSharedUserRowHighlighter());

        $table->addMetaData('filterOptions', [
            'status:full'     => __('Status').': '.__('Full'),
            'status:left'     => __('Status').': '.__('Left'),
            'status:expected' => __('Status').': '.__('Expected'),
            'date:starting'   => __('Before Start Date'),
            'date:ended'      => __('After End Date'),
        ]);

        // COLUMNS
        //$table->addCheckboxColumn('pupilsightPersonID', __(''));

        $table->addCheckboxColumn('pupilsightPersonID', __(''))
        ->setClass('chkbox')
        ->notSortable()
        ->format(function ($students) {
            if(!empty($students['yearGroup'])){
                return "<i class='mdi mdi-check'></i>";
            } else {    
                return "<input type='checkbox' value='".$students['pupilsightPersonID']."' class='stuid'>";
            }
        });

        $table->addColumn('officialName', __('Student'));
            // ->sortable(['surname', 'preferredName'])
            // ->format(function ($person) {
            //     return Format::name('', $person['preferredName'], $person['surname'], 'Student', true, true) . '<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';
            // });
        $table->addColumn('program', __('Program'));    
        $table->addColumn('yearGroup', __('Class'));
        $table->addColumn('rollGroup', __('Section'))
           // ->description(__('Roll Order'))
            ->format(function($row) {
                return $row['rollGroup'] . (!empty($row['rollOrder']) ? '<br/><span class="small emphasis">'.$row['rollOrder'].'</span>' : '');
            });

        $table->addActionColumn()
            ->addParam('pupilsightStudentEnrolmentID')
            ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->addParam('search', $criteria->getSearchText(true))
            ->format(function ($students, $actions) {
                if(!empty($students['yearGroup'])){
                    $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Students/studentEnrolment_manage_edit.php');

                    $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Students/studentEnrolment_manage_delete.php');
                }
            });

        echo $table->render($students);
    }
}
