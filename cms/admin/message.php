<?php 
include("template/header.php");
   include_once '../w2f/adminLib.php';
   $adminlib = new adminlib();
   $section = $adminlib->getUserMessageData();

   //echo '<pre>';print_r($section);exit;

?>
<style>
table {
  table-layout: fixed; 
  width: 100%
}

td {
  word-wrap: break-word;
}
</style>
<section id="middle">
   <div id="content" class="padding-20">
      <div id="panel-1" class="panel panel-default">
         <div class="panel-heading">
            <h3> List 
			<!--<a href="addcategory.php" class="btn btn-sm btn-success" style="float:right;">Add</a>-->
			</h3>
            <!-- right options -->
            <ul class="options pull-right list-inline">
               <li><a href="#" class="opt panel_colapse" data-toggle="tooltip" title="Colapse" data-placement="bottom"></a></li>
               <li><a href="#" class="opt panel_fullscreen hidden-xs" data-toggle="tooltip" title="Fullscreen" data-placement="bottom"><i class="fa fa-expand"></i></a></li>
               <li><a href="#" class="opt panel_close" data-confirm-title="Confirm" data-confirm-message="Are you sure you want to remove this panel?" data-toggle="tooltip" title="Close" data-placement="bottom"><i class="fa fa-times"></i></a></li>
            </ul>
            <!-- /right options -->
         </div>
         <!-- panel content -->
         <div class="panel-body">
            <table class="table table-striped table-hover table-bordered" id="sample_editable_1">
               <thead>
                  <tr>
                     <th style="width:5%">SL No</th>
                     <th style="width:10%">Date</th>
                     <th style="width:10%">Name</th>
                     <th style="width:15%">Email</th>
                     <th style="width:10%">Subject</th>
                     <th style="width:40%">Message</th>
					      <th style="width:10%">Action</th>
                  </tr>
               </thead>
               <tbody>
                  <?php
                  $i=1; 
				    foreach($section as $val){
                 
                 $date_arr=$val['created_at'];
                 if($date_arr)
                 $res=explode(" ",$date_arr);

                 if($res)
                 $date_exp=explode("-",$res[0]);


                 if($date_exp)
                 $ddmmyy=$date_exp[2]."-".$date_exp[1]."-".$date_exp[0];

      ?>
                  <tr>
                     <td><?php echo $i++;?></td>
                     <td><?php echo $ddmmyy;?></td>
                     <td><?php echo $val['name'];?></td>
                     <td><?php echo $val['email'];?></td>
                     <td><?php echo $val['subject'];?></td>
					      <td><?php echo $val['message'];?></td>
                    

                
					<td > 
						
                        <a class="btn btn-danger btn-xs" href="deletemsg.php?id=<?php echo $val['id']; ?>" ><i class="fa fa-trash-o"></i></a> 
                     </td>
                  </tr>
                  <?php  }
                     ?>
               </tbody>
            </table>
         </div>
         <!-- /panel content -->
         <!-- panel footer -->
         <div class="panel-footer">
         </div>
         <!-- /panel footer -->
      </div>
      <!-- /PANEL -->
      <!-- Small Modal >-->
   </div>
</section>
<!-- /MIDDLE -->
<?php include("template/footer.php");?> 