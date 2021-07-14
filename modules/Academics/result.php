<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/result.php') == false) {
    //Access denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Academic Result'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $callbacklink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
        "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .
        $_SERVER['REQUEST_URI'];

    //$baseurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];     

    $baseurl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
    $baseurl .= "://" . $_SERVER['HTTP_HOST'];
    $baseurl .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);

    $CurriculamGateway = $container->get(CurriculamGateway::class);

    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $roleID = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
    
    if($roleID == '004'){
        $sqlf = 'SELECT pupilsightFamilyID FROM pupilsightFamilyAdult WHERE pupilsightPersonID= ' . $cuid . ' ';
        $resultf = $connection2->query($sqlf);
        $fdata = $resultf->fetch();
        $pupilsightFamilyID = $fdata['pupilsightFamilyID'];

        if (!empty($_GET['cid'])) {
            $chkchilds = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID = ' . $_GET['cid'] . ' ';
            $resultachk = $connection2->query($chkchilds);
            $chkstuData = $resultachk->fetch();

            if (!empty($chkstuData)) {
                $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" ';
                $resulta = $connection2->query($childs);
                $stuData = $resulta->fetchAll();

                $students = $chkstuData;
                $stuId = $_GET['cid'];
            } else {
                echo '<h1>No Child</h1>';
            }
        } else {
            $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" ';
            $resulta = $connection2->query($childs);
            $stuData = $resulta->fetchAll();
            $students = $stuData[0];
            $stuId = $students['pupilsightPersonID'];
        }

        //$students = $resulta->fetchAll();
        // echo '<pre>';
        // print_r($students);
        // echo '</pre>';
        // die();

        // $parents = 'SELECT email, phone1 FROM pupilsightPerson WHERE pupilsightPersonID = ' . $cuid . ' ';
        // $resultp = $connection2->query($parents);
        // $parData = $resultp->fetch();

        // QUERY
        $criteria = $CurriculamGateway->newQueryCriteria()
            ->sortBy(['id'])
            ->fromPOST();

        if (!empty($_GET['success']) && $_GET['success'] == '1') {
            echo '<h3 style="color:light-green;color: green;border: 1px solid grey;text-align: center;padding: 5px 5px;">Payment Succesfully Done!</h3>';
        }

        $tab = '';
        if (!empty($stuData) && count($stuData) > 1) {
            $tab = '<div style="display:inline-flex;width:25%"><span style="width:25%">Child : </span><select id="childSel" class="form-control" style="width:100%">';
            foreach ($stuData as $stu) {
                $selected = '';
                if (!empty($_GET['cid'])) {
                    if ($_GET['cid'] == $stu['pupilsightPersonID']) {
                        $selected = 'selected';
                    }
                }
                $tab .=  '<option value=' . $stu['pupilsightPersonID'] . '  ' . $selected . '>' . $stu['officialName'] . '</option>';
            }
            $tab .=  '</select></div>';
        }
        echo $tab;
        // die();

        if (!empty($_GET['cid'])) {
            $chkchilds = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1, c.* FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID = ' . $_GET['cid'] . ' AND c.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ';
            $resultachk = $connection2->query($chkchilds);
            $chkstuData = $resultachk->fetch();

            if (!empty($chkstuData)) {
                $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1, c.* FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" AND c.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ';
                $resulta = $connection2->query($childs);
                $stuData = $resulta->fetchAll();

                $students = $chkstuData;
                $stuId = $_GET['cid'];
            } else {
                echo '<h1>No Child</h1>';
            }
        } else {
            $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1, c.* FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" AND c.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ';
            $resulta = $connection2->query($childs);
            $stuData = $resulta->fetchAll();
            $students = $stuData[0];
            $stuId = $students['pupilsightPersonID'];
        }

    } else {
        $stuId = $_SESSION[$guid]['pupilsightPersonID'];
        $chkchilds = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1, c.* FROM pupilsightPerson AS b LEFT JOIN pupilsightStudentEnrolment AS c ON b.pupilsightPersonID = c.pupilsightPersonID WHERE b.pupilsightPersonID = ' . $stuId . ' AND c.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ';
        $resultachk = $connection2->query($chkchilds);
        $students = $resultachk->fetch();
    }

    if(!empty($students)){
        $sql = 'SELECT b.id as test_id, b.name as test_name, b.enable_html, b.enable_pdf FROM examinationTestAssignClass AS a LEFT JOIN examinationTest AS b ON a.test_id = b.id WHERE a.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND a.pupilsightProgramID = '.$students['pupilsightProgramID'].' AND a.pupilsightYearGroupID = '.$students['pupilsightYearGroupID'].' AND a.pupilsightRollGroupID = '.$students['pupilsightRollGroupID'].' ';
        $result = $connection2->query($sql);
        $testData = $result->fetchAll();
    }

    // echo '<pre>';
    // print_r($testData);
    // echo '</pre>';
    // echo $stuId;

    if(!empty($testData)){
       
    ?>
            <div class="mt-5">
                <h2>Academic Tests</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Test Name</th>
                            <th>View Details</th>
                            <!-- <th>Progress Report</th> -->
                        </tr>
                    </thead>
                    <tbody>
            <?php   
                    foreach($testData as $tdata){
                        $enable_html = $tdata['enable_html'];
                        $enable_pdf = $tdata['enable_pdf'];
                        if($enable_html == '1' || $enable_pdf == '1'){
                            $sql = "SELECT lock_marks_entry, enable_pdf, enable_html, enable_test_report FROM examinationTestStudentConfig WHERE pupilsightSchoolYearID = ".$pupilsightSchoolYearID." AND pupilsightProgramID = ".$students['pupilsightProgramID']." AND pupilsightYearGroupID = ".$students['pupilsightYearGroupID']." AND pupilsightRollGroupID = ".$students['pupilsightRollGroupID']." AND test_id = ".$tdata['test_id']." AND pupilsightPersonID = ".$students['pupilsightPersonID']."";
                            $result = $connection2->query($sql);
                            $chkData = $result->fetch();

                            $sql = 'SELECT b.sketch_id, c.id FROM examinationTest AS a LEFT JOIN examinationReportSketchTemplateMaster AS b ON a.report_template_id = b.id LEFT JOIN examinationReportTemplateSketchGenerate AS c ON b.sketch_id = c.sketch_id WHERE a.id = '.$tdata['test_id'].' ';
                            $result = $connection2->query($sql);
                            $skcData = $result->fetch();
                            $sketch_id = $skcData['sketch_id'];
                            $sketch_gen_id = $skcData['id'];
                            
            ?>
                            <tr>
                                <td><?php echo $tdata['test_name']?></td>
                                <td >
                                    <?php if(!empty($chkData) && ($chkData['enable_html'] == '2')){ ?>
                                        <!-- <button class="disable_test_result btn btn-white">Details</button> -->
                                    <?php } else { ?>
                                        <a href="index.php?q=/modules/Academics/result_details.php&tid=<?php echo $tdata['test_id']?>&cid=<?php echo $stuId?>" target="_blank" class="btn btn-white">Details</a>
                                    <?php } ?>

                                    <?php if(!empty($chkData) && ($chkData['enable_pdf'] == '2')){ ?>
                                        
                                        <?php } else { 
                                            echo '<a class="btn btn-white" href="thirdparty/pdfgenerate/pdflib-single.php?id=' . $sketch_id . '&skgid=' . $sketch_gen_id . '&stuid=' . $stuId .'">Download PDF</a>';
                                        } 
                                    ?>
                                </td>
                                <!-- <td></td> -->
                            </tr>
            <?php } 
            } ?>
                    </tbody>
                </table>
            </div>
    <?php
        
    } else {
        echo '<h1>No Test</h1>';
    }
}

?>

<script>

    $(document).on('change', '#childSel', function() {
        var id = $(this).val();
        var hrf = 'index.php?q=/modules/Academics/result.php&cid=' + id;
        window.location.href = hrf;
    });

    $(document).on('click', '.disable_test_result', function() {
        alert('Your Details is Disabled by Administrator, please contact Administrator.');
    });

    

</script>