<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/index.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $id = $_GET['id'];
    $page->breadcrumbs
        ->add(__('Manage Application Form Template'), 'form_template_manage.php&id='.$id.'')
        ->add(__('Application Form Template Fields'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    

   
        // echo '<h2>';
        // echo __('Application Form Template Fields');
        // echo '</h2>';

        $sqlf = 'Select b.form_fields FROM campaign AS a LEFT JOIN wp_fluentform_forms AS b ON a.form_id = b.id WHERE a.id = '.$id.' ';
        $resultvalf = $connection2->query($sqlf);
        $fluent = $resultvalf->fetch(); 
        $field = json_decode($fluent['form_fields']);
        $fields = array();

        $arrHeader = array();
        if(!empty($field)){
            foreach($field as $fe){
                foreach($fe as $f){
                    if(!empty($f->attributes)){
                        $arrHeader[] = $f->attributes->name;
                    }
                }
            }
        }

        // echo '<pre>';
        // print_r($arrHeader);
        // echo '</pre>';
}
?>

<table class="table">
    <thead>
        <tr>
            <th>Sl No</th>
            <th>Field Name</th>
            <th>Template Field type</th>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($arrHeader)){ 
                $i = 1;
                foreach($arrHeader as $ah){   
                    $headcol = ucwords(str_replace("_", " ", $ah)); 
        ?>
        <tr>
            <td><?php echo $i;?></td>
            <td><?php echo $headcol; ?></td>
            <td><?php echo '${'.$ah.'}';?></td>
        </tr>
        <?php $i++; } } else { ?>
            <tr><td colspan="3">No Data</td></tr>
        <?php } ?>
    </tbody>

</table>