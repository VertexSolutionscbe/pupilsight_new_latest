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
    </style>
</head>

<body id='chkCounterSession' class='antialiased'>
    <!-- Preloader Start Here -->
    <div id="preloader" style="display:none;"></div>
    <!-- Preloader End Here -->

    <div class="page">
        <header class="navbar navbar-expand-md navbar-light">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a href="<?= $baseurl; ?>/index.php" class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pr-0 pr-md-3">
                    <img src="<?= $baseurl . "/themes/Default/img/logo.png"; ?>" alt="Pupilpod" class="navbar-brand-image">
                </a>

                <div class="navbar-nav flex-row order-md-last">

                    <div class="collapse navbar-collapse" id="navbar-menu">
                        <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                            <ul class="navbar-nav">
                                <?php
                                $menu = array();
                                $menu[0]["title"] = "About Us";
                                $menu[0]["link"] = "#";
                                $menu[0]["icon"] = "mdi-information-outline";
                                $menu[0]["iconActive"] = "mdi-information";

                                $menu[1]["title"] = "Courses";
                                $menu[1]["link"] = "#";
                                $menu[1]["icon"] = "mdi-certificate-outline";
                                $menu[1]["iconActive"] = "mdi-certificate";

                                $menu[2]["title"] = "Events";
                                $menu[2]["link"] = "#";
                                $menu[2]["icon"] = "mdi-calendar-check-outline";
                                $menu[3]["iconActive"] = "mdi-calendar-check";

                                $menu[3]["title"] = "Announcement";
                                $menu[3]["link"] = "#";
                                $menu[3]["icon"] = "mdi-bullhorn-outline";
                                $menu[3]["iconActive"] = "mdi-bullhorn";

                                $menu[4]["title"] = "Contact us";
                                $menu[4]["link"] = "#";
                                $menu[4]["icon"] = "mdi-phone-in-talk-outline";
                                $menu[4]["iconActive"] = "mdi-phone-in-talk";

                                $menu[5]["title"] = "Admission";
                                $menu[5]["link"] = "#";
                                $menu[5]["icon"] = "mdi-clipboard-text-outline";
                                $menu[5]["iconActive"] = "mdi-clipboard-text";

                                $menu[6]["title"] = "Login";
                                $menu[6]["link"] = "#";
                                $menu[6]["icon"] = "mdi-login-variant";
                                $menu[6]["iconActive"] = "mdi-login-variant";

                                $len = count($menu);
                                $i = 0;
                                while ($i < $len) {
                                ?>
                                    <li class="nav-item">
                                        <a class="nav-link chkCounter" href="#">
                                            <span class="nav-link-icon d-md-none d-lg-inline-block mdi <?= $menu[$i]["icon"] ?>"></span>
                                            <span class="nav-link-title"><?= $menu[$i]["title"]; ?></span>
                                        </a>
                                    </li>
                                <?php
                                    $i++;
                                }
                                ?>
                            </ul>
                        </div>
                    </div>

                </div>


            </div>
        </header>



        <!-- Page Area End Here -->
    </div>


    <footer class="footer footer-transparent">
        <div class="container">
            <div class="row text-center align-items-center flex-row-reverse">
                <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                    Copyright © 2020
                    <a href="." class="link-secondary">Pupilpod</a>.
                    All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.body.style.display = "block";
    </script>

</body>

</html>