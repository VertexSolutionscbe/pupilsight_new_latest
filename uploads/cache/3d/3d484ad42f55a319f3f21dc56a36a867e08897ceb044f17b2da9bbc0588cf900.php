<?php

/* components/dataTable.twig.html */
class __TwigTemplate_b7bdbbfb2a865d5ab2d314efe6bc948d321c4599268880ea287b3a297355b4a1 extends Twig_Template
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
    }

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
<div class=\"table-responsive dataTables_wrapper my-2\">
    <table class=\"table\" id=\"expore_tbl\">
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
            // line 114
            echo " ";
        }
        // line 115
        echo "
<footer>
    ";
        // line 117
        $this->displayBlock('footer', $context, $blocks);
        // line 118
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

<div class=\"table-responsive dataTables_wrapper my-2\">
    <table class=\"table\" id=\"expore_tbl\">
        <!--<div class=\"overflow-x-auto overflow-y-visible\">
            <table class=\"";
        // line 53
        echo twig_escape_filter($this->env, ($context["class"] ?? null), "html", null, true);
        echo " w-full\" cellspacing=0>-->
        <thead>
            ";
        // line 55
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["headers"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["headerRow"]) {
            // line 56
            echo "            <tr>
                ";
            // line 57
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["columns"] ?? null));
            foreach ($context['_seq'] as $context["columnIndex"] => $context["column"]) {
                echo " ";
                $context["th"] = (($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a = $context["headerRow"]) && is_array($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a) || $__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a instanceof ArrayAccess ? ($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a[$context["columnIndex"]] ?? null) : null);
                echo " ";
                if (($context["th"] ?? null)) {
                    // line 58
                    echo "                <th ";
                    echo twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getAttributeString", array());
                    echo ">
                    ";
                    // line 59
                    echo twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getOutput", array());
                    echo " ";
                    if (twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getData", array(0 => "description"), "method")) {
                        echo " ";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getData", array(0 => "description"), "method"), "html", null, true);
                        echo "
                    ";
                    }
                    // line 61
                    echo "                </th>
                ";
                }
                // line 62
                echo " ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['columnIndex'], $context['column'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 63
            echo "            </tr>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['headerRow'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 65
        echo "
        </thead>
        <tbody>
            ";
        // line 68
        if (( !($context["rows"] ?? null) && ($context["isFiltered"] ?? null))) {
            // line 69
            echo "            <tr class=\"h-48 bg-gray shadow-inner\">
                <td class=\"p-0\" colspan=\"";
            // line 70
            echo twig_escape_filter($this->env, twig_length_filter($this->env, ($context["columns"] ?? null)), "html", null, true);
            echo "\">
                    ";
            // line 71
            $this->displayBlock("blankslate", $context, $blocks);
            echo "
                </td>
            </tr>
            ";
        }
        // line 74
        echo " ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["rows"] ?? null));
        foreach ($context['_seq'] as $context["rowIndex"] => $context["rowData"]) {
            echo " ";
            $context["row"] = twig_get_attribute($this->env, $this->source, $context["rowData"], "row", array());
            // line 75
            echo "
            <tr ";
            // line 76
            echo twig_get_attribute($this->env, $this->source, ($context["row"] ?? null), "getAttributeString", array());
            echo ">
                ";
            // line 77
            echo twig_get_attribute($this->env, $this->source, ($context["row"] ?? null), "getPrepended", array());
            echo " ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["columns"] ?? null));
            foreach ($context['_seq'] as $context["columnIndex"] => $context["column"]) {
                // line 78
                echo "                ";
                $context["cell"] = (($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 = twig_get_attribute($this->env, $this->source, $context["rowData"], "cells", array())) && is_array($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57) || $__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 instanceof ArrayAccess ? ($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57[$context["columnIndex"]] ?? null) : null);
                // line 79
                echo "
                <td ";
                // line 80
                echo twig_get_attribute($this->env, $this->source, ($context["cell"] ?? null), "getAttributeString", array());
                echo ">
                    ";
                // line 81
                echo twig_get_attribute($this->env, $this->source, ($context["cell"] ?? null), "getPrepended", array());
                echo " ";
                if ((twig_get_attribute($this->env, $this->source, $context["column"], "getID", array()) == "actions")) {
                    // line 82
                    echo "                    <nav class=\"relative group\">
                        ";
                    // line 83
                    twig_get_attribute($this->env, $this->source, $context["column"], "getOutput", array(0 => twig_get_attribute($this->env, $this->source, $context["rowData"], "data", array())), "method");
                    echo " ";
                    $context["actions"] = twig_get_attribute($this->env, $this->source, $context["column"], "getActions", array());
                    // line 84
                    echo "
                        <div
                            class=\"";
                    // line 86
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["column"], "getClass", array(), "method"), "html", null, true);
                    echo " ";
                    echo (((twig_length_filter($this->env, ($context["actions"] ?? null)) == 1)) ? ("flex -m-2 sm:m-0") : ("hidden-1 group-hover:flex sm:flex absolute sm:static top-0 right-0 -mr-1 rounded  sm:shadow-none sm:bg-transparent px-1 -mt-3 sm:m-0 sm:p-0 z-10"));
                    echo "\">
                            ";
                    // line 87
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable(($context["actions"] ?? null));
                    foreach ($context['_seq'] as $context["actionName"] => $context["action"]) {
                        echo " ";
                        twig_get_attribute($this->env, $this->source, $context["action"], "addClass", array(0 => ""), "method");
                        // line 88
                        echo "                            ";
                        echo twig_get_attribute($this->env, $this->source, $context["action"], "getOutput", array(0 => twig_get_attribute($this->env, $this->source, $context["rowData"], "data", array()), 1 => twig_get_attribute($this->env, $this->source, $context["column"], "getParams", array())), "method");
                        echo " ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['actionName'], $context['action'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 89
                    echo "                        </div>

                        ";
                    // line 91
                    if ((twig_length_filter($this->env, ($context["actions"] ?? null)) > 1)) {
                        // line 92
                        echo "                        <!--
<button class=\"block sm:hidden rounded mx-auto my-1 px-1 py-2 bg-gray text-2xl text-gray font-sans font-bold leading-none\" onClick=\"event.preventDefault();\" onTouchEnd=\"event.preventDefault();\">
                            <span class=\"block -mt-3\">...</span>
                        </button> 
                        -->

                        ";
                    }
                    // line 99
                    echo "                    </nav>

                    ";
                } else {
                    // line 101
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
            // line 103
            echo " ";
            echo twig_get_attribute($this->env, $this->source, ($context["row"] ?? null), "getAppended", array());
            echo "
            </tr>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['rowIndex'], $context['rowData'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 106
        echo "        </tbody>

    </table>
</div>




";
    }

    // line 117
    public function block_footer($context, array $blocks = array())
    {
        echo " ";
    }

    public function getTemplateName()
    {
        return "components/dataTable.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  411 => 117,  399 => 106,  389 => 103,  375 => 101,  370 => 99,  361 => 92,  359 => 91,  355 => 89,  347 => 88,  341 => 87,  335 => 86,  331 => 84,  327 => 83,  324 => 82,  320 => 81,  316 => 80,  313 => 79,  310 => 78,  304 => 77,  300 => 76,  297 => 75,  290 => 74,  283 => 71,  279 => 70,  276 => 69,  274 => 68,  269 => 65,  262 => 63,  256 => 62,  252 => 61,  243 => 59,  238 => 58,  230 => 57,  227 => 56,  223 => 55,  218 => 53,  211 => 48,  208 => 47,  203 => 20,  189 => 19,  186 => 18,  183 => 17,  179 => 12,  175 => 11,  164 => 10,  161 => 9,  156 => 8,  150 => 118,  148 => 117,  144 => 115,  141 => 114,  138 => 47,  126 => 38,  119 => 36,  113 => 35,  109 => 34,  100 => 32,  95 => 31,  87 => 30,  84 => 29,  80 => 28,  75 => 26,  69 => 22,  67 => 17,  64 => 16,  62 => 15,  58 => 13,  56 => 8,  52 => 6,  45 => 5,  39 => 4,  36 => 3,  30 => 2,  28 => 1,);
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
<div class=\"table-responsive dataTables_wrapper my-2\">
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

        </tbody>

    </table>

</div>
{% else %} {% block tableInner %}


<div class=\"table-responsive dataTables_wrapper my-2\">
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

{% endblock table %}", "components/dataTable.twig.html", "F:\\suhail\\Office\\xampp\\htdocs\\newcode\\pupilsight_new\\resources\\templates\\components\\dataTable.twig.html");
    }
}
