<?php

//include 'pupilsight.php';
include_once 'cms/w2f/adminLib.php';
$adminlib = new adminlib();
$data = $adminlib->getPupilSightData();

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

$title = isset($data["title"]) ? ucwords($data["title"]) : "Pupilpod";


$logo = $baseurl . "/cms/images/pupilpod_logo.png";
if (isset($data['logo_image'])) {
    $logo = $baseurl . '/cms/images/logo/' . $data['logo_image'];
}

$key = $_GET['key'];

$sqlp = 'SELECT username, email, pupilsightPersonID FROM pupilsightPerson WHERE password_reset_key = "'.$key.'" ';
$rowdataprog = database::doSelectOne($sqlp);
// $resultp = $connection2->query($sqlp);
// $rowdataprog = $resultp->fetch();

if(!empty($rowdataprog)){
    $email = $rowdataprog['email'];
    $pupilsightPersonID = $rowdataprog['pupilsightPersonID'];

    if(!empty($email)){
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
                                        <div class="container" style="width:50%;padding: 50px 50px;border: 1px solid gainsboro;margin-top: 20px;border-radius: 25px;">
                                            <div class="i-am-centered" style="">

                                                    <h2 style="text-align:center;margin-bottom: 20px;">Reset Password</h2>
                                                    <div id="progClassDiv" class="row mt-12">
                                                        <div class="col-md-6 col-sm-12">
                                                            <span>Password : </span>
                                                        </div>
                                                        <div class="col-md-6 col-sm-12">
                                                            <input type="password" id="password">
                                                        </div>
                                                    </div>
                                                    <div id="progClassDiv" class="row mt-4">
                                                        <div class="col-md-6 col-sm-12">
                                                            <span>Confirm Password : </span>
                                                        </div>
                                                        <div class="col-md-6 col-sm-12">
                                                            <input type="password" id="confirmPassword">
                                                        </div>
                                                    </div>
                                                    <div id="progClassDiv" class="row mt-4">
                                                        <div class="col-md-12 col-sm-12" style="text-align:center;">
                                                            <button id="resetPassword" class="btn btn-primary" >Submit</button>
                                                        </div>
                                                        <!-- <span style="color:red;font-size: 11px;">You Have to Select Class</span> -->
                                                    </div>
                                                    <input type="hidden" id="pid" value="<?php echo $key;?>">
                                            
                                            </div>
                                            <!-- <span style="color:red;font-size: 11px;">You Have to Select Class</span> -->
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
                <script type='text/javascript'>
                    
                    $(document).on('click', '#resetPassword', function() {
                        var pass = $("#password").val();
                        var conpass = $("#confirmPassword").val();
                        var type = 'resetpassword';
                        var pid = $("#pid").val();
                        if(pass == conpass){
                            $.ajax({
                                url: 'ajax_data.php',
                                type: 'post',
                                data: {
                                    val: pass,
                                    type: type,
                                    pid: pid
                                },
                                async: true,
                                success: function(response) {
                                    if(response == 'success'){
                                        alert('Password Reset Successfully, Please Login!');
                                        location.href = 'home.php';
                                    } else {
                                        alert('Password Reset Key did not Matched!');
                                    }
                                }
                            });
                        } else {
                            alert('Password did not Match with Confirm Password!');
                        }
                    });
                </script>
            </body>
        </html>
<?php
    } else {
    ?>
        <script>
            alert('Invalid Token or Token Expired!');
            location.href = 'home.php';
        </script>
    <?php 
        // header("Location: home.php");
        exit;
    }
} else {
    ?>
        <script>
            alert('Invalid Token or Token Expired!');
            location.href = 'home.php';
        </script>
    <?php 
    //header("Location: home.php");
    exit;
}

 ?>