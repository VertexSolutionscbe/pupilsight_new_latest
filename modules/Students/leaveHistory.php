<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\StudentGateway;

if (isActionAccessible($guid, $connection2, '/modules/Students/leaveHistory.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Leave History'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $studentGateway = $container->get(StudentGateway::class);

    // QUERY
    $criteria = $studentGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $roleID = $_SESSION[$guid]['pupilsightRoleIDPrimary'];

    if($roleID == '003'){
        $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
    } else {
        $sqlp = 'SELECT GROUP_CONCAT(pupilsightPersonID2) as pupilsightPersonID FROM pupilsightFamilyRelationship WHERE pupilsightPersonID1 = '.$_SESSION[$guid]['pupilsightPersonID'].' ';
        $resultp = $connection2->query($sqlp);
        $childData = $resultp->fetch();
        $pupilsightPersonID = $childData['pupilsightPersonID'];
    }
    //  echo $pupilsightPersonID;
    //  die();
    
    $leaveHistory = $studentGateway->getLeaveHistory($criteria, $pupilsightPersonID);

    // DATA TABLE

    
    $table = DataTable::createPaginated('programManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/School Admin/leaveReason_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Students/leaveApply.php' class='btn btn-primary'>Leave Apply</a></div><div class='float-none'></div></div>";  

    
    
    $table->addColumn('serial_number', __('Sl No'));
    $table->addColumn('studentName', __('Student Name'));
    $table->addColumn('from_date', __('From Date'));
    $table->addColumn('to_date', __('To Date'));
    $table->addColumn('leaveReason', __('Reason'));
    $table->addColumn('remarks', __('Remarks'));
    $table->addColumn('status', __('Status'))
        ->format(function ($dataSet) {
            if ($dataSet['status'] == '1') {
                return 'Approved';
            } else if ($dataSet['status'] == '2') {
                return 'Declined';
            } else {
                return 'Pending';
            }
            return $dataSet['status'];
        });
   
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($leaveHistory, $actions) use ($guid) {
            // $actions->addAction('edit', __('Edit'))
            //         ->setURL('/modules/School Admin/leaveReason_edit.php');
            $date = date('Y-m-d');
            if($date < $leaveHistory['from_date'] && $leaveHistory['status'] == '0'){
            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Students/leaveApply_delete.php');
            }
        });

    echo $table->render($leaveHistory);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
