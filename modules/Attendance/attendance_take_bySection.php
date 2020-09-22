<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Module\Attendance\AttendanceView;
use Pupilsight\Domain\Helper\HelperGateway;

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_bySection.php') == false) {
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
        }
        $HelperGateway = $container->get(HelperGateway::class); 
        
        $pupilsightPersonID =   $_SESSION[$guid]['pupilsightPersonID'];
        $pupilsightRoleIDPrimary =$_SESSION[$guid]['pupilsightRoleIDPrimary'];
        $program = array();
        $program2 = array();
        $program1 = array('' => 'Select Program');
        if( $pupilsightRoleIDPrimary !='001')//for staff login
        {
        $staff_person_id=$pupilsightPersonID;
        $sql1 = "SELECT p.pupilsightProgramID,p.name AS program,a.pupilsightYearGroupID FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN assignstaff_toclasssection b ON(a.pupilsightMappingID =b.pupilsightMappingID) LEFT JOIN pupilsightProgram AS p
        ON(p.pupilsightProgramID =a.pupilsightProgramID) WHERE b.pupilsightPersonID=".$pupilsightPersonID."  GROUP By a.pupilsightYearGroupID ";//except Admin //0000002962
        $result1 = $connection2->query($sql1);
        $row1 = $result1->fetchAll();
        /* echo "<pre>";
        print_r($row1);*/
        $progrm_id="Staff_program";
        $class_id="Staff_class";
        $section_id= "Staff_section";
        foreach ($row1 as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['program'];
        }
        $program = $program1 + $program2;            
        $disable_cls= 'dsble_attr';        
        }
        else
        {
            $staff_person_id= Null;
            $disable_cls= '';
            $progrm_id="pupilsightProgramID";
            $class_id="pupilsightYearGroupID";
            $section_id= "pupilsightRollGroupID";
            $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
            $resultp = $connection2->query($sqlp);
            $rowdataprog = $resultp->fetchAll();         
        
            foreach ($rowdataprog as $dt) {
                $program2[$dt['pupilsightProgramID']] = $dt['name'];
            }
            $program = $program1 + $program2;            
        }

        if($_GET){
            $pupilsightProgramID =  $_GET['pupilsightProgramID'];
            $pupilsightYearGroupID =  $_GET['pupilsightYearGroupID'];
            $pupilsightRollGroupID =  $_GET['pupilsightRollGroupID'];
            $searchDate = $_GET['ttDate'];
            
            $sqls = 'SELECT a.pupilsightRollGroupID, a.name FROM pupilsightRollGroup AS a LEFT JOIN pupilsightProgramClassSectionMapping AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE b.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" ';
            $results = $connection2->query($sqls);
            $sectiondata = $results->fetchAll();
            $sections = array();
            foreach ($sectiondata as $dt) {
                $sections[$dt['pupilsightRollGroupID']] = $dt['name'];
            }
            $classes =  $HelperGateway->getClassByProgram_staff($connection2, $pupilsightProgramID,$staff_person_id);
            $sections =  $HelperGateway->getSectionByProgram_staff($connection2, $pupilsightYearGroupID,  $pupilsightProgramID,$staff_person_id);

        } else {
            $searchDate = '';
            $pupilsightProgramID =  '';
            $pupilsightYearGroupID =  '';
            $pupilsightRollGroupID =  '';
            $classes = array('');
            $sections = array();
          
        }

    // $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    // $resultp = $connection2->query($sqlp);
    // $rowdataprog = $resultp->fetchAll();

    // $program=array();  
    // $program2=array();  
    // $program1=array(''=>'Select Program');
    // foreach ($rowdataprog as $dt) {
    //     $program2[$dt['pupilsightProgramID']] = $dt['name'];
    // }
    // $program= $program1 + $program2;  
        
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

        $page->breadcrumbs->add(__('Attendance by TimeTable'));
   
    
    // echo "<a style='display:none' id='clickStudentPage' href='fullscreen.php?q=/modules/Staff/select_staff_toAssign.php&width=800'  class='thickbox '>Change Route</a>";   
    // echo "<div style='height:50px;'><div class='float-left mb-2'><a  id='assignStudentPage' data-type='staff' class='btn btn-primary'>Assign</a>&nbsp;&nbsp;";  
    // echo "</div><div class='float-none'></div></div>";

    $searchform = Form::create('searchForm',$_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
     $searchform->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/attendance_take_bySection.php');
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();

     $col = $row->addColumn()->setClass('newdes');
     $col->addLabel('pupilsightProgramID', __('Program'));
     $col->addSelect('pupilsightProgramID')->fromArray($program)->setId($progrm_id)->required()->selected($pupilsightProgramID)->placeholder();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->setId($class_id)->fromArray($classes)->selected($pupilsightYearGroupID)->required();
    $col->addTextField('pupilsightPersonID')->setId('staff_id')->addClass('nodisply')->setValue($pupilsightPersonID);
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->required()->fromArray($sections)->setId($section_id)->selected($pupilsightRollGroupID)->placeholder();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('ttDate', __('Date'))->addClass('dte');
    $col->addDate('ttDate')->setId('dueDate')->required()->setValue($searchDate);

    $col = $row->addColumn()->setClass('newdes');   
    
    
    $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>');  
    echo $searchform->getOutput();

    
    include './modules/Timetable/moduleFunctions.php';
    $ttDate = null;
    if (isset($_POST['ttDate'])) {
        $ttDate = dateConvertToTimestamp(dateConvert($guid, $_POST['ttDate']));
    }
    $tt = renderTTAttendance($guid, $connection2, $pupilsightYearGroupID, $pupilsightRollGroupID, '', $ttDate, '/modules/Attendance/attendance_take_bySection.php');
    if ($tt != false) {
        echo $tt;
    } else {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    }
}

