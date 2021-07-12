<?php

/* index.twig.html */
class __TwigTemplate_68e21a593dec9ec39b650d45a5b9b216735c24a9a6843453e0a70812e9b06bdc extends Twig_Template
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
<html class=\"no-js\" lang=\"\" translate='no' lang='en' class='notranslate' translate='no'>

<head>
    <meta charset=\"utf-8\" />
    <meta name='google' content='notranslate' />
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

    <link rel=\"stylesheet\"
        href=\"//cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.8.55/css/materialdesignicons.min.css\">

    <link rel=\"stylesheet\" href=\"";
        // line 25
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/fullcalendar.min.css?v=1.0\" type=\"text/css\" media=\"all\" />

    <link rel=\"stylesheet\" href=\"";
        // line 27
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/jquery.dataTables.min.css?v=1.0\" />



    <link rel=\"stylesheet\" href=\"";
        // line 31
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/bootstrap-multiselect.css?v=1.0\" type=\"text/css\"
        media=\"all\" />
    <!--
<link rel=\"stylesheet\" href=\"";
        // line 34
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-ui/css/blitzer/jquery-ui.css?v=1.0\" type=\"text/css\" media=\"all\" />
    -->

    <link rel=\"stylesheet\" href=\"";
        // line 37
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-timepicker/jquery.timepicker.css?v=1.0\"
        type=\"text/css\" media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 39
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/thickbox.css?v=1.0\" type=\"text/css\"
        media=\"all\" />
    <link rel=\"stylesheet\" href=\"";
        // line 41
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/normalize.css?v=1.0\" type=\"text/css\" media=\"all\" />

    <link href=\"";
        // line 43
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/selectize.css\" rel=\"stylesheet\" />
    <link href=\"";
        // line 44
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/tabler.css\" rel=\"stylesheet\" />
    <link href=\"";
        // line 45
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/dev.css\" rel=\"stylesheet\" />
    <link href=\"";
        // line 46
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/select2.min.css\" rel=\"stylesheet\" />

    <link rel=\"stylesheet\" href=\"";
        // line 48
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/Sortable.css?v=1.0\" type=\"text/css\" media=\"all\" />
    <!--
<link href=\"//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css\" rel=\"stylesheet\" />
    -->


    <!-- Libs JS -->
    <script src=\"";
        // line 55
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js\"></script>
    <script src=\"";
        // line 56
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery/dist/jquery-3.5.1.min.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 57
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery/jquery-migrate.min.js?v=1.0\"></script>
    <script src=\"";
        // line 58
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-ui/js/jquery-ui.min.js?v=1.0\"></script>
    <script src=\"";
        // line 59
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.dataTables.min.js?v=1.0v=1.0\"></script>
    <script src=\"";
        // line 60
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-timepicker/jquery.timepicker.min.js?v=1.0\"></script>
    <script src=\"";
        // line 61
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/livevalidation/livevalidation_standalone.compressed.js\"></script>


    <script src=\"";
        // line 64
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/core.js\"></script>
    <script src=\"";
        // line 65
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/main.js\"></script>
    <script src=\"";
        // line 66
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.table2excel.js\"></script>
    <script
        type=\"text/javascript\">var tb_pathToImage = \"";
        // line 68
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/loadingAnimation.gif\";</script>
    <script type=\"text/javascript\" src=\"";
        // line 69
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/tinymce/tinymce.min.js?v=1.0\"></script>
    <script type=\"text/javascript\"
        src=\"";
        // line 71
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/jquery-tokeninput/src/jquery.tokeninput.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 72
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/moment.min.js?v=1.0\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 73
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/fullcalendar.min.js?v=1.0\"></script>

    <script type=\"text/javascript\" src=\"";
        // line 75
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/bootstrap-multiselect.js?v=1.0\"></script>
    <script src=\"";
        // line 76
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/selectize.min.js\"></script>
    <script src=\"";
        // line 77
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/tabler.min.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 78
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/thickbox-compressed.js?v=1.0\"></script>
    <script src=\"";
        // line 79
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/select2.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 80
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.form.js?v=1.0\"></script>


    <script type=\"text/javascript\" src=\"";
        // line 83
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/Sortable.js?v=1.0\"></script>

    <script src=\"";
        // line 85
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/trumbowyg/dist/trumbowyg.min.js\"></script>
    <link rel=\"stylesheet\" href=\"";
        // line 86
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/trumbowyg/dist/ui/trumbowyg.min.css\" type=\"text/css\"
        media=\"all\" />

    <link rel=\"stylesheet\"
        href=\"";
        // line 90
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/trumbowyg/dist/plugins/colors/ui/trumbowyg.colors.min.css\" type=\"text/css\"
        media=\"all\" />

    <script src=\"";
        // line 93
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/trumbowyg/dist/plugins/colors/trumbowyg.colors.min.js\"></script>

    <link rel=\"stylesheet\" href=\"";
        // line 95
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/toastr/toastr.min.css\" type=\"text/css\" media=\"all\" />

    <script src=\"";
        // line 97
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/toastr/toastr.min.js\"></script>

    <!--

    <link rel=\"stylesheet\" href=\"";
        // line 101
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/airdatepicker/dist/css/datepicker.min.css\"
        type=\"text/css\" media=\"all\" />

    <script src=\"";
        // line 104
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/airdatepicker/dist/js/datepicker.min.js\"></script>

   
    <script src=\"";
        // line 107
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/airdatepicker/dist/js/i18n/datepicker.en.js\"></script>
    -->


    <!-- JavaScript Bundle with Popper -->
    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js\"
        integrity=\"sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4\"
        crossorigin=\"anonymous\"></script>

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

        .ui-datepicker-title {
            display: inline-flex;
        }

        .ui-datepicker-month {
            margin: 0 5px 0 12px !important;
        }

        .ui-datepicker-year {
            margin: 0 !important;
        }
    </style>
