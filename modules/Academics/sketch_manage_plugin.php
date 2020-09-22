<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_plugin.php') == false) {
    //Acess denied
    echo "<div class='error'>"; 
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Manage Sketch plugin'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    echo '<h3>';
    echo __('Sketch Plugin');
    echo '<a id="saveAttrPlugin" class="btn btn-primary" style="float: right;margin: -6px 0 0 0px;">Save</a>';
    echo '</h3>';

    
    $attrId = $_GET['id'];

    $sql = "SELECT * FROM examinationReportTemplatePlugin ORDER BY pos ASC";
    $result = $connection2->query($sql);
    $plugindata = $result->fetchAll();

    // $sql = "SELECT table_label FROM examinationReportTemplateConfiguration GROUP BY table_label";
    // $result = $connection2->query($sql);
    // $labeldata = $result->fetchAll();
    // print_r($plugindata);
    // die();

?>
            
    
    <div id="cloning" class="row">
        <form id="sketchPLuginForm">
            <input type="hidden" name="erta_id" value="<?php echo $attrId;?>">
            <table class="table table-hover" style="margin: 0 70px 0px 15px;">
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Plugin</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($plugindata)) { 
                        foreach($plugindata as $pd){
                            $sqls = "SELECT * FROM examinationReportTemplatePluginAttributeMapping WHERE plugin_id = ".$pd['id']." AND erta_id = ".$attrId." ";
                            $results = $connection2->query($sqls);
                            $plumappdata = $results->fetch();
                            if(!empty($plumappdata)){
                                $pval = $plumappdata['plugin_val'];
                                $checked = 'checked';
                            } else {
                                $pval = '';
                                $checked = '';
                            }
                    ?>
                        <tr>
                            <td><input type="checkbox" class="pluginId" name="plugin_id[]" value="<?php echo $pd['id'];?>"  <?php echo $checked; ?>></td>
                            <td><?php echo $pd['name'];?></td>

                            <td>
                                <?php if($pd['id'] == '3') { ?>
                                    <select name="plugin_val[<?php echo $pd['id'];?>]" class="form-control">
                                        <option>Select</option>
                                        <option value="dd/mm/YYYY" <?php if($plumappdata['plugin_val'] == 'dd/mm/YYYY') { ?> selected <?php } ?>>DD/MM/YYYY</option>
                                        <option value="dd/mm/YY" <?php if($plumappdata['plugin_val'] == 'dd/mm/YY') { ?> selected <?php } ?>>DD/MM/YY</option>
                                        <option value="mm/dd/YYYY" <?php if($plumappdata['plugin_val'] == 'mm/dd/YYYY') { ?> selected <?php } ?>>MM/DD/YYYY</option>
                                        <option value="mm/dd/YY" <?php if($plumappdata['plugin_val'] == 'mm/dd/YY') { ?> selected <?php } ?>>MM/DD/YY</option>
                                    </select>

                                <?php } elseif($pd['id'] == '6') { ?>
                                    <select name="plugin_val[<?php echo $pd['id'];?>]" class="form-control">
                                        <option>Select</option>
                                        <option value="strtoupper" <?php if($plumappdata['plugin_val'] == 'strtoupper') { ?> selected <?php } ?>>Upper Case</option>
                                        <option value="strtolower" <?php if($plumappdata['plugin_val'] == 'strtolower') { ?> selected <?php } ?>>Lower Case</option>
                                        <option value="ucfirst" <?php if($plumappdata['plugin_val'] == 'ucfirst') { ?> selected <?php } ?>>Sentence Case</option>
                                        <option value="trim" <?php if($plumappdata['plugin_val'] == 'trim') { ?> selected <?php } ?>>Trim</option>
                                    </select>    
                                <?php } else { ?>
                                <input type="textbox" name="plugin_val[<?php echo $pd['id'];?>]" value="<?php echo $pval;?>"  class="form-control" style="background-clip: padding-box;border: 1px solid #ced4da;border-radius: 0.27em;">
                                <?php } ?>

                            </td>
                        </tr>
                        
                    <?php       
                        } }   
                    ?>
                </tbody>
            </table>
        </form>
    </div>    

			
<?php  
}

?>
