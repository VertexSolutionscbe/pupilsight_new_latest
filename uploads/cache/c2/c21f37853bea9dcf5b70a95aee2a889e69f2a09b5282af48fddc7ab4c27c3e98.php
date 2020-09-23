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
        echo "/assets/css/bootstrap-multiselect.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <!--
<link rel=\"stylesheet\" href=\"";
        // line 27
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-ui/css/blitzer/jquery-ui.css?v=1.0\" type=\"text/css\" media=\"all\" />
    -->

    <link rel=\"stylesheet\" href=\"";
        // line 30
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-timepicker/jquery.timepicker.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 31
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/thickbox.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 32
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/normalize.css?v=1.0\" type=\"text/css\" media=\"all\" />

    <link href=\"";
        // line 34
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/selectize.css\" rel=\"stylesheet\" />
    <link href=\"";
        // line 35
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/tabler.css\" rel=\"stylesheet\" />
    <link href=\"";
        // line 36
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/dev.css\" rel=\"stylesheet\" />

    <!-- Libs JS -->
    <script src=\"";
        // line 39
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js\"></script>
    <script src=\"";
        // line 40
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery/dist/jquery-3.5.1.min.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 41
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery/jquery-migrate.min.js?v=1.0\"></script>
    <script src=\"";
        // line 42
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-ui/js/jquery-ui.min.js?v=1.0\"></script>
    <script src=\"";
        // line 43
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.dataTables.min.js?v=1.0v=1.0\"></script>
    <script src=\"";
        // line 44
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-timepicker/jquery.timepicker.min.js?v=1.0\"></script>
    <script src=\"";
        // line 45
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/livevalidation/livevalidation_standalone.compressed.js\"></script>
    
    
    <script src=\"";
        // line 48
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/core.js\"></script>
    <script src=\"";
        // line 49
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/main.js\"></script>
    <script src=\"";
        // line 50
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.table2excel.js\"></script>
    <script type=\"text/javascript\">var tb_pathToImage=\"";
        // line 51
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/loadingAnimation.gif\";</script>
    <script type=\"text/javascript\" src=\"";
        // line 52
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/tinymce/tinymce.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 53
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-tokeninput/src/jquery.tokeninput.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 54
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/moment.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 55
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/fullcalendar.min.js?v=1.0\"></script>
   
    <script type=\"text/javascript\" src=\"";
        // line 57
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/bootstrap-multiselect.js?v=1.0\"></script>
    <script src=\"";
        // line 58
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/selectize.min.js\"></script>
    <script src=\"";
        // line 59
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/tabler.min.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 60
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/thickbox-compressed.js?v=1.0\"></script>
<!--
    <link rel=\"stylesheet\" href=\"";
        // line 62
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/bootstrap.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    
    
    
    
    

    <link rel=\"stylesheet\" href=\"";
        // line 69
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/all.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 70
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/fonts/flaticon.css?v=1.0\" type=\"text/css\" media=\"all\" />

    <link rel=\"stylesheet\" href=\"";
        // line 72
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/animate.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 73
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/sortable/css/Sortable.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 74
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/style.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 75
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/jquery.dropdown.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/themes/Default/css/main.css?v=1.0.00\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/resources/assets/css/theme.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"http://testoxygen.pupilpod.net/resources/assets/css/core.min.css?v=1.0\" 

    <script type=\"text/javascript\" src=\"";
        // line 80
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/popper.min.js?v=1.0\"></script>
    
    <script type=\"text/javascript\" src=\"";
        // line 82
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/chained/jquery.chained.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 83
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/modernizr-3.6.0.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 84
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.dropdown.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 85
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jszip.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 86
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/plugins.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 87
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.counterup.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 88
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.waypoints.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 89
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.scrollUp.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 90
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/Chart.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 91
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-jslatex/jquery.jslatex.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 92
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-form/jquery.form.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 93
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-ui/i18n/jquery.ui.datepicker-en-GB.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 94
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-autosize/jquery.autosize.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 95
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/jquery-sessionTimeout/jquery.sessionTimeout.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 96
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
        // line 106
        echo twig_escape_filter($this->env, ($context["counterid"] ?? null), "html", null, true);
        echo "' class='antialiased'>

    <div class=\"page\">
        <header class=\"navbar navbar-expand-md navbar-light\">
            <div class=\"container-fluid\">
                <button class=\"navbar-toggler\" type=\"button\" data-toggle=\"collapse\" data-target=\"#navbar-menu\">
                    <span class=\"navbar-toggler-icon\"></span>
                </button>
                <a href=\"";
        // line 114
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/index.php\"
                    class=\"navbar-brand navbar-brand-autodark d-none-navbar-horizontal pr-0 pr-md-3\">
                    <img src=\"";
        // line 116
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
        // line 144
        echo ($context["uname"] ?? null);
        echo "</div>
                                <div class=\"mt-1 small text-muted\">Administrator</div>
                            </div>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right\">
                            <a class=\"dropdown-item\" href=\"";
        // line 149
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/index.php?q=preferences.php\">
                                <span class=\"mdi mdi-account-cog-outline mr-2\"></span>
                                Preferences
                            </a>
                            <div class=\"dropdown-divider\"></div>
                            <a class=\"dropdown-item\" href=\"";
        // line 154
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
        // line 165
        $context["comActive"] = "";
        // line 166
        echo "                            ";
        if ((($context["currentModule"] ?? null) == "Dashboard")) {
            // line 167
            echo "                                ";
            $context["comActive"] = "active";
            // line 168
            echo "                            ";
        }
        // line 169
        echo "
                            <li class=\"nav-item ";
        // line 170
        echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
        echo "\">
                                <a class=\"nav-link chkCounter\" href=\"";
        // line 171
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
        // line 180
        $context["comActive"] = "";
        // line 181
        echo "                            ";
        if ((($context["currentModule"] ?? null) == "Timetable Admin")) {
            // line 182
            echo "                            ";
            $context["comActive"] = "active";
            // line 183
            echo "                            ";
        }
        // line 184
        echo "
                            <li class=\"nav-item ";
        // line 185
        echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
        echo "\">
                                <a class=\"nav-link chkCounter\" href=\"";
        // line 186
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
        // line 196
        $context["comActive"] = "";
        // line 197
        echo "                            ";
        if ((($context["currentModule"] ?? null) == "Messenger")) {
            // line 198
            echo "                                ";
            $context["comActive"] = "active";
            // line 199
            echo "                            ";
        }
        // line 200
        echo "
                            <li class=\"nav-item ";
        // line 201
        echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
        echo "\">
                                <a class=\"nav-link chkCounter\" href=\"";
        // line 202
        echo twig_escape_filter($this->env, (($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 = (($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217 = (($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105 = ($context["menuMain"] ?? null)) && is_array($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105) || $__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105 instanceof ArrayAccess ? ($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105["Communication"] ?? null) : null)) && is_array($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217) || $__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217 instanceof ArrayAccess ? ($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217[0] ?? null) : null)) && is_array($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9) || $__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 instanceof ArrayAccess ? ($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9["url"] ?? null) : null), "html", null, true);
        echo "\">
                                    <span class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-handshake\"></span>
                                    <span class=\"nav-link-title\">
                                        Communication
                                    </span>
                                </a>
                            </li>


                            ";
        // line 211
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["menuMain"] ?? null));
        foreach ($context['_seq'] as $context["categoryName"] => $context["items"]) {
            // line 212
            echo "
                            ";
            // line 213
            if (($context["categoryName"] == "People")) {
                // line 214
                echo "
                            ";
                // line 215
                $context["comActive"] = "";
                // line 216
                echo "                            ";
                if ((($context["currentModule"] ?? null) == "People")) {
                    // line 217
                    echo "                            ";
                    $context["comActive"] = "active";
                    // line 218
                    echo "                            ";
                }
                // line 219
                echo "
                            <li class=\"nav-item dropdown ";
                // line 220
                echo twig_escape_filter($this->env, ($context["comActive"] ?? null), "html", null, true);
                echo "\">
                                <a class=\"nav-link dropdown-toggle chkCounter\" href=\"#navbar-base\" data-toggle=\"dropdown\"
                                    role=\"button\" aria-expanded=\"false\">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block ";
                // line 224
                echo twig_escape_filter($this->env, (($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779 = ($context["menuMainIcon"] ?? null)) && is_array($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779) || $__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779 instanceof ArrayAccess ? ($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779[$context["categoryName"]] ?? null) : null), "html", null, true);
                echo "\"></span>
                                    <span class=\"nav-link-title\">
                                        ";
                // line 226
                echo twig_escape_filter($this->env, $context["categoryName"], "html", null, true);
                echo "
                                    </span>
                                </a>

                                <ul class=\"dropdown-menu\">
                                    ";
                // line 231
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($context["items"]);
                foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                    // line 232
                    echo "                                    <li>
                                        <a class=\"dropdown-item chkCounter\" href=\"";
                    // line 233
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                    echo "\">
                                            ";
                    // line 234
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                    echo "
                                        </a>
                                    </li>
                                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 238
                echo "                                </ul>

                            </li>
                            ";
            }
            // line 242
            echo "                            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['categoryName'], $context['items'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 243
        echo "
                            <li class=\"nav-item\">
                                <a class=\"nav-link\" href=\"http://pupilsight.pupilpod.in/index.php?r=site%2Flogin\" target='_blank'>
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-chart-bar-stacked\"></span>
                                    <span class=\"nav-link-title\">
                                        Analytics
                                    </span>
                                </a>
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
        // line 284
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["menuMain"] ?? null));
        foreach ($context['_seq'] as $context["categoryName"] => $context["items"]) {
            // line 285
            echo "
                            ";
            // line 286
            if (((($context["categoryName"] != "People") && ($context["categoryName"] != "TimeTable")) && ($context["categoryName"] != "Communication"))) {
                // line 287
                echo "
                            ";
                // line 288
                $context["menuSelect"] = "";
                // line 289
                echo "                            ";
                if (($context["categoryName"] == ($context["currentModule"] ?? null))) {
                    // line 290
                    echo "                            ";
                    $context["menuSelect"] = "active";
                    // line 291
                    echo "                            ";
                }
                // line 292
                echo "
                            ";
                // line 293
                $context["dropmenu"] = "";
                // line 294
                echo "                            ";
                $context["dropdownToggle"] = "";
                // line 295
                echo "                            ";
                $context["navlink"] = "#navbar-base";
                // line 296
                echo "                            ";
                $context["data_toggle"] = "";
                // line 297
                echo "
                            ";
                // line 298
                if ((twig_length_filter($this->env, $context["items"]) > 1)) {
                    // line 299
                    echo "                            ";
                    $context["dropmenu"] = "dropdown";
                    // line 300
                    echo "                            ";
                    $context["dropdownToggle"] = "dropdown-toggle";
                    // line 301
                    echo "                            ";
                    $context["data_toggle"] = "data-toggle=dropdown role=button aria-expanded=false";
                    // line 302
                    echo "                            ";
                } else {
                    // line 303
                    echo "                            ";
                    $context["navlink"] = twig_get_attribute($this->env, $this->source, (($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1 = $context["items"]) && is_array($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1) || $__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1 instanceof ArrayAccess ? ($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1[0] ?? null) : null), "url", array());
                    // line 304
                    echo "                            ";
                }
                // line 305
                echo "
                            <li class=\"nav-item ";
                // line 306
                echo twig_escape_filter($this->env, ($context["dropmenu"] ?? null), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, ($context["menuSelect"] ?? null), "html", null, true);
                echo "\">
                                <a class=\"nav-link ";
                // line 307
                echo twig_escape_filter($this->env, ($context["dropdownToggle"] ?? null), "html", null, true);
                echo " chkCounter\" href=\"";
                echo twig_escape_filter($this->env, ($context["navlink"] ?? null), "html", null, true);
                echo "\" ";
                echo twig_escape_filter($this->env, ($context["data_toggle"] ?? null), "html", null, true);
                echo ">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block ";
                // line 309
                echo twig_escape_filter($this->env, (($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0 = ($context["menuMainIcon"] ?? null)) && is_array($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0) || $__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0 instanceof ArrayAccess ? ($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0[$context["categoryName"]] ?? null) : null), "html", null, true);
                echo "\"></span>
                                    <span class=\"nav-link-title\">
                                        ";
                // line 311
                echo twig_escape_filter($this->env, $context["categoryName"], "html", null, true);
                echo "
                                    </span>
                                </a>
                                ";
                // line 314
                if ((($context["dropmenu"] ?? null) == "dropdown")) {
                    // line 315
                    echo "
                                ";
                    // line 316
                    $context["menucol"] = "";
                    // line 317
                    echo "                                ";
                    if (twig_get_attribute($this->env, $this->source, (($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866 = $context["items"]) && is_array($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866) || $__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866 instanceof ArrayAccess ? ($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866[0] ?? null) : null), "col", array())) {
                        // line 318
                        echo "                                ";
                        $context["menucol"] = twig_get_attribute($this->env, $this->source, (($__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f = $context["items"]) && is_array($__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f) || $__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f instanceof ArrayAccess ? ($__internal_de222b1ef20cf829a938a4545cbb79f4996337944397dd43b1919bce7726ae2f[0] ?? null) : null), "col", array());
                        // line 319
                        echo "                                ";
                    }
                    // line 320
                    echo "
                                <ul class=\"dropdown-menu ";
                    // line 321
                    echo twig_escape_filter($this->env, ($context["menucol"] ?? null), "html", null, true);
                    echo "\">
                                    ";
                    // line 322
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($context["items"]);
                    foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                        // line 323
                        echo "                                    <li>
                                        ";
                        // line 324
                        if (twig_get_attribute($this->env, $this->source, $context["item"], "list", array())) {
                            // line 325
                            echo "                                        <span class=\"dropdown-item mdi mdi-arrow-down\">
                                            <strong class='ml-1 badge bg-indigo'>";
                            // line 326
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                            echo "</strong>
                                        </span>
                                        <div class='ml-2'>
                                            ";
                            // line 329
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["item"], "list", array()));
                            foreach ($context['_seq'] as $context["_key"] => $context["sitem"]) {
                                // line 330
                                echo "                                            <div>
                                                ";
                                // line 331
                                if (twig_get_attribute($this->env, $this->source, $context["sitem"], "name", array())) {
                                    // line 332
                                    echo "                                                <a class=\"dropdown-item chkCounter\" href=\"";
                                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["sitem"], "url", array()), "html", null, true);
                                    echo "\">
                                                    <span class=\"badge bg-indigo\"></span>
                                                    <span class='ml-2'>";
                                    // line 334
                                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["sitem"], "name", array()), "html", null, true);
                                    echo "</span>
                                                </a>
                                                ";
                                } else {
                                    // line 337
                                    echo "                                                &nbsp;
                                                ";
                                }
                                // line 339
                                echo "                                            </div>
                                            ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['sitem'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 341
                            echo "                                        </div>
                                        ";
                        } else {
                            // line 343
                            echo "                                        <a class=\"dropdown-item chkCounter\" href=\"";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                            echo "\">
                                            ";
                            // line 344
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                            echo "
                                        </a>
                                        ";
                        }
                        // line 347
                        echo "                                    </li>
                                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 349
                    echo "                                </ul>
                                ";
                }
                // line 351
                echo "
                            </li>
                            ";
            }
            // line 354
            echo "                            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['categoryName'], $context['items'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 355
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
        // line 370
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            // line 371
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
                // line 372
                echo "                                        ";
                if (twig_get_attribute($this->env, $this->source, $context["loop"], "last", array())) {
                    // line 373
                    echo "                                        ";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "
                                        ";
                }
                // line 375
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
            // line 376
            echo "                                        ";
        }
        // line 377
        echo "                                    </h2>
                                </div>
                                <!-- Page title actions -->
                                <div class=\"col-auto ml-auto d-print-none\">
                                    <ol class=\"breadcrumb\" aria-label=\"breadcrumbs\">


                                        ";
        // line 384
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            // line 385
            echo "
                                        ";
            // line 386
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
                    // line 387
                    echo "                                        <li class=\"breadcrumb-item active\" aria-current=\"page\">";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "</li>
                                        ";
                } elseif (((twig_get_attribute($this->env, $this->source,                 // line 388
$context["loop"], "revindex", array()) > 5) && (twig_get_attribute($this->env, $this->source, $context["loop"], "index", array()) != 1))) {
                    // line 389
                    echo "                                        <li class=\"breadcrumb-item\">
                                            <a href=\"";
                    // line 390
                    echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                    echo "/";
                    echo twig_escape_filter($this->env, $context["src"], "html", null, true);
                    echo "\">...</a>
                                        </li>
                                        ";
                } else {
                    // line 393
                    echo "                                        <li class=\"breadcrumb-item\">
                                            <a href=\"";
                    // line 394
                    echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                    echo "/";
                    echo twig_escape_filter($this->env, $context["src"], "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "</a>
                                        </li>
                                        ";
                }
                // line 396
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
            // line 397
            echo "
                                        ";
        }
        // line 398
        echo " ";
        $this->displayBlock('page', $context, $blocks);
        // line 422
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
        // line 449
        echo twig_include($this->env, $context, "alert.twig.html");
        echo "


    <script>
        document.body.style.display = \"block\";
    </script>

