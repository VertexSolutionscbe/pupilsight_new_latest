<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/applicationForm_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    echo '<h2>';
    echo __('Staff Application Form Printout');
    echo '</h2>';

    $pupilsightStaffApplicationFormID = $_GET['pupilsightStaffApplicationFormID'];
    $search = '';
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
    }

    if ($pupilsightStaffApplicationFormID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        //Proceed!
        try {
            $data = array('pupilsightStaffApplicationFormID' => $pupilsightStaffApplicationFormID);
            $sql = 'SELECT pupilsightStaffApplicationForm.*, pupilsightStaffJobOpening.jobTitle, pupilsightStaffJobOpening.type FROM pupilsightStaffApplicationForm JOIN pupilsightStaffJobOpening ON (pupilsightStaffApplicationForm.pupilsightStaffJobOpeningID=pupilsightStaffJobOpening.pupilsightStaffJobOpeningID) LEFT JOIN pupilsightPerson ON (pupilsightStaffApplicationForm.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStaffApplicationFormID=:pupilsightStaffApplicationFormID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There is no data to display, or an error has occurred.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            echo '<h4>'.__('For Office Use').'</h4>';
            echo "<table cellspacing='0' style='width: 100%'>";
            echo '<tr>';
            echo "<td style='width: 25%; padding-top: 15px; vertical-align: top'>";
            echo "<span class='form-label'>".__('Application ID').'</span><br/>';
            echo '<i>'.htmlPrep($row['pupilsightStaffApplicationFormID']).'</i>';
            echo '</td>';
            echo "<td style='width: 25%; padding-top: 15px; vertical-align: top'>";
            echo "<span class='form-label'>".__('Priority').'</span><br/>';
            echo '<i>'.htmlPrep($row['priority']).'</i>';
            echo '</td>';
            echo "<td style='width: 50%; padding-top: 15px; vertical-align: top'>";
            echo "<span class='form-label'>".__('Status').'</span><br/>';
            echo '<i>'.htmlPrep($row['status']).'</i>';
            echo '</td>';
            echo '</tr>';
            echo '<tr>';
            echo "<td style='padding-top: 15px; vertical-align: top'>";
            echo "<span class='form-label'>".__('Start Date').'</span><br/>';
            echo '<i>'.dateConvertBack($guid, $row['dateStart']).'</i>';
            echo '</td>';
            echo "<td style='padding-top: 15px; vertical-align: top'>";
            echo "<span class='form-label'>".__('Milestones').'</span><br/>';
            echo '<i>'.htmlPrep($row['milestones']).'</i>';
            echo '</td>';
            echo "<td style='padding-top: 15px; vertical-align: top'>";

            echo '</td>';
            echo '</tr>';
            if ($row['notes'] != '') {
                echo '<tr>';
                echo "<td style='padding-top: 15px; vertical-align: top' colspan=3>";
                echo "<span class='form-label'>".__('Notes').'</span><br/>';
                echo '<i>'.$row['notes'].'</i>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';

            echo '<h4>'.__('Job Related Information').'</h4>';
            echo "<table cellspacing='0' style='width: 100%'>";
            echo '<tr>';
            echo "<td style='width: 33%; padding-top: 15px; vertical-align: top' colspan=2>";
            echo "<span class='form-label'>".__('Job Opening').'</span><br/>';
            echo '<i>'.htmlPrep($row['jobTitle']).'</i>';
            echo '</td>';
            echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
            echo "<span class='form-label'>".__('Job Type').'</span><br/>';
            echo '<i>'.htmlPrep($row['type']).'</i>';
            echo '</td>';
            echo '</tr>';
            echo '<tr>';
            echo "<td style='width: 33%; padding-top: 15px; vertical-align: top' colspan=3>";
            echo "<span class='form-label'>".__('Application Questions').'</span><br/>';
            echo '<i>'.addSlashes($row['questions']).'</i>';
            echo '</td>';
            echo '</tr>';
            echo '</table>';

            echo '<h4>'.__('Applicant Details').'</h4>';
            echo "<table cellspacing='0' style='width: 100%'>";
            if ($row['pupilsightPersonID'] != '') {
                echo '<tr>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Internal Candidate').'</span><br/>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Surname').'</span><br/>';
                echo '<i>'.htmlPrep($row['surname']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Preferred Name').'</span><br/>';
                echo '<i>'.htmlPrep($row['preferredName']).'</i>';
                echo '</td>';
                echo '</tr>';
            } else {
                echo '<tr>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Surname').'</span><br/>';
                echo '<i>'.htmlPrep($row['surname']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Preferred Name').'</span><br/>';
                echo '<i>'.htmlPrep($row['preferredName']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Official Name').'</span><br/>';
                echo '<i>'.htmlPrep($row['officialName']).'</i>';
                echo '</td>';
                echo '</tr>';
                echo '<tr>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Gender').'</span><br/>';
                echo '<i>'.htmlPrep($row['gender']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Date of Birth').'</span><br/>';
                echo '<i>'.dateConvertBack($guid, $row['dob']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";

                echo '</td>';
                echo '</tr>';
                echo '<tr>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('First Language').'</span><br/>';
                echo '<i>'.htmlPrep($row['languageFirst']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Second Language').'</span><br/>';
                echo '<i>'.htmlPrep($row['languageSecond']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Third Language').'</span><br/>';
                echo '<i>'.htmlPrep($row['languageThird']).'</i>';
                echo '</td>';
                echo '</tr>';
                echo '<tr>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Country of Birth').'</span><br/>';
                echo '<i>'.htmlPrep($row['countryOfBirth']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Citizenship').'</span><br/>';
                echo '<i>'.htmlPrep($row['citizenship1']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Passport Number').'</span><br/>';
                echo '<i>'.htmlPrep($row['citizenship1Passport']).'</i>';
                echo '</td>';
                echo '</tr>';
                echo '<tr>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>";
                if ($_SESSION[$guid]['country'] == '') {
                    echo '<b>'.__('National ID Card Number').'</b>';
                } else {
                    echo '<b>'.$_SESSION[$guid]['country'].' '.__('ID Card Number').'</b>';
                }
                echo '</span><br/>';
                echo '<i>'.htmlPrep($row['nationalIDCardNumber']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>";
                if ($_SESSION[$guid]['country'] == '') {
                    echo '<b>'.__('Residency/Visa Type').'</b>';
                } else {
                    echo '<b>'.$_SESSION[$guid]['country'].' '.__('Residency/Visa Type').'</b>';
                }
                echo '</span><br/>';
                echo '<i>'.htmlPrep($row['residencyStatus']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>";
                if ($_SESSION[$guid]['country'] == '') {
                    echo '<b>'.__('Visa Expiry Date').'</b>';
                } else {
                    echo '<b>'.$_SESSION[$guid]['country'].' '.__('Visa Expiry Date').'</b>';
                }
                echo '</span><br/>';
                echo '<i>'.dateConvertBack($guid, $row['visaExpiryDate']).'</i>';
                echo '</td>';
                echo '</tr>';
                echo '<tr>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Email').'</span><br/>';
                echo '<i>'.htmlPrep($row['email']).'</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                echo "<span class='form-label'>".__('Phone').'</span><br/>';
                echo '<i>';
                if ($row['phone1Type'] != '') {
                    echo htmlPrep($row['phone1Type']).': ';
                }
                if ($row['phone1CountryCode'] != '') {
                    echo htmlPrep($row['phone1CountryCode']).' ';
                }
                echo htmlPrep(formatPhone($row['phone1'])).' ';
                echo '</i>';
                echo '</td>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";

                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }
}
