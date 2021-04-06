
<?php
include("template/header.php");

include_once '../w2f/adminLib.php';
$adminlib = new adminlib();


function getDomain()
{
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}
//$baseurl = getDomain().'/pupilsight_new';
$baseurl = getDomain();
$id = $_GET['id'];
$data=$adminlib->getPupilSightEctionDataById($id);
if($_POST){
	$input = $_POST;
	if($input['type'] == '8') { $input['title'] = 'comments'; } else {$input['title'] = $input['title'];}
	if(!empty($input['type']) && !empty($input['title']) && !empty($input['short_description'])){
		// if($_FILES["image"]["tmp_name"]){
		// 	$fileName = time().'_'.basename($_FILES['image']['name']);
		// 	$sourcePath = $_FILES['image']['tmp_name'];       // Storing source path of the file in a variable
		// 	$targetPath = "../images/upload/".$fileName; // Target path where file is to be stored
		// 	//die(0);
		// 	if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
		// 		//echo "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
		// 	} else {
		// 		//echo "Sorry, there was an error uploading your file.";
		// 	}
			
		// 	// img upload ends
		// 	$input['image']=$fileName;
		// 	$input['image_path']=$targetPath;
		// }
		// if($_FILES["person_image"]["tmp_name"]){
		// 	$fileName = time().'_'.basename($_FILES['person_image']['name']);
		// 	$sourcePath = $_FILES['person_image']['tmp_name'];       // Storing source path of the file in a variable
		// 	$targetPath = "../images/upload/".$fileName; // Target path where file is to be stored
		// 	//die(0);
		// 	if (move_uploaded_file($_FILES["person_image"]["tmp_name"], $targetPath)) {
		// 		//echo "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
		// 	} else {
		// 		//echo "Sorry, there was an error uploading your file.";
		// 	}
			
		// 	// img upload ends
		// 	$input['person_image']=$fileName;
		// 	$input['person_image_path']=$targetPath;
		// }

		/*******Convert base64 to Jpg *********/
        define('UPLOAD_DIR', '../images/upload/');

        //echo '<pre>';print_r($_POST['porpin'][0]);exit;
        $img_str=$_POST['image'][0];

		//echo $img_str;exit;
		if($img_str!='')
		{
			$image_parts = explode(";base64,", $img_str);
			//print_r($image_parts);exit;
			$image_type_aux = explode("image/", $image_parts[0]);
			$image_type = $image_type_aux[1];
			$image_base64 = base64_decode($image_parts[1]);
			$fname=uniqid() . '.jpg';
			$file = UPLOAD_DIR . $fname;
			file_put_contents($file, $image_base64);
			
			$input['image']=$fname;
			$input['image_path']=$file;

		}

		else
		{
			unset($input['image']);
		}
		/*******Convert base64 to Jpg *********/

		
		// echo '<pre>';
		// print_r($input);
		// echo '</pre>';
		// die(0);

		$adminlib->updateSectionData($input,$id);
		echo "<script>window.location='category.php'</script>";
		echo "<script>alert('Category Updated Successfully');</script>";
	}
	 
	else {
		echo "<script>alert('Please Fill Required Fields.');</script>";
	} 
}
?>
<section id="middle">
<script src="<?= $baseurl; ?>/assets/libs/jquery/jquery.js"></script>
    <style>
        .hide {
            display: none;
            visibility: hidden;
        }

        .cropImgPanel {
            background-color: #f3f3f3;
            cursor: pointer;
            height: 50px;
            margin: 10px;
            width: 200px;
            line-height: 50px;
            text-align: center;
        }
    </style>
   <div id="content" class="padding-20">
      <div id="panel-1" class="panel panel-default">
         <div class="panel-heading">
            <h3>Edit Category </h3>
           
         </div>
         <!-- panel content -->
         <div class="panel-body">
				<form onsubmit="return validateCategory()" role="form" method="post" action="" enctype="multipart/form-data">
                <div class="card-body">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Category Type <span style="color:red;">*</span></label>
                    <select name="type" class="form-control" id="showType">
						<option value="">Select Category</option>
						<option value="2" <?php if($data['type'] == '2') { ?> selected <?php } ?>>Course</option>
						<option value="3" <?php if($data['type'] == '3') { ?> selected <?php } ?>>Announcement</option>
						<option value="4" <?php if($data['type'] == '4') { ?> selected <?php } ?>>Experience</option>
						<option value="6" <?php if($data['type'] == '6') { ?> selected <?php } ?>>About Us</option>
						<option value="7" <?php if($data['type'] == '7') { ?> selected <?php } ?>>Events</option>
						<option value="8" <?php if($data['type'] == '8') { ?> selected <?php } ?>>Comments</option>
					</select>
                  </div>
				  <div class="form-group">
					<?php for($i=2;$i<=8;$i++){ ?>
						<img src="../images/type/<?php echo $i.'.png';?>" style="width:50%; <?php if($data['type'] == $i) { ?> display:block; <?php } else  { ?>display:none; <?php } ?>" class="alltypes" id="show-<?php echo $i;?>">
					<?php } ?>
                  </div>
				   <div class="form-group">
                    <label for="exampleInputPassword1">Title <span style="color:red;">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" id="exampleInputPassword1" placeholder="Title" value="<?php echo $data['title'];?>">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">Short Description <span style="color:red;">*</span></label>
                    <textarea  name="short_description" id="short_description" class="form-control"  placeholder="Short Description"><?php echo $data['short_description'];?></textarea>
                  </div>
				  <div class="form-group desc" id="desc6" style="<?php if($data['type'] == '6') { ?> display:block; <?php } else  { ?>display:none; <?php } ?>">
                    <label for="exampleInputPassword1">Description <span style="color:red;">*</span></label>
                    <textarea  name="description" id="description" class="form-control"  placeholder="Description"><?php echo $data['description'];?></textarea>
                  </div>
				    <div class="form-group imgc" style="<?php if($data['type'] == '8') { ?> display:none; <?php } else  { ?>display:block; <?php } ?>">
                    <label for="exampleInputFile">Image <span style="color:red;">*</span>
					<!-- <span class="wchng" id="wchng2" style="<?php if($data['type'] == '2') { ?> display:block; <?php } else  { ?>display:none; <?php } ?>">(Width:300 / Height:205)</span>
					<span class="wchng" id="wchng3" style="<?php if($data['type'] == '3') { ?> display:block; <?php } else  { ?>display:none; <?php } ?>">(Width:450 / Height:300)</span>
					<span class="wchng" id="wchng4" style="<?php if($data['type'] == '4') { ?> display:block; <?php } else  { ?>display:none; <?php } ?>">(Width:426 / Height:426)</span>
					<span class="wchng" id="wchng6" style="<?php if($data['type'] == '6') { ?> display:block; <?php } else  { ?>display:none; <?php } ?>">(Width:623 / Height:610)</span>
					<span class="wchng" id="wchng7" style="<?php if($data['type'] == '7') { ?> display:block; <?php } else  { ?>display:none; <?php } ?>">(Width:600 / Height:400)</span>
					 -->
					</label>
                    <div class="input-group">
                      <!-- <div class="custom-file">
                        <input type="file" id="image" class="custom-file-input"  name="image">
						</div>
                       -->

					   <?php
					$i   = 0;
					$len = 1;
					while ($i < $len) {
					?>

						<div id='prophoto_pr<?= $i ?>' class="cropImgPanel" onclick="editPhoto_pr<?= $i ?>();">Upload Image </div>
						<input type="hidden" id="porpin_pr<?= $i ?>" name="image[]" value="">
						
					<?php

						// $vwidth = 200;
						// $vheight = 258;
						// $bwidth = 250;
						// $bheight = 308;
						// $owidth = 775;
						// $oheight = 1000;

						
						$vwidth = 600;
						$vheight = 300;
						$bwidth = 650;
						$bheight = 350;
						$owidth = 600;
						$oheight = 300;
						$suffix = '_pr' . $i;
						//isfilebtn= FALSE;
						if ($i == 0) {
							$isimportreq = true;
						} else {
							$isimportreq = false;
						}

						//echo view('partials/croppie', $dt);
						include $_SERVER['DOCUMENT_ROOT'] . '/debug/crop/croplib.php';
						//include $_SERVER['DOCUMENT_ROOT'] . '/pupilsight_new/debug/crop/croplib.php';
						$i++;
					}

					

					?>
                    </div>
                  </div>
				  <input type="hidden" id="hidden_image" value="<?php if(!empty($data['image'])) { echo $data['image_path']; }?>" />
				   <?php if(!empty($data['image'])) { ?>
				   <div class="form-group">
                    <img src="<?php echo $data['image_path'];?>" style="width:15%">
					
					
					<a id="deleteImg" data-id="<?php echo $data['id'];?>" data-col="image" style="font-size: 23px;color: red;margin: 0px 0px 0 0px;"><i class="fa fa-trash" aria-hidden="true" ></i></a>
                  </div>
				   <?php } ?>
				 
				   <div class="form-group usr8 cmnts" style="<?php if($data['type'] == '8') { ?> display:block; <?php } else  { ?>display:none; <?php } ?>">
                    <label for="exampleInputPassword1">User Name</label>
                    <input type="text" name="person_name" class="form-control" id="" placeholder="User Name" value="<?php echo $data['person_name'];?>">
                  </div>
				  <div class="form-group usr8 cmnts" style="<?php if($data['type'] == '8') { ?> display:block; <?php } else  { ?>display:none; <?php } ?>">
                    <label for="exampleInputPassword1">User Designation</label>
                    <input type="text" name="person_designation" class="form-control" id="" placeholder="User Designation" value="<?php echo $data['person_designation'];?>">
                  </div>
				  
				   <div class="form-group usr8 cmnts" style="<?php if($data['type'] == '8') { ?> display:block; <?php } else  { ?>display:none; <?php } ?>">
                    <label for="exampleInputFile">User Image  (Width:150 / Height:150)</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input"  name="person_image">
						</div>
                      
                    </div>
                  </div>
				  
				  <?php if(!empty($data['person_image'])) { ?>
				   <div class="form-group">
                    <img src="<?php echo $data['person_image_path'];?>" style="width:15%">

					<a id="deleteImg" data-id="<?php echo $data['id'];?>" data-col="person_image" style="font-size: 23px;color: red;margin: 0px 0px 0 0px;"><i class="fa fa-trash" aria-hidden="true"></i></a>
                  </div>
				  <?php } ?>
				  
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

