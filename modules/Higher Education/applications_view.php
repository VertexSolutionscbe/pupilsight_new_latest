<?php
/*
Pupilsight, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/applications_view.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = staffHigherEducationRole($_SESSION[$guid]['pupilsightPersonID'], $connection2);
    if ($role == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the Higher Education programme.'));
    } else {
        $page->breadcrumbs->add(__('View Applications'));
        echo '<p>';
        echo "Your higher educatuion staff role is $role. The students listed below are determined by your role, and student-staff relationship assignment.";
        echo '</p>';

        try {
            if ($role == 'Coordinator') {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, higherEducationStudentID, surname, preferredName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, pupilsightRollGroup.pupilsightRollGroupID, pupilsightPersonIDAdvisor, pupilsightSchoolYear.name AS schoolYear FROM higherEducationStudent JOIN pupilsightPerson ON (higherEducationStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (higherEducationStudent.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) LEFT JOIN pupilsightSchoolYear ON (pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightPerson.pupilsightSchoolYearIDClassOf) LEFT JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' ORDER BY pupilsightSchoolYear.sequenceNumber DESC, surname, preferredName";
            } else {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'advisor' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, higherEducationStudentID, surname, preferredName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, pupilsightRollGroup.pupilsightRollGroupID, pupilsightPersonIDAdvisor, pupilsightSchoolYear.name AS schoolYear FROM higherEducationStudent JOIN pupilsightPerson ON (higherEducationStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (higherEducationStudent.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) LEFT JOIN pupilsightSchoolYear ON (pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightPerson.pupilsightSchoolYearIDClassOf) LEFT JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' AND pupilsightPersonIDAdvisor=:advisor ORDER BY pupilsightSchoolYear.sequenceNumber DESC, surname, preferredName";
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }

        if ($result->rowCount() < 1) {
            $page->addError(__('There are no students to display.'));
        } else {
            echo "<div class='linkTop'>";
            echo 'Filter Roll Group: ';

            ?>
                <script type="text/javascript">
                $(document).ready(function() {
                    $('.searchInput').val(1);
                    $('.body').find("tr:odd").addClass('odd');
                    $('.body').find("tr:even").addClass('even');

                    $(".searchInput").change(function(){
                        $('.body').find("tr").hide() ;
                        if ($('.searchInput :selected').val() == "" ) {
                            $('.body').find("tr").show() ;
                        }
                        else {
                            $('.body').find('.' + $('.searchInput :selected').val()).show();
                        }

                        $('.body').find("tr").removeClass('odd even');
                        $('.body').find('tr:visible:odd').addClass('odd');
                        $('.body').find('tr:visible:even').addClass('even');
                    });
                });
                </script>

                <select name="searchInput" class="searchInput" style='float: none; width: 100px'>
                    <option selected value=''>All</option>
                    <?php
                    try {
                        if ($role == 'Coordinator') {
                            $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                            $sqlSelect = "SELECT DISTINCT pupilsightRollGroup.nameShort AS rollGroup, pupilsightRollGroup.pupilsightRollGroupID FROM higherEducationStudent JOIN pupilsightPerson ON (higherEducationStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (higherEducationStudent.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' ORDER BY pupilsightRollGroup.nameShort";
                        } else {
                            $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'advisor' => $_SESSION[$guid]['pupilsightPersonID']);
                            $sqlSelect = "SELECT DISTINCT pupilsightRollGroup.nameShort AS rollGroup, pupilsightRollGroup.pupilsightRollGroupID FROM higherEducationStudent JOIN pupilsightPerson ON (higherEducationStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (higherEducationStudent.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' AND pupilsightPersonIDAdvisor=:advisor ORDER BY pupilsightRollGroup.nameShort";
                        }
                        $resultSelect = $connection2->prepare($sqlSelect);
                        $resultSelect->execute($dataSelect);
                    } catch (PDOException $e) {
                        $page->addError($e->getMessage());
                    }

                    while ($rowSelect = $resultSelect->fetch()) {
                        echo "<option value='".$rowSelect['pupilsightRollGroupID']."'>".htmlPrep($rowSelect['rollGroup']).'</option>';
                    }
                    ?>
                </select>
                <?php
                echo '</div>';

                echo "<table cellspacing='0' style='width: 100%'>";
                echo "<tr class='head'>";
                echo '<th>';
                echo 'Name';
                echo '</th>';
                echo '<th>';
                echo 'Roll<br/>Group';
                echo '</th>';
                echo '<th>';
                echo 'Applying';
                echo '</th>';
                echo '<th>';
                echo 'Applications';
                echo '</th>';
                echo '<th>';
                echo 'Actions';
                echo '</th>';
                echo '</tr>';
                echo "<tbody class='body'>";

                $count = 0;
                $rowNum = 'odd';
                while ($row = $result->fetch()) {
                    ++$count;

                    //COLOR ROW BY STATUS!
                    echo "<tr class='".$row['pupilsightRollGroupID']."' id='".$row['rollGroup']."' name='".$row['rollGroup']."'>";
                    echo '<td>';
                    echo formatName('', $row['preferredName'], $row['surname'], 'Student', true, true);
                    echo '</td>';
                    echo '<td>';
                    echo $row['rollGroup'];
                    echo '</td>';
                    echo '<td>';
                    try {
                        $dataAdvisor = array('pupilsightPersonID' => $row['pupilsightPersonID']);
                        $sqlAdvisor = 'SELECT * FROM higherEducationApplication LEFT JOIN higherEducationApplicationInstitution ON (higherEducationApplicationInstitution.higherEducationApplicationID=higherEducationApplication.higherEducationApplicationID) WHERE pupilsightPersonID=:pupilsightPersonID';
                        $resultAdvisor = $connection2->prepare($sqlAdvisor);
                        $resultAdvisor->execute($dataAdvisor);
                    } catch (PDOException $e) {
                        echo "<div class='warning'>";
                            echo $e->getMessage();
                        echo '</div>';
                    }

                    if ($resultAdvisor->rowCount() < 1) {
                        echo '<i>Not yet indicated.</i>';
                    } else {
                        $rowAdvisor = $resultAdvisor->fetch();
                        echo $rowAdvisor['applying'];
                    }
                    echo '</td>';
                    echo '<td>';
                    echo $resultAdvisor->rowCount();
                    echo '</td>';
                    echo '<td>';
                    if ($resultAdvisor->rowCount() > 0 and $rowAdvisor['applying'] == 'Y') {
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/applications_view_details.php&pupilsightPersonID='.$row['pupilsightPersonID']."'><img title='Details' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_right.png'/></a> ";
                    }
                    echo '</td>';
                    echo '</tr>';
                }
            echo '</tbody>';
            echo '</table>';
        }
    }
}
?>