</head>

<body id='chkCounterSession' data-val='";
        // line 145
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
        // line 156
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/index.php\"
                    class=\"navbar-brand navbar-brand-autodark d-none-navbar-horizontal pr-0 pr-md-3\">
                    <img src=\"";
        // line 158
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/";
        echo twig_escape_filter($this->env, (((isset($context["organisationLogo"]) || array_key_exists("organisationLogo", $context))) ? (_twig_default_filter(($context["organisationLogo"] ?? null), " /themes/Default/img/logo.png ")) : (" /themes/Default/img/logo.png ")), "html", null, true);
        echo "\"
                        alt=\"Tabler\" class=\"navbar-brand-image\">
                </a>
                <div class=\"navbar-nav flex-row order-md-last\">
                    ";
        // line 162
        if ((($context["changeyear"] ?? null) == "allow")) {
            // line 163
            echo "                    <div class=\"nav-item d-none d-md-flex mr-3\">
                        <form action=\"yearSwitcherProcess.php\" method=\"post\">
                            <div style=\"display:inline-flex;\">
                                <div class=\"input-group\">
                                    <select name=\"pupilsightSchoolYearID\" style=\"float:left;width: 120px;\"
                                        id=\"academicYearChange\">
                                        <option value=\"\">Select Academic Year</option>
                                        ";
            // line 170
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["academicYear"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["ay"]) {
                // line 171
                echo "                                        ";
                if ((twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()) == ($context["pupilsightSchoolYearID"] ?? null))) {
                    // line 172
                    echo "                                        <option value='";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()), "html", null, true);
                    echo "' selected>";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "name", array()), "html", null, true);
                    echo "
                                        </option>
                                        ";
                } else {
                    // line 175
                    echo "                                        <option value='";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()), "html", null, true);
                    echo "'>";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "name", array()), "html", null, true);
                    echo "
                                        </option>
                                        ";
                }
                // line 178
                echo "                                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['ay'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 179
            echo "                                    </select>
                                    <button type=\"submit\" class=\"btn btn-white\">Change Year</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    ";
        }
        // line 186
        echo "
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
                            <span class=\"avatar\">";
        // line 210
        echo twig_escape_filter($this->env, ($context["shortname"] ?? null), "html", null, true);
        echo "</span>
                            <div class=\"d-none d-xl-block pl-2\">
                                <div>";
        // line 212
        echo ($context["uname"] ?? null);
        echo "</div>
                                <div class=\"mt-1 small text-muted\">";
        // line 213
        echo twig_escape_filter($this->env, ($context["roleCategory"] ?? null), "html", null, true);
        echo "</div>
                            </div>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right\">
                            <a class=\"dropdown-item\" href=\"";
        // line 217
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/index.php?q=preferences.php\">
                                <span class=\"mdi mdi-account-cog-outline mr-2\"></span>
                                Preferences
                            </a>
                            <div class=\"dropdown-divider\"></div>
                            <a class=\"dropdown-item\" href=\"";
        // line 222
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/logout.php\">
                                <span class=\"mdi mdi-logout-variant mr-2\"></span>
                                Logout</a>
                        </div>
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
        // line 236
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["menuMain"] ?? null));
        foreach ($context['_seq'] as $context["categoryName"] => $context["items"]) {
            // line 237
            echo "
                            ";
            // line 238
            $context["menuSelect"] = "";
            // line 239
            echo "                            ";
            if (($context["categoryName"] == ($context["currentModule"] ?? null))) {
                // line 240
                echo "                            ";
                $context["menuSelect"] = "active";
                // line 241
                echo "                            ";
            }
            // line 242
            echo "
                            ";
            // line 243
            $context["dropmenu"] = "";
            // line 244
            echo "                            ";
            $context["dropdownToggle"] = "";
            // line 245
            echo "                            ";
            $context["navlink"] = "#navbar-base";
            // line 246
            echo "                            ";
            $context["data_toggle"] = "";
            // line 247
            echo "
                            ";
            // line 248
            if ((twig_length_filter($this->env, $context["items"]) > 1)) {
                // line 249
                echo "                            ";
                $context["dropmenu"] = "dropdown";
                // line 250
                echo "                            ";
                $context["dropdownToggle"] = "dropdown-toggle";
                // line 251
                echo "                            ";
                $context["data_toggle"] = "data-toggle=dropdown role=button aria-expanded=false";
                // line 252
                echo "                            ";
            } else {
                // line 253
                echo "                            ";
                $context["navlink"] = twig_get_attribute($this->env, $this->source, (($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 = $context["items"]) && is_array($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5) || $__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 instanceof ArrayAccess ? ($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5[0] ?? null) : null), "url", array());
                // line 254
                echo "                            ";
            }
            // line 255
            echo "
                            <li class=\"nav-item ";
            // line 256
            echo twig_escape_filter($this->env, ($context["dropmenu"] ?? null), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, ($context["menuSelect"] ?? null), "html", null, true);
            echo "\">
                                <a class=\"nav-link ";
            // line 257
            echo twig_escape_filter($this->env, ($context["dropdownToggle"] ?? null), "html", null, true);
            echo "\" href=\"";
            echo twig_escape_filter($this->env, ($context["navlink"] ?? null), "html", null, true);
            echo "\" ";
            echo twig_escape_filter($this->env, ($context["data_toggle"] ?? null), "html", null, true);
            echo ">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block ";
            // line 259
            echo twig_escape_filter($this->env, (($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a = ($context["menuMainIcon"] ?? null)) && is_array($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a) || $__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a instanceof ArrayAccess ? ($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a[$context["categoryName"]] ?? null) : null), "html", null, true);
            echo "\"></span>
                                    <span class=\"nav-link-title\">
                                        ";
            // line 261
            echo twig_escape_filter($this->env, $context["categoryName"], "html", null, true);
            echo "
                                    </span>
                                </a>
                                ";
            // line 264
            if ((($context["dropmenu"] ?? null) == "dropdown")) {
                // line 265
                echo "
                                ";
                // line 266
                $context["menucol"] = "";
                // line 267
                echo "                                ";
                if (twig_get_attribute($this->env, $this->source, (($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 = $context["items"]) && is_array($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57) || $__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 instanceof ArrayAccess ? ($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57[0] ?? null) : null), "col", array())) {
                    // line 268
                    echo "                                ";
                    $context["menucol"] = twig_get_attribute($this->env, $this->source, (($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 = $context["items"]) && is_array($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9) || $__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 instanceof ArrayAccess ? ($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9[0] ?? null) : null), "col", array());
                    // line 269
                    echo "                                ";
                }
                // line 270
                echo "
                                <ul class=\"dropdown-menu ";
                // line 271
                echo twig_escape_filter($this->env, ($context["menucol"] ?? null), "html", null, true);
                echo "\">
                                    ";
                // line 272
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($context["items"]);
                foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                    // line 273
                    echo "                                    <li>
                                        ";
                    // line 274
                    if (twig_get_attribute($this->env, $this->source, $context["item"], "list", array())) {
                        // line 275
                        echo "                                        <span class=\"dropdown-item mdi mdi-arrow-down\">
                                            <strong class='ml-1 badge bg-indigo'>";
                        // line 276
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                        echo "</strong>
                                        </span>
                                        <div class='ml-2'>
                                            ";
                        // line 279
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["item"], "list", array()));
                        foreach ($context['_seq'] as $context["_key"] => $context["sitem"]) {
                            // line 280
                            echo "                                            <div>
                                                ";
                            // line 281
                            if (twig_get_attribute($this->env, $this->source, $context["sitem"], "name", array())) {
                                // line 282
                                echo "                                                <a class=\"dropdown-item\" href=\"";
                                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["sitem"], "url", array()), "html", null, true);
                                echo "\">
                                                    <span class=\"badge bg-indigo\"></span>
                                                    <span class='ml-2'>";
                                // line 284
                                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["sitem"], "name", array()), "html", null, true);
                                echo "</span>
                                                </a>
                                                ";
                            } else {
                                // line 287
                                echo "                                                &nbsp;
                                                ";
                            }
                            // line 289
                            echo "                                            </div>
                                            ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['sitem'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 291
                        echo "                                        </div>
                                        ";
                    } else {
                        // line 293
                        echo "                                        <a class=\"dropdown-item\" href=\"";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                        echo "\">
                                            ";
                        // line 294
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                        echo "
                                        </a>
                                        ";
                    }
                    // line 297
                    echo "                                    </li>
                                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 299
                echo "                                </ul>
                                ";
            }
            // line 301
            echo "
                            </li>
                            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['categoryName'], $context['items'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 304
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
        // line 319
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            // line 320
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
                // line 321
                echo "                                        ";
                if (twig_get_attribute($this->env, $this->source, $context["loop"], "last", array())) {
                    // line 322
                    echo "                                        ";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "
                                        ";
                }
                // line 324
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
            // line 325
            echo "                                        ";
        }
        // line 326
        echo "                                    </h2>
                                </div>
                                <!-- Page title actions -->
                                <div class=\"col-auto ml-auto d-print-none\">
                                    <ol class=\"breadcrumb\" aria-label=\"breadcrumbs\">


                                        ";
        // line 333
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            // line 334
            echo "
                                        ";
            // line 335
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
                    // line 336
                    echo "                                        <li class=\"breadcrumb-item active\" aria-current=\"page\">";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "</li>
                                        ";
                } elseif (((twig_get_attribute($this->env, $this->source,                 // line 337
$context["loop"], "revindex", array()) > 5) && (twig_get_attribute($this->env, $this->source, $context["loop"], "index", array()) != 1))) {
                    // line 338
                    echo "                                        <li class=\"breadcrumb-item\">
                                            <a href=\"";
                    // line 339
                    echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                    echo "/";
                    echo twig_escape_filter($this->env, $context["src"], "html", null, true);
                    echo "\">...</a>
                                        </li>
                                        ";
                } else {
                    // line 342
                    echo "                                        <li class=\"breadcrumb-item\">
                                            <a href=\"";
                    // line 343
                    echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                    echo "/";
                    echo twig_escape_filter($this->env, $context["src"], "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "</a>
                                        </li>
                                        ";
                }
                // line 345
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
            // line 346
            echo "
                                        ";
        }
        // line 347
        echo " ";
        $this->displayBlock('page', $context, $blocks);
        // line 364
        echo "                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        ";
        // line 372
        if ((($context["reportAutoLogin"] ?? null) != "")) {
            // line 373
            echo "        <div style=\"display:none;visibility: hidden;\">
            <iframe id='iframeReportAutoLogin' src=\"";
            // line 374
            echo twig_escape_filter($this->env, ($context["reportAutoLogin"] ?? null), "html", null, true);
            echo "\"
                style=\"border:0;width:1px;height:1px;\"></iframe>
        </div>
        ";
        }
        // line 378
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
        // line 398
        echo twig_include($this->env, $context, "alert.twig.html");
        echo "


    <script>
        document.body.style.display = \"block\";
        //type log|info|success|warning|error
        toastr.options = {
            \"closeButton\": true,
            \"debug\": false,
            \"newestOnTop\": false,
            \"progressBar\": true,
            \"positionClass\": \"toast-top-right\",
            \"preventDuplicates\": false,
            \"onclick\": null,
            \"showDuration\": \"300\",
            \"hideDuration\": \"1000\",
            \"timeOut\": \"5000\",
            \"extendedTimeOut\": \"1000\",
            \"showEasing\": \"swing\",
            \"hideEasing\": \"linear\",
            \"showMethod\": \"fadeIn\",
            \"hideMethod\": \"fadeOut\"
        };

        function toast(type, msg, title) {
            if (type == \"success\") {
                toastr.success(msg, title);
            } else if (type == \"warning\") {
                toastr.warning(msg, title);
            } else if (type == \"error\") {
                toastr.error(msg, title);
            } else {
                toastr.info(msg, title);
            }
        }
    </script>

