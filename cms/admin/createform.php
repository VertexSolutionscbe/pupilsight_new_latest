<?php
include("template/header.php");

include_once '../w2f/adminLib.php';
$adminlib = new adminlib();
$data = $adminlib->getPupilSightData();


//echo '<pre>'; print_r($data);exit;

$fileName = $targetPath = '';

$input['cms_banner_image'] = $input['cms_banner_image_path'] = '';

if ($_POST) {
  //echo '<pre>'; print_r($_POST);exit;
  // echo '</pre>';
  // die(0);	

  $input = $_POST;
  // if ($_FILES["cms_banner_image"]["tmp_name"])

  if ($_FILES["cms_banner_image"]["error"] != 4) {

    $fileName = time() . '_' . basename($_FILES['cms_banner_image']['name']);
    $sourcePath = $_FILES['cms_banner_image']['tmp_name'];       // Storing source path of the file in a variable
    $targetPath = "../images/banner/" . $fileName; // Target path where file is to be stored
    //die(0);
    if (move_uploaded_file($_FILES["cms_banner_image"]["tmp_name"], $targetPath)) {
      //echo "The file ". basename( $_FILES["cms_banner_image"]["name"]). " has been uploaded.";
    } else {
      //echo "Sorry, there was an error uploading your file.";
    }

    // img upload ends
    $input['cms_banner_image'] = $fileName;
    $input['cms_banner_image_path'] = $targetPath;
  } else if ($_POST['hidden_banner_image'] == '') {
    $input['cms_banner_image'] = $fileName;
    $input['cms_banner_image_path'] = $targetPath;
  }

  if ($_FILES["logo_image"]["error"] != 4) {

    //echo "<script>alert('aaa');</script>";

    $fileName = time() . '_' . basename($_FILES['logo_image']['name']);
    $sourcePath = $_FILES['logo_image']['tmp_name'];       // Storing source path of the file in a variable
    $targetPath = "../images/logo/" . $fileName; // Target path where file is to be stored
    //die(0);
    if (move_uploaded_file($_FILES["logo_image"]["tmp_name"], $targetPath)) {
      //echo "The file ". basename( $_FILES["logo_image"]["name"]). " has been uploaded.";
    } else {
      //echo "Sorry, there was an error uploading your file.";
    }

    // img upload ends
    $input['logo_image'] = $fileName;
    $input['logo_image_path'] = $targetPath;
  } else if ($_POST['hidden_logo_image'] == '') {


    $input['logo_image'] = $fileName;
    $input['logo_image_path'] = $targetPath;
  }

  if ($_FILES["comment_image"]["error"] != 4) {
    $fileName = time() . '_' . basename($_FILES['comment_image']['name']);
    $sourcePath = $_FILES['comment_image']['tmp_name'];       // Storing source path of the file in a variable
    $targetPath = "../images/upload/" . $fileName; // Target path where file is to be stored
    //die(0);
    if (move_uploaded_file($_FILES["comment_image"]["tmp_name"], $targetPath)) {
      //echo "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
    } else {
      //echo "Sorry, there was an error uploading your file.";
    }

    // img upload ends
    $input['comment_image'] = $fileName;
    $input['comment_image_path'] = $targetPath;
  } else if ($_POST['hidden_comment_image'] == '') {


    $input['comment_image'] = $fileName;
    $input['comment_image_path'] = $targetPath;
  }

  //echo '<pre>';print_r($input);exit;
  $adminlib->createPupilSightData($input);
  $data = $adminlib->getPupilSightData();
  echo "<script>alert('Updated Successfully');</script>";
}