</body>

</html>";
    }

    // line 398
    public function block_page($context, array $blocks = array())
    {
        echo " ";
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            echo " ";
        }
        // line 399
        echo "                                    </ol>
                                </div>
                            </div>
                        </div>
                        <!--
                        <div class=\"row\">
                            <div id=\"content\" class=\"col-12 col-xl-12\" style=\"overflow: scroll;\">
                                <iframe id='iframeMaster' src=\"";
        // line 406
        echo twig_escape_filter($this->env, ($context["framesrc"] ?? null), "html", null, true);
        echo "\" style=\"border:0;width:100%;height:100vh;padding-bottom:20px;\"></iframe>
                            </div>
                        </div>
                        -->

                        <div class='card'>
                            <div class='card-body'>
                                ";
        // line 413
        if (($context["submenu"] ?? null)) {
            // line 414
            echo "                                <div class=\"mb-2\">
                                    ";
            // line 415
            echo twig_include($this->env, $context, "navigation.twig.html");
            echo "
                                </div>
                                ";
        }
        // line 418
        echo "
                                ";
        // line 419
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "alerts", array()));
        foreach ($context['_seq'] as $context["type"] => $context["alerts"]) {
            echo " ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($context["alerts"]);
            foreach ($context['_seq'] as $context["_key"] => $context["text"]) {
                // line 420
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
            // line 421
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
        return array (  1028 => 421,  1017 => 420,  1009 => 419,  1006 => 418,  1000 => 415,  997 => 414,  995 => 413,  985 => 406,  976 => 399,  969 => 398,  955 => 449,  926 => 422,  923 => 398,  919 => 397,  905 => 396,  895 => 394,  892 => 393,  884 => 390,  881 => 389,  879 => 388,  874 => 387,  855 => 386,  852 => 385,  850 => 384,  841 => 377,  838 => 376,  824 => 375,  818 => 373,  815 => 372,  797 => 371,  795 => 370,  778 => 355,  772 => 354,  767 => 351,  763 => 349,  756 => 347,  750 => 344,  745 => 343,  741 => 341,  734 => 339,  730 => 337,  724 => 334,  718 => 332,  716 => 331,  713 => 330,  709 => 329,  703 => 326,  700 => 325,  698 => 324,  695 => 323,  691 => 322,  687 => 321,  684 => 320,  681 => 319,  678 => 318,  675 => 317,  673 => 316,  670 => 315,  668 => 314,  662 => 311,  657 => 309,  648 => 307,  642 => 306,  639 => 305,  636 => 304,  633 => 303,  630 => 302,  627 => 301,  624 => 300,  621 => 299,  619 => 298,  616 => 297,  613 => 296,  610 => 295,  607 => 294,  605 => 293,  602 => 292,  599 => 291,  596 => 290,  593 => 289,  591 => 288,  588 => 287,  586 => 286,  583 => 285,  579 => 284,  536 => 243,  530 => 242,  524 => 238,  514 => 234,  510 => 233,  507 => 232,  503 => 231,  495 => 226,  490 => 224,  483 => 220,  480 => 219,  477 => 218,  474 => 217,  471 => 216,  469 => 215,  466 => 214,  464 => 213,  461 => 212,  457 => 211,  445 => 202,  441 => 201,  438 => 200,  435 => 199,  432 => 198,  429 => 197,  427 => 196,  414 => 186,  410 => 185,  407 => 184,  404 => 183,  401 => 182,  398 => 181,  396 => 180,  384 => 171,  380 => 170,  377 => 169,  374 => 168,  371 => 167,  368 => 166,  366 => 165,  352 => 154,  344 => 149,  336 => 144,  303 => 116,  298 => 114,  287 => 106,  274 => 96,  270 => 95,  266 => 94,  262 => 93,  258 => 92,  254 => 91,  250 => 90,  246 => 89,  242 => 88,  238 => 87,  234 => 86,  230 => 85,  226 => 84,  222 => 83,  218 => 82,  213 => 80,  205 => 75,  201 => 74,  197 => 73,  193 => 72,  188 => 70,  184 => 69,  174 => 62,  169 => 60,  165 => 59,  161 => 58,  157 => 57,  152 => 55,  148 => 54,  144 => 53,  140 => 52,  136 => 51,  132 => 50,  128 => 49,  124 => 48,  118 => 45,  114 => 44,  110 => 43,  106 => 42,  102 => 41,  98 => 40,  94 => 39,  88 => 36,  84 => 35,  80 => 34,  75 => 32,  71 => 31,  67 => 30,  61 => 27,  56 => 25,  52 => 24,  48 => 23,  24 => 1,);
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
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/bootstrap-multiselect.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <!--
<link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/libs/jquery-ui/css/blitzer/jquery-ui.css?v=1.0\" type=\"text/css\" media=\"all\" />
    -->

    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/libs/jquery-timepicker/jquery.timepicker.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/libs/thickbox/thickbox.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/normalize.css?v=1.0\" type=\"text/css\" media=\"all\" />

    <link href=\"{{ absoluteURL }}/assets/css/selectize.css\" rel=\"stylesheet\" />
    <link href=\"{{ absoluteURL }}/assets/css/tabler.css\" rel=\"stylesheet\" />
    <link href=\"{{ absoluteURL }}/assets/css/dev.css\" rel=\"stylesheet\" />

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
    <script type=\"text/javascript\">var tb_pathToImage=\"{{ absoluteURL }}/assets/libs/thickbox/loadingAnimation.gif\";</script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/libs/tinymce/tinymce.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/libs/jquery-tokeninput/src/jquery.tokeninput.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/moment.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/fullcalendar.min.js?v=1.0\"></script>
   
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/bootstrap-multiselect.js?v=1.0\"></script>
    <script src=\"{{ absoluteURL }}/assets/js/selectize.min.js\"></script>
    <script src=\"{{ absoluteURL }}/assets/js/tabler.min.js\"></script>
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/libs/thickbox/thickbox-compressed.js?v=1.0\"></script>
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
                                <a class=\"nav-link dropdown-toggle chkCounter\" href=\"#navbar-base\" data-toggle=\"dropdown\"
                                    role=\"button\" aria-expanded=\"false\">
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
                                <a class=\"nav-link\" href=\"http://pupilsight.pupilpod.in/index.php?r=site%2Flogin\" target='_blank'>
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block mdi mdi-chart-bar-stacked\"></span>
                                    <span class=\"nav-link-title\">
                                        Analytics
                                    </span>
                                </a>
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
                                <a class=\"nav-link {{ dropdownToggle }} chkCounter\" href=\"{{ navlink }}\" {{ data_toggle }}>
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
