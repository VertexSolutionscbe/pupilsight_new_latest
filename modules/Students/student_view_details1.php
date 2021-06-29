<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Module\Attendance\StudentHistoryData;
use Pupilsight\Module\Attendance\StudentHistoryView;
use Pupilsight\Domain\Students\StudentGateway;
use Pupilsight\Domain\System\CustomField;

//Module includes for User Admin (for custom fields)
include './modules/User Admin/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {

    function loadRowData($data, $col = 2)
    {
        $str = "";
        $len = count($data);
        $i = 0;
        while ($i < $len) {
            $dt = $data[$i];
            $str .= "\n<div class='py-3 lh-sm col-md-" . $col . " col-sm-12 border-bottom' id='" . $dt["id"] . "'>";
            $str .= "<label>" . $dt["label"] . "</label><label class='form-label'>" . $dt["val"] . "</label>";
            $str .= "</div>";
            $i++;
        }
        return $str;
    }

    function setdt($val)
    {
        $rv = "NA";
        if ($val) {
            $rv = $val;
        }
        return $rv;
    }

    $pupilsightPersonID = "";
    if (isset($_GET['pupilsightPersonID'])) {
        $pupilsightPersonID = $_GET['pupilsightPersonID'];
    } else {
        echo "error page";
        die();
    }

    $subpage = "Overview";
    if (isset($_GET['subpage'])) {
        $subpage = $_GET['subpage'];
    }

    if ($_SESSION[$guid]['absoluteURL'] == "https://amaatra.pupilpod.net") {
        $st = array("Overview", "Personal", "Family", "Academic", "Emergency");
    } else {
        $st = array("Overview", "Personal", "Family", "Emergency", "Attendance", "Library Borrowing", "Activities", "Homework", "Behaviour", "Academic");
    }


