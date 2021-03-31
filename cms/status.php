<?php
include_once 'w2f/adminLib.php';
$adminlib = new adminlib();
session_start();
$data = $adminlib->getPupilSightData();
$section = $adminlib->getPupilSightSectionFrontendData();
$campaign = $adminlib->getcampaign();
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$responseLink = $base_url . "/thirdparty/payment/worldline/skit/meTrnSuccess.php";

//baseurl = http://testchristacademy.pupilpod.net
// $app_status = $adminlib->getApp_statusData();
//  print_r($app_status);die();
/*$status= 1;


$data_target = $status==1 ? "#Application" : "#Login-reg"; */
 $sid = $_SESSION["appsubmitionid"];

if (empty($sid)) {
    $sid = 0;
}
/*
if (!empty($_REQUEST['id'])) {
    $sid = $_REQUEST['id'];
} else {
    $sid = 0;
}*/


$chkstatus = $adminlib->getStatus($sid);

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

$baseurl = getDomain();
//$baseurl = getDomain().'/pupilsight';

$logo = $baseurl . "/cms/images/pupilpod_logo.png";
if (isset($data['logo_image'])) {
    $logo = $baseurl . '/cms/images/logo/' . $data['logo_image'];
}
?>

<?php 
// include("index_header.php"); 
?>

<!doctype html>
<html class="no-js" lang="">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title><?= $title; ?></title>
    <meta name="description" content="Pupilpod is India’s first cloud based School ERP Software. It is 100% customizable and evolves to meet each School or University’s needs.
    Discover how with Pupilpod you can automate your entire Academic, Operational, and Management information systems" />
    <meta name="keywords" content="Pupilpod,School ERP,erp,School ERP Software, School Management Solution">

    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <meta name="msapplication-TileColor" content="#206bc4" />
    <meta name="theme-color" content="#206bc4" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="HandheldFriendly" content="True" />
    <meta name="MobileOptimized" content="320" />
    <meta name="robots" content="noindex,nofollow,noarchive" />
    <link rel="icon" href="./favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon" />

    <!-- CSS files -->
    <link rel="stylesheet" href="//cdn.materialdesignicons.com/5.0.45/css/materialdesignicons.min.css">


    <link rel="stylesheet" href="<?= $baseurl; ?>/assets/css/normalize.css?v=1.0" type="text/css" media="all" />

    <link href="<?= $baseurl; ?>/assets/css/tabler.css" rel="stylesheet" />
    <link href="<?= $baseurl; ?>/assets/css/dev.css" rel="stylesheet" />

    <!-- Libs JS -->
    <script src="<?= $baseurl; ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseurl; ?>/assets/libs/jquery/dist/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/libs/jquery/jquery-migrate.min.js?v=1.0"></script>


    <script src="<?= $baseurl; ?>/assets/js/core.js"></script>

    <script type="text/javascript">
        var tb_pathToImage = "<?= $baseurl; ?>/assets/libs/thickbox/loadingAnimation.gif";
    </script>

    <script src="<?= $baseurl; ?>/assets/js/tabler.min.js"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/libs/thickbox/thickbox-compressed.js?v=1.0"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/js/jquery.form.js?v=1.0"></script>


    <style>
        .carouselTitle {
            line-height: 42px;
            font-size: 42px;
            font-weight: 600;
        }

        .carouselSubTitle {
            line-height: 36px;
            font-size: 36px;
            font-weight: 600;
        }

        .multiple_device {
            left: 45%;
            margin: 0 auto;
            max-width: 115vw;
            position: relative;
            -ms-transform: translateX(-50%);
            transform: translateX(-50%);
            width: 115vw;
        }

        .mh375 {
            min-height: 375px;
        }

        .gray {
            background-color: #f8f9fa;
        }

        .main-img {
            width: 70%;
            height: 70%;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
        }

        @media only screen and (min-width: 768px) {
            .navDesktop {
                /*position: fixed;
                z-index: 1000;*/
                width: 100%;
                min-height: 70px;
                font-size: 16px;
            }
        }

        .closeX {
            position: absolute;
            right: 10px;
            top: 0px;
            font-size: 30px;
            cursor: pointer;
            color: #6e7582;
        }

        .gmap_canvas,
        .mapouter {
            width: 100% !important;
            height: 100% !important;
        }

        .hero {
            border: 1px solid rgba(110, 117, 130, .2);
            border-radius: 3px;
            max-height: 400px;
        }

        .slick-slide {
            margin: 0 10px;
        }

        /* the parent */
        .slick-list {
            margin: 0 -10px;
        }

        .chkemptycolor {
            border: 1px red solid;
        }

        .btnPay {
            display: inline-block;
            font-weight: bold;
            font-size: 20px;
            width: 200px;
            line-height: 1.4285714;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            padding: 0.4375rem 1rem;
            border-radius: 3px;
            color: #ffffff !important;
            background-color: #206bc4;
            border-color: #206bc4;
        }

        .i-am-centered {
            margin: 0 auto;
            width: 50%;
        }
    </style>
