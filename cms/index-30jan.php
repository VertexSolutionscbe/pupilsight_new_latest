<?php
include_once 'w2f/adminLib.php';
$adminlib = new adminlib();
$data = $adminlib->getPupilSightData();
$section = $adminlib->getPupilSightSectionFrontendData();
$campaign=$adminlib->getcampaign();
// $app_status = $adminlib->getApp_statusData();
//  print_r($campaign);die();
/*$status= 1;


$data_target = $status==1 ? "#Application" : "#Login-reg"; */

// echo '<pre>';
// print_r($data);
// echo '</pre>';
// die();
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

.dropdown-content {
  display: none;
  position: absolute;
  z-index: 1;
}
.dropdown-content li
{
background-color: #557ff7;
    width: auto;
    padding: 5px 3px 5px 8px;
}


.dropdown:hover .dropdown-content {display: block;    margin-left: 10px;}
.sel_mbcode
{
	height: 40px;
    border: 1px solid #afadad;
    border-radius: 4px;
    margin-left: 29px;
	margin-top: 10px;
	width: 111px;
}
</style>

    <script>
	//sendOTP
	$('#sendotp').click(function(){
//alert("xfxcv");
   // var book_id = $(this).parent().data('id');
/*
    $.ajax
    ({ 
        url: 'reservebook.php',
        data: {"bookID": book_id},
        type: 'post',
        success: function(result)
        {
            $('.modal-box').text(result).fadeIn(700, function() 
            {
                setTimeout(function() 
                {
                    $('.modal-box').fadeOut();
                }, 2000);
            });
        }
    });*/
});
	


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
    <div id="thim-preloading">
        <div class="sk-wave">
            <div class="sk-rect sk-rect1"></div>
            <div class="sk-rect sk-rect2"></div>
            <div class="sk-rect sk-rect3"></div>
            <div class="sk-rect sk-rect4"></div>
            <div class="sk-rect sk-rect5"></div>
        </div>
    </div>
    <div id="wrapper-container" class="content-pusher creative-right bg-type-color">
        <header id="masthead"
            class="site-header affix-top header-overlay sticky-header custom-sticky has-retina-logo header_v2 transparent header_large">
            <div class="container">
                <div class="header-wrapper">
                    <div class="width-logo sm-logo">
                        <img class="mobile-logo" src="<?php echo 'images/logo/' . $data['logo_image']; ?>" alt="" />
                        <img src="<?php echo 'images/logo/' . $data['logo_image']; ?>" alt="" width="221" height="64" />
                    </div>
                    <nav class="width-navigation main-navigation">
                        <ul id="primaryMenu">
                            <li id="menu-item-646"
                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-17 current_page_item current-menu-ancestor current-menu-parent current_page_parent current_page_ancestor  menu-item-646 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="" class="tc-menu-inner">Home</a>
                            </li>

                            <li id="menu-item-634"
                                class="menu-item menu-item-type-custom menu-item-object-custom  menu-item-634 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="#aboutus" class="tc-menu-inner">About</a>
                            </li>
                            <li id="menu-item-642"
                                class="menu-item menu-item-type-post_type_archive menu-item-object-lp_course  menu-item-642 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="#courses" class="tc-menu-inner">Courses</a>
                            </li>
                            <li id="menu-item-1069"
                                class="menu-item menu-item-type-post_type_archive menu-item-object-tp_event menu-item-1069 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="#admission" class="tc-menu-inner">Admission</a></li>
                            <li id="menu-item-1069"
                                class="menu-item menu-item-type-post_type_archive menu-item-object-tp_event menu-item-1069 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="#events" class="tc-menu-inner">Events</a></li>
                            <li id="menu-item-1069"
                                class="menu-item menu-item-type-post_type_archive menu-item-object-tp_event menu-item-1069 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="#announcement" class="tc-menu-inner">Announcement</a></li>
<?php if(!empty($campaign)){?>
                            <li id="menu-item-606"
                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-606 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="#contactus" class="tc-menu-inner">Contact us</a></li>
								<li id="menu-item-606"
                                class=" dropdown menu-item show_list menu-item-type-post_type menu-item-object-page menu-item-606 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                <a href="#" class="tc-menu-inner"
								
								>Admission</a>
								<ul  class="dropdown-content" >
					<li id="menu-item-606" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-606 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default"><a href="#application" class="tc-menu-inner"
								data-toggle="modal" 
								
								data-target="#Application">Application</a>
													</li>
									<li id="menu-item-606" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-606 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default"><a href="#app_status" class="tc-menu-inner"
								data-toggle="modal" 
								
								data-target="#app_status">Application Status</a>
									</li>
<?php } ?> 
    
