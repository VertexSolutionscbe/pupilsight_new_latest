<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Transport\TransportGateway;

if (isActionAccessible($guid, $connection2, '/modules/Transport/bus_view_details.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Fee Structure'));

    
     $pupilsightID = $_GET['id'];
     if ($pupilsightID == '') {
         echo "<div class='error'>";
         echo __('You have not specified one or more required parameters.');
         echo '</div>';
     } else {


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
    
        $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();
    
        $program=array();  
        $program2=array();  
        $program1=array(''=>'Select Program');
        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program= $program1 + $program2;  
    
        $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
        $resultval = $connection2->query($sqlq);
             $rowdata = $resultval->fetchAll();
             $academic=array();
             $ayear = '';
            if(!empty($rowdata)){
                $ayear = $rowdata[0]['name'];
                foreach ($rowdata as $dt) {
                    $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
                }
            }
        
        if($_POST){
            $input = $_POST; 
            $pupilsightProgramID =  $_POST['pupilsightProgramID'];
            $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
            $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
            $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
            $pupilsightPersonID = $_POST['pupilsightPersonID'];
            $student_name = $_POST['student_name'];
            $searchbyPost =  '';
            $search =  $_POST['search'];
            $stuId = $_POST['studentId'];
        } else {
            $input = ''; 
            $pupilsightProgramID =  '';
            $pupilsightSchoolYearIDpost = '';
            $pupilsightYearGroupID =  '';
            $pupilsightRollGroupID =  '';
            $pupilsightPersonID = '';
            $student_name = '';
            $searchbyPost =  '';
            $search = '';
            $stuId = '0';
        }
    
        try {
            // //`trans_bus_details`(`id`, `vehicle_number`, `name`, `model`, `vtype`, `capacity`, `register_date`, `insurance_exp`, `fc_expiry`, `driver_name`, `driver_mobile`, `coordinator_name`, `coordinator_mobile`, `photo`, `cdt`, `udt`)  
            $data = array('id' => $pupilsightID);
            $sql = 'SELECT * FROM trans_bus_details WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();
            $imgpath = 'modules/Transport/'.$values['photo'];
            echo "<div style=''><h4>Details of '".$values['name']."' </h4><br/></div>";
            echo "<table cellspacing='0' class='table display data-table text-nowrap' style='width: 100%;'>
            <thead><tr class='head'>
            <th>Reg.Date</th>
            <th>Insurance Expiry</th>
            <th>F.C Expiry Date</th>
            <th>Driver Name</th>
            <th>Driver Mobile</th>
            <th>Transport Coordinator Name</th>
            <th>Transport Coordinator Mobile</th>
            <th>Photo</th>
            </thead>
           </tr> ";

            if($values['register_date']!='0000-00-00')
                  $register_date = date('d/m/Y', strtotime($values['register_date']));
            else
                  $register_date = '';

            if($values['insurance_exp']!='0000-00-00')
                  $insurance_exp = date('d/m/Y', strtotime($values['insurance_exp']));
            else
                  $insurance_exp = '';

            if($values['fc_expiry']!='0000-00-00')
                  $fc_expiry = date('d/m/Y', strtotime($values['fc_expiry']));
            else
                  $fc_expiry = '';

            echo '<tr>';
            echo "<td style='width: 33%; vertical-align: top'>".$register_date."</td>";
            echo "<td style='width: 33%; vertical-align: top'>".$insurance_exp. "</td>";
            echo "<td style='width: 33%; vertical-align: top'>".$fc_expiry."</td>";
            echo "<td style='width: 33%; vertical-align: top'>".$values['driver_name']. "</td>";
            echo "<td style='width: 33%; vertical-align: top'>".$values['driver_mobile']."</td>";
            echo "<td style='width: 33%; vertical-align: top'>".$values['coordinator_name']. "</td>";
            echo "<td style='width: 33%; vertical-align: top'>".$values['coordinator_mobile']."</td>";
            echo "<td style='width: 33%; vertical-align: top'>";
            if(!empty($values['photo'])){
                echo  "<img src='".$imgpath."' height='50px' width='50px' />";
            } 
            echo "</td>";
            echo "</tr>";
            echo "</table>";

        }
      
    
        $TransportGateway = $container->get(TransportGateway::class);
        $criteria = $TransportGateway->newQueryCriteria()
            ->sortBy(['id'])
            ->fromPOST();
    
        $yearGroups = $TransportGateway->getBusdetails_byid($criteria,$pupilsightID);
        $table = DataTable::createPaginated('BusManage_view', $criteria);
        
       // echo "<div style='height:50px;'><div class='float-right mb-2'>";  
      //  echo "&nbsp;&nbsp;<a href='index.php?q=/modules/Transport/bus_manage_add.php' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  
    
      //`trans_bus_details`(`id`, `vehicle_number`, `name`, `model`, `vtype`, `capacity`, `register_date`, `insurance_exp`, `fc_expiry`, `driver_name`, `driver_mobile`, `coordinator_name`, `coordinator_mobile`, `photo`, `cdt`, `udt`)  
        
        
       
       /*
        $table->addColumn('1', __('SI No'));
       $table->addColumn('2', __('Vehicle Number'));
        $table->addColumn('3', __('Name'));
        $table->addColumn('4', __('Model'));
        $table->addColumn('5', __('Type'));
        $table->addColumn('6', __('Capacity'));*/

        /*
        $table->addColumn('register_date', __('Reg.Date'));
        $table->addColumn('insurance_exp', __('Insurance Expiry'));
        $table->addColumn('fc_expiry', __('F.C Expiry Date'));
        $table->addColumn('driver_name', __('Driver Name'));
        $table->addColumn('driver_mobile', __('Driver Mobile'));
        $table->addColumn('coordinator_name', __('Transport Coordinator Name'));
        $table->addColumn('coordinator_mobile', __('Transport Coordinator Mobile'));
echo "<div style='height:15px'></div>";

    
       

        $table->addColumn('photo', __('Photo'))
        ->context('primary')
        ->width('10%')
        ->notSortable()
        ->format(Format::using('userPhoto', ['photo', 'sm']));

        
       
        // $table->addColumn('bank_name', __('Bank Name'));
        // $table->addColumn('ac_no', __('Account No'));
    
        echo $table->render($yearGroups);

      
        echo "<div style='height:15px'></div>";*/

     }



   

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
