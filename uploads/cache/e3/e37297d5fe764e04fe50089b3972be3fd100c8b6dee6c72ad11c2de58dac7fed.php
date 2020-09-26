<?php

/* components/dataTable.twig.html */
class __TwigTemplate_2ee01db9b68785b7252dc6c038d7c3a9f05330e86b3b2017b231e5e83d08a1e7 extends Twig_Template
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
<div class=\"table-responsive dataTables_wrapper \">
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
                            echo " ";
                        }
                        // line 33
                        echo "                </th>
                ";
                    }
                    // line 34
                    echo " ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['columnIndex'], $context['column'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 35
                echo "            </tr>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['headerRow'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 37
            echo "
        </thead>
        <tbody>
           
    </tbody>

</table>
</div>
";
        } else {
            // line 45
            echo " ";
            $this->displayBlock('tableInner', $context, $blocks);
            // line 106
            echo " ";
        }
        // line 107
        echo "
<footer>
    ";
        // line 109
        $this->displayBlock('footer', $context, $blocks);
        // line 110
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

    // line 45
    public function block_tableInner($context, array $blocks = array())
    {
        // line 46
        echo "<div class=\"table-responsive dataTables_wrapper\">
    <table class=\"table\" id=\"expore_tbl\">
        <!--<div class=\"overflow-x-auto overflow-y-visible\">
            <table class=\"";
        // line 49
        echo twig_escape_filter($this->env, ($context["class"] ?? null), "html", null, true);
        echo " w-full\" cellspacing=0>-->
        <thead>
            ";
        // line 51
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["headers"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["headerRow"]) {
            // line 52
            echo "            <tr>
                ";
            // line 53
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["columns"] ?? null));
            foreach ($context['_seq'] as $context["columnIndex"] => $context["column"]) {
                echo " ";
                $context["th"] = (($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a = $context["headerRow"]) && is_array($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a) || $__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a instanceof ArrayAccess ? ($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a[$context["columnIndex"]] ?? null) : null);
                echo " ";
                if (($context["th"] ?? null)) {
                    // line 54
                    echo "                <th ";
                    echo twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getAttributeString", array());
                    echo ">
                    ";
                    // line 55
                    echo twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getOutput", array());
                    echo " ";
                    if (twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getData", array(0 => "description"), "method")) {
                        echo " ";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["th"] ?? null), "getData", array(0 => "description"), "method"), "html", null, true);
                        echo " ";
                    }
                    // line 56
                    echo "                </th>
                ";
                }
                // line 57
                echo " ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['columnIndex'], $context['column'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 58
            echo "            </tr>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['headerRow'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 60
        echo "
        </thead>
        <tbody>
            ";
        // line 63
        if (( !($context["rows"] ?? null) && ($context["isFiltered"] ?? null))) {
            // line 64
            echo "            <tr class=\"h-48 bg-gray shadow-inner\">
                <td class=\"p-0\" colspan=\"";
            // line 65
            echo twig_escape_filter($this->env, twig_length_filter($this->env, ($context["columns"] ?? null)), "html", null, true);
            echo "\">
                    ";
            // line 66
            $this->displayBlock("blankslate", $context, $blocks);
            echo "
                </td>
            </tr>
            ";
        }
        // line 69
        echo " ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["rows"] ?? null));
        foreach ($context['_seq'] as $context["rowIndex"] => $context["rowData"]) {
            echo " ";
            $context["row"] = twig_get_attribute($this->env, $this->source, $context["rowData"], "row", array());
            // line 70
            echo "
            <tr ";
            // line 71
            echo twig_get_attribute($this->env, $this->source, ($context["row"] ?? null), "getAttributeString", array());
            echo ">
                ";
            // line 72
            echo twig_get_attribute($this->env, $this->source, ($context["row"] ?? null), "getPrepended", array());
            echo " ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["columns"] ?? null));
            foreach ($context['_seq'] as $context["columnIndex"] => $context["column"]) {
                echo " ";
                $context["cell"] = (($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 = twig_get_attribute($this->env, $this->source, $context["rowData"], "cells", array())) && is_array($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57) || $__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 instanceof ArrayAccess ? ($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57[$context["columnIndex"]] ?? null) : null);
                // line 73
                echo "
                <td ";
                // line 74
                echo twig_get_attribute($this->env, $this->source, ($context["cell"] ?? null), "getAttributeString", array());
                echo ">
                    ";
                // line 75
                echo twig_get_attribute($this->env, $this->source, ($context["cell"] ?? null), "getPrepended", array());
                echo " ";
                if ((twig_get_attribute($this->env, $this->source, $context["column"], "getID", array()) == "actions")) {
                    // line 76
                    echo "                    <nav class=\"relative group\">
                        ";
                    // line 77
                    twig_get_attribute($this->env, $this->source, $context["column"], "getOutput", array(0 => twig_get_attribute($this->env, $this->source, $context["rowData"], "data", array())), "method");
                    echo " ";
                    $context["actions"] = twig_get_attribute($this->env, $this->source, $context["column"], "getActions", array());
                    // line 78
                    echo "
                        <div class=\"";
                    // line 79
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["column"], "getClass", array(), "method"), "html", null, true);
                    echo " ";
                    echo (((twig_length_filter($this->env, ($context["actions"] ?? null)) == 1)) ? ("flex -m-2 sm:m-0") : ("hidden-1 group-hover:flex sm:flex absolute sm:static top-0 right-0 -mr-1 rounded  sm:shadow-none sm:bg-transparent px-1 -mt-3 sm:m-0 sm:p-0 z-10"));
                    echo "\">
                            ";
                    // line 80
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable(($context["actions"] ?? null));
                    foreach ($context['_seq'] as $context["actionName"] => $context["action"]) {
                        echo " ";
                        twig_get_attribute($this->env, $this->source, $context["action"], "addClass", array(0 => ""), "method");
                        echo " ";
                        echo twig_get_attribute($this->env, $this->source, $context["action"], "getOutput", array(0 => twig_get_attribute($this->env, $this->source, $context["rowData"], "data", array()), 1 => twig_get_attribute($this->env, $this->source, $context["column"], "getParams", array())), "method");
                        echo " ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['actionName'], $context['action'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 81
                    echo "                        </div>

                        ";
                    // line 83
                    if ((twig_length_filter($this->env, ($context["actions"] ?? null)) > 1)) {
                        // line 84
                        echo "                        <!--
<button class=\"block sm:hidden rounded mx-auto my-1 px-1 py-2 bg-gray text-2xl text-gray font-sans font-bold leading-none\" onClick=\"event.preventDefault();\" onTouchEnd=\"event.preventDefault();\">
                            <span class=\"block -mt-3\">...</span>
                        </button> 
                        -->
                        
                        ";
                    }
                    // line 91
                    echo "                    </nav>

                    ";
                } else {
                    // line 93
                    echo " ";
                    echo twig_get_attribute($this->env, $this->source, $context["column"], "getOutput", array(0 => twig_get_attribute($this->env, $this->source, $context["rowData"], "data", array())), "method");
                    echo " ";
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
            // line 95
            echo " ";
            echo twig_get_attribute($this->env, $this->source, ($context["row"] ?? null), "getAppended", array());
            echo "
            </tr>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['rowIndex'], $context['rowData'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 98
        echo "        </tbody>

    </table>
</div>




";
    }

    // line 109
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
        return array (  403 => 109,  391 => 98,  381 => 95,  367 => 93,  362 => 91,  353 => 84,  351 => 83,  347 => 81,  334 => 80,  328 => 79,  325 => 78,  321 => 77,  318 => 76,  314 => 75,  310 => 74,  307 => 73,  299 => 72,  295 => 71,  292 => 70,  285 => 69,  278 => 66,  274 => 65,  271 => 64,  269 => 63,  264 => 60,  257 => 58,  251 => 57,  247 => 56,  239 => 55,  234 => 54,  226 => 53,  223 => 52,  219 => 51,  214 => 49,  209 => 46,  206 => 45,  201 => 20,  187 => 19,  184 => 18,  181 => 17,  177 => 12,  173 => 11,  162 => 10,  159 => 9,  154 => 8,  148 => 110,  146 => 109,  142 => 107,  139 => 106,  136 => 45,  125 => 37,  118 => 35,  112 => 34,  108 => 33,  100 => 32,  95 => 31,  87 => 30,  84 => 29,  80 => 28,  75 => 26,  69 => 22,  67 => 17,  64 => 16,  62 => 15,  58 => 13,  56 => 8,  52 => 6,  45 => 5,  39 => 4,  36 => 3,  30 => 2,  28 => 1,);
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
    <table class=\"table\" id=\"expore_tbl\">
        <!--<div class=\"overflow-x-auto overflow-y-visible\">
            <table class=\"{{ class }} w-full\" cellspacing=0>-->
        <thead>
            {% for headerRow in headers %}
            <tr>
                {% for columnIndex, column in columns %} {% set th = headerRow[columnIndex] %} {% if th %}
                <th {{ th.getAttributeString|raw }}>
                    {{ th.getOutput|raw }} {% if th.getData('description') %} {{ th.getData('description') }} {% endif %}
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
<div class=\"table-responsive dataTables_wrapper\">
    <table class=\"table\" id=\"expore_tbl\">
        <!--<div class=\"overflow-x-auto overflow-y-visible\">
            <table class=\"{{ class }} w-full\" cellspacing=0>-->
        <thead>
            {% for headerRow in headers %}
            <tr>
                {% for columnIndex, column in columns %} {% set th = headerRow[columnIndex] %} {% if th %}
                <th {{ th.getAttributeString|raw }}>
                    {{ th.getOutput|raw }} {% if th.getData('description') %} {{ th.getData('description') }} {% endif %}
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
                {{ row.getPrepended|raw }} {% for columnIndex, column in columns %} {% set cell = rowData.cells[columnIndex] %}

                <td {{ cell.getAttributeString|raw }}>
                    {{ cell.getPrepended|raw }} {% if column.getID == \"actions\" %}
                    <nav class=\"relative group\">
                        {% do column.getOutput(rowData.data) %} {% set actions = column.getActions %}

                        <div class=\"{{ column.getClass() }} {{ actions|length == 1 ? 'flex -m-2 sm:m-0' : 'hidden-1 group-hover:flex sm:flex absolute sm:static top-0 right-0 -mr-1 rounded  sm:shadow-none sm:bg-transparent px-1 -mt-3 sm:m-0 sm:p-0 z-10' }}\">
                            {% for actionName, action in actions %} {% do action.addClass('') %} {{ action.getOutput(rowData.data, column.getParams)|raw }} {% endfor %}
                        </div>

                        {% if actions|length > 1 %}
                        <!--
<button class=\"block sm:hidden rounded mx-auto my-1 px-1 py-2 bg-gray text-2xl text-gray font-sans font-bold leading-none\" onClick=\"event.preventDefault();\" onTouchEnd=\"event.preventDefault();\">
                            <span class=\"block -mt-3\">...</span>
                        </button> 
                        -->
                        
                        {% endif %}
                    </nav>

                    {% else %} {{ column.getOutput(rowData.data)|raw }} {% endif %} {{ cell.getAppended|raw }}
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

{% endblock table %}", "components/dataTable.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\components\\dataTable.twig.html");
    }
}
