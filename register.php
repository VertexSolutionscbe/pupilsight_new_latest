<?php

//include 'pupilsight.php';

include_once "cms/w2f/adminLib.php";
$adminlib = new adminlib();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$data = $adminlib->getPupilSightData();
$section = $adminlib->getPupilSightSectionFrontendData();
$campaign_id = $_REQUEST["url_id"];

if ($_POST) {
    function getSalt()
    {
        $c = explode(
            " ",
            ". / a A b B c C d D e E f F g G h H i I j J k K l L m M n N o O p P q Q r R s S t T u U v V w W x X y Y z Z 0 1 2 3 4 5 6 7 8 9"
        );
        $ks = array_rand($c, 22);
        $s = "";
        foreach ($ks as $k) {
            $s .= $c[$k];
        }

        return $s;
    }

    $salt = getSalt();
    $passwordStrong = hash("sha256", $salt . $_POST["password"]);
    $input = [];
    $input["firstName"] = $_POST["name"];
    $input["preferredName"] = $_POST["name"];
    $input["officialName"] = $_POST["name"];
    $input["email"] = $_POST["email"];
    $input["username"] = $_POST["email"];
    //$input['mobile'] = $_POST['mobile'];
    //$input['campaign_id'] = $_POST['campaign_id'];
    $input["passwordStrong"] = $passwordStrong;
    $input["passwordStrongSalt"] = $salt;
    $input["canLogin"] = "Y";
    $input["pupilsightRoleIDPrimary"] = "033";
    $input["pupilsightRoleIDAll"] = "033";
    $input["phone1Type"] = "Mobile";
    $input["phone1"] = $_POST["mobile"];
    //$_SESSION['campaignuserdata'] = $input;

    //$insert = $adminlib->createCampaignRegistration($input, $_POST['campaign_id']);

    $sql =
        'SELECT count(*) AS cnt FROM pupilsightPerson WHERE email="' .
        $input["email"] .
        '" ';

    $result = database::doSelectOne($sql);
    //print_r($result);
    //echo "cnt".$result['cnt'];
    if ($result["cnt"] > 0) {
        echo "<script type='text/javascript'>alert('Email id already exists ');
		window.history.go(-1);</script>";
        exit();
    } else {
        if ($campaign_id) {
            $insert = $adminlib->createCampaignRegistration(
                $input,
                $_POST["campaign_id"]
            );
        }
        //  $URL = 'application_page.php?url_id='.$campaign_id.'&status=not';
        //$URL = "register.php?url_id=" . $campaign_id . "&reg_status=1";
        echo "<script type='text/javascript'>
        alert('Thank you for Registration. Please login and apply for admission.');
        location.href='home.php';
		</script>";
        die();
        //header("Location: {$URL}");
    }
}

function getDomain()
{
    if (isset($_SERVER["HTTPS"])) {
        $protocol =
            $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off" ? "https" : "http";
    } else {
        $protocol = "http";
    }
    //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $protocol . "://" . $_SERVER["HTTP_HOST"];
}
$baseurl = getDomain();
//$baseurl = getDomain().'/pupilsight';

$title = isset($data["title"]) ? ucwords($data["title"]) : "Pupilpod";

$logo = $baseurl . "/cms/images/pupilpod_logo.png";
if (isset($data["logo_image"])) {
    $logo = $baseurl . "/cms/images/logo/" . $data["logo_image"];
}
?>
    
        <!doctype html>
        <html class="no-js" lang="">

        <head>
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
            <meta http-equiv="X-UA-Compatible" content="ie=edge" />
            <title><?= $title ?> | Reset Password</title>
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


            <link rel="stylesheet" href="<?= $baseurl ?>/assets/css/normalize.css?v=1.0" type="text/css" media="all" />

            <link href="<?= $baseurl ?>/assets/css/tabler.css" rel="stylesheet" />
            <link href="<?= $baseurl ?>/assets/css/dev.css" rel="stylesheet" />

            <!-- Libs JS -->
            <script src="<?= $baseurl ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
            <script src="<?= $baseurl ?>/assets/libs/jquery/dist/jquery-3.5.1.min.js"></script>
            <script type="text/javascript" src="<?= $baseurl ?>/assets/libs/jquery/jquery-migrate.min.js?v=1.0"></script>


            <script src="<?= $baseurl ?>/assets/js/core.js"></script>

            <script type="text/javascript">
                var tb_pathToImage = "<?= $baseurl ?>/assets/libs/thickbox/loadingAnimation.gif";
            </script>

            <script src="<?= $baseurl ?>/assets/js/tabler.min.js"></script>
            <script type="text/javascript" src="<?= $baseurl ?>/assets/libs/thickbox/thickbox-compressed.js?v=1.0"></script>
            <script type="text/javascript" src="<?= $baseurl ?>/assets/js/jquery.form.js?v=1.0"></script>
        </head>

        <body id='chkCounterSession' class='antialiased'>
            <div id="homePanel" class="page">

                <div id="applicationList" class="container-fluid">
                    <div class="row">
                        <div class="col-12 col-sm-12 text-right mt-2">
                            <button class="btn btn-secondary" onclick="homepage();">Back</button>
                        </div>
                    </div>
                </div>
            
                <form class="needs-validation" novalidate method="post" action="">

                    <div class="container-tight py-4">
                        <div class="card card-md">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="text-center my-3" style='cursor:pointer;' onclick="homepage();">
                                            <img src="<?= $logo ?>" height="50" alt="">
                                        </div>
                                    </div>
                                    <div class="col-12 text-center">
                                        <h2>
                                            Registration
                                        </h2>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 mt-3">
                                        <label class="form-label">Name</label>
                                    </div>
                                    <div class="col-12">
                                        <input type="text" id="name" name="name" class="form-control" required>
                                        <span id="result"></span>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <label class="form-label">Email</label>
                                    </div>
                                    <div class="col-12">
                                        <input type="email" id="email"  name ="email"  class="form-control" required>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <label class="form-label">Mobile</label>
                                    </div>
                                    <div class="col-12">
                                        <input type="text" maxlength="10" id="mobile"  name ="mobile" class="form-control" required>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <label class="form-label">Password</label>
                                    </div>
                                    <div class="col-12">
                                        <input type="password" maxlength="10" id="firstpassword"  name ="password"  class="form-control" autocomplete="new-password" required>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <label class="form-label">Confirm Password</label>
                                    </div>
                                    <div class="col-12">
                                        <input type="password" maxlength="10" id="confirm_password"  name ="confpassword" class="form-control" autocomplete="new-password" required>
                                    </div>
                                    <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                                </div>
                                <div class="row mt-1">
                                    <div class="col-12">
                                        <br />
                                        <button type='submit' id="chkRegister" class="btn btn-primary btn-block btn-square">Submit</button>
                                    </div>
                                    <!-- <span style="color:red;font-size: 11px;">You Have to Select Class</span> -->
                                </div>
                            </div>
                        </div>
                        <!-- <span style="color:red;font-size: 11px;">You Have to Select Class</span> -->
                    </div>
                </form>
            </div>

            <script>
                function homepage() {
                    location.href = "home.php";
                }

                (function () {
                'use strict'

                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.querySelectorAll('.needs-validation')

                // Loop over them and prevent submission
                Array.prototype.slice.call(forms)
                  .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                      if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                      }
                      form.classList.add('was-validated')
                    }, false)
                  })
              })()
            </script>

        </body>

        </html>
        