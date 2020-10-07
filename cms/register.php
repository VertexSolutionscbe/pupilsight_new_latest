<?php
include_once 'w2f/adminLib.php';
$adminlib = new adminlib();
session_start();
$data = $adminlib->getPupilSightData();
$section = $adminlib->getPupilSightSectionFrontendData();
$campaign_id=$_REQUEST['url_id'];
if($_POST){
    function getSalt()
    {
        $c = explode(' ', '. / a A b B c C d D e E f F g G h H i I j J k K l L m M n N o O p P q Q r R s S t T u U v V w W x X y Y z Z 0 1 2 3 4 5 6 7 8 9');
        $ks = array_rand($c, 22);
        $s = '';
        foreach ($ks as $k) {
            $s .= $c[$k];
        }

        return $s;
    }

    $salt = getSalt();
    $passwordStrong = hash('sha256', $salt.$_POST['password']);
    $input = array();
    $input['firstName'] = $_POST['name'];
    $input['preferredName'] = $_POST['name'];
    $input['officialName'] = $_POST['name'];
    $input['email'] = $_POST['email'];
    $input['username'] = $_POST['email'];
    //$input['mobile'] = $_POST['mobile'];
    //$input['campaign_id'] = $_POST['campaign_id'];
    $input['passwordStrong'] = $passwordStrong;
    $input['passwordStrongSalt'] = $salt;
    $input['canLogin'] = 'Y';
    $input['pupilsightRoleIDPrimary'] = '033';
    $input['pupilsightRoleIDAll'] = '033';
    $input['phone1Type'] = 'Mobile';
    $input['phone1'] = $_POST['mobile'];
    //$_SESSION['campaignuserdata'] = $input; 
    
    //$insert = $adminlib->createCampaignRegistration($input, $_POST['campaign_id']);

     
    $sql = 'SELECT count(*) AS cnt FROM pupilsightPerson WHERE email="'.$input['email'].'" ';
              
    $result = database::doSelectOne($sql);	
    //print_r($result);
    //echo "cnt".$result['cnt'];
        if($result['cnt']>0)
        {
            echo "<script type='text/javascript'>alert('Email id already exists ');
		window.history.go(-1);

		</script>";
		exit;
        }

        else 
        {
            $insert = $adminlib->createCampaignRegistration($input, $_POST['campaign_id']);
          //  $URL = 'application_page.php?url_id='.$campaign_id.'&status=not';
          $URL = 'register.php?url_id='.$campaign_id.'&reg_status=1';
          header("Location: {$URL}");
        }

        

    
      
    
}



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
        $("#term_cndn").modal('show');
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
    </script>
    <script type='text/javascript' src='assets/plugins/learnpress-wishlist/assets/js/wishlist.js'></script>
    <script type='text/javascript' src='assets/plugins/miniorange-login-openid/includes/js/jquery.cookie.min.js'>
    </script>
    <script type='text/javascript' src='assets/plugins/miniorange-login-openid/includes/js/social_login.js'></script>
    <script type='text/javascript' src='assets/plugins/builderpress/assets/libs/slick/slick.min.js'></script>
    <script type='text/javascript' src='assets/plugins/builderpress/assets/js/builderpress.js'></script>
    <script type='text/javascript' src='assets/revslider/public/assets/js/revolution.tools.min.js'></script>
    <script type='text/javascript' src='assets/revslider/public/assets/js/rs6.min.js'></script>
    <script type='text/javascript' src='assets/js/underscore.min.js'></script>
    <script type='text/javascript'
        src='assets/plugins/learnpress-coming-soon-courses/assets/js/jquery.mb-coming-soon.min.js'></script>
    <script type='text/javascript' src='assets/plugins/learnpress-coming-soon-courses/assets/js/coming-soon-course.js'>
    </script>
    <script type='text/javascript' src='assets/plugins/learnpress/assets/js/vendor/plugins.all.min.js'></script>


    <link rel="icon" href="assets/uploads/sites/5/2018/10/favicon.png" sizes="32x32" />
    <link rel="icon" href="assets/uploads/sites/5/2018/10/favicon.png" sizes="192x192" />
    <link rel="apple-touch-icon-precomposed" href="icon/favicon.png" />
    <meta name="msapplication-TileImage" content="icon/favicon.png" />
    <script type="text/javascript">
    function setREVStartSize(a) {
        try {
            var b, c = document.getElementById(a.c).parentNode.offsetWidth;
            if (c = 0 === c || isNaN(c) ? window.innerWidth : c, a.tabw = void 0 === a.tabw ? 0 : parseInt(a.tabw), a
                .thumbw = void 0 === a.thumbw ? 0 : parseInt(a.thumbw), a.tabh = void 0 === a.tabh ? 0 : parseInt(a
                    .tabh), a.thumbh = void 0 === a.thumbh ? 0 : parseInt(a.thumbh), a.tabhide = void 0 === a.tabhide ?
                0 : parseInt(a.tabhide), a.thumbhide = void 0 === a.thumbhide ? 0 : parseInt(a.thumbhide), a.mh =
                void 0 === a.mh || "" == a.mh ? 0 : a.mh, "fullscreen" === a.layout || "fullscreen" === a.l) b = Math
                .max(a.mh, window.innerHeight);
            else {
                for (var d in a.gw = Array.isArray(a.gw) ? a.gw : [a.gw], a.rl)(void 0 === a.gw[d] || 0 === a.gw[d]) &&
                    (a.gw[d] = a.gw[d - 1]);
                for (var d in a.gh = void 0 === a.el || "" === a.el || Array.isArray(a.el) && 0 == a.el.length ? a.gh :
                        a.el, a.gh = Array.isArray(a.gh) ? a.gh : [a.gh], a.rl)(void 0 === a.gh[d] || 0 === a.gh[d]) &&
                    (a.gh[d] = a.gh[d - 1]);
                var e, f = Array(a.rl.length),
                    g = 0;
                for (var d in a.tabw = a.tabhide >= c ? 0 : a.tabw, a.thumbw = a.thumbhide >= c ? 0 : a.thumbw, a.tabh =
                        a.tabhide >= c ? 0 : a.tabh, a.thumbh = a.thumbhide >= c ? 0 : a.thumbh, a.rl) f[d] = a.rl[d] <
                    window.innerWidth ? 0 : a.rl[d];
                for (var d in e = f[0], f) e > f[d] && 0 < f[d] && (e = f[d], g = d);
                var h = c > a.gw[g] + a.tabw + a.thumbw ? 1 : (c - (a.tabw + a.thumbw)) / a.gw[g];
                b = a.gh[g] * h + (a.tabh + a.thumbh)
            }
            void 0 === window.rs_init_css && (window.rs_init_css = document.head.appendChild(document.createElement(
                    "style"))), document.getElementById(a.c).height = b, window.rs_init_css.innerHTML += "#" + a.c +
                "_wrapper { height: " + b + "px }"
        } catch (a) {
            console.log("Failure at Presize of Slider:" + a)
        }
    };
    </script>


