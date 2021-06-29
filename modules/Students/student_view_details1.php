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
    if ($subpage == 'Overview') {

        if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
            echo "<div class='text-right'>";
            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Students/student_edit.php&pupilsightPersonID=$pupilsightPersonID' class='btn btn-link'><span class='mdi mdi-pencil-box-outline mdi-18px mr-1'></span> Edit</a> ";
            echo '</div>';
        }

        //Medical alert!
        $alert = getHighestMedicalRisk($guid,  $pupilsightPersonID, $connection2);
        if ($alert != false) {
            $highestLevel = $alert[1];
            $highestColour = $alert[3];
            $highestColourBG = $alert[4];
            echo "<div class='alert alert-danger' style='background-color: #" . $highestColourBG . '; border: 1px solid #' . $highestColour . '; color: #' . $highestColour . "'>";
            echo '<b>' . sprintf(__('This student has one or more %1$s risk medical conditions.'), strToLower(__($highestLevel))) . '</b>';
            echo '</div>';
        }
        $cols = array(
            "officialName" => "Official Name",
            "pupilsightYearGroupID" => "Class",
            "pupilsightRollGroupID" => "Section",
            "username" => "User Name",
            "email" => "Email",
            "dob" => "Date of Birth",
            "dob" => "Age",
            "gender" => "Gender",
            "dateStart" => "Start Date"
        );
    ?>

        <div class="row">
            <?php
            $str = "";
            foreach ($cols as $id => $label) {
                $str .= "\n<div class='col-md-3 col-sm-12' id='div_basic_information'>";
                $str .= "<label class='form-label'>" . $label . "</label>testing.";
                $str .= "</div>";
            }

            echo $str;
            ?>
        </div>
<?php

    }
}
