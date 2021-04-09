<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Staff/assign_staff_toClassSection.php') == false) {
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
        $pupilsightSchoolYearID = '';
        if (isset($_GET['pupilsightSchoolYearID'])) {
            $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
        } else {
            $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        }

        

        //echo $pupilsightSchoolYearID;
        if ($_POST) {
            $pupilsightProgramID =  $_POST['pupilsightProgramID'];
            $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
            $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
            $stuId = $_POST['studentId'];
        } else {
            $pupilsightProgramID =  '';
            $pupilsightYearGroupID =  '';
            $pupilsightRollGroupID =  '';
            $stuId = '0';
        }

        $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        $program = array();
        $program2 = array();
        $program1 = array('' => 'Select Program');
        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program = $program1 + $program2;

        $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
        $resultval = $connection2->query($sqlq);
        $rowdata = $resultval->fetchAll();
        $academic = array();
        $ayear = '';
        if (!empty($rowdata)) {
            $ayear = $rowdata[0]['name'];
            foreach ($rowdata as $dt) {
                $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        }



        $page->breadcrumbs->add(__('Assign Staff to Class and Section'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }
    }


    $StaffGateway = $container->get(StaffGateway::class);
    $criteria = $StaffGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->pageSize(1000)
        ->fromPOST();

    $staff = $StaffGateway->getassignedstaff($criteria, $pupilsightSchoolYearID);
    /*
echo "<pre>";
print_r($staff);
*/

    $table = DataTable::createPaginated('FeeStructureManage', $criteria);
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Staff/assign_staff_toClassSection_add.php' class='btn btn-primary'>Assign staff to class and section</a>";
    echo "&nbsp;&nbsp;</div><div class='float-none'></div></div>";

    // $table->addColumn('student_name', __('Name'));
    $table->addColumn('program', __('Program'));
    $table->addColumn('yearGroup', __('Class'));
    $table->addColumn('rollGroup', __('Section'));
    $table->addColumn('name', __('Staff Name'));
    // $table->addColumn('onward_stop_name', __('Onward Stop'));
    // $table->addColumn('return_route_name', __('Return Route'));
    // $table->addColumn('return_stop_name', __('Return Stop'));
    $table->addActionColumn()
        ->addParam('st_id')
        ->addParam('pupilsightMappingID')
        ->format(function ($facilities, $actions) use ($guid) {
            // $actions->addAction('copynew', __('Copy'))
            //         ->setURL('/modules/Transport/transport_route_copy.php');

            $actions->addAction('deleteStaff', __('DeleteStaff'))
                ->setURL('/modules/Staff/remove_assined_staff.php');
        });
    echo $table->render($staff);
}

echo "<style>

#TB_window
{
    margin-left: -340px;
    width: 680px;
    margin-top: -280px !important;
   
    min-height:400px !important;

}
</style>";
