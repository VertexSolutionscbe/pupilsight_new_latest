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
        // line 25
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/jquery.dataTables.min.css?v=1.0\" />



    <link rel=\"stylesheet\" href=\"";
        // line 29
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/bootstrap-multiselect.css?v=1.0\" type=\"text/css\"
        media=\"all\" />
    <!--
<link rel=\"stylesheet\" href=\"";
        // line 32
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-ui/css/blitzer/jquery-ui.css?v=1.0\" type=\"text/css\" media=\"all\" />
    -->

    <link rel=\"stylesheet\" href=\"";
        // line 35
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-timepicker/jquery.timepicker.css?v=1.0\"
        type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 37
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/thickbox.css?v=1.0\" type=\"text/css\"
        media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 39
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/normalize.css?v=1.0\" type=\"text/css\" media=\"all\" />

    <link href=\"";
        // line 41
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/selectize.css\" rel=\"stylesheet\" />
    <link href=\"";
        // line 42
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/tabler.css\" rel=\"stylesheet\" />
    <link href=\"";
        // line 43
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/dev.css\" rel=\"stylesheet\" />
    <link href=\"";
        // line 44
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/select2.min.css\" rel=\"stylesheet\" />

    <link rel=\"stylesheet\" href=\"";
        // line 46
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/Sortable.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <!--
<link href=\"//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css\" rel=\"stylesheet\" />
    -->


    <!-- Libs JS -->
    <script src=\"";
        // line 53
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js\"></script>
    <script src=\"";
        // line 54
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery/dist/jquery-3.5.1.min.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 55
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery/jquery-migrate.min.js?v=1.0\"></script>
    <script src=\"";
        // line 56
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-ui/js/jquery-ui.min.js?v=1.0\"></script>
    <script src=\"";
        // line 57
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.dataTables.min.js?v=1.0v=1.0\"></script>
    <script src=\"";
        // line 58
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-timepicker/jquery.timepicker.min.js?v=1.0\"></script>
    <script src=\"";
        // line 59
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/livevalidation/livevalidation_standalone.compressed.js\"></script>


    <script src=\"";
        // line 62
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/core.js\"></script>
    <script src=\"";
        // line 63
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/main.js\"></script>
    <script src=\"";
        // line 64
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.table2excel.js\"></script>
    <script
        type=\"text/javascript\">var tb_pathToImage = \"";
        // line 66
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/loadingAnimation.gif\";</script>
    <script type=\"text/javascript\" src=\"";
        // line 67
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/tinymce/tinymce.min.js?v=1.0\"></script>
    <script type=\"text/javascript\"
        src=\"";
        // line 69
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-tokeninput/src/jquery.tokeninput.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 70
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/moment.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 71
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/fullcalendar.min.js?v=1.0\"></script>

    <script type=\"text/javascript\" src=\"";
        // line 73
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/bootstrap-multiselect.js?v=1.0\"></script>
    <script src=\"";
        // line 74
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/selectize.min.js\"></script>
    <script src=\"";
        // line 75
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/tabler.min.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 76
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/thickbox-compressed.js?v=1.0\"></script>
    <script src=\"";
        // line 77
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/select2.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 78
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.form.js?v=1.0\"></script>


    <script type=\"text/javascript\" src=\"";
        // line 81
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/Sortable.js?v=1.0\"></script>
    <!-- <script src=\"";
        // line 82
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/print.js\"></script> -->
    <!--
    <link rel=\"stylesheet\" href=\"";
        // line 84
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/bootstrap.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    
    
    
    
    

    <link rel=\"stylesheet\" href=\"";
        // line 91
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/all.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 92
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/fonts/flaticon.css?v=1.0\" type=\"text/css\" media=\"all\" />

    <link rel=\"stylesheet\" href=\"";
        // line 94
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/animate.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 95
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/sortable/css/Sortable.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 96
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/style.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 97
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/jquery.dropdown.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/themes/Default/css/main.css?v=1.0.00\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/resources/assets/css/theme.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/resources/assets/css/core.min.css?v=1.0\" 

    <script type=\"text/javascript\" src=\"";
        // line 102
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/popper.min.js?v=1.0\"></script>
    
    <script type=\"text/javascript\" src=\"";
        // line 104
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/chained/jquery.chained.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 105
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/modernizr-3.6.0.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 106
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.dropdown.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 107
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jszip.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 108
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/plugins.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 109
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.counterup.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 110
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.waypoints.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 111
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.scrollUp.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 112
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/Chart.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 113
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-jslatex/jquery.jslatex.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 114
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-form/jquery.form.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 115
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-ui/i18n/jquery.ui.datepicker-en-GB.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 116
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-autosize/jquery.autosize.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 117
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-sessionTimeout/jquery.sessionTimeout.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 118
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/sortable/js/Sortable.js?v=1.0\"></script>
-->

    <style>
        body {
            display: none;
        }

        table.dataTable thead th,
        table.dataTable thead td {
            border-bottom: 1px solid rgba(110, 117, 130, 0.2) !important;
        }

        .dataTables_wrapper .dataTables_filter {
            text-align: right;
            margin: 10px;
        }
    </style>
