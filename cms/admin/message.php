<?php 
include("template/header.php");
   include_once '../w2f/adminLib.php';
   $adminlib = new adminlib();
   $section = $adminlib->getUserMessageData();
?>
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
                     <th>Name</th>
                     <th>Email</th>
                     <th>Subject</th>
                     <th>Message</th>
					 <th>Action</th>
                  </tr>
               </thead>
               <tbody>
                  <?php
				    foreach($section as $val){
                                                 
                     ?>
                  <tr>
                     <td><?php echo$val['name'];?></td>
                     <td><?php echo$val['email'];?></td>
                     <td><?php echo$val['subject'];?></td>
					 <td><?php echo$val['message'];?></td>
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