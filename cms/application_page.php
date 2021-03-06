<?php
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
include_once 'w2f/adminLib.php';
$adminlib = new adminlib();
session_start();
$url_id = $_REQUEST['url_id'];
if (empty($url_id)) {
    header("Location: index.php");
    exit;
}

if ($adminlib->isCampaignActive($url_id) == FALSE) {
    echo "<h2>Campaign is no longer active.</h2>";
    die();
}


$data = $adminlib->getPupilSightData();
$section = $adminlib->getPupilSightSectionFrontendData();
$campaign = $adminlib->getcampaign();


//baseurl = http://testchristacademy.pupilpod.net
// $app_status = $adminlib->getApp_statusData();
//  print_r($app_status);die();
/*$status= 1;


$data_target = $status==1 ? "#Application" : "#Login-reg"; */

$chkstatus = $adminlib->chkCampaignStatus($url_id);
// echo $chkstatus;
// die(0);
if ($chkstatus == '2') {
    header("Location: formstop.php");
    exit;
}
$campaign_byid = $adminlib->getcampaign_byid($url_id);

$sqlchk = "SELECT a.id, a.pupilsightProgramID, a.campaign_id, b.name FROM campaign_prog_class AS a LEFT JOIN pupilsightProgram AS b ON a.pupilsightProgramID = b.pupilsightProgramID  WHERE a.campaign_id = " . $url_id . " GROUP BY a.pupilsightProgramID ";
$cmpProClsChkData = database::doSelect($sqlchk);

$programData = array();
if (!empty($cmpProClsChkData)) {
    $programData = $cmpProClsChkData;
} else {
    $program = $campaign_byid['progname'];
    if (!empty($campaign_byid['classes'])) {
        $getClass = $adminlib->getCampaignClass($campaign_byid['classes']);
    }
}

//print_r($programData);die();



//echo $campaign_byid['page_link'];

$app_links = array();
// echo '<pre>';
// print_r($_SESSION['campaignuserdata']);
// echo '</pre>';

$sql = "SELECT id FROM campaign  WHERE id = " . $url_id . " AND  status = '2' AND CURDATE() between start_date and end_date order by id DESC";
$campaignStatus = database::doSelect($sql);

$sqlo = "SELECT * FROM pupilsight_cms  WHERE title != '' ";
$orgData = database::doSelectOne($sqlo);
// echo '<pre>';
// print_r($orgData);
// echo '</pre>';
// die();

if (empty($campaignStatus)) {
    header("Location: index.php");
    exit;
}

$logo = $baseurl . "/cms/images/pupilpod_logo.png";
if (isset($data['logo_image'])) {
    $logo = $baseurl . '/cms/images/logo/' . $data['logo_image'];
}

$title = isset($data["title"]) ? ucwords($data["title"]) : "Pupilpod";
?>
<!doctype html>
<html class="no-js" lang="">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title><?= $title; ?></title>
    <meta name="description" content="Pupilpod is India???s first cloud based School ERP Software. It is 100% customizable and evolves to meet each School or University???s needs.
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

