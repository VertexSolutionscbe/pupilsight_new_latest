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

    $page->breadcrumbs
        ->add(__('Manage Sketch'), 'sketch_manage.php')
        ->add(__('Manage Sketch Configurations'));
    

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h3>';
    echo __('Sketch Attribute');
    echo '<div style="float:right;"><a id="deleteAttribute" class="btn btn-primary" style="float: right;margin: -6px 0 0 5px;">Delete Attribute</a> &nbsp;&nbsp; <a id="modifyAttribute" class="btn btn-primary" style="float: right;margin: -6px 0 0 5px;">Modify Attribute</a>&nbsp;&nbsp;<a id="addAttribute" class="btn btn-primary" style="float: right;margin: -6px 0 0 5px;">Add Attribute</a>   <a id="clickModifyAttribute" data-hrf="index.php?q=/modules/Academics/sketch_manage_attribute_edit.php&id=" href="" style="display:none;">modifyattr</a></div>';
    echo '</h3>';

    $sketchId = $_GET['id'];

    $sqla = "SELECT * FROM examinationReportTemplateAttributes WHERE sketch_id = ".$sketchId." ORDER BY pos ASC";
    $resulta = $connection2->query($sqla);
    $attrdata = $resulta->fetchAll();

    $sql = "SELECT table_label FROM examinationReportTemplateConfiguration GROUP BY table_label";
    $result = $connection2->query($sql);
    $labeldata = $result->fetchAll();
    
?>
    <form id="attributeForm" method="post" action="modules/Academics/sketch_manage_attributeProcess.php" style="display:none;">
    <input type="hidden" name="sketch_id" id="sketchId" value="<?php echo $sketchId;?>">
    <div class="col-12" style="display:flex;" >
        <div class="col-3">
            <input type="text" name="attribute_name" placeholder="Attribute Name" class="w-full" id="attrname">
        </div>
        <div class="col-3">
            <select name="attribute_category" class="form-control" id="attrcat">
                <option>Select Attribute Category</option>
                <option value="Entity">Entity</option>
                <option value="Test">Test</option>
                <option value="Computed">Computed</option>
            </select>
        </div>
        <div class="col-3">
            <select id="labeldata" name="attribute_type" class="form-control" id="attrtype">
                <option>Select Type</option>
                
                <?php foreach($labeldata as $ld) { ?>
                    <option value="<?php echo $ld['table_label'];?>"><?php echo $ld['table_label'];?></option>
                <?php }  ?>
            </select>
        </div>
        <div class="col-3">
            <a class="btn btn-primary" id="saveAttributes">Add Attribute</a>
            <a class="btn btn-primary" id="saveAttributes" style="display:none;">Add Test Master</a>
        </div>
    
    </div>
    <div id="labelDataDiv" style="margin-top:10px;">
    </div>
    </form>

    <h3 id="line" style="display:none;"></h3>
    
    <div style="width:30%; margin-bottom:10px;" >
        <input type="text" class="w-full" id="searchTable" placeholder="Search">
    </div>

    <div id="cloning" class="row">

        <table class="table table-hover" id="myTable">
            <thead>
                <tr>
                    <th style="width:15%">Select</th>
                    <th style="width:15%">Attribute</th>
                    <th style="width:40%">Category</th>
                    <th style="width:30%">Types</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($attrdata)) { 
                    foreach($attrdata as $ad){
                ?>
                    <tr>
                    <td><input type="checkbox" class="attrId" name="id[]" value="<?php echo $ad['id'];?>"></td>
                    <td><?php echo $ad['attribute_name'];?></td>
                    <td><?php echo $ad['attribute_category'];?></td>
                    <td><?php echo $ad['attribute_type'];?></td>
                    </tr>
                <?php       
                    } }   
                ?>
                
            </tbody>
        </table>    
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
    
    // $(document).on('change', '#labeldata', function() {
    //     var val = $(this).val();
    //     var type = 'getSketchLabelData';
    //     $.ajax({
    //         url: 'modules/Academics/sketch_manage_attribute_plugin.php',
    //         type: 'POST',
    //         data: {val:val},
    //         async: true,
    //         success: function(response) {
    //             $("#labelDataDiv").html('');
    //             $("#labelDataDiv").append(response);
    //         }
    //     });
    // });

    
    $(document).on('click', '#addAttribute', function() {
        $("#attributeForm").show();
        $("#line").show();
    });

    $(document).on('click', '#saveAttributes', function() {
        var attrname = $("#attrname").val();
        var attrcat = $("#attrcat").val();
        var attrtype = $("#attrcat").val();

        if (attrname != '' && attrcat != '' && attrtype != '') {
           $("#attributeForm").submit();
        } else {
            alert('Please Fill all The Fields');
        }
    });

    $("#searchTable").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#myTable tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $(document).on('click', '#deleteAttribute', function() {
        var attrid = [];
        $.each($(".attrId:checked"), function() {
            attrid.push($(this).val());
        });
        var val = attrid.join(",");
        var type = "deleteSketchAttribute";
        if (attrid != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: {val: val, type: type},
                async: true,
                success: function(response) {
                   alert('Attribute Deleted Successfully');
                   location.reload();
                }
            });
        } else {
            alert('Please Select Attribute');
        }
    });

    $(document).on('click', '#modifyAttribute', function() {
        var attrid = [];
        $.each($(".attrId:checked"), function() {
            attrid.push($(this).val());
        });
        var val = attrid.join(",");
        if (attrid != '') {
            if (attrid.length == 1) {
                
                var hrf= $("#clickModifyAttribute").attr('data-hrf');
                var newhrf = hrf+val;
                $("#clickModifyAttribute").attr('href', newhrf);
                $("#clickModifyAttribute")[0].click();
            } else {
                alert('Please Select One Attribute');
            }    
        } else {
            alert('Please Select Attribute');
        }
    });
</script>