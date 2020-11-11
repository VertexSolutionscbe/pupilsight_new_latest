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

?>
<!doctype html>
<html class="no-js" lang="">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Pupilpod</title>
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
    <link rel="stylesheet" href="<?= $baseurl; ?>/assets/css/fullcalendar.min.css?v=1.0" type="text/css" media="all" />
    <link rel="stylesheet" href="<?= $baseurl; ?>/assets/css/jquery.dataTables.min.css?v=1.0" />
    <link rel="stylesheet" href="<?= $baseurl; ?>/assets/css/bootstrap-multiselect.css?v=1.0" type="text/css" media="all" />

    <link rel="stylesheet" href="<?= $baseurl; ?>/assets/libs/jquery-timepicker/jquery.timepicker.css?v=1.0" type="text/css" media="all" />
    <link rel="stylesheet" href="<?= $baseurl; ?>/assets/libs/thickbox/thickbox.css?v=1.0" type="text/css" media="all" />
    <link rel="stylesheet" href="<?= $baseurl; ?>/assets/css/normalize.css?v=1.0" type="text/css" media="all" />

    <link href="<?= $baseurl; ?>/assets/css/selectize.css" rel="stylesheet" />
    <link href="<?= $baseurl; ?>/assets/css/tabler.css" rel="stylesheet" />
    <link href="<?= $baseurl; ?>/assets/css/dev.css" rel="stylesheet" />
    <link href="<?= $baseurl; ?>/assets/css/select2.min.css" rel="stylesheet" />

    <!-- Libs JS -->
    <script src="<?= $baseurl; ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseurl; ?>/assets/libs/jquery/dist/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/libs/jquery/jquery-migrate.min.js?v=1.0"></script>
    <script src="<?= $baseurl; ?>/assets/libs/jquery-ui/js/jquery-ui.min.js?v=1.0"></script>
    <script src="<?= $baseurl; ?>/assets/js/jquery.dataTables.min.js?v=1.0v=1.0"></script>
    <script src="<?= $baseurl; ?>/assets/libs/jquery-timepicker/jquery.timepicker.min.js?v=1.0"></script>
    <script src="<?= $baseurl; ?>/assets/libs/livevalidation/livevalidation_standalone.compressed.js"></script>


    <script src="<?= $baseurl; ?>/assets/js/core.js"></script>
    <script src="<?= $baseurl; ?>/assets/js/jquery.table2excel.js"></script>
    <script type="text/javascript">
        var tb_pathToImage = "<?= $baseurl; ?>/assets/libs/thickbox/loadingAnimation.gif";
    </script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/libs/tinymce/tinymce.min.js?v=1.0"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/libs/jquery-tokeninput/src/jquery.tokeninput.js?v=1.0"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/js/moment.min.js?v=1.0"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/js/fullcalendar.min.js?v=1.0"></script>

    <script type="text/javascript" src="<?= $baseurl; ?>/assets/js/bootstrap-multiselect.js?v=1.0"></script>
    <script src="<?= $baseurl; ?>/assets/js/selectize.min.js"></script>
    <script src="<?= $baseurl; ?>/assets/js/tabler.min.js"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/libs/thickbox/thickbox-compressed.js?v=1.0"></script>
    <script src="<?= $baseurl; ?>/assets/js/select2.js"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/js/jquery.form.js?v=1.0"></script>


    <style>
        body {
            display: none;
        }

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

        .carouselBox {
            margin: auto;
            width: 100%;
            min-height: 400px;
        }

        .owl-nav .owl-next,
        .owl-nav .owl-prev {
            position: absolute;
            top: 48%;
            transform: translateY(-50%);
            -webkit-transform: translateY(-50%);
        }

        .owl-nav .owl-next {
            right: 0;
            display: flex;
            margin-right: 2%;
            font-size: 25px !important;
        }

        .owl-nav .owl-prev {
            left: 0;
            display: flex;
            margin-left: 2%;
            font-size: 25px !important;
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
    </style>
</head>

<body id='chkCounterSession' class='antialiased'>
    <!-- Preloader Start Here -->
    <div id="preloader" style="display:none;"></div>
    <!-- Preloader End Here -->

    <div id="homePanel" class="page">
        <header class="navbar navbar-expand-md navbar-light navDesktop">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a href="<?= $baseurl; ?>/index.php" class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pr-0 pr-md-3">
                    <img src="<?= $baseurl . "/cms/images/logo.png"; ?>" alt="Pupilpod" class="navbar-brand-image">
                </a>

                <div class="navbar-collapse collapse" id="navbar-menu" style='flex: inherit !important;'>
                    <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                        <ul class="navbar-nav">
                            <?php
                            $menu = array();
                            $menu[0]["title"] = "About Us";
                            $menu[0]["link"] = "#about";
                            $menu[0]["icon"] = "mdi-information-outline";
                            $menu[0]["iconActive"] = "mdi-information";

                            $menu[1]["title"] = "Courses";
                            $menu[1]["link"] = "#courses";
                            $menu[1]["icon"] = "mdi-certificate-outline";
                            $menu[1]["iconActive"] = "mdi-certificate";

                            $menu[2]["title"] = "Events";
                            $menu[2]["link"] = "#events";
                            $menu[2]["icon"] = "mdi-calendar-check-outline";
                            $menu[3]["iconActive"] = "mdi-calendar-check";

                            $menu[3]["title"] = "Announcements";
                            $menu[3]["link"] = "#announcements";
                            $menu[3]["icon"] = "mdi-bullhorn-outline";
                            $menu[3]["iconActive"] = "mdi-bullhorn";

                            $menu[4]["title"] = "Contact us";
                            $menu[4]["link"] = "#contact";
                            $menu[4]["icon"] = "mdi-phone-in-talk-outline";
                            $menu[4]["iconActive"] = "mdi-phone-in-talk";

                            $menu[5]["title"] = "Admission";
                            $menu[5]["link"] = "#";
                            $menu[5]["icon"] = "mdi-clipboard-text-outline";
                            $menu[5]["iconActive"] = "mdi-clipboard-text";

                            $menu[6]["title"] = "Login";
                            $menu[6]["link"] = "javascript:loginPanel();";
                            $menu[6]["icon"] = "mdi-login-variant";
                            $menu[6]["iconActive"] = "mdi-login-variant";

                            $len = count($menu);
                            $i = 0;
                            while ($i < $len) {
                                if ($menu[$i]["title"] != "Admission") {
                            ?>
                                    <li class="nav-item">
                                        <a class="nav-link chkCounter" href="<?= $menu[$i]["link"]; ?>">
                                            <span class="nav-link-icon d-md-none d-lg-inline-block mdi <?= $menu[$i]["icon"] ?>"></span>
                                            <span class="nav-link-title"><?= $menu[$i]["title"]; ?></span>
                                        </a>
                                    </li>
                                <?php
                                } else {
                                ?>
                                    <li class="nav-item dropdown">

                                        <a class="nav-link dropdown-toggle" href="#navbar-admission" data-toggle="dropdown" role="button" aria-expanded="false">
                                            <span class="nav-link-icon d-md-none d-lg-inline-block mdi <?= $menu[$i]["icon"] ?>"></span>
                                            <span class="nav-link-title"><?= $menu[$i]["title"]; ?></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#">
                                                    Application List
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#">
                                                    Regsitartion Status
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                            <?php
                                }
                                $i++;
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-fluid">
            <!---Hero Page---->
            <a name="home"></a>
            <div class="row">
                <div class="col-md-6 col-sm-12 m-auto">
                    <div class='my-3' style="width:400px;margin:auto;">
                        <div class="carouselTitle">Welcome to Christ Academy</div>
                        <div class='mt-3'>
                            <div>
                                Christ Academy School aims a tradition of excellence
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="#" class="btn btn-primary btn-lg btn-square">View Our Courses</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12 m-auto">
                    <div style="min-height:315px;" class="mx-2 my-4">
                        <img src="<?= $baseurl . "/cms/images/hero.jpg"; ?>" style="border: 1px solid rgba(110,117,130,.2);border-radius: 3px;max-height:400px;" class="img-fluid" />
                    </div>
                </div>
            </div>

            <!---About us Page---->
            <div class="row bg-white" id="about">
                <div class="col-md-6 col-sm-12 m-auto">
                    <div style="max-height:400px;" class="mx-2 my-4">
                        <img src="<?= $baseurl . "/cms/images/upload/1573726080_bg-18.jpg"; ?>" class="img-fluid" style='max-height:400px;' />
                    </div>
                </div>

                <div class="col-md-6 col-sm-12 m-auto">
                    <div class='my-3' style="width:400px;margin:auto;">
                        <div class="carouselTitle">About Us</div>
                        <div class='mt-3'>
                            <div>
                                Demo Group is an established education provider and entrepreneurship incubator in India
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!---Courses---->
            <div class="row" id="courses">
                <div class="col-12 carouselTitle text-center my-5">
                    Courses
                </div>
                <?php
                $i = 0;
                $len = 4;
                while ($i < $len) {
                ?>
                    <div class="col-sm">
                        <div class="card">
                            <img src="/cms/images/upload/1573794579_collection-4-300x205.png" class="card-img-top" alt="Card top image">
                            <div class="card-body">
                                <h3 class="card-title">Computer Science</h3>
                                <p>Education University Technology</p>
                            </div>
                        </div>
                    </div>
                <?php
                    $i++;
                }
                ?>

            </div>


            <!---Announcements---->
            <div class="row bg-white" id="announcements">
                <div class="col-12 carouselTitle text-center my-5">
                    Announcements
                </div>
                <?php
                $i = 0;
                $len = 4;
                while ($i < $len) {
                ?>
                    <div class="col-sm">
                        <div class="card">
                            <img src="/cms/images/upload/1574079204_Untitled-18-450x300 (1).jpg" class="card-img-top" alt="Card top image">
                            <div class="card-body">
                                <!--
                                <h3 class="card-title">Card with top image</h3>
                                -->
                                <p>The registration for admission to Montessori, KG I and classes 2 to 9 closed. </p>
                            </div>
                        </div>
                    </div>
                <?php
                    $i++;
                }
                ?>

            </div>

            <!---Chairman Message---->
            <div class="row">
                <div class="col-md-6 col-sm-12 m-auto">
                    <div class="mx-2 my-4">
                        <center>
                            <img src="<?= $baseurl . "/cms/images/upload/1574137742_Untitled-2-426x426.jpg"; ?>" class="img-fluid main-img" />
                        </center>
                    </div>
                </div>

                <div class="col-md-6 col-sm-12 m-auto">
                    <div class='my-3' style="width:400px;margin:auto;">
                        <div class="carouselTitle">Chairman'S Message</div>
                        <div class='mt-3'>
                            <div>
                                Few things have greater importance to parents than the education of their children. They look for academic excellence, good values, and discipline for their children. Choosing the right school for them is therefore a crucial decision.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!---Events---->
            <div class="row bg-white" id="events">
                <div class="col-12 carouselTitle text-center my-5">
                    Events
                </div>
                <?php
                $i = 0;
                $len = 4;
                while ($i < $len) {
                ?>
                    <div class="col-sm">
                        <div class="card">
                            <img src="/cms/images/upload/1574059249_hm_onam19.jpg" class="card-img-top" alt="Card top image">
                            <div class="card-body">
                                <h3 class="card-title">ONAM CELEBRATIONS - 2019</h3>
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aperiam deleniti fugit incidunt, iste, itaque minima
                                    neque pariatur perferendis sed suscipit velit vitae voluptatem.</p>
                            </div>
                        </div>
                    </div>
                <?php
                    $i++;
                }
                ?>

            </div>

            <!---Contact Us---->
            <div class="row" id="contact">
                <div class="col-12 carouselTitle text-center my-5">
                    Contact Us
                </div>

                <div class="col-md-7 col-sm-12">
                    <div id="map" class='card'>
                        <iframe width="100%" height="407" border='0' id="gmap_canvas" src="https://maps.google.com/maps?q=12th%20A%20Main%20Rd%2C%20HAL%202nd%20Stage%2C%20Indiranagar%2C%20Bengaluru%2C%20Karnataka%20560008&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                    </div>
                </div>

                <div class="col-md-5 col-sm-12">

                    <div id="form" class="card">
                        <div class="card-body">
                            <form id="contactForm" class="wpcf7-form" novalidate="novalidate">
                                <div class="mb-2">
                                    <label class="form-label">Name</label>
                                    <input class="form-control" placeholder="" name="name">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Email-Address</label>
                                    <input class="form-control" placeholder="" name="email">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Message</label>
                                    <textarea class="form-control" name="message" rows="5"></textarea>
                                </div>

                                <div class="form-footer">
                                    <button class="btn btn-primary btn-block" id="submitContact">Send Your Message</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


            </div>

        </div>
        <!-- Page Area End Here -->
    </div>

    <div id="loginPanel" class="container-tight py-6 hide">

        <form action="/verify" class="card card-md needs-validation" novalidate="" method="post">
            <div class="card-body">
                <div class="text-center my-3">
                    <img src="/cms/images/logo.png" height="36" alt="">
                </div>
                <h2 class="mb-3 text-center">Login to your account</h2>
                <div class="mb-3">
                    <label class="form-label">User Name or Email Address</label>
                    <input type="text" id="em" name="em" class="form-control" autocomplete="off" required="">
                    <div class="invalid-feedback">Invalid User Name or Email Addresss</div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Password</label>
                    <input type="password" id="pass" name="pass" class="form-control" autocomplete="password" required="">
                    <div class="invalid-feedback">Invalid Password</div>
                </div>
                <div class="mb-2">
                    <div class="float-left">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input">
                            <span class="form-check-label">Remember me on this device</span>
                        </label>
                    </div>
                    <div class="float-right">
                        <span class="form-label-description">
                            <a href="forgotpassword">Lost your password?</a>
                        </span>
                    </div>
                    <div class="float-none">&nbsp;</div>
                </div>
                <div class="form-footer mb-3">
                    <div class="row">
                        <div class='col-6'><button type="submit" class="btn btn-primary btn-block btn-square">Sign in</button></div>
                        <div class='col-6'><button type="button" onclick="homePanel();" class="btn btn-secondary btn-block btn-square">Back</button></div>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <footer id="footPanel" class="footer footer-transparent mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class='col-sm'>
                    <a href="#" class="link-secondary mr-2">Home</a>
                    <a href="#" class="link-secondary mr-2">About Us</a>
                    <a href="#" class="link-secondary mr-2">Courses</a>
                    <a href="#" class="link-secondary mr-2">Events</a>
                    <a href="#" class="link-secondary mr-2">Annoucements</a>
                    <a href="#" class="link-secondary mr-2">Contact Us</a>
                    <a href="#" class="link-secondary mr-2">Admission</a>
                </div>
                <div class="col-auto align-self-end">
                    <a href="#" class="link-secondary">Powered by ParentOf</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.body.style.display = "block";
        $(document).ready(function() {
            $("#loginPanel").hide().removeClass("hide");
        });

        function loginPanel() {
            $("#homePanel, #footPanel").hide(400);
            $("#loginPanel").show(400);
        }

        function homePanel() {
            $("#loginPanel").hide(400);
            $("#homePanel, #footPanel").show(400);
        }
    </script>

</body>

</html>