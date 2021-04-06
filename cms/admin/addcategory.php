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


if($_POST){
	// echo '<pre>';
	// print_r($_POST);
	// echo '</pre>';
	// die(0);
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
		

		//echo 's';exit;

		/*******Convert base64 to Jpg *********/
        define('UPLOAD_DIR', '../images/upload/');

        //echo '<pre>';print_r($_POST['porpin'][0]);exit;
        $img_str=$_POST['image'][0];
        $image_parts = explode(";base64,", $img_str);
        //print_r($image_parts);exit;
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
		$fname=uniqid() . '.jpg';
        $file = UPLOAD_DIR . $fname;
        file_put_contents($file, $image_base64);
		/*******Convert base64 to Jpg *********/

		$input['image']=$fname;
		$input['image_path']=$file;

		// echo '<pre>';
		// print_r($input['porpin']);
		// echo '</pre>';
		// die(0);

		
		$adminlib->createSectionData($input);
		echo "<script>alert('Category Added Successfully');</script>";
		echo "<script>window.location='category.php'</script>";
	} else {
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
            <h3>Add Category </h3>
           
         </div>
         <!-- panel content -->
         <div class="panel-body">
				<form onsubmit="return validateCategory()" role="form" method="post" action="" enctype="multipart/form-data">
				<div class="error-message"></div>
                <div class="card-body">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Category Type <span style="color:red;">*</span></label>
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
				   <div class="form-group ttle" id="ttle8">
                    <label for="exampleInputPassword1">Title <span style="color:red;">*</span></label>
                    <input type="text" name="title" class="form-control" id="title" placeholder="Title" value="">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">Short Description <span style="color:red;">*</span></label>
                    <textarea  name="short_description" class="form-control" id="short_description"  placeholder="Short Description"></textarea>
                  </div>
				  <div class="form-group desc" id="desc6" style="display:none;">
                    <label for="exampleInputPassword1">Description <span style="color:red;">*</span></label>
                    <textarea  name="description" id="description" class="form-control"  placeholder="Description"></textarea>
                  </div>
				    <div class="form-group imgc">
                    <label for="exampleInputFile">Image <span style="color:red;">*</span>
					<!-- <span class="wchng" id="wchng2" style="display:none;">(Width:300 / Height:205)</span>
					<span class="wchng" id="wchng3" style="display:none;">(Width:450 / Height:300)</span>
					<span class="wchng" id="wchng4" style="display:none;">(Width:426 / Height:426)</span>
					<span class="wchng" id="wchng6" style="display:none;">(Width:623 / Height:610)</span>
					<span class="wchng" id="wchng7" style="display:none;">(Width:600 / Height:400)</span>
					 -->
					
					</label>
                    <!-- <div class="input-group">
                      <div class="custom-file">
                        <input type="file" id="image" class="custom-file-input"  name="image">
						</div>
                     
                    </div> -->


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
				  
				   <div class="form-group usr8 cmnts" style="display:none;">
                    <label for="exampleInputPassword1">User Name </label>
                    <input type="text" name="person_name" id="person_name" class="form-control" id="" placeholder="User Name" value="">
                  </div>
				  <div class="form-group usr8 cmnts" style="display:none;">
                    <label for="exampleInputPassword1">User Designation </label>
                    <input type="text" name="person_designation" id="person_designation" class="form-control" id="" placeholder="User Designation" value="">
                  </div>
				  
				   <div class="form-group usr8 cmnts" style="display:none;">
                    <label for="exampleInputFile">User Image (Width:150 / Height:150)</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="person_image"  name="person_image">
						</div>
                      
                    </div>
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


	function validateCategory()
    {
        var showType = document.getElementById("showType").value;
        var title = document.getElementById("title").value;
		var short_description = document.getElementById("short_description").value;
		
		var image = $("input[name='image[]']")
              .map(function(){return $(this).val();}).get();

		

        if(showType=='' || title=='' || short_description=='' || image=='')
        {
            alert('Please enter required fields');
            return false;
        }
        else
        return true;
    }

</script>