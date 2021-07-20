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
    <!--
    <link rel=\"stylesheet\" href=\"";
        // line 25
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/css/fullcalendar.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
-->
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
    <!--
    <script type=\"text/javascript\" src=\"";
        // line 74
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/fullcalendar.min.js?v=1.0\"></script>
    -->

    <script type=\"text/javascript\" src=\"";
        // line 77
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/bootstrap-multiselect.js?v=1.0\"></script>
    <script src=\"";
        // line 78
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/selectize.min.js\"></script>
    <script src=\"";
        // line 79
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/tabler.min.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 80
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/thickbox/thickbox-compressed.js?v=1.0\"></script>
    <script src=\"";
        // line 81
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/select2.js\"></script>
    <script type=\"text/javascript\" src=\"";
        // line 82
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/jquery.form.js?v=1.0\"></script>


    <script type=\"text/javascript\" src=\"";
        // line 85
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/js/Sortable.js?v=1.0\"></script>

    <script src=\"";
        // line 87
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/trumbowyg/dist/trumbowyg.min.js\"></script>
    <link rel=\"stylesheet\" href=\"";
        // line 88
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/trumbowyg/dist/ui/trumbowyg.min.css\" type=\"text/css\"
        media=\"all\" />

    <link rel=\"stylesheet\"
        href=\"";
        // line 92
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/trumbowyg/dist/plugins/colors/ui/trumbowyg.colors.min.css\" type=\"text/css\"
        media=\"all\" />

    <script src=\"";
        // line 95
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/trumbowyg/dist/plugins/colors/trumbowyg.colors.min.js\"></script>

    <link rel=\"stylesheet\" href=\"";
        // line 97
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/toastr/toastr.min.css\" type=\"text/css\" media=\"all\" />

    <script src=\"";
        // line 99
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/toastr/toastr.min.js\"></script>

    <!--

    <link rel=\"stylesheet\" href=\"";
        // line 103
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/airdatepicker/dist/css/datepicker.min.css\"
        type=\"text/css\" media=\"all\" />

    <script src=\"";
        // line 106
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/assets/libs/airdatepicker/dist/js/datepicker.min.js\"></script>

   
    <script src=\"";
        // line 109
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
        // line 147
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
        // line 158
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/index.php\"
                    class=\"navbar-brand navbar-brand-autodark d-none-navbar-horizontal pr-0 pr-md-3\">
                    <img src=\"";
        // line 160
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/";
        echo twig_escape_filter($this->env, (((isset($context["organisationLogo"]) || array_key_exists("organisationLogo", $context))) ? (_twig_default_filter(($context["organisationLogo"] ?? null), " /themes/Default/img/logo.png ")) : (" /themes/Default/img/logo.png ")), "html", null, true);
        echo "\"
                        alt=\"Tabler\" class=\"navbar-brand-image\">
                </a>
                <div class=\"navbar-nav flex-row order-md-last\">
                    ";
        // line 164
        if ((($context["changeyear"] ?? null) == "allow")) {
            // line 165
            echo "                    <div class=\"nav-item d-none d-md-flex mr-3\">
                        <form action=\"yearSwitcherProcess.php\" method=\"post\">
                            <div style=\"display:inline-flex;\">
                                <div class=\"input-group\">
                                    <select name=\"pupilsightSchoolYearID\" style=\"float:left;width: 120px;\"
                                        id=\"academicYearChange\">
                                        <option value=\"\">Select Academic Year</option>
                                        ";
            // line 172
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["academicYear"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["ay"]) {
                // line 173
                echo "                                        ";
                if ((twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()) == ($context["pupilsightSchoolYearID"] ?? null))) {
                    // line 174
                    echo "                                        <option value='";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()), "html", null, true);
                    echo "' selected>";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "name", array()), "html", null, true);
                    echo "
                                        </option>
                                        ";
                } else {
                    // line 177
                    echo "                                        <option value='";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "pupilsightSchoolYearID", array()), "html", null, true);
                    echo "'>";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ay"], "name", array()), "html", null, true);
                    echo "
                                        </option>
                                        ";
                }
                // line 180
                echo "                                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['ay'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 181
            echo "                                    </select>
                                    <button type=\"submit\" class=\"btn btn-white\">Change Year</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    ";
        }
        // line 188
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
        // line 212
        echo twig_escape_filter($this->env, ($context["shortname"] ?? null), "html", null, true);
        echo "</span>
                            <div class=\"d-none d-xl-block pl-2\">
                                <div>";
        // line 214
        echo ($context["uname"] ?? null);
        echo "</div>
                                <div class=\"mt-1 small text-muted\">";
        // line 215
        echo twig_escape_filter($this->env, ($context["roleCategory"] ?? null), "html", null, true);
        echo "</div>
                            </div>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right\">
                            <a class=\"dropdown-item\" href=\"";
        // line 219
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/index.php?q=preferences.php\">
                                <span class=\"mdi mdi-account-cog-outline mr-2\"></span>
                                Preferences
                            </a>
                            <div class=\"dropdown-divider\"></div>
                            <a class=\"dropdown-item\" href=\"";
        // line 224
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
        // line 238
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["menuMain"] ?? null));
        foreach ($context['_seq'] as $context["categoryName"] => $context["items"]) {
            // line 239
            echo "
                            ";
            // line 240
            $context["menuSelect"] = "";
            // line 241
            echo "                            ";
            if (($context["categoryName"] == ($context["currentModule"] ?? null))) {
                // line 242
                echo "                            ";
                $context["menuSelect"] = "active";
                // line 243
                echo "                            ";
            }
            // line 244
            echo "
                            ";
            // line 245
            $context["dropmenu"] = "";
            // line 246
            echo "                            ";
            $context["dropdownToggle"] = "";
            // line 247
            echo "                            ";
            $context["navlink"] = "#navbar-base";
            // line 248
            echo "                            ";
            $context["data_toggle"] = "";
            // line 249
            echo "
                            ";
            // line 250
            if ((twig_length_filter($this->env, $context["items"]) > 1)) {
                // line 251
                echo "                            ";
                $context["dropmenu"] = "dropdown";
                // line 252
                echo "                            ";
                $context["dropdownToggle"] = "dropdown-toggle";
                // line 253
                echo "                            ";
                $context["data_toggle"] = "data-toggle=dropdown role=button aria-expanded=false";
                // line 254
                echo "                            ";
            } else {
                // line 255
                echo "                            ";
                $context["navlink"] = twig_get_attribute($this->env, $this->source, (($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 = $context["items"]) && is_array($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5) || $__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 instanceof ArrayAccess ? ($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5[0] ?? null) : null), "url", array());
                // line 256
                echo "                            ";
            }
            // line 257
            echo "
                            <li class=\"nav-item ";
            // line 258
            echo twig_escape_filter($this->env, ($context["dropmenu"] ?? null), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, ($context["menuSelect"] ?? null), "html", null, true);
            echo "\">
                                <a class=\"nav-link ";
            // line 259
            echo twig_escape_filter($this->env, ($context["dropdownToggle"] ?? null), "html", null, true);
            echo "\" href=\"";
            echo twig_escape_filter($this->env, ($context["navlink"] ?? null), "html", null, true);
            echo "\" ";
            echo twig_escape_filter($this->env, ($context["data_toggle"] ?? null), "html", null, true);
            echo ">
                                    <span
                                        class=\"nav-link-icon d-md-none d-lg-inline-block ";
            // line 261
            echo twig_escape_filter($this->env, (($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a = ($context["menuMainIcon"] ?? null)) && is_array($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a) || $__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a instanceof ArrayAccess ? ($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a[$context["categoryName"]] ?? null) : null), "html", null, true);
            echo "\"></span>
                                    <span class=\"nav-link-title\">
                                        ";
            // line 263
            echo twig_escape_filter($this->env, $context["categoryName"], "html", null, true);
            echo "
                                    </span>
                                </a>
                                ";
            // line 266
            if ((($context["dropmenu"] ?? null) == "dropdown")) {
                // line 267
                echo "
                                ";
                // line 268
                $context["menucol"] = "";
                // line 269
                echo "                                ";
                if (twig_get_attribute($this->env, $this->source, (($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 = $context["items"]) && is_array($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57) || $__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 instanceof ArrayAccess ? ($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57[0] ?? null) : null), "col", array())) {
                    // line 270
                    echo "                                ";
                    $context["menucol"] = twig_get_attribute($this->env, $this->source, (($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 = $context["items"]) && is_array($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9) || $__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 instanceof ArrayAccess ? ($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9[0] ?? null) : null), "col", array());
                    // line 271
                    echo "                                ";
                }
                // line 272
                echo "
                                <ul class=\"dropdown-menu ";
                // line 273
                echo twig_escape_filter($this->env, ($context["menucol"] ?? null), "html", null, true);
                echo "\">
                                    ";
                // line 274
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($context["items"]);
                foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                    // line 275
                    echo "                                    <li>
                                        ";
                    // line 276
                    if (twig_get_attribute($this->env, $this->source, $context["item"], "list", array())) {
                        // line 277
                        echo "                                        <span class=\"dropdown-item mdi mdi-arrow-down\">
                                            <strong class='ml-1 badge bg-indigo'>";
                        // line 278
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                        echo "</strong>
                                        </span>
                                        <div class='ml-2'>
                                            ";
                        // line 281
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["item"], "list", array()));
                        foreach ($context['_seq'] as $context["_key"] => $context["sitem"]) {
                            // line 282
                            echo "                                            <div>
                                                ";
                            // line 283
                            if (twig_get_attribute($this->env, $this->source, $context["sitem"], "name", array())) {
                                // line 284
                                echo "                                                <a class=\"dropdown-item\" href=\"";
                                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["sitem"], "url", array()), "html", null, true);
                                echo "\">
                                                    <span class=\"badge bg-indigo\"></span>
                                                    <span class='ml-2'>";
                                // line 286
                                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["sitem"], "name", array()), "html", null, true);
                                echo "</span>
                                                </a>
                                                ";
                            } else {
                                // line 289
                                echo "                                                &nbsp;
                                                ";
                            }
                            // line 291
                            echo "                                            </div>
                                            ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['sitem'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 293
                        echo "                                        </div>
                                        ";
                    } else {
                        // line 295
                        echo "                                        <a class=\"dropdown-item\" href=\"";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                        echo "\">
                                            ";
                        // line 296
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), "html", null, true);
                        echo "
                                        </a>
                                        ";
                    }
                    // line 299
                    echo "                                    </li>
                                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 301
                echo "                                </ul>
                                ";
            }
            // line 303
            echo "
                            </li>
                            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['categoryName'], $context['items'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 306
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
        // line 321
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            // line 322
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
                // line 323
                echo "                                        ";
                if (twig_get_attribute($this->env, $this->source, $context["loop"], "last", array())) {
                    // line 324
                    echo "                                        ";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "
                                        ";
                }
                // line 326
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
            // line 327
            echo "                                        ";
        }
        // line 328
        echo "                                    </h2>
                                </div>
                                <!-- Page title actions -->
                                <div class=\"col-auto ml-auto d-print-none\">
                                    <ol class=\"breadcrumb\" aria-label=\"breadcrumbs\">


                                        ";
        // line 335
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            // line 336
            echo "
                                        ";
            // line 337
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
                    // line 338
                    echo "                                        <li class=\"breadcrumb-item active\" aria-current=\"page\">";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "</li>
                                        ";
                } elseif (((twig_get_attribute($this->env, $this->source,                 // line 339
$context["loop"], "revindex", array()) > 5) && (twig_get_attribute($this->env, $this->source, $context["loop"], "index", array()) != 1))) {
                    // line 340
                    echo "                                        <li class=\"breadcrumb-item\">
                                            <a href=\"";
                    // line 341
                    echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                    echo "/";
                    echo twig_escape_filter($this->env, $context["src"], "html", null, true);
                    echo "\">...</a>
                                        </li>
                                        ";
                } else {
                    // line 344
                    echo "                                        <li class=\"breadcrumb-item\">
                                            <a href=\"";
                    // line 345
                    echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                    echo "/";
                    echo twig_escape_filter($this->env, $context["src"], "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $context["title"], "html", null, true);
                    echo "</a>
                                        </li>
                                        ";
                }
                // line 347
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
            // line 348
            echo "
                                        ";
        }
        // line 349
        echo " ";
        $this->displayBlock('page', $context, $blocks);
        // line 366
        echo "                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        ";
        // line 374
        if ((($context["reportAutoLogin"] ?? null) != "")) {
            // line 375
            echo "        <div style=\"display:none;visibility: hidden;\">
            <iframe id='iframeReportAutoLogin' src=\"";
            // line 376
            echo twig_escape_filter($this->env, ($context["reportAutoLogin"] ?? null), "html", null, true);
            echo "\"
                style=\"border:0;width:1px;height:1px;\"></iframe>
        </div>
        ";
        }
        // line 380
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
        // line 400
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

    // line 349
    public function block_page($context, array $blocks = array())
    {
        echo " ";
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) {
            echo " ";
        }
        // line 350
        echo "                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class='card'>
                            <div class='card-body'>
                                ";
        // line 357
        if (($context["submenu"] ?? null)) {
            // line 358
            echo "                                <div class=\"mb-2\">
                                    ";
            // line 359
            echo twig_include($this->env, $context, "navigation.twig.html");
            echo "
                                </div>
                                ";
        }
        // line 362
        echo "
                                ";
        // line 363
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "alerts", array()));
        foreach ($context['_seq'] as $context["type"] => $context["alerts"]) {
            echo " ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($context["alerts"]);
            foreach ($context['_seq'] as $context["_key"] => $context["text"]) {
                // line 364
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
            // line 365
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
        return array (  923 => 365,  912 => 364,  904 => 363,  901 => 362,  895 => 359,  892 => 358,  890 => 357,  881 => 350,  874 => 349,  830 => 400,  808 => 380,  801 => 376,  798 => 375,  796 => 374,  786 => 366,  783 => 349,  779 => 348,  765 => 347,  755 => 345,  752 => 344,  744 => 341,  741 => 340,  739 => 339,  734 => 338,  715 => 337,  712 => 336,  710 => 335,  701 => 328,  698 => 327,  684 => 326,  678 => 324,  675 => 323,  657 => 322,  655 => 321,  638 => 306,  630 => 303,  626 => 301,  619 => 299,  613 => 296,  608 => 295,  604 => 293,  597 => 291,  593 => 289,  587 => 286,  581 => 284,  579 => 283,  576 => 282,  572 => 281,  566 => 278,  563 => 277,  561 => 276,  558 => 275,  554 => 274,  550 => 273,  547 => 272,  544 => 271,  541 => 270,  538 => 269,  536 => 268,  533 => 267,  531 => 266,  525 => 263,  520 => 261,  511 => 259,  505 => 258,  502 => 257,  499 => 256,  496 => 255,  493 => 254,  490 => 253,  487 => 252,  484 => 251,  482 => 250,  479 => 249,  476 => 248,  473 => 247,  470 => 246,  468 => 245,  465 => 244,  462 => 243,  459 => 242,  456 => 241,  454 => 240,  451 => 239,  447 => 238,  430 => 224,  422 => 219,  415 => 215,  411 => 214,  406 => 212,  380 => 188,  371 => 181,  365 => 180,  356 => 177,  347 => 174,  344 => 173,  340 => 172,  331 => 165,  329 => 164,  320 => 160,  315 => 158,  301 => 147,  260 => 109,  254 => 106,  248 => 103,  241 => 99,  236 => 97,  231 => 95,  225 => 92,  218 => 88,  214 => 87,  209 => 85,  203 => 82,  199 => 81,  195 => 80,  191 => 79,  187 => 78,  183 => 77,  177 => 74,  172 => 72,  168 => 71,  163 => 69,  159 => 68,  154 => 66,  150 => 65,  146 => 64,  140 => 61,  136 => 60,  132 => 59,  128 => 58,  124 => 57,  120 => 56,  116 => 55,  106 => 48,  101 => 46,  97 => 45,  93 => 44,  89 => 43,  84 => 41,  79 => 39,  74 => 37,  68 => 34,  62 => 31,  55 => 27,  50 => 25,  24 => 1,);
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
    <!--
    <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/assets/css/fullcalendar.min.css?v=1.0\" type=\"text/css\" media=\"all\" />
-->
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
    <!--
    <script type=\"text/javascript\" src=\"{{ absoluteURL }}/assets/js/fullcalendar.min.js?v=1.0\"></script>
    -->

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