</ul>
								
								</li>
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
                                                                    <span class="current-title">Register</span>
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
                                                                                    type="email" name="user_email"
                                                                                    class="input" />
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
                                                                        <div class='mo-openid-app-icons'>
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
                                                                        </div>
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
                                                                    <span><a href="#register" class="display-box"
                                                                            data-display=".box-register">Register</a></span>
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
                                                                        <div class='mo-openid-app-icons'>
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
                                                                        </div>
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
                <div class="vc_row wpb_row vc_row-fluid bp-background-size-auto">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div class="wpb_revslider_element wpb_content_element">
                                    <!-- START Home Slider REVOLUTION SLIDER 6.1.0 -->
                                    <p class="rs-p-wp-fix"></p>
                                    <rs-module-wrap id="rev_slider_2_1_wrapper" data-source="gallery"
                                        style="background:transparent;padding:0;">
                                        <rs-module id="rev_slider_2_1" style="display:none;" data-version="6.1.0">
                                            <rs-slides>
                                                <rs-slide data-key="rs-2" data-title="Slide"
                                                    data-anim="ei:d;eo:d;s:600;r:0;t:fade;sl:d;">
                                                    <img src="assets/revslider/public/assets/assets/transparent.png"
                                                        title="Home"
                                                        data-bg="c:linear-gradient(90deg, rgba(78,88,178,1) 0%, rgba(80,98,217,1) 100%);"
                                                        class="rev-slidebg" data-no-retina>
                                                    <!--
							-->
                                                    <rs-layer id="slider-2-slide-2-layer-5"
                                                        class="tp-shape tp-shapewrapper" data-type="shape"
                                                        data-rsp_ch="on" data-xy="x:r;xo:-505px;yo:-200px;"
                                                        data-text="fw:100;a:inherit;" data-dim="w:825px;h:825px;"
                                                        data-border="bor:50%,50%,50%,50%;"
                                                        data-frame_0="x:100%;o:1;tp:600;" data-frame_0_mask="u:t;"
                                                        data-frame_1="tp:600;st:500;sp:1500;sR:500;"
                                                        data-frame_1_mask="u:t;"
                                                        data-frame_999="st:w;sp:1000;auto:true;"
                                                        data-frame_999_mask="u:t;"
                                                        style="z-index:5;background:linear-gradient(90deg, rgba(77,88,177,1) 0%, rgba(80,98,218,1) 100%);">
                                                    </rs-layer>
                                                    <!--
							-->
                                                    <rs-layer id="slider-2-slide-2-layer-3" data-type="image"
                                                        data-rsp_ch="on" data-xy="x:c;y:b;" data-text="l:22;a:inherit;"
                                                        data-dim="w:['1920px','1920px','1920px','1920px'];h:['1124px','1124px','1124px','1124px'];"
                                                        data-basealign="slide" data-frame_0="y:100%;tp:600;"
                                                        data-frame_0_mask="u:t;y:100%;"
                                                        data-frame_1="tp:600;st:300;sp:1390;sR:300;"
                                                        data-frame_1_mask="u:t;"
                                                        data-frame_999="st:w;sp:1000;auto:true;"
                                                        data-frame_999_mask="u:t;" style="z-index:6;"><img
                                                            src="assets/uploads/sites/5/2018/12/bg-slider-03.png"
                                                            width="1920" height="1124" data-no-retina>
                                                    </rs-layer>
                                                    <!--
							-->
                                                    <rs-layer id="slider-2-slide-2-layer-1" data-type="text"
                                                        data-rsp_ch="on"
                                                        data-xy="x:l,l,c,c;y:m;yo:-175px,-160px,-130px,-130px;"
                                                        data-text="s:65,50,65,55;l:75,60,75,65;ls:0px,0,0,0;fw:300;a:inherit,inherit,center,center;"
                                                        data-dim="h:160px,auto,160px,140px;"
                                                        data-frame_0="y:100%;tp:600;" data-frame_0_mask="u:t;y:100%;"
                                                        data-frame_1="tp:600;st:900;sp:1500;sR:900;"
                                                        data-frame_1_mask="u:t;"
                                                        data-frame_999="st:w;sp:1000;auto:true;"
                                                        data-frame_999_mask="u:t;"
                                                        style="z-index:7;font-family:Poppins;">
                                                        <?php echo $data['cms_banner_title']; ?>
                                                    </rs-layer>
                                                    <!--
							-->
                                                    <rs-layer id="slider-2-slide-2-layer-2" data-type="image"
                                                        data-rsp_ch="on" data-xy="x:r;xo:-75px;y:m;yo:-50px,-30px,0,0;"
                                                        data-text="l:22;a:inherit;"
                                                        data-dim="w:791px,598px,791px,791px;h:555px,420px,555px,555px;"
                                                        data-vbility="t,t,f,f" data-frame_0="y:100%;tp:600;"
                                                        data-frame_0_mask="u:t;y:100%;"
                                                        data-frame_1="tp:600;e:Power2.easeInOut;st:500;sp:1500;sR:500;"
                                                        data-frame_1_mask="u:t;"
                                                        data-frame_999="st:w;sp:1000;auto:true;"
                                                        data-frame_999_mask="u:t;" style="z-index:8;"><img
                                                            src="<?php echo 'images/banner/' . $data['cms_banner_image']; ?>"
                                                            width="791" height="555" data-no-retina>
                                                    </rs-layer>
                                                    <!--
							-->
                                                    <rs-layer id="slider-2-slide-2-layer-6" data-type="text"
                                                        data-rsp_ch="on" data-xy="x:l,l,c,c;y:m;yo:-45px,-55px,0,0;"
                                                        data-text="s:18,18,18,14;l:30;fw:200,200,200,400;a:inherit,inherit,center,center;"
                                                        data-frame_0="y:100%;tp:600;" data-frame_0_mask="u:t;y:100%;"
                                                        data-frame_1="tp:600;st:900;sp:1500;sR:900;"
                                                        data-frame_1_mask="u:t;"
                                                        data-frame_999="st:w;sp:1000;auto:true;"
                                                        data-frame_999_mask="u:t;"
                                                        style="z-index:9;font-family:Poppins;">
                                                        <?php echo $data['cms_banner_short_description']; ?>
                                                    </rs-layer>
                                                    <!--
							--><a href="#courses">
                                                        <rs-layer id="slider-2-slide-2-layer-7" class="rev-btn"
                                                            data-type="button" data-color="rgba(255,255,255,1)"
                                                            data-xy="x:l,l,c,c;y:m;yo:60px,40px,100px,100px;"
                                                            data-text="s:16,16,16,14;l:48,48,48,40;ls:0px,0,0,0;a:inherit;"
                                                            data-rsp_bd="off"
                                                            data-padding="r:42,42,42,35;l:42,42,42,35;"
                                                            data-border="bos:solid;boc:#ffffff;bow:1px,1px,1px,1px;bor:30px,30px,30px,30px;"
                                                            data-frame_0="x:100%;o:1;tp:600;" data-frame_0_mask="u:t;"
                                                            data-frame_1="tp:600;st:900;sp:1500;sR:900;"
                                                            data-frame_1_mask="u:t;"
                                                            data-frame_999="st:w;sp:1000;auto:true;"
                                                            data-frame_999_mask="u:t;"
                                                            data-frame_hover="bgc:#f8c76c;boc:#f8c76c;bor:30px,30px,30px,30px;bos:solid;bow:1px,1px,1px,1px;oX:50;oY:50;sp:0;"
                                                            style="z-index:10;background-color:rgba(0,0,0,0);font-family:Poppins;cursor:pointer;outline:none;box-shadow:none;box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;">
                                                            VIEW OUR COURSES
                                                        </rs-layer>
                                                    </a>
                                                    <!--
							-->
                                                    <rs-layer id="slider-2-slide-2-layer-8" class="revo-image-effect"
                                                        data-type="image" data-rsp_ch="on"
                                                        data-xy="x:c;xo:-420px,-420px,-200px,-200px;y:b;yo:-19px,-19px,-200px,-200px;"
                                                        data-text="l:22;a:inherit;"
                                                        data-dim="w:['248px','248px','248px','248px'];h:['238px','238px','238px','238px'];"
                                                        data-vbility="t,t,t,f" data-frame_0="y:100%;tp:600;"
                                                        data-frame_0_mask="u:t;y:100%;"
                                                        data-frame_1="tp:600;e:Power2.easeInOut;st:500;sp:2000;sR:500;"
                                                        data-frame_1_mask="u:t;"
                                                        data-frame_999="st:w;sp:1000;auto:true;"
                                                        data-frame_999_mask="u:t;" style="z-index:11;"><img
                                                            src="assets/uploads/sites/5/2018/12/cycle-1-03.png"
                                                            width="248" height="238" data-no-retina>
                                                    </rs-layer>
                                                    <!--
							-->
                                                    <rs-layer id="slider-2-slide-2-layer-9" class="revo-image-effect"
                                                        data-type="image" data-rsp_ch="on"
                                                        data-xy="x:c;xo:480px,480px,300px,300px;y:b;yo:-40px,-90px,-300px,-300px;"
                                                        data-text="l:22;a:inherit;"
                                                        data-dim="w:['211px','211px','211px','211px'];h:['201px','201px','201px','201px'];"
                                                        data-vbility="t,t,t,f" data-frame_0="y:100%;tp:600;"
                                                        data-frame_0_mask="u:t;y:100%;"
                                                        data-frame_1="tp:600;e:Power2.easeInOut;st:570;sp:2000;sR:570;"
                                                        data-frame_1_mask="u:t;"
                                                        data-frame_999="st:w;sp:1000;auto:true;"
                                                        data-frame_999_mask="u:t;" style="z-index:12;"><img
                                                            src="assets/uploads/sites/5/2018/12/cycle-2-03.png"
                                                            width="211" height="201" data-no-retina>
                                                    </rs-layer>
                                                    <!--
							-->
                                                    <rs-layer id="slider-2-slide-2-layer-10" data-type="image"
                                                        data-rsp_ch="on"
                                                        data-xy="x:r;xo:-250px,-250px,-250px,0;y:b;yo:118px,118px,118px,-60px;"
                                                        data-text="l:22;a:inherit;"
                                                        data-dim="w:['156px','156px','156px','156px'];h:['153px','153px','153px','153px'];"
                                                        data-frame_0="y:bottom;sX:2;sY:2;o:1;rZ:90deg;tp:600;"
                                                        data-frame_1="tp:600;st:650;sp:2000;sR:650;"
                                                        data-frame_999="st:w;sp:1000;auto:true;" style="z-index:13;">
                                                        <img src="assets/uploads/sites/5/2018/12/cycle-3-03.png"
                                                            width="156" height="153" data-no-retina>
                                                    </rs-layer>
                                                    <!--
