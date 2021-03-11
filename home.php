<?php
include_once 'cms/w2f/adminLib.php';
$adminlib = new adminlib();
$data = $adminlib->getPupilSightData();

//echo '<pre>';
//print_r($data);

$section = $adminlib->getPupilSightSectionFrontendData();
//print_r($section);
//die();
$campaign = $adminlib->getcampaign();
session_start();
if (isset($_SESSION["loginstatus"])) {
    header("Location: index.php");
}

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

$logo = $baseurl . "/cms/images/pupilpod_logo.png";
$hero_image = $baseurl . "/cms/images/welcome.png";
$about_us = $baseurl . "/cms/images/about_us.png";
$announcements = $baseurl . "/cms/images/announcements.png";
$chairmans_message = $baseurl . "/cms/images/chairmans_message.png";
$events = $baseurl . "/cms/images/events.png";
$courses = $baseurl . "/cms/images/courses.png";

$title = isset($data["title"]) ? ucwords($data["title"]) : "Pupilpod";
$cms_banner_title = isset($data["cms_banner_title"]) ? $data["cms_banner_title"] : "Over a decade’s legacy";
$cms_banner_short_description = isset($data["cms_banner_short_description"]) ? $data["cms_banner_short_description"] : "of bringing cutting edge technology to education.";
if (isset($data["cms_banner_image_path"]) && file_exists($data["cms_banner_image_path"])) {
    $hero_image = $data["cms_banner_image_path"];
}

