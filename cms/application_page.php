<?php
include_once 'w2f/adminLib.php';
$adminlib = new adminlib();
session_start();
$data = $adminlib->getPupilSightData();
$section = $adminlib->getPupilSightSectionFrontendData();
$campaign=$adminlib->getcampaign();
// $app_status = $adminlib->getApp_statusData();
//  print_r($app_status);die();
/*$status= 1;


$data_target = $status==1 ? "#Application" : "#Login-reg"; */
$url_id=$_REQUEST['url_id'];
$chkstatus = $adminlib->chkCampaignStatus($url_id);
// echo $chkstatus;
// die(0);
if($chkstatus == '2'){
    header("Location: formstop.php"); 
    exit; 
} 
$campaign_byid=$adminlib->getcampaign_byid($url_id);

$program = $campaign_byid['progname'];
if(!empty($campaign_byid['classes'])){
    $getClass = $adminlib->getCampaignClass($campaign_byid['classes']);
}
//print_r($getClass);die();



//echo $campaign_byid['page_link'];

$app_links =array();
// echo '<pre>';
// print_r($_SESSION['campaignuserdata']);
// echo '</pre>';

?>
<!DOCTYPE html>
<html itemscope itemtype="http://schema.org/WebPage" lang="en-US"
    prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta charset="UTF-8">
    <title><?php echo $data['title']; ?></title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script> -->
    <!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script> -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel='stylesheet' id='rs-plugin-settings-css' href='assets/revslider/public/assets/css/rs6.css' type='text/css'
        media='all' />

    <link rel='stylesheet' id='learn-press-pmpro-style-css'
        href='assets/plugins/learnpress-paid-membership-pro/assets/style.css' type='text/css' media='all' />

    <link rel='stylesheet' id='builder-press-slick-css' href='assets/plugins/builderpress/assets/libs/slick/slick.css'
        type='text/css' media='all' />

    <link rel='stylesheet' id='js_composer_front-css' href='assets/plugins/js_composer/assets/css/js_composer.min.css'
        type='text/css' media='all' />

    <link rel='stylesheet' id='dashicons-css' href='assets/css/dashicons.min.css' type='text/css' media='all' />
    <link rel='stylesheet' id='learn-press-bundle-css' href='assets/plugins/learnpress/assets/css/bundle.min.css'
        type='text/css' media='all' />
    <link rel='stylesheet' id='learn-press-css' href='assets/plugins/learnpress/assets/css/learnpress.css'
        type='text/css' media='all' />
    <link rel='stylesheet' id='ionicon-css' href='assets/css/ionicons/ionicons.css' type='text/css' media='all' />
    <link rel='stylesheet' id='select2-style-css' href='assets/css/select2/core.css' type='text/css' media='all' />
    <link rel='stylesheet' id='builder-press-bootstrap-css' href='assets/css/bootstrap/bootstrap.css' type='text/css'
        media='all' />


    <link rel='stylesheet' id='thim-style-css' href='assets/css/style.css' type='text/css' media='all' />


    <link rel='stylesheet' id='thim-style-options-css' href='assets/css/demo.css' type='text/css' media='all' />
    <script type='text/javascript' src='assets/js/jquery/jquery.js'></script>
    <script type='text/javascript' src='assets/js/jquery/jquery-migrate.min.js'></script>


    <script type="text/javascript"
        src="../lib/LiveValidation/livevalidation_standalone.compressed.js?v=18.0.01"></script>
    <script type="text/javascript" src="../lib/jquery/jquery.js?v=18.0.01"></script>
    <script type="text/javascript" src="../lib/jquery/jquery-migrate.min.js?v=18.0.01"></script>
    <script type="text/javascript" src="../lib/jquery-ui/js/jquery-ui.min.js?v=18.0.01"></script>
    <script type="text/javascript"
        src="../lib/jquery-timepicker/jquery.timepicker.min.js?v=18.0.01"></script>
    <script type="text/javascript" src="../lib/chained/jquery.chained.min.js?v=18.0.01"></script>
    <script type="text/javascript" src="../resources/assets/js/core.min.js?v=18.0.01"></script>