-->
                                                </rs-slide>
                                            </rs-slides>
                                            <rs-progress class="rs-bottom" style="visibility: hidden !important;">
                                            </rs-progress>
                                        </rs-module>
                                        <script type="text/javascript">
                                        setREVStartSize({
                                            c: 'rev_slider_2_1',
                                            rl: [1920, 1400, 1025, 767],
                                            el: [],
                                            gw: [1330, 1110, 768, 480],
                                            gh: [868, 650, 400, 600],
                                            layout: 'fullscreen',
                                            offsetContainer: '',
                                            offset: '',
                                            mh: "800px"
                                        });
                                        var revapi2,
                                            tpj;
                                        jQuery(function() {
                                            tpj = jQuery;
                                            if (tpj("#rev_slider_2_1").revolution == undefined) {
                                                revslider_showDoubleJqueryError("#rev_slider_2_1");
                                            } else {
                                                revapi2 = tpj("#rev_slider_2_1").show().revolution({
                                                    jsFileLocation: "//ivy-school.thimpress.com/assets/plugins/revslider/public/assets/js/",
                                                    sliderLayout: "fullscreen",
                                                    visibilityLevels: "1920,1400,1025,767",
                                                    gridwidth: "1330,1110,768,480",
                                                    gridheight: "868,650,400,600",
                                                    minHeight: "800px",
                                                    spinner: "spinner0",
                                                    responsiveLevels: "1920,1400,1025,767",
                                                    disableProgressBar: "on",
                                                    navigation: {
                                                        onHoverStop: false
                                                    },
                                                    fallbacks: {
                                                        allowHTML5AutoPlayOnAndroid: true
                                                    },
                                                });
                                            }
                                        });
                                        </script>
                                    </rs-module-wrap>
                                    <!-- END REVOLUTION SLIDER -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vc_row-full-width vc_clearfix"></div>

                <?php if ($data['aboutus_status'] == '1') { ?>
                <div class="vc_row wpb_row vc_row-fluid bp-background-size-auto vc_row-o-equal-height vc_row-o-content-middle vc_row-flex"
                    id="aboutus">
                    <div class="mobile-margin-0 wpb_column vc_column_container vc_col-sm-6 bp-background-size-auto">
                        <div class="vc_column-inner vc_custom_1540537006055">
                            <div class="wpb_wrapper">
                                <div
                                    class="bp-element bp-element-heading vc_custom_1542033515902 align-right layout-1  mobile-center mobile-line-heading">
                                    <h3 class="title"
                                        style="max-width:501px; color:#7c7c7c; line-height:1.625; font-size:16px; font-weight:300; text-transform:none; margin:0 0 7px auto; ">
                                        <?php if (!empty($section['6']['0']['title'])) {
													echo $section['6']['0']['title'];
												} else {
													echo 'PupilSight';
												} ?> </h3>
                                    <span class="sub-title"
                                        style="max-width:501px; color:#292929; line-height:1.25; font-size:35px; font-weight:400; text-transform:none; margin:0 0 0px auto; ">
                                        <?php if (!empty($section['6']['0']['short_description'])) {
													echo $section['6']['0']['short_description'];
												} else {
													echo 'Limitless learning, more possibilities';
												} ?>

                                    </span>
                                    <div class="line"
                                        style="height:2px; width:87px; background-color:#e1e1e1; margin:0 -20px 0 0; ">
                                    </div>
                                </div>
                                <div class="wpb_text_column wpb_content_element  vc_custom_1541409660821 mobile-center">
                                    <div class="wpb_wrapper">
                                        <p style="text-align: right;">
                                            <?php if (!empty($section['6']['0']['description'])) {
														echo $section['6']['0']['description'];
													} else {
														echo 'High is a nationally recognized K-12 independent school situatedin the hills of Oakland, California. Our mission is to inspire a maplifelonglove of learning with a focus on scholarship. For 23 years of existence,Ed hasmore.';
													} ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wpb_column vc_column_container vc_col-sm-6 bp-background-size-auto">
                        <div class="vc_column-inner vc_custom_1539746106290">
                            <div class="wpb_wrapper">
                                <div class="wpb_single_image wpb_content_element vc_align_right">
                                    <figure class="wpb_wrapper vc_figure">
                                        <?php if (!empty($section['6']['0']['image_path'])) { ?>
                                        <div class="vc_single_image-wrapper   vc_box_border_grey "><img width="685"
                                                height="670"
                                                src="<?php echo 'images/upload/' . $section['6']['0']['image']; ?>"
                                                class="vc_single_image-img attachment-full" alt="" srcset=""
                                                sizes="(max-width: 685px) 100vw, 685px" /></div>
                                        <?php } else { ?>
                                        <div class="vc_single_image-wrapper   vc_box_border_grey"><img width="685"
                                                height="670" src="assets/uploads/sites/5/2018/10/bg-18.jpg"
                                                class="vc_single_image-img attachment-full" alt=""
                                                srcset="assets/uploads/sites/5/2018/10/bg-18.jpg 685w, assets/uploads/sites/5/2018/10/bg-18-300x293.jpg 300w, assets/uploads/sites/5/2018/10/bg-18-600x587.jpg 600w"
                                                sizes="(max-width: 685px) 100vw, 685px" /></div>
                                        <?php } ?>


                                    </figure>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php } ?>

                <?php if ($data['course_status'] == '1') { ?>
                <div data-class-mobile="vc_custom_1542980405538" data-vc-full-width="true"
                    data-vc-full-width-init="false"
                    class="vc_row wpb_row vc_row-fluid vc_custom_1542980405535 bp-background-size-auto vc_row-has-fill"
                    id="courses">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div
                                    class="bp-element bp-element-heading vc_custom_1542033287631 align-center layout-1  ">
                                    <h3 class="title"
                                        style="color:#7c7c7c; line-height:1.625; font-size:16px; font-weight:300; text-transform:none; margin:0 auto 5px auto; ">
                                        <?php echo $data['title']; ?> </h3>
                                    <span class="sub-title"
                                        style="color:#292929; line-height:1.25; font-size:35px; font-weight:400; text-transform:none; margin:0 auto 0px auto; ">Courses</span>
                                    <div class="line" style="height:2px; width:87px; background-color:#e1e1e1; "></div>
                                </div>
                                <div class="bp-element bp-element-course-collections   layout-slider-2 ">
                                    <div class="slide-category-course js-call-slick-col" dir="rtl" data-rtl="1"
                                        data-numofslide="4" data-numofscroll="1" data-loopslide="0" data-autoscroll="0"
                                        data-speedauto="6000" data-respon="4, 1], [4, 1], [2, 1], [1, 1], [1, 1]">
                                        <div class="slide-slick">
                                            <?php if (!empty($section['2'])) {
														foreach ($section['2'] as $k => $sec) {
															if ($k % 2 == 0) {
																$cls = 'color-2';
															} else {
																$cls = 'color-1';
															}
															?>
                                            <div class="item-slick">
                                                <div class="course-item <?php echo $cls; ?>">
                                                    <a href="" class="content"
                                                        style="background-image: url(<?php echo 'images/upload/' . $sec['image']; ?>);">
                                                        <h3 class="title">
                                                            <?php echo $sec['title']; ?> </h3>
                                                        <div class="description">
                                                            <?php echo $sec['short_description']; ?> </div>
                                                    </a>
                                                </div>
                                            </div>
                                            <?php }
													}  ?>

                                        </div>
                                        <div class="wrap-arrow-slick">
                                            <div class="arow-slick prev-slick">
                                                <i class="ion ion-ios-arrow-thin-left"></i>
                                            </div>
                                            <div class="arow-slick next-slick">
                                                <i class="ion ion-ios-arrow-thin-right"></i>
                                            </div>
                                        </div>
                                        <div class="wrap-arrow-slick-clone">
                                            <div class="arow-slick next-slick"></div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="vc_row wpb_row vc_inner vc_row-fluid thim-style-counter-box vc_custom_1539896686311 bp-background-size-auto">
                                    <div
                                        class="thim-line-right wpb_column vc_column_container vc_col-sm-4 bp-background-size-auto">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <!--counter box element-->
                                                <div
                                                    class="bp-element bp-element-counter-box style-horizontal text-left  layout-1   ">
                                                    <div class="counter-boxes">
                                                        <div class="item">
                                                            <div class="counter-box">
                                                                <div class="number"
                                                                    style="color:#7c7c7c; font-size:50px; font-weight:200; margin:0 10px -5px 0; ">
                                                                    <span class="number_counter"
                                                                        data-number="<?php echo $data['total_student']; ?>"
                                                                        data-separator="" data-unit=""></span>
                                                                </div>
                                                                <h3 class="title"
                                                                    style="color:#7c7c7c; font-size:16px; font-weight:200; ">
                                                                    Students </h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="thim-line-right wpb_column vc_column_container vc_col-sm-4 bp-background-size-auto">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <!--counter box element-->
                                                <div
                                                    class="bp-element bp-element-counter-box style-horizontal text-left  layout-1   ">
                                                    <div class="counter-boxes">
                                                        <div class="item">
                                                            <div class="counter-box">
                                                                <div class="number"
                                                                    style="color:#7c7c7c; font-size:50px; font-weight:200; margin:0 10px -5px 0; ">
                                                                    <span class="number_counter"
                                                                        data-number="<?php echo $data['total_course']; ?>"
                                                                        data-separator="" data-unit=""></span>
                                                                </div>
                                                                <h3 class="title"
                                                                    style="color:#7c7c7c; font-size:16px; font-weight:200; ">
                                                                    Courses </h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wpb_column vc_column_container vc_col-sm-4 bp-background-size-auto">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <!--counter box element-->
                                                <div
                                                    class="bp-element bp-element-counter-box style-horizontal text-left  layout-1   ">
                                                    <div class="counter-boxes">
                                                        <div class="item">
                                                            <div class="counter-box">
                                                                <div class="number"
                                                                    style="color:#7c7c7c; font-size:50px; font-weight:200; margin:0 10px -5px 0; ">
                                                                    <span class="number_counter"
                                                                        data-number="<?php echo $data['total_hours_video']; ?>"
                                                                        data-separator="" data-unit=""></span>
                                                                </div>
                                                                <h3 class="title"
                                                                    style="color:#7c7c7c; font-size:16px; font-weight:200; ">
                                                                    Hours video </h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>



                <div class="vc_row-full-width vc_clearfix"></div>

                <?php if ($data['announcement_status'] == '1') { ?>
                <div data-class-mobile="vc_custom_1542980392927" data-vc-full-width="true"
                    data-vc-full-width-init="false"
                    class="vc_row wpb_row vc_row-fluid vc_custom_1542980392924 bp-background-size-auto vc_row-has-fill">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div
                                    class="bp-element bp-element-heading vc_custom_1542033353267 align-center layout-1  ">
                                    <h3 class="title"
                                        style="max-width:500px; color:#7c7c7c; line-height:1.625; font-size:16px; font-weight:300; text-transform:none; margin:0 auto 5px auto; ">
                                        <?php echo $data['title']; ?> </h3>
                                    <span class="sub-title"
                                        style="max-width:500px; color:#292929; line-height:1.25; font-size:35px; font-weight:400; text-transform:none; margin:0 auto 0px auto; ">Announcement</span>
                                    <div class="line" style="height:2px; width:87px; background-color:#e1e1e1; "></div>
                                </div>
                                <div class="bp-element bp-element-list-courses    layout-grid">
                                    <div class="row">
                                        <?php if (!empty($section['3'])) {
													foreach ($section['3'] as $k => $crs) {
														if ($k % 2 == 0) {
															$cls = 'color-2';
														} else {
															$cls = 'color-1';
														}
														?>
                                        <div class="custom-col col-sm-6 col-md-6 col-lg-3 wrapper-item-course">
                                            <div class="item-course <?php echo $cls; ?>">
                                                <?php if (!empty($crs['image'])) { ?>
                                                <div class="pic" style='height:175px;'>
                                                    <a href="index.html">
                                                        <img src="<?php echo 'images/upload/' . $crs['image']; ?>"
                                                            alt="Untitled-19" class="">
                                                    </a>

                                                </div>
                                                <?php } ?>
                                                <div class="text">
                                                    <div class="teacher">
                                                        <?php if (!empty($crs['person_image'])) { ?>
                                                        <div class="ava">
                                                            <img alt="Admin bar avatar"
                                                                src="<?php echo 'images/upload/' . $crs['person_image']; ?>"
                                                                class="avatar avatar-68 photo" height="68" width="68" />
                                                        </div>
                                                        <?php } ?>
                                                        <?php if (!empty($crs['person_name'])) { ?>
                                                        <a href="index.html"><?php echo $crs['person_name']; ?> </a>
                                                        <?php } ?>
                                                    </div>
                                                    <h3 class="title-course">
                                                        <a href="index.html">
                                                            <?php echo $crs['title']; ?> </a>
                                                    </h3>

                                                </div>
                                            </div>
                                        </div>

                                        <?php }
													} else { ?>
                                        <div class="custom-col col-sm-6 col-md-6 col-lg-3 wrapper-item-course">
                                            <div class="item-course color-2">
                                                <div class="pic">
                                                    <a href="index.html">
                                                        <img src="assets/uploads/sites/5/2017/12/Untitled-19-450x300.jpg"
                                                            alt="Untitled-19" class="">
                                                    </a>
                                                    <div class="price">
                                                        &#036;49.00 <span class="old-price"> &#036;69.00</span>
                                                    </div>
                                                </div>
                                                <div class="text">
                                                    <div class="teacher">
                                                        <div class="ava">
                                                            <img alt="Admin bar avatar"
                                                                src="assets/uploads/learn-press-profile/5/2448c53ace919662a2b977d2be3a47c5.jpg"
                                                                class="avatar avatar-68 photo" height="68" width="68" />
                                                        </div>
                                                        <a href="index.html">Charlie Brown</a>
                                                    </div>
                                                    <h3 class="title-course">
                                                        <a href="index.html">
                                                            Learn Python - Interactive Python </a>
                                                    </h3>
                                                    <div class="info-course">
                                                        <span>
                                                            <i class="ion ion-android-person"></i>
                                                            3549 </span>
                                                        <a href="index.html">
                                                            <i class="ion ion-ios-pricetags-outline"></i>
                                                            education </a>
                                                        <span class="star">
                                                            <i class="ion ion-android-star"></i>
                                                            0 </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>

                                    </div>
                                </div>
                                <div class="bp-element bp-element-button   align-center  shape-round icon_alignment-left  "
                                    style=" margin: 22px 0 0 0;">
                                    <!--<a class="btn btn-primary bp-element-hover  btn-normal "
           href="demo-3/courses.html"
						 title='VIEW ALL COURSES'           data-hover="">
            <span class="inner-text">VIEW ALL COURSES</span>
        </a>-->
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php } ?>


                <div class="vc_row-full-width vc_clearfix"></div>

                <?php if ($data['experience_status'] == '1') { ?>
                <div data-class-mobile="vc_custom_1541584275873"
                    class="vc_row wpb_row vc_row-fluid vc_custom_1541584275872 bp-background-size-auto">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div
                                    class="bp-element bp-element-heading vc_custom_1542033381124 align-center layout-1  ">
                                    <h3 class="title"
                                        style="color:#7c7c7c; line-height:1.625; font-size:16px; font-weight:300; text-transform:none; margin:0 auto 5px auto; ">
                                        <?php echo $data['title']; ?> </h3>
                                    <span class="sub-title"
                                        style="color:#292929; line-height:1.25; font-size:35px; font-weight:400; text-transform:none; margin:0 auto 0px auto; ">Live
                                        the experience</span>
                                    <div class="line" style="height:2px; width:87px; background-color:#e1e1e1; "></div>
                                </div>

                                <?php if (!empty($section['4'])) {
											foreach ($section['4'] as $k => $exp) {
												if ($k % 2 == 0) {
													$cls = 'image-left';
												} else {
													$cls = 'image-right';
												}
								?>
                                <div class="bp-element bp-element-image-box    demo-1 <?php echo $cls; ?> color-1 layout-default">
                                    <div class="pic">
                                        <div class="wrap-img">
                                            <div class="main-img">
                                                <!--  <a href="#" class="link">
                Read More                <i class="ion ion-ios-arrow-thin-right"></i>
            </a> -->
                                                <?php if (!empty($exp['image_path'])) { ?>
                                                <img src="<?php echo 'images/upload/' . $exp['image']; ?>"
                                                    alt="Untitled-2A" class="">
                                                <?php } else { ?>
                                                <img src="assets/uploads/sites/5/2018/10/Untitled-2-426x426.jpg"
                                                    alt="Untitled-2" class="">
                                                <?php } ?>

                                            </div>
                                            <div class="background">
                                                <span class="grey-bg small"></span>
                                                <span class="grey-bg big"></span>
                                                <span class="color-bg small"></span>
                                                <span class="color-bg normal"></span>
                                                <span class="color-bg big"></span>
                                            </div>
                                            <div class="symbol">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text">
                                        <div class="wrap-content">
                                            <h3 class="title">
                                                <?php if (!empty($exp['title'])) {
																			echo $exp['title'];
																		} else {
																			echo 'Learn at your own pace';
																		} ?>


                                            </h3>
                                            <div class="content">
                                                <?php if (!empty($exp['short_description'])) {
																			echo $exp['short_description'];
																		} else {
																			echo 'Programs are available in fall, spring, and summer semesters. Many fall and spring programs offer similar shorter programs in the summer, and some may be combined for a full academic year. ';
																		} ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php }
										} ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <?php if ($data['events_status'] == '1') { ?>
                <div data-class-mobile="vc_custom_1542980430319" data-vc-full-width="true"
                    data-vc-full-width-init="false"
                    class="vc_row wpb_row vc_row-fluid vc_custom_1542980430316 bp-background-size-auto vc_row-has-fill"
                    id="events">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div
                                    class="bp-element bp-element-heading vc_custom_1542033409553 align-center layout-1  ">
                                    <h3 class="title"
                                        style="color:#7c7c7c; line-height:1.625; font-size:16px; font-weight:300; text-transform:none; margin:0 auto 5px auto; ">
                                    </h3>
                                    <span class="sub-title"
                                        style="color:#292929; line-height:1.25; font-size:35px; font-weight:400; text-transform:none; margin:0 auto 0px auto; ">Events</span>
                                    <div class="line" style="height:2px; width:87px; background-color:#e1e1e1; "></div>
                                </div>
                                <div class="bp-element bp-element-list-events   mobile-center layout-slider-2">
                                    <div class="slide-events js-call-slick-col" data-numofslide="4" data-numofscroll="1"
                                        data-loopslide="0" data-autoscroll="0" data-speedauto="6000"
                                        data-respon="[4, 1], [3, 1], [3, 1], [2, 1], [1, 1]">
                                        <div class="wrap-arrow-slick">
                                            <div class="arow-slick prev-slick">
                                                <i class="ion ion-ios-arrow-thin-right"></i>
                                            </div>
                                            <div class="arow-slick next-slick">
                                                <i class="ion ion-ios-arrow-thin-left"></i>
                                            </div>
                                        </div>
                                        <div class="wrap-arrow-slick-clone">
                                            <div class="arow-slick next-slick"></div>
                                        </div>
                                        <div class="slide-slick">
                                            <?php if (!empty($section['7'])) {
														foreach ($section['7'] as $k => $nws) {
															if ($k % 2 == 0) {
																$cls = 'color-2';
															} else {
																$cls = 'color-1';
															}
															?>
                                            <div class="item-slick">
                                                <div class="event-item <?php echo $cls; ?>">
                                                    <div class="pic">
                                                        <img src="<?php echo 'images/upload/' . $nws['image']; ?>"
                                                            alt="Untitled-13" class="">
                                                    </div>
                                                    <?php if (!empty($nws['date'])) { ?>
                                                    <div class="date">
                                                        <span><?php echo $nws['date']; ?></span> </div>
                                                    <?php } ?>
                                                    <div class="text">
                                                        <div class="time">
                                                            <?php echo $nws['time']; ?> </div>
                                                        <h3 class="title">
                                                            <a href="index.html" style="font-size:17px;">
                                                                <?php echo $nws['title']; ?> </a>
                                                        </h3>

                                                        <?php if (!empty($nws['person_name'])) { ?>
                                                        <div class="author">
                                                            <div class="ava">
                                                                <img src="<?php echo 'images/upload/' . $nws['person_image']; ?>"
                                                                    alt="IMG">
                                                            </div>
                                                            <div class="info">
                                                                <div class="name">
                                                                    By <a
                                                                        href="index.html"><?php echo $nws['person_name']; ?></a>
                                                                </div>
                                                                <div class="address">
                                                                    <?php echo $nws['person_address']; ?> </div>
                                                            </div>
                                                        </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>


                                            <?php }
													}  ?>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php } ?>


                <div class="vc_row-full-width vc_clearfix"></div>

                <?php if ($data['comments_status'] == '1') { ?>
                <div class="vc_row wpb_row vc_row-fluid vc_custom_1539759301463 bp-background-size-auto">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div class="bp-element bp-element-testimonials   layout-slider-4  ">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="img-testimonial">
                                                <img src="<?php echo 'images/upload/' . $data['comment_image']; ?>"
                                                    alt="">
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="slide-testimonial js-call-slick-col" data-numofslide="1"
                                                data-numofscroll="1" data-loopslide="1" data-autoscroll="0"
                                                data-speedauto="6000" data-customdot="0"
                                                data-respon="[1, 1], [1, 1], [1, 1], [1, 1], [1, 1]">
                                                <div class="slide-slick">
                                                    <?php if (!empty($section['8'])) {
																foreach ($section['8'] as $k => $cmm) {
																	if ($k % 2 == 0) {
																		$cls = 'color-2';
																	} else {
																		$cls = 'color-1';
																	}
																	?>
                                                    <div class="testimonial-item">
                                                        <div class="content">
                                                            <?php echo $cmm['description']; ?> </div>
                                                        <div class="author">
                                                            <?php if (!empty($cmm['person_image'])) { ?>
                                                            <div class="ava">
                                                                <img src="<?php echo 'images/upload/' . $cmm['person_image']; ?>"
                                                                    alt="gallery-07" class="">
                                                            </div>
                                                            <?php } ?>
                                                            <div class="info">
                                                                <a href="#" target="_blank"
                                                                    class="name"><?php echo $cmm['person_name']; ?></a>
                                                                <span
                                                                    class="description"><?php echo $cmm['person_designation']; ?></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <?php }
															}  ?>


                                                </div>
                                                <div class="wrap-dot-slick"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <!-- contact us Start-->

                <?php if ($data['contact_status'] == '1') { ?>

                <div class="vc_row wpb_row vc_row-fluid bp-background-size-auto" id="contactus">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div class="vc_empty_space" style="height: 92px"><span
                                        class="vc_empty_space_inner"></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vc_row wpb_row vc_row-fluid thim-leave-message bp-background-size-auto">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div class="bp-element bp-element-heading  align-center layout-1  thim-style-demo">
                                    <h3 class="title"
                                        style="color:#292929; font-size:35px; font-weight:700; text-transform:none; margin:0 0 13px 0; ">
                                        Contact Us </h3>



                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vc_row wpb_row vc_row-fluid vc_custom_1539115596535 bp-background-size-auto">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div class="vc_row-full-width vc_clearfix" style="padding:20px;"></div>
                                <!--google map element-->
                                <div class="mapouter">
                                    <div class="gmap_canvas"><iframe width="1100" height="440" id="gmap_canvas"
                                            src="https://maps.google.com/maps?q=12th%20A%20Main%20Rd%2C%20HAL%202nd%20Stage%2C%20Indiranagar%2C%20Bengaluru%2C%20Karnataka%20560008&t=&z=13&ie=UTF8&iwloc=&output=embed"
                                            frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe><a
                                            href="https://www.embedgooglemap.net/blog/divi-discount-code-elegant-themes-coupon/">other
                                            divi discount code</a></div>
                                    <style>
                                    .mapouter {
                                        position: relative;
                                        text-align: right;
                                        height: 440px;
                                        width: 1100px;
                                    }

                                    .gmap_canvas {
                                        overflow: hidden;
                                        background: none !important;
                                        height: 440px;
                                        width: 1100px;
                                    }
                                    </style>
                                </div>



                                <!--<div class="bp-element bp-element-google-map   "      style="height: 440px" >

	
	
    <div class="ob-google-map-canvas"
         id="ob-map-canvas-bc180dbc583491c00f8a1cd134f7517b" data-address='london'  data-zoom='12' data-scroll-zoom=''  data-draggable='' data-style='default'  data-api_key='AIzaSyA0rsuYtaOizexB6cCvcQSRIskctOGCCwo'></div>
</div>-->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="vc_row-full-width vc_clearfix" style="padding:20px;"></div>
                <div class="vc_row wpb_row vc_row-fluid vc_custom_1539164509860 bp-background-size-auto">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div role="form" class="wpcf7" id="wpcf7-f930-p43-o1" lang="en-US" dir="ltr">
                                    <div class="screen-reader-response"></div>
                                    <form id="contactForm" class="wpcf7-form" novalidate="novalidate">
                                        <div style="display: none;">

                                        </div>
                                        <div class="form-contactpage">
                                            <label class="wrap-input"><span
                                                    class="wpcf7-form-control-wrap your-name"><input type="text"
                                                        name="name" value="" size="40"
                                                        class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required chkempty"
                                                        aria-required="true" aria-invalid="false"
                                                        placeholder="Name" /></span></label> <label
                                                class="wrap-input"><span
                                                    class="wpcf7-form-control-wrap your-subject"><input type="text"
                                                        name="subject" value="" size="40"
                                                        class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required chkempty"
                                                        aria-required="true" aria-invalid="false"
                                                        placeholder="Subject" /></span></label> <label
                                                class="wrap-input"><span
                                                    class="wpcf7-form-control-wrap your-email"><input type="email"
                                                        name="email" value="" size="40"
                                                        class="wpcf7-form-control wpcf7-text wpcf7-email wpcf7-validates-as-required wpcf7-validates-as-email chkempty"
                                                        aria-required="true" aria-invalid="false"
                                                        placeholder="Email" /></span></label><br />
                                            <label class="wap-textarea"><span
                                                    class="wpcf7-form-control-wrap your-message"><textarea
                                                        name="message" cols="40" rows="10"
                                                        class="wpcf7-form-control wpcf7-textarea chkempty"
                                                        aria-invalid="false"
                                                        placeholder="Message"></textarea></span></label><br />
                                            <input type="submit" id="submitContact" value="send your message"
                                                class=" btn-normal shape-round" />
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                </div>


                <h3 class="title" style="display:none;text-align: center;color: green;padding: 10px 0px 10px 0px;"
                    id="showmsg">Your Message Sent Successfully</h3>

                <h3 class="title" style="display:none;text-align: center;color: green;padding: 10px 0px 10px 0px;"
                    id="showmsg">Your Message Sent Successfully</h3>

                <?php } ?>
                <!-- contact end -->
                <!-- Admission starts -->
                <?php if ($data['admission_status'] == '1') { ?>

                <?php } ?>
                <div class="vc_row wpb_row vc_row-fluid bp-background-size-auto" id="admission">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div class="vc_empty_space" style="height: 92px"><span
                                        class="vc_empty_space_inner"></span></div>
                            </div>
                        </div>
                    </div>
				</div>
				<div class="vc_row wpb_row vc_row-fluid thim-leave-message bp-background-size-auto">
                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                        <div class="vc_column-inner">
                            <div class="wpb_wrapper">
                                <div class="bp-element bp-element-heading  align-center layout-1  thim-style-demo">
                                    <h3 class="title"
                                        style="color:#292929; font-size:35px; font-weight:700; text-transform:none; margin:0 0 13px 0; ">
                                        Admission </h3>



                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="container">
                    <div class="row">
                        <div class="col-sm-3 col-lg-3"></div>

                        <div class="col-sm-7 col-lg-7 ">
                            <form class="form_boarder">
                                <center>
                                    <h5><span id="clicklogin" class="underline_click"><a  onclick="loginsection()" >Login</a></span> /<span  id="clickreg" class="underline_click"><a  onclick="regsection()">
                                            Registration</a></span>
                                    </h5>
                                </center>
                                <hr style="background:#939191">
                                <div id="login">
                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable>User Name:</lable>
                                        </span>
                                        <input type='text' name='uname' class='custom_input'>
                                    </div>
                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable>Password:</lable>
                                        </span>
                                        <input type='password' name='pw' class='custom_input'>
                                    </div>
                                    <center>
                                        <button type="button" class='loginbtn btn-primary'>Submit</button>
                                    </center>
                                </div>

                                <div id="register">
                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable>Name:</lable>
                                        </span>
                                        <input type='text' name='name' class='custom_input'>
                                    </div>

                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable>Email:</lable>
                                        </span>
                                        <input type='email' name='email' class='custom_input'>
                                    </div>
                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable>Mobile:</lable>
                                        </span>
                                        <input type='number' name='mobile' class='custom_input'>
                                    </div>
                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable>Password:</lable>
                                        </span>
                                        <input type='password' name='pw' class='custom_input'>
                                    </div>
                                    <div class="form-group">
                                        <span class="loginlable">
                                            <lable>Confirm Password:</lable>
                                        </span>
                                        <input type='password' name='cpw' class='custom_input'>
                                    </div>
                                    <center>
                                        <button type="button" class='loginbtn btn-primary'>Submit</button>
                                    </center>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

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

				}
				.css_clicked{
					color:green !important;
				}
				.css_notclicked{
					color:#7c7c7c!important;
				}
                </style>
                <script>
                $(document).ready(function() {
                    $("#register").hide();
					$("#login").show();
					$("#clicklogin").addClass('css_clicked underline_click');
					$("#clickreg").removeClass('css_clicked underline_click');

                });

                function loginsection() {
					$("#clicklogin").addClass('css_clicked underline_click');
					$("#clicklogin").removeClass('css_notclicked');
					$("#clickreg").addClass('css_notclicked');
					$("#clickreg").removeClass('css_clicked underline_click');
					
                    $("#register").hide();
                    $("#login").show();
                }

                function regsection() {
					$("#clickreg").addClass('css_clicked underline_click');
					$("#clickreg").removeClass('css_notclicked');
					$("#clicklogin").addClass('css_notclicked');
					$("#clicklogin").removeClass('css_clicked underline_click');
                    $("#register").show();
                    $("#login").hide();
                }
                </script>




               

                <!-- Admission  End -->
                <div class="vc_row-full-width vc_clearfix" style="padding:20px;"></div>
                <!--
<div class="vc_row wpb_row vc_row-fluid vc_custom_1539759383463 bp-background-size-auto"><div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto"><div class="vc_column-inner" ><div class="wpb_wrapper">
<div class="bp-element bp-element-call-to-action  layout-5  background-default "   >
<div class="inner-action">
    <div class="content-text">
                    <h3 class="title"  style="font-size:22px; ">
                Start mastering your courses! Try now for free            </h3>
        <div class="call-action-button">
                            <a class="btn-get-started" href="#"
                     title='GET STARTED'>
                    GET STARTED </a>
                <a class="btn-sign-up" href="#"
                     title='SIGN UP'>
                    SIGN UP </a>
                    </div>
    </div>
</div>
</div>
</div></div></div></div>

<div class="vc_row wpb_row vc_row-fluid vc_custom_1539761025254 bp-background-size-auto"><div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto"><div class="vc_column-inner" ><div class="wpb_wrapper">
<div class="bp-element bp-element-brands   layout-slider " >
<div class="js-call-slick-col" data-pagination="" data-numofslide="5" data-numofscroll="1"
     data-loopslide="0" data-autoscroll="0" data-speedauto="6000"
     data-respon="[5, 1], [5, 1], [3, 1], [2, 1], [2, 1]">
    <div class="slide-slick">
		            <div class="item-slick">
                <div class="item-brands">
					<img src="assets/uploads/sites/5/2018/10/logo-07.png" width="127" height="42" alt="Logo">                </div>
            </div>
		            <div class="item-slick">
                <div class="item-brands">
					<img src="assets/uploads/sites/5/2018/10/logo-07.png" width="127" height="42" alt="Logo">                </div>
            </div>
		            <div class="item-slick">
                <div class="item-brands">
					<img src="assets/uploads/sites/5/2018/10/logo-07.png" width="127" height="42" alt="Logo">                </div>
            </div>
		            <div class="item-slick">
                <div class="item-brands">
					<img src="assets/uploads/sites/5/2018/10/logo-07.png" width="127" height="42" alt="Logo">                </div>
            </div>
		            <div class="item-slick">
                <div class="item-brands">
					<img src="assets/uploads/sites/5/2018/10/logo-07.png" width="127" height="42" alt="Logo">                </div>
            </div>
		            <div class="item-slick">
                <div class="item-brands">
					<img src="assets/uploads/sites/5/2018/10/logo-07.png" width="127" height="42" alt="Logo">                </div>
            </div>
		    </div>
    </div>
</div></div></div></div></div>

-->
            </div><!-- #home-main-content -->
        </div><!-- #main-content -->
        <footer id="colophon" class="footer_home_3 site-footer">
            <div class="footer">
                <div class="container">
                    <div class="footer-sidebars columns-5 row">
                        <div class="col-xs-12 col-sm-6 col-md-">
                            <aside id="thim_layout_builder-4" class="widget widget_thim_layout_builder">
                                <style>
                                .vc_custom_1542034720329 {
                                    padding-right: 80px !important;
                                }

                                .vc_custom_1542034720330 {
                                    padding-right: 0px !important;
                                }

                                .vc_custom_1542034720330 {
                                    padding-right: 0px !important;
                                }
                                </style>
                                <div data-class-tablet="vc_custom_1542034720330"
                                    data-class-mobile="vc_custom_1542034720330"
                                    class="vc_row wpb_row vc_row-fluid vc_custom_1542034720329 bp-background-size-auto">
                                    <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div
                                                    class="wpb_single_image wpb_content_element vc_align_left   max-width-163">
                                                    <figure class="wpb_wrapper vc_figure">
                                                        <div class="vc_single_image-wrapper   vc_box_border_grey"><img
                                                                width="336" height="276"
                                                                src="<?php echo 'images/logo/' . $data['logo_image']; ?>"
                                                                class="vc_single_image-img attachment-full" alt=""
                                                                srcset="" sizes="(max-width: 336px) 100vw, 336px" />
                                                        </div>
                                                    </figure>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </aside>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-">
                            <aside id="nav_menu-3" class="widget widget_nav_menu">
                                <div class="menu-footer-1-container">
                                    <ul id="menu-footer-1" class="menu">
                                        <li id="menu-item-1222"
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-17 current_page_item menu-item-1222 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                            <a href="" class="tc-menu-inner">Home</a></li>
                                        <li id="menu-item-1223"
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1223 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                            <a href="#aboutus" class="tc-menu-inner">About</a></li>
                                        <li id="menu-item-1226"
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1226 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                            <a href="#announcement" class="tc-menu-inner">Announcement</a></li>

                                    </ul>
                                </div>
                            </aside>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-">
                            <aside id="nav_menu-4" class="widget widget_nav_menu">
                                <div class="menu-footer-2-container">
                                    <ul id="menu-footer-2" class="menu">
                                        <li id="menu-item-1227"
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1227 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                            <a href="#contactus" class="tc-menu-inner">Contact us</a></li>
                                        <li id="menu-item-1228"
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1228 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                            <a href="#courses" class="tc-menu-inner">Courses</a></li>
                                        <li id="menu-item-1229"
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1229 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default">
                                            <a href="#events" class="tc-menu-inner">Events</a></li>
                                    </ul>
                                </div>
                            </aside>
                        </div>
                        <!--<div class="col-xs-12 col-sm-6 col-md-">
						<aside id="nav_menu-5" class="widget widget_nav_menu"><div class="menu-footer-3-container"><ul id="menu-footer-3" class="menu"><li id="menu-item-1230" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1230 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default"><a href="demo-3/membership-account/membership-levels.html" class="tc-menu-inner">Membership</a></li>
<li id="menu-item-1231" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1231 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default"><a href="index.html" class="tc-menu-inner">Page 404</a></li>
<li id="menu-item-1232" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1232 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default"><a href="index.html" class="tc-menu-inner">Profile</a></li>
<li id="menu-item-1233" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1233 tc-menu-item tc-menu-depth-0 tc-menu-align-left tc-menu-layout-default"><a href="index.html" class="tc-menu-inner">Programs</a></li>
</ul></div></aside>					</div>-->
                        <div class="col-xs-12 col-sm-6 col-md-">
                            <aside id="text-7" class="text-left widget widget_text">
                                <h3 class="widget-title">Subscribe</h3>
                                <div class="textwidget">
                                    <section id="yikes-mailchimp-container-1"
                                        class="yikes-mailchimp-container yikes-mailchimp-container-1 ">
                                        <form id="footer-email-1"
                                            class="yikes-easy-mc-form yikes-easy-mc-form-1  layout-footer" method="POST"
                                            data-attr-form-id="1">
                                            <label for="yikes-easy-mc-form-1-EMAIL"
                                                class="EMAIL-label yikes-mailchimp-field-required ">
                                                <!-- dictate label visibility -->
                                                <!-- Description Above -->
                                                <input id="yikes-easy-mc-form-1-EMAIL" name="EMAIL"
                                                    placeholder="Your Email Here"
                                                    class="yikes-easy-mc-email field-no-label" required="required"
                                                    type="email" value="">
                                                <!-- Description Below -->
                                            </label>
                                            <!-- Honeypot Trap -->
                                            <input type="hidden" name="yikes-mailchimp-honeypot"
                                                id="yikes-mailchimp-honeypot-1" value="">
                                            <!-- List ID -->
                                            <input type="hidden" name="yikes-mailchimp-associated-list-id"
                                                id="yikes-mailchimp-associated-list-id-1" value="dbd7a7673e">
                                            <!-- The form that is being submitted! Used to display error/success messages above the correct form -->
                                            <input type="hidden" name="yikes-mailchimp-submitted-form"
                                                id="yikes-mailchimp-submitted-form-1" value="1">
                                            <!-- Submit Button -->
                                            <button type="submit"
                                                class="yikes-easy-mc-submit-button yikes-easy-mc-submit-button-1 btn btn-primary ">
                                                <span
                                                    class="yikes-mailchimp-submit-button-span-text">SUBSCRIBE</span></button>
                                            <!-- Nonce Security Check -->
                                            <input type="hidden" id="yikes_easy_mc_new_subscriber_1"
                                                name="yikes_easy_mc_new_subscriber" value="5985a25ad1">
                                            <input type="hidden" name="_wp_http_referer" value="/demo-3/" />
                                        </form>
                                    </section>
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>
            </div>
            <div class="copyright-area">
                <div class="container">
                    <div class="copyright-content">
                        <div class="copyright-text">
                            <a target="_blank" href="https://www.parentof.com/">Powered by ParentOf </a> </div>
                        <aside id="thim_layout_builder-12" class="widget widget_thim_layout_builder">
                            <div class="vc_row wpb_row vc_row-fluid bp-background-size-auto">
                                <div class="wpb_column vc_column_container vc_col-sm-12 bp-background-size-auto">
                                    <div class="vc_column-inner">
                                        <div class="wpb_wrapper">
                                            <div class="bp-element bp-element-social-links default   ">
                                                <ul class="socials">
                                                    <?php if (!empty($data['facebook_link'])) { ?>
                                                    <li class="facebook">
                                                        <a target="_blank" href="<?php echo $data['facebook_link']; ?>">
                                                            <i class="social-icon fa fa-facebook"></i>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                    <?php if (!empty($data['twitter_link'])) { ?>
                                                    <li class="twitter">
                                                        <a target="_blank" href="<?php echo $data['twitter_link']; ?>">
                                                            <i class="social-icon fa fa-twitter"></i>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                    <?php if (!empty($data['pinterest_link'])) { ?>
                                                    <li class="pinterest-p">
                                                        <a target="_blank"
                                                            href="<?php echo $data['pinterest_link']; ?>">
                                                            <i class="social-icon fa fa-pinterest-p"></i>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                    <?php if (!empty($data['linkdlin_link'])) { ?>
                                                    <li class="linkedin">
                                                        <a target="_blank" href="<?php echo $data['linkdlin_link']; ?>">
                                                            <i class="social-icon fa fa-linkedin"></i>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </footer><!-- #colophon -->
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

   <div class="modal" id="app_status">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Application Status </h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body">
					 <div class="container">
        <div class="alert alert-danger"></div>
        <div class="row">
            <div class="col-sm-3 col-lg-3">
            </div>
            <div class="col-sm-12 col-lg-12">
                <form class='formcss' id="frm-mobile-verification">
                    <h4>Search Application </h4>
                    <div class="row">
                        <div class="col-sm-2 col-lg-2">
                            <span class="lablembl">Mobile :</span>
                        </div>
						<div class="col-sm-2 col-lg-2">
						<select name="countryCode" class="sel_mbcode"  id="">
	<option data-countryCode="GB" value="44" >UK (+44)</option>
	<option data-countryCode="US" value="1">USA (+1)</option>
	<optgroup label="Other countries">
		<option data-countryCode="DZ" value="213">Algeria (+213)</option>
		<option data-countryCode="AD" value="376">Andorra (+376)</option>
		<option data-countryCode="AO" value="244">Angola (+244)</option>
		<option data-countryCode="AI" value="1264">Anguilla (+1264)</option>
		<option data-countryCode="AG" value="1268">Antigua &amp; Barbuda (+1268)</option>
		<option data-countryCode="AR" value="54">Argentina (+54)</option>
		<option data-countryCode="AM" value="374">Armenia (+374)</option>
		<option data-countryCode="AW" value="297">Aruba (+297)</option>
		<option data-countryCode="AU" value="61">Australia (+61)</option>
		<option data-countryCode="AT" value="43">Austria (+43)</option>
		<option data-countryCode="AZ" value="994">Azerbaijan (+994)</option>
		<option data-countryCode="BS" value="1242">Bahamas (+1242)</option>
		<option data-countryCode="BH" value="973">Bahrain (+973)</option>
		<option data-countryCode="BD" value="880">Bangladesh (+880)</option>
		<option data-countryCode="BB" value="1246">Barbados (+1246)</option>
		<option data-countryCode="BY" value="375">Belarus (+375)</option>
		<option data-countryCode="BE" value="32">Belgium (+32)</option>
		<option data-countryCode="BZ" value="501">Belize (+501)</option>
		<option data-countryCode="BJ" value="229">Benin (+229)</option>
		<option data-countryCode="BM" value="1441">Bermuda (+1441)</option>
		<option data-countryCode="BT" value="975">Bhutan (+975)</option>
		<option data-countryCode="BO" value="591">Bolivia (+591)</option>
		<option data-countryCode="BA" value="387">Bosnia Herzegovina (+387)</option>
		<option data-countryCode="BW" value="267">Botswana (+267)</option>
		<option data-countryCode="BR" value="55">Brazil (+55)</option>
		<option data-countryCode="BN" value="673">Brunei (+673)</option>
		<option data-countryCode="BG" value="359">Bulgaria (+359)</option>
		<option data-countryCode="BF" value="226">Burkina Faso (+226)</option>
		<option data-countryCode="BI" value="257">Burundi (+257)</option>
		<option data-countryCode="KH" value="855">Cambodia (+855)</option>
		<option data-countryCode="CM" value="237">Cameroon (+237)</option>
		<option data-countryCode="CA" value="1">Canada (+1)</option>
		<option data-countryCode="CV" value="238">Cape Verde Islands (+238)</option>
		<option data-countryCode="KY" value="1345">Cayman Islands (+1345)</option>
		<option data-countryCode="CF" value="236">Central African Republic (+236)</option>
		<option data-countryCode="CL" value="56">Chile (+56)</option>
		<option data-countryCode="CN" value="86">China (+86)</option>
		<option data-countryCode="CO" value="57">Colombia (+57)</option>
		<option data-countryCode="KM" value="269">Comoros (+269)</option>
		<option data-countryCode="CG" value="242">Congo (+242)</option>
		<option data-countryCode="CK" value="682">Cook Islands (+682)</option>
		<option data-countryCode="CR" value="506">Costa Rica (+506)</option>
		<option data-countryCode="HR" value="385">Croatia (+385)</option>
		<option data-countryCode="CU" value="53">Cuba (+53)</option>
		<option data-countryCode="CY" value="90392">Cyprus North (+90392)</option>
		<option data-countryCode="CY" value="357">Cyprus South (+357)</option>
		<option data-countryCode="CZ" value="42">Czech Republic (+42)</option>
		<option data-countryCode="DK" value="45">Denmark (+45)</option>
		<option data-countryCode="DJ" value="253">Djibouti (+253)</option>
		<option data-countryCode="DM" value="1809">Dominica (+1809)</option>
		<option data-countryCode="DO" value="1809">Dominican Republic (+1809)</option>
		<option data-countryCode="EC" value="593">Ecuador (+593)</option>
		<option data-countryCode="EG" value="20">Egypt (+20)</option>
		<option data-countryCode="SV" value="503">El Salvador (+503)</option>
		<option data-countryCode="GQ" value="240">Equatorial Guinea (+240)</option>
		<option data-countryCode="ER" value="291">Eritrea (+291)</option>
		<option data-countryCode="EE" value="372">Estonia (+372)</option>
		<option data-countryCode="ET" value="251">Ethiopia (+251)</option>
		<option data-countryCode="FK" value="500">Falkland Islands (+500)</option>
		<option data-countryCode="FO" value="298">Faroe Islands (+298)</option>
		<option data-countryCode="FJ" value="679">Fiji (+679)</option>
		<option data-countryCode="FI" value="358">Finland (+358)</option>
		<option data-countryCode="FR" value="33">France (+33)</option>
		<option data-countryCode="GF" value="594">French Guiana (+594)</option>
		<option data-countryCode="PF" value="689">French Polynesia (+689)</option>
		<option data-countryCode="GA" value="241">Gabon (+241)</option>
		<option data-countryCode="GM" value="220">Gambia (+220)</option>
		<option data-countryCode="GE" value="7880">Georgia (+7880)</option>
		<option data-countryCode="DE" value="49">Germany (+49)</option>
		<option data-countryCode="GH" value="233">Ghana (+233)</option>
		<option data-countryCode="GI" value="350">Gibraltar (+350)</option>
		<option data-countryCode="GR" value="30">Greece (+30)</option>
		<option data-countryCode="GL" value="299">Greenland (+299)</option>
		<option data-countryCode="GD" value="1473">Grenada (+1473)</option>
		<option data-countryCode="GP" value="590">Guadeloupe (+590)</option>
		<option data-countryCode="GU" value="671">Guam (+671)</option>
		<option data-countryCode="GT" value="502">Guatemala (+502)</option>
		<option data-countryCode="GN" value="224">Guinea (+224)</option>
		<option data-countryCode="GW" value="245">Guinea - Bissau (+245)</option>
		<option data-countryCode="GY" value="592">Guyana (+592)</option>
		<option data-countryCode="HT" value="509">Haiti (+509)</option>
		<option data-countryCode="HN" value="504">Honduras (+504)</option>
		<option data-countryCode="HK" value="852">Hong Kong (+852)</option>
		<option data-countryCode="HU" value="36">Hungary (+36)</option>
		<option data-countryCode="IS" value="354">Iceland (+354)</option>
		<option data-countryCode="IN" value="91" Selected>India (+91)</option>
		<option data-countryCode="ID" value="62">Indonesia (+62)</option>
		<option data-countryCode="IR" value="98">Iran (+98)</option>
		<option data-countryCode="IQ" value="964">Iraq (+964)</option>
		<option data-countryCode="IE" value="353">Ireland (+353)</option>
		<option data-countryCode="IL" value="972">Israel (+972)</option>
		<option data-countryCode="IT" value="39">Italy (+39)</option>
		<option data-countryCode="JM" value="1876">Jamaica (+1876)</option>
		<option data-countryCode="JP" value="81">Japan (+81)</option>
		<option data-countryCode="JO" value="962">Jordan (+962)</option>
		<option data-countryCode="KZ" value="7">Kazakhstan (+7)</option>
		<option data-countryCode="KE" value="254">Kenya (+254)</option>
		<option data-countryCode="KI" value="686">Kiribati (+686)</option>
		<option data-countryCode="KP" value="850">Korea North (+850)</option>
		<option data-countryCode="KR" value="82">Korea South (+82)</option>
		<option data-countryCode="KW" value="965">Kuwait (+965)</option>
		<option data-countryCode="KG" value="996">Kyrgyzstan (+996)</option>
		<option data-countryCode="LA" value="856">Laos (+856)</option>
		<option data-countryCode="LV" value="371">Latvia (+371)</option>
		<option data-countryCode="LB" value="961">Lebanon (+961)</option>
		<option data-countryCode="LS" value="266">Lesotho (+266)</option>
		<option data-countryCode="LR" value="231">Liberia (+231)</option>
		<option data-countryCode="LY" value="218">Libya (+218)</option>
		<option data-countryCode="LI" value="417">Liechtenstein (+417)</option>
		<option data-countryCode="LT" value="370">Lithuania (+370)</option>
		<option data-countryCode="LU" value="352">Luxembourg (+352)</option>
		<option data-countryCode="MO" value="853">Macao (+853)</option>
		<option data-countryCode="MK" value="389">Macedonia (+389)</option>
		<option data-countryCode="MG" value="261">Madagascar (+261)</option>
		<option data-countryCode="MW" value="265">Malawi (+265)</option>
		<option data-countryCode="MY" value="60">Malaysia (+60)</option>
		<option data-countryCode="MV" value="960">Maldives (+960)</option>
		<option data-countryCode="ML" value="223">Mali (+223)</option>
		<option data-countryCode="MT" value="356">Malta (+356)</option>
		<option data-countryCode="MH" value="692">Marshall Islands (+692)</option>
		<option data-countryCode="MQ" value="596">Martinique (+596)</option>
		<option data-countryCode="MR" value="222">Mauritania (+222)</option>
		<option data-countryCode="YT" value="269">Mayotte (+269)</option>
		<option data-countryCode="MX" value="52">Mexico (+52)</option>
		<option data-countryCode="FM" value="691">Micronesia (+691)</option>
		<option data-countryCode="MD" value="373">Moldova (+373)</option>
		<option data-countryCode="MC" value="377">Monaco (+377)</option>
		<option data-countryCode="MN" value="976">Mongolia (+976)</option>
		<option data-countryCode="MS" value="1664">Montserrat (+1664)</option>
		<option data-countryCode="MA" value="212">Morocco (+212)</option>
		<option data-countryCode="MZ" value="258">Mozambique (+258)</option>
		<option data-countryCode="MN" value="95">Myanmar (+95)</option>
		<option data-countryCode="NA" value="264">Namibia (+264)</option>
		<option data-countryCode="NR" value="674">Nauru (+674)</option>
		<option data-countryCode="NP" value="977">Nepal (+977)</option>
		<option data-countryCode="NL" value="31">Netherlands (+31)</option>
		<option data-countryCode="NC" value="687">New Caledonia (+687)</option>
		<option data-countryCode="NZ" value="64">New Zealand (+64)</option>
		<option data-countryCode="NI" value="505">Nicaragua (+505)</option>
		<option data-countryCode="NE" value="227">Niger (+227)</option>
		<option data-countryCode="NG" value="234">Nigeria (+234)</option>
		<option data-countryCode="NU" value="683">Niue (+683)</option>
		<option data-countryCode="NF" value="672">Norfolk Islands (+672)</option>
		<option data-countryCode="NP" value="670">Northern Marianas (+670)</option>
		<option data-countryCode="NO" value="47">Norway (+47)</option>
		<option data-countryCode="OM" value="968">Oman (+968)</option>
		<option data-countryCode="PW" value="680">Palau (+680)</option>
		<option data-countryCode="PA" value="507">Panama (+507)</option>
		<option data-countryCode="PG" value="675">Papua New Guinea (+675)</option>
		<option data-countryCode="PY" value="595">Paraguay (+595)</option>
		<option data-countryCode="PE" value="51">Peru (+51)</option>
		<option data-countryCode="PH" value="63">Philippines (+63)</option>
		<option data-countryCode="PL" value="48">Poland (+48)</option>
		<option data-countryCode="PT" value="351">Portugal (+351)</option>
		<option data-countryCode="PR" value="1787">Puerto Rico (+1787)</option>
		<option data-countryCode="QA" value="974">Qatar (+974)</option>
		<option data-countryCode="RE" value="262">Reunion (+262)</option>
		<option data-countryCode="RO" value="40">Romania (+40)</option>
		<option data-countryCode="RU" value="7">Russia (+7)</option>
		<option data-countryCode="RW" value="250">Rwanda (+250)</option>
		<option data-countryCode="SM" value="378">San Marino (+378)</option>
		<option data-countryCode="ST" value="239">Sao Tome &amp; Principe (+239)</option>
		<option data-countryCode="SA" value="966">Saudi Arabia (+966)</option>
		<option data-countryCode="SN" value="221">Senegal (+221)</option>
		<option data-countryCode="CS" value="381">Serbia (+381)</option>
		<option data-countryCode="SC" value="248">Seychelles (+248)</option>
		<option data-countryCode="SL" value="232">Sierra Leone (+232)</option>
		<option data-countryCode="SG" value="65">Singapore (+65)</option>
		<option data-countryCode="SK" value="421">Slovak Republic (+421)</option>
		<option data-countryCode="SI" value="386">Slovenia (+386)</option>
		<option data-countryCode="SB" value="677">Solomon Islands (+677)</option>
		<option data-countryCode="SO" value="252">Somalia (+252)</option>
		<option data-countryCode="ZA" value="27">South Africa (+27)</option>
		<option data-countryCode="ES" value="34">Spain (+34)</option>
		<option data-countryCode="LK" value="94">Sri Lanka (+94)</option>
		<option data-countryCode="SH" value="290">St. Helena (+290)</option>
		<option data-countryCode="KN" value="1869">St. Kitts (+1869)</option>
		<option data-countryCode="SC" value="1758">St. Lucia (+1758)</option>
		<option data-countryCode="SD" value="249">Sudan (+249)</option>
		<option data-countryCode="SR" value="597">Suriname (+597)</option>
		<option data-countryCode="SZ" value="268">Swaziland (+268)</option>
		<option data-countryCode="SE" value="46">Sweden (+46)</option>
		<option data-countryCode="CH" value="41">Switzerland (+41)</option>
		<option data-countryCode="SI" value="963">Syria (+963)</option>
		<option data-countryCode="TW" value="886">Taiwan (+886)</option>
		<option data-countryCode="TJ" value="7">Tajikstan (+7)</option>
		<option data-countryCode="TH" value="66">Thailand (+66)</option>
		<option data-countryCode="TG" value="228">Togo (+228)</option>
		<option data-countryCode="TO" value="676">Tonga (+676)</option>
		<option data-countryCode="TT" value="1868">Trinidad &amp; Tobago (+1868)</option>
		<option data-countryCode="TN" value="216">Tunisia (+216)</option>
		<option data-countryCode="TR" value="90">Turkey (+90)</option>
		<option data-countryCode="TM" value="7">Turkmenistan (+7)</option>
		<option data-countryCode="TM" value="993">Turkmenistan (+993)</option>
		<option data-countryCode="TC" value="1649">Turks &amp; Caicos Islands (+1649)</option>
		<option data-countryCode="TV" value="688">Tuvalu (+688)</option>
		<option data-countryCode="UG" value="256">Uganda (+256)</option>
		<!-- <option data-countryCode="GB" value="44">UK (+44)</option> -->
		<option data-countryCode="UA" value="380">Ukraine (+380)</option>
		<option data-countryCode="AE" value="971">United Arab Emirates (+971)</option>
		<option data-countryCode="UY" value="598">Uruguay (+598)</option>
		<!-- <option data-countryCode="US" value="1">USA (+1)</option> -->
		<option data-countryCode="UZ" value="7">Uzbekistan (+7)</option>
		<option data-countryCode="VU" value="678">Vanuatu (+678)</option>
		<option data-countryCode="VA" value="379">Vatican City (+379)</option>
		<option data-countryCode="VE" value="58">Venezuela (+58)</option>
		<option data-countryCode="VN" value="84">Vietnam (+84)</option>
		<option data-countryCode="VG" value="84">Virgin Islands - British (+1284)</option>
		<option data-countryCode="VI" value="84">Virgin Islands - US (+1340)</option>
		<option data-countryCode="WF" value="681">Wallis &amp; Futuna (+681)</option>
		<option data-countryCode="YE" value="969">Yemen (North)(+969)</option>
		<option data-countryCode="YE" value="967">Yemen (South)(+967)</option>
		<option data-countryCode="ZM" value="260">Zambia (+260)</option>
		<option data-countryCode="ZW" value="263">Zimbabwe (+263)</option>
	</optgroup>
</select>
						</div>
                        <div class="col-sm-8 col-lg-8">
						    
                            <input type='text' id='txtPhone' class="mblnum" maxlength="10" />
                            <button id="sendotp" type="button" class="btnSubmit icon" value="Send OTP" ><i
                                    class="fa fa-search fa-1x"></i>

                            </button>
                            <span id="spnPhoneStatus"></span>
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-sm-4 col-lg-4">
                            <span class="lablembl">Varify OTP:</span>
                        </div>
                        <div class="col-sm-6 col-lg-6">
                            <input type='text' id='txtPhone' class="mblnum" maxlength="10" />

                        </div>
                    </div>
                    <div class="row" style="float:right">
                        <div class="col-sm-12 col-lg-12">
                            <button type="button" class="btnSubmit icon ">Validation</button>

                        </div>

                    </div>


                </form>
            </div>
            <div class="col-sm-3 col-lg-3">
            </div>
        </div>

        <br><br><br>

      
    </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btncss" data-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>
		
		

    


    <div class="container">
        <h2>Campaign List</h2>
        <!-- Button to Open the Modal -->
        

        <!-- The Modal -->
        <div class="modal" id="Application">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Campaign List</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body">
					<table cellspacing='0' class='table display data-table text-nowrap' style='width: 100%; font-size: 16px;'>
                    <thead>
                    <tr class='head'>
                    <th>
                    Campaign Name
                    </th>
                    <th>
                    Academic Year
                    </th>
					
					 <th>
                    Start Date
                    </th>
					 <th>
                    End date
                    </th>
					 <th>
                    Action
                    </th>
					 </tr>
					</thead>
					 
						<?php  foreach($campaign as $row)
						 {
							 
						echo "<tr>";
							echo '<td>';
							echo $row["name"];
							echo '</a></td>';
							echo '<td>';
							echo $row['academic_year'];
							echo '</td>';
							
							echo '<td>';
							echo $row['start_date'];
							echo '</td>';
							echo '<td>';
							echo $row['end_date'];
							echo '</td>';
							echo '<td>';
							echo ' <a href="application_page.php?url_id='.$row['id'].'" title="'.$row['id'].'"  class="btnShow btn btn-info btn-lg" type="button" id="btnShow"  style="font-size: 14px;" >Apply Now</a>';
							echo '</td>';
                        echo '</tr>';
                    
						
						?>	 
						 <?php }?>	
						 </table>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btncss" data-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>
		
		

    </div>
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
        border: 1px solid #afadad;
        border-radius: 4px;
        margin-left: 29px;
        width: 257px;
    }

    .lablembl {
        float: right;
        color: #292929;
        font-weight: normal;
	    margin-top: 15px;
    }

    .formcss {
        border: 1px solid #afadad;
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
    </script>


</body>
</body>

</html>
