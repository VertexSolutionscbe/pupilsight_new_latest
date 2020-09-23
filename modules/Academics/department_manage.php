<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Departments\DepartmentGateway;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/department_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Manage Subjects'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    echo '<h3>';
    echo __('Subjects');
    echo '</h3>';
    
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
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        if ($result->rowcount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    }

    $types = array(
        '' => __('Select Type'),
        'Scholastic' => __('Scholastic'),
        'Co-Scholastic' => __('Co-Scholastic'),
        'Part A' => __('Part A'),
        'Part B' => __('Part B'),
        'Learning Area' => __('Learning Area'),
        'Administration' => __('Administration'),
    );

    if($_POST){
        if(!empty($_POST['search'])){
            $serchName = $_POST['search'];
        } else {
            $serchName = '';
        }
        if(!empty($_POST['type'])){
            $serchType = $_POST['type'];
        } else {
            $serchType = '';
        }
    } else {
        $serchName = '';
        $serchType = '';
        unset($_SESSION['serchType']);
    }

    if(!empty($serchType)){
        $serchType = $_POST['type'];
        $_SESSION['serchType'] = $serchType;
    }
    

    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
    $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('type', __('Type'));
        $col->addSelect('type')->fromArray($types)->selected($serchType);

    $col = $row->addColumn()->setClass('newdes');    
        $col->addLabel('search', __('Subject Name'));
        $col->addTextField('search')->placeholder('Search by Subject Name')->addClass('txtfield')->setValue($serchName);

    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    
    $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>');  
    echo $searchform->getOutput();

    

    $departmentGateway = $container->get(DepartmentGateway::class);
   
    // QUERY
    //echo $serchName;
    $criteria = $departmentGateway->newQueryCriteria()
        ->sortBy('name')
        //->searchBy($search)
        ->searchBy('name', $serchName)
        ->fromPOST();

    if($_SESSION['serchType']){
        $serchType = $_SESSION['serchType'];
    } else {
        $serchType = '';
    }
    $departments = $departmentGateway->queryDepartments($criteria, $serchType);

    // DATA TABLE
    $table = DataTable::createPaginated('departmentManage', $criteria);

    $table->addHeaderAction('Assign Subjects to Class', __('Assign Subjects to class'))
        ->setClass('btn btn-primary')
        ->setURL('/modules/Academics/assign_subjects_class_add.php')
        ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
        ->addParam('search', $criteria->getSearchText(true))
        ->displayLabel();

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Academics/department_manage_add.php')
        ->displayLabel();

    $table->addColumn('name', __('Name'));
    $table->addColumn('type', __('Type'))->translatable();
    $table->addColumn('nameShort', __('Subject Code'));
    // $table->addColumn('staff', __('Staff'))
    //     ->sortable(false)
    //     ->format(function($row) use ($departmentGateway) {
    //         $staff = $departmentGateway->selectStaffByDepartment($row['pupilsightDepartmentID'])->fetchAll();
    //         return (!empty($staff)) 
    //             ? Format::nameList($staff, 'Staff', true, true)
    //             : '<i>'.__('None').'</i>';
    //     });
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightDepartmentID')
        ->format(function ($department, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Academics/department_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Academics/department_manage_delete.php');
        });

    echo $table->render($departments);
}
