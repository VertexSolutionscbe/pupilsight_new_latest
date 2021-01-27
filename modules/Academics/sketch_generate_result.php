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
    
    echo '<h3>';
    echo __('Generate Sketch Result');
    echo '<div style="float:right;"><a id="generateSketchResult" data-id="'.$sketchId.'" class="btn btn-primary" style="float: right;margin: -6px 0 0 5px;">Generate Result</a> <a id="downloadSketchResult" data-id="'.$sketchId.'" href="thirdparty/phpword/sketchreportcard.php?id='.$sketchId.'" class="btn btn-primary" style="float: right;margin: -6px 0 0 5px;">Download Result</a> </div>';
    echo '</h3>';

  

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
        <div style="width:90%; margin-bottom:10px;" >
            <input type="text" class="w-full" id="searchTable" placeholder="Search">
        </div>
        <!-- <div style="height: 500px;overflow:auto;position: absolute;margin-top: 50px;width:33%;"> -->
        <div style="height: 500px;overflow:auto;margin-top: 50px;">
            <table class="table table-hover" id="myTable">
                <thead>
                    <tr>
                        <th style="width:5%">Select</th>
                        <th style="width:15%">Student Name</th>
                        <th style="width:15%">Program</th>
                        <th style="width:15%">Class - Section</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($studata)) { 
                        foreach($studata as $sd){
                    ?>
                        <tr>
                        <td><input type="radio" class="studentId" name="pupilsightPersonID" value="<?php echo $sd['pupilsightPersonID'];?>" data-sid="<?php echo $sketchId;?>" data-pid="<?php echo $sd['pupilsightPersonID'];?>"></td>
                        <td><?php echo $sd['officialName'];?></td>
                        <td><?php echo $sd['progname'];?></td>
                        <td><?php echo $sd['classname'].' - '.$sd['sectionname'];?></td>
                        </tr>
                    <?php       
                        } }   
                    ?>
                    
                </tbody>
            </table>    
        </div>   
    </div>

    

    <div id="cloning" class="row" style="width:100%">

    <div style="width:90%; margin-bottom:10px;" >
        <input type="text" class="w-full" id="searchTable2" placeholder="Search">
    </div>
    <div style="height: 500px;overflow:auto;margin-top: 50px;">
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

    $(document).on('click', '#generateSketchResult', function() {
        var id = $(this).attr('data-id');
        $("#preloader").show();
        $.ajax({
            url: 'modules/Academics/sketch_generate_resultProcess.php',
            type: 'post',
            data: {id: id},
            async: true,
            success: function(response) {
                console.log(response);
                if(response == 'done'){
                    alert('Sketch Result Generated Successfully');
                    location.reload();
                }
            }
        });
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

    
</script>