</head>


<!-- status -->

<body class='antialiased'>
    <div id="homePanel" class="page">
        <header class="navbar navbar-expand-md navbar-light navDesktop">
            <div class="container-fluid">
                <div style="width:100%;">
                    <div class="float-left">
                        <a href="<?= $baseurl; ?>/index.php" class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pr-0 pr-md-3">
                            <img src="<?= $logo; ?>" alt="Pupilpod" class="navbar-brand-image">
                        </a>
                    </div>
                    <div class="float-right">
                        <a href="<?= $baseurl; ?>" class='btn btn-secondary'>Back</a>
                    </div>
                    <div class="float-none"></div>
                </div>
            </div>
        </header>
        <div id="wrapper-container" class="content-pusher creative-right bg-type-color">
            <div id="main-content">
            <div id="home-main-content" class="home-content home-page container" role="main">


                <div class="mobile-margin-0 wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                    <div class="vc_column-inner vc_custom_1540537006055">
                        <div class="container" style="margin-top: 50px;text-align:center;">
                            <div class="bp-element bp-element-heading vc_custom_1542033515902  layout-1  mobile-center mobile-line-heading">

                            </div>
                            <div class="wpb_text_column wpb_content_element  vc_custom_1541409660821 mobile-center">
                                <div class="wpb_wrapper">

                                    <div class="row" style="">
                                        <div class="col-sm-12 col-lg-12">
                                            <table id="app_lst_tbl" cellspacing='0' class='table'>
                                                <thead>
                                                    <tr class='head'>
                                                        <th style="width:5%">
                                                            SI No
                                                        </th>
                                                        <th style="width:20%">
                                                            Applicant Name
                                                        </th>
                                                        <th style="width:20%">
                                                            Campaign Name
                                                        </th>
                                                        <th style="width:20%">
                                                            Submission Date
                                                        </th>

                                                        <th style="width:5%">
                                                            Status
                                                        </th>

                                                        <th style="width:5%">
                                                            Form
                                                        </th>
                                                        <th style="width:5%">
                                                            Fee Receipt
                                                        </th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($chkstatus)) {
                                                        $cnt = 1;
                                                        foreach ($chkstatus as $row) {
                                                            $statedata = $adminlib->getstatedata($row['id'], $row['form_id'], $row['submission_id']);
                                                            echo "<tr>";
                                                            echo '<td>';
                                                            echo $cnt;
                                                            echo '</td>';
                                                            echo '<td>';
                                                            echo $row["username"];
                                                            echo '</td>';
                                                            echo '<td>';
                                                            echo $row["name"];
                                                            echo '</td>';
                                                            echo '<td>';
                                                            echo $row['created_at'];
                                                            echo '</td>';
                                                            echo '<td>';
                                                            echo $statedata;
                                                            echo '</td>';
                                                            echo '<td>';
                                                            if (!empty($row['application_no'])) {
                                                                $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                                                                $fname = trim(str_replace("/", "_", $row['application_no']));
                                                                //$link = $base_url . '/public/applicationpdf/parent/' . $fname;
                                                                $link = $base_url . '/cms/ajaxfile.php?cid=' . $row['id'] . "&submissionId=" . $row['submission_id'];
                                                                echo '<a href="' . $link . '"><img title="Download" src="' . $base_url . '/cms/assets/css/img/download-box.png"></img></a>';
                                                            }
                                                            echo '</td>';
                                                            echo '<td>';
                                                            if (!empty($row['transaction_id'])) {
                                                                $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                                                                $link = $base_url . '/public/receipts/' . $row['transaction_id'];
                                                                echo '<a href="' . $link . '" download><img title="Download" src="' . $base_url . '/cms/assets/css/img/download-box.png"></img></a>';
                                                            }
                                                            echo '</td>';
                                                            echo '</tr>';
                                                        }
                                                    } ?>
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
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
                    <button type="button" class="close" onclick="window.history.back()">&times;</button>
                    <input type="hidden" id="term_accepted" name="term_accepted" value="" />
                </div>
                <div class="modal-body">
                    <p class="statusMsg">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">

                    <button type="button" class="btn btn_css btn-primary btn-default" onclick="window.history.back()">Reject</button>
                    <button type="button" id="term_click" class="btn btn_css btn-primary btn-default" data-dismiss="modal">Accept</button>
                </div>
            </div>

        </div>
    </div>

    <!-- online Payment By Bikash -->
    <?php
    $callbacklink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
        "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .
        $_SERVER['REQUEST_URI'];

    ?>

    <!-- online Payment By Bikash -->

    <!-- #colophon -->
    </div><!-- wrapper-container -->
    <div id="back-to-top" class="default">
        <i data-fip-value="ion-ios-arrow-thin-up" class="ion-ios-arrow-thin-up"></i> </div>
    <!-- Memberships powered by Paid Memberships Pro v2.0.7.
 -->
