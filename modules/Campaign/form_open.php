 
<?php
include '../../pupilsight.php';
$session = $container->get('session');
$subId = $_GET['subid'];
$sql = 'Select a.form_id,b.id AS campId FROM wp_fluentform_submissions AS a LEFT JOIN campaign AS b ON a.form_id = b.form_id WHERE a.id = '.$subId.' ';
$result = $connection2->query($sql);
$forms = $result->fetch();
$formId = $forms['form_id'];
$campId = $forms['campId'];
?>

<div class="modal fade bd-example-modal-lg show" id="popUp" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display:block !important;">
  <div class="modal-dialog modal-lg" style="max-width:1250px !important;">
    <div class="modal-content">
    <div class="modal-header">
       
        <span class="modal-title mt-5" id="mySmallModalLabel" style="font-size:20px;font-weight:bold;">View & Edit Form</span>  

        <span class='ml-5 mt-5'>(Email or Mobile Field is Mandotry (input type field should be email or mobile)</span>

        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closepopup" style="text-align: right; font-size: 35px;">
            <span aria-hidden="true">×</span>
            </button>
    </div>    
    <div class="modal-body">
     <!-- <iframe src="http://wp.pupiltalk.com/wp-admin/admin.php?page=fluent_forms#add=1" style="width:100%;height:93vh"></iframe>-->
       
        <iframe id="wppage" src="<?php echo $_SESSION[$guid]['absoluteURL'];?>/wp/wp-admin/admin.php?page=fluent_forms&route=entries&form_id=<?php echo $formId;?>#/entries/<?php echo $subId;?>" style="width:100%;height:85vh"></iframe>
    </div>     
    </div>
  </div>
</div>
<script>
    $(document).on('click','#closepopup',function(){
        // if (confirm("Are you sure want to close?")) {
            window.location.href = "<?php echo $_SESSION[$guid]['absoluteURL'];?>/index.php?q=/modules/Campaign/campaignFormList.php&id=<?php echo $campId;?>";
            
        // }
        // return false;
    });

    $('#wppage').load(function() {
        var iframe = $('#wppage').contents();
        iframe.find("#wpadminbar").remove();
        iframe.find(".form_internal_menu").remove();
        iframe.find("#adminmenumain").remove();
        iframe.find(".entry_submission_activity").remove();
        iframe.find(".entry_submission_logs").remove();
        iframe.find(".ff_email_resend_inline").remove();
        iframe.find(".el-icon-back").parent().remove();
        iframe.find(".el-dropdown-selfdefine").remove();
        iframe.find("#wpcontent").append($("<style type='text/css'> #wpcontent {margin-left:0px !important;margin-top: -50px;}  </style>"));
        
       
        iframe.find("#saveFormData").click(function() {
            var fformid = $(this).parent().children().attr('data-clipboard-text');
            
        });

       
        iframe.find(".el-icon-edit").click(function() {
            setTimeout(function(){
                $.each(iframe.find(".el-form-item__label"), function() {
                    console.log('work');
                    var hidden = $(this).text();
                    if(hidden == 'input_hidden' ){
                        alert('find');
                        $(this).parent().hide();
                    }
                });
            },1000);
        });

        
    });
</script>    

