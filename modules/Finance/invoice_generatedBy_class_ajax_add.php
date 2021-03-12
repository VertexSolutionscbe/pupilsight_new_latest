<?php
    $progId = $_POST['id'];
    $pupilsightSchoolYearID = $_POST['aid'];

    $sqlc = 'SELECT a.pupilsightYearGroupID, a.name, b.id, GROUP_CONCAT(b.fn_fee_structure_id) AS fsid FROM pupilsightProgramClassSectionMapping AS p LEFT JOIN  pupilsightYearGroup AS a ON p.pupilsightYearGroupID = a.pupilsightYearGroupID LEFT JOIN fn_fees_class_assign AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE p.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND p.pupilsightProgramID = '.$progId.' GROUP BY a.pupilsightYearGroupID ORDER BY a.pupilsightYearGroupID ASC ';
    $resultc = $connection2->query($sqlc);
    $rowdatacls = $resultc->fetchAll();

    // echo '<pre>';
    // print_r($rowdatacls);
    // echo '</pre>';
    $classes=array();  
    foreach ($rowdatacls as $dt) {
      
        $classes[$dt['pupilsightYearGroupID']] = $dt['name'];
    }
     
 
 ?>
 
      
                                
                                                                            
        <td class="">
			<div class="">
                <div class=" mb-4" style="width:100%">
                <label for="name" style="float: left;padding:5px" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">Select Class </label></div>
                </br>
                
                <?php foreach($rowdatacls as $k => $cl){ 
                     
                ?>
               
                    <div class="">
                        <label class="" style="width: 60px;" for="pupilsightYearGroupID[class][<?php echo $cl['name'];?>]"> <?php echo $cl['name'];?></label> 
                        <input type="checkbox" name="class[]" id="pupilsightYearGroupID[class][<?php echo $cl['pupilsightYearGroupID'];?>]" value="<?php echo $cl['pupilsightYearGroupID'];?>" class="">
                    </div>
                <?php }  ?>
                
			</div>	
        </td>
        <!-- <td class="flex flex-col flex-grow justify-center -mb-1 sm:mb-0  px-2 border-b-0 sm:border-b border-t-0 ">
                <div class="input-group stylish-input-group">
                    <label for="pupilsightYearGroupID" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"> </label>
                </div>	
        </td>                    -->
                                                                            
        
  