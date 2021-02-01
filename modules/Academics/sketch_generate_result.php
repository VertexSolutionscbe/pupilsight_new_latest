<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

/* Update for push */
if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute.php') == false) {
    //Acess denied
    echo "<div class='error'>"; 
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs
        ->add(__('Manage Sketch'), 'sketch_manage.php')
        ->add(__('Generate Sketch Result'));
    

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    $sketchId = $_GET['id'];

    $sql = "SELECT * FROM examinationReportTemplateSketch WHERE id = ".$sketchId." ";
    $result = $connection2->query($sql);
    $sktdata = $result->fetch();
    
    echo '<h3>';
    echo __('Sketch Name - '.$sktdata['sketch_name']);
    echo '<div style="float:right;"><a id="generateSketchResult" data-id="'.$sketchId.'" class="btn btn-primary" style="float: right;margin: -6px 0 0 5px;">Generate Result</a> <a id="clickSketchResult" data-id="'.$sketchId.'" data-hrf="thirdparty/phpword/sketchreportcard.php?id='.$sketchId.'"  class="btn btn-primary" style="float: right;margin: -6px 0 0 5px;">Download Result</a> <a style="display:none;" id="downloadSketchResult" href="">Download</a><a  id="editSketchData" class="btn btn-primary" style="float: right;margin: -6px 0 0 5px;">Edit Data</a><a  id="saveSketchData" style="display:none;float: right;margin: -6px 0 0 5px;" class="btn btn-primary">Save Data</a></div>';
    echo '</h3>';

    $sql = "SELECT name FROM pupilsightProgram WHERE pupilsightProgramID = ".$sktdata['pupilsightProgramID']." ";
    $result = $connection2->query($sql);
    $prodata = $result->fetch();
    $progName = $prodata['name'];

    $sql = "SELECT a.pupilsightYearGroupID, a.name , b.pupilsightSchoolYearID, b.pupilsightMappingID, b.pupilsightProgramID, c.pupilsightRollGroupID, c.name as secName FROM pupilsightYearGroup AS a LEFT JOIN pupilsightProgramClassSectionMapping AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS c ON b.pupilsightRollGroupID = c.pupilsightRollGroupID WHERE a.pupilsightYearGroupID IN  (".$sktdata['class_ids'].") AND b.pupilsightProgramID = ".$sktdata['pupilsightProgramID']." AND b.pupilsightSchoolYearID = ".$sktdata['pupilsightSchoolYearID']." ";
    $result = $connection2->query($sql);
    $clsdata = $result->fetchAll();

    // echo '<pre>';
    // print_r($clsdata);
    // echo '</pre>';
  

    $sqla = "SELECT * FROM examinationReportTemplateAttributes WHERE sketch_id = ".$sketchId." ORDER BY pos ASC";
    $resulta = $connection2->query($sqla);
    $attrdata = $resulta->fetchAll();

    $sql = "SELECT a.*, b.officialName, p.name as progname,  d.name as classname, e.name as sectionname FROM examinationReportTemplateSketchData AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightProgram AS p ON c.pupilsightProgramID = p.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE a.sketch_id = ".$sketchId." GROUP BY a.pupilsightPersonID";
    $result = $connection2->query($sql);
    $studata = $result->fetchAll();
    //print_r($labeldata);

