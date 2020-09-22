<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Admission\AdmissionGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/email_sms_template.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $type = $_GET['type'];
    $wsid = $_GET['wsid'];
    if($type == '1'){
        $ntype = "'Email'";
        $templ = 'Email Template';
    } else if($type == '2'){
        $ntype = "'Sms'";
        $templ = 'Sms Template';
    } else {
        $ntype = "'Email','Sms'";
        $templ = 'Email & Sms Template';
    }
    $page->breadcrumbs->add(__($templ));
    
    //die();

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    
    $sql = 'SELECT * FROM pupilsightTemplate WHERE type IN ('.$ntype.')';
    $result = $connection2->query($sql);
    $template = $result->fetchAll();

    // echo '<pre>';
    // print_r($template);
    // echo '</pre>';
    // die();

   
    echo "<h4>".$templ." <a id='configureTemplate' data-id='".$wsid."' data-type='".$type."' class='btn btn-primary right_align' style='margin-top: -10px;'>Save</a></h4>";


?>

<?php if($type == '3') { ?>
    <h4>Email Template<h4>
    <table style="width:100%">
        <thead>
            <tr>
                <th>SI No</th>
                <th>Template Name</th>
                <th>Template Type</th>
                <th>State</th>
                <th>Description</th>
                <th>Action</th>
                <th>Select</th>
            </tr>    
        </thead>
        <tbody>
            <?php if(!empty($template)){
                $i = 1;
                foreach($template as $fg){    
                    if($fg['state'] == '1'){
                        $state = 'In Use';
                    } else {
                        $state = 'Not In Use';
                    }
                    if($fg['type'] == 'Email'){
                        $href= $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/School+Admin%2Femail_template_manage_edit.php&pupilsightTemplateID=".$fg['pupilsightTemplateID'];
                    
            ?>
                <tr>
                    <td><?php echo $i;?></td>
                    <td><?php echo $fg['name'];?></td>
                    <td><?php echo $fg['type'];?></td>
                    <td><?php echo $state;?></td>
                    <td><?php echo $fg['description'];?></td>
                    <td><a href="<?php echo $href;?>" target="_blank"><i title="Edit" class="fas fa-edit px-2"></i></a></td>
                    <td><input type="checkbox" data-nme="<?php echo $fg['name'];?>" class="email-pupilsightTemplateID" value="<?php echo $fg['pupilsightTemplateID'];?>" ></td>
                </tr>    
            <?php $i++; } } } ?>
        </tbody>
    </table>
    
    <h4>Sms Template<h4>
    <table style="width:100%">
        <thead>
            <tr>
                <th>SI No</th>
                <th>Template Name</th>
                <th>Template Type</th>
                <th>State</th>
                <th>Description</th>
                <th>Action</th>
                <th>Select</th>
            </tr>    
        </thead>
        <tbody>
            <?php if(!empty($template)){
                $i = 1;
                foreach($template as $fg){    
                    
                    if($fg['state'] == '1'){
                        $state = 'In Use';
                    } else {
                        $state = 'Not In Use';
                    }
                    if($fg['type'] == 'Sms'){
                        $href= $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/School+Admin%2Fsms_template_manage_edit.php&pupilsightTemplateID=".$fg['pupilsightTemplateID'];
                    
            ?>
                <tr>
                    <td><?php echo $i;?></td>
                    <td><?php echo $fg['name'];?></td>
                    <td><?php echo $fg['type'];?></td>
                    <td><?php echo $state;?></td>
                    <td><?php echo $fg['description'];?></td>
                    <td><a href="<?php echo $href;?>" target="_blank"><i title="Edit" class="fas fa-edit px-2"></i></a></td>
                    <td><input type="checkbox" data-nme="<?php echo $fg['name'];?>" class="sms-pupilsightTemplateID" value="<?php echo $fg['pupilsightTemplateID'];?>" ></td>
                </tr>    
            <?php $i++; } } } ?>
        </tbody>
    </table>



<?php } else { ?>
<table style="width:100%">
        <thead>
            <tr>
                <th>SI No</th>
                <th>Template Name</th>
                <th>Template Type</th>
                <th>State</th>
                <th>Description</th>
                <th>Action</th>
                <th>Select</th>
            </tr>    
        </thead>
        <tbody>
            <?php if(!empty($template)){
                $i = 1;
                foreach($template as $fg){    
                    if($fg['state'] == '1'){
                        $state = 'In Use';
                    } else {
                        $state = 'Not In Use';
                    }
                    if($fg['type'] == 'Email'){
                        $href= $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/School+Admin%2Femail_template_manage_edit.php&pupilsightTemplateID=".$fg['pupilsightTemplateID'];
                    } else {
                        $href= $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/School+Admin%2Fsms_template_manage_edit.php&pupilsightTemplateID=".$fg['pupilsightTemplateID'];
                    }
            ?>
                <tr>
                    <td><?php echo $i;?></td>
                    <td><?php echo $fg['name'];?></td>
                    <td><?php echo $fg['type'];?></td>
                    <td><?php echo $state;?></td>
                    <td><?php echo $fg['description'];?></td>
                    <td><a href="<?php echo $href;?>" target="_blank"><i title="Edit" class="fas fa-edit px-2"></i></a></td>
                    <td><input type="checkbox" data-nme="<?php echo $fg['name'];?>" class="pupilsightTemplateID" value="<?php echo $fg['pupilsightTemplateID'];?>" ></td>
                </tr>    
            <?php $i++; } } ?>
        </tbody>
    </table>

<?php } }?>
<style>
    .mb-1 label {
        height: auto !important;
    }
</style>

<?php
