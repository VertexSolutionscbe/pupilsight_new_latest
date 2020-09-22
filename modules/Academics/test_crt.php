<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Academics/create_test.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Create Test'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
?>

<h2>Crate Test</h2>
<form name="">
    <table class="smallIntBorder fullWidth standardForm relative" cellspacing="0">

        <tbody>
            <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
                <td colspan="3" class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                    <table class="tablewidth" border="0">
                        <tr>
                            <th>Program</th>
                            <th>Test Status</th>
                        <tr>
                        <tr class="header">
                            <td> <i class="fa fa-chevron-circle-down rotate padding"></i><input type="checkbox">CBSE
                            </td>
                            <td><i class="fas fa-times fa-2x"></i></td>
                        </tr>
                        <tr>
                            <td><span class="childrow"><input type="checkbox">LkG</span></td>
                            <td><i class="fas fa-times fa-2x"></i></td>
                        </tr>
                        <tr>
                            <td><span class="childrow abcd"><input type="checkbox">LkG I</span></td>
                            <td><i class="fas fa-times fa-2x"></i></td>
                        </tr>
                        <tr>
                            <td><span class="childrow"><input type="checkbox">UkG</span></td>
                            <td><i class="fas fa-times fa-2x"></i></td>
                        </tr>
                        <tr>
                            <td><span class="childrow"><input type="checkbox">1ST stand</span></td>
                            <td><i class="fas fa-times fa-2x"></i></td>
                        </tr>
                        <tr>
                            <td><span class="childrow"><input type="checkbox">2nd stand </span></td>
                            <td><i class="fas fa-times fa-2x"></i></td>
                        </tr>


                    </table>
                </td>


                <td colspan='2' class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">

                    <?php  $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/ac_manage_skill_addProcess.php');
                $form->setFactory(DatabaseFormFactory::create($pdo));
                    echo "<a style='display:none' id='seletcategories' href='fullscreen.php?q=/modules/Academics/select_sub_categories.php&width=1000'  class='thickbox '> unassign staff</a>"; 
                $form->addHiddenValue('address', $_SESSION[$guid]['address']); 

                    $row = $form->addRow();
                    $row->addLabel('testname', __('Name'));
                    $row->addTextField('testname')->maxLength(40)->required();
                    $row = $form->addRow();
                    $types = array(__('Select') => array ('Term1' => __('Term 1'), 'Term2' => __('Term 2')));
                    $row->addLabel('test_type', __('Test type'));
                    $row->addSelect('option')->fromArray($types)->placeholder()->required();


                    $row = $form->addRow();
                    // $row->addLabel('Academic Year', __('Academic Year'));
                    $row->addRadio('select_subject')->setID('select_sub')->fromArray(array('All' => __('Include All Subject'), 'cat' => __('Select Subject Categories')))->checked('All')->inline();

                    $row = $form->addRow();
                    $method = array(__('Basic') => array ('Marks' => __('Marks'), 'Grade' => __('Grade')));
                    $row->addLabel('assignment_method', __('Assignment Method'));
                    $row->addSelect('method')->fromArray($method)->placeholder()->required();
                    $row = $form->addRow();
                    $option = array(__('Basic') => array ('Dropdown' => __('Dropdown'), 'radio_botton' => __('Radio Button')));
                    $row->addLabel('assignment_method', __('Assignment Option'));
                    $row->addSelect('option')->fromArray($option)->placeholder()->required();
                    $row = $form->addRow();
                    $row->addLabel('max_mark', __('Max Marks'));
                    $row->addTextField('max_mark')->maxLength(40)->required();
                    $row = $form->addRow();
                    $row->addLabel('min_mark', __('Min Marks'));
                    $row->addTextField('min_mark')->maxLength(40)->required();
                    $row = $form->addRow();
                    $row->addLabel('grade_system', __('Grading System'));
                    $row->addTextField('grade_system')->maxLength(40)->required();
                    
                    $row = $form->addRow();                  
                    $row->addCheckbox('enable_mark')->description(__('Enable Marks'))->addClass(' dte')->required();
                    $row->addCheckbox('enable_shedule')->description(__('Enable Shedule'))->addClass(' dte')->required();

                    $row = $form->addRow();
                    $row->addLabel('St_date', __('Start Date'));
                    $row->addDate('St_date')->maxLength(40)->required();
               
                    $row = $form->addRow();
                    $row->addLabel('start_time', __('start Time'));
                    $row->addNumber('start_time')->maxLength(40)->required();

                    $row = $form->addRow();
                    $row->addLabel('end_date', __('End Date'));
                    $row->addDate('end_date')->maxLength(40)->required();
               
                    $row = $form->addRow();
                    $row->addLabel('end_time', __('end Time'));
                    $row->addNumber('end_time')->maxLength(40)->required();


                    $row = $form->addRow();
                    $row->addFooter();
                    $row->addLabel('', __(''));
                    $row->addContent(' ');  
                    $row->addSubmit(__('Save'));
                    $row->addContent('<a  id="" class=" btn btn-primary" style=" font-size: 13px !important;">Delete</a>');  
                   
            
                echo $form->getOutput();?>
                </td>
            </tr>

        </tbody>
    </table>
    </div>
</form>
<script>

</script>

<style>
.fa-times {
    color: red;
}

.rotate {
    -moz-transition: all .5s linear;
    -webkit-transition: all .5s linear;
    transition: all .5s linear;
}

.rotate.down {
    -moz-transform: rotate(-90deg);
    -webkit-transform: rotate(-90deg);
    transform: rotate(-90deg);
}

tr.header {
    cursor: pointer;
}

.padding {
    padding: 5px;
}

.tablewidth {
    width: 100% !important;
}

.childrow {
    padding-left: 38px;

}
</style>

<?php
  
}