?>

    <div class="my-4">
        <ul class="nav nav-tabs" data-toggle="tabs">
            <?php
            $len = count($st);
            $i = 0;

            while ($i < $len) {
                $stactive = "";
                if ($subpage == $st[$i]) {
                    $stactive = " active";
                }
            ?>
                <li class="nav-item">
                    <a href="index.php?q=/modules/Students/student_view_details1.php&pupilsightPersonID=<?= $pupilsightPersonID . "&subpage=" . $st[$i] ?>" class="nav-link <?= $stactive; ?>"><?= $st[$i] ?></a>
                </li>
            <?php
                $i++;
            }
            ?>
        </ul>
    </div>

    <?php
    //if ($subpage == 'Overview') {

    if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
        echo "<div class='text-right'>";
        echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Students/student_edit.php&pupilsightPersonID=$pupilsightPersonID' class='btn btn-primary my-2'><span class='mdi mdi-pencil-box-outline mdi-18px mr-1'></span> Edit</a> ";
        echo '</div>';
    }

    try {
        //get student all information and class and section
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = "SELECT DISTINCT pupilsightPerson.*,pupilsightStudentEnrolment.*  
            FROM pupilsightPerson
            LEFT JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
            WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID";
        //echo $sql;
        $result = $connection2->prepare($sql);
        $result->execute($data);
        $row = $result->fetch();
        $studentImage = $row['image_240'];
    } catch (Exception $ex) {
        echo "Student Details: " . $ex->getMessage();
    }

    try {
        //get class name
        $dataDetail = array('pupilsightYearGroupID' => $row['pupilsightYearGroupID']);
        $sqlDetail = 'SELECT * FROM pupilsightYearGroup 
            WHERE pupilsightYearGroupID=:pupilsightYearGroupID';
        $resultDetail = $connection2->prepare($sqlDetail);
        $resultDetail->execute($dataDetail);
        $rowDetail = $resultDetail->fetch();
        $className = $rowDetail['name'];
    } catch (Exception $ex) {
        echo "Student Class: " . $ex->getMessage();
    }

    try {
        //get section name
        $dataDetail = array('pupilsightRollGroupID' => $row['pupilsightRollGroupID']);
        $sqlDetail = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightRollGroupID=:pupilsightRollGroupID';
        $resultDetail = $connection2->prepare($sqlDetail);
        $resultDetail->execute($dataDetail);
        $rowDetail = $resultDetail->fetch();
        $sectionName = $rowDetail['name'];
    } catch (Exception $ex) {
        echo "Student Class: " . $ex->getMessage();
    }

    $age = "NA";
    $dob = "NA";
    if ($row['dob']) {
        $age = Format::age($row['dob']);
        $dob = date("d-M-Y", strtotime($row['dob']));
    }

    $startDate = "NA";
    if ($row['dateStart']) {
        $startDate = date("d-M-Y", strtotime($row['dateStart']));
    }


    $bi[] = array("id" => "officialName", "label" => "Student Name", "val" => ucwords($row['officialName']));
    $bi[] = array("id" => "username", "label" => "User Name", "val" => $row['username']);
    $bi[] = array("id" => "pupilsightYearGroupID", "label" => "Class", "val" => $className);
    $bi[] = array("id" => "pupilsightRollGroupID", "label" => "Section", "val" => $sectionName);
    $bi[] = array("id" => "dob", "label" => "Date of Birth", "val" => $dob);
    $bi[] = array("id" => "age", "label" => "Age", "val" => $age);
    $bi[] = array("id" => "gender", "label" => "Gender", "val" => $row['gender']);
    $bi[] = array("id" => "dateStart", "label" => "Start Date", "val" => $startDate);

    //$cols[] = array("id" => "email", "label" => "Email", "val" => $email);

    ?>
    <div class="hr-text">
        <h3 class='text-primary'>Basic Information</h3>
    </div>

    <div class="row border-top" id='div_basic_information'>
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <?php
                        echo loadRowData($bi, 6);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="hr-text">
        <h3 class='text-primary'>Contact Information</h3>
    </div>
    <div class="row border-top" id='div_basic_information'>
        <?php
        $ci[] = array("id" => "address1", "label" => "Address", "val" => $row['address1']);
        $ci[] = array("id" => "email", "label" => "Email", "val" => setdt($row['email']));
        $ci[] = array("id" => "phone", "label" => "Phone", "val" => setdt($row['phone1']));

        echo loadRowData($ci, 4);
        ?>
    </div>
    <div class="hr-text">
        <h3 class='text-primary'>Background Information</h3>
    </div>
    <div class="row border-top" id='div_basic_information'>
        <?php
        $binfo[] = array("id" => "nationality", "label" => "Nationality", "val" => setdt($row['nationality']));
        $binfo[] = array("id" => "citizenship1", "label" => "Citizenship 1", "val" => setdt($row['citizenship1']));
        $binfo[] = array("id" => "citizenship2", "label" => "Citizenship 2", "val" => setdt($row['citizenship2']));
        $binfo[] = array("id" => "countryOfBirth", "label" => "Country of Birth", "val" => $row['countryOfBirth']);
        $binfo[] = array("id" => "religion", "label" => "Religion", "val" => setdt($row['religion']));
        $binfo[] = array("id" => "languageFirst", "label" => "First Language", "val" => setdt($row['languageFirst']));
        $binfo[] = array("id" => "languageSecond", "label" => "Second Language", "val" => setdt($row['languageSecond']));
        $binfo[] = array("id" => "languageThird", "label" => "Third Language", "val" => setdt($row['languageThird']));
        $binfo[] = array("id" => "year_joined", "label" => "Year Joined", "val" => setdt($row['year_joined']));
        $binfo[] = array("id" => "ethnicity", "label" => "Ethnicity", "val" => setdt($row['ethnicity']));

        echo loadRowData($binfo);
        ?>
    </div>


    <div class="hr-text">
        <h3 class='text-primary'>System Information</h3>
    </div>
    <div class="row border-top" id='div_basic_information'>
        <?php
        $cont[] = array("id" => "username", "label" => "User Name", "val" => setdt($row['username']));
        $cont[] = array("id" => "canLogin", "label" => "Can Login?", "val" => setdt($row['canLogin']));
        $cont[] = array("id" => "lastIPAddress", "label" => "Last IP Address", "val" => setdt($row['lastIPAddress']));

        echo loadRowData($cont, 4);
        ?>
    </div>
<?php

    //}
}