</body>

</html>";
    }

    // line 347
    public function block_page($context, array $blocks = array())
    {
        echo " ";
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            echo " ";
        }
        // line 348
        echo "                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class='card'>
                            <div class='card-body'>
                                ";
        // line 355
        if (($context["submenu"] ?? null)) {
            // line 356
            echo "                                <div class=\"mb-2\">
                                    ";
            // line 357
            echo twig_include($this->env, $context, "navigation.twig.html");
            echo "
                                </div>
                                ";
        }
        // line 360
        echo "
                                ";
        // line 361
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "alerts", array()));
        foreach ($context['_seq'] as $context["type"] => $context["alerts"]) {
            echo " ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($context["alerts"]);
            foreach ($context['_seq'] as $context["_key"] => $context["text"]) {
                // line 362
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
            // line 363
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
        return "index.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  921 => 363,  910 => 362,  902 => 361,  899 => 360,  893 => 357,  890 => 356,  888 => 355,  879 => 348,  872 => 347,  828 => 398,  806 => 378,  799 => 374,  796 => 373,  794 => 372,  784 => 364,  781 => 347,  777 => 346,  763 => 345,  753 => 343,  750 => 342,  742 => 339,  739 => 338,  737 => 337,  732 => 336,  713 => 335,  710 => 334,  708 => 333,  699 => 326,  696 => 325,  682 => 324,  676 => 322,  673 => 321,  655 => 320,  653 => 319,  636 => 304,  628 => 301,  624 => 299,  617 => 297,  611 => 294,  606 => 293,  602 => 291,  595 => 289,  591 => 287,  585 => 284,  579 => 282,  577 => 281,  574 => 280,  570 => 279,  564 => 276,  561 => 275,  559 => 274,  556 => 273,  552 => 272,  548 => 271,  545 => 270,  542 => 269,  539 => 268,  536 => 267,  534 => 266,  531 => 265,  529 => 264,  523 => 261,  518 => 259,  509 => 257,  503 => 256,  500 => 255,  497 => 254,  494 => 253,  491 => 252,  488 => 251,  485 => 250,  482 => 249,  480 => 248,  477 => 247,  474 => 246,  471 => 245,  468 => 244,  466 => 243,  463 => 242,  460 => 241,  457 => 240,  454 => 239,  452 => 238,  449 => 237,  445 => 236,  428 => 222,  420 => 217,  413 => 213,  409 => 212,  404 => 210,  378 => 186,  369 => 179,  363 => 178,  354 => 175,  345 => 172,  342 => 171,  338 => 170,  329 => 163,  327 => 162,  318 => 158,  313 => 156,  299 => 145,  258 => 107,  252 => 104,  246 => 101,  239 => 97,  234 => 95,  229 => 93,  223 => 90,  216 => 86,  212 => 85,  207 => 83,  201 => 80,  197 => 79,  193 => 78,  189 => 77,  185 => 76,  181 => 75,  176 => 73,  172 => 72,  168 => 71,  163 => 69,  159 => 68,  154 => 66,  150 => 65,  146 => 64,  140 => 61,  136 => 60,  132 => 59,  128 => 58,  124 => 57,  120 => 56,  116 => 55,  106 => 48,  101 => 46,  97 => 45,  93 => 44,  89 => 43,  84 => 41,  79 => 39,  74 => 37,  68 => 34,  62 => 31,  55 => 27,  50 => 25,  24 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("<!doctype html>
<html class=\"no-js\" lang=\"\" translate='no' lang='en' class='notranslate' translate='no'>

<head>
    <meta charset=\"utf-8\" />
    <meta name='google' content='notranslate' />
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

    <link rel=\"stylesheet\"
        href=\"//cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.8.55/css/materialdesignicons.min.css\">

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

    <script src=\"{{ absoluteURL }}/assets/libs/trumbowyg/dist/trumbowyg.min.js\"></script>
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/libs/trumbowyg/dist/ui/trumbowyg.min.css\" type=\"text/css\"
        media=\"all\" />

    <link rel=\"stylesheet\"
        href=\"{{ absoluteURL }}/assets/libs/trumbowyg/dist/plugins/colors/ui/trumbowyg.colors.min.css\" type=\"text/css\"
        media=\"all\" />

    <script src=\"{{ absoluteURL }}/assets/libs/trumbowyg/dist/plugins/colors/trumbowyg.colors.min.js\"></script>

    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/libs/toastr/toastr.min.css\" type=\"text/css\" media=\"all\" />

    <script src=\"{{ absoluteURL }}/assets/libs/toastr/toastr.min.js\"></script>

    <!--

    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/libs/airdatepicker/dist/css/datepicker.min.css\"
        type=\"text/css\" media=\"all\" />

    <script src=\"{{ absoluteURL }}/assets/libs/airdatepicker/dist/js/datepicker.min.js\"></script>

   
    <script src=\"{{ absoluteURL }}/assets/libs/airdatepicker/dist/js/i18n/datepicker.en.js\"></script>
    -->


    <!-- JavaScript Bundle with Popper -->
    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js\"
        integrity=\"sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4\"
        crossorigin=\"anonymous\"></script>

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

        .ui-datepicker-title {
            display: inline-flex;
        }

        .ui-datepicker-month {
            margin: 0 5px 0 12px !important;
        }

        .ui-datepicker-year {
            margin: 0 !important;
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
                    {% if changeyear==\"allow\" %}
                    <div class=\"nav-item d-none d-md-flex mr-3\">
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
                    </div>
                    {% endif %}

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
                            <span class=\"avatar\">{{ shortname }}</span>
                            <div class=\"d-none d-xl-block pl-2\">
                                <div>{{ uname|raw }}</div>
                                <div class=\"mt-1 small text-muted\">{{ roleCategory }}</div>
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
            </div>
        </header>

        <div class=\"navbar-expand-md\">
            <div class=\"collapse navbar-collapse\" id=\"navbar-menu\">
                <div class=\"navbar navbar-light\">
                    <div class=\"container-fluid\">
                        <ul class=\"navbar-nav\">
                            {% for categoryName, items in menuMain %}

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
                                <a class=\"nav-link {{ dropdownToggle }}\" href=\"{{ navlink }}\" {{ data_toggle }}>
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
                                                <a class=\"dropdown-item\" href=\"{{ sitem.url }}\">
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
                                        <a class=\"dropdown-item\" href=\"{{ item.url }}\">
                                            {{ item.name }}
                                        </a>
                                        {% endif %}
                                    </li>
                                    {% endfor %}
                                </ul>
                                {% endif %}

                            </li>
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
        //type log|info|success|warning|error
        toastr.options = {
            \"closeButton\": true,
            \"debug\": false,
            \"newestOnTop\": false,
            \"progressBar\": true,
            \"positionClass\": \"toast-top-right\",
            \"preventDuplicates\": false,
            \"onclick\": null,
            \"showDuration\": \"300\",
            \"hideDuration\": \"1000\",
            \"timeOut\": \"5000\",
            \"extendedTimeOut\": \"1000\",
            \"showEasing\": \"swing\",
            \"hideEasing\": \"linear\",
            \"showMethod\": \"fadeIn\",
            \"hideMethod\": \"fadeOut\"
        };

        function toast(type, msg, title) {
            if (type == \"success\") {
                toastr.success(msg, title);
            } else if (type == \"warning\") {
                toastr.warning(msg, title);
            } else if (type == \"error\") {
                toastr.error(msg, title);
            } else {
                toastr.info(msg, title);
            }
        }
    </script>

</body>

</html>", "index.twig.html", "F:\\suhail\\Office\\xampp\\htdocs\\newcode\\pupilsight_new\\resources\\templates\\index.twig.html");
    }
}