</head>

<body id='chkCounterSession' data-val='";
        // line 138
        echo twig_escape_filter($this->env, ($context["counterid"] ?? null), "html", null, true);
        echo "' data-chkCounter=\"\" class='antialiased'>
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
        // line 149
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/index.php\"
                    class=\"navbar-brand navbar-brand-autodark d-none-navbar-horizontal pr-0 pr-md-3\">
                    <img src=\"";
        // line 151
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
                                <path d=\"M9 17v1a3 3 0 0 0 6 0v-1\" />
                            </svg>
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
        // line 180
        echo ($context["uname"] ?? null);
        echo "</div>
                                <div class=\"mt-1 small text-muted\">Administrator</div>
                            </div>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right\">
                            <a class=\"dropdown-item\" href=\"";
        // line 185
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/index.php?q=preferences.php\">
                                <span class=\"mdi mdi-account-cog-outline mr-2\"></span>
                                Preferences
                            </a>
                            <div class=\"dropdown-divider\"></div>
                            <a class=\"dropdown-item\" href=\"";
        // line 190
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
        // line 201
        $context["comActive"] = "";
        // line 202
        echo "                            ";
        if ((($context["currentModule"] ?? null) == "Dashboard")) {
            // line 203
            echo "                            ";
            $context["comActive"] = "active";
            // line 204
            echo "                            ";
        }
        // line 205
        echo "
                            <li class=\"nav-item ";
        // line 206
        echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
        echo "\">
                                <a class=\"nav-link chkCounter\" href=\"";
        // line 207
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
        // line 216
        $context["comActive"] = "";
        // line 217
        echo "                            ";
        if ((($context["currentModule"] ?? null) == "Timetable Admin")) {
            // line 218
            echo "                            ";
            $context["comActive"] = "active";
            // line 219
            echo "                            ";
        }
        // line 220
        echo "
                            <li class=\"nav-item ";
        // line 221
        echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
        echo "\">
                                <a class=\"nav-link chkCounter\" href=\"";
        // line 222
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
        // line 232
        $context["comActive"] = "";
        // line 233
        echo "                            ";
        if ((($context["currentModule"] ?? null) == "Messenger")) {
            // line 234
            echo "                            ";
            $context["comActive"] = "active";
            // line 235
            echo "                            ";
        }
        // line 236
        echo "
                            <li class=\"nav-item ";
        // line 237
        echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
        echo "\">
                                <a class=\"nav-link chkCounter\" href=\"";
        // line 238
        echo twig_escape_filter($this->env, (($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 = (($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217 = (($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105 = ($context["menuMain"] ?? null)) && is_array($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105) || $__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105 instanceof ArrayAccess ? ($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105["Communication"] ?? null) : null)) && is_array($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217) || $__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217 instanceof ArrayAccess ? ($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217[0] ?? null) : null)) && is_array($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9) || $__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 instanceof ArrayAccess ? ($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9["url"] ?? null) : null), "html", null, true);
        echo "\">
                                    <span class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-handshake\"></span>
                                    <span class=\"nav-link-title\">
                                        Communication
                                    </span>
                                </a>
                            </li>


                            ";
        // line 247
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["menuMain"] ?? null));
        foreach ($context['_seq'] as $context["categoryName"] => $context["items"]) {
            // line 248
            echo "
                            ";
            // line 249
            if (($context["categoryName"] == "People")) {
                // line 250
                echo "
                            ";
                // line 251
                $context["comActive"] = "";
                // line 252
                echo "                            ";
                if ((($context["currentModule"] ?? null) == "People")) {
                    // line 253
                    echo "                            ";
                    $context["comActive"] = "active";
                    // line 254
                    echo "                            ";
                }
                // line 255
                echo "
                            <li class=\"nav-item dropdown ";
                // line 256
                echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
                echo "\">
                                <a class=\"nav-link dropdown-toggle chkCounter\" href=\"#navbar-base\"
                                    data-toggle=\"dropdown\" role=\"button\" aria-expanded=\"false\">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block ";
                // line 260
                echo twig_escape_filter($this->env, (($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779 = ($context["menuMainIcon"] ?? null)) && is_array($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779) || $__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779 instanceof ArrayAccess ? ($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779[$context["categoryName"]] ?? null) : null), "html", null, true);
                echo "\"></span>
                                    <span class=\"nav-link-title\">
                                        ";
                // line 262
                echo twig_escape_filter($this->env, $context["categoryName"], "html", null, true);
                echo "
                                    </span>
                                </a>

                                <ul class=\"dropdown-menu\">
                                    ";
                // line 267
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($context["items"]);
                foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                    // line 268
                    echo "                                    <li>
                                        <a class=\"dropdown-item chkCounter\" href=\"";
                    // line 269
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                    echo "\">
                                            ";
                    // line 270
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                    echo "
                                        </a>
                                    </li>
                                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 274
                echo "                                </ul>

                            </li>
                            ";
            }
            // line 278
            echo "                            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['categoryName'], $context['items'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 279
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
                                        <div class=\"input-group\">
                                            <select name=\"pupilsightSchoolYearID\" style=\"float:left;width: 120px;\"
                                                id=\"academicYearChange\">
                                                <option value=\"\">Select Academic Year</option>
                                                ";
        // line 298
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["academicYear"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["ay"]) {
            // line 299
            echo "                                                ";
            if ((twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()) == ($context["pupilsightSchoolYearID"] ?? null))) {
                // line 300
                echo "                                                <option value='";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()), "html", null, true);
                echo "' selected>";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "name", array()), "html", null, true);
                echo "
                                                </option>
                                                ";
            } else {
                // line 303
                echo "                                                <option value='";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()), "html", null, true);
                echo "'>";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "name", array()), "html", null, true);
                echo "
                                                </option>
                                                ";
            }
            // line 306
            echo "                                                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['ay'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 307
        echo "                                            </select>
                                            <button type=\"submit\" class=\"btn btn-white\">Change Year</a>
                                        </div>
                                    </div>
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
        // line 344
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["menuMain"] ?? null));
        foreach ($context['_seq'] as $context["categoryName"] => $context["items"]) {
            // line 345
            echo "
                            ";
            // line 346
            if (((($context["categoryName"] != "People") && ($context["categoryName"] != "TimeTable")) && ($context["categoryName"] != "Communication"))) {
                // line 348
                echo "
                            ";
                // line 349
                $context["menuSelect"] = "";
                // line 350
                echo "                            ";
                if (($context["categoryName"] == ($context["currentModule"] ?? null))) {
                    // line 351
                    echo "                            ";
                    $context["menuSelect"] = "active";
                    // line 352
                    echo "                            ";
                }
                // line 353
                echo "
                            ";
                // line 354
                $context["dropmenu"] = "";
                // line 355
                echo "                            ";
                $context["dropdownToggle"] = "";
                // line 356
                echo "                            ";
                $context["navlink"] = "#navbar-base";
                // line 357
                echo "                            ";
                $context["data_toggle"] = "";
                // line 358
                echo "
                            ";
                // line 359
                if ((twig_length_filter($this->env, $context["items"]) > 1)) {
                    // line 360
                    echo "                            ";
                    $context["dropmenu"] = "dropdown";
                    // line 361
                    echo "                            ";
                    $context["dropdownToggle"] = "dropdown-toggle";
                    // line 362
                    echo "                            ";
                    $context["data_toggle"] = "data-toggle=dropdown role=button aria-expanded=false";
                    // line 363
                    echo "                            ";
                } else {
                    // line 364
                    echo "                            ";
                    $context["navlink"] = twig_get_attribute($this->env, $this->source, (($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1 = $context["items"]) && is_array($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1) || $__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1 instanceof ArrayAccess ? ($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1[0] ?? null) : null), "url", array());
                    // line 365
                    echo "                            ";
                }
                // line 366
                echo "
                            <li class=\"nav-item ";
                // line 367
                echo twig_escape_filter($this->env, ($context["dropmenu"] ?? null), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, ($context["menuSelect"] ?? null), "html", null, true);
                echo "\">
                                <a class=\"nav-link ";
                // line 368
                echo twig_escape_filter($this->env, ($context["dropdownToggle"] ?? null), "html", null, true);
                echo " chkCounter\" href=\"";
                echo twig_escape_filter($this->env, ($context["navlink"] ?? null), "html", null, true);
                echo "\" ";
                echo twig_escape_filter($this->env, ($context["data_toggle"] ?? null), "html", null, true);
                // line 369
                echo ">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block ";
                // line 371
                echo twig_escape_filter($this->env, (($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0 = ($context["menuMainIcon"] ?? null)) && is_array($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0) || $__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0 instanceof ArrayAccess ? ($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0[$context["categoryName"]] ?? null) : null), "html", null, true);
                echo "\"></span>
                                    <span class=\"nav-link-title\">
                                        ";
                // line 373
                echo twig_escape_filter($this->env, $context["categoryName"], "html", null, true);
                echo "
                                    </span>
                                </a>
                                ";
                // line 376
                if ((($context["dropmenu"] ?? null) == "dropdown")) {
                    // line 377
                    echo "
                                ";
                    // line 378
                    $context["menucol"] = "";
                    // line 379
                    echo "                                ";
                    if (twig_get_attribute($this->env, $this->source, (($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866 = $context["items"]) && is_array($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866) || $__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866 instanceof ArrayAccess ? ($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866[0] ?? null) : null), "col", array())) {
                        // line 380
                        echo "                                ";
                        $context["menucol"] = twig_get_attribute($this->env, $this->source, (($__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f = $context["items"]) && is_array($__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f) || $__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f instanceof ArrayAccess ? ($__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f[0] ?? null) : null), "col", array());
                        // line 381
                        echo "                                ";
                    }
                    // line 382
                    echo "
                                <ul class=\"dropdown-menu ";
                    // line 383
                    echo twig_escape_filter($this->env, ($context["menucol"] ?? null), "html", null, true);
                    echo "\">
                                    ";
                    // line 384
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($context["items"]);
                    foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                        // line 385
                        echo "                                    <li>
                                        ";
                        // line 386
                        if (twig_get_attribute($this->env, $this->source, $context["item"], "list", array())) {
                            // line 387
                            echo "                                        <span class=\"dropdown-item mdi mdi-arrow-down\">
                                            <strong class='ml-1 badge bg-indigo'>";
                            // line 388
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                            echo "</strong>
                                        </span>
                                        <div class='ml-2'>
                                            ";
                            // line 391
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["item"], "list", array()));
                            foreach ($context['_seq'] as $context["_key"] => $context["sitem"]) {
                                // line 392
                                echo "                                            <div>
                                                ";
                                // line 393
                                if (twig_get_attribute($this->env, $this->source, $context["sitem"], "name", array())) {
                                    // line 394
                                    echo "                                                <a class=\"dropdown-item chkCounter\" href=\"";
                                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["sitem"], "url", array()), "html", null, true);
                                    echo "\">
                                                    <span class=\"badge bg-indigo\"></span>
                                                    <span class='ml-2'>";
                                    // line 396
                                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["sitem"], "name", array()), "html", null, true);
                                    echo "</span>
                                                </a>
                                                ";
                                } else {
                                    // line 399
                                    echo "                                                &nbsp;
                                                ";
                                }
                                // line 401
                                echo "                                            </div>
                                            ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['sitem'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 403
                            echo "                                        </div>
                                        ";
                        } else {
                            // line 405
                            echo "                                        <a class=\"dropdown-item chkCounter\" href=\"";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                            echo "\">
                                            ";
                            // line 406
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                            echo "
                                        </a>
                                        ";
                        }
                        // line 409
                        echo "                                    </li>
                                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 411
                    echo "                                </ul>
                                ";
                }
                // line 413
                echo "
                            </li>
                            ";
            }
            // line 416
            echo "                            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['categoryName'], $context['items'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 417
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
        // line 432
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            // line 433
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
                // line 434
                echo "                                        ";
                if (twig_get_attribute($this->env, $this->source, $context["loop"], "last", array())) {
                    // line 435
                    echo "                                        ";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "
                                        ";
                }
                // line 437
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
            // line 438
            echo "                                        ";
        }
        // line 439
        echo "                                    </h2>
                                </div>
                                <!-- Page title actions -->
                                <div class=\"col-auto ml-auto d-print-none\">
                                    <ol class=\"breadcrumb\" aria-label=\"breadcrumbs\">


                                        ";
        // line 446
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            // line 447
            echo "
                                        ";
            // line 448
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
                    // line 449
                    echo "                                        <li class=\"breadcrumb-item active\" aria-current=\"page\">";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "</li>
                                        ";
                } elseif (((twig_get_attribute($this->env, $this->source,                 // line 450
$context["loop"], "revindex", array()) > 5) && (twig_get_attribute($this->env, $this->source, $context["loop"], "index", array()) != 1))) {
                    // line 451
                    echo "                                        <li class=\"breadcrumb-item\">
                                            <a href=\"";
                    // line 452
                    echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                    echo "/";
                    echo twig_escape_filter($this->env, $context["src"], "html", null, true);
                    echo "\">...</a>
                                        </li>
                                        ";
                } else {
                    // line 455
                    echo "                                        <li class=\"breadcrumb-item\">
                                            <a href=\"";
                    // line 456
                    echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                    echo "/";
                    echo twig_escape_filter($this->env, $context["src"], "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "</a>
                                        </li>
                                        ";
                }
                // line 458
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
            // line 459
            echo "
                                        ";
        }
        // line 460
        echo " ";
        $this->displayBlock('page', $context, $blocks);
        // line 484
        echo "                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        ";
        // line 492
        if ((($context["reportAutoLogin"] ?? null) != "")) {
            // line 493
            echo "        <div style=\"display:none;visibility: hidden;\">
            <iframe id='iframeReportAutoLogin' src=\"";
            // line 494
            echo twig_escape_filter($this->env, ($context["reportAutoLogin"] ?? null), "html", null, true);
            echo "\"
                style=\"border:0;width:1px;height:1px;\"></iframe>
        </div>
        ";
        }
        // line 498
        echo "
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
        // line 518
        echo twig_include($this->env, $context, "alert.twig.html");
        echo "


    <script>
        document.body.style.display = \"block\";
    </script>

