<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$pupilsightPersonID = $_GET['sid'];

if (isActionAccessible($guid, $connection2, '/modules/Students/student_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
   
    $page->breadcrumbs
        ->add(__('Student Enrolment'), 'studentEnrolment_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Add Student Enrolment'));

   
    //Check if school year specified
    if ($pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        
        $sqla = 'SELECT * FROM pupilsightDocTemplate ';
        $resulta = $connection2->query($sqla);
        $templateData = $resulta->fetchAll();

        echo '<h2>';
        echo __('Letter');
        echo '</h2>';

?>

        <div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Template Name</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($templateData)){ 
                        foreach($templateData as $tdata) {    
                            if($tdata['type'] == 'TC'){
                                $href= "cms/generatetc.php?aid=".$pupilsightSchoolYearID."&sid=".$pupilsightPersonID."&tid=".$tdata['id']." ";
                            }
                            if($tdata['type'] == 'Study Certificate'){
                                $href= "cms/generateStudy.php?aid=".$pupilsightSchoolYearID."&sid=".$pupilsightPersonID."&tid=".$tdata['id']." ";
                            }
                            if($tdata['type'] == 'Bonafide Certificate'){
                                $href= "cms/generateBonafide.php?aid=".$pupilsightSchoolYearID."&sid=".$pupilsightPersonID."&tid=".$tdata['id']." ";
                            }
                            if($tdata['type'] == 'Conduct Certificate'){
                                $href= "cms/generateConduct.php?aid=".$pupilsightSchoolYearID."&sid=".$pupilsightPersonID."&tid=".$tdata['id']." ";
                            }
                            if($tdata['type'] == 'Fee Letter'){
                                $href= "cms/generatefeeletter.php?aid=".$pupilsightSchoolYearID."&sid=".$pupilsightPersonID."&tid=".$tdata['id']." ";
                            }
                ?>
                    <tr>
                        <td><?php echo $tdata['name'];?></td>
                        <td><a href="<?php echo $href; ?>"><i class="mdi mdi-download-circle mdi-24px"></i></a></td>
                    </tr>
                <?php } } ?>
                </tbody>
            </table>
        </div>


<?php
    }
}


