 <?php
    $progId = $_POST['id'];

    $sqlc = 'SELECT a.pupilsightYearGroupID, a.name, b.id, GROUP_CONCAT(b.fn_fee_structure_id) AS fsid FROM pupilsightProgramClassSectionMapping AS p LEFT JOIN  pupilsightYearGroup AS a ON p.pupilsightYearGroupID = a.pupilsightYearGroupID LEFT JOIN fn_fees_class_assign AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE p.pupilsightProgramID = '.$progId.' GROUP BY a.pupilsightYearGroupID ORDER BY a.pupilsightYearGroupID ASC ';
    $resultc = $connection2->query($sqlc);
    $rowdatacls = $resultc->fetchAll();

    // echo '<pre>';
    // print_r($rowdatacls);
    // echo '</pre>';
    $classes=array();  
    foreach ($rowdatacls as $dt) {
        if(empty($dt['id'])){
            $content = '<span style="color:red;">(Not Assign)</span>';
        } else {
            $content = '';
        }
        $classes[$dt['pupilsightYearGroupID']] = $content.' '.$dt['name'];
    }
     
 
 ?>
 
        <td class="flex flex-col flex-grow justify-center -mb-1 sm:mb-0  px-2 border-b-0 sm:border-b border-t-0 ">
                <div class="input-group stylish-input-group">
                    <label for="pupilsightYearGroupID" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"> </label>
                </div>	
        </td>
                                
                                                                            
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes mbtm2">
			<div class="input-group stylish-input-group">
                <div class=" mb-1" style="width:100%">
                <label for="name" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">Class </label></div>
                
                <?php foreach($rowdatacls as $k => $cl){ 
                    if(empty($cl['id'])){    
                ?>
                    <div class="right mb-1">
                        <div class="inline flex-1 relative">
                        <label class="leading-normal" for="pupilsightYearGroupID[class][<?php echo $cl['pupilsightYearGroupID'];?>]"><span style="color:red;">(Not Assign)</span> <?php echo $cl['name'];?></label> 
                        <input type="checkbox" name="pupilsightYearGroupID[class][<?php echo $cl['pupilsightYearGroupID'];?>]" id="pupilsightYearGroupID[class][<?php echo $cl['pupilsightYearGroupID'];?>]" value="on" class="right" disabled="1"><br>
                        </div>
                    </div>

                <?php } else { ?>
                
                    <div class="right mb-1">
                        <div class="inline flex-1 relative">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <label class="leading-normal" for="pupilsightYearGroupID[class][<?php echo $cl['name'];?>]"> <?php echo $cl['name'];?></label> 
                            <input type="checkbox" name="pupilsightYearGroupID[class][<?php echo $cl['pupilsightYearGroupID'];?>]" id="pupilsightYearGroupID[class][<?php echo $cl['pupilsightYearGroupID'];?>]" value="on" class="right"><br>
                        </div>
                    </div>
                <?php } } ?>
                
			</div>	
        </td>
                                
                                                                            
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
			<div class="input-group stylish-input-group">
                <div class=" mb-1" style="width:100%"><label for="invoice_title" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"></label></div>
                <?php foreach($rowdatacls as $k => $cl){ 
                    if(!empty($cl['fsid'])){
                        $sqlchk = 'SELECT GROUP_CONCAT(DISTINCT b.fn_fee_structure_id) as fid FROM fn_fee_invoice_class_assign AS a LEFT JOIN fn_fee_invoice AS b ON a.fn_fee_invoice_id = b.id WHERE a.pupilsightYearGroupID = '.$cl['pupilsightYearGroupID'].' AND a.pupilsightProgramID = '.$progId.' ';
                        $resultchk = $connection2->query($sqlchk);
                        $chkstrid = $resultchk->fetch();
                        $fid = $chkstrid['fid'];
                        if(!empty($fid)){
                            $sqlf = 'SELECT GROUP_CONCAT(DISTINCT id) as fsid FROM fn_fee_structure WHERE id IN ('.$cl['fsid'].') AND id NOT IN ('.$fid.') ';
                        } else {
                            $sqlf = 'SELECT GROUP_CONCAT(DISTINCT id) as fsid FROM fn_fee_structure WHERE id IN ('.$cl['fsid'].') ';
                        }
                        //echo $sqlf;
                        $resultf = $connection2->query($sqlf);
                        $rowdatafees = $resultf->fetch();
                        if(!empty($rowdatafees)){
                            
                            $feesStructure = $rowdatafees['fsid'];    
                ?>
                <input type="hidden" id="pupilsightYearGroupID[structure][<?php echo $cl['pupilsightYearGroupID'];?>]" name="pupilsightYearGroupID[structure][<?php echo $cl['pupilsightYearGroupID'];?>]" value="<?php echo $feesStructure; ?>">
                <?php /* ?>
                    <div class="mbtm3 mb-1">
                        <div class="flex-1 relative">
                            <select id="pupilsightYearGroupID[structure][<?php echo $cl['pupilsightYearGroupID'];?>]" name="pupilsightYearGroupID[structure][<?php echo $cl['pupilsightYearGroupID'];?>]" class="mbtm3">
                            <option value="">Select Fee Structure</option>
                            <?php foreach($feesStructure as $fs){ ?>
                                <option value="<?php echo $fs['id'];?>"><?php echo $fs['name'];?></option>
                            <?php } ?>
                            </select>
                        </div>
                    </div>
                <?php */ ?>    
                <?php } else { ?>
                    <div class="hidelevel mbtm3 mb-1"><label for="pupilsightYearGroupID[structure][<?php echo $cl['pupilsightYearGroupID'];?>]" class="hidelevel mbtm3 inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"> </label></div>
                <?php } } else { ?>
                    <div class="hidelevel mbtm3 mb-1"><label for="pupilsightYearGroupID[structure][<?php echo $cl['pupilsightYearGroupID'];?>]" class="hidelevel mbtm3 inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"> </label></div>
                <?php } } ?>
             </div>	
        </td>
  