<?php

//include 'pupilsight.php';
include_once "cms/w2f/adminLib.php";
$adminlib = new adminlib();
$data = $adminlib->getPupilSightData();

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

$key = $_GET["key"];

$sqlp =
    'SELECT username, email, pupilsightPersonID FROM pupilsightPerson WHERE password_reset_key = "' .
    $key .
    '" ';
$rowdataprog = database::doSelectOne($sqlp);
// $resultp = $connection2->query($sqlp);
// $rowdataprog = $resultp->fetch();

if (!empty($rowdataprog)) {
    $email = $rowdataprog["email"];
    $pupilsightPersonID = $rowdataprog["pupilsightPersonID"];

    if (!empty($email)) { ?>
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
                <div class="container-tight py-6">
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
                                        Reset Password
                                    </h2>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mt-3">
                                    <label class="form-label">Password</label>
                                </div>
                                <div class="col-12">
                                    <input type="password" id="password" autocomplete="new-password">
                                    <span id="result"></span>
                                </div>

                                <div class="col-12 mt-3">
                                    <label class="form-label">Confirm Password</label>
                                </div>
                                <div class="col-12">
                                    <input type="password" id="confirmPassword" autocomplete="new-password">
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-12">
                                    <br />
                                    <button id="resetPassword" class="btn btn-primary btn-block btn-square">Submit</button>
                                </div>
                                <!-- <span style="color:red;font-size: 11px;">You Have to Select Class</span> -->
                            </div>
                            <input type="hidden" id="pid" value="<?php echo $key; ?>">
                        </div>
                    </div>
                    <!-- <span style="color:red;font-size: 11px;">You Have to Select Class</span> -->
                </div>
            </div>

            <script>
                function homepage() {
                    location.href = "home.php";
                }
            </script>

            <script type='text/javascript'>
                $(document).ready(function() {
                    $('#password').keyup(function() {
                        $('#result').html(checkStrength($('#password').val()))
                    })

                    function checkStrength(password) {
                        //initial strength 

                        var strength = 0

                        //if the password length is less than 6, return message. 
                        if (password.length < 8) {
                            $('#result').removeClass()
                            $('#result').addClass('short')
                            $("#password").addClass('week');
                            return 'Too short'
                        }

                        //length is ok, lets continue. //if length is 8 characters or more, increase strength value 

                        if (password.length > 7)
                            strength += 1

                        //if password contains both lower and uppercase characters, increase strength value 

                        if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))
                            strength += 1

                        //if it has numbers and characters, increase strength value 

                        if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/))
                            strength += 1

                        //if it has one special character, increase strength value 

                        if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/))
                            strength += 1

                        //if it has two special characters, increase strength value 

                        if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,",%,&,@,#,$,^,*,?,_,~])/)) strength += 1

                        //now we have calculated strength value, we can return messages //if value is less than 2 

                        if (strength < 2) {
                            $('#result').removeClass()
                            $('#result').addClass('weak')
                            $("#password").addClass('week');
                            return 'Weak'
                        } else if (strength == 3) {
                            $('#result').removeClass()
                            $('#result').addClass('good')
                            $("#password").removeClass('week');
                            return 'Good'
                        } else if (strength == 4) {
                            $('#result').removeClass()
                            $('#result').addClass('strong')
                            $("#password").removeClass('week');
                            return 'Strong'
                        }
                    }
                });


                $(document).on('click', '#resetPassword', function() {
                    var pass = $("#password").val();
                    var conpass = $("#confirmPassword").val();
                    var type = 'resetpassword';
                    var pid = $("#pid").val();
                    if ($("#password").hasClass('week')) {
                        alert('Your Password is Week!');
                    } else {
                        if (pass != '' && conpass != '') {
                            if (pass == conpass) {
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
                                        if (response == 'success') {
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
                        } else {
                            alert('Please Enter The Password!');
                        }
                    }
                });
            </script>
        </body>

        </html>
    <?php } else { ?>
        <script>
            alert('Invalid Token or Token Expired!');
            location.href = 'home.php';
        </script>
    <?php // header("Location: home.php");
    exit();}
} else {
     ?>
    <script>
        alert('Invalid Token or Token Expired!');
        location.href = 'home.php';
    </script>
<?php //header("Location: home.php");
exit();
}

?>