?>
    
    <div style="display:inline-flex;width: 100%;">

    <div id="cloning" class="row" style="width:50%;margin: 0 50px 0 0px;">
        <div style="margin-bottom: 10px;">
            <input type="text" class="w-full" id="searchTable3" placeholder="Search">
        </div>
        <!-- <div style="height: 500px;overflow:auto;position: absolute;margin-top: 50px;width:33%;"> -->
        <div style="height: 500px;overflow:auto;">
            <table class="table table-hover" id="myTable3">
                <thead>
                    <tr>
                        <th style="width:5%"><input type="checkbox" class="chkAll chkAllStuData" ></th>
                        <th style="width:15%">Program</th>
                        <th style="width:15%">Class - Section</th>
                        <th style="width:15%">Generated Date</th>
                        <th style="width:15%">Generated By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($clsdata)) { 
                        foreach($clsdata as $cd){
                            $sql = "SELECT a.*, b.officialName FROM examinationReportTemplateSketchGenerate AS a LEFT JOIN pupilsightPerson AS b ON a.created_by = b.pupilsightPersonID WHERE a.pupilsightYearGroupID = ".$cd['pupilsightYearGroupID']." AND a.pupilsightProgramID = ".$cd['pupilsightProgramID']." AND a.pupilsightSchoolYearID = ".$cd['pupilsightSchoolYearID']." AND a.pupilsightRollGroupID = ".$cd['pupilsightRollGroupID']." AND a.sketch_id = ".$sketchId." " ;
                            $result = $connection2->query($sql);
                            $skGdata = $result->fetch();
                            if(!empty($skGdata)){
                                $createdBy = $skGdata['officialName'];
                                $createdAt = date('d-m-Y h:i:s', strtotime($skGdata['created_at']));
                                $sketchGeneratedId = $skGdata['id'];
                            } else {
                                $createdBy = '';
                                $createdAt = '';
                                $sketchGeneratedId = '';
                            }
                    ?>
                        <tr>
                        <td><input type="checkbox" class="chkChild getStudentInSketch" name="class_sec_id" value="<?php echo $cd['pupilsightYearGroupID'];?>" data-sid="<?php echo $sketchId;?>" data-secid="<?php echo $cd['pupilsightRollGroupID'];?>" data-pid="<?php echo $sktdata['pupilsightProgramID'];?>" data-aid="<?php echo $sktdata['pupilsightSchoolYearID'];?>" data-mapid="<?php echo $cd['pupilsightMappingID'];?>" data-skgId="<?php echo $sketchGeneratedId;?>"></td>
                        <td><?php echo $progName;?></td>
                        <td><?php echo $cd['name'].' - '.$cd['secName'];?></td>
                        <td><?php echo $createdBy;?></td>
                        <td><?php echo $createdAt;?></td>
                        </tr>
                    <?php       
                        } }   
                    ?>
                    
                </tbody>
            </table>    
        </div>   
    </div>
    
    
    <div id="cloning" class="row" style="width:50%;margin: 0 50px 0 0px;">
        <div  style="margin-bottom: 10px;">
            <input type="text" class="w-full" id="searchTable" placeholder="Search">
        </div>
        <!-- <div style="height: 500px;overflow:auto;position: absolute;margin-top: 50px;width:33%;"> -->
        <div style="height: 500px;overflow:auto;">
            <table class="table table-hover" id="myTable">
                <thead>
                    <tr>
                        <th style="width:5%">Select</th>
                        <th style="width:15%">Student Name</th>
                    </tr>
                </thead>
                <tbody id="studentData">
                    <tr>
                        <td style="width:15%"></td>
                        <td style="width:15%"></td>
                    </tr>
                </tbody>
            </table>    
        </div>   
    </div>

    

    <div id="cloning" class="row" style="width:100%">

        <div style="margin-bottom: 10px;">
            <input type="text" class="w-full" id="searchTable2" placeholder="Search">
        </div>
        <div style="height: 500px;overflow:auto;">
            <table class="table table-hover" id="myTable2">
                <thead>
                    <tr>
                        <th style="width:15%">Master Attribute Name</th>
                        <th style="width:15%">Attribute Name Word</th>
                        <th style="width:15%">Attribute Value</th>
                    </tr>
                </thead>
                <tbody id="sketchResultData">
                    <tr>
                        <td style="width:15%"></td>
                        <td style="width:15%"></td>
                        <td style="width:15%"></td>
                    </tr>
                </tbody>
            </table>    
        </div>		
      
    </div>

    </div>   

<?php  
}

?>
<style>
    #content {
        height : 700px;
    }

</style>

