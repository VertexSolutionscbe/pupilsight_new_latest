<?php
echo "<style>
.card_custom {
    margin-top: 10px;
    margin-button: 10px;
    border-radius: 10px;
    text-align: center;
    padding-top: 10px;
    box-shadow: 1px 2px 4px rgba(0, 0, 0, .5);
}
.campaign_name{
    font-weight: bold;
    font-size: 18px;
    
}
.apply_button{
 background: #FFB533;
color: white;
width: 51px;
height: 20px;
border-radius: 4px;
text-align: center;}

</style>";

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
use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Admission\AdmissionGateway;


// Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Campaign/application_status.php') != false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    
    $search = isset($_GET['search'])? $_GET['search'] : '';    
    $admissionGateway = $container->get(AdmissionGateway::class);
     $criteria = $admissionGateway->newQueryCriteria()
        ->searchBy($admissionGateway->getSearchableColumns(), $search)
        ->sortBy(['id'])
        ->fromPOST();
        $uid = ltrim($_SESSION[$guid]['pupilsightPersonID'], '0');
      
    //  $sqlq = 'select c.id,c.name,c.end_date from campaign as c left join campaign_parent_registration as cp on c.id=cp.campaign_id where c.id not in(select campaign_id from campaign_parent_registration where pupilsightPersonID='.$uid.')  and CURDATE() between start_date and end_date   AND c.status = "2" GROUP BY c.id ';

      $sqlq = 'select c.id,c.name,c.end_date,c.allow_multiple_submission, c.form_id from campaign as c left join campaign_parent_registration as cp on c.id=cp.campaign_id where CURDATE() between start_date and end_date   AND c.status = "2" AND is_publish_parent = "1" GROUP BY c.id ';

     //   $sqlq = 'select c.id,c.name from campaign as c left join campaign_parent_registration as cp on c.id=cp.campaign_id where c.id not in(select campaign_id from campaign_parent_registration where pupilsightPersonID='.$uid.') and c.page_for="2"  AND c.status = "2" GROUP BY c.id ';
        //$sqlq = 'SELECT a.id, a.name FROM campaign AS a JOIN campaign_parent_registration AS b ON a.id = b.campaign_id where a.page_for = "2" AND a.status = "2" AND b.pupilsightPersonID != '.$uid.' ';
         $resultval = $connection2->query($sqlq);
             $rowdata = $resultval->fetchAll();
             $arr=array();
             foreach ($rowdata as $dt) {
                
                 $arr[] = $dt;
             }
            
     // QUERY
     echo '<h2>';
     echo __('Campaign List');
     echo '</h2>';
    //  print_r($criteria);
    //  die();
    //$dataSet = $admissionGateway->getAllCampaign($criteria);
    // echo "<div class='row' >";  
  
    //  print_r(json_encode($obj->name);
    
    //$result = json_decode($dataSet);
  
    $len = count($arr);

    $i = 0;
    $ic = 1;
    $isactive = FALSE;
    if($len){
        while ($i < $len) {
                if (($i % 4) == 0) {
                    if ($i > 0) {
                        echo "</div>";
                        $isactive = FALSE;
                    }
                    echo "<div class='row '>";
                    $isactive = TRUE;
                }

            $sqlchk = 'SELECT a.id, b.id as campid FROM wp_fluentform_submissions AS a LEFT JOIN campaign AS b ON a.form_id = b.form_id where a.pupilsightPersonID = '.$uid.' AND a.form_id = '.$arr[$i]['form_id'].' AND b.id = '.$arr[$i]['id'].' GROUP BY a.form_id ';
            $resultchk = $connection2->query($sqlchk);
            $rowdatachk = $resultchk->fetch();
            //print_r($rowdatachk);
            echo "<div class='col-sm-3 col-lg-3' >";
            echo "<div class='card card_custom' >";
            echo '<span class="campaign_name">';
            
            echo $arr[$i]['name'];
            echo '</span>';
            if($arr[$i]['allow_multiple_submission'] == '1'){
                echo "<center><a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/popup.php&id='.$arr[$i]['id'].''."&width=500&height=250'> ";   
                echo '<button class="btn btn-primary my-2">Apply Here</button></a>';
            } else {
                if(!empty($rowdatachk)){
                    echo '<center><button class="btn btn-primary my-2">Already Applied</button>';
                } else {
                    echo "<center><a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/popup.php&id='.$arr[$i]['id'].''."&width=500&height=250'> ";   
                    echo '<button class="btn btn-primary my-2">Apply Here</button></a>';
                }
            }
            // echo "<center><a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/popup.php&id='.$arr[$i]['id'].''."&width=500&height=250'> ";   
            // echo '<button class="btn btn-primary my-2">';
            // echo 'Apply Here</button></a><p style="text-align:center pb-2">Application ends on ';

            echo '<p style="text-align:center pb-2">Application ends on ';
            $dt = new DateTime($arr[$i]['end_date']);
            echo $dt->format('d-m-Y');
           // echo $arr[$i]['end_date'];
            echo'</p></center>';

            echo "</div>";
            echo "</div>";
            $i++;
            $ic++;

        }
    } else {
        echo '<p>No Active Campaign Available Here</p>';
    }
if ($isactive) {
    // echo "</div>";
    $isactive = FALSE;
}

 
 
 

}