$logo = $baseurl . "/cms/images/pupilpod_logo.png";
if (isset($data['logo_image'])) {
    $logo = $baseurl . '/cms/images/logo/' . $data['logo_image'];
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <link rel="stylesheet" href="//cdn.materialdesignicons.com/5.0.45/css/materialdesignicons.min.css">


    <link rel="stylesheet" href="<?= $baseurl; ?>/assets/css/normalize.css?v=1.0" type="text/css" media="all" />

    <link href="<?= $baseurl; ?>/assets/css/selectize.css" rel="stylesheet" />
    <link href="<?= $baseurl; ?>/assets/css/tabler.css" rel="stylesheet" />
    <link href="<?= $baseurl; ?>/assets/css/dev.css" rel="stylesheet" />
    <link href="<?= $baseurl; ?>/assets/css/select2.min.css" rel="stylesheet" />

    <!-- Libs JS -->
    <script src="<?= $baseurl; ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseurl; ?>/assets/libs/jquery/dist/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/libs/jquery/jquery-migrate.min.js?v=1.0"></script>


    <script src="<?= $baseurl; ?>/assets/js/core.js"></script>

    <script type="text/javascript">
        var tb_pathToImage = "<?= $baseurl; ?>/assets/libs/thickbox/loadingAnimation.gif";
    </script>

    <script type="text/javascript" src="<?= $baseurl; ?>/assets/js/bootstrap-multiselect.js?v=1.0"></script>
    <script src="<?= $baseurl; ?>/assets/js/selectize.min.js"></script>
    <script src="<?= $baseurl; ?>/assets/js/tabler.min.js"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/libs/thickbox/thickbox-compressed.js?v=1.0"></script>
    <script src="<?= $baseurl; ?>/assets/js/select2.js"></script>
    <script type="text/javascript" src="<?= $baseurl; ?>/assets/js/jquery.form.js?v=1.0"></script>

    <link rel="stylesheet" href="<?= $baseurl; ?>/assets/libs/slick/slick.css">
    <link rel="stylesheet" href="<?= $baseurl; ?>/assets/libs/slick/slick-theme.css">
    <script src="<?= $baseurl; ?>/assets/libs/slick/slick.js" type="text/javascript" charset="utf-8"></script>


    <style>
        body {
            display: none;
        }


        .carouselTitle {
            line-height: 42px;
            font-size: 42px;
            font-weight: 600;
        }

        .bannerTitle {
            line-height: 42px;
            font-size: 30px;
            font-weight: 600;
            /* font-family: "serif"; */
        }

        .bannerDes {
            font-size: 18px;
            font-family: "serif";
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
            height: 482px !important;
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

        .font20 {
            font-size: 20px !important;
        }

        .wordwrap {
            width: 90%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
    </style>
</head>

<body id='chkCounterSession' class='antialiased'>

    <!-- Preloader Start Here -->
    <div id="preloader" style="display:none;"></div>
    <!-- Preloader End Here -->

    <div id="homePanel" class="page" >
        <header class="navbar navbar-expand-md navbar-light navDesktop">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a href="<?= $baseurl; ?>/index.php" class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pr-0 pr-md-3">
                    <!-- <img src="<?= $logo; ?>" class="navbar-brand-image" title="<?= $data["logo_title"]; ?>"> -->
                    <img src="<?= $logo; ?>" width="160" height="50" title="<?= $data["logo_title"]; ?>">
                </a>

                <div class="navbar-collapse collapse" id="navbar-menu" style='flex: inherit !important;'>
                    <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                        <ul class="navbar-nav">
                            <?php
                            $menu = array();

                            $menu[0]["title"] = "Home";
                            $menu[0]["link"] = "<?=$baseurl;?>";
                            $menu[0]["icon"] = "mdi-home-outline";
                            $menu[0]["iconActive"] = "mdi-home";


                            if ($data["aboutus_status"] == 1) {
                                $menu[1]["title"] = "About Us";
                                $menu[1]["link"] = "#about";
                                $menu[1]["icon"] = "mdi-information-outline";
                                $menu[1]["iconActive"] = "mdi-information";
                            }

                            if ($data["course_status"] == 1) {
                                $menu[2]["title"] = "Courses";
                                $menu[2]["link"] = "#courses";
                                $menu[2]["icon"] = "mdi-certificate-outline";
                                $menu[2]["iconActive"] = "mdi-certificate";
                            }

                            if ($data["announcement_status"] == 1) {
                                $menu[3]["title"] = "Announcements";
                                $menu[3]["link"] = "#announcements";
                                $menu[3]["icon"] = "mdi-bullhorn-outline";
                                $menu[3]["iconActive"] = "mdi-bullhorn";
                            }

                            if ($data["events_status"] == 1) {
                                $menu[4]["title"] = "Events";
                                $menu[4]["link"] = "#events";
                                $menu[4]["icon"] = "mdi-calendar-check-outline";
                                $menu[4]["iconActive"] = "mdi-calendar-check";
                            }

                            if ($data["contact_status"] == 1) {
                                $menu[5]["title"] = "Contact us";
                                $menu[5]["link"] = "#contact";
                                $menu[5]["icon"] = "mdi-phone-in-talk-outline";
                                $menu[5]["iconActive"] = "mdi-phone-in-talk";
                            }

                            $menu[6]["title"] = "Admission";
                            $menu[6]["link"] = "#";
                            $menu[6]["icon"] = "mdi-clipboard-text-outline";
                            $menu[6]["iconActive"] = "mdi-clipboard-text";

                            $menu[7]["title"] = "Login";
                            $menu[7]["link"] = "javascript:loginPanel();";
                            $menu[7]["icon"] = "mdi-login-variant";
                            $menu[7]["iconActive"] = "mdi-login-variant";

                            //$len = count($menu);
                            //$i = 0;
                            //while ($i < $len) {
                            foreach ($menu as $m) {

                                if ($m["title"] != "Admission") {
                            ?>
                                    <li class="nav-item">
                                        <a class="nav-link chkCounter" href="<?= $m["link"]; ?>" onclick="homePanel();">
                                            <span class="nav-link-icon d-md-none d-lg-inline-block mdi <?= $m["icon"] ?>"></span>
                                            <span class="nav-link-title"><?= $m["title"]; ?></span>
                                        </a>
                                    </li>
                                <?php
                                } else {
                                ?>
                                    <li class="nav-item dropdown">

                                        <a class="nav-link dropdown-toggle" href="#navbar-admission" data-toggle="dropdown" role="button" aria-expanded="false">
                                            <span class="nav-link-icon d-md-none d-lg-inline-block mdi <?= $m["icon"] ?>"></span>
                                            <span class="nav-link-title"><?= $m["title"]; ?></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="javascript:applicationList();">
                                                    Application List
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:applicationStatus();">
                                                    Application Status
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-fluid" id="contentPanel">
            <!---Hero Page---->
            <a name="home"></a>
            <div class="row">
                <div class="col-md-8 col-sm-12 m-auto">
                    <!-- <div class='my-3' style="width:400px;margin:auto;"> -->
                    <div class='mx-6'>
                        <div class="bannerTitle"><?= trim(html_entity_decode($data['cms_banner_title'])); ?></div>
                        <div class='mt-3'>
                            <div class="bannerDes">
                                <?= trim(html_entity_decode($data['cms_banner_short_description'])); ?>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary btn-lg btn-square" onclick="loginPanel();">Login</button>
                            <!-- <a class="btn btn-primary btn-lg btn-square" href="#courses">View Course</a> -->
                        </div>

                    </div>
                </div>
                <div class="col-md-4 col-sm-12 m-auto">
                    <div style="min-height:315px;" class="mx-2 my-4">
                        <?php
                        $herocss = "";
                        if ($data['cms_banner_image']) {
                            $imgpath = $_SERVER["DOCUMENT_ROOT"] . '/cms/images/banner/' . $data['cms_banner_image'];
                            if (file_exists($imgpath)) {
                                $hero_image = 'cms/images/banner/' . $data['cms_banner_image'];
                                $herocss = "hero";
                            }
                        }
                        ?>
                        <img src="<?= $hero_image; ?>" class="img-fluid <?= $herocss; ?>" />
                    </div>
                </div>
            </div>

            <!---About us Page---->
            <?php
            if ($data["aboutus_status"] == 1) {
            ?>
                <div class="row bg-white" id="about">
                    <div class="col-md-6 col-sm-12 m-auto">
                        <div class="mx-2 my-4 py-4">
                            <center>


                                <?php if (!empty($section['6']['0']['image_path'])) { ?>
                                    <img src="<?= 'cms/images/upload/' . $section['6']['0']['image']; ?>" class="img-fluid" style='max-height:300px;' />
                                <?php } else { ?>
                                    <img src="<?= $about_us; ?>" class="img-fluid" style='max-height:300px;' />
                                <?php } ?>
                            </center>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12 m-auto">
                        <div class='my-3' style="width:400px;margin:auto;">
                            <div class="carouselTitle">
                                About Us
                                <?php  ?>
                            </div>
                            <div class='mt-3'>
                                <div>
                                    <?php
                                    /*if (!empty($section['6']['0']['title'])) {
                                        echo "<h4>" . ucwords($section['6']['0']['title']) . "</h4>";
                                    }*/

                                    if (!empty($section['6']['0']['short_description'])) {
                                        echo "<b>" . $section['6']['0']['short_description'] . "</b>";
                                    } else {
                                        echo '<b>Limitless learning, more possibilities</b>';
                                    }

                                    if (!empty($section['6']['0']['description'])) {
                                        echo "<p class='wordwrap'>" . $section['6']['0']['description'] . "</p>";
                                    } else {
                                        echo '<p class="wordwrap">High is a nationally recognized K-12 independent school situatedin the hills of Oakland, California. Our mission is to inspire a maplifelonglove of learning with a focus on scholarship. For 23 years of existence,Ed has more.</p>';
                                    }

                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            if ($data["course_status"] == 1) {
            ?>

                <!---Courses---->
                <div class="row" id="courses">
                    <div class="col-12 carouselTitle text-center my-5">
                        Courses
                    </div>
                    <section id="courseSlide" class="lazy slider" data-sizes="50vw">
                        <?php if (!empty($section['2'])) {
                            foreach ($section['2'] as $k => $sec) {
                                $cimg = $courses;
                                if ($sec['image']) {
                                    $cimg = 'cms/images/upload/' . $sec['image'];
                                }
                        ?>

                                <div class="col-sm">
                                    <a href="#courseModal" class="courseData" data-toggle="modal" data-cimg="<?= $cimg; ?>" data-title="<?= ucwords($sec['title']); ?>" data-desc="<?= $sec['short_description']; ?>">

                                        <div class="card">
                                            <img src="<?= $cimg; ?>" class="card-img-top" style='height: 200px;background-size: contain;'>
                                            <div class="card-body">
                                                <h3 class="card-title wordwrap"><?= ucwords($sec['title']); ?></h3>
                                                <p class="wordwrap"><?= $sec['short_description']; ?></p>
                                            </div>
                                        </div>

                                    </a>
                                </div>

                        <?php }
                        }  ?>
                    </section>

                </div>


                <!-- Modal Popop -->

                <div class="container">
                    <div class="modal fade" id="courseModal" role="dialog">
                        <div class="modal-dialog">

                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1></h1>
                                    <button type="button" class="clsbtn close" data-dismiss="modal">&times;</button>

                                </div>
                                <div class="modal-body">

                                    <div class="col-sm">
                                        <div class="card">
                                            <img id="cimg" src="" align="center">
                                            <div class="card-body">
                                                <h3 id="title" class="card-title"></h3>
                                                <p id="desc"></p>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                                <div class="modal-footer">
                                    <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php
            } ?>

            <div class="row mt-5">
                <div class="col-lg-12 d-flex justify-content-center">
                    <span class="carouselTitle"><?php echo $data['total_student']; ?><span> <span class="ml-2 carouselTitle"><?php echo $data['total_course']; ?><span>
                                    <h3> <span style="margin-left:10px">Students</span><span style="margin-left:40px">Courses</span> </h3>

                </div>
            </div>

            <?php
            if ($data["announcement_status"] == 1) {
            ?>

                <!---Announcements---->
                <div class="row bg-white" id="announcements">
                    <div class="col-12 carouselTitle text-center my-5">
                        Announcements
                    </div>

                    <div class="row">
                        <section id="announcementsSlide" class="lazy slider" data-sizes="50vw">
                            <?php if (!empty($section['3'])) {

                                foreach ($section['3'] as $k => $crs) {
                                    $aimg = $announcements;
                                    if ($crs['image']) {
                                        $aimg = 'cms/images/upload/' . $crs['image'];
                                    }
                            ?>

                                    <div class="col-sm">
                                        <a href="#annModal" class="annData" data-toggle="modal" data-aimg="<?= $aimg; ?>" data-title="<?= $crs['title']; ?>" data-desc="<?= $crs['short_description']; ?>">
                                            <div class="card">
                                                <img src="<?= $aimg; ?>" class="card-img-top" style='height: 200px;background-size: cover;'>
                                                <div class="card-body wordwrap" title='<?= $crs['title']; ?>'>
                                                    <?= $crs['title']; ?>
                                                </div>
                                            </div>

                                        </a>
                                    </div>


                            <?php
                                }
                            }
                            ?>
                        </section>

                    </div>
                </div>



                <!-- Modal Popop -->

                <div class="container">
                    <div class="modal fade" id="annModal" role="dialog">
                        <div class="modal-dialog">

                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1></h1>
                                    <button type="button" class="clsbtn close" data-dismiss="modal">&times;</button>

                                </div>
                                <div class="modal-body">

                                    <div class="col-sm">
                                        <div class="card">
                                            <img id="aimg" src="" align="center">
                                            <div class="card-body">
                                                <h3 id="atitle" class="card-title"></h3>
                                                <p id="adesc"></p>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                                <div class="modal-footer">
                                    <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php
            }
            if ($data["experience_status"] == 1) {
            ?>
                <!---Chairman Message---->
                <div class="row">
                    <?php if (!empty($section['4'])) {
                        $flag = true;
                        foreach ($section['4'] as $k => $exp) {
                            $cmimg = $chairmans_message;
                            if ($exp['image_path']) {
                                $cmimg = 'cms/images/upload/' . $exp['image'];
                            }
                            if ($flag) {

                    ?>
                                <div class="col-md-6 col-sm-12 m-auto">
                                    <div class="mx-2 my-4 py-4">
                                        <center>
                                            <img src="<?= $cmimg; ?>" class="rounded-circle" style='max-height:300px;' />
                                        </center>
                                    </div>
                                </div>
                            <?php
                                $flag = false;
                            }
                            ?>

                            <div class="col-md-6 col-sm-12 m-auto">
                                <!-- <div class='my-3' style="width:400px;margin:auto;"> -->
                                <div class='my-3'>
                                    <div class="carouselTitle">Chairman'S Message</div>
                                    <div class='mt-3'>
                                        <?php
                                        if ($exp['title']) {
                                            echo "<b>" . $exp['title'] . "</b>";
                                        } else {
                                            echo '<b>Learn at your own pace<b>';
                                        }

                                        if ($exp['short_description']) {
                                            echo "<p class='bannerDes'>" . $exp['short_description'] . "</p>";
                                        } else {
                                            echo '<p>Programs are available in fall, spring, and summer semesters. Many fall and spring programs offer similar shorter programs in the summer, and some may be combined for a full academic year.</p>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>



                    <?php }
                    } ?>

                </div>
            <?php
            }
            if ($data["events_status"] == 1) {
            ?>
                <!---Events---->
                <div class="row bg-white" id="events">
                    <div class="col-12 carouselTitle text-center my-5">
                        Events
                    </div>
                    <section id="eventsSlide" class="lazy slider" data-sizes="50vw">

                        <?php if (!empty($section['7'])) {
                            foreach ($section['7'] as $k => $nws) {
                                //print_r($nws);
                                $eimg = $events;
                                if ($nws['image']) {
                                    $eimg = 'cms/images/upload/' . $nws['image'];
                                }
                                $etitle = $nws['title'];
                                if ($nws['date']) {
                                    $etitle .= " " . $nws['date'];
                                }
                                if ($nws['time']) {
                                    $etitle .= " " . $nws['time'];
                                }
                        ?>

                                <div class="col-sm">
                                    <div class="card">
                                        <img src="<?= $eimg; ?>" class="card-img-top" style='height:200px;background-size:contain;'>
                                        <div class="card-body">
                                            <h3 class="card-title"><?= $etitle; ?></h3>
                                        </div>
                                    </div>
                                </div>



                        <?php }
                        }  ?>
                    </section>

                </div>
            <?php
            }
            if ($data["contact_status"] == 1) {
            ?>
                <!---Contact Us---->
                
                <div class="row" id="contact">
                    <div class="col-12 carouselTitle text-center my-5">
                        Contact Us
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div id="map" class='card'>
                            <?php if ($data['contact_map'])
                                echo html_entity_decode($data['contact_map']) ?>
                            <!--
                            <iframe width="100%" height="407" border='0' id="gmap_canvas" src="https://maps.google.com/maps?q=12th%20A%20Main%20Rd%2C%20HAL%202nd%20Stage%2C%20Indiranagar%2C%20Bengaluru%2C%20Karnataka%20560008&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                            -->
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <!-- <div class='font20' style="margin:10px"> -->
                        <div class='bannerDes' style="margin:20px">

                            <?php if ($data['address'] != '') { ?>


                                <!-- <label class="form-label font20">Address</label> -->
                                <div class="row">
                                    <div class="col-lg-1">

                                        <i class="fa fa-map-marker" style="font-size:24px;color:#206bc4" aria-hidden="true"></i>
                                    </div>
                                    <div class="col-lg-11">
                                        <?php echo $data['address']; ?>
                                    </div>
                                </div>
                                <br>
                            <?php  } ?>
                            <?php if ($data['phone'] != '') { ?>
                                <div class="row">
                                    <div class="col-lg-1">
                                        <!-- <label class="form-label font20">Contact Number</label> -->
                                        <i class="fa fa-phone" style="font-size:24px;color:#206bc4"></i>
                                    </div>
                                    <div class="col-lg-11"><?php echo $data['phone']; ?></div>
                                </div>
                                <br>

                            <?php  } ?>



                            <?php if ($data['primary_email'] != '' || $data['secondary_email'] != '') { ?>

                                <!-- <label class="form-label font20">Email</label> -->
                                <div class="row">
                                    <div class="col-lg-1">
                                        <i class="fa fa-envelope-o" style="font-size:24px;color:#206bc4"></i>
                                    </div>
                                    <div class="col-lg-11">
                                        <diV><?php echo $data['primary_email']; ?></div>
                                        <div><?php echo $data['secondary_email']; ?></div>
                                    </div>
                                </div>
                                <br>
                            <?php  } ?>

                            <?php //if ($data['fax'] != '') { 
                            ?>
                            <!-- <div>
                                     <label class="form-label font20">Fax</label> 

                                    <i class="fa fa-fax" style="font-size:24px;color:blue"></i>
                                    <?php //echo $data['fax']; 
                                    ?>
                                </div>

                                <br> -->
                            <?php  //} 
                            ?>

                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">

                        <div id="form" class="card">
                            <div class="card-body">
                                <form id="contactForm" class="wpcf7-form">
                                    <div class="mb-2">
                                        <label class="form-label">Name *</label>
                                        <input class="form-control chkempty" id="con_name" name="name" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Subject *</label>
                                        <input class="form-control chkempty" id="con_subject" name="subject" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Email *</label>
                                        <input class="form-control chkempty" id="con_email" name="email" required>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label">Message *</label>
                                        <textarea class="form-control chkempty" id="con_message" name="message" rows="5" required></textarea>
                                    </div>

                                    <div class="form-footer">
                                        <button type="submit" class="btn btn-primary btn-block" id="submitContact">Send Your Message</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


                </div>
            <?php
            }
            ?>
        </div>

        <div id="applicationList" class="container-fluid my-5 hide">
            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <div class="carouselTitle">Application List</div>


                </div>
                <div class="col-md-4 col-sm-12 text-right">
                    <button class="btn btn-secondary" onclick="homeApplicationPanel();">Back</button>
                </div>

                <div class="col-md-12">
                    <table class="table my-2">
                        <thead>
                            <tr>
                                <th>Campaign Name</th>
                                <th>Academic Year</th>
                                <th>Start Date</th>
                                <th>End date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <?php foreach ($campaign as $row) {

                            echo "<tr>";
                            echo '<td>';
                            echo $row["name"];
                            echo '</a></td>';
                            echo '<td>';
                            echo $row['academic_year'];
                            echo '</td>';

                            echo '<td>';
                            echo date('d M Y', strtotime($row['start_date']));
                            echo '</td>';
                            echo '<td>';
                            echo date('d M Y', strtotime($row['end_date']));
                            echo '</td>';
                            echo '<td>';
                            if ($row['page_for'] == '1') {
                                echo ' <a href="cms/application_page.php?url_id=' . $row['id'] . '&status=not"   class="btn btn-primary btn-lg" type="button" id="btnShow"  style="font-size: 14px;" >Apply Now</a>';
                            } else {
                                echo ' <a href="cms/register.php?url_id=' . $row['id'] . '"   class="btn btn-primary btn-lg" type="button" id="btnShow"  style="font-size: 14px;" >Register & Apply</a>';
                            }
                            echo '</td>';
                            echo '</tr>';


                        ?>
                        <?php } ?>
                    </table>
                </div>
            </div>

        </div>

        <div id="applicationStatus" class="container-fluid my-5 hide">
            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <div class="carouselTitle">Application Status</div>
                </div>
                <div class="col-md-4 col-sm-12 text-right">
                    <button class="btn btn-secondary" onclick="homeApplicationPanel();">Back</button>
                </div>

                <div class="col-md-12 m-auto">
                    <form class='chkdata my-4' action="" id="statusSubmit">
                        <div class="row">
                            <div class="col-md-3 col-sm-12">
                                <label class="form-label">Mobile / Email</label>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-3 col-sm-12">
                                <input type="text" id="txtPhone" name="txtPhone" class="form-control" required="">
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col-md-3 col-sm-12">
                                <button id="searchStatus" type="button" class="btn btn-primary" value="Send OTP">Submit</button>
                            </div>
                        </div>
                </div>
                </form>
                <div class="col-md-12">
                    <table id="app_lst_tbl" class='table my-2'>
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
                        <tbody id="app_lst_tbl_body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div id="applicationStatus" class="hide"></div>
    <!-- Page Area End Here -->
    </div>

    <div id="loginPanel" class="container-tight py-6 hide">

        <form action="<?php echo $baseurl;?>/login.php?" class="card card-md needs-validation" novalidate="" method="post" autocomplete="off">
        <!-- <form action="../login.php?" class="card card-md needs-validation" novalidate="" method="post" autocomplete="off"> -->
            <div class="card-body">
                <div class="closeX">
                    <span class="mdi mdi-close-circle" onclick="homePanel();"></span>
                </div>
                <div class="text-center my-3">
                    <img src="<?= $logo; ?>" height="36" alt="">
                </div>
                <h2 class="mb-3 text-center">Login to your account</h2>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required="">
                    <div class="invalid-feedback">Invalid User Name or Email Addresss</div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" autocomplete="password" required="">
                    <div class="invalid-feedback">Invalid Password</div>
                    <select id="pupilsightSchoolYearID" name="pupilsightSchoolYearID" class="d-none fullWidth">
                        <option value="023">2017-18</option>
                        <option value="024">2018-19</option>
                        <option value="025" selected="">2019-20</option>
                        <option value="026">2020-21</option>
                    </select>
                </div>

                <div class="form-footer mb-3">
                    <div class="row">
                        <p class="login-remember"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> Remember
                                Me</label></p>
                        <div class='col-12'><button type="submit" class="btn btn-primary btn-block btn-square">Sign in</button></div>
                        <!--
                        <div class='col-6'><button type="button" onclick="homePanel();" class="btn btn-secondary btn-block btn-square">Back</button></div>
                        -->
                    </div>
                </div>

                <div class="mt-2">
                    <!--
                    <div class="float-left">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input">
                            <span class="form-check-label">Remember me on this device</span>
                        </label>
                    </div>
                    --->
                    <div class="float-right">

                        <button class="btn btn-link" type="button">Lost your password?</button>

                    </div>
                    <div class="float-none">&nbsp;</div>
                </div>

            </div>
        </form>
    </div>


    <footer id="footPanel" class="footer footer-transparent mt-4">
        <div class="container-fluid">
            <div class="row">
                <!--
                <div class='col-sm'>
                    <a href="#" class="link-secondary mr-2">Home</a>
                    <a href="#" class="link-secondary mr-2">About Us</a>
                    <a href="#" class="link-secondary mr-2">Courses</a>
                    <a href="#" class="link-secondary mr-2">Annoucements</a>
                    <a href="#" class="link-secondary mr-2">Events</a>
                    <a href="#" class="link-secondary mr-2">Contact Us</a>
                    <a href="#" class="link-secondary mr-2">Admission</a>
                </div>
                -->
                <!-- <div class="col-auto align-self-end"> -->
                <div class="row">

                    <div class="col-md-10">
                        <a href="https://www.parentof.com/" target="_blank" class="link-secondary">Powered by ParentOf</a>
                    </div>

                    <div class="col-md-2">

                <div class="col-md-2">    
                    
                   <div style="margin-left:50px">

                            <a target="_blank" href="<?php echo $data['facebook_link']; ?>">
                                <i class="social-icon fa fa-facebook" style="font-size:24px;color:#206bc4"></i>
                            </a>&nbsp;&nbsp;

                        <?php } ?>
                        <?php if (!empty($data['twitter_link'])) { ?>
                            <a target="_blank" href="<?php echo $data['twitter_link']; ?>">
                                <i class="social-icon fa fa-twitter" style="font-size:24px;color:#206bc4"></i>
                            </a>&nbsp;&nbsp;

                        <?php } ?>
                        <?php if (!empty($data['pinterest_link'])) { ?>
                            <a target="_blank" href="<?php echo $data['pinterest_link']; ?>">
                                <i class="social-icon fa fa-pinterest-p" style="font-size:24px;color:#206bc4"></i>
                            </a>&nbsp;&nbsp;

                        <?php } ?>
                        <?php if (!empty($data['linkdlin_link'])) { ?>
                            <a target="_blank" href="<?php echo $data['linkdlin_link']; ?>">
                                <i class="social-icon fa fa-linkedin" style="font-size:24px;color:#206bc4"></i>
                            </a>
                    <?php } ?>
                    </div>
                    
                </div>

                </div>


            </div>
        </div>
    </footer>

    <div id="back-to-top" class="default">
        <a href="<?php echo $baseurl.'/home.php'?>"><i class="fa fa-caret-square-o-up" style="font-size:50px;color:#206bc4" title="Go Up"></i></a>
    </div>

    <style>
        #back-to-top {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            color: white;
            text-align: right;
        }
    </style>

    <script>
        $('.lazy').slick({
            arrows: false,
            centerMode: false,
            mobileFirst: false,
            dots: true,
            pauseOnHover: false,
            swipe: true,
            infinite: true,
            adaptiveHeight: true,
            slidesToShow: 4,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 2000,
            responsive: [{
                breakpoint: 500,
                settings: "unslick"
            }]
        });
    </script>

    <script>
        document.body.style.display = "block";
        $(document).ready(function() {
            $("#loginPanel, #applicationList, #applicationStatus").hide().removeClass("hide");
            try {
                $('.gmap_canvas a').remove();
            } catch (ex) {
                console.log(ex);
            }
        });

        function loginPanel() {
            $("#username").val("");
            $("#password").val("");
            $("#homePanel, #footPanel, #applicationList, #applicationStatus").hide(400);
            $("#loginPanel").show(400);
        }

        function applicationList() {
            $("#contentPanel, #applicationStatus").hide(400);
            $("#applicationList").show(400);
        }

        function applicationStatus() {
            $("#txtPhone").val("");
            $('#app_lst_tbl_body').html("");
            $("#contentPanel, #applicationList").hide(400);
            $("#applicationStatus").show(400);
        }

        function homeApplicationPanel() {
            $("#contentPanel").show(400);
            $("#applicationList, #applicationStatus").hide(400);
        }

        function homePanel() {
            $("#loginPanel, #applicationList, #applicationStatus").hide(400);
            $("#homePanel, #contentPanel, #footPanel").show(400);
        }
    </script>

    <script>
        $(document).on('click', '#searchStatus', function(e) {
            e.preventDefault();
            $("#spnPhoneStatus").html("").hide();
            var val = $("#txtPhone").val();
            if (val != '' && val.length >= 10) {
                // if (val != '') {
                // if (isEmail(val) == false) {
                //     alert('Email is not Valid!');
                //     return false;
                // }
                //$('#app_lst_tbl').css('display', 'block');
                $.ajax({
                    url: 'cms/getdata.php',
                    type: 'POST',
                    data: {
                        val: val
                    },
                    success: function(response) {
                        //$("#app_lst_tbl").removeClass("hide");
                        console.log(response);
                        if (response != '') {
                            //$(".chkdata").hide();
                            $('#app_lst_tbl_body').html(response);
                        } else {
                            $('#app_lst_tapp_lst_tbl_body').html("<tr><td colspan='4' style='color:red;font-size:20px;'>No Records Found !</td></tr>");
                        }
                    }
                });
            }
        });

        function isEmail(email) {
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return regex.test(email);
        }


        jQuery(document).on('click', '#submitContact', function(e) {

            e.preventDefault();
            var chk = '0';
            jQuery('.chkempty').each(function() {
                var val = jQuery(this).val();
                if (val == '') {
                    jQuery(this).addClass('chkemptycolor');
                    //chk = '0';
                    chk++;
                } else {
                    jQuery(this).removeClass('chkemptycolor');
                    //chk = '1';

                }
            });
            //if (chk == '1') {
            if (chk == '0') {

                jQuery.ajax({
                    url: "cms/ajax.php",
                    type: 'POST',
                    data: jQuery('#contactForm').serialize(),
                    success: function(data) {
                        if (data == 'done') {
                            alert("Your Message Sent Successfully.");
                            jQuery('#contactForm')[0].reset();
                        }
                    }
                });
            }
        });

        $(document).on("click", ".courseData", function() {
            var title = $(this).data('title');
            var desc = $(this).data('desc');
            var cimg = $(this).data('cimg');

            $('#title').text(title);
            $('#desc').text(desc);
            $('#cimg').attr("src", cimg);
        });

        $(document).on("click", ".annData", function() {

            var title = $(this).data('title');
            var desc = $(this).data('desc');
            var aimg = $(this).data('aimg');

            $('#atitle').text(title);
            $('#adesc').text(desc);
            $('#aimg').attr("src", aimg);
        });

        $(document).on("click", ".clsbtn", function() {
            location.reload(true);
            //$('.courseData').css('border', '0px'); 
        });
    </script>

</body>

</html>