<style>
@media (min-width: 576px)
{
.modal-dialog {
    max-width: 1010px !important;
}

}
.btncss {
    
    margin-bottom: 0;
    font-weight: 400;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    -ms-touch-action: manipulation;
    touch-action: manipulation;
    cursor: pointer;
    background-image: none;
    border: 1px solid transparent!important;
    padding: 6px 12px;
    font-size: 14px!important;
    line-height: 1.42857143;
    border-radius: 4px!important;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
.header_colr
{
background-color: rgba(78, 88, 178, 0.75)!important;
}
.btn_css
{
	    background-color: #2b97e4 !important;
}
.modal-dialog {
    -webkit-transform: none;
    transform: none;
    margin-top: 150px!important;
}
</style>

<script>
    $(document).ready(function(){
      
        var url = window.location.href;
        var arguments = url.split('status')[1].split('=');
//alert(arguments);
     
  if(arguments==',not')
        {
            
            $("#term_cndn").modal('show');

           
        } 
        else{
           
             $("#term_cndn").css("display", "none");
             
        }  

//arguments.shift();
        //alert(arguments);
        
       
        
      
     
       // 
    });
</script>

    <script>
	
    jQuery(document).on('click', '#submitContact', function(e) {
        e.preventDefault();
        //alert(1);
        //$("#ajaxloader").show();
        var chk = '0';
        jQuery('.chkempty').each(function() {
            var val = jQuery(this).val();
            if (val == '') {
                jQuery(this).addClass('chkemptycolor');
                chk = '0';
            } else {
                jQuery(this).removeClass('chkemptycolor');
                chk = '1';
            }
        });

        if (chk == '1') {
            jQuery.ajax({
                url: "ajax.php",
                type: 'POST',
                data: jQuery('#contactForm').serialize(),
                success: function(data) {
                    if (data == 'done') {
                        jQuery("#showmsg").show();
                        setTimeout(function() {
                            jQuery("#showmsg").hide();
                        }, 3000);
                        jQuery('#contactForm')[0].reset();
                    }
                }
            });
        }
    });

    jQuery(document).on('click', '#term_click', function(e) {
        var url      = window.location.href+'&status=accept'; 
       // alert(url);
        $("#term_accepted").val("1");
        //alert($("#term_accepted").val());
        window.location.href = url;

    });

    $(document).ready(function() {

    // $("#term_accepted").val("1");
     // var term_status=  $("#term_accepted").val();
    //alert(term_status);
     /* if(term_status =="1")
      {
        $("#term_cndn").modal('hide');

      }
      else
      {
        $("#term_cndn").modal('show');
      }*/

    });
 
    </script>
   


</head>

<body
    class="home page-template page-template-templates page-template-home-page page-template-templateshome-page-php page page-id-17 wp-embed-responsive theme-ivy-school pmpro-body-has-access woocommerce-no-js bg-type-color responsive auto-login left_courses wpb-js-composer js-comp-ver-6.0.5 vc_responsive">
   
    <div id="wrapper-container" class="content-pusher creative-right bg-type-color">
        
        
		  <?php include("index_header.php");?>
        <div id="main-content">
            <div id="home-main-content" class="home-content home-page container" role="main">
                
                
                <div class="vc_row-full-width vc_clearfix" ></div>
              
                    <div class="mobile-margin-0 wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner vc_custom_1540537006055">
                            <div class="container">
                                <div
                                    class="bp-element bp-element-heading vc_custom_1542033515902  layout-1  mobile-center mobile-line-heading">
                                   
                                    <span  id="showdiv" class="sub-title"
                                        style=" color:#292929; line-height:1.25; font-size:35px; font-weight:400; text-align:center"><?php echo ucwords($campaign_byid['name']).'  '.ucwords($campaign_byid['academic_year']);?></span>
                                   <!-- <div class="line"
                                        style="height:2px; width:300px; background-color:#e1e1e1;  ">
										
                                    </div>-->
                                    <input type="hidden" id="pid" value="<?php echo $campaign_byid['pupilsightProgramID'];?>">
                                    <input type="hidden" id="fid" value="<?php echo $campaign_byid['form_id'];?>">
                                    
                                    <div>
                                    <span>Program: <?php echo $program; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <span>Class <span style="color:red">* </span>: </span>
                                    <select id="class">
                                    <?php if(!empty($getClass)){
                                        foreach($getClass as $cls){    
                                    ?>
                                    <option value="<?php echo  $cls['pupilsightYearGroupID'];?>"><?php echo  $cls['name'];?></option>
                                    <?php } } ?>
                                    </select>
                                    <!-- <span style="color:red;font-size: 11px;">You Have to Select Class</span> -->
                                    </div>
                                </div>
                                <div class="wpb_text_column wpb_content_element  vc_custom_1541409660821 mobile-center">
                                    <div class="wpb_wrapper">
									
						 <iframe data-campid="<?php echo $campaign_byid['id'];?>" id="application_view" height="2000px" width="1000"
    src="<?php echo $campaign_byid['page_link'];?>">
</iframe>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wpb_column vc_column_container  bp-background-size-auto">
                        <div class="vc_column-inner vc_custom_1539746106290">
                            <div class="wpb_wrapper">
                               
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- #home-main-content -->
        </div><!-- #main-content -->
		<div id="myModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Subscribe our Newsletter</h4>
            </div>
            <div class="modal-body">
                <p>Subscribe to our mailing list to get the latest updates straight in your inbox.</p>
                <form>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Name">
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" placeholder="Email Address">
                    </div>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="term_cndn" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
	  <h4 style="float:left" class="modal-title">Terms & Conditions</h4>
        <button type="button" class="close"  onclick="window.history.back()">&times;</button>
        <input type="hidden" id="term_accepted" name="term_accepted" value="" />
      </div>
      <div class="modal-body">
        <p class="statusMsg">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
      </div>
     <!-- Modal Footer -->
            <div class="modal-footer">
                
                <button type="button" class="btn btn_css btn-primary btn-default" onclick="window.history.back()" >Reject</button>
				<button type="button" id="term_click" class="btn btn_css btn-primary btn-default" data-dismiss="modal" >Accept</button>
            </div>
    </div> 

  </div>
</div>
		
        
		
		<!-- #colophon -->
    </div><!-- wrapper-container -->
    <div id="back-to-top" class="default">
        <i data-fip-value="ion-ios-arrow-thin-up" class="ion-ios-arrow-thin-up"></i> </div>
    <!-- Memberships powered by Paid Memberships Pro v2.0.7.
 -->


    <div id="tp_chameleon_list_google_fonts"></div>
    
   
    <script type='text/javascript'>
    WebFont.load({
        google: {
            families: ['Poppins:300,500', 'Roboto:400']
        }
    });
    </script>
   
    <script type='text/javascript'>
    /* <![CDATA[ */
    var login_popup_js = {
        "login": "Email",
        "password": "Password"
    };
    var login_popup_js = {
        "login": "Email",
        "password": "Password"
    };
    /* ]]> */
    </script>
    


    <style type="text/css">
    .table {
        color: #7c7c7c;
    }

    .table-bordered,
    .table-bordered td,
    .table-bordered th {
        border: 1px solid #7c7c7c;
    }

    span {
        font-weight: bold;
    }



    .mblnum {
        height: 40px;
        border: 1px solid black;
        border-radius: 4px;
        margin-left: 29px;
        width: 257px;
    }

    .lablembl {
        float: right;
        color: #292929;
        font-weight: normal;
    }

    .formcss {
        border: 1px solid black;
        padding: 10px;
        padding-top: 30px;
        height: 108%;
    }

    .icon {
        border-radius: 4px;
        padding: 4px;
        background: dodgerblue;
        color: white;
        margin: 10px;
        text-align: center;
        margin-left: -4px;
    }

    .table-bordered thead th {
        border-bottom-width: 0px;
    }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.min.js"></script>
<script src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>
    <script type='text/javascript'>
    //<![CDATA[
    $(window).load(function() {
        $(document).ready(function() {
            $('#txtPhone').blur(function(e) {
                if (validatePhone('txtPhone')) {
                    $('#spnPhoneStatus').html(' 	');
                    $('#spnPhoneStatus').css('color', 'green');
                } else {
                    $('#spnPhoneStatus').html('Invalid Mobile Number');
                    $('#spnPhoneStatus').css('color', 'red');
                }
            });
        });

        function validatePhone(txtPhone) {
            var a = document.getElementById(txtPhone).value;
            var filter = /[1-9]{1}[0-9]{9}/;
            if (filter.test(a)) {
                return true;
            } else {
                return false;
            }
        }
    }); //]]> 

    
    var iframe = document.getElementById("application_view");
    
    // Adjusting the iframe height onload event
    iframe.onload = function(){
        iframe.style.height = (Number(iframe.contentWindow.document.body.scrollHeight) + 100) + 'px';
    }

    $('#application_view').load(function(){
        var iframe = $('#application_view').contents();
        iframe.find("#wpadminbar").hide();
        iframe.find(".section-inner").hide();

        var pid = iframe.find(".fluentform");
        iframe.find("form").submit(function(){
            getPDF(pid);
            setTimeout(function() {
                var flag = true;
                iframe.find(".text-danger").each(function(){
                    flag = false;
                });
                if(flag){
                    insertcampaign();
                }
            }, 2000);
        });
    });

    function getPDF(pid){

        var HTML_Width = pid.width();
        var HTML_Height = pid.height();
        var top_left_margin = 15;
        var PDF_Width = HTML_Width+(top_left_margin*2);
        var PDF_Height = (PDF_Width*1.5)+(top_left_margin*2);
        var canvas_image_width = HTML_Width;
        var canvas_image_height = HTML_Height;

        var totalPDFPages = Math.ceil(HTML_Height/PDF_Height)-1;


        html2canvas(pid[0],{allowTaint:true}).then(function(canvas) {
            canvas.getContext('2d');
            
            console.log(canvas.height+"  "+canvas.width);
            
            
            var imgData = canvas.toDataURL("image/jpeg", 1.0);
            var pdf = new jsPDF('p', 'pt',  [PDF_Width, PDF_Height]);
            pdf.addImage(imgData, 'JPG', top_left_margin, top_left_margin,canvas_image_width,canvas_image_height);
            
            
            for (var i = 1; i <= totalPDFPages; i++) { 
                pdf.addPage(PDF_Width, PDF_Height);
                pdf.addImage(imgData, 'JPG', top_left_margin, -(PDF_Height*i)+(top_left_margin*4),canvas_image_width,canvas_image_height);
            }
            
            //pdf.save("HTML-Document.pdf");
            //pdf.save();
            var blob = btoa(pdf.output()); 
            // var blob = pdf.output('blob');
            var formData = new FormData();
            var type = 'saveApplicantForm';
            formData.append('pdf', blob);
            formData.append('type', type);
            formData.append('val', 'pdfdata');
            $.ajax({
                url: 'ajax_data.php', // not an actual good naming 
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response){console.log(response)},
                error: function(err){console.log(err)}
            });
        });
    };

    function insertcampaign(){
        var val = $("#application_view").attr('data-campid');
        var pid = $("#pid").val();
        var fid = $("#fid").val();
        var clid = $("#class").val();
        if(val != ''){
            var type = 'insertcampaigndetails';
            setTimeout(function() {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type:type, pid:pid, fid:fid, clid:clid },
                    async: true,
                    success: function(response) {
                        $('html, body').animate({scrollTop: $("#showdiv").offset().top}, 2000);
                    }
                });
            }, 500);
        }
    }
    </script>


</body>