</div>

    <div id="tp_chameleon_list_google_fonts"></div>
    <a id="downloadLink" href="ajaxfile.php?cid=<?php echo $url_id; ?>" class="" style="display:none;">Download Receipts</a>

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

        .error {
            border: 2px solid red;
        }

        .iheight {
            height: 300px !important;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.min.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>
    <script type='text/javascript'>
        //<![CDATA[
        $(window).load(function() {



            var d = new Date();
            var n = d.getTime();
            var orderID = n + '' + randomFromTo(0, 1000);

            document.getElementById("OrderId").value = orderID;

            function randomFromTo(from, to) {
                return Math.floor(Math.random() * (to - from + 1) + from);
            }

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
        iframe.onload = function() {
            iframe.style.height = (Number(iframe.contentWindow.document.body.scrollHeight) + 100) + 'px';
        }

        $('#application_view').load(function() {
            var iframe = $('#application_view').contents();
            iframe.find(".ff-btn-submit").prop('disabled', true);
            iframe.find("head").append($("<style type='text/css'>  html{margin-top:-90px;}  </style>"));
            iframe.find("#wpadminbar").hide();
            iframe.find(".section-inner").hide();
            iframe.find("input[name=age_value]").prop('readonly', true);
            iframe.find("input[name=dob_in_words]").prop('readonly', true);
            var pid = iframe.find(".fluentform");
            iframe.find("input[name=date_of_birth]").change(function() {

                var userDate = $(this).val();
                var date_string = moment(userDate, "DD/MM/YYYY").format("MM/DD/YYYY");
                var From_date = new Date(date_string);

                var userDate2 = iframe.find("input[name=as_on_date]").val();
                var date_string2 = moment(userDate2, "DD/MM/YYYY").format("MM/DD/YYYY");
                var To_date = new Date(date_string2);

                var diff_date = To_date - From_date;


                var years = Math.floor(diff_date / 31536000000);
                var months = Math.floor((diff_date % 31536000000) / 2628000000);
                var days = Math.floor(((diff_date % 31536000000) % 2628000000) / 86400000);
                var ageval = years + " years " + months + " months and " + days + " days";
                iframe.find("input[name=age_value]").val(ageval);

                var dateTime = new Date(From_date);
                var month = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                var date = ['First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth', 'Seventh', 'Eighth', 'Ninth', 'Tenth', 'Eleventh', 'Twelfth', 'Thirteenth', 'Fourteenth', 'Fifteenth', 'Sixteenth', 'Seventeenth', 'Eighteenth', 'Nineteenth', 'Twentieth', 'Twenty-First', 'Twenty-Second', 'Twenty-Third', 'Twenty-Fourth', 'Twenty-Fifth', 'Twenty-Sixth', 'Twenty-Seventh', 'Twenty-Eighth', 'Twenty-Ninth', 'Thirtieth', 'Thirty-First'];
                var strDateTime = date[dateTime.getDate() - 1] + " " + month[dateTime.getMonth()] + " " + toWords(dateTime.getFullYear());
                iframe.find("input[name=dob_in_words]").val(strDateTime);
            });

            var cls = iframe.find("#class").prop('readonly', true);

            iframe.find(".ff-el-form-control").change(function() {
                $.each($(this), function() {
                    val = $("#class option:selected").val();
                    if (val == '') {
                        $("#class").addClass('error').focus();
                        iframe.find(".ff-btn-submit").prop('disabled', true);
                        alert('You Have to Select Class');
                        return false;
                    } else {
                        $("#class").removeClass('error');
                        iframe.find(".ff-btn-submit").prop('disabled', false);
                        return true;
                    }
                });
            });

            iframe.find("form").submit(function() {
                $("#back-to-top").click();
                //getPDF(pid);
                setTimeout(function() {
                    var flag = true;
                    iframe.find(".text-danger").each(function() {
                        flag = false;
                    });
                    if (flag) {
                        insertcampaign();
                    }
                }, 2000);
            });

        });


        function toWords(s) {
            var th = ['', 'Thousand', 'Million', 'Billion', 'Trillion'];
            var dg = ['Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
            var tn = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
            var tw = ['Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
            s = s.toString();
            s = s.replace(/[\, ]/g, '');
            if (s != parseFloat(s)) {
                return 'not a number';
            }
            var x = s.indexOf('.');
            if (x == -1) x = s.length;
            if (x > 15) return 'too big';
            var n = s.split('');
            var str = '';
            var sk = 0;
            for (var i = 0; i < x; i++) {
                if ((x - i) % 3 == 2) {
                    if (n[i] == '1') {
                        str += tn[Number(n[i + 1])] + ' ';
                        i++;
                        sk = 1;
                    } else if (n[i] != 0) {
                        str += tw[n[i] - 2] + ' ';
                        sk = 1;
                    }
                } else if (n[i] != 0) {
                    str += dg[n[i]] + ' ';
                    if ((x - i) % 3 == 0) str += 'hundred ';
                    sk = 1;
                }
                if ((x - i) % 3 == 1) {
                    if (sk) str += th[(x - i - 1) / 3] + ' ';
                    sk = 0;
                }
            }
            if (x != s.length) {
                var y = s.length;
                str += 'point ';
                for (var i = x + 1; i < y; i++) str += dg[n[i]] + ' ';
            }
            return str.replace(/\s+/g, ' ');
        }

        function getPDF(pid) {

            var HTML_Width = pid.width();
            var HTML_Height = pid.height();
            var top_left_margin = 15;
            var PDF_Width = HTML_Width + (top_left_margin * 2);
            var PDF_Height = (PDF_Width * 1.5) + (top_left_margin * 2);
            var canvas_image_width = HTML_Width;
            var canvas_image_height = HTML_Height;

            var totalPDFPages = Math.ceil(HTML_Height / PDF_Height) - 1;


            html2canvas(pid[0], {
                allowTaint: true
            }).then(function(canvas) {
                canvas.getContext('2d');

                console.log(canvas.height + "  " + canvas.width);


                var imgData = canvas.toDataURL("image/jpeg", 1.0);
                var pdf = new jsPDF('p', 'pt', [PDF_Width, PDF_Height]);
                pdf.addImage(imgData, 'JPG', top_left_margin, top_left_margin, canvas_image_width, canvas_image_height);


                for (var i = 1; i <= totalPDFPages; i++) {
                    pdf.addPage(PDF_Width, PDF_Height);
                    pdf.addImage(imgData, 'JPG', top_left_margin, -(PDF_Height * i) + (top_left_margin * 4), canvas_image_width, canvas_image_height);
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
                    success: function(response) {
                        console.log(response)
                    },
                    error: function(err) {
                        console.log(err)
                    }
                });
            });
        };

        function insertcampaign() {
            var val = $("#application_view").attr('data-campid');
            var pid = $("#pid").val();
            var fid = $("#fid").val();
            var clid = $("#class option:selected").val();
            if (val != '') {
                var type = 'insertcampaigndetails';
                setTimeout(function() {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            val: val,
                            type: type,
                            pid: pid,
                            fid: fid,
                            clid: clid
                        },
                        async: true,
                        success: function(response) {
                            $("#progClassDiv").remove();
                            //$("#downloadLink")[0].click();
                            $("#application_view").addClass('iheight');
                            $("#payAdmissionFee").show();
                        }
                    });
                }, 500);
            }
        }
    </script>


</body>