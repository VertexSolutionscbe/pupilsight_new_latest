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

    $sql = 'Select * FROM campaign_form_status WHERE submission_id = '.$submission_id.' ';
    $result = $connection2->query($sql);
    $statusdata = $result->fetchAll();

    $sqle = 'Select * FROM campaign_email_sms_sent_details WHERE submission_id = '.$submission_id.' ';
    $resulte = $connection2->query($sqle);
    $mailsmsdata = $resulte->fetchAll();
$names="";
$email="";
    $sql1 = 'Select response FROM wp_fluentform_submissions WHERE id = '.$submission_id.' ';
    $resultval1 = $connection2->query($sql1);
    $submissionData = $resultval1->fetch();
    $sd = json_decode($submissionData['response'], TRUE);
    if(!empty($sd)){
        $names = implode(' ', $sd['names']);
        $email = $sd['email'];
    }
    
     echo '<h2>';
     echo __('Applicant Status History');
     echo '</h2>';
     
    ?>
    <table style="width:100%">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Status Change Date & Time</th>
        </tr>
    </thead>
    <tbody>
    <?php if(!empty($statusdata)) { 
        foreach($statusdata as $std){ 
        ?>
        <tr>
            <th><?php echo $names; ?></th>
            <th><?php echo $email; ?></th>
            <th><?php echo $std['state']; ?></th>
            <th><?php 
            $dt = new DateTime($std['cdt']);
            $created_at= $dt->format('d-m-Y H:i:s');
            
            echo $created_at; ?></th>
        </tr>
    <?php } } ?>    
    </tbody>

    </table>

    <?php if(!empty($mailsmsdata)) { ?>
    <h2>Email Sms Sent History<h2>
     <table style="width:100%">
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