<script>
    
    $("#searchTable").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#myTable tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $("#searchTable2").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#myTable2 tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $("#searchTable3").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#myTable3 tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $(document).on('click', '#generateSketchResult', function() {
        var id = $(this).attr('data-id');
        var checked = $(".getStudentInSketch:checked").length;
        if (checked >= 1) {
            //$("#preloader").show();
            var clid = [];
            var secid = [];
            var pid = '';
            var yid = '';
            var skid = '';
            var mapid = [];
            var stuid = [];
            $.each($(".getStudentInSketch:checked"), function () {
                clid.push($(this).val());
                secid.push($(this).attr('data-secid'));
                pid = $(this).attr('data-pid');
                yid = $(this).attr('data-aid');
                skid = $(this).attr('data-sid');
                mapid.push($(this).attr('data-mapid'));
            });

            $.each($(".studentId:checked"), function () {
                stuid.push($(this).val());
            });

            var cid = clid.join(",");
            var secid = secid.join(",");
            var mid = mapid.join(",");
            var stid = stuid.join(",");
            $.ajax({
                url: 'modules/Academics/sketch_generate_resultProcess.php',
                type: 'post',
                data: {id: id, pid: pid, cid: cid, yid: yid, secid:secid, mid:mid, stid:stid},
                async: true,
                success: function(response) {
                    console.log(response);
                    if(response == 'done'){
                        alert('Sketch Result Generated Successfully');
                        location.reload();
                    }
                }
            });
        } else {
            alert('You Have to Select Class');
        }
    });

    $(document).on('change', '.getStudentInSketch', function() {
        var checked = $(".getStudentInSketch:checked").length;
        if (checked >= 1) {
            var pid = $(this).attr('data-pid');
            var yid = $(this).attr('data-aid');
            var skid = $(this).attr('data-sid');
            var type = 'getStudentDataForSketch';

            var clid = [];
            var secid = [];
            $.each($(".getStudentInSketch:checked"), function () {
                clid.push($(this).val());
                secid.push($(this).attr('data-secid'));
            });

            var cid = clid.join(",");
            var id = secid.join(",");
            
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: {val: id, type: type, pid: pid, cid: cid, yid: yid, skid:skid },
                async: true,
                success: function(response) {
                    $("#studentData").html('');
                    $("#studentData").html(response);
                }
            });
        }
    });

    $(document).on('change', '.chkAllStuData', function() {
        var checked = $(".chkAllStuData:checked").length;
        if (checked >= 1) {
            
            var type = 'getStudentDataForSketch';

            var clid = [];
            var secid = [];
            var pid = '';
            var yid = '';
            var skid = '';
            $.each($(".getStudentInSketch:checked"), function () {
                clid.push($(this).val());
                secid.push($(this).attr('data-secid'));
                pid = $(this).attr('data-pid');
                yid = $(this).attr('data-aid');
                skid = $(this).attr('data-sid');
            });

            var cid = clid.join(",");
            var id = secid.join(",");
            
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: {val: id, type: type, pid: pid, cid: cid, yid: yid, skid:skid },
                async: true,
                success: function(response) {
                    $("#studentData").html('');
                    $("#studentData").html(response);
                }
            });
        }
    });
    

    $(document).on('change', '.studentId', function() {
        var id = $(this).attr('data-sid');
        var pid = $(this).attr('data-pid');
        var type = 'getStudentSketchData';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {val: id, type: type, pid: pid },
            async: true,
            success: function(response) {
                $("#sketchResultData").html('');
                $("#sketchResultData").html(response);
            }
        });
    });

    $(document).on('click', '#clickSketchResult', function() {
        var hrf = $(this).attr('data-hrf');
        var checked = $(".getStudentInSketch:checked").length;
        if (checked >= 1) {
            $("#preloader").show();
            var skgid = [];
            var stuid = [];
            $.each($(".getStudentInSketch:checked"), function () {
                skgid.push($(this).attr('data-skgid'));
            });

            $.each($(".studentId:checked"), function () {
                stuid.push($(this).val());
            });

            var sgid = skgid.join(",");
            var stid = stuid.join(",");

            var newhrf = hrf+'&skgid='+sgid+'&stuid='+stid;
            $("#downloadSketchResult").attr('href', newhrf);
            $("#downloadSketchResult")[0].click();
            $("#preloader").hide();
        } else {
            alert('You Have to Select Class');
        }
    });

    $(document).on('click', '#editSketchData', function() {
        $(".noEditTd").hide();
        $(".editTd").show();
        $("#saveSketchData").show();
    });

    $(document).on('click', '#saveSketchData', function() {
        var sdata = [];
        $.each($(".updateSketchData"), function () {
            sdata.push($(this).attr('data-id')+'-'+$(this).val());
        });

        var skdata = sdata.join(",");
        var type = 'updateStudentSketchData';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {val: skdata, type: type },
            async: true,
            success: function(response) {
                alert('Sketch Data Updated Successfully');
                $(".studentId").trigger('change');
                $("#saveSketchData").hide();
                //$("#sketchResultData").html('');
                //$("#sketchResultData").html(response);
            }
        });
    });
    
</script>