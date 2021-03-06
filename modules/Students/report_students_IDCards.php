<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_students_IDCards.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Student ID Cards'));

    echo '<p>';
    echo __('This report allows a user to select a range of students and create ID cards for those students.');
    echo '</p>';

    echo '<h2>';
    echo 'Choose Students';
    echo '</h2>';

    $choices = array();
    if (isset($_POST['pupilsightPersonID'])) {
        $choices = $_POST['pupilsightPersonID'];
    }

    $form = Form::create('action',  $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Students/report_students_IDCards.php");

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $row = $form->addRow();
    $row->addLabel('pupilsightPersonID', __('Students'));
    $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'], array("allStudents" => false, "byName" => true, "byRoll" => true))->required()->placeholder()->selectMultiple()->selected($choices);

    $row = $form->addRow();
    $row->addLabel('file', __('Card Background'))->description('.png or .jpg file, 448 x 268px.');
    $row->addFileUpload('file')
        ->accepts('.jpg,.jpeg,.png')->required();

    $row = $form->addRow();
    $row->addFooter();
    $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if (count($choices) > 0) {
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sqlWhere = ' AND (';
            for ($i = 0; $i < count($choices); ++$i) {
                $data[$choices[$i]] = $choices[$i];
                $sqlWhere = $sqlWhere . 'pupilsightPerson.pupilsightPersonID=:' . $choices[$i] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -4);
            $sqlWhere = $sqlWhere . ')';
            $sql = "SELECT officialName, image_240, dob, studentID, pupilsightPerson.pupilsightPersonID, pupilsightYearGroup.name AS year, pupilsightRollGroup.name AS roll FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID $sqlWhere ORDER BY surname, preferredName";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }

        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo 'There is not data to display in this report';
            echo '</div>';
        } else {
            echo '<p>';
            echo __('These cards are designed to be printed to credit-card size, however, they will look bigger on screen. To print in high quality (144dpi) and at true size, save the cards as an image, and print to 50% scale.');
            echo '</p>';

            //Get background image
            $bg = '';
            if (!empty($_FILES['file']['tmp_name'])) {
                $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

                $file = (isset($_FILES['file'])) ? $_FILES['file'] : null;



                $filename = $_FILES["file"]["name"]; 
                $tempname = $_FILES["file"]["tmp_name"];     
                $folder = "uploads/".$filename; 
                
                      
                // Now let's move the uploaded image into the folder: image 
                if (move_uploaded_file($tempname, $folder))  { 
                        $bg = 'background: url("'.$folder.'")';

                    }else{ 
                    echo '<div class="alert alert-danger">';
                    echo __('Your request failed due to an attachment error.');
                    echo ' ' . $fileUploader->getLastError();
                    echo '</div>';
                  } 

                  
                // Upload the file, return the /uploads relative path
                // $attachment = $fileUploader->uploadFromPost($file, 'Card_BG');


                // if (empty($attachment)) {
                //     echo '<div class="alert alert-danger">';
                //     echo __('Your request failed due to an attachment error.');
                //     echo ' ' . $fileUploader->getLastError();
                //     echo '</div>';
                // } else {
                //     $bg = 'background: url("' . $_SESSION[$guid]['absoluteURL'] . "/$attachment\") repeat left top #fff;";
                // }
            }
            
            //echo $bg;exit;
            echo "<table class='blank' cellspacing='0' style='width: 100%'>";

            $count = 0;
            $columns = 1;
            $rowNum = 'odd';
            while ($row = $result->fetch()) {
                if ($count % $columns == 0) {
                    echo '<tr>';
                }
                echo "<td style='width:" . (100 / $columns) . "%; text-align: center; vertical-align: top'>";
                echo "<div style='width: 600px; height: 1000px; border: 1px solid black; $bg'>";
                echo "<table class='blank' cellspacing='0' style='width 448px; max-width 448px; height: 268px; max-height: 268px; margin: 45px 10px 10px 10px'>";
                echo '<tr>';
                // echo "<td style='padding: 0px ; width: 150px; height: 200px; vertical-align: top' rowspan=5>";
                // if ($row['image_240'] == '' or file_exists($_SESSION[$guid]['absolutePath'] . '/' . $row['image_240']) == false) {
                //     echo "<img style='width: 150px; height: 200px' class='user' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['pupilsightThemeName'] . "/img/anonymous_240.jpg'/><br/>";
                // } else {
                //     echo "<img style='width: 150px; height: 200px' class='user' src='" . $_SESSION[$guid]['absoluteURL'] . '/' . $row['image_240'] . "'/><br/>";
                // }
                // echo '</td>';
                echo "<td style='padding: 0px ; width: 18px'></td>";
                echo "<td style='padding: 15px 0 0 0 ; text-align: left; width: 280px; vertical-align: top; font-size: 22px'>";
                echo "<div style='margin-top:450px;padding: 5px; background-color: rgba(255,255,255,0.3); min-height: 200px'>";
                $size = (strlen($row['officialName']) <= 28) ? 30 : 20;
                echo "<div style='font-weight: bold; font-size: " . $size . "px'>" . $row['officialName'] . '</div><br/>';
                echo '<b>' . __('DOB') . "</b>: <span style='float: right'><i>" . dateConvertBack($guid, $row['dob']) . '</span><br/>';
                echo '<b>' . $_SESSION[$guid]['organisationNameShort'] . ' ' . __('ID') . "</b>: <span style='float: right'><i>" . $row['studentID'] . '</span><br/>';
                echo '<b>' . __('Class/Section') . "</b>: <span style='float: right'><i>" . __($row['year']) . ' / ' . $row['roll'] . '</span><br/>';
                echo '<b>' . __('School Year') . "</b>: <span style='float: right'><i>" . $_SESSION[$guid]['pupilsightSchoolYearName'] . '</span><br/>';
                echo '</div>';
                echo '</td>';
                echo '</tr>';
                echo '</table>';
                echo '</div>';

                echo '</td>';

                if ($count % $columns == ($columns - 1)) {
                    echo '</tr>';
                }
                ++$count;
            }
            for ($i = 0; $i < $columns - ($count % $columns); ++$i) {
                echo '<td></td>';
            }

            if ($count % $columns != 0) {
                echo '</tr>';
            }
            echo '</table>';
        }
    }
}
