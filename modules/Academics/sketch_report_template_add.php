<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$name = $session->get('file_name_tmp');
$file = $session->get('file_doc_tmp');
if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $sketch_id = $_GET['id'];

    $page->breadcrumbs
        ->add(__('Manage Sketch Template'), 'sketch_report_template_manage.php&id=' . $sketch_id . '')
        ->add(__('Add Sketch Template'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

        

        $sql = 'SELECT * FROM examinationReportTemplateSketch WHERE id = "' . $sketch_id . '" ';
        $result = $connection2->query($sql);
        $sketchData = $result->fetch();


        $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightSchoolYearID = ' . $sketchData['pupilsightSchoolYearID'] . ' AND a.pupilsightProgramID = ' . $sketchData['pupilsightProgramID'] . ' AND a.pupilsightYearGroupID IN (' . $sketchData['class_ids'] . ') GROUP BY a.pupilsightYearGroupID';
        $result = $connection2->query($sql);
        $classeData = $result->fetchAll();

        $class = array();
        $class2 = array();
        $class1 = array('' => 'Select Class');
        foreach ($classeData as $dt) {
            $class2[$dt['pupilsightYearGroupID']] = $dt['name'];
        }
        $class = $class1 + $class2;

        $sqlimg = 'SELECT a.* FROM examinationReportTemplateAttributes AS a LEFT JOIN examinationReportTemplateConfiguration AS b ON a.ertc_id = b.id WHERE a.sketch_id = '.$sketch_id.' AND b.type= "image" ';
        $resultimg = $connection2->query($sqlimg);
        $imgData = $resultimg->fetchAll();
        //print_r($imgData);
   
        echo '<h2>';
        echo __('Add Sketch Template');
        echo '</h2>';

        $form = Form::create('reportTemplate', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/sketch_report_template_addProcess.php?address='.$_SESSION[$guid]['address']);

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('sketch_id', $sketch_id);
        $form->addHiddenValue('pupilsightSchoolYearID', $sketchData['pupilsightSchoolYearID']);
        $form->addHiddenValue('pupilsightProgramID', $sketchData['pupilsightProgramID']);


        $row = $form->addRow();
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->required();

        $row = $form->addRow();
        $row->addLabel('pupilsightYearGroupID', __('Class'));
        $row->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDbyPP')->fromArray($class)->required()->placeholder('Select Class');

        $row = $form->addRow();
        $row->addLabel('file', __('Template'));
        $row->addFileUpload('file')->accepts('.pdf')->setMaxUpload(false)->required();

        $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();
        echo $form->getOutput();
}
?>

<?php if(!empty($imgData)) { ?>
    <div style="margin-top:100px;">
        <h3>Configure Image Fields</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Sl No</th>
                    <th>Attribute Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach($imgData as $imd) { ?>
                    <tr>
                        <th><?= $i; ?></th>
                        <th><?= $imd['attribute_name']; ?></th>
                        <th><a class="btn btn-white thickbox" href="fullscreen.php?q=/modules/Academics/sketch_report_template_configure_image.php&id=<?= $imd['id']; ?>&skid=<?= $imd['sketch_id']; ?>">Set Parameter</a></th>
                    </tr>
                <?php $i++; } ?>
            </tbody>
        </table>
    </div>
<?php } ?>




<!-- <script type="text/javascript">
    $('#edit_template_form').on('submit',(function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url:"ajaxSwitch.php", 
        type: "POST",             
        data: formData, 
        contentType: false,      
        cache: false,             
        processData:false, 
        async: false,       
        success: function(data)  
        {
           
            alert(data);
            window.location.reload()
        }
    });

    }));
</script> -->