</head>

<body
    class="home page-template page-template-templates page-template-home-page page-template-templateshome-page-php page page-id-17 wp-embed-responsive theme-ivy-school pmpro-body-has-access woocommerce-no-js bg-type-color responsive auto-login left_courses wpb-js-composer js-comp-ver-6.0.5 vc_responsive">
   
    <div id="wrapper-container" class="content-pusher creative-right bg-type-color">
        <header id="masthead"
            class="header_colr site-header affix-top header-overlay sticky-header custom-sticky has-retina-logo header_v2 transparent header_large">
            <div class="container">
                <div class="header-wrapper" style="padding-top: 17px; padding-bottom: 17px;">
                    <div class="width-logo sm-logo">
                        <img class="mobile-logo" src="<?php echo 'images/logo/' . $data['logo_image']; ?>" alt="" />
                        <img src="<?php echo 'images/logo/' . $data['logo_image']; ?>" alt="" width="221" height="64" />
                    </div>
                    <nav class="width-navigation main-navigation">
                        <ul id="primaryMenu">
                            <li id="menu-item-646"
                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home  menu-item-646 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="index.php" class="tc-menu-inner">Home</a>
                            </li>

                            <li id="menu-item-634"
                                class="menu-item menu-item-type-custom menu-item-object-custom  menu-item-634 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="index.php#aboutus" class="tc-menu-inner">About</a>
                            </li>
                            <li id="menu-item-642"
                                class="menu-item menu-item-type-post_type_archive menu-item-object-lp_course  menu-item-642 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="index.php#courses" class="tc-menu-inner">Courses</a>
                            </li>
                            <li id="menu-item-1069"
                                class="menu-item menu-item-type-post_type_archive menu-item-object-tp_event menu-item-1069 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="index.php#events" class="tc-menu-inner">Events</a></li>
                            <li id="menu-item-1069"
                                class="menu-item menu-item-type-post_type_archive menu-item-object-tp_event menu-item-1069 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="index.php#announcement" class="tc-menu-inner">Announcement</a></li>

                            <li id="menu-item-606"
                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-606 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="index.php#contactus" class="tc-menu-inner">Contact us</a></li>
								<li id="menu-item-606"
                                class="menu-item  menu-item-type-post_type menu-item-object-page menu-item-606 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="index.php#application" class="tc-menu-inner"
								data-toggle="modal" 
								
								data-target="index.php#Application"
								>Admission</a></li>
                        </ul>
                        <div class="menu-mobile-effect navbar-toggle hidden" data-effect="mobile-effect">
                            <div class="text-menu">
                                Menu </div>
                            <div class="icon-wrap">
                                <i class="ion-navicon"></i>
                            </div>
                        </div>
                    </nav>
                    <div class="menu-right">
                        <aside id="thim_layout_builder-2" class="widget widget_thim_layout_builder">
                            <div class="vc_row wpb_row vc_row-fluid bp-background-size-auto">
                                <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                                    <div class="vc_column-inner">
                                        <div class="wpb_wrapper">
                                            <div class="bp-element bp-element-login-popup    layout-1 ">
                                                <div class="login-links">
                                                    <a class="login" data-active=".box-login" data-effect="mfp-zoom-in"
                                                        title="Login" href="#bp-popup-login">Login</a>
                                                </div>
                                                <div id="bp-popup-login"
                                                    class="white-popup mfp-with-anim mfp-hide has-shortcode">
                                                    <div class="loginwrapper">
                                                        <!--                register-->
                                                        <div class="login-popup box-register">
                                                            <div class="media-content"
                                                                style="background-image: url(assets/uploads/sites/5/2018/10/event-02.jpg)">
                                                            </div>
                                                            <div class="inner-login">
                                                                <h3 class="title">
                                                                    
                                                                    <span><a href="#login" class="display-box"
                                                                            data-display=".box-login">Login</a></span>
                                                                </h3>
                                                                <div class="form-row">
                                                                    <div class="wrap-form">
                                                                        <div class="form-desc">We will need...</div>
                                                                        <form name="loginform" id="popupRegisterForm"
                                                                            action="index.html" method="post">
                                                                            <input type="hidden" id="register_security"
                                                                                name="register_security"
                                                                                value="d3c6bd19e1" /><input
                                                                                type="hidden" name="_wp_http_referer"
                                                                                value="/demo-3/" />
                                                                            <p class="login-username">
                                                                                <input required placeholder="Username"
                                                                                    type="text" name="user_login"
                                                                                    class="input" />
                                                                            </p>
                                                                            <p class="login-email">
                                                                                <input required
                                                                                    placeholder="Email Address"
                                                                                    type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" name="user_email"
                                                                                    class="input"  />
                                                                            </p>
                                                                            <p class="login-password">
                                                                                <input required placeholder="Password"
                                                                                    type="password" name="password"
                                                                                    class="input" />
                                                                            </p>
                                                                            <p class="login-password">
                                                                                <input required
                                                                                    placeholder="Confirm Password"
                                                                                    type="password"
                                                                                    name="repeat_password"
                                                                                    class="input" />
                                                                            </p>
                                                                            <p class="login-submit">
                                                                                <input type="submit" name="wp-submit"
                                                                                    id="popupRegisterSubmit"
                                                                                    class="button button-primary button-large"
                                                                                    value="Sign Up">
                                                                                <input type="hidden" name="redirect_to"
                                                                                    value="">
                                                                            </p>
                                                                            <div class="popup-message"></div>
                                                                        </form>
                                                                    </div>
                                                                    <div class="shortcode">
                                                                        <script>
                                                                        jQuery(".btn-mo").prop("disabled", false);
                                                                        </script>
                                                                        <script
                                                                            src="assets/plugins/miniorange-login-openid/includes/js/jquery.cookie.min.js">
                                                                        </script>
                                                                        <script type="text/javascript">
                                                                        function mo_openid_on_consent_change(checkbox,
                                                                            value) {
                                                                            if (value == 0) {
                                                                                jQuery('#mo_openid_consent_checkbox')
                                                                                    .val(1);
                                                                                jQuery(".btn-mo").attr("disabled",
                                                                                    true);
                                                                                jQuery(".login-button").addClass("dis");
                                                                            } else {
                                                                                jQuery('#mo_openid_consent_checkbox')
                                                                                    .val(0);
                                                                                jQuery(".btn-mo").attr("disabled",
                                                                                    false);
                                                                                jQuery(".login-button").removeClass(
                                                                                    "dis");
                                                                            }
                                                                        }

                                                                        function moOpenIdLogin(app_name,
                                                                            is_custom_app) {
                                                                            var current_url = window.location.href;
                                                                            var cookie_name = "redirect_current_url";
                                                                            var d = new Date();
                                                                            d.setTime(d.getTime() + (2 * 24 * 60 * 60 *
                                                                                1000));
                                                                            var expires = "expires=" + d.toUTCString();
                                                                            document.cookie = cookie_name + "=" +
                                                                                current_url + ";" + expires + ";path=/";
                                                                            var base_url = '';
                                                                            var request_uri = '/demo-3/';
                                                                            var http = 'http://';
                                                                            var http_host = 'ivy-school.thimpress.com';
                                                                            var default_nonce = 'a983c9d137';
                                                                            var custom_nonce = '3a897d2049';
                                                                            if (is_custom_app == 'false') {
                                                                                if (request_uri.indexOf(
                                                                                        'wp-login.php') != -1) {
                                                                                    var redirect_url = base_url +
                                                                                        '/?option=getmosociallogin&wp_nonce=' +
                                                                                        default_nonce + '&app_name=';
                                                                                } else {
                                                                                    var redirect_url = http +
                                                                                        http_host + request_uri;
                                                                                    if (redirect_url.indexOf('?') != -
                                                                                        1) {
                                                                                        redirect_url = redirect_url +
                                                                                            '&option=getmosociallogin&wp_nonce=' +
                                                                                            default_nonce +
                                                                                            '&app_name=';
                                                                                    } else {
                                                                                        redirect_url = redirect_url +
                                                                                            '?option=getmosociallogin&wp_nonce=' +
                                                                                            default_nonce +
                                                                                            '&app_name=';
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                if (request_uri.indexOf(
                                                                                        'wp-login.php') != -1) {
                                                                                    var redirect_url = base_url +
                                                                                        '/?option=oauthredirect&wp_nonce=' +
                                                                                        custom_nonce + '&app_name=';
                                                                                } else {
                                                                                    var redirect_url = http +
                                                                                        http_host + request_uri;
                                                                                    if (redirect_url.indexOf('?') != -1)
                                                                                        redirect_url = redirect_url +
                                                                                        '&option=oauthredirect&wp_nonce=' +
                                                                                        custom_nonce + '&app_name=';
                                                                                    else
                                                                                        redirect_url = redirect_url +
                                                                                        '?option=oauthredirect&wp_nonce=' +
                                                                                        custom_nonce + '&app_name=';
                                                                                }
                                                                            }
                                                                            window.location.href = redirect_url +
                                                                                app_name;
                                                                        }
                                                                        </script>
                                                                       <!-- <div class='mo-openid-app-icons'>
                                                                            <p style='color:#000000'> Connect with:</p>
                                                                            <a class=' login-button' rel='nofollow'
                                                                                onClick="moOpenIdLogin('google','false');"
                                                                                title=' Login with Google'><img
                                                                                    alt='Google'
                                                                                    style='width:35px !important;height: 35px !important;margin-left: 10px !important'
                                                                                    src='assets/plugins/miniorange-login-openid/includes/images/icons/google.png'
                                                                                    class=' login-button roundededges'></a><a
                                                                                class=' login-button' rel='nofollow'
                                                                                title=' Login with Twitter'
                                                                                onClick="moOpenIdLogin('twitter','false');"><img
                                                                                    alt='Twitter'
                                                                                    style=' width:35px !important;height: 35px !important;margin-left: 10px !important'
                                                                                    src='assets/plugins/miniorange-login-openid/includes/images/icons/twitter.png'
                                                                                    class=' login-button roundededges'></a><a
                                                                                class=' login-button' rel='nofollow'
                                                                                title=' Login with LinkedIn'
                                                                                onClick="moOpenIdLogin('linkedin','false');"><img
                                                                                    alt='LinkedIn'
                                                                                    style='width:35px !important;height: 35px !important;margin-left: 10px !important'
                                                                                    src='assets/plugins/miniorange-login-openid/includes/images/icons/linkedin.png'
                                                                                    class=' login-button roundededges'></a><a
                                                                                class=' login-button' rel='nofollow'
                                                                                Login with Instagram'
                                                                                onClick="moOpenIdLogin('instagram','false');"><img
                                                                                    alt='Instagram'
                                                                                    style='width:35px !important;height: 35px !important;margin-left: 10px !important'
                                                                                    src='assets/plugins/miniorange-login-openid/includes/images/icons/instagram.png'
                                                                                    class=' login-button roundededges'></a>
                                                                        </div> <br>
                                                                        <div style='float:left;' class='mo_image_id'>
                                                                            <a target='_blank'
                                                                                href='https://www.miniorange.com/'>
                                                                                <img alt='logo'
                                                                                    src='assets/plugins/miniorange-login-openid/includes/images/miniOrange.png'
                                                                                    class='mo_openid_image'>
                                                                            </a>
                                                                        </div>-->
                                                                        <br />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--login-->
                                                        <div class="login-popup box-login">
                                                            <div class="media-content"
                                                                style="background-image: url(assets/uploads/sites/5/2018/10/event-02.jpg)">
                                                            </div>
                                                            <div class="inner-login">
                                                                <h3 class="title">
                                                                    
                                                                    <span class="current-title">Login</span>
                                                                </h3>
                                                                <div class="form-row">
                                                                    <div class="wrap-form">
                                                                        <div class="form-desc">We will need...</div>
                                                                        <form action="../login.php?" method="post"
                                                                            autocomplete="off"
                                                                            enctype="multipart/form-data" id="loginForm"
                                                                            onsubmit="pupilsightFormSubmitted(this)">
                                                                            <p class="login-username">
                                                                                <label for="bp_login_name">Username or
                                                                                    Email Address</label>
                                                                                <input type="text" name="username"
                                                                                    id="username" class="input" value=""
                                                                                    size="20" />
                                                                            </p>
                                                                            <p class="login-password">
                                                                                <label
                                                                                    for="bp_login_pass">Password</label>
                                                                                <input type="password" name="password"
                                                                                    id="password" class="input" value=""
                                                                                    size="20" />
                                                                            </p>
                                                                            <p class="login-remember"><label><input
                                                                                        name="rememberme"
                                                                                        type="checkbox" id="rememberme"
                                                                                        value="forever" /> Remember
                                                                                    Me</label></p>
                                                                            <p class="login-submit">
                                                                                <input type="submit" name="wp-submit"
                                                                                    id="wp-submit"
                                                                                    class="button button-primary"
                                                                                    value="Sign In" />
                                                                                <input type="hidden" name="redirect_to"
                                                                                    value="" />
                                                                            </p>
                                                                            <select id="pupilsightSchoolYearID"
                                                                                name="pupilsightSchoolYearID"
                                                                                class="d-none fullWidth">
                                                                                <option value="023">2017-18</option>
                                                                                <option value="024">2018-19</option>
                                                                                <option value="025" selected="">2019-20
                                                                                </option>
                                                                                <option value="026">2020-21</option>
                                                                            </select>
                                                                        </form>
                                                                        <p class="link-bottom"><a href="#losspw"
                                                                                class="display-box"
                                                                                data-display=".box-lostpass">Lost your
                                                                                password?</a>
                                                                        </p>
                                                                    </div>
                                                                    <div class="shortcode">
                                                                        <script>
                                                                        jQuery(".btn-mo").prop("disabled", false);
                                                                        </script>
                                                                        <script
                                                                            src="assets/plugins/miniorange-login-openid/includes/js/jquery.cookie.min.js">
                                                                        </script>
                                                                        <script type="text/javascript">
                                                                        function mo_openid_on_consent_change(checkbox,
                                                                            value) {
                                                                            if (value == 0) {
                                                                                jQuery('#mo_openid_consent_checkbox')
                                                                                    .val(1);
                                                                                jQuery(".btn-mo").attr("disabled",
                                                                                    true);
                                                                                jQuery(".login-button").addClass("dis");
                                                                            } else {
                                                                                jQuery('#mo_openid_consent_checkbox')
                                                                                    .val(0);
                                                                                jQuery(".btn-mo").attr("disabled",
                                                                                    false);
                                                                                jQuery(".login-button").removeClass(
                                                                                    "dis");
                                                                            }
                                                                        }

                                                                        function moOpenIdLogin(app_name,
                                                                            is_custom_app) {
                                                                            var current_url = window.location.href;
                                                                            var cookie_name = "redirect_current_url";
                                                                            var d = new Date();
                                                                            d.setTime(d.getTime() + (2 * 24 * 60 * 60 *
                                                                                1000));
                                                                            var expires = "expires=" + d.toUTCString();
                                                                            document.cookie = cookie_name + "=" +
                                                                                current_url + ";" + expires + ";path=/";
                                                                            var base_url =
                                                                                'http://ivy-school.thimpress.com/demo-3';
                                                                            var request_uri = '/demo-3/';
                                                                            var http = 'http://';
                                                                            var http_host = 'ivy-school.thimpress.com';
                                                                            var default_nonce = 'a983c9d137';
                                                                            var custom_nonce = '3a897d2049';
                                                                            if (is_custom_app == 'false') {
                                                                                if (request_uri.indexOf(
                                                                                        'wp-login.php') != -1) {
                                                                                    var redirect_url = base_url +
                                                                                        '/?option=getmosociallogin&wp_nonce=' +
                                                                                        default_nonce + '&app_name=';
                                                                                } else {
                                                                                    var redirect_url = http +
                                                                                        http_host + request_uri;
                                                                                    if (redirect_url.indexOf('?') != -
                                                                                        1) {
                                                                                        redirect_url = redirect_url +
                                                                                            '&option=getmosociallogin&wp_nonce=' +
                                                                                            default_nonce +
                                                                                            '&app_name=';
                                                                                    } else {
                                                                                        redirect_url = redirect_url +
                                                                                            '?option=getmosociallogin&wp_nonce=' +
                                                                                            default_nonce +
                                                                                            '&app_name=';
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                if (request_uri.indexOf(
                                                                                        'wp-login.php') != -1) {
                                                                                    var redirect_url = base_url +
                                                                                        '/?option=oauthredirect&wp_nonce=' +
                                                                                        custom_nonce + '&app_name=';
                                                                                } else {
                                                                                    var redirect_url = http +
                                                                                        http_host + request_uri;
                                                                                    if (redirect_url.indexOf('?') != -1)
                                                                                        redirect_url = redirect_url +
                                                                                        '&option=oauthredirect&wp_nonce=' +
                                                                                        custom_nonce + '&app_name=';
                                                                                    else
                                                                                        redirect_url = redirect_url +
                                                                                        '?option=oauthredirect&wp_nonce=' +
                                                                                        custom_nonce + '&app_name=';
                                                                                }
                                                                            }
                                                                            window.location.href = redirect_url +
                                                                                app_name;
                                                                        }
                                                                        </script>
                                                                        <!--<div class='mo-openid-app-icons'>
                                                                            <p style='color:#000000'> Connect with:</p>
                                                                            <a class=' login-button' rel='nofollow'
                                                                                onClick="moOpenIdLogin('google','false');"
                                                                                title=' Login with Google'><img
                                                                                    alt='Google'
                                                                                    style='width:35px !important;height: 35px !important;margin-left: 10px !important'
                                                                                    src='assets/plugins/miniorange-login-openid/includes/images/icons/google.png'
                                                                                    class=' login-button roundededges'></a><a
                                                                                class=' login-button' rel='nofollow'
                                                                                title=' Login with Twitter'
                                                                                onClick="moOpenIdLogin('twitter','false');"><img
                                                                                    alt='Twitter'
                                                                                    style=' width:35px !important;height: 35px !important;margin-left: 10px !important'
                                                                                    src='assets/plugins/miniorange-login-openid/includes/images/icons/twitter.png'
                                                                                    class=' login-button roundededges'></a><a
                                                                                class=' login-button' rel='nofollow'
                                                                                title=' Login with LinkedIn'
                                                                                onClick="moOpenIdLogin('linkedin','false');"><img
                                                                                    alt='LinkedIn'
                                                                                    style='width:35px !important;height: 35px !important;margin-left: 10px !important'
                                                                                    src='assets/plugins/miniorange-login-openid/includes/images/icons/linkedin.png'
                                                                                    class=' login-button roundededges'></a><a
                                                                                class=' login-button' rel='nofollow'
                                                                                Login with Instagram'
                                                                                onClick="moOpenIdLogin('instagram','false');"><img
                                                                                    alt='Instagram'
                                                                                    style='width:35px !important;height: 35px !important;margin-left: 10px !important'
                                                                                    src='assets/plugins/miniorange-login-openid/includes/images/icons/instagram.png'
                                                                                    class=' login-button roundededges'></a>
                                                                        </div> <br>
                                                                        <div style='float:left;' class='mo_image_id'>
                                                                            <a target='_blank'
                                                                                href='https://www.miniorange.com/'>
                                                                                <img alt='logo'
                                                                                    src='assets/plugins/miniorange-login-openid/includes/images/miniOrange.png'
                                                                                    class='mo_openid_image'>
                                                                            </a>
                                                                        </div>-->
                                                                        <br />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--                lost password-->
                                                        <div class="login-popup box-lostpass">
                                                            <div class="media-content"
                                                                style="background-image: url(assets/uploads/sites/5/2018/10/event-02.jpg)">
                                                            </div>
                                                            <div class="inner-login">
                                                                <h3 class="title">
                                                                    <span class="current-title">Reset Password</span>
                                                                </h3>
                                                                <div class="form-row">
                                                                    <form name="lostpasswordform" id="lostpasswordform"
                                                                        action="index.html" method="post">
                                                                        <p class="description">Please enter your
                                                                            username or email address. You will receive
                                                                            a link to create a new password via email.
                                                                        </p>
                                                                        <p class="login-username">
                                                                            <input placeholder="Username or email"
                                                                                type="text" name="user_login"
                                                                                id="user_login_lostpass"
                                                                                class="input" />
                                                                        </p>
                                                                        <input type="hidden" name="redirect_to"
                                                                            value="http://ivy-school.thimpress.com/demo-3/account/?result=reset" />
                                                                        <p>
                                                                            <input type="submit"
                                                                                name="wp-submit-lostpass"
                                                                                id="wp-submit-lostpass"
                                                                                class="button button-primary button-large"
                                                                                value="Reset password" />
                                                                        </p>
                                                                        <p class="link-bottom">Are you a member? <a
                                                                                href="#login" class="display-box"
                                                                                data-display=".box-login">Sign in
                                                                                now</a>
                                                                        </p>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="d-none bp-element bp-element-button  align-right  shape-round icon_alignment-left  ">
                                                <a class="btn btn-primary bp-element-hover  btn-normal " href="#"
                                                    style=" color: #292929; background-color: #ffffff; padding: 0px 30px; line-height: 40px; height: 40px; font-size: 14px; font-weight: 400;"
                                                    title='Apply Now'
                                                    data-hover="color: #ffffff;background-color: #f3ae7f;">
                                                    <span class="inner-text">Apply Now</span>
                                                </a>
                                            </div>
                                            <div class="d-none bp-element bp-element-search  layout-1">
                                                <!--search posts element-->
                                                <!--    button search-->
                                                <div class="search-button"></div>
                                                <div class="search-form">
                                                    <!--        button close-->
                                                    <span class="close-form"></span>
                                                    <!--        search form-->
                                                    <form role="search" method="get" class="form-search"
                                                        action="demo-3.html">
                                                        <input type="search" class="search-field" value="" name="s"
                                                            required />
                                                        <span class="search-notice"> Hit enter to search or ESC to
                                                            close</span>
                                                    </form>
                                                    <ul class="list-search list-unstyled"></ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </header><!-- #masthead -->
        
		<nav class="visible-xs mobile-menu-container mobile-effect" itemscope
            itemtype="http://schema.org/SiteNavigationElement">
            <ul class="nav navbar-nav">
                <li
                    class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-17 current_page_item current-menu-ancestor current-menu-parent current_page_parent current_page_ancestor  menu-item-646 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                    <a href="" class="tc-menu-inner">Home</a>
                </li>
                <li
                    class="menu-item menu-item-type-custom menu-item-object-custom  menu-item-634 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                    <a href="#aboutus" class="tc-menu-inner">About</a>
                </li>
                <li
                    class="menu-item menu-item-type-post_type_archive menu-item-object-lp_course  menu-item-642 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                    <a href="#courses" class="tc-menu-inner">Courses</a>
                </li>
                <li
                    class="menu-item menu-item-type-post_type_archive menu-item-object-tp_event menu-item-1069 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                    <a href="#events" class="tc-menu-inner">Events</a></li>
                <li
                    class="menu-item menu-item-type-post_type menu-item-object-page menu-item-605 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                    <a href="#announcement" class="tc-menu-inner">Announcement</a></li>
                <li
                    class="menu-item menu-item-type-post_type menu-item-object-page menu-item-606 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                    <a href="#contactus" class="tc-menu-inner">Contact us</a></li>
            </ul>
        </nav><!-- nav.mobile-menu-container -->
        <div id="main-content">
            <div id="home-main-content" class="home-content home-page container" role="main">
                
                
                <div class="vc_row-full-width vc_clearfix" style="padding:20px;"></div>
              
                    <div class="mobile-margin-0 wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner vc_custom_1540537006055">
                            <div class="container">
                                <div
                                    class="bp-element bp-element-heading vc_custom_1542033515902  layout-1  mobile-center mobile-line-heading">
                                   
                                    <div class="container">
                                    <div class="row">
                                        
                                    <div class="col-sm-12 col-lg-12 ">
                                        <center>
                                            <?php if($_REQUEST)
                                            {
                                                $sts= isset($_REQUEST['reg_status'])?1:"";
                                                if($sts==1)
                                                {
                                            ?>
                                    <span class="success_fnt">Thank you for Registration,  <div class="login-links" style="display: inline">
                                                    <a class="login" data-active=".box-login" data-effect="mfp-zoom-in"
                                                        title="Login" href="#bp-popup-login"><span class="blinking">Click here</a></span>
                                                </div> to Login and Apply for Admissions</span>
                                <?php }} ?>                                  
                                </center>                               
                                    <br>
                                                                    </div>  
                                                                    </div>                              
                    <div class="row">
                        
                    <div class="col-sm-3 col-lg-3 ">
                                                                    </div>
                                                                   
                        <div class="col-sm-6 col-lg-6 ">
                            <form class="form_boarder" method="post" action="">
                                <center>
                                    <h5><span id="clicklogin" class="underline_click"></span> <span  id="clickreg" class="underline_click"><a  >
                                            Registration</a></span>
                                    </h5>
                                </center>
                                <hr style="background:#939191">
                                

                                <div id="register">
                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable>Name:</lable>
                                        </span>
                                        <input type='text' name='name' class='custom_input form-control' required>
                                    </div>

                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable>Email:</lable>
                                        </span>
                                        <input type='email' name='email' pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" class='custom_input form-control' required>
                                    </div>
                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable> Mobile:</lable>
                                        </span>
                                        <input type='mobile' name='mobile' class='custom_input form-control'  minlength="10" maxlength="10" required>
                                    </div>
                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable>Password:</lable>
                                        </span>
                                        <input type='password' id="firstpassword" name='password' class='custom_input form-control' required>
                                    </div>
                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable>Conf Password:</lable>
                                        </span>
                                        <input type='password' id="confirm_password" name='confpassword' class='custom_input form-control' required>
                                    </div>
                                   <input type="hidden" name="campaign_id" value="<?php echo $campaign_id;?>">
                                    <center>
                                        <button type="submit" id="chkRegister" class='loginbtn btn-primary pl-4 pr-4 pt-1 pb-1 ml-1'>Submit</button>
                                    </center>
                                </div>
                            </form>
                        </div>
                        <div class="col-sm-3 col-lg-3 ">
                                                                    </div>
                    </div>
                </div>
                                </div>
                                <div class="wpb_text_column wpb_content_element  vc_custom_1541409660821 mobile-center">
                                    <div class="wpb_wrapper">
							
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


		
        
		
		<!-- #colophon -->
    </div><!-- wrapper-container -->
    <div id="back-to-top" class="default">
        <i data-fip-value="ion-ios-arrow-thin-up" class="ion-ios-arrow-thin-up"></i> </div>
    <!-- Memberships powered by Paid Memberships Pro v2.0.7.
 -->


    <div id="tp_chameleon_list_google_fonts"></div>
    <link href="https://fonts.googleapis.com/css?family=Poppins:300%2C200%2C400" rel="stylesheet" property="stylesheet"
        type="text/css" media="all">


    <link rel='stylesheet' id='builder-press-magnific-popup-css'
        href='assets/plugins/builderpress/assets/libs/magnific-popup/magnific-popup.css' type='text/css' media='all' />
    <link rel='stylesheet' id='builderpress-element-login-popup-css'
        href='assets/plugins/builderpress/inc/elements/general/login-popup/assets/css/login-popup.css' type='text/css'
        media='all' />
    <link rel='stylesheet' id='builderpress-element-button-css'
        href='assets/plugins/builderpress/inc/elements/general/button/assets/css/button.css' type='text/css'
        media='all' />
    <link rel='stylesheet' id='builderpress-element-search-posts-css'
        href='assets/plugins/builderpress/inc/elements/general/search-posts/assets/css/search-posts.css' type='text/css'
        media='all' />
    <link rel='stylesheet' id='builderpress-element-image-box-css'
        href='assets/plugins/builderpress/inc/elements/general/image-box/assets/css/image-box.css' type='text/css'
        media='all' />
    <link rel='stylesheet' id='builderpress-element-heading-css'
        href='assets/plugins/builderpress/inc/elements/general/heading/assets/css/heading.css' type='text/css'
        media='all' />
    <link rel='stylesheet' id='builderpress-element-course-collections-css'
        href='assets/plugins/builderpress/inc/elements/learnpress-collections/course-collections/assets/css/course-collections.css'
        type='text/css' media='all' />
    <link rel='stylesheet' id='builderpress-element-counter-box-css'
        href='assets/plugins/builderpress/inc/elements/general/counter-box/assets/css/counter-box.css' type='text/css'
        media='all' />
    <link rel='stylesheet' id='builderpress-element-list-courses-css'
        href='assets/plugins/builderpress/inc/elements/learnpress/list-courses/assets/css/list-courses.css'
        type='text/css' media='all' />
    <link rel='stylesheet' id='builderpress-element-list-events-css'
        href='assets/plugins/builderpress/inc/elements/wp-events-manager/list-events/assets/css/list-events.css'
        type='text/css' media='all' />
    <link rel='stylesheet' id='builderpress-element-testimonials-css'
        href='assets/plugins/builderpress/inc/elements/general/testimonials/assets/css/testimonials.css' type='text/css'
        media='all' />
    <link rel='stylesheet' id='builderpress-element-call-to-action-css'
        href='assets/plugins/builderpress/inc/elements/general/call-to-action/assets/css/call-to-action.css'
        type='text/css' media='all' />
    <link rel='stylesheet' id='builder-press-owl-carousel-css'
        href='assets/plugins/builderpress/assets/libs/owl-carousel/owl.carousel.min.css' type='text/css' media='all' />
    <link rel='stylesheet' id='builderpress-element-brands-css'
        href='assets/plugins/builderpress/inc/elements/general/brands/assets/css/brands.css' type='text/css'
        media='all' />
    <link rel='stylesheet' id='builderpress-element-social-links-css'
        href='assets/plugins/builderpress/inc/elements/general/social-links/assets/css/social-links.css' type='text/css'
        media='all' />
    <script type='text/javascript' src='assets/js/jquery/ui/core.min.js'></script>

    <script type='text/javascript' src='assets/js/wp-util.min.js'></script>
    <script type='text/javascript' src='assets/js/backbone.min.js'></script>
    <script type='text/javascript'
        src='assets/plugins/wp-events-manager/inc/libraries/countdown/js/jquery.plugin.min.js'></script>

    <script type='text/javascript'
        src='assets/plugins/wp-events-manager/inc/libraries/countdown/js/jquery.countdown.min.js'></script>
    <script type='text/javascript'
        src='assets/plugins/wp-events-manager/inc/libraries/owl-carousel/js/owl.carousel.min.js'></script>
    <script type='text/javascript'
        src='assets/plugins/wp-events-manager/inc/libraries/magnific-popup/js/jquery.magnific-popup.min.js'></script>
    <script type='text/javascript' src='assets/plugins/wp-events-manager/assets/js/frontend/events.js'></script>

    <script type='text/javascript' src='assets/themes/ivy-school/assets/js/libs/1_tether.min.js'></script>
    <script type='text/javascript' src='assets/themes/ivy-school/assets/js/libs/bootstrap.min.js'></script>
    <script type='text/javascript'
        src='assets/plugins/js_composer/assets/lib/bower/flexslider/jquery.flexslider-min.js'></script>
    <script type='text/javascript' src='assets/themes/ivy-school/assets/js/libs/stellar.min.js'></script>
    <script type='text/javascript' src='assets/themes/ivy-school/assets/js/libs/theia-sticky-sidebar.js'></script>
    <script type='text/javascript' src='assets/js/imagesloaded.min.js'></script>
    <script type='text/javascript' src='assets/themes/ivy-school/assets/js/thim-custom.js'></script>
    <script type='text/javascript' src='assets/js/wp-embed.min.js'></script>
    <script type='text/javascript' src='ajax/libs/webfont/1-6-26/webfont.js'></script>
    <script type='text/javascript'>
    WebFont.load({
        google: {
            families: ['Poppins:300,500', 'Roboto:400']
        }
    });
    </script>
    <script type='text/javascript' src='assets/plugins/js_composer/assets/js/dist/js_composer_front.min.js'></script>
    <script type='text/javascript'
        src='assets/plugins/builderpress/assets/libs/magnific-popup/jquery.magnific-popup.min.js'></script>
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
    <script type='text/javascript'
        src='assets/plugins/builderpress/inc/elements/general/login-popup/assets/js/login-popup.js'></script>
    <script type='text/javascript'
        src='assets/plugins/builderpress/inc/elements/general/search-posts/assets/js/search-posts.js'></script>
    <script type='text/javascript'
        src='assets/plugins/builderpress/inc/elements/general/image-box/assets/js/image-box.js'></script>
    <script type='text/javascript' src='assets/plugins/builderpress/assets/libs/waypoints/jquery.waypoints.min.js'>
    </script>
    <script type='text/javascript'
        src='assets/plugins/builderpress/inc/elements/general/counter-box/assets/js/counter-box.js'></script>
    <script type='text/javascript'
        src='assets/plugins/builderpress/inc/elements/general/testimonials/assets/js/testimonials.js'></script>
    <script type='text/javascript' src='assets/plugins/builderpress/assets/libs/owl-carousel/owl.carousel.min.js'>
    </script>
    <script type='text/javascript' src='assets/plugins/builderpress/inc/elements/general/brands/assets/js/brands.js'>
    </script>

<style>
                .loginlable {
                    font-weight: 500;

				}
				.underline_click{
					border-bottom: 4px solid green;

				}

                <!-- Contact us End -->
                .loginbtn {
                    text-align: center;
                    padding: 6px;
                    width: 50%;
                    margin-bottom: 20px;
                    margin-top: 20px;
                    border: 1px solid;
                    border-radius: 5px;
                    height: 50px;
                }

                .custom_input {
                    width: 65%;

                    float: right;
                    border-radius: 4px;
                    border: 1px solid #6C6767;
                    height: 40px;

                }

                .form-group {
                    margin: 30px;
                }

                .form_boarder {
                    border: 1px solid #7B7A7A;

                    border-radius: 4px;
                    padding-top: 34px;
                    padding-bottom: 34px;

				}
				.css_clicked{
					color:green !important;
				}
				.css_notclicked{
					color:#7c7c7c!important;
				}
                </style>
                <script>
               
               

                function regsection() {
					$("#clickreg").addClass('css_clicked underline_click');
					$("#clickreg").removeClass('css_notclicked');
					$("#clicklogin").addClass('css_notclicked');
					$("#clicklogin").removeClass('css_clicked underline_click');
                    $("#register").show();
                    $("#login").hide();
                }
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
    .success_fnt
    {
        font-size: 16px;
    color: #20c020;
    font-weight: 500;
    }
    .blinking{
       
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  70% {
    opacity: 0;
  }
}
    </style>
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

        var password = document.getElementById("firstpassword");
        var confirm_password = document.getElementById("confirm_password");

        function validatePassword(){
        if(password.value != confirm_password.value) {
            confirm_password.setCustomValidity("Passwords Don't Match");
        } else {
            confirm_password.setCustomValidity('');
        }
        }

        password.onchange = validatePassword;
        confirm_password.onkeyup = validatePassword;
    }); 

    // $(document).on('click','#chkRegister', function(){
    //     var p1 = $("#firstpassword").val();
    //     var p2 = $("#confirm_password").val();
    //     alert(p1);
    //     alert(p2);
    //     if(p1 == p2){
    //         return true;
    //     } else {
    //         alert('Your Confirm Password is not Matched with Password');
    //         return false;
    //     }
    // });
    

   
    </script>


</body>