?>
<section>


  <div id="content">
    <div id="panel-1" class="panel panel-default">
      <div class="panel-heading">
        <h3>Section </h3>

      </div>
      <!-- panel content -->
      <div class="panel-body">
        <form role="form" method="post" action="" enctype="multipart/form-data">
          <div class="card-body">
            <div class="form-group">
              <label for="exampleInputEmail1">Title</label>
              <input type="text" name="title" class="form-control" placeholder="Title" value="<?php echo $data['title']; ?>">
            </div>
            <div class="form-group">
              <!-- <label for="exampleInputEmail1">Banner Title (For break line <?= htmlentities('<br>'); ?>)</label> -->
              <label for="exampleInputEmail1">Banner Title</label>
              <input type="text" name="cms_banner_title" class="form-control" placeholder="Banner Title" value="<?php echo $data['cms_banner_title']; ?>">
            </div>
            <div class="form-group">
              <label for="exampleInputPassword1">Banner Description</label>
              <input type="text" name="cms_banner_short_description" class="form-control" id="exampleInputPassword1" placeholder="Banner Description" value="<?php echo $data['cms_banner_short_description']; ?>">
              <!-- <textarea name="cms_banner_short_description" class="form-control" placeholder="Banner Description"><?php echo $data['cms_banner_short_description']; ?></textarea> -->
            </div>
            <div class="form-group">
              <label for="exampleInputFile">Banner Image (Width:790 / Height:555)</label>
              <div class="input-group">
                <div class="custom-file">


                  <input type="file" id="cms_banner_image" onChange="displayImageBanner(this)" class="custom-file-input" name="cms_banner_image">

                  <div id="b1">
                    To Delete Uploaded Banner: <img src="../images/delete-icon.jpg" height="20" width="20" />
                  </div>
                  <br>
                  <!--<div class="input-group-append">
                        <span class="input-group-text" id="">Upload</span>
                      </div>-->

                </div>
                <div class="form-check">
                  <img id="cms_banner_image_path" src="<?php echo $data['cms_banner_image_path']; ?>" onClick="triggerClickBanner()" style="width:15%">

                  <input type="hidden" name="hidden_banner_image" id="hidden_banner_image" value="<?php echo $data['cms_banner_image_path']; ?>" />

                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Primary Email</label>
                  <input type="text" name="primary_email" class="form-control" placeholder="Primary Email" value="<?php echo $data['primary_email']; ?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Secondary Email</label>
                  <input type="text" name="secondary_email" class="form-control" placeholder="Secondary Email" value="<?php echo $data['secondary_email']; ?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Phone</label>
                  <input type="text" name="phone" class="form-control" placeholder="Phone" value="<?php echo $data['phone']; ?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Fax</label>
                  <input type="text" name="fax" class="form-control" placeholder="Fax" value="<?php echo $data['fax']; ?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Address</label>
                  <textarea name="address" class="form-control" placeholder="Address"><?php echo $data['address']; ?></textarea>
                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Total Students</label>
                  <input type="text" name="total_student" class="form-control" placeholder="Total Students" value="<?php echo $data['total_student']; ?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Total Courses</label>
                  <input type="text" name="total_course" class="form-control" placeholder="Total Courses" value="<?php echo $data['total_course']; ?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Total Hours Video</label>
                  <input type="text" name="total_hours_video" class="form-control" placeholder="Total Hours Video" value="<?php echo $data['total_hours_video']; ?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Logo Title</label>
                  <input type="text" name="logo_title" class="form-control" placeholder="Logo Title" value="<?php echo $data['logo_title']; ?>">
                </div>
                <div class="form-group">
                  <!-- <label for="exampleInputFile">Logo (Width:220 / Height:65)</label> -->

                  <label for="exampleInputFile">Logo (Width:160 / Height:50)</label>
                  <div class="input-group">

                    <input type="file" onChange="displayImageLogo(this)" class="custom-file-input" id="logo_image" name="logo_image">
                    <div class="custom-file" id="b2">
                      To Delete Uploaded Logo: <img src="../images/delete-icon.jpg" height="20" width="20" />
                    </div><br>
                    <!--<div class="input-group-append">
                        <span class="input-group-text" id="">Upload</span>
                      </div>-->
                  </div>
                </div>
                <div class="form-check">

                  <img src="<?php echo $data['logo_image_path']; ?>" id="logo_image_path" style="width:15%">
                  <input type="hidden" name="hidden_logo_image" id="hidden_logo_image" value="<?php echo $data['logo_image_path']; ?>" />
                </div>

                <!-- /.card-body -->
                <div class="form-group">
                  <label for="exampleInputEmail1">Facebook Link</label>
                  <input type="text" name="facebook_link" class="form-control" placeholder="Facebook Link" value="<?php echo $data['facebook_link']; ?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Twitter Link</label>
                  <input type="text" name="twitter_link" class="form-control" placeholder="Twitter Link" value="<?php echo $data['twitter_link']; ?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Pinterest Link</label>
                  <input type="text" name="pinterest_link" class="form-control" placeholder="Pinterest Link" value="<?php echo $data['pinterest_link']; ?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputEmail1">Linkdlin Link</label>
                  <input type="text" name="linkdlin_link" class="form-control" placeholder="Linkdlin Link" value="<?php echo $data['linkdlin_link']; ?>">
                </div>

                <div class="form-group">
                  <label for="exampleInputEmail1">Course</label>
                  <input type="checkbox" data-name="course_status" class="chkCategory" <?php if ($data['course_status'] == '1') { ?>checked <?php } ?>>
                  <label for="exampleInputEmail1">Announcement</label>
                  <input type="checkbox" data-name="announcement_status" class="chkCategory" <?php if ($data['announcement_status'] == '1') { ?>checked<?php } ?>>
                  <label for="exampleInputEmail1">Experience</label>
                  <input type="checkbox" data-name="experience_status" class="chkCategory" <?php if ($data['experience_status'] == '1') { ?>checked<?php } ?>>
                  <label for="exampleInputEmail1">About Us</label>
                  <input type="checkbox" data-name="aboutus_status" class="chkCategory" <?php if ($data['aboutus_status'] == '1') { ?>checked<?php } ?>>
                  <label for="exampleInputEmail1">Events</label>
                  <input type="checkbox" data-name="events_status" class="chkCategory" <?php if ($data['events_status'] == '1') { ?>checked<?php } ?>>
                  <label for="exampleInputEmail1">Comments</label>
                  <input type="checkbox" data-name="comments_status" class="chkCategory" <?php if ($data['comments_status'] == '1') { ?>checked<?php } ?>>
                  <label for="exampleInputEmail1">Contact Us</label>
                  <input type="checkbox" data-name="contact_status" class="chkCategory" <?php if ($data['contact_status'] == '1') { ?>checked<?php } ?>>
                </div>
                <div class="form-group">
                  <label for="exampleInputFile">Comment Image (Width:540 / Height:445)</label>
                  <div class="input-group">
                    <div class="custom-file" id="b3">
                      <input type="file" class="custom-file-input" id="comment_image" onChange="displayImageComment(this)" name="comment_image">
                      To Delete Uploaded Comment Image: <img src="../images/delete-icon.jpg" height="20" width="20" />
                    </div><br>
                    <!--<div class="input-group-append">
                        <span class="input-group-text" id="">Upload</span>
                      </div>-->
                  </div>
                </div>
                <div class="form-check">
                  <img id="comment_image_path" src="<?php echo $data['comment_image_path']; ?>" style="width:15%">
                  <input type="hidden" name="hidden_comment_image" id="hidden_comment_image" value="<?php echo $data['comment_image_path']; ?>" />
                </div>

                <div class="form-group">
                  <label for="exampleInputEmail1">Contact Us Google Map (Width:1100 / Height:440) <a target="_blank" href="https://www.embedgooglemap.net/">Get Link</a></label>
                  <textarea name="contact_map" class="form-control" placeholder="Contact Us Google Map"><?php echo $data['contact_map']; ?></textarea>
                </div>
                <br><br>
                <div class="card-footer">
                  <br> <button type="submit" class="btn btn-success">Submit</button>
                </div>
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

