<?php
echo "<style>

.popup_text{
    
    font-weight: bold;
    font-size: 18px;
}
.margin_top70{
    
    margin-top:70px !important;}

.popupbtns{
    background: #FFB533  !important;
    color: white  !important;
    width: 80px  !important;
    border-radius: 4px  !important;
    text-align: center  !important;
    margin-top:10px  !important;
    height:50px  !important;
}
    .accept{
        background:green;
    }
    .cancel{
        background:red;
    }

#TB_window {
    width :800px !important;
    margin-left: -360px !important;
}    

#TB_ajaxContent {
    width :750px !important;
    height: auto !important;
}

.modal-dialog{
    max-width: 700px;
}

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
    $id = isset($_GET['id'])? $_GET['id'] : '';
  
    
    $admissionGateway = $container->get(AdmissionGateway::class);
     $criteria = $admissionGateway->newQueryCriteria()
        ->searchBy($admissionGateway->getSearchableColumns(), $search)
        ->sortBy(['id'])
        ->fromPOST();
    
        // echo "<div class='row margin_top'>";   
        // echo "<div class='col-sm-12 col-lg-12 margin_top70' style='margin-bottom: 30px;'> ";
        // echo "<center><span class='popup_text'>";
        // echo "Terms And Conditions";
        // echo "</center></span>";
        // echo "</div></br>";
        // echo "<div class='col-sm-3 col-lg-3' > ";
        // echo "</div>";
        // echo "<div class='col-sm-3 col-lg-3' > ";
        // echo '<a class="btn btn-primary accept" href="?q=/modules/Campaign/formopen.php&id='.$id.' "> Accept';
        // echo '</a>';
        // echo "</div>";
        // echo "<div class='col-sm-3 col-lg-3' >";
        // echo '<button id="TB_closeWindowButton" class="btn-fill-md text-light bg-red cancel"> cancel';
        // echo '</button>';
        // echo "</div>";
        // echo "</div>";

        $div = '<div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
          <h4 style="float:left" class="modal-title">Terms &amp; Conditions</h4>
            <button type="button" class="close" data-dismiss="modal">×</button>
            
          </div>
          <div class="modal-body">
            <p class="statusMsg">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
          </div>
         <!-- Modal Footer -->
                <div class="modal-footer">
                    
                    <a  id="TB_closeWindowButton" class="btn btn_css btn-primary btn-default" href="">Reject</a>
                     <a class="btn btn_css btn-primary btn-default" href="?q=/modules/Campaign/formopen.php&id='.$id.' ">Accept</a>
                </div>
        </div> 
    
      </div>';
      echo $div;
}