/* Closed By Bikash Ui TimeTable 
    echo "<table class='tablecss' border='1'>

    <tr>
    <td style='border-bottom:0px'>Time</td>
    <td>Mon</td>
    
    <td>Tue</td>
    <td>Wed</td>
    
    <td>Thur</td>
    
    <td>Fir</td>
    
    <td>Sat</td>
    
    </tr>";
     

// while($row = mysql_fetch_array($result))

// {

echo "<tr>";
echo "<td style='border-bottom:0px'>  8:00</td>";
echo "<td><p>Kannada</p><p>03/03/12</p><i class='mdi mdi-check mdi-24px'> </i></td>";

echo "<td> <p>English</p><p>03/03/12</p><i class='mdi mdi-check mdi-24px'> </i></td>";

echo "<td><a href='fullscreen.php?q=/modules/Attendance/select_attendance_bySection.php&width=800' class= 'thickbox'> <p>Maths</p><p>03/03/12</p></a></td>";

echo "<td><a href='fullscreen.php?q=/modules/Attendance/select_attendance_bySection.php&width=800' class= ' thickbox' > <p>Science</p><p>03/03/12</p></a></td>";

echo "<td><a href='fullscreen.php?q=/modules/Attendance/select_attendance_bySection.php&width=800' class= ' thickbox' > <p>Hindhi</p><p>03/03/12</p></a></td>";

echo "<td><a href='fullscreen.php?q=/modules/Attendance/select_attendance_bySection.php&width=800' class= ' thickbox' > <p>Social</p><p>03/03/12</p></a></td>";

echo "</tr>";
echo "<tr>";
echo "<td style='border-bottom:0px'>  9:00</td>";
echo "<td><p>Kannada</p><p>03/03/12</p><i class='mdi mdi-check mdi-24px'> </i></td>";

echo "<td> <p>English</p><p>03/03/12</p><i class='mdi mdi-check mdi-24px'> </i></td>";

echo "<td><a href='fullscreen.php?q=/modules/Attendance/select_attendance_bySection.php&width=800' class= 'thickbox'> <p>Maths</p><p>03/03/12</p></a></td>";

echo "<td><a href='fullscreen.php?q=/modules/Attendance/select_attendance_bySection.php&width=800' class= ' thickbox' > <p>Science</p><p>03/03/12</p></a></td>";

echo "<td><a href='fullscreen.php?q=/modules/Attendance/select_attendance_bySection.php&width=800' class= ' thickbox' > <p>Hindhi</p><p>03/03/12</p></a></td>";

echo "<td><a href='fullscreen.php?q=/modules/Attendance/select_attendance_bySection.php&width=800' class= ' thickbox' > <p>Social</p><p>03/03/12</p></a></td>";

echo "</tr>";
//}

echo "</table>";

*/ 

}