<?php //include("template/footer.php"); //commented out to avoid jquery lib clashes?> 
</body>
</html>
<script>
$(document).on('change', '#showType', function(e){	
	var id = $(this).val();
	$(".alltypes").hide();
	$(".wchng").hide();
	$(".cmnts").hide();
	$(".desc").hide();
	
	$("#show-"+id).show();
	$("#wchng"+id).show();
	$(".usr"+id).show();
	$("#desc"+id).show();
	if(id == '8'){
		$(".ttle").hide();
		$(".imgc").hide();
	} else {
		$(".ttle").show();
		$(".imgc").show();
	}
});

$(document).on('click', '#deleteImg', function(e){	
	var id = $(this).attr('data-id');
	var col = $(this).attr('data-col');
	$.ajax({
		  url: "deleteimage.php",
		  type: 'POST',
		  data: {id:id,col:col},
		  success: function(data){
			$('#hidden_image').val();  
			window.location='editcategory.php?id='+id;
			alert('Image Deleted Successfully.')
		  }
		});
});

function validateCategory()
{
	var showType = document.getElementById("showType").value;
	var title = document.getElementById("title").value;
	var short_description = document.getElementById("short_description").value;
	var image = $("input[name='image[]']")
              .map(function(){return $(this).val();}).get();

	
	var hidden_image = document.getElementById("hidden_image").value;



	if(showType=='' || title=='' || short_description=='' || (image=='' && hidden_image==''))
	{
		alert('Please enter required fields');
		return false;
	}
	else
	return true;
}
</script>