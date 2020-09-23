<?php
/*
Pupilsight, Flexible & Open School System
*/
//include '../../pupilsight.php';
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {  
   
    $type = $_POST['type'] ;
    //Proceed!

if($type == "row_wise"){ 
    $pid = $_POST['pid'];
    $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pid . '" GROUP BY a.pupilsightYearGroupID';
    $result = $connection2->query($sql);
    $classes = $result->fetchAll();  
    $data = '
    <div style=" border-bottom: 1px solid #dfdfdf;
    padding: 4px;" class="input-group stylish-input-group">
                <div class="dte mb-1"><label for="classes" class="dte inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"><b style="font-size: 14px; font-weight: 600;">Class </b><br/><span class="text-xxs text-gray italic font-normal mt-1 sm:mt-0">Use Control, Command and/or Shift to select multiple.</span></label></div><div  class=" txtfield mb-1"  style="float: right !important;  display: inline-block; width: 93%; text-align: right;"><div class="flex-1 relative"><select id="fetchClassByprogramId" name="classes[]" class="w-full txtfield" multiple size="8">';
                foreach($classes as $k=>$cls){ 
                    $data .= '<option value="'.$cls['pupilsightYearGroupID'].'" >'.$cls['name'].'</option>';
                }
                
        $data .= ' </select></div></div>
    </div>
    ';}
    echo $data;
 
}
?>
<style>
    .multiselect-container {
        height: auto !important;
    }
</style>
<script>
    $(document).ready(function() {
        $("#fetchClassByprogramId").multiselect({
            includeSelectAllOption: true
        });
    });
</script>        