</body>

</html>";
    }

    // line 460
    public function block_page($context, array $blocks = array())
    {
        echo " ";
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            echo " ";
        }
        // line 461
        echo "                                    </ol>
                                </div>
                            </div>
                        </div>
                        <!--
                        <div class=\"row\">
                            <div id=\"content\" class=\"col-12 col-xl-12\" style=\"overflow: scroll;\">
                                <iframe id='iframeMaster' src=\"";
        // line 468
        echo twig_escape_filter($this->env, ($context["framesrc"] ?? null), "html", null, true);
        echo "\" style=\"border:0;width:100%;height:100vh;padding-bottom:20px;\"></iframe>
                            </div>
                        </div>
                        -->

                        <div class='card'>
                            <div class='card-body'>
                                ";
        // line 475
        if (($context["submenu"] ?? null)) {
            // line 476
            echo "                                <div class=\"mb-2\">
                                    ";
            // line 477
            echo twig_include($this->env, $context, "navigation.twig.html");
            echo "
                                </div>
                                ";
        }
        // line 480
        echo "
                                ";
        // line 481
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "alerts", array()));
        foreach ($context['_seq'] as $context["type"] => $context["alerts"]) {
            echo " ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($context["alerts"]);
            foreach ($context['_seq'] as $context["_key"] => $context["text"]) {
                // line 482
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
            // line 483
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
        return array (  1146 => 483,  1135 => 482,  1127 => 481,  1124 => 480,  1118 => 477,  1115 => 476,  1113 => 475,  1103 => 468,  1094 => 461,  1087 => 460,  1073 => 518,  1051 => 498,  1044 => 494,  1041 => 493,  1039 => 492,  1029 => 484,  1026 => 460,  1022 => 459,  1008 => 458,  998 => 456,  995 => 455,  987 => 452,  984 => 451,  982 => 450,  977 => 449,  958 => 448,  955 => 447,  953 => 446,  944 => 439,  941 => 438,  927 => 437,  921 => 435,  918 => 434,  900 => 433,  898 => 432,  881 => 417,  875 => 416,  870 => 413,  866 => 411,  859 => 409,  853 => 406,  848 => 405,  844 => 403,  837 => 401,  833 => 399,  827 => 396,  821 => 394,  819 => 393,  816 => 392,  812 => 391,  806 => 388,  803 => 387,  801 => 386,  798 => 385,  794 => 384,  790 => 383,  787 => 382,  784 => 381,  781 => 380,  778 => 379,  776 => 378,  773 => 377,  771 => 376,  765 => 373,  760 => 371,  756 => 369,  750 => 368,  744 => 367,  741 => 366,  738 => 365,  735 => 364,  732 => 363,  729 => 362,  726 => 361,  723 => 360,  721 => 359,  718 => 358,  715 => 357,  712 => 356,  709 => 355,  707 => 354,  704 => 353,  701 => 352,  698 => 351,  695 => 350,  693 => 349,  690 => 348,  688 => 346,  685 => 345,  681 => 344,  642 => 307,  636 => 306,  627 => 303,  618 => 300,  615 => 299,  611 => 298,  590 => 279,  584 => 278,  578 => 274,  568 => 270,  564 => 269,  561 => 268,  557 => 267,  549 => 262,  544 => 260,  537 => 256,  534 => 255,  531 => 254,  528 => 253,  525 => 252,  523 => 251,  520 => 250,  518 => 249,  515 => 248,  511 => 247,  499 => 238,  495 => 237,  492 => 236,  489 => 235,  486 => 234,  483 => 233,  481 => 232,  468 => 222,  464 => 221,  461 => 220,  458 => 219,  455 => 218,  452 => 217,  450 => 216,  438 => 207,  434 => 206,  431 => 205,  428 => 204,  425 => 203,  422 => 202,  420 => 201,  406 => 190,  398 => 185,  390 => 180,  356 => 151,  351 => 149,  337 => 138,  314 => 118,  310 => 117,  306 => 116,  302 => 115,  298 => 114,  294 => 113,  290 => 112,  286 => 111,  282 => 110,  278 => 109,  274 => 108,  270 => 107,  266 => 106,  262 => 105,  258 => 104,  253 => 102,  245 => 97,  241 => 96,  237 => 95,  233 => 94,  228 => 92,  224 => 91,  214 => 84,  209 => 82,  205 => 81,  199 => 78,  195 => 77,  191 => 76,  187 => 75,  183 => 74,  179 => 73,  174 => 71,  170 => 70,  166 => 69,  161 => 67,  157 => 66,  152 => 64,  148 => 63,  144 => 62,  138 => 59,  134 => 58,  130 => 57,  126 => 56,  122 => 55,  118 => 54,  114 => 53,  104 => 46,  99 => 44,  95 => 43,  91 => 42,  87 => 41,  82 => 39,  77 => 37,  72 => 35,  66 => 32,  60 => 29,  53 => 25,  48 => 23,  24 => 1,);
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

    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/Sortable.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <!--
<link href=\"//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css\" rel=\"stylesheet\" />
    -->


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
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/jquery.form.js?v=1.0\"></script>


    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/Sortable.js?v=1.0\"></script>
    <!-- <script src=\"{{ absoluteURL }}/assets/js/print.js\"></script> -->
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

        table.dataTable thead th,
        table.dataTable thead td {
            border-bottom: 1px solid rgba(110, 117, 130, 0.2) !important;
        }

        .dataTables_wrapper .dataTables_filter {
            text-align: right;
            margin: 10px;
        }
    </style>
</head>

<body id='chkCounterSession' data-val='{{ counterid }}' data-chkCounter=\"\" class='antialiased'>
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
                                <path d=\"M9 17v1a3 3 0 0 0 6 0v-1\" />
                            </svg>
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
                                        <div class=\"input-group\">
                                            <select name=\"pupilsightSchoolYearID\" style=\"float:left;width: 120px;\"
                                                id=\"academicYearChange\">
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
                                            <button type=\"submit\" class=\"btn btn-white\">Change Year</a>
                                        </div>
                                    </div>
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

                            {% if categoryName != 'People' and categoryName != 'TimeTable' and categoryName !=
                            'Communication' %}

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
                                <a class=\"nav-link {{ dropdownToggle }} chkCounter\" href=\"{{ navlink }}\" {{ data_toggle
                                    }}>
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

        {% if reportAutoLogin != \"\" %}
        <div style=\"display:none;visibility: hidden;\">
            <iframe id='iframeReportAutoLogin' src=\"{{ reportAutoLogin }}\"
                style=\"border:0;width:1px;height:1px;\"></iframe>
        </div>
        {% endif %}

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