<?php include("template/footer.php"); ?>

<script>
  function displayImageBanner(e) {

    $("#hidden_banner_image").val('');
    if (e.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {


        document.querySelector('#cms_banner_image_path').setAttribute('src', e.target.result);
      }
      reader.readAsDataURL(e.files[0]);
    }
  }

  function displayImageLogo(e) {

    $("#hidden_logo_image").val('');
    if (e.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {


        document.querySelector('#logo_image_path').setAttribute('src', e.target.result);
      }
      reader.readAsDataURL(e.files[0]);
    }
  }


  function displayImageComment(e) {

    $("#hidden_comment_image").val('');
    if (e.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {


        document.querySelector('#comment_image_path').setAttribute('src', e.target.result);
      }
      reader.readAsDataURL(e.files[0]);
    }
  }
  //To Delete uploaded file
  $(document).ready(function() {
    $("#b1").click(function() {
      $("#cms_banner_image").wrap('<form>').closest('form').get(0).reset();
      $("#cms_banner_image").unwrap();
      $('#cms_banner_image_path').attr('src', '../images/noimg.png');
      $("#hidden_banner_image").val('');

    });

    $("#b2").click(function() {
      $("#logo_image").wrap('<form>').closest('form').get(0).reset();
      $("#logo_image").unwrap();
      $('#logo_image_path').attr('src', '../images/noimg.png');
      $("#hidden_logo_image").val('');
    });

    $("#b3").click(function() {
      $("#comment_image").wrap('<form>').closest('form').get(0).reset();
      $("#comment_image").unwrap();
      $('#comment_image_path').attr('src', '../images/noimg.png');
      $("#hidden_comment_image").val('');
    });



  });
  $(document).on('change', '.chkCategory', function(e) {
    var name = $(this).attr('data-name');
    // alert(name);
    if ($(this).prop('checked') == true) {
      var val = '1';
    } else {
      var val = '0';
    }
    $.ajax({
      url: "changestatus.php",
      type: 'POST',
      data: {
        name: name,
        val: val
      },
      success: function(data) {

      }
    });
  });

  
</script>