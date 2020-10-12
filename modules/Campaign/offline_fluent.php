 <?php
    include '../../pupilsight.php';
    $session = $container->get('session');
    $cid = $_SESSION['campaignid'];

    ?>

 <div class="modal fade bd-example-modal-lg show" id="popUp" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display:block !important;">
     <div class="modal-dialog modal-lg" style="max-width:1250px !important;">
         <div class="modal-content">
             <div class="modal-header">

                 <span class="modal-title mt-5" id="mySmallModalLabel" style="font-size:20px;font-weight:bold;">Create Form</span>

                 <span class='ml-5 mt-5'>(Email or Mobile Field is Mandotry (input type field should be email or mobile)</span>

                 <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closepopup" style="text-align: right; font-size: 35px;">
                     <span aria-hidden="true">×</span>
                 </button>
             </div>
             <div class="modal-body">
                 <!-- <iframe src="http://wp.pupiltalk.com/wp-admin/admin.php?page=fluent_forms#add=1" style="width:100%;height:93vh"></iframe>-->

                 <iframe id="wppage" src="<?php echo $_SESSION[$guid]['absoluteURL']; ?>/wp/wp-admin/admin.php?page=fluent_forms#add=1" border='0' style="border:0;width:100%;height:100vh"></iframe>
             </div>
         </div>
     </div>
 </div>
 <script>
     $(document).on('click', '#closepopup', function() {
         if (confirm("Are you sure want to close?")) {
             window.location.href = "<?php echo $_SESSION[$guid]['absoluteURL']; ?>/index.php?q=/modules/Campaign/index.php";

         }
         return false;
     });

     // $(document).on('click', '#saveFormData', function () {
     //     alert('1');
     //     var fformid = $(this).parent().children().attr('data-clipboard-text');
     //     $.ajax({
     //         url: 'modules/Campaign/addCampaignAjaxForm.php',
     //         type: 'post',
     //         data: { formshortcode: fformid },
     //         async: true,
     //         success: function (response) {

     //         }
     //     });

     // });

     $('#wppage').load(function() {
         var iframe = $('#wppage').contents();
         iframe.find("#wpadminbar").hide();
         iframe.find("#saveFormData").click(function() {
             var fformid = $(this).parent().children().attr('data-clipboard-text');
             $.ajax({
                 url: 'modules/Campaign/offline_addCampaignAjaxForm.php',
                 type: 'post',
                 data: {
                     formshortcode: fformid
                 },
                 async: true,
                 success: function(response) {
                     //location.reload();
                     //alertcampaign();
                     // var type = '1';
                     // $.ajax({
                     //     url: 'modules/Campaign/campaignfor.php',
                     //     type: 'post',
                     //     data: { type: type },
                     //     async: true,
                     //     success: function (response) {
                     //         window.location.href = response;
                     //     }
                     // });
                     var response = 'index.php?q=modules/Campaign/edit.php&id=<?php echo $cid; ?>';
                     alert('Your Offline Application Form Saved Successfully!');
                     window.location.href = response;

                     // $("#popUp").removeClass('show');
                     //setTimeout("alertcampaign()", 100);
                 }
             });

         });
     });
 </script>