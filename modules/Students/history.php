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

    $pupilsightPersonID =  $_GET['pupilsightPersonID'];


    $sqle = 'SELECT a.*, b.officialName, c.officialName as uname FROM user_email_sms_sent_details AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightPerson AS c ON a.uid = c.pupilsightPersonID WHERE a.pupilsightPersonID = '.$pupilsightPersonID.' ';
    $resulte = $connection2->query($sqle);
    $mailsmsdata = $resulte->fetchAll();
    
     echo '<h2>';
     echo __('Student Message History');
     echo '</h2>';
     
    ?>
    
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Sent Via</th>
                <th>Sent To</th>
                <th>Subject</th>
                <th>Description</th>
                <th>Sent By</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if(!empty($mailsmsdata)) { 
                foreach($mailsmsdata as $estd){ 
                    $stype = '';
                    $sto = '';
                    if($estd['email'] != 'NULL'){
                        $stype = 'Email';
                        $sto = $estd['email'];
                    } else {
                        $stype = 'Sms';
                        $sto = $estd['phone'];
                    }
                ?>
                <tr>
                    <th><?php echo $estd['officialName']; ?></th>
                    <th><?php echo $stype; ?></th>
                    <th><?php echo $sto; ?></th>
                    <th><?php echo $estd['subject']; ?></th>
                    <th><?php echo $estd['description']; ?></th>
                    <th><?php echo $estd['uname']; ?></th>
                    <th><?php echo $estd['cdt']; ?></th>
                </tr>
            <?php  } } else { ?> 
                <tr>
                    <th colspan="7">No Message History</th>
                </tr>
            <?php } ?>
        </tbody>

    </table>

<?php   
}
?>

