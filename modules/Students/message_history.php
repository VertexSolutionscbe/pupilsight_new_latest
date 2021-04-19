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


if (isActionAccessible($guid, $connection2, '/modules/Students/student_view.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    
    $page->breadcrumbs->add(__('Message History'));

 
     if (isset($_GET['return'])) {
         returnProcess($guid, $_GET['return'], null, null);
     }

    $pupilsightPersonID =  $_GET['pupilsightPersonID'];

    $startDate = '';
    $endDate = '';
    $stDate = '';
    $enDate = '';
    $sdate = '';
    if($_POST){
        if(!empty($_POST['start_date'])){
            $sdate = str_replace('/', '-', $_POST['start_date']);
            $start_date = date('Y-m-d', strtotime($sdate)).'  00:00:01';
            $startDate = date('Y-m-d', strtotime($sdate));
            $stDate = $_POST['start_date'];
        } else {
            $start_date = date('2019-01-01', strtotime($sdate)).'  00:00:01';
            $stDate = '';
        }
            
            
        if(!empty($_POST['end_date'])){
            $edate = str_replace('/', '-', $_POST['end_date']);
            $end_date = date('Y-m-d', strtotime($edate)).'  23:59:59';
            $endDate = date('Y-m-d', strtotime($edate));
            $enDate = $_POST['end_date'];
        } else {
            if(!empty($sdate)){
                $end_date = date('Y-m-d', strtotime($sdate)).'  23:59:59';
            } else {
                $end_date = date('Y-m-d').'  23:59:59';
            }
            $enDate = '';
        }

        $type = $_POST['type'];

        $sqle = 'SELECT a.*, b.officialName, c.officialName as uname FROM user_email_sms_sent_details AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightPerson AS c ON a.uid = c.pupilsightPersonID WHERE  a.sent_to = "1" AND a.cdt >= "'.$start_date.'" AND a.cdt <= "'.$end_date.'" ';
        if(!empty($type)){
            $sqle .= 'AND a.type = '.$type.'';
        }
        $sqle .= ' ORDER BY a.id DESC ';
        //echo $sqle;
        // die();
        $resulte = $connection2->query($sqle);
        $mailsmsdata = $resulte->fetchAll();
        // } else {
        //     $sqle = 'SELECT a.*, b.officialName, c.officialName as uname FROM user_email_sms_sent_details AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightPerson AS c ON a.uid = c.pupilsightPersonID WHERE a.sent_to = "1" ORDER BY a.id DESC';
        //     $resulte = $connection2->query($sqle);
        //     $mailsmsdata = $resulte->fetchAll();
        // }
        
    } else {
        $sqle = 'SELECT a.*, b.officialName, c.officialName as uname FROM user_email_sms_sent_details AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightPerson AS c ON a.uid = c.pupilsightPersonID WHERE a.sent_to = "1" ORDER BY a.id DESC';
        $resulte = $connection2->query($sqle);
        $mailsmsdata = $resulte->fetchAll();
    }

    // echo '<pre>';
    // print_r($mailsmsdata);
    // echo '</pre>';
    
    
     echo '<h2>';
     echo __('Student Message History');
     echo '</h2>';
     
     $types = array('' => 'Select Type', '1' => 'SMS', '2' => 'EMAIL');
     $form = Form::create('filter', '');

            $form->setClass('noIntBorder fullWidth');
            $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view.php');
            $row = $form->addRow();

            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('pupilsightProgramID', __('Program'));
            // $col->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDbyPP')->fromArray($program)->selected($pupilsightProgramID)->placeholder('Select Program');


            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('pupilsightYearGroupID', __('Class'));
            // $col->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDbyPP')->fromArray($classes)->selected($pupilsightYearGroupID)->placeholder('Select Class');


            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('pupilsightRollGroupID', __('Section'));
            // $col->addSelect('pupilsightRollGroupID')->setId('pupilsightRollGroupIDbyPP')->fromArray($sections)->selected($pupilsightRollGroupID)->placeholder('Select Section');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('start_date', __('From Date'))->addClass('dte');
            $col->addDate('start_date')->setValue($stDate);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('end_date', __('To Date'))->addClass('dte');
            $col->addDate('end_date')->setValue($enDate);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('type', __('Type'))->addClass('dte');
            $col->addSelect('type')->fromArray($types)->selected($type);


            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __(''));
            $col->addSearchSubmit($pupilsight->session, __('Clear Search'));



            echo $form->getOutput();

    ?>
    
    <table class="table" id="historyTable">
        <thead>
            <tr>
                <th>Sl No</th>
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
                $i = 1;
                foreach($mailsmsdata as $estd){ 
                    $stype = '';
                    $sto = '';
                    if($estd['type'] == '2'){
                        $stype = 'Email';
                        $sto = $estd['email'];
                    } else {
                        $stype = 'Sms';
                        $sto = $estd['phone'];
                    }
                ?>
                <tr>
                    <th><?php echo $i; ?></th>
                    <th><?php echo $estd['officialName']; ?></th>
                    <th><?php echo $stype; ?></th>
                    <th><?php echo $sto; ?></th>
                    <th><?php echo $estd['subject']; ?></th>
                    <th><?php echo $estd['description']; ?></th>
                    <th><?php echo $estd['uname']; ?></th>
                    <th><?php echo date('d/m/Y h:i:s', strtotime($estd['cdt'])); ?></th>
                </tr>
            <?php  $i++; } } else { ?> 
                <tr>
                    <th colspan="7">No Message History</th>
                </tr>
            <?php } ?>
        </tbody>

    </table>

<?php   
}
?>

<script>
    $(function(){
        $("#historyTable").dataTable();
    })
    $("#start_date").datepicker({
        //minDate: 0,
        onClose: function (selectedDate) {
            $("#end_date").datepicker("option", "minDate", selectedDate);
        }
    });
</script>