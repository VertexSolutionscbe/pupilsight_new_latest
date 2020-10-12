<?php
/*
Pupilsight, Flexible & Open School System
*/


use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $campaignid = '';
    if(!empty($campaignid)){
        $sqlrec = 'SELECT b.id, b.formatval FROM campaign AS a LEFT JOIN fn_fee_series AS b ON a.application_series_id = b.id WHERE a.id = "'.$campaignid.'" ';
        $resultrec = $connection2->query($sqlrec);
        $recptser = $resultrec->fetch();

        $seriesId = $recptser['id'];

        if(!empty($seriesId)){
            $invformat = explode('$',$recptser['formatval']);
            $iformat = '';
            $orderwise = 0;
            foreach($invformat as $inv){
                if($inv == '{AB}'){
                    $datafort = array('fn_fee_series_id'=>$seriesId,'order_wise' => $orderwise, 'type' => 'numberwise');
                    $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                    $resultfort = $connection2->prepare($sqlfort);
                    $resultfort->execute($datafort);
                    $formatvalues = $resultfort->fetch();
                    
                    $iformat .= $formatvalues['last_no'];
                    
                    $str_length = $formatvalues['no_of_digit'];

                    $lastnoadd = $formatvalues['last_no'] + 1;

                    $lastno = substr("0000000{$lastnoadd}", -$str_length); 

                    $datafort1 = array('fn_fee_series_id'=>$seriesId,'order_wise' => $orderwise, 'type' => 'numberwise' , 'last_no' => $lastno);
                    $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                    $resultfort1 = $connection2->prepare($sqlfort1);
                    $resultfort1->execute($datafort1);

                } else {
                    $iformat .= $inv;
                }
                $orderwise++;
            }
            $application_id = $iformat;
        } else {
            $application_id = '';
        }
    }