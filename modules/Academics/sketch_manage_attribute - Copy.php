<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute.php') == false) {
    //Acess denied
    echo "<div class='error'>"; 
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Manage Sketch Configurations'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    echo '<h3>';
    echo __('Add Sketch Attribute');
    echo '</h3>';

    $sketchId = $_GET['id'];

    $sqla = "SELECT b.id, b.report_column_word, b.report_column_label FROM examinationReportTemplateAttributes AS a LEFT JOIN  examinationReportTemplateConfiguration AS b ON a.ertc_id = b.id WHERE sketch_id = ".$sketchId." ORDER BY a.pos ASC";
    $resulta = $connection2->query($sqla);
    $attrdata = $resulta->fetchAll();

    $sql = "SELECT table_label FROM examinationReportTemplateConfiguration GROUP BY table_label";
    $result = $connection2->query($sql);
    $labeldata = $result->fetchAll();
    //print_r($labeldata);

?>
            
    
    <div id="cloning" class="row">
			
            <div class="list-group col-6" style="margin-bottom: 15px;">
                <span style="font-size:12pt">Sketch Attributes
                <a class="btn btn-primary" id="saveAttributes">Save</a></span>
            </div>
            <div class="list-group col-6" style="margin-bottom: 15px;">
                <div id="labelDataDiv"> 
                    <select id="labeldata" class="form-control">
                        <option>Select</option>
                        <?php foreach($labeldata as $ld) { ?>
                            <option value="<?php echo $ld['table_label'];?>"><?php echo $ld['table_label'];?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <form id="attributeForm" style="width: 49%;margin-right: 10px;">
            <input type="hidden" name="sketch_id" id="sketchId" value="<?php echo $sketchId;?>">
			<div id="example3Left" class="list-group col">
                <?php if(!empty($attrdata)) { 
                    foreach($attrdata as $ad){
                ?>
                    <div class="list-group-item tinted getattributes" data-id="<?php echo $ad['id'];?>" ><?php echo $ad['report_column_label'];?> <input type="hidden" name="ertc_id[]" value="<?php echo $ad['id'];?>"></div>
                <?php       
                    } }   
                ?>
            
			</div>
            </form>
              
			<div id="example3Right" class="list-group col">
            
			</div>
    </div>

       

<?php  
}

?>

<script>
    $(function() {
        new Sortable(example3Left, {
            group: {
                name: 'shared',
                pull: 'clone' // To clone: set pull to 'clone'
            },
            animation: 150
        });  

        new Sortable(example3Right, {
            group: {
                name: 'shared',
                pull: 'clone'
            },  
            animation: 150
        });
    });
    
    $(document).on('change', '#labeldata', function() {
        var val = $(this).val();
        var type = 'getSketchLabelData';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: val, type: type },
            async: true,
            success: function(response) {
                $("#example3Right").html('');
                $("#example3Right").append(response);
            }
        });
    });

    $(document).on('click', '#saveAttributes', function() {
        var attributes = [];
        $('#example3Left').find('.getattributes').each(function() {
            attributes.push($(this).attr('data-id'));
        });    
        var skid = $("#sketchId").val();
        //alert(attributes);
        var attrid = attributes.join(",");
        var frmData = $('#attributeForm').serialize();
        if (attrid) {
            var val = skid;
            var type = 'insertSketchLabelData';
            $.ajax({
                url: 'modules/Academics/sketch_manage_attributeProcess.php',
                type: 'post',
                data: $('#attributeForm').serialize(),
                async: true,
                success: function(response) {
                    alert("Your Attribute Configuration Saved Successfully");
                    location.reload();
                }
            });
        } else {
            alert('Please Swipe Attributes');
        }
    });
</script>