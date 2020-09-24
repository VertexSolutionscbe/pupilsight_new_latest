<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Admission'), 'index.php')
        ->add(__('Edit Admission'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $id = $_GET['id'];
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM campaign WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $kount = 0;
            $totalseats = 0;
            $values = $result->fetch();
            $campaignid = $id;
            $data1 = array('campaignid' => $campaignid);
            $sql1 = 'SELECT * FROM seatmatrix WHERE campaignid=:campaignid';
            $result1 = $connection2->prepare($sql1);
            $result1->execute($data1);
            $seatvalues = $result1->fetchall();
            $kount = count($seatvalues);
            if(!empty($seatvalues)){
                foreach($seatvalues as $ts){
                    $totalseats += $ts['seats'];
                }
            }

            $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
            $resultp = $connection2->query($sqlp);
            $rowdataprog = $resultp->fetchAll();

            $program=array();  
            $program2=array();  
            $program1=array(''=>'Select Program');
            foreach ($rowdataprog as $key => $dt) {
                $program2[$dt['pupilsightProgramID']] = $dt['name'];
            }
            $program= $program1 + $program2;

            $sqlcs = 'SELECT id, series_name FROM fn_fee_series WHERE type IN ("Application","Admission")';
            $resultcs = $connection2->query($sqlcs);
            $seriesData = $resultcs->fetchAll();

            $campSeries=array();  
            $campSeries2=array();  
            $campSeries1=array(''=>'Select Series');
            foreach ($seriesData as $key => $cst) {
                $campSeries2[$cst['id']] = $cst['series_name'];
            }
            $campSeries = $campSeries1 + $campSeries2; 
            
            $pid = $values['pupilsightProgramID'];
            $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pid . '" GROUP BY a.pupilsightYearGroupID';
            $result = $connection2->query($sql);
            $classesdata = $result->fetchAll();
            $classes=array();  
            foreach ($classesdata as $ke => $cl) {
                $classes[$cl['pupilsightYearGroupID']] = $cl['name'];
            }

            $setclass = array();
            if(!empty($values['classes'])){
                $setclass = explode(',', $values['classes']);
            }
            
            //print_r($classes);
            $form = Form::create('Admission', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/editProcess.php?id='.$id)->addClass('newform');
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
            $resultval = $connection2->query($sqlq);
                $rowdata = $resultval->fetchAll();
                $academic=array();
                $ayear = '';
                if(!empty($rowdata)){
                    $ayear = $rowdata[0]['name'];
                    foreach ($rowdata as $dt) {
                        $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
                    }
                }
            $form->addHiddenValue('ayear', $values['academic_year']); 
            $statuses = array(
                '1'     => __('Draft'),
                '2'  => __('Publish'),
                '3' => __('Stop'),
            );
            $reg_status = array(
                '1'     => __('No'),  //public// page_for-1(db)
                '2'  => __('Yes'),   // private// page_for-2(db)
               
            );
           
            echo '<h2>';
            echo __('Edit Campaign');
            echo '</h2>';

            $row = $form->addRow();

                    
            $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('name', __('Name'));
                    $col->addTextField('name')->addClass('txtfield')->required()->setValue($values['name']);
                
            $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('academic_year', __('Academic Year'));
                    $col->addSelect('academic_id')->addClass('txtfield')->fromArray($academic)->required()->selected($values['academic_id']);

            $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('pupilsightProgramID', __('Program'));
                    $col->addSelect('pupilsightProgramID')->setId('getMultiClassByProg')->addClass('txtfield')->fromArray($program)->required()->selected($values['pupilsightProgramID']);;        
                    
            $col = $row->addColumn()->setClass('newdes showClass');
                    $col->addLabel('classes', __('Class'))->addClass('dte');
                    $col->addSelect('classes')->setId('showMultiClassByProg')->addClass('txtfield')->placeholder('Select Class')->selectMultiple()->fromArray($classes)->selected($setclass);         

            // $col = $row->addColumn()->setClass('newdes');
            //         $col->addLabel('seats', __('Seats'))->addClass('dte');
            //         $col->addTextField('seats')->addClass('txtfield')->readonly()->setValue($values['seats']);
            $form->addHiddenValue('seats', $values['seats']); 
                       


            $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('limit_max_users', __('Limit Max Users'))->addClass('dte');
                    $col->addNumber('limit_apply_form')->setId('numAllow')->addClass('txtfield')->setValue($values['limit_apply_form']); 

            $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('status', __('Status'));
                $col->addSelect('status')->addClass('txtfield')->fromArray($statuses)->required()->selected($values['status']);  
            
            
               
            $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('start_date', __('Start Date'))->addClass('dte');
                $col->addDate('start_date')->addClass('txtfield')->readonly()->required()->setValue(dateConvertBack($guid, $values['start_date']));  

            $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('end_date', __('End Date'))->addClass('dte');
                $col->addDate('end_date')->addClass('txtfield')->readonly()->required()->setValue(dateConvertBack($guid, $values['end_date']));

        $row = $form->addRow();
        
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('reg_req', __('Registration Required'));
            $col->addSelect('reg_req')->addClass('txtfield')->fromArray($reg_status)->required()->selected($values['page_for']);
                        
            $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('campaign_series_id', __('Application Series'));
                $col->addSelect('campaign_series_id')->addClass('txtfield')->fromArray($campSeries)->selected($values['campaign_series_id']);
            
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('', __(''));
            
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('', __(''));
                
            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('description', __('Description'));
                    $col->addTextArea('description')->addClass('txtfield')->setRows(4)->setValue($values['description']);   

            $row = $form->addRow()->setID('seatdiv');
                    $col = $row->addColumn()->setClass('newdes');
                    //$col->addButton(__('Add More Seat Matrix'))->addData('cid', $kount)->setID('addSeats')->addClass('bttnsubmt');
                    $col->addContent('<a class="btn btn-primary" id="addSeats" data-cid='.$kount.'>Add More Seat Matrix</a>');
        
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('Total Seats : ', __('Total Seats : '.$totalseats.''))->addClass('showSeats');
                   // $row->addButton('Add Seats')->addData('class', 'addSeats')->addClass('submt');    
                  
            if(!empty($seatvalues)){ 
                $i = '1';
                foreach($seatvalues as $k=>$sv){
                    $row = $form->addRow()->setClass('deltr'.$k);
                    
                        $col = $row->addColumn()->setClass('newdes');
                    if($i == '1'){
                        $col->addLabel('name', __('Name'));
                    }    
                        $col->addTextField('seatname['.$k.']')->addClass('txtfield')->setValue($sv['name']);  
                    
                    $col = $row->addColumn()->setClass('newdes');
                    if($i == '1'){
                        $col->addLabel('seat', __('Campaign Seat
                        '))->addClass('dte');
                    }    
                        $col->addNumber('seatallocation['.$k.']')->addClass('txtfield kountseat szewdt')->setValue($sv['seats']); 
                        $col->addContent('<div class="dte mb-1"  style="font-size: 25px; padding:  6px 0 0px 4px"><i style="cursor:pointer" class="far fa-times-circle delSeattr " data-id="'.$k.'" data-sid="'.$sv['id'].'"></i></div>'); 
                        
                        $i++;
                }         
            }   
            $row = $form->addRow()->setID('lastseatdiv');
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
?>

<style>
    
    .multiselect {
        width: 212px;
        height: 35px;
    }
    .multiselect-container{
        height: 300px;
        overflow: auto;
    }

    
</style>
<script>
    $(document).ready(function () {
      	$('#showMultiClassByProg').selectize({
      		maxItems: 15,
      		plugins: ['remove_button'],
      	});
    });
</script>
