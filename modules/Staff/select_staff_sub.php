<?php
/*
Pupilsight, Flexible & Open School System
*/

$session = $container->get('session');
$id = $session->get('staffs_id');

use Pupilsight\Domain\Staff\StaffGateway;
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Helper\HelperGateway;


if (isActionAccessible($guid, $connection2, '/modules/Staff/select_staff_sub.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
    ->add(__('Assign Staff To Subject'), 'assign_staff_toSubject.php')
    ->add(__('Choose A Staff to Subject'));
    $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_structure_assign_student_manage_edit.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Choose A Staff to Subject');
    echo '</h2>';
   
    $StaffGateway = $container->get(StaffGateway::class);
    $criteria = $StaffGateway->newQueryCriteria()
            //->sortBy(['id'])
            ->fromPOST();


    $classes = array('' => 'Select Class');
    $sections = array('' => 'Select Section');
    $roleId = $_SESSION[$guid]['pupilsightRoleIDPrimary'];

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

    $HelperGateway = $container->get(HelperGateway::class);

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    if ($_POST) {
        $input = $_POST;
        $pupilsightProgramID = $_POST['pupilsightProgramID'];
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
        $search = $_POST['search'];


        $uid = $_SESSION[$guid]['pupilsightPersonID'];

        if ($roleId == '2') {
            $classes =  $HelperGateway->getClassByProgramForTeacher($connection2, $pupilsightProgramID, $uid);
            $sections =  $HelperGateway->getSectionByProgramForTeacher($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $uid);
        } else {
            $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
            $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearID);
        }

    } else {
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
        $pupilsightProgramID = '';
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID = '';
        $search = '';
        $input = '';
       
    }

    $form = Form::create('assignStaffToSubjectSearch', '');

    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view.php');
    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDbyPP')->fromArray($program)->selected($pupilsightProgramID)->placeholder('Select Program')->required();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDbyPP')->fromArray($classes)->selected($pupilsightYearGroupID)->placeholder('Select Class')->required();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->setId('pupilsightRollGroupIDbyPP')->fromArray($sections)->selected($pupilsightRollGroupID)->placeholder('Select Section')->required();

    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));
    $col->addSearchSubmit($pupilsight->session, __('Clear Search'));



    echo $form->getOutput();

          
    //$getselstaff = $StaffGateway->getselectedStaff($criteria);
    //print_r($getselstaff);die();
    
    if(!empty($pupilsightProgramID) && !empty($pupilsightYearGroupID) && !empty($pupilsightRollGroupID)){
        $sqlp = 'SELECT GROUP_CONCAT(pupilsightMappingID) AS mappingIds FROM pupilsightProgramClassSectionMapping WHERE pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND pupilsightProgramID = '.$pupilsightProgramID.' AND pupilsightYearGroupID = '.$pupilsightYearGroupID.' AND pupilsightRollGroupID = '.$pupilsightRollGroupID.' ';
        $resultp = $connection2->query($sqlp);
        $getMapData = $resultp->fetch();



        $sqlp = 'SELECT a.pupilsightStaffID,a.staff_status AS stat,b.*, b.pupilsightPersonID AS stu_id , a.type, b.officialName AS name FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN assignstaff_toclasssection AS c ON a.pupilsightPersonID = c.pupilsightPersonID  WHERE c.pupilsightMappingID IN ('.$getMapData['mappingIds'].') AND b.pupilsightRoleIDPrimary NOT IN (003,004) ';
        $resultp = $connection2->query($sqlp);
        $getstaff= $resultp->fetchAll();
        
    
        $sqld = 'SELECT a.name, a.pupilsightDepartmentID AS sub_id FROM pupilsightDepartment AS a LEFT JOIN subjectToClassCurriculum AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID WHERE b.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND b.pupilsightProgramID = '.$pupilsightProgramID.' AND b.pupilsightYearGroupID = '.$pupilsightYearGroupID.'';
        $resultd = $connection2->query($sqld);
        $getsub= $resultd->fetchAll();

        echo "<a style='display:none' id='clickstaffunassign' href='fullscreen.php?q=/modules/Staff/remove_assigned_staffSub.php&width=800'  class='thickbox '>Unassign staff</a>"; 
    
        $getselectedstaff = [];
    ?>
    <form action="<?=$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/assign_staff_toSubjectProcess.php';?>" method="post" autocomplete="on" enctype="multipart/form-data" class="smallIntBorder fullWidth standardForm" id="assignStaffForm">
        <input type="hidden" name="address" value="/modules/Staff/select_staff_sub.php">
        <div class='row'>
            <div class='col-12 text-right'>
                <a id="assignStaffToSubject" style="height: 34px; float: right;"class=" btn btn-primary">Assign</a>
            </div>
        </div>
        <!-- Edited By : Mandeep, Reason : Search filter added to both staff and subjects -->
        <div class = "row mt-2">
            <div class = "col-6">
                <input type="text" id="staff_search" onkeyup="searchStaff()" placeholder="Search for staff..">
            </div>
            <div class = "col-6">
                <input type="text" id="subject_search" onkeyup="searchSubject()" placeholder="Search for subject..">
            </div>
        </div>
        <!-- Edited By : Mandeep, Reason : margin added -->
        <div class='row mt-2'>

            <div class='col-6'>
                <h3>Staff List</h3>
                <!-- Edited By : Mandeep, Reason : Search filter added to both staff and subjects -->
                <div id = "staff_list">
                <?php
                    foreach($getstaff as $staff){
                        $staffName = $staff['name'];
                        if($staffName){
                            $staffid = $staff['pupilsightStaffID'];
                            echo "\n<div class='m-2' style='line-height:20px;'>";
                            echo "<input type=\"checkbox\" name=\"selected_sstaff[]\" id=\"".$staffid."\" value='".$staffid."'>";
                            echo "<label class='ml-2' for=\"".$staffid."\">".ucwords($staffName)."</label></div>";
                        }
                    }
                ?>
                </div>
            </div>
            <div class='col-6'>
                <h3>Subject List</h3>
                <!-- Edited By : Mandeep, Reason : Search filter added to both staff and subjects -->
                <div id = "subject_list">
                <?php
                    foreach($getsub as $sub){
                        $subname = $sub['name'];
                        if($subname){
                            $subid = $sub['sub_id'];
                            echo "\n<div class='m-2' style='line-height:20px;'>";
                            echo "\n<input type=\"checkbox\" name=\"selected_sub[]\" id=\"".$subid."\" value='".$subid."'>";
                            echo "<label class='ml-2' for=\"".$subid."\">".ucwords($subname)."</label></div>";
                        }
                    }
                ?>
                </div>
            </div>
        </div>
        
    </form>

    

<?php } ?>
    <!-- Edited By : Mandeep, Reason : Search filter added to both staff and subjects -->
    <script>
        function searchStaff() {
            var input, filter, div, div_in, a, i, txtValue;
            input = document.getElementById("staff_search");
            filter = input.value.toUpperCase();
            div = document.getElementById("staff_list");
            div_in = div.getElementsByTagName("div");
            for (i = 0; i < div_in.length; i++) {
                a = div_in[i].getElementsByTagName("label")[0];
                txtValue = a.textContent || a.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    div_in[i].style.display = "";
                } else {
                    div_in[i].style.display = "none";
                }
            }
        }

        function searchSubject() {
            var input, filter, div, div_in, a, i, txtValue;
            input = document.getElementById("subject_search");
            filter = input.value.toUpperCase();
            div = document.getElementById("subject_list");
            div_in = div.getElementsByTagName("div");
            for (i = 0; i < div_in.length; i++) {
                a = div_in[i].getElementsByTagName("label")[0];
                txtValue = a.textContent || a.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    div_in[i].style.display = "";
                } else {
                    div_in[i].style.display = "none";
                }
            }
        }

        $(document).on('click', '#assignStaffToSubject', function (e) {

            e.preventDefault();
            var formData = new FormData(document.getElementById("assignStaffForm"));
            var favorite = [];
            $.each($("input[name='selected_sstaff[]']:checked"), function () {
                favorite.push($(this).val());
            });
            var staff_id = favorite.join(", ");

            var favorite1 = [];
            $.each($("input[name='selected_sub[]']:checked"), function () {
                favorite1.push($(this).val());
            });
            var subject_id = favorite1.join(", ");
            //   alert(submit_id + '-' + form_id + '-' + camp_id);
            if (staff_id) {
                if (subject_id) {
                    $("#preloader").show();
                    $.ajax({
                        url: "modules/Staff/assign_staff_toSubjectProcess.php",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        cache: false,
                        processData: false,
                        async: false,
                        success: function (data) {
                            alert("Staff Assigned Successfully!");
                            $("#preloader").hide();
                            $("#assignStaffToSubjectSearch").submit();
                        }
                    });
                } else {
                    alert('You Have to Select Subject.');
                    $("#preloader").hide();
                }
            } else {
                alert('You Have to Select Staff.');
                $("#preloader").hide();
            }
            });
    </script>
    <?php
    
}
