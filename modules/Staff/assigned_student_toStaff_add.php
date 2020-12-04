<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
$student_id = $session->get('staff_id');

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Staff/assigned_student_toStaff_add.php') != false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Staff'), 'staff_view.php')
        ->add(__('Change Staff Status'));
  
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Select Staff');
    echo '</h2>';

    $pupilsightSchoolYearID = $_GET['aid'];
    $pupilsightProgramID = $_GET['pid'];
    $pupilsightYearGroupID = $_GET['cid'];
    $pupilsightRollGroupID = $_GET['sid'];

    $sqlp = 'SELECT GROUP_CONCAT(pupilsightMappingID) AS mappingIds FROM pupilsightProgramClassSectionMapping WHERE pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND pupilsightProgramID = '.$pupilsightProgramID.' AND pupilsightYearGroupID = '.$pupilsightYearGroupID.' AND pupilsightRollGroupID = '.$pupilsightRollGroupID.' ';
    $resultp = $connection2->query($sqlp);
    $getMapData = $resultp->fetch();
    
    if(!empty($getMapData)){
        $sqlp = 'SELECT GROUP_CONCAT(a.pupilsightStaffID) AS staffIds   FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN assignstaff_toclasssection AS c ON a.pupilsightPersonID = c.pupilsightPersonID  WHERE c.pupilsightMappingID IN ('.$getMapData['mappingIds'].') ';
        $resultp = $connection2->query($sqlp);
        $getstaff= $resultp->fetch();

        if(!empty($getstaff['staffIds'])){
            $sqlp = 'SELECT  b.pupilsightPersonID AS staff_id , b.officialName AS name FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightStaffID IN ('.$getstaff['staffIds'].') ';
            $resultp = $connection2->query($sqlp);
            $getstaffs= $resultp->fetchAll();
            //$rowdataprog = $resultp->fetchAll();

            $getstaff=array();  
            $getstaff2=array();  
            $getstaff1=array(''=>'Select staff');
            foreach ($getstaffs as $dt) {
                $getstaff2[$dt['staff_id']] = $dt['name'];
            }
            $getstaff= $getstaff1 + $getstaff2;
        } else {
            $getstaff=array(''=>'Select staff');
        }  
    } else {
        $getstaff= array();  
    }
        
            $form = Form::create('assignStaffForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/assigned_staff_toStudent_addProcess.php');
            $form->setFactory(DatabaseFormFactory::create($pdo));
        
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('student_id', $student_id);
           
                $row = $form->addRow();
                    $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
                    
                    $col->addTextField('');  

                    $col = $row->addColumn()->setClass('newdes')->setID('staffstatus');
                    $col->addLabel('staff_id', __('List of  Staff'));
                    $col->addSelect('staff_id')->fromArray($getstaff)->required(); 
                    
               
                    $col = $row->addColumn()->setClass('newdes');   
                    
                    $col->addLabel('', __(''));
                    $col->addContent(' <a id="assignStudentToStaff" style=""class=" btn btn-primary">Assign</a>');  
                    
                    $col = $row->addColumn()->setClass('newdes')->setID('TB_closeAjaxWindow');   
                    
                    $col->addLabel('', __(''));
                    $col->addContent('<a  href="#" id="TB_closeWindowButton"  <button style="margin: 0 0 0 -175px;" class="closeAssn btn btn-primary" onclick="history.go(0);" >Cancel</button></a>');  
                    $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
                    
                    $col->addTextField(''); 

echo $form->getOutput();
            
}

?>

<script>
    $(document).on('click', '#assignStudentToStaff', function () {
        $("#preloader").show();
        window.setTimeout(function () {
            var staff_id = $("#staff_id").val();
            
            var formData = new FormData(document.getElementById("assignStaffForm"));
            if (staff_id) {
                $.ajax({
                    url: 'modules/Staff/assigned_staff_toStudent_addProcess.php',
                    type: 'post',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    async: false,
                    success: function (response) {
                        $("#preloader").hide();
                        alert('Student Assign Successfully!');
                        $("#assignStaffForm")[0].reset();
                        $(".closeAssn").click();
                        // location.reload();
                    }
                });
            } else {
                $("#preloader").hide();
                alert('You Have to Select Staff.');

            }
        }, 100);
    });
</script>