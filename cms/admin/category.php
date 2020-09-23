<?php 
include("template/header.php");
   include_once '../w2f/adminLib.php';
   $adminlib = new adminlib();
   $section = $adminlib->getPupilSightSectionData();
?>
<section id="middle">
   <div id="content" class="padding-20">
      <div id="panel-1" class="panel panel-default">
         <div class="panel-heading">
            <h3> List 
			<a href="addcategory.php" class="btn btn-sm btn-success" style="float:right;">Add</a>
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

		 <select name="type" class="form-control" id="seacrchByCategory" style="Width:20%;margin: 13px;">
				<option value="">Select Category</option>
				<option value="2">Course</option>
				<option value="3">Announcement</option>
				<option value="4">Experience</option>
				<option value="6">About Us</option>
				<option value="7">Events</option>
				<option value="8">Comments</option>
			</select>
			
			<div id="categoryList">
            <table class="table table-striped table-hover table-bordered" id="">
               <thead>
                  <tr>
                     <th>Category</th>
                     <th>Title</th>
                     <th>Description</th>
                     <th>Image</th>
					 <th>Sorting Order</th>
                     <th>Status</th>
                     <th>Action</th>
                  </tr>
               </thead>
               <tbody>
                  <?php
				    foreach($section as $val){
                                                 if($val['status']==1){
                                                 $st="<span style='color:blue;cursor:pointer;' data-type='' class='' data-id='".$val['id']."' data-text='Inactive'>Active</span>";
                                                 } else {
                                                 $st="<span style='color:blue;cursor:pointer;' data-type=''  class='' data-id='".$val['id']."' data-text='Active'>Inactive</span>";
                                                 }
                     ?>
                  <tr>
                     <td>
						<?php 
							if($val['type'] == '2'){ echo 'Course';}
							if($val['type'] == '3'){ echo 'Announcement';}
							if($val['type'] == '4'){ echo 'Experience';}
							if($val['type'] == '6'){ echo 'About Us';}
							if($val['type'] == '7'){ echo 'Events';}
							if($val['type'] == '8'){ echo 'Comments';}
						?>
					 
					 
					 </td>
                     <td><?php echo$val['title'];?></td>
                     <td><?php echo$val['short_description'];?></td>
                    <td>
					<?php if(!empty($val['image'])) { ?> <img src="<?php echo $val['image_path']; ?>" style="width:15%"> <?php } ?>
					</td>
					<td><?php echo$val['sorting_order'];?></td>
                     <td><?php echo $st; ?></td>
					 <td > 
						<a class="btn btn-info btn-xs" href="editcategory.php?id=<?php echo $val['id']; ?>"><i class="fa fa-pencil-square-o"></i></a>
                        <a class="btn btn-danger btn-xs" href="deletecategory.php?id=<?php echo $val['id']; ?>" ><i class="fa fa-trash-o"></i></a> 
                     </td>
                  </tr>
                  <?php  }
                     ?>
               </tbody>
            </table>
		</div>	
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

<script>
$(document).on('change', '#seacrchByCategory', function(e){	
	var id = $(this).val();
	$.ajax({
	  url: "searchcategory.php",
	  type: 'POST',
	  data: {id:id},
	  success: function(data){
		$("#categoryList").html(data);
	  }
	});
});
</script>