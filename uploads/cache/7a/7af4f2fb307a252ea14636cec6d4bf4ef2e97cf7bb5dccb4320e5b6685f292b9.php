<?php

/* components/paginatedTable.twig.html */
class __TwigTemplate_36c94239a672a0761b4c21b3a2e34f6d5ba02099663bdd41fc153fd89fa642a4 extends Twig_Template
{
    private $source;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = array(
            'table' => array($this, 'block_table'),
            'header' => array($this, 'block_header'),
            'blankslate' => array($this, 'block_blankslate'),
            'tableInner' => array($this, 'block_tableInner'),
            'footer' => array($this, 'block_footer'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        if (twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getTitle", array())) {
            // line 2
            echo "<h2>";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getTitle", array()), "html", null, true);
            echo "</h2>
";
        }
        // line 3
        echo " ";
        if (twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getDescription", array())) {
            // line 4
            echo "<p>";
            echo twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getDescription", array());
            echo "</p>
";
        }
        // line 5
        echo " ";
        $this->displayBlock('table', $context, $blocks);
        // line 166
        echo "<script>
    \$(document).ready( function () {
        \$('#expore_tbl').DataTable({
            \"pageLength\": 25,
            \"lengthMenu\": [[10, 25, 50, 250, -1], [10, 25, 50, 250, \"All\"]],
            \"sDom\": '<\"top\"lfp>rt<\"bottom\"ip><\"clear\">'
        });
        \$(\".dataTables_length\" ).find(\"select\").css(\"width\",\"90px\");
        \$(\".dataTables_length\" ).find(\"select\").css(\"display\",\"inline-block\");
    });
</script>";
    }

    // line 5
    public function block_table($context, array $blocks = array())
    {
        // line 6
        echo "
<header class=\"relative\">
    ";
        // line 8
        $this->displayBlock('header', $context, $blocks);
        // line 13
        echo "</header>

";
        // line 15
        if ((( !($context["rows"] ?? null) &&  !($context["isFiltered"] ?? null)) && (twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getResultCount", array()) == 0))) {
            // line 16
            echo "<!-- <div class=\"h-48 rounded-sm border bg-gray shadow-inner overflow-hidden\">
    ";
            // line 17
            $this->displayBlock('blankslate', $context, $blocks);
            // line 22
            echo "</div> -->
<div class=\"table-responsive dataTables_wrapper \">
    <table class=\"table\" id=\"expore_tbls\">
        <!--<div class=\"overflow-x-auto overflow-y-visible\">
            <table class=\"";
            // line 26
            echo twig_escape_filter($this->env, ($context["class"] ?? null), "html", null, true);
            echo " w-full\" cellspacing=0>-->
        <thead>
            ";
            // line 28
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["headers"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["headerRow"]) {
                // line 29
                echo "            <tr>
                ";
                // line 30
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["columns"] ?? null));
                foreach ($context['_seq'] as $context["columnIndex"] => $context["column"]) {
                    echo " ";
                    $context["th"] = (($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 = $context["headerRow"]) && is_array($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5) || $__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 instanceof ArrayAccess ? ($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5[$context["columnIndex"]] ?? null) : null);
                    echo " ";
                    if (($context["th"] ?? null)) {
                        // line 31
                        echo "                <th ";
                        echo twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getAttributeString", array());
                        echo ">
                    ";
                        // line 32
                        echo twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getOutput", array());
                        echo " ";
                        if (twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getData", array(0 => "description"), "method")) {
                            echo " ";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getData", array(0 => "description"), "method"), "html", null, true);
                            echo "
                    ";
                        }
                        // line 34
                        echo "                </th>
                ";
                    }
                    // line 35
                    echo " ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['columnIndex'], $context['column'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 36
                echo "            </tr>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['headerRow'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 38
            echo "
        </thead>
        <tbody>

        </tbody>

    </table>

</div>
";
        } else {
            // line 47
            echo " ";
            $this->displayBlock('tableInner', $context, $blocks);
            // line 159
            echo " ";
        }
        // line 160
        echo "
<footer>
    ";
        // line 162
        $this->displayBlock('footer', $context, $blocks);
        // line 163
        echo "</footer>

";
    }

    // line 8
    public function block_header($context, array $blocks = array())
    {
        echo " ";
        if (twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getHeader", array())) {
            // line 9
            echo "    <div class=\"linkTop\">
        ";
            // line 10
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getHeader", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["action"]) {
                echo " ";
                echo twig_get_attribute($this->env, $this->source, $context["action"], "getOutput", array());
                echo " ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['action'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 11
            echo "    </div>
    ";
        }
        // line 12
        echo " ";
    }

    // line 17
    public function block_blankslate($context, array $blocks = array())
    {
        // line 18
        echo "    <div class=\"w-full h-full flex flex-col items-center justify-center text-gray text-lg\">
        ";
        // line 19
        if (($context["isFiltered"] ?? null)) {
            echo " ";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("No results matched your search.")), "html", null, true);
            echo " ";
        } elseif (($context["blankSlate"] ?? null)) {
            echo " ";
            echo ($context["blankSlate"] ?? null);
            echo " ";
        } else {
            echo " ";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("There are no records to display.")), "html", null, true);
            echo " ";
        }
        // line 20
        echo "    </div>
    ";
    }

    // line 47
    public function block_tableInner($context, array $blocks = array())
    {
        // line 48
        echo "
";
        // line 49
        if ((twig_length_filter($this->env, ($context["headers"] ?? null)) > 0)) {
            // line 50
            echo "<style>
@media only screen and (max-width: 760px), (min-device-width: 768px) and (max-device-width: 1024px)  {
    /* Force table to not be like tables anymore */
    table, thead, tbody, th, td, tr {
        display: block;
    }
    /* Hide table headers (but not display: none;, for accessibility) */
    thead tr {
        position: absolute !important;
        top: -9999px !important;
        left: -9999px !important;
    }
    tr {
        border: 1px solid #ccc !important;
    }
    .p-2 {
        padding: 0 !important;
    }
    .dataTables_wrapper .table tbody tr td, td {
        /* Behave  like a \"row\" */
        border: none !important;
        border-bottom: 1px solid #eee;
        position: relative !important;
        padding-left: 50% !important;
    }
    td:before {
        /* Now like a table header */
        position: absolute !important;
        /* Top/left values mimic padding */
        top: 6px !important;
        left: 6px !important;
        width: 45% !important;
        padding-right: 10px !important;
        white-space: nowrap !important;
    }

    ";
            // line 86
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["headers"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["headerRow"]) {
                // line 87
                echo "                ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["columns"] ?? null));
                foreach ($context['_seq'] as $context["columnIndex"] => $context["column"]) {
                    echo " ";
                    $context["th"] = (($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a = $context["headerRow"]) && is_array($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a) || $__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a instanceof ArrayAccess ? ($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a[$context["columnIndex"]] ?? null) : null);
                    echo " ";
                    if (($context["th"] ?? null)) {
                        // line 88
                        echo "                td:nth-of-type(";
                        echo twig_escape_filter($this->env, ($context["columnIndex"] + 1), "html", null, true);
                        echo "):before {
                    content: \"";
                        // line 89
                        echo twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getOutput", array());
                        echo "\";
                }
                ";
                    }
                    // line 91
                    echo " ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['columnIndex'], $context['column'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 92
                echo "    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['headerRow'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 93
            echo "}
</style>
";
        }
        // line 96
        echo "<div class=\"table-responsive dataTables_wrapper\">
    <table class=\"table\" id=\"expore_tbl\">
        <!--<div class=\"overflow-x-auto overflow-y-visible\">
            <table class=\"";
        // line 99
        echo twig_escape_filter($this->env, ($context["class"] ?? null), "html", null, true);
        echo " w-full\" cellspacing=0>-->
        <thead>
            ";
        // line 101
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["headers"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["headerRow"]) {
            // line 102
            echo "            <tr>
                ";
            // line 103
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["columns"] ?? null));
            foreach ($context['_seq'] as $context["columnIndex"] => $context["column"]) {
                echo " ";
                $context["th"] = (($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 = $context["headerRow"]) && is_array($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57) || $__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 instanceof ArrayAccess ? ($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57[$context["columnIndex"]] ?? null) : null);
                echo " ";
                if (($context["th"] ?? null)) {
                    // line 104
                    echo "                <th ";
                    echo twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getAttributeString", array());
                    echo ">
                    ";
                    // line 105
                    echo twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getOutput", array());
                    echo " ";
                    if (twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getData", array(0 => "description"), "method")) {
                        echo " ";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getData", array(0 => "description"), "method"), "html", null, true);
                        echo "
                    ";
                    }
                    // line 107
                    echo "                </th>
                ";
                }
                // line 108
                echo " ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['columnIndex'], $context['column'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 109
            echo "            </tr>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['headerRow'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 111
        echo "
        </thead>
        <tbody>
            ";
        // line 114
        if (( !($context["rows"] ?? null) && ($context["isFiltered"] ?? null))) {
            // line 115
            echo "            <tr class=\"h-48 bg-gray shadow-inner\">
                <td class=\"p-0\" colspan=\"";
            // line 116
            echo twig_escape_filter($this->env, twig_length_filter($this->env, ($context["columns"] ?? null)), "html", null, true);
            echo "\">
                    ";
            // line 117
            $this->displayBlock("blankslate", $context, $blocks);
            echo "
                </td>
            </tr>
            ";
        }
        // line 120
        echo " ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["rows"] ?? null));
        foreach ($context['_seq'] as $context["rowIndex"] => $context["rowData"]) {
            echo " ";
            $context["row"] = twig_get_attribute($this->env, $this->source, $context["rowData"], "row", array());
            // line 121
            echo "
            <tr ";
            // line 122
            echo twig_get_attribute($this->env, $this->source, ($context["row"] ?? null), "getAttributeString", array());
            echo ">
                ";
            // line 123
            echo twig_get_attribute($this->env, $this->source, ($context["row"] ?? null), "getPrepended", array());
            echo " ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["columns"] ?? null));
            foreach ($context['_seq'] as $context["columnIndex"] => $context["column"]) {
                // line 124
                echo "                ";
                $context["cell"] = (($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 = twig_get_attribute($this->env, $this->source, $context["rowData"], "cells", array())) && is_array($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9) || $__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 instanceof ArrayAccess ? ($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9[$context["columnIndex"]] ?? null) : null);
                // line 125
                echo "
                <td ";
                // line 126
                echo twig_get_attribute($this->env, $this->source, ($context["cell"] ?? null), "getAttributeString", array());
                echo ">
                    ";
                // line 127
                echo twig_get_attribute($this->env, $this->source, ($context["cell"] ?? null), "getPrepended", array());
                echo " ";
                if ((twig_get_attribute($this->env, $this->source, $context["column"], "getID", array()) == "actions")) {
                    // line 128
                    echo "                    <nav class=\"relative group\">
                        ";
                    // line 129
                    twig_get_attribute($this->env, $this->source, $context["column"], "getOutput", array(0 => twig_get_attribute($this->env, $this->source, $context["rowData"], "data", array())), "method");
                    echo " ";
                    $context["actions"] = twig_get_attribute($this->env, $this->source, $context["column"], "getActions", array());
                    // line 130
                    echo "
                        <div
                            class=\"";
                    // line 132
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["column"], "getClass", array(), "method"), "html", null, true);
                    echo " ";
                    echo (((twig_length_filter($this->env, ($context["actions"] ?? null)) == 1)) ? ("flex -m-2 sm:m-0") : ("hidden-1 group-hover:flex sm:flex absolute sm:static top-0 right-0 -mr-1 rounded  sm:shadow-none sm:bg-transparent px-1 -mt-3 sm:m-0 sm:p-0 z-10"));
                    echo "\">
                            ";
                    // line 133
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable(($context["actions"] ?? null));
                    foreach ($context['_seq'] as $context["actionName"] => $context["action"]) {
                        echo " ";
                        twig_get_attribute($this->env, $this->source, $context["action"], "addClass", array(0 => ""), "method");
                        // line 134
                        echo "                            ";
                        echo twig_get_attribute($this->env, $this->source, $context["action"], "getOutput", array(0 => twig_get_attribute($this->env, $this->source, $context["rowData"], "data", array()), 1 => twig_get_attribute($this->env, $this->source, $context["column"], "getParams", array())), "method");
                        echo " ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['actionName'], $context['action'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 135
                    echo "                        </div>

                        ";
                    // line 137
                    if ((twig_length_filter($this->env, ($context["actions"] ?? null)) > 1)) {
                        // line 138
                        echo "                        <!--
<button class=\"block sm:hidden rounded mx-auto my-1 px-1 py-2 bg-gray text-2xl text-gray font-sans font-bold leading-none\" onClick=\"event.preventDefault();\" onTouchEnd=\"event.preventDefault();\">
                            <span class=\"block -mt-3\">...</span>
                        </button> 
                        -->

                        ";
                    }
                    // line 145
                    echo "                    </nav>

                    ";
                } else {
                    // line 147
                    echo " ";
                    echo twig_get_attribute($this->env, $this->source, $context["column"], "getOutput", array(0 => twig_get_attribute($this->env, $this->source, $context["rowData"], "data", array())), "method");
                    echo "&nbsp; ";
                }
                echo " ";
                echo twig_get_attribute($this->env, $this->source, ($context["cell"] ?? null), "getAppended", array());
                echo "
                </td>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['columnIndex'], $context['column'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 149
            echo " ";
            echo twig_get_attribute($this->env, $this->source, ($context["row"] ?? null), "getAppended", array());
            echo "
            </tr>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['rowIndex'], $context['rowData'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 152
        echo "        </tbody>

    </table>
</div>



";
    }

    // line 162
    public function block_footer($context, array $blocks = array())
    {
        echo " ";
    }

    public function getTemplateName()
    {
        return "components/paginatedTable.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  505 => 162,  494 => 152,  484 => 149,  470 => 147,  465 => 145,  456 => 138,  454 => 137,  450 => 135,  442 => 134,  436 => 133,  430 => 132,  426 => 130,  422 => 129,  419 => 128,  415 => 127,  411 => 126,  408 => 125,  405 => 124,  399 => 123,  395 => 122,  392 => 121,  385 => 120,  378 => 117,  374 => 116,  371 => 115,  369 => 114,  364 => 111,  357 => 109,  351 => 108,  347 => 107,  338 => 105,  333 => 104,  325 => 103,  322 => 102,  318 => 101,  313 => 99,  308 => 96,  303 => 93,  297 => 92,  291 => 91,  285 => 89,  280 => 88,  271 => 87,  267 => 86,  229 => 50,  227 => 49,  224 => 48,  221 => 47,  216 => 20,  202 => 19,  199 => 18,  196 => 17,  192 => 12,  188 => 11,  177 => 10,  174 => 9,  169 => 8,  163 => 163,  161 => 162,  157 => 160,  154 => 159,  151 => 47,  139 => 38,  132 => 36,  126 => 35,  122 => 34,  113 => 32,  108 => 31,  100 => 30,  97 => 29,  93 => 28,  88 => 26,  82 => 22,  80 => 17,  77 => 16,  75 => 15,  71 => 13,  69 => 8,  65 => 6,  62 => 5,  48 => 166,  45 => 5,  39 => 4,  36 => 3,  30 => 2,  28 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{% if table.getTitle %}
<h2>{{ table.getTitle }}</h2>
{% endif %} {% if table.getDescription %}
<p>{{ table.getDescription|raw }}</p>
{% endif %} {% block table %}

<header class=\"relative\">
    {% block header %} {% if table.getHeader %}
    <div class=\"linkTop\">
        {% for action in table.getHeader %} {{ action.getOutput|raw }} {% endfor %}
    </div>
    {% endif %} {% endblock header %}
</header>

{% if not rows and not isFiltered and dataSet.getResultCount == 0 %}
<!-- <div class=\"h-48 rounded-sm border bg-gray shadow-inner overflow-hidden\">
    {% block blankslate %}
    <div class=\"w-full h-full flex flex-col items-center justify-center text-gray text-lg\">
        {% if isFiltered %} {{ __('No results matched your search.') }} {% elseif blankSlate %} {{ blankSlate|raw }} {% else %} {{ __('There are no records to display.') }} {% endif %}
    </div>
    {% endblock blankslate %}
</div> -->
<div class=\"table-responsive dataTables_wrapper \">
    <table class=\"table\" id=\"expore_tbls\">
        <!--<div class=\"overflow-x-auto overflow-y-visible\">
            <table class=\"{{ class }} w-full\" cellspacing=0>-->
        <thead>
            {% for headerRow in headers %}
            <tr>
                {% for columnIndex, column in columns %} {% set th = headerRow[columnIndex] %} {% if th %}
                <th {{ th.getAttributeString|raw }}>
                    {{ th.getOutput|raw }} {% if th.getData('description') %} {{ th.getData('description') }}
                    {% endif %}
                </th>
                {% endif %} {% endfor %}
            </tr>
            {% endfor %}

        </thead>
        <tbody>

        </tbody>

    </table>

</div>
{% else %} {% block tableInner %}

{% if headers|length > 0 %}
<style>
@media only screen and (max-width: 760px), (min-device-width: 768px) and (max-device-width: 1024px)  {
    /* Force table to not be like tables anymore */
    table, thead, tbody, th, td, tr {
        display: block;
    }
    /* Hide table headers (but not display: none;, for accessibility) */
    thead tr {
        position: absolute !important;
        top: -9999px !important;
        left: -9999px !important;
    }
    tr {
        border: 1px solid #ccc !important;
    }
    .p-2 {
        padding: 0 !important;
    }
    .dataTables_wrapper .table tbody tr td, td {
        /* Behave  like a \"row\" */
        border: none !important;
        border-bottom: 1px solid #eee;
        position: relative !important;
        padding-left: 50% !important;
    }
    td:before {
        /* Now like a table header */
        position: absolute !important;
        /* Top/left values mimic padding */
        top: 6px !important;
        left: 6px !important;
        width: 45% !important;
        padding-right: 10px !important;
        white-space: nowrap !important;
    }

    {% for headerRow in headers %}
                {% for columnIndex, column in columns %} {% set th = headerRow[columnIndex] %} {% if th %}
                td:nth-of-type({{columnIndex+1}}):before {
                    content: \"{{ th.getOutput|raw }}\";
                }
                {% endif %} {% endfor %}
    {% endfor %}
}
</style>
{% endif %}
<div class=\"table-responsive dataTables_wrapper\">
    <table class=\"table\" id=\"expore_tbl\">
        <!--<div class=\"overflow-x-auto overflow-y-visible\">
            <table class=\"{{ class }} w-full\" cellspacing=0>-->
        <thead>
            {% for headerRow in headers %}
            <tr>
                {% for columnIndex, column in columns %} {% set th = headerRow[columnIndex] %} {% if th %}
                <th {{ th.getAttributeString|raw }}>
                    {{ th.getOutput|raw }} {% if th.getData('description') %} {{ th.getData('description') }}
                    {% endif %}
                </th>
                {% endif %} {% endfor %}
            </tr>
            {% endfor %}

        </thead>
        <tbody>
            {% if not rows and isFiltered %}
            <tr class=\"h-48 bg-gray shadow-inner\">
                <td class=\"p-0\" colspan=\"{{ columns|length }}\">
                    {{ block('blankslate') }}
                </td>
            </tr>
            {% endif %} {% for rowIndex, rowData in rows %} {% set row = rowData.row %}

            <tr {{ row.getAttributeString|raw }}>
                {{ row.getPrepended|raw }} {% for columnIndex, column in columns %}
                {% set cell = rowData.cells[columnIndex] %}

                <td {{ cell.getAttributeString|raw }}>
                    {{ cell.getPrepended|raw }} {% if column.getID == \"actions\" %}
                    <nav class=\"relative group\">
                        {% do column.getOutput(rowData.data) %} {% set actions = column.getActions %}

                        <div
                            class=\"{{ column.getClass() }} {{ actions|length == 1 ? 'flex -m-2 sm:m-0' : 'hidden-1 group-hover:flex sm:flex absolute sm:static top-0 right-0 -mr-1 rounded  sm:shadow-none sm:bg-transparent px-1 -mt-3 sm:m-0 sm:p-0 z-10' }}\">
                            {% for actionName, action in actions %} {% do action.addClass('') %}
                            {{ action.getOutput(rowData.data, column.getParams)|raw }} {% endfor %}
                        </div>

                        {% if actions|length > 1 %}
                        <!--
<button class=\"block sm:hidden rounded mx-auto my-1 px-1 py-2 bg-gray text-2xl text-gray font-sans font-bold leading-none\" onClick=\"event.preventDefault();\" onTouchEnd=\"event.preventDefault();\">
                            <span class=\"block -mt-3\">...</span>
                        </button> 
                        -->

                        {% endif %}
                    </nav>

                    {% else %} {{ column.getOutput(rowData.data)|raw }}&nbsp; {% endif %} {{ cell.getAppended|raw }}
                </td>
                {% endfor %} {{ row.getAppended|raw }}
            </tr>
            {% endfor %}
        </tbody>

    </table>
</div>



{% endblock tableInner %} {% endif %}

<footer>
    {% block footer %} {% endblock footer %}
</footer>

{% endblock table %}
<script>
    \$(document).ready( function () {
        \$('#expore_tbl').DataTable({
            \"pageLength\": 25,
            \"lengthMenu\": [[10, 25, 50, 250, -1], [10, 25, 50, 250, \"All\"]],
            \"sDom\": '<\"top\"lfp>rt<\"bottom\"ip><\"clear\">'
        });
        \$(\".dataTables_length\" ).find(\"select\").css(\"width\",\"90px\");
        \$(\".dataTables_length\" ).find(\"select\").css(\"display\",\"inline-block\");
    });
</script>", "components/paginatedTable.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\components\\paginatedTable.twig.html");
    }
}
