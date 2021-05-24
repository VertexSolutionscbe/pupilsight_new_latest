<?php
 
 
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
//echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>';

use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Admission\AdmissionGateway;


// Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Campaign/history.php') != false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    
    $page->breadcrumbs->add(__('Applicant Status History'));

 
     if (isset($_GET['return'])) {
         returnProcess($guid, $_GET['return'], null, null);
     }

    $submission_id =  $_GET['submission_id'];

    $sql = 'Select a.*, b.officialName FROM campaign_form_status AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.submission_id = '.$submission_id.' ';
    $result = $connection2->query($sql);
    $statusdata = $result->fetchAll();

    $sqle = 'Select * FROM campaign_email_sms_sent_details WHERE submission_id = '.$submission_id.' ';
    $resulte = $connection2->query($sqle);
    $mailsmsdata = $resulte->fetchAll();

    $sql1 = 'Select a.response, a.created_at, b.officialName FROM wp_fluentform_submissions AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.id = '.$submission_id.' ';
    $resultval1 = $connection2->query($sql1);
    $submissionData = $resultval1->fetch();
    $sd = json_decode($submissionData['response'], TRUE);
    //print_r($sd);
    $names = '';
    $email = '';
    // if(!empty($sd['student_name'])){
    //     $names = implode(' ', $sd['student_name']);
    // }

    $names = $sd['student_name'];

    if(!empty($sd['father_email'])){
        $email = $sd['father_email'];
    }

    if(!empty($sd['form_id'])){
        $sqlc = 'Select form_id, offline_form_id FROM campaign WHERE form_id = '.$sd['form_id'].' OR offline_form_id = '.$sd['form_id'].' ';
        $resultc = $connection2->query($sqlc);
        $submissionData = $resultc->fetch();
    }
    
     echo '<h2>';
     echo __('Applicant Status History');
     echo '</h2>';
     
    ?>
    <table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Status Change By</th>
            <th>Status Change Date & Time</th>
        </tr>
    </thead>
    <tbody>
    <?php if(!empty($submissionData)){ ?>
        <tr>
            <th><?php echo $names; ?></th>
            <th><?php echo $email; ?></th>
            <th>Submitted</th>
            <th></th>
            <?php if(!empty($submissionData['officialName'])) { ?>
                <th><?php echo $submissionData['officialName']; ?></th>
            <?php } else { ?>
                <th><?php echo $email; ?></th>
            <?php } ?>
            <th><?php 
            $dt1 = new DateTime($submissionData['created_at']);
            $created_at1 = $dt1->format('d-m-Y H:i:s');
            
            echo $created_at1; ?></th>
        </tr>
    <?php } ?>    
    <?php if(!empty($statusdata)) { 
        foreach($statusdata as $std){ 

        ?>
        <tr>
            <th><?php echo $names; ?></th>
            <th><?php echo $email; ?></th>
            <th><?php echo $std['state']; ?></th>
            <th><?php echo $std['remarks']; ?></th>
            <th><?php echo $std['officialName']; ?></th>
            <th><?php 
            $dt = new DateTime($std['cdt']);
            $created_at= $dt->format('d-m-Y H:i:s');
            
            echo $created_at; ?></th>
        </tr>
    <?php } } ?>    
    </tbody>

    </table>

    <?php if(!empty($mailsmsdata)) { ?>
    <h2>Email Sms Sent History</h2>
     <table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Sent Via</th>
            <th>Sent To</th>
            <th>State</th>
            <th>Subject</th>
            <th>Description</th>
            <th>Date & Time</th>
        </tr>
    </thead>
    <tbody>
    <?php 
        foreach($mailsmsdata as $estd){ 
            if(!empty($estd['email'])){
                $stype = 'Email';
                $sto = $estd['email'];
            } else {
                $stype = 'Sms';
                $sto = $estd['phone'];
            }
        ?>
        <tr>
            <th><?php echo $names; ?></th>
            <th><?php echo $stype; ?></th>
            <th><?php echo $sto; ?></th>
            <th><?php echo $estd['state_name']; ?></th>
            <th><?php echo $estd['subject']; ?></th>
            <th><?php echo $estd['description']; ?></th>
            <th><?php echo $estd['cdt']; ?></th>
        </tr>
    <?php  } ?>    
    </tbody>

    </table>

        <?php    }
}
?>