<body id='chkCounterSession' class='antialiased'>
    <!-- Preloader Start Here -->
    <div id="preloader" style="display:none;"></div>
    <!-- Preloader End Here -->

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
                <div id="home-main-content" class="container-fluid" role="main">


                    <div class="">
                        <div class="">
                            <div class="container" style="">
                                <div class="i-am-centered">

                                    <input type="hidden" id="chkemph" value="0">

                                    <input type="hidden" id="fid" value="<?php echo $campaign_byid['form_id']; ?>">
                                    <input type="hidden" id="allowms" value="<?php echo $campaign_byid['allow_multiple_submission']; ?>">
                                    <input type="hidden" id="chkfeesett" value="<?php echo $campaign_byid['is_fee_generate']; ?>">
                                    <input type="hidden" id="cid" value="<?php echo $url_id; ?>">

                                    <?php if (!empty($programData)) { ?>
                                        <div id="progClassDiv" class="row mt-4">
                                            <div class="col-md-3 col-sm-12">
                                                <span>Program<span style="color:red">* </span> </span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <select id="pid">
                                                    <option value="">Select</option>
                                                    <?php if (!empty($programData)) {
                                                        foreach ($programData as $prg) {
                                                    ?>
                                                            <option value="<?php echo  $prg['pupilsightProgramID']; ?>"><?php echo  $prg['name']; ?></option>
                                                    <?php }
                                                    } ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 col-sm-12">
                                                <span>Class <span style="color:red">* </span> </span>
                                                <select id="class" class="form-control">
                                                    <option value="">Select</option>

                                                </select>
                                            </div>
                                            <!-- <span style="color:red;font-size: 11px;">You Have to Select Class</span> -->
                                        </div>
                                        <input type="hidden" id="chkProg" value="1">
                                    <?php } else { ?>
                                        <input type="hidden" id="chkProg" value="2">
                                        <input type="hidden" id="pid" value="<?php echo $campaign_byid['pupilsightProgramID']; ?>">
                                        <div id="progClassDiv" class="row mt-4">
                                            <div class="col-md-4 col-sm-12"></div>
                                            <span>Program: <?php echo $program; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        </div>
                                        <div class="col-md-4 col-sm-12"></div>
                                        <span>Class <span style="color:red">* </span> </span>
                                        <select id="class" class="form-control">
                                            <option value="">Select</option>
                                            <?php if (!empty($getClass)) {
                                                foreach ($getClass as $cls) {
                                            ?>
                                                    <option value="<?php echo  $cls['pupilsightYearGroupID']; ?>"><?php echo  $cls['name']; ?></option>
                                            <?php }
                                            } ?>
                                        </select>
                                </div>
                                <!-- <span style="color:red;font-size: 11px;">You Have to Select Class</span> -->
                            </div>
                        <?php } ?>


                        </div>
                        <div class="">
                            <div class="">

                                <iframe data-campid="<?php echo $campaign_byid['id']; ?>" id="application_view" width="100%" border='0' style='border:0;' src="<?php echo $campaign_byid['page_link']; ?>">
                                </iframe>

                                <?php if (!empty($campaign_byid['fn_fee_structure_id']) && $campaign_byid['is_fee_generate'] == '2') {
                                    $sql = "SELECT SUM(total_amount) AS amt FROM fn_fee_structure_item WHERE fn_fee_structure_id = " . $campaign_byid['fn_fee_structure_id'] . " ";
                                    $result = database::doSelectOne($sql);
                                    $applicationAmount = $result['amt'] * 100;

                                    $random_number = mt_rand(1000, 9999);
                                    $today = time();
                                    $orderId = $today . $random_number;

                                    $sqlfh = "SELECT fn_fees_head_id FROM fn_fee_structure WHERE id =".$campaign_byid['fn_fee_structure_id']." ";
                                    $resultfh = database::doSelectOne($sqlfh);
                                   

                                    $fn_fees_head_id = $resultfh['fn_fees_head_id'];

                                    $sql = 'SELECT b.* FROM fn_fees_head AS a LEFT JOIN fn_fee_payment_gateway AS b ON a.payment_gateway_id = b.id WHERE a.id = '.$fn_fees_head_id.' ';
                                    $gatewayData = database::doSelectOne($sql);
                                    
                                    $terms = $gatewayData['terms_and_conditions'];
                                    $gatewayID = $gatewayData['id'];
                                    $gateway = $gatewayData['name'];

                                    if (!empty($gateway)) {
                                        if ($gateway == 'WORLDLINE') {

                                            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                                            $responseLink = $base_url . "/thirdparty/payment/worldline/skit/meTrnSuccess.php";
                                        ?>
                                            <form id="admissionPay" action="../thirdparty/payment/worldline/skit/meTrnPay.php" method="post" style="text-align:center;">
                                                <input type="hidden" name="payment_gateway_id" value="<?php echo $gatewayID; ?>">
                                                <input type="hidden" value="<?php echo $orderId; ?>" id="OrderId" name="OrderId">
                                                <input type="hidden" name="amount" value="<?php echo $applicationAmount; ?>">
                                                <input type="hidden" value="INR" id="currencyName" name="currencyName">
                                                <input type="hidden" value="S" id="meTransReqType" name="meTransReqType">
                                                <input type="hidden" name="mid" id="mid" value="<?php echo $gatewayData['mid']; ?>">
                                                <input type="hidden" name="enckey" id="enckey" value="<?php echo $gatewayData['key_id']; ?>">
                                                <input type="hidden" name="campaignid" value="<?php echo $url_id; ?>">
                                                <input type="hidden" name="sid" value="0">
                                                <input type="hidden" class="applicantName" name="name" value="">
                                                <input type="hidden" class="applicantEmail" name="email" value="">
                                                <input type="hidden" class="applicantPhone" name="phone" value="">

                                                <input type="hidden" name="responseUrl" id="responseUrl" value="<?php echo $responseLink; ?>" />

                                                <button type="submit" class="btnPay" style="display:none;" id="payAdmissionFee">Pay</button>
                                            </form>
                                        <?php } elseif ($gateway == 'RAZORPAY') {
                                            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                                            // $responseLink = $base_url . "/cms/index.php?return=1";
                                            $responseLink = $base_url . "/home.php";

                                        ?>
                                            <form id="admissionPay" action="../thirdparty/paymentadm/razorpay/pay.php" method="post" style="text-align:center;">
                                                <input type="hidden" name="payment_gateway_id" value="<?php echo $gatewayID; ?>">
                                                <input type="hidden" value="<?php echo $orderId; ?>" id="OrderId" name="OrderId">
                                                <input type="hidden" name="amount" value="<?php echo $applicationAmount; ?>">

                                                <input type="hidden" name="mid" id="mid" value="WL0000000009424">
                                                <input type="hidden" name="enckey" id="enckey" value="4d6428bf5c91676b76bb7c447e6546b8">
                                                <input type="hidden" name="campaignid" value="<?php echo $url_id; ?>">
                                                <input type="hidden" name="sid" value="0">
                                                <input type="hidden" class="applicantName" name="name" value="">
                                                <input type="hidden" class="applicantEmail" name="email" value="">
                                                <input type="hidden" class="applicantPhone" name="phone" value="">

                                                <input type="hidden" name="callbackurl" id="responseUrl" value="<?= $responseLink ?>">
                                                <input type="hidden" value="<?php echo $orgData['title']; ?>" id="organisationName" name="organisationName">
                                                <input type="hidden" value="<?php echo $orgData['logo_image']; ?>" id="organisationLogo" name="organisationLogo">

                                                <button type="submit" class="btnPay" style="display:none;" id="payAdmissionFee">Pay</button>
                                            </form>

                                        <?php } elseif ($gateway == 'PAYU') {
                                            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                                            //$responseLink = $base_url . "/cms/index.php?return=1";
                                            $responseLink = $base_url . "/home.php";
                                        ?>
                                            <form id="admissionPay" action="../thirdparty/payment/payu/checkout.php" method="post" style="text-align:center;">
                                                <input type="hidden" name="payment_gateway_id" value="<?php echo $gatewayID; ?>">
                                                <input type="hidden" value="<?php echo $orderId; ?>" id="OrderId" name="OrderId">
                                                <input type="hidden" name="amount" value="<?php echo $applicationAmount; ?>">

                                                <input type="hidden" name="campaignid" value="<?php echo $url_id; ?>">
                                                <input type="hidden" name="sid" value="0">
                                                <input type="hidden" class="applicantName" name="name" value="">
                                                <input type="hidden" class="applicantEmail" name="email" value="">
                                                <input type="hidden" class="applicantPhone" name="phone" value="">

                                                <input type="hidden" name="callbackurl" id="responseUrl" value="<?= $responseLink ?>">
                                                <input type="hidden" value="<?php echo $orgData['title']; ?>" id="organisationName" name="organisationName">
                                                <input type="hidden" value="<?php echo $orgData['logo_image']; ?>" id="organisationLogo" name="organisationLogo">

                                                <button type="submit" class="btnPay" style="display:none;" id="payAdmissionFee">Pay</button>
                                            </form>
                                        <?php } elseif ($gateway == 'AIRPAY') {
                                            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                                            //$responseLink = $base_url . "/cms/index.php?return=1";
                                            $responseLink = $base_url . "/home.php";
                                            $airpayamount = number_format($applicationAmount, 2, '.', '');
                                        ?>
                                            <form id="admissionPay" action="../thirdparty/payment/airpay/sendtoairpay.php" method="post" style="text-align:center;">
                                                <input type="hidden" name="payment_gateway_id" value="<?php echo $gatewayID; ?>">
                                                <input type="hidden" value="<?php echo $orderId; ?>" id="OrderId" name="orderid">
                                                <input type="hidden" name="amount" value="<?php echo $airpayamount; ?>">

                                                <input type="hidden" name="campaignid" value="<?php echo $url_id; ?>">
                                                <input type="hidden" name="sid" value="0">
                                                <input type="hidden" class="applicantName" name="buyerFirstName" value="">
                                                <input type="hidden" class="applicantName" name="buyerLastName" value="">
                                                <input type="hidden" class="applicantEmail" name="buyerEmail" value="">
                                                <input type="hidden" class="applicantAirPayPhone" name="buyerPhone" value="">

                                                <input type="hidden" class="buyerAddress" name="buyerAddress" value="">
                                                <input type="hidden" class="buyerCity" name="buyerCity" value="">
                                                <input type="hidden" class="buyerState" name="buyerState" value="">
                                                <input type="hidden" class="buyerPinCode" name="buyerPinCode" value="">
                                                <input type="hidden" class="buyerCountry" name="buyerCountry" value="">
                                                <input type="hidden" class="ptype" name="ptype" value="admission">

                                                <input type="hidden" name="callbackurl" id="responseUrl" value="<?= $responseLink ?>">
                                                <input type="hidden" value="<?php echo $orgData['title']; ?>" id="organisationName" name="organisationName">
                                                <input type="hidden" value="<?php echo $orgData['logo_image']; ?>" id="organisationLogo" name="organisationLogo">

                                                <button type="submit" class="btnPay" style="display:none;" id="payAdmissionFee">Pay</button>
                                            </form>
                                <?php   }
                                    }
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--
            <div class="wpb_column vc_column_container  bp-background-size-auto">
                <div class="vc_column-inner vc_custom_1539746106290">
                    <div class="wpb_wrapper">

                    </div>
                </div>
            </div>
            -->
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
        <i data-fip-value="ion-ios-arrow-thin-up" class="ion-ios-arrow-thin-up"></i>
    </div>
    <!-- Memberships powered by Paid Memberships Pro v2.0.7.
 -->


    <div id="tp_chameleon_list_google_fonts"></div>
    <a id="downloadLink" href="ajaxfile.php?cid=<?php echo $url_id; ?>" class="" style="display:none;">Download Receipts</a>



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

            document.body.style.display = "block";

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

        $(document).ready(function() {
            resetIframe();
        });


        function resetIframe() {
            try {
                setTimeout(function() {
                    iframe = document.getElementById("application_view");
                    iframe.style.height = (Number(iframe.contentWindow.document.body.scrollHeight) + 100) + 'px';
                }, 1000);
            } catch (ex) {
                resetIframe();
            }
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
                iframe.find(".dobval").remove();
                var userDate = $(this).val();
                var date_string = moment(userDate, "DD/MM/YYYY").format("MM/DD/YYYY");
                var From_date = new Date(date_string);

                var userDate2 = iframe.find("input[name=as_on_date]").val();
                var date_string2 = moment(userDate2, "DD/MM/YYYY").format("MM/DD/YYYY");
                var To_date = new Date(date_string2);

                var diff_date = To_date - From_date;


                var duration = moment.duration(diff_date, 'milliseconds');
                var totDays = duration.asDays();
                //console.log(totDays);


                // if (totDays > 1491 || totDays < 1035) {
                //     $(this).val("");
                //     alert("Kindly Note: 3 Years to be completed as on 31st May 2021");
                // }

                /*
                var ageMonths = Number(years * 12) + months;
                //console.log("years: ", years, " months: ", months, " ageMonths: ", ageMonths);
                if (ageMonths < 34 || ageMonths > 49) {
                    $(this).val("");
                    alert("Kindly Note: 3 Years to be completed as on 31st May 2021");
                }*/

                var years = Math.floor(diff_date / 31536000000);
                var months = Math.floor((diff_date % 31536000000) / 2628000000);
                var days = Math.floor(((diff_date % 31536000000) % 2628000000) / 86400000);



                var ageval = years + " years " + months + " months and " + days + " days";
                iframe.find("input[name=age_value]").val(ageval);
                // if (years < 3) {
                //     iframe.find("input[name=dob_in_words]").after('<span class="dobval" style="color:red;font-size: 15px;font-weight: 600;">Kindly Note: 3 Years to be completed as on 31st May 2021<span>');
                // }

                var dateTime = new Date(From_date);
                var month = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                var date = ['First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth', 'Seventh', 'Eighth', 'Ninth', 'Tenth', 'Eleventh', 'Twelfth', 'Thirteenth', 'Fourteenth', 'Fifteenth', 'Sixteenth', 'Seventeenth', 'Eighteenth', 'Nineteenth', 'Twentieth', 'Twenty-First', 'Twenty-Second', 'Twenty-Third', 'Twenty-Fourth', 'Twenty-Fifth', 'Twenty-Sixth', 'Twenty-Seventh', 'Twenty-Eighth', 'Twenty-Ninth', 'Thirtieth', 'Thirty-First'];
                var strDateTime = date[dateTime.getDate() - 1] + " " + month[dateTime.getMonth()] + " " + toWords(dateTime.getFullYear());
                iframe.find("input[name=dob_in_words]").val(strDateTime);
            });

            var cls = iframe.find("#class").prop('readonly', true);
            var fid = $("#fid").val();
            var allms = $("#allowms").val();
            // if(allms == '0'){
            //     iframe.find("input[name=father_email], input[name=mother_email]").change(function() {
            //         var val = $(this).val();
            //         var ths = $(this);
            //         if (val != '') {
            //             var type = 'chkPreviousSubmission';
            //             $.ajax({
            //                 url: 'ajax_data.php',
            //                 type: 'post',
            //                 data: {val: val,type: type, fid: fid},
            //                 async: true,
            //                 success: function(response) {
            //                     if(response == '1'){
            //                         alert('You are Already Applied');
            //                         iframe.find(".ff-btn-submit").prop('disabled', true);
            //                         iframe.find(".ff-btn-submit").hide();
            //                         ths.val('');
            //                     } else {
            //                         iframe.find(".ff-btn-submit").prop('disabled', false);
            //                         iframe.find(".ff-btn-submit").show();
            //                     }
            //                 }
            //             });
            //         }
            //     });
            //     iframe.find("input[name=father_mobile], input[name=mother_mobile]").change(function() {
            //         var val = '+91'+$(this).val();
            //         var ths = $(this);
            //         if (val != '') {
            //             var type = 'chkPreviousSubmission';
            //             $.ajax({
            //                 url: 'ajax_data.php',
            //                 type: 'post',
            //                 data: {val: val,type: type, fid: fid},
            //                 async: true,
            //                 success: function(response) {
            //                     if(response == '1'){
            //                         alert('You are Already Applied');
            //                         iframe.find(".ff-btn-submit").prop('disabled', true);
            //                         iframe.find(".ff-btn-submit").hide();
            //                         ths.val('');
            //                     } else {
            //                         iframe.find(".ff-btn-submit").prop('disabled', false);
            //                         iframe.find(".ff-btn-submit").show();
            //                     }
            //                 }
            //             });
            //         }
            //     });
            // }

            iframe.find("input[name=father_email]").change(function() {
                var val = $(this).val();
                if (val != '') {
                    $(".applicantEmail").val(val);
                }
            });

            iframe.find("input[name=student_name]").change(function() {
                var val = $(this).val();
                if (val != '') {
                    $(".applicantName").val(val);
                }
            });

            iframe.find("input[name='student_name[first_name]']").change(function() {
                var val = $(this).val();
                //alert(val);
                if (val != '') {
                    $(".applicantName").val(val);
                }
            });

            iframe.find("input[name=father_mobile]").change(function() {
                var val = '+91' + $(this).val();
                var aval = $(this).val();
                //alert(val);
                if (val != '') {
                    $(".applicantPhone").val(val);
                    $(".applicantAirPayPhone").val(aval);
                    
                }
            });

            iframe.find(".ff-el-form-control").change(function() {
                $.each($(this), function() {
                    chkprog = $("#chkProg").val();
                    var val = $("#class option:selected").val();
                    if (val == '') {
                        $("#class").addClass('error').focus();
                        iframe.find(".ff-btn-submit").prop('disabled', true);
                        alert('You Have to Select Class');
                        if (chkprog == '1') {
                            var pval = $("#pid option:selected").val();
                            if (pval == '') {
                                $("#pid").addClass('error').focus();
                                iframe.find(".ff-btn-submit").prop('disabled', true);
                                alert('You Have to Select Program');
                                return false;
                            } else {
                                $("#pid").removeClass('error');
                                iframe.find(".ff-btn-submit").prop('disabled', false);
                                return true;
                            }
                        }
                        return false;
                    } else {
                        $("#class").removeClass('error');
                        iframe.find(".ff-btn-submit").prop('disabled', false);
                        if (chkprog == '1') {
                            var pval = $("#pid option:selected").val();
                            if (pval == '') {
                                $("#pid").addClass('error').focus();
                                iframe.find(".ff-btn-submit").prop('disabled', true);
                                alert('You Have to Select Program');
                                return false;
                            } else {
                                $("#pid").removeClass('error');
                                iframe.find(".ff-btn-submit").prop('disabled', false);
                                return true;
                            }
                        }
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
        </script>
        <script>


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
            var chkfeeSett = $("#chkfeesett").val();
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
                            clid: clid,
                            chkfeeSett: chkfeeSett
                        },
                        async: true,
                        success: function(response) {
                            $("#progClassDiv").remove();
                            //$("#downloadLink")[0].click();
                            <?php
                                if(strpos($baseurl,"gigis")>-1){
                                   echo "location.href='".$baseurl."/contractForm.php';";
                                }else{
                            ?>
                            $("#application_view").addClass('iheight');
                            if (chkfeeSett == '2') {
                                $("#payAdmissionFee").show();
                            }
                            <?php
                                }
                            ?>
                        }
                    });
                }, 500);
            }
        }

        $(document).on('change', '#pid', function() {
            var val = $(this).val();
            var cid = $("#cid").val();
            if (val != '') {
                var type = 'getCampClass';
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: {
                        val: val,
                        type: type,
                        cid: cid
                    },
                    async: true,
                    success: function(response) {
                        $("#class").html('');
                        $("#class").html(response);
                    }
                });
            }
        });
    </script>
</body>