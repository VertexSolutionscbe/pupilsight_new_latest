
<?php
include("template/header.php");

include_once '../w2f/adminLib.php';
$adminlib = new adminlib();


if($_POST){
	// echo '<pre>';
	// print_r($_POST);
	// echo '</pre>';
	// die(0);
	$input = $_POST;
	if(!empty($input['type'])){
		if($_FILES["image"]["tmp_name"]){
			$fileName = time().'_'.basename($_FILES['image']['name']);
			$sourcePath = $_FILES['image']['tmp_name'];       // Storing source path of the file in a variable
			$targetPath = "../images/upload/".$fileName; // Target path where file is to be stored
			//die(0);
			if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
				//echo "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
			} else {
				//echo "Sorry, there was an error uploading your file.";
			}
			
			// img upload ends
			$input['image']=$fileName;
			$input['image_path']=$targetPath;
		}
		if($_FILES["person_image"]["tmp_name"]){
			$fileName = time().'_'.basename($_FILES['person_image']['name']);
			$sourcePath = $_FILES['person_image']['tmp_name'];       // Storing source path of the file in a variable
			$targetPath = "../images/upload/".$fileName; // Target path where file is to be stored
			//die(0);
			if (move_uploaded_file($_FILES["person_image"]["tmp_name"], $targetPath)) {
				//echo "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
			} else {
				//echo "Sorry, there was an error uploading your file.";
			}
			
			// img upload ends
			$input['person_image']=$fileName;
			$input['person_image_path']=$targetPath;
		}
		
		$adminlib->createSectionData($input);
	}
	 echo "<script>window.location='category.php'</script>";
}

?>
<section id="middle">
   <div id="content" class="padding-20">
      <div id="panel-1" class="panel panel-default">
         <div class="panel-heading">
            <h3>Category </h3>
           
         </div>
         <!-- panel content -->
         <div class="panel-body">
				<form role="form" method="post" action="" enctype="multipart/form-data">
                <div class="card-body">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Category Type</label>
                    <select name="type" class="form-control" id="showType">
						<option value="">Select Category</option>
						<option value="2">Course</option>
						<option value="3">Announcement</option>
						<option value="4">Experience</option>
						<option value="6">About Us</option>
						<option value="7">Events</option>
						<option value="8">Comments</option>
					</select>
                  </div>
				  <div class="form-group">
					<?php for($i=2;$i<=8;$i++){ ?>
						<img src="../images/type/<?php echo $i.'.png';?>" style="width:50%; display:none;" class="alltypes" id="show-<?php echo $i;?>">
					<?php } ?>
                  </div>
				   <div class="form-group">
                    <label for="exampleInputPassword1">Title</label>
                    <input type="text" name="title" class="form-control" id="exampleInputPassword1" placeholder="Title" value="">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">Short Description</label>
                    <textarea  name="short_description" class="form-control"  placeholder="Short Description"></textarea>
                  </div>
				  <div class="form-group">
                    <label for="exampleInputPassword1">Description</label>
                    <textarea  name="description" class="form-control"  placeholder="Description"></textarea>
                  </div>
				    <div class="form-group">
                    <label for="exampleInputFile">Image</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input"  name="image">
						</div>
                      <!--<div class="input-group-append">
                        <span class="input-group-text" id="">Upload</span>
                      </div>-->
                    </div>
                  </div>
				   <div class="form-group">
                    <label for="exampleInputPassword1">Price</label>
                    <input type="text" name="price" class="form-control" id="" placeholder="Price" value="">
                  </div>
				   <div class="form-group">
                    <label for="exampleInputPassword1">User Name</label>
                    <input type="text" name="person_name" class="form-control" id="" placeholder="User Name" value="">
                  </div>
				  <div class="form-group">
                    <label for="exampleInputPassword1">User Designation</label>
                    <input type="text" name="person_designation" class="form-control" id="" placeholder="User Designation" value="">
                  </div>
				  <div class="form-group">
                    <label for="exampleInputPassword1">User Address</label>
                    <input type="text" name="person_address" class="form-control" id="" placeholder="User Address" value="">
                  </div>
				   <div class="form-group">
                    <label for="exampleInputFile">User Image</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input"  name="person_image">
						</div>
                      
                    </div>
                  </div>
				   <div class="form-group">
                    <label for="exampleInputPassword1">Date</label>
                    <input type="text" name="date" class="form-control" id="" placeholder="Date" value="">
                  </div>
				   <div class="form-group">
                    <label for="exampleInputPassword1">Time</label>
                    <input type="text" name="time" class="form-control" id="" placeholder="Time" value="">
                  </div>
				   <div class="form-group">
                    <label for="exampleInputPassword1">Sorting Order</label>
                    <input type="text" name="sorting_order" class="form-control" id="" placeholder="Order" value="">
                  </div>
				 
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form>		
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

<?php include("template/footer.php");?> 
<script>
$(document).on('change', '#showType', function(e){	
	var id = $(this).val();
	$(".alltypes").hide();
	$("#show-"+id).show();
});
</script>