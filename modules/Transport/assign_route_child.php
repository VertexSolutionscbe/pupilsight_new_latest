<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/assign_route_child.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Assign route to Student'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];

        
    $TransportGateway = $container->get(TransportGateway::class);
    
    $criteria = $TransportGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->fromPOST();


      //  $FeesGateway = $container->get(FeesGateway::class);
        $cuid = $_SESSION[$guid]['pupilsightPersonID'];
        $childs = 'SELECT b.pupilsightPersonID, b.officialName FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID2 = b.pupilsightPersonID WHERE a.pupilsightPersonID1 = ' . $cuid . ' GROUP BY a.pupilsightPersonID1 LIMIT 0,1';
        $resulta = $connection2->query($childs);
        $students = $resulta->fetch();
    
      


        // $transportDtl = 'SELECT trans_route_assign.*,trans_route_assign.id as transid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, g.fine_type, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype, pupilsightPerson.officialName , pupilsightPerson.email, pupilsightPerson.phone1, pupilsightStudentEnrolment.pupilsightYearGroupID as classid, pupilsightStudentEnrolment.pupilsightRollGroupID as sectionid FROM trans_route_assign LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE pupilsightPerson.pupilsightPersonID = "' . $stuId . '" GROUP BY fn_fee_invoice.id';
        
        
        $inputdata = $students['pupilsightPersonID'];
        echo"<h4> Child route Details </h4>";      
        $students = $TransportGateway->getchildData($criteria,$inputdata);
        
        $table = DataTable::createPaginated('FeeStructureManage', $criteria);
        $table->addColumn('student_name', __('Name'));
        // $table->addColumn('studentid', __('Student Id'));
        // $table->addColumn('academicyear', __('Academic Year'));
        $table->addColumn('class', __('Class'));
        $table->addColumn('section', __('Section'));
        $table->addColumn('onward_route_name', __('Onward Route'));
        $table->addColumn('onward_stop_name', __('Onward Stop'));
        $table->addColumn('return_route_name', __('Return Route'));
        $table->addColumn('return_stop_name', __('Return Stop'));

        echo $table->render($students);
        echo"<h4>Bus Details</h4>";
        $bus_dtl = $TransportGateway->getchildData($criteria,$inputdata);
                
        $table = DataTable::createPaginated('FeeStructureManage', $criteria);
        $table->addColumn('name', __('Bus Name'));
        $table->addColumn('vehicle_number', __('Bus Number'));
        $table->addColumn('driver_name', __('Driver Name'));
        $table->addColumn('driver_mobile', __('Driver number'));
        $table->addColumn('pickup_time', __('Start Time'));
        $table->addColumn('drop_time', __('End Time'));


        echo $table->render($bus_dtl);

}