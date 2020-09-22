<?php 

   include_once '../w2f/adminLib.php';
   $adminlib = new adminlib();
   $id = $_POST['id'];
   $section = $adminlib->getPupilSightSectionDataByCategory($id);
?>

            <table class="table table-striped table-hover table-bordered" id="sample_editable_1">
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
        
<script>
$(document).on('change', '#seacrchByCategory', function(e){	
	var id = $(this).val();
	$.ajax({
	  url: "searchcategory.php",
	  type: 'POST',
	  data: {id:id},
	  success: function(data){
		
	  }
	});
});
</script>