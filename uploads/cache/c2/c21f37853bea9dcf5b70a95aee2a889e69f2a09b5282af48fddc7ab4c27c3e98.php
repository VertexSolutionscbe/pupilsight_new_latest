<?php

/* index_admin.twig.html */
class __TwigTemplate_0e26bd8c61b5c60a8dfbfc18576013cb167e701a311a2c0ef74a9d1c722d2326 extends Twig_Template
{
    private $source;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = array(
            'page' => array($this, 'block_page'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!doctype html>
<html class=\"no-js\" lang=\"\">

<head>
    <meta charset=\"utf-8\" />
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, viewport-fit=cover\" />
    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\" />
    <title>Pupilpod</title>
    <link rel=\"preconnect\" href=\"https://fonts.gstatic.com/\" crossorigin>
    <meta name=\"msapplication-TileColor\" content=\"#206bc4\" />
    <meta name=\"theme-color\" content=\"#206bc4\" />
    <meta name=\"apple-mobile-web-app-status-bar-style\" content=\"black-translucent\" />
    <meta name=\"apple-mobile-web-app-capable\" content=\"yes\" />
    <meta name=\"mobile-web-app-capable\" content=\"yes\" />
    <meta name=\"HandheldFriendly\" content=\"True\" />
    <meta name=\"MobileOptimized\" content=\"320\" />
    <meta name=\"robots\" content=\"noindex,nofollow,noarchive\" />
    <link rel=\"icon\" href=\"./favicon.ico\" type=\"image/x-icon\" />
    <link rel=\"shortcut icon\" href=\"./favicon.ico\" type=\"image/x-icon\" />

    <!-- CSS files -->
    <link rel=\"stylesheet\" href=\"//cdn.materialdesignicons.com/5.0.45/css/materialdesignicons.min.css\">
    <link rel=\"stylesheet\" href=\"";
        // line 23
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/fullcalendar.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 24
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/jquery.dataTables.min.css?v=1.0\" />
    <link rel=\"stylesheet\" href=\"";
        // line 25
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/bootstrap-multiselect.css?v=1.0\" type=\"text/css\"
        media=\"all\" />
    <!--
<link rel=\"stylesheet\" href=\"";
        // line 28
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-ui/css/blitzer/jquery-ui.css?v=1.0\" type=\"text/css\" media=\"all\" />
    -->

    <link rel=\"stylesheet\" href=\"";
        // line 31
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-timepicker/jquery.timepicker.css?v=1.0\"
        type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 33
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/thickbox.css?v=1.0\" type=\"text/css\"
        media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 35
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/normalize.css?v=1.0\" type=\"text/css\" media=\"all\" />

    <link href=\"";
        // line 37
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/selectize.css\" rel=\"stylesheet\" />
    <link href=\"";
        // line 38
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/tabler.css\" rel=\"stylesheet\" />
    <link href=\"";
        // line 39
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/dev.css\" rel=\"stylesheet\" />
    <link href=\"";
        // line 40
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/select2.min.css\" rel=\"stylesheet\" />

    <!-- Libs JS -->
    <script src=\"";
        // line 43
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js\"></script>
    <script src=\"";
        // line 44
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery/dist/jquery-3.5.1.min.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 45
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery/jquery-migrate.min.js?v=1.0\"></script>
    <script src=\"";
        // line 46
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-ui/js/jquery-ui.min.js?v=1.0\"></script>
    <script src=\"";
        // line 47
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.dataTables.min.js?v=1.0v=1.0\"></script>
    <script src=\"";
        // line 48
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-timepicker/jquery.timepicker.min.js?v=1.0\"></script>
    <script src=\"";
        // line 49
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/livevalidation/livevalidation_standalone.compressed.js\"></script>


    <script src=\"";
        // line 52
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/core.js\"></script>
    <script src=\"";
        // line 53
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/main.js\"></script>
    <script src=\"";
        // line 54
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.table2excel.js\"></script>
    <script
        type=\"text/javascript\">var tb_pathToImage = \"";
        // line 56
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/loadingAnimation.gif\";</script>
    <script type=\"text/javascript\" src=\"";
        // line 57
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/tinymce/tinymce.min.js?v=1.0\"></script>
    <script type=\"text/javascript\"
        src=\"";
        // line 59
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-tokeninput/src/jquery.tokeninput.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 60
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/moment.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 61
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/fullcalendar.min.js?v=1.0\"></script>

    <script type=\"text/javascript\" src=\"";
        // line 63
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/bootstrap-multiselect.js?v=1.0\"></script>
    <script src=\"";
        // line 64
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/selectize.min.js\"></script>
    <script src=\"";
        // line 65
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/tabler.min.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 66
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/thickbox-compressed.js?v=1.0\"></script>
    <script src=\"";
        // line 67
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/select2.js\"></script>
    <!--
    <link rel=\"stylesheet\" href=\"";
        // line 69
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/bootstrap.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    
    
    
    
    

    <link rel=\"stylesheet\" href=\"";
        // line 76
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/all.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 77
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/fonts/flaticon.css?v=1.0\" type=\"text/css\" media=\"all\" />

    <link rel=\"stylesheet\" href=\"";
        // line 79
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/animate.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 80
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/sortable/css/Sortable.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 81
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/style.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 82
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/jquery.dropdown.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/themes/Default/css/main.css?v=1.0.00\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/resources/assets/css/theme.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/resources/assets/css/core.min.css?v=1.0\" 

    <script type=\"text/javascript\" src=\"";
        // line 87
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/popper.min.js?v=1.0\"></script>
    
    <script type=\"text/javascript\" src=\"";
        // line 89
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/chained/jquery.chained.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 90
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/modernizr-3.6.0.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 91
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.dropdown.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 92
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jszip.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 93
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/plugins.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 94
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.counterup.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 95
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.waypoints.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 96
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.scrollUp.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 97
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/Chart.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 98
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-jslatex/jquery.jslatex.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 99
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-form/jquery.form.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 100
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-ui/i18n/jquery.ui.datepicker-en-GB.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 101
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-autosize/jquery.autosize.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 102
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-sessionTimeout/jquery.sessionTimeout.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 103
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/sortable/js/Sortable.js?v=1.0\"></script>
-->

    <style>
        body {
            display: none;
        }
    </style>
</head>

<body id='chkCounterSession' data-val='";
        // line 113
        echo twig_escape_filter($this->env, ($context["counterid"] ?? null), "html", null, true);
        echo "' class='antialiased'>
    <!-- Preloader Start Here -->
    <div id=\"preloader\" style=\"display:none;\"></div>
    <!-- Preloader End Here -->

    <div class=\"page\">
        <header class=\"navbar navbar-expand-md navbar-light\">
            <div class=\"container-fluid\">
                <button class=\"navbar-toggler\" type=\"button\" data-toggle=\"collapse\" data-target=\"#navbar-menu\">
                    <span class=\"navbar-toggler-icon\"></span>
                </button>
                <a href=\"";
        // line 124
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/index.php\"
                    class=\"navbar-brand navbar-brand-autodark d-none-navbar-horizontal pr-0 pr-md-3\">
                    <img src=\"";
        // line 126
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/";
        echo twig_escape_filter($this->env, (((isset($context["organisationLogo"]) || array_key_exists("organisationLogo", $context))) ? (_twig_default_filter(($context["organisationLogo"] ?? null), " /themes/Default/img/logo.png ")) : (" /themes/Default/img/logo.png ")), "html", null, true);
        echo "\"
                        alt=\"Tabler\" class=\"navbar-brand-image\">
                </a>
                <div class=\"navbar-nav flex-row order-md-last\">
                    <div class=\"nav-item dropdown d-none d-md-flex mr-3\">
                        <a href=\"#\" class=\"nav-link px-0\" data-toggle=\"dropdown\" tabindex=\"-1\">
                            <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\"
                                viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\"
                                stroke-linecap=\"round\" stroke-linejoin=\"round\">
                                <path stroke=\"none\" d=\"M0 0h24v24H0z\" />
                                <path
                                    d=\"M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6\" />
                                <path d=\"M9 17v1a3 3 0 0 0 6 0v-1\" /></svg>
                            <span class=\"_badge bg-red\"></span>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-card\">
                            <div class=\"card\">
                                <div class=\"card-body\">
                                    Notifications
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class=\"nav-item dropdown\">
                        <a href=\"#\" class=\"nav-link d-flex lh-1 text-reset p-0\" data-toggle=\"dropdown\"
                            aria-expanded=\"false\">
                            <span class=\"avatar\" style=\"background-image: url(./static/avatars/000m.jpg)\"></span>
                            <div class=\"d-none d-xl-block pl-2\">
                                <div>";
        // line 154
        echo ($context["uname"] ?? null);
        echo "</div>
                                <div class=\"mt-1 small text-muted\">Administrator</div>
                            </div>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right\">
                            <a class=\"dropdown-item\" href=\"";
        // line 159
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/index.php?q=preferences.php\">
                                <span class=\"mdi mdi-account-cog-outline mr-2\"></span>
                                Preferences
                            </a>
                            <div class=\"dropdown-divider\"></div>
                            <a class=\"dropdown-item\" href=\"";
        // line 164
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/logout.php\">
                                <span class=\"mdi mdi-logout-variant mr-2\"></span>
                                Logout</a>
                        </div>
                    </div>
                </div>

                <div class=\"collapse navbar-collapse\" id=\"navbar-menu\">
                    <div class=\"d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center\">
                        <ul class=\"navbar-nav\">

                            ";
        // line 175
        $context["comActive"] = "";
        // line 176
        echo "                            ";
        if ((($context["currentModule"] ?? null) == "Dashboard")) {
            // line 177
            echo "                            ";
            $context["comActive"] = "active";
            // line 178
            echo "                            ";
        }
        // line 179
        echo "
                            <li class=\"nav-item ";
        // line 180
        echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
        echo "\">
                                <a class=\"nav-link chkCounter\" href=\"";
        // line 181
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/index.php\">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-view-dashboard\"></span>
                                    <span class=\"nav-link-title\">
                                        Dashboard
                                    </span>
                                </a>
                            </li>

                            ";
        // line 190
        $context["comActive"] = "";
        // line 191
        echo "                            ";
        if ((($context["currentModule"] ?? null) == "Timetable Admin")) {
            // line 192
            echo "                            ";
            $context["comActive"] = "active";
            // line 193
            echo "                            ";
        }
        // line 194
        echo "
                            <li class=\"nav-item ";
        // line 195
        echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
        echo "\">
                                <a class=\"nav-link chkCounter\" href=\"";
        // line 196
        echo twig_escape_filter($this->env, (($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 = (($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a = (($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 = ($context["menuMain"] ?? null)) && is_array($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57) || $__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 instanceof ArrayAccess ? ($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57["TimeTable"] ?? null) : null)) && is_array($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a) || $__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a instanceof ArrayAccess ? ($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a[0] ?? null) : null)) && is_array($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5) || $__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 instanceof ArrayAccess ? ($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5["url"] ?? null) : null), "html", null, true);
        echo "\">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-calendar-month\"></span>
                                    <span class=\"nav-link-title\">
                                        Time-Table
                                    </span>
                                </a>
                            </li>


                            ";
        // line 206
        $context["comActive"] = "";
        // line 207
        echo "                            ";
        if ((($context["currentModule"] ?? null) == "Messenger")) {
            // line 208
            echo "                            ";
            $context["comActive"] = "active";
            // line 209
            echo "                            ";
        }
        // line 210
        echo "
                            <li class=\"nav-item ";
        // line 211
        echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
        echo "\">
                                <a class=\"nav-link chkCounter\" href=\"";
        // line 212
        echo twig_escape_filter($this->env, (($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 = (($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217 = (($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105 = ($context["menuMain"] ?? null)) && is_array($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105) || $__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105 instanceof ArrayAccess ? ($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105["Communication"] ?? null) : null)) && is_array($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217) || $__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217 instanceof ArrayAccess ? ($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217[0] ?? null) : null)) && is_array($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9) || $__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 instanceof ArrayAccess ? ($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9["url"] ?? null) : null), "html", null, true);
        echo "\">
                                    <span class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-handshake\"></span>
                                    <span class=\"nav-link-title\">
                                        Communication
                                    </span>
                                </a>
                            </li>


                            ";
        // line 221
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["menuMain"] ?? null));
        foreach ($context['_seq'] as $context["categoryName"] => $context["items"]) {
            // line 222
            echo "
                            ";
            // line 223
            if (($context["categoryName"] == "People")) {
                // line 224
                echo "
                            ";
                // line 225
                $context["comActive"] = "";
                // line 226
                echo "                            ";
                if ((($context["currentModule"] ?? null) == "People")) {
                    // line 227
                    echo "                            ";
                    $context["comActive"] = "active";
                    // line 228
                    echo "                            ";
                }
                // line 229
                echo "
                            <li class=\"nav-item dropdown ";
                // line 230
                echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
                echo "\">
                                <a class=\"nav-link dropdown-toggle chkCounter\" href=\"#navbar-base\"
                                    data-toggle=\"dropdown\" role=\"button\" aria-expanded=\"false\">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block ";
                // line 234
                echo twig_escape_filter($this->env, (($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779 = ($context["menuMainIcon"] ?? null)) && is_array($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779) || $__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779 instanceof ArrayAccess ? ($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779[$context["categoryName"]] ?? null) : null), "html", null, true);
                echo "\"></span>
                                    <span class=\"nav-link-title\">
                                        ";
                // line 236
                echo twig_escape_filter($this->env, $context["categoryName"], "html", null, true);
                echo "
                                    </span>
                                </a>

                                <ul class=\"dropdown-menu\">
                                    ";
                // line 241
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($context["items"]);
                foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                    // line 242
                    echo "                                    <li>
                                        <a class=\"dropdown-item chkCounter\" href=\"";
                    // line 243
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                    echo "\">
                                            ";
                    // line 244
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                    echo "
                                        </a>
                                    </li>
                                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 248
                echo "                                </ul>

                            </li>
                            ";
            }
            // line 252
            echo "                            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['categoryName'], $context['items'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 253
        echo "
                            <li class=\"nav-item\">
                                <a class=\"nav-link\" href=\"http://pupilsight.pupilpod.in/index.php?r=site%2Flogin\"
                                    target='_blank'>
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-chart-bar-stacked\"></span>
                                    <span class=\"nav-link-title\">
                                        Analytics
                                    </span>
                                </a>
                            </li>

                            <li class=\"nav-item\">
                                <form action=\"yearSwitcherProcess.php\" method=\"post\">
                                    <div style=\"display:inline-flex;\">
                                        <select name=\"pupilsightSchoolYearID\"
                                            style=\"float:left;width: 150px;margin-right: 10px;\" id=\"academicYearChange\">
                                            <option value=\"\">Select Academic Year</option>
                                            ";
        // line 271
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["academicYear"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["ay"]) {
            // line 272
            echo "                                            ";
            if ((twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()) == ($context["pupilsightSchoolYearID"] ?? null))) {
                // line 273
                echo "                                            <option value='";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()), "html", null, true);
                echo "' selected>";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "name", array()), "html", null, true);
                echo "
                                            </option>
                                            ";
            } else {
                // line 276
                echo "                                            <option value='";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()), "html", null, true);
                echo "'>";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "name", array()), "html", null, true);
                echo "
                                            </option>
                                            ";
            }
            // line 279
            echo "                                            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['ay'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 280
        echo "                                        </select>
                                        <button type=\"submit\" style=\"width:120px;\" class=\"btn btn-primary\">Change
                                            Year</a></div>
                                </form>
                            </li>


                        </ul>
                        <!--
                        <div
                            class=\"ml-md-auto pl-md-4 py-2 py-md-0 mr-md-4 order-first order-md-last flex-grow-1 flex-md-grow-0\">
                            <form action=\".\" method=\"get\">
                                <div class=\"input-icon\">
                                    <span class=\"input-icon-addon\">
                                        <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\"
                                            viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\"
                                            stroke-linecap=\"round\" stroke-linejoin=\"round\">
                                            <path stroke=\"none\" d=\"M0 0h24v24H0z\" />
                                            <circle cx=\"10\" cy=\"10\" r=\"7\" />
                                            <line x1=\"21\" y1=\"21\" x2=\"15\" y2=\"15\" /></svg>
                                    </span>
                                    <input type=\"text\" class=\"form-control\" placeholder=\"Search…\">
                                </div>
                            </form>
                        </div>
                        -->
                    </div>
                </div>
            </div>
        </header>

        <div class=\"navbar-expand-md\">
            <div class=\"collapse navbar-collapse\" id=\"navbar-menu\">
                <div class=\"navbar navbar-light\">
                    <div class=\"container-fluid\">
                        <ul class=\"navbar-nav\">
                            ";
        // line 316
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["menuMain"] ?? null));
        foreach ($context['_seq'] as $context["categoryName"] => $context["items"]) {
            // line 317
            echo "
                            ";
            // line 318
            if (((($context["categoryName"] != "People") && ($context["categoryName"] != "TimeTable")) && ($context["categoryName"] != "Communication"))) {
                // line 319
                echo "
                            ";
                // line 320
                $context["menuSelect"] = "";
                // line 321
                echo "                            ";
                if (($context["categoryName"] == ($context["currentModule"] ?? null))) {
                    // line 322
                    echo "                            ";
                    $context["menuSelect"] = "active";
                    // line 323
                    echo "                            ";
                }
                // line 324
                echo "
                            ";
                // line 325
                $context["dropmenu"] = "";
                // line 326
                echo "                            ";
                $context["dropdownToggle"] = "";
                // line 327
                echo "                            ";
                $context["navlink"] = "#navbar-base";
                // line 328
                echo "                            ";
                $context["data_toggle"] = "";
                // line 329
                echo "
                            ";
                // line 330
                if ((twig_length_filter($this->env, $context["items"]) > 1)) {
                    // line 331
                    echo "                            ";
                    $context["dropmenu"] = "dropdown";
                    // line 332
                    echo "                            ";
                    $context["dropdownToggle"] = "dropdown-toggle";
                    // line 333
                    echo "                            ";
                    $context["data_toggle"] = "data-toggle=dropdown role=button aria-expanded=false";
                    // line 334
                    echo "                            ";
                } else {
                    // line 335
                    echo "                            ";
                    $context["navlink"] = twig_get_attribute($this->env, $this->source, (($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1 = $context["items"]) && is_array($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1) || $__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1 instanceof ArrayAccess ? ($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1[0] ?? null) : null), "url", array());
                    // line 336
                    echo "                            ";
                }
                // line 337
                echo "
                            <li class=\"nav-item ";
                // line 338
                echo twig_escape_filter($this->env, ($context["dropmenu"] ?? null), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, ($context["menuSelect"] ?? null), "html", null, true);
                echo "\">
                                <a class=\"nav-link ";
                // line 339
                echo twig_escape_filter($this->env, ($context["dropdownToggle"] ?? null), "html", null, true);
                echo " chkCounter\" href=\"";
                echo twig_escape_filter($this->env, ($context["navlink"] ?? null), "html", null, true);
                echo "\"
                                    ";
                // line 340
                echo twig_escape_filter($this->env, ($context["data_toggle"] ?? null), "html", null, true);
                echo ">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block ";
                // line 342
                echo twig_escape_filter($this->env, (($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0 = ($context["menuMainIcon"] ?? null)) && is_array($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0) || $__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0 instanceof ArrayAccess ? ($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0[$context["categoryName"]] ?? null) : null), "html", null, true);
                echo "\"></span>
                                    <span class=\"nav-link-title\">
                                        ";
                // line 344
                echo twig_escape_filter($this->env, $context["categoryName"], "html", null, true);
                echo "
                                    </span>
                                </a>
                                ";
                // line 347
                if ((($context["dropmenu"] ?? null) == "dropdown")) {
                    // line 348
                    echo "
                                ";
                    // line 349
                    $context["menucol"] = "";
                    // line 350
                    echo "                                ";
                    if (twig_get_attribute($this->env, $this->source, (($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866 = $context["items"]) && is_array($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866) || $__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866 instanceof ArrayAccess ? ($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866[0] ?? null) : null), "col", array())) {
                        // line 351
                        echo "                                ";
                        $context["menucol"] = twig_get_attribute($this->env, $this->source, (($__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f = $context["items"]) && is_array($__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f) || $__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f instanceof ArrayAccess ? ($__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f[0] ?? null) : null), "col", array());
                        // line 352
                        echo "                                ";
                    }
                    // line 353
                    echo "
                                <ul class=\"dropdown-menu ";
                    // line 354
                    echo twig_escape_filter($this->env, ($context["menucol"] ?? null), "html", null, true);
                    echo "\">
                                    ";
                    // line 355
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($context["items"]);
                    foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                        // line 356
                        echo "                                    <li>
                                        ";
                        // line 357
                        if (twig_get_attribute($this->env, $this->source, $context["item"], "list", array())) {
                            // line 358
                            echo "                                        <span class=\"dropdown-item mdi mdi-arrow-down\">
                                            <strong class='ml-1 badge bg-indigo'>";
                            // line 359
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                            echo "</strong>
                                        </span>
                                        <div class='ml-2'>
                                            ";
                            // line 362
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["item"], "list", array()));
                            foreach ($context['_seq'] as $context["_key"] => $context["sitem"]) {
                                // line 363
                                echo "                                            <div>
                                                ";
                                // line 364
                                if (twig_get_attribute($this->env, $this->source, $context["sitem"], "name", array())) {
                                    // line 365
                                    echo "                                                <a class=\"dropdown-item chkCounter\" href=\"";
                                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["sitem"], "url", array()), "html", null, true);
                                    echo "\">
                                                    <span class=\"badge bg-indigo\"></span>
                                                    <span class='ml-2'>";
                                    // line 367
                                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["sitem"], "name", array()), "html", null, true);
                                    echo "</span>
                                                </a>
                                                ";
                                } else {
                                    // line 370
                                    echo "                                                &nbsp;
                                                ";
                                }
                                // line 372
                                echo "                                            </div>
                                            ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['sitem'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 374
                            echo "                                        </div>
                                        ";
                        } else {
                            // line 376
                            echo "                                        <a class=\"dropdown-item chkCounter\" href=\"";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                            echo "\">
                                            ";
                            // line 377
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                            echo "
                                        </a>
                                        ";
                        }
                        // line 380
                        echo "                                    </li>
                                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 382
                    echo "                                </ul>
                                ";
                }
                // line 384
                echo "
                            </li>
                            ";
            }
            // line 387
            echo "                            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['categoryName'], $context['items'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 388
        echo "                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id='content' class=\"content\">
            <div class=\"container-fluid\">
                <div class='row'>
                    <div class='col-12'>
                        <!-- Page title -->
                        <div class=\"page-header\">
                            <div class=\"row align-items-center\">
                                <div class=\"col-auto\">
                                    <h2 class=\"page-title\">
                                        ";
        // line 403
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            // line 404
            echo "                                        ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array()));
            $context['loop'] = array(
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            );
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["title"] => $context["src"]) {
                // line 405
                echo "                                        ";
                if (twig_get_attribute($this->env, $this->source, $context["loop"], "last", array())) {
                    // line 406
                    echo "                                        ";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "
                                        ";
                }
                // line 408
                echo "                                        ";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['length'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['title'], $context['src'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 409
            echo "                                        ";
        }
        // line 410
        echo "                                    </h2>
                                </div>
                                <!-- Page title actions -->
                                <div class=\"col-auto ml-auto d-print-none\">
                                    <ol class=\"breadcrumb\" aria-label=\"breadcrumbs\">


                                        ";
        // line 417
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            // line 418
            echo "
                                        ";
            // line 419
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array()));
            $context['loop'] = array(
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            );
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["title"] => $context["src"]) {
                echo " ";
                if (twig_get_attribute($this->env, $this->source, $context["loop"], "last", array())) {
                    // line 420
                    echo "                                        <li class=\"breadcrumb-item active\" aria-current=\"page\">";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "</li>
                                        ";
                } elseif (((twig_get_attribute($this->env, $this->source,                 // line 421
$context["loop"], "revindex", array()) > 5) && (twig_get_attribute($this->env, $this->source, $context["loop"], "index", array()) != 1))) {
                    // line 422
                    echo "                                        <li class=\"breadcrumb-item\">
                                            <a href=\"";
                    // line 423
                    echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                    echo "/";
                    echo twig_escape_filter($this->env, $context["src"], "html", null, true);
                    echo "\">...</a>
                                        </li>
                                        ";
                } else {
                    // line 426
                    echo "                                        <li class=\"breadcrumb-item\">
                                            <a href=\"";
                    // line 427
                    echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                    echo "/";
                    echo twig_escape_filter($this->env, $context["src"], "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "</a>
                                        </li>
                                        ";
                }
                // line 429
                echo " ";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['length'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['title'], $context['src'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 430
            echo "
                                        ";
        }
        // line 431
        echo " ";
        $this->displayBlock('page', $context, $blocks);
        // line 455
        echo "                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Page Area End Here -->
    </div>


    <footer class=\"footer footer-transparent\">
        <div class=\"container\">
            <div class=\"row text-center align-items-center flex-row-reverse\">
                <div class=\"col-12 col-lg-auto mt-3 mt-lg-0\">
                    Copyright © 2020
                    <a href=\".\" class=\"link-secondary\">Pupilpod</a>.
                    All rights reserved.
                </div>
            </div>
        </div>
    </footer>
    </div>
    </div>

    </div>
    ";
        // line 482
        echo twig_include($this->env, $context, "alert.twig.html");
        echo "


    <script>
        document.body.style.display = \"block\";
    </script>

</body>

</html>";
    }

    // line 431
    public function block_page($context, array $blocks = array())
    {
        echo " ";
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            echo " ";
        }
        // line 432
        echo "                                    </ol>
                                </div>
                            </div>
                        </div>
                        <!--
                        <div class=\"row\">
                            <div id=\"content\" class=\"col-12 col-xl-12\" style=\"overflow: scroll;\">
                                <iframe id='iframeMaster' src=\"";
        // line 439
        echo twig_escape_filter($this->env, ($context["framesrc"] ?? null), "html", null, true);
        echo "\" style=\"border:0;width:100%;height:100vh;padding-bottom:20px;\"></iframe>
                            </div>
                        </div>
                        -->

                        <div class='card'>
                            <div class='card-body'>
                                ";
        // line 446
        if (($context["submenu"] ?? null)) {
            // line 447
            echo "                                <div class=\"mb-2\">
                                    ";
            // line 448
            echo twig_include($this->env, $context, "navigation.twig.html");
            echo "
                                </div>
                                ";
        }
        // line 451
        echo "
                                ";
        // line 452
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "alerts", array()));
        foreach ($context['_seq'] as $context["type"] => $context["alerts"]) {
            echo " ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($context["alerts"]);
            foreach ($context['_seq'] as $context["_key"] => $context["text"]) {
                // line 453
                echo "                                <div class=\"";
                echo twig_escape_filter($this->env, $context["type"], "html", null, true);
                echo "\">";
                echo $context["text"];
                echo "</div>
                                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['text'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 454
            echo " ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['type'], $context['alerts'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        echo " ";
        echo twig_join_filter(($context["content"] ?? null), "
");
        echo " ";
    }

    public function getTemplateName()
    {
        return "index_admin.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  1092 => 454,  1081 => 453,  1073 => 452,  1070 => 451,  1064 => 448,  1061 => 447,  1059 => 446,  1049 => 439,  1040 => 432,  1033 => 431,  1019 => 482,  990 => 455,  987 => 431,  983 => 430,  969 => 429,  959 => 427,  956 => 426,  948 => 423,  945 => 422,  943 => 421,  938 => 420,  919 => 419,  916 => 418,  914 => 417,  905 => 410,  902 => 409,  888 => 408,  882 => 406,  879 => 405,  861 => 404,  859 => 403,  842 => 388,  836 => 387,  831 => 384,  827 => 382,  820 => 380,  814 => 377,  809 => 376,  805 => 374,  798 => 372,  794 => 370,  788 => 367,  782 => 365,  780 => 364,  777 => 363,  773 => 362,  767 => 359,  764 => 358,  762 => 357,  759 => 356,  755 => 355,  751 => 354,  748 => 353,  745 => 352,  742 => 351,  739 => 350,  737 => 349,  734 => 348,  732 => 347,  726 => 344,  721 => 342,  716 => 340,  710 => 339,  704 => 338,  701 => 337,  698 => 336,  695 => 335,  692 => 334,  689 => 333,  686 => 332,  683 => 331,  681 => 330,  678 => 329,  675 => 328,  672 => 327,  669 => 326,  667 => 325,  664 => 324,  661 => 323,  658 => 322,  655 => 321,  653 => 320,  650 => 319,  648 => 318,  645 => 317,  641 => 316,  603 => 280,  597 => 279,  588 => 276,  579 => 273,  576 => 272,  572 => 271,  552 => 253,  546 => 252,  540 => 248,  530 => 244,  526 => 243,  523 => 242,  519 => 241,  511 => 236,  506 => 234,  499 => 230,  496 => 229,  493 => 228,  490 => 227,  487 => 226,  485 => 225,  482 => 224,  480 => 223,  477 => 222,  473 => 221,  461 => 212,  457 => 211,  454 => 210,  451 => 209,  448 => 208,  445 => 207,  443 => 206,  430 => 196,  426 => 195,  423 => 194,  420 => 193,  417 => 192,  414 => 191,  412 => 190,  400 => 181,  396 => 180,  393 => 179,  390 => 178,  387 => 177,  384 => 176,  382 => 175,  368 => 164,  360 => 159,  352 => 154,  319 => 126,  314 => 124,  300 => 113,  287 => 103,  283 => 102,  279 => 101,  275 => 100,  271 => 99,  267 => 98,  263 => 97,  259 => 96,  255 => 95,  251 => 94,  247 => 93,  243 => 92,  239 => 91,  235 => 90,  231 => 89,  226 => 87,  218 => 82,  214 => 81,  210 => 80,  206 => 79,  201 => 77,  197 => 76,  187 => 69,  182 => 67,  178 => 66,  174 => 65,  170 => 64,  166 => 63,  161 => 61,  157 => 60,  153 => 59,  148 => 57,  144 => 56,  139 => 54,  135 => 53,  131 => 52,  125 => 49,  121 => 48,  117 => 47,  113 => 46,  109 => 45,  105 => 44,  101 => 43,  95 => 40,  91 => 39,  87 => 38,  83 => 37,  78 => 35,  73 => 33,  68 => 31,  62 => 28,  56 => 25,  52 => 24,  48 => 23,  24 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("<!doctype html>
<html class=\"no-js\" lang=\"\">

<head>
    <meta charset=\"utf-8\" />
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, viewport-fit=cover\" />
    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\" />
    <title>Pupilpod</title>
    <link rel=\"preconnect\" href=\"https://fonts.gstatic.com/\" crossorigin>
    <meta name=\"msapplication-TileColor\" content=\"#206bc4\" />
    <meta name=\"theme-color\" content=\"#206bc4\" />
    <meta name=\"apple-mobile-web-app-status-bar-style\" content=\"black-translucent\" />
    <meta name=\"apple-mobile-web-app-capable\" content=\"yes\" />
    <meta name=\"mobile-web-app-capable\" content=\"yes\" />
    <meta name=\"HandheldFriendly\" content=\"True\" />
    <meta name=\"MobileOptimized\" content=\"320\" />
    <meta name=\"robots\" content=\"noindex,nofollow,noarchive\" />
    <link rel=\"icon\" href=\"./favicon.ico\" type=\"image/x-icon\" />
    <link rel=\"shortcut icon\" href=\"./favicon.ico\" type=\"image/x-icon\" />

    <!-- CSS files -->
    <link rel=\"stylesheet\" href=\"//cdn.materialdesignicons.com/5.0.45/css/materialdesignicons.min.css\">
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/fullcalendar.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/jquery.dataTables.min.css?v=1.0\" />
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/bootstrap-multiselect.css?v=1.0\" type=\"text/css\"
        media=\"all\" />
    <!--
<link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/libs/jquery-ui/css/blitzer/jquery-ui.css?v=1.0\" type=\"text/css\" media=\"all\" />
    -->

    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/libs/jquery-timepicker/jquery.timepicker.css?v=1.0\"
        type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/libs/thickbox/thickbox.css?v=1.0\" type=\"text/css\"
        media=\"all\" />
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/normalize.css?v=1.0\" type=\"text/css\" media=\"all\" />

    <link href=\"{{ absoluteURL }}/assets/css/selectize.css\" rel=\"stylesheet\" />
    <link href=\"{{ absoluteURL }}/assets/css/tabler.css\" rel=\"stylesheet\" />
    <link href=\"{{ absoluteURL }}/assets/css/dev.css\" rel=\"stylesheet\" />
    <link href=\"{{ absoluteURL }}/assets/css/select2.min.css\" rel=\"stylesheet\" />

    <!-- Libs JS -->
    <script src=\"{{ absoluteURL }}/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js\"></script>
    <script src=\"{{ absoluteURL }}/assets/libs/jquery/dist/jquery-3.5.1.min.js\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/libs/jquery/jquery-migrate.min.js?v=1.0\"></script>
    <script src=\"{{ absoluteURL }}/assets/libs/jquery-ui/js/jquery-ui.min.js?v=1.0\"></script>
    <script src=\"{{ absoluteURL }}/assets/js/jquery.dataTables.min.js?v=1.0v=1.0\"></script>
    <script src=\"{{ absoluteURL }}/assets/libs/jquery-timepicker/jquery.timepicker.min.js?v=1.0\"></script>
    <script src=\"{{ absoluteURL }}/assets/libs/livevalidation/livevalidation_standalone.compressed.js\"></script>


    <script src=\"{{ absoluteURL }}/assets/js/core.js\"></script>
    <script src=\"{{ absoluteURL }}/assets/js/main.js\"></script>
    <script src=\"{{ absoluteURL }}/assets/js/jquery.table2excel.js\"></script>
    <script
        type=\"text/javascript\">var tb_pathToImage = \"{{ absoluteURL }}/assets/libs/thickbox/loadingAnimation.gif\";</script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/libs/tinymce/tinymce.min.js?v=1.0\"></script>
    <script type=\"text/javascript\"
        src=\"{{ absoluteURL }}/assets/libs/jquery-tokeninput/src/jquery.tokeninput.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/moment.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/fullcalendar.min.js?v=1.0\"></script>

    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/bootstrap-multiselect.js?v=1.0\"></script>
    <script src=\"{{ absoluteURL }}/assets/js/selectize.min.js\"></script>
    <script src=\"{{ absoluteURL }}/assets/js/tabler.min.js\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/libs/thickbox/thickbox-compressed.js?v=1.0\"></script>
    <script src=\"{{ absoluteURL }}/assets/js/select2.js\"></script>
    <!--
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/bootstrap.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    
    
    
    
    

    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/all.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/fonts/flaticon.css?v=1.0\" type=\"text/css\" media=\"all\" />

    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/animate.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/sortable/css/Sortable.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/style.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/jquery.dropdown.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/themes/Default/css/main.css?v=1.0.00\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/resources/assets/css/theme.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/resources/assets/css/core.min.css?v=1.0\" 

    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/popper.min.js?v=1.0\"></script>
    
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/chained/jquery.chained.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/modernizr-3.6.0.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/jquery.dropdown.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/jszip.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/plugins.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/jquery.counterup.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/jquery.waypoints.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/jquery.scrollUp.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/Chart.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/jquery-jslatex/jquery.jslatex.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/jquery-form/jquery.form.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/jquery-ui/i18n/jquery.ui.datepicker-en-GB.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/jquery-autosize/jquery.autosize.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/jquery-sessionTimeout/jquery.sessionTimeout.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/sortable/js/Sortable.js?v=1.0\"></script>
-->

    <style>
        body {
            display: none;
        }
    </style>
</head>

<body id='chkCounterSession' data-val='{{ counterid }}' class='antialiased'>
    <!-- Preloader Start Here -->
    <div id=\"preloader\" style=\"display:none;\"></div>
    <!-- Preloader End Here -->

    <div class=\"page\">
        <header class=\"navbar navbar-expand-md navbar-light\">
            <div class=\"container-fluid\">
                <button class=\"navbar-toggler\" type=\"button\" data-toggle=\"collapse\" data-target=\"#navbar-menu\">
                    <span class=\"navbar-toggler-icon\"></span>
                </button>
                <a href=\"{{ absoluteURL }}/index.php\"
                    class=\"navbar-brand navbar-brand-autodark d-none-navbar-horizontal pr-0 pr-md-3\">
                    <img src=\"{{ absoluteURL }}/{{ organisationLogo|default(\" /themes/Default/img/logo.png \") }}\"
                        alt=\"Tabler\" class=\"navbar-brand-image\">
                </a>
                <div class=\"navbar-nav flex-row order-md-last\">
                    <div class=\"nav-item dropdown d-none d-md-flex mr-3\">
                        <a href=\"#\" class=\"nav-link px-0\" data-toggle=\"dropdown\" tabindex=\"-1\">
                            <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\"
                                viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\"
                                stroke-linecap=\"round\" stroke-linejoin=\"round\">
                                <path stroke=\"none\" d=\"M0 0h24v24H0z\" />
                                <path
                                    d=\"M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6\" />
                                <path d=\"M9 17v1a3 3 0 0 0 6 0v-1\" /></svg>
                            <span class=\"_badge bg-red\"></span>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-card\">
                            <div class=\"card\">
                                <div class=\"card-body\">
                                    Notifications
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class=\"nav-item dropdown\">
                        <a href=\"#\" class=\"nav-link d-flex lh-1 text-reset p-0\" data-toggle=\"dropdown\"
                            aria-expanded=\"false\">
                            <span class=\"avatar\" style=\"background-image: url(./static/avatars/000m.jpg)\"></span>
                            <div class=\"d-none d-xl-block pl-2\">
                                <div>{{ uname|raw }}</div>
                                <div class=\"mt-1 small text-muted\">Administrator</div>
                            </div>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right\">
                            <a class=\"dropdown-item\" href=\"{{ absoluteURL }}/index.php?q=preferences.php\">
                                <span class=\"mdi mdi-account-cog-outline mr-2\"></span>
                                Preferences
                            </a>
                            <div class=\"dropdown-divider\"></div>
                            <a class=\"dropdown-item\" href=\"{{ absoluteURL }}/logout.php\">
                                <span class=\"mdi mdi-logout-variant mr-2\"></span>
                                Logout</a>
                        </div>
                    </div>
                </div>

                <div class=\"collapse navbar-collapse\" id=\"navbar-menu\">
                    <div class=\"d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center\">
                        <ul class=\"navbar-nav\">

                            {% set comActive = \"\" %}
                            {% if currentModule == 'Dashboard' %}
                            {% set comActive = \"active\" %}
                            {% endif%}

                            <li class=\"nav-item {{comActive}}\">
                                <a class=\"nav-link chkCounter\" href=\"{{ absoluteURL }}/index.php\">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-view-dashboard\"></span>
                                    <span class=\"nav-link-title\">
                                        Dashboard
                                    </span>
                                </a>
                            </li>

                            {% set comActive = \"\" %}
                            {% if currentModule == 'Timetable Admin' %}
                            {% set comActive = \"active\" %}
                            {% endif%}

                            <li class=\"nav-item {{ comActive }}\">
                                <a class=\"nav-link chkCounter\" href=\"{{ menuMain['TimeTable'][0]['url'] }}\">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-calendar-month\"></span>
                                    <span class=\"nav-link-title\">
                                        Time-Table
                                    </span>
                                </a>
                            </li>


                            {% set comActive = \"\" %}
                            {% if currentModule == 'Messenger' %}
                            {% set comActive = \"active\" %}
                            {% endif%}

                            <li class=\"nav-item {{ comActive }}\">
                                <a class=\"nav-link chkCounter\" href=\"{{ menuMain['Communication'][0]['url'] }}\">
                                    <span class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-handshake\"></span>
                                    <span class=\"nav-link-title\">
                                        Communication
                                    </span>
                                </a>
                            </li>


                            {% for categoryName, items in menuMain %}

                            {% if categoryName == 'People'%}

                            {% set comActive = \"\" %}
                            {% if currentModule == 'People' %}
                            {% set comActive = \"active\" %}
                            {% endif%}

                            <li class=\"nav-item dropdown {{comActive}}\">
                                <a class=\"nav-link dropdown-toggle chkCounter\" href=\"#navbar-base\"
                                    data-toggle=\"dropdown\" role=\"button\" aria-expanded=\"false\">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block {{ menuMainIcon[categoryName] }}\"></span>
                                    <span class=\"nav-link-title\">
                                        {{ categoryName }}
                                    </span>
                                </a>

                                <ul class=\"dropdown-menu\">
                                    {% for item in items %}
                                    <li>
                                        <a class=\"dropdown-item chkCounter\" href=\"{{ item.url }}\">
                                            {{ item.name }}
                                        </a>
                                    </li>
                                    {% endfor %}
                                </ul>

                            </li>
                            {% endif %}
                            {% endfor %}

                            <li class=\"nav-item\">
                                <a class=\"nav-link\" href=\"http://pupilsight.pupilpod.in/index.php?r=site%2Flogin\"
                                    target='_blank'>
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-chart-bar-stacked\"></span>
                                    <span class=\"nav-link-title\">
                                        Analytics
                                    </span>
                                </a>
                            </li>

                            <li class=\"nav-item\">
                                <form action=\"yearSwitcherProcess.php\" method=\"post\">
                                    <div style=\"display:inline-flex;\">
                                        <select name=\"pupilsightSchoolYearID\"
                                            style=\"float:left;width: 150px;margin-right: 10px;\" id=\"academicYearChange\">
                                            <option value=\"\">Select Academic Year</option>
                                            {% for ay in academicYear %}
                                            {% if ay.pupilsightSchoolYearID == pupilsightSchoolYearID %}
                                            <option value='{{ ay.pupilsightSchoolYearID }}' selected>{{ ay.name }}
                                            </option>
                                            {% else %}
                                            <option value='{{ ay.pupilsightSchoolYearID }}'>{{ ay.name }}
                                            </option>
                                            {% endif %}
                                            {% endfor %}
                                        </select>
                                        <button type=\"submit\" style=\"width:120px;\" class=\"btn btn-primary\">Change
                                            Year</a></div>
                                </form>
                            </li>


                        </ul>
                        <!--
                        <div
                            class=\"ml-md-auto pl-md-4 py-2 py-md-0 mr-md-4 order-first order-md-last flex-grow-1 flex-md-grow-0\">
                            <form action=\".\" method=\"get\">
                                <div class=\"input-icon\">
                                    <span class=\"input-icon-addon\">
                                        <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\"
                                            viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\"
                                            stroke-linecap=\"round\" stroke-linejoin=\"round\">
                                            <path stroke=\"none\" d=\"M0 0h24v24H0z\" />
                                            <circle cx=\"10\" cy=\"10\" r=\"7\" />
                                            <line x1=\"21\" y1=\"21\" x2=\"15\" y2=\"15\" /></svg>
                                    </span>
                                    <input type=\"text\" class=\"form-control\" placeholder=\"Search…\">
                                </div>
                            </form>
                        </div>
                        -->
                    </div>
                </div>
            </div>
        </header>

        <div class=\"navbar-expand-md\">
            <div class=\"collapse navbar-collapse\" id=\"navbar-menu\">
                <div class=\"navbar navbar-light\">
                    <div class=\"container-fluid\">
                        <ul class=\"navbar-nav\">
                            {% for categoryName, items in menuMain %}

                            {% if categoryName != 'People' and categoryName != 'TimeTable' and categoryName != 'Communication' %}

                            {% set menuSelect = ''%}
                            {% if categoryName == currentModule %}
                            {% set menuSelect = 'active'%}
                            {% endif %}

                            {% set dropmenu = ''%}
                            {% set dropdownToggle = '' %}
                            {% set navlink = '#navbar-base' %}
                            {% set data_toggle = '' %}

                            {% if items|length > 1 %}
                            {% set dropmenu = 'dropdown' %}
                            {% set dropdownToggle = 'dropdown-toggle' %}
                            {% set data_toggle = 'data-toggle=dropdown role=button aria-expanded=false' %}
                            {% else %}
                            {% set navlink = items[0].url %}
                            {% endif %}

                            <li class=\"nav-item {{ dropmenu }} {{menuSelect}}\">
                                <a class=\"nav-link {{ dropdownToggle }} chkCounter\" href=\"{{ navlink }}\"
                                    {{ data_toggle }}>
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block {{ menuMainIcon[categoryName] }}\"></span>
                                    <span class=\"nav-link-title\">
                                        {{ categoryName }}
                                    </span>
                                </a>
                                {% if dropmenu == 'dropdown' %}

                                {% set menucol = '' %}
                                {% if items[0].col %}
                                {% set menucol = items[0].col %}
                                {% endif %}

                                <ul class=\"dropdown-menu {{ menucol }}\">
                                    {% for item in items %}
                                    <li>
                                        {% if item.list %}
                                        <span class=\"dropdown-item mdi mdi-arrow-down\">
                                            <strong class='ml-1 badge bg-indigo'>{{ item.name }}</strong>
                                        </span>
                                        <div class='ml-2'>
                                            {% for sitem in item.list %}
                                            <div>
                                                {% if(sitem.name) %}
                                                <a class=\"dropdown-item chkCounter\" href=\"{{ sitem.url }}\">
                                                    <span class=\"badge bg-indigo\"></span>
                                                    <span class='ml-2'>{{ sitem.name }}</span>
                                                </a>
                                                {% else %}
                                                &nbsp;
                                                {% endif %}
                                            </div>
                                            {% endfor %}
                                        </div>
                                        {% else %}
                                        <a class=\"dropdown-item chkCounter\" href=\"{{ item.url }}\">
                                            {{ item.name }}
                                        </a>
                                        {% endif %}
                                    </li>
                                    {% endfor %}
                                </ul>
                                {% endif %}

                            </li>
                            {% endif %}
                            {% endfor %}
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id='content' class=\"content\">
            <div class=\"container-fluid\">
                <div class='row'>
                    <div class='col-12'>
                        <!-- Page title -->
                        <div class=\"page-header\">
                            <div class=\"row align-items-center\">
                                <div class=\"col-auto\">
                                    <h2 class=\"page-title\">
                                        {% if page.breadcrumbs %}
                                        {% for title, src in page.breadcrumbs %}
                                        {% if loop.last %}
                                        {{ title }}
                                        {% endif %}
                                        {% endfor %}
                                        {% endif %}
                                    </h2>
                                </div>
                                <!-- Page title actions -->
                                <div class=\"col-auto ml-auto d-print-none\">
                                    <ol class=\"breadcrumb\" aria-label=\"breadcrumbs\">


                                        {% if page.breadcrumbs %}

                                        {% for title, src in page.breadcrumbs %} {% if loop.last %}
                                        <li class=\"breadcrumb-item active\" aria-current=\"page\">{{ title }}</li>
                                        {% elseif loop.revindex > 5 and loop.index != 1 %}
                                        <li class=\"breadcrumb-item\">
                                            <a href=\"{{ absoluteURL }}/{{ src }}\">...</a>
                                        </li>
                                        {% else %}
                                        <li class=\"breadcrumb-item\">
                                            <a href=\"{{ absoluteURL }}/{{ src }}\">{{ title }}</a>
                                        </li>
                                        {% endif %} {% endfor %}

                                        {% endif %} {% block page %} {% if page.breadcrumbs %} {% endif %}
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <!--
                        <div class=\"row\">
                            <div id=\"content\" class=\"col-12 col-xl-12\" style=\"overflow: scroll;\">
                                <iframe id='iframeMaster' src=\"{{ framesrc }}\" style=\"border:0;width:100%;height:100vh;padding-bottom:20px;\"></iframe>
                            </div>
                        </div>
                        -->

                        <div class='card'>
                            <div class='card-body'>
                                {% if submenu %}
                                <div class=\"mb-2\">
                                    {{ include('navigation.twig.html') }}
                                </div>
                                {% endif %}

                                {% for type, alerts in page.alerts %} {% for text in alerts %}
                                <div class=\"{{ type }}\">{{ text|raw }}</div>
                                {% endfor %} {% endfor %} {{ content|join(\"\\n\")|raw }} {% endblock %}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Page Area End Here -->
    </div>


    <footer class=\"footer footer-transparent\">
        <div class=\"container\">
            <div class=\"row text-center align-items-center flex-row-reverse\">
                <div class=\"col-12 col-lg-auto mt-3 mt-lg-0\">
                    Copyright © 2020
                    <a href=\".\" class=\"link-secondary\">Pupilpod</a>.
                    All rights reserved.
                </div>
            </div>
        </div>
    </footer>
    </div>
    </div>

    </div>
    {{ include('alert.twig.html') }}


    <script>
        document.body.style.display = \"block\";
    </script>

</body>

</html>", "index_admin.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\index_admin.twig.html");
    }
}
