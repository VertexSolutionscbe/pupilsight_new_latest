<?php

/* components/paginatedTable.twig.html */
class __TwigTemplate_36c94239a672a0761b4c21b3a2e34f6d5ba02099663bdd41fc153fd89fa642a4 extends Twig_Template
{
    private $source;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        // line 8
        $this->parent = $this->loadTemplate("components/dataTable.twig.html", "components/paginatedTable.twig.html", 8);
        $this->blocks = array(
            'table' => array($this, 'block_table'),
            'header' => array($this, 'block_header'),
            'footer' => array($this, 'block_footer'),
            'filters' => array($this, 'block_filters'),
            'pageCount' => array($this, 'block_pageCount'),
            'pagination' => array($this, 'block_pagination'),
            'bulkActions' => array($this, 'block_bulkActions'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "components/dataTable.twig.html";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    public function block_table($context, array $blocks = array())
    {
        // line 9
        echo "<div id=\"";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getID", array()), "html", null, true);
        echo "\" style=\"width:100%\">
    <div class=\"dataTable\" data-results=\"";
        // line 10
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getResultCount", array()), "html", null, true);
        echo "\">
        ";
        // line 11
        $this->displayParentBlock("table", $context, $blocks);
        echo "
    </div>
</div>

";
        // line 15
        $this->displayBlock("bulkActions", $context, $blocks);
        echo "

<script>
     \$(function(){
        \$('#";
        // line 19
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getID", array()), "html", null, true);
        echo "').pupilsightDataTable( '";
        echo ($context["path"] ?? null);
        echo "', ";
        echo ($context["jsonData"] ?? null);
        echo ", '";
        echo twig_escape_filter($this->env, ($context["identifier"] ?? null), "html", null, true);
        echo "');
    });
</script>
";
    }

    // line 22
    public function block_header($context, array $blocks = array())
    {
        // line 23
        echo "<div class=\"flex items-end justify-between pb-2\">
    <div class=\"\">
        ";
        // line 25
        $this->displayBlock("pageCount", $context, $blocks);
        echo "
    </div>

    ";
        // line 28
        $this->displayParentBlock("header", $context, $blocks);
        echo "
</div>

";
        // line 31
        if ((twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getTotalCount", array()) > 0)) {
            // line 32
            echo "<div class=\"flex flex-wrap sm:flex-no-wrap items-stretch justify-between\">
    <div class=\"flex items-stretch h-full\">
        ";
            // line 34
            if (($context["pageSize"] ?? null)) {
                // line 35
                echo "        <div class=\"relative mr-1\">
            <div class=\"absolute caret z-10 mt-3 right-0 mr-5 pointer-events-none\"></div>
            ";
                // line 37
                echo ($context["pageSize"] ?? null);
                echo "
        </div>
        ";
            }
            // line 39
            echo " ";
            if (($context["filterOptions"] ?? null)) {
                // line 40
                echo "        <div class=\"relative\">
            <div class=\"absolute caret z-10 mt-3 right-0 mr-5 pointer-events-none\"></div>
            ";
                // line 42
                echo ($context["filterOptions"] ?? null);
                echo "
        </div>
        ";
            }
            // line 44
            echo " ";
            if ((($context["filterCriteria"] ?? null) && ($context["filterOptions"] ?? null))) {
                // line 45
                echo "        <nav class=\"flex cursor-default\" style='height: 36px;line-height: 20px;margin-top: 16px;'>
            ";
                // line 46
                $this->displayBlock("filters", $context, $blocks);
                echo "
        </nav>
        ";
            }
            // line 49
            echo "    </div>

    ";
            // line 51
            $this->displayBlock("pagination", $context, $blocks);
            echo "
</div>
";
        }
        // line 53
        echo " ";
    }

    public function block_footer($context, array $blocks = array())
    {
        // line 54
        echo "<!-- ";
        if ((twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getResultCount", array()) > twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getPageSize", array()))) {
            // line 55
            echo "    <div class=\"flex flex-col sm:flex-row sm:items-end justify-end mt-2\">
        ";
            // line 56
            $this->displayBlock("pagination", $context, $blocks);
            echo "
    </div>
    ";
        }
        // line 58
        echo " -->
";
    }

    // line 59
    public function block_filters($context, array $blocks = array())
    {
        echo " ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["filterCriteria"] ?? null));
        foreach ($context['_seq'] as $context["name"] => $context["label"]) {
            // line 60
            echo "<a href=\"javascript:void()\" class=\"filter -mx-px py-2 px-3 border border-blue bg-blue hover:bg-blue z-10 text-white font-bold\" data-filter=\"";
            echo twig_escape_filter($this->env, $context["name"], "html", null, true);
            echo "\">
            ";
            // line 61
            echo $context["label"];
            echo "
        </a> ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['name'], $context['label'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 63
        echo "
<a href=\"javascript:void()\" class=\"filter p-2 rounded-r border border-gray text-white bg-gray font-bold hover:bg-gray clear\">
        ";
        // line 65
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Clear")), "html", null, true);
        echo "
    </a> ";
    }

    // line 66
    public function block_pageCount($context, array $blocks = array())
    {
        echo " ";
        if ((twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getTotalCount", array()) > 0)) {
            // line 67
            echo "<!-- <div class=\"text-xs\">
        ";
            // line 68
            echo twig_escape_filter($this->env, ((($context["searchText"] ?? null)) ? ((call_user_func_array($this->env->getFunction('__')->getCallable(), array("Search")) . " ")) : ("")), "html", null, true);
            echo "

        ";
            // line 70
            echo twig_escape_filter($this->env, ((twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "isSubset", array())) ? (call_user_func_array($this->env->getFunction('__')->getCallable(), array("Results"))) : (call_user_func_array($this->env->getFunction('__')->getCallable(), array("Records")))), "html", null, true);
            echo "

        ";
            // line 72
            if ((twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "count", array()) > 0)) {
                // line 73
                echo "            ";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getPageFrom", array()), "html", null, true);
                echo "-";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getPageTo", array()), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("of")), "html", null, true);
                echo "
        ";
            }
            // line 74
            echo " 
        
        ";
            // line 76
            echo twig_escape_filter($this->env, twig_number_format_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getResultCount", array())), "html", null, true);
            echo "
    </div> -->
";
        }
        // line 78
        echo " ";
    }

    public function block_pagination($context, array $blocks = array())
    {
        echo " ";
        $context["buttonStyle"] = "border -ml-px px-2 py-1 font-bold leading-loose";
        echo " ";
        if (((twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getResultCount", array()) > twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getPageSize", array())) || ($context["filterOptions"] ?? null))) {
            // line 79
            echo "<div class=\"pagination mb-2\">
    <a href=\"javascript:void()\" class=\"ml-1 padipag2  prv paginate rounded-l text-white bg-gray border-gray ";
            // line 80
            echo (( !twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "isFirstPage", array())) ? ("hover:bg-gray") : (""));
            echo " ";
            echo twig_escape_filter($this->env, ($context["buttonStyle"] ?? null), "html", null, true);
            echo "\" data-page=\"";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getPrevPageNumber", array()), "html", null, true);
            echo "\" ";
            echo ((twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "isFirstPage", array())) ? ("disabled") : (""));
            // line 81
            echo ">
            ";
            // line 82
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Prev")), "html", null, true);
            echo "
        </a>";
            // line 83
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getPaginatedRange", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["page"]) {
                if (($context["page"] == "...")) {
                    // line 84
                    echo "<a href=\"javascript:void()\" class=\"ml-1 padipag2  prv ";
                    echo twig_escape_filter($this->env, ($context["buttonStyle"] ?? null), "html", null, true);
                    echo "\" disabled>...</a>";
                } else {
                    // line 85
                    echo "<a href=\"javascript:void()\" class=\"ml-1 padipag2  prv paginate ";
                    echo twig_escape_filter($this->env, ($context["buttonStyle"] ?? null), "html", null, true);
                    echo " ";
                    echo ((($context["page"] == twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getPage", array()))) ? ("bg-blue border-blue text-white relative z-10") : ("text-gray hover:bg-gray border-gray"));
                    echo "\" data-page=\"";
                    echo twig_escape_filter($this->env, $context["page"], "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $context["page"], "html", null, true);
                    echo "</a>";
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['page'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 87
            echo "<a href=\"javascript:void()\" class=\"ml-1 padipag2  prv paginate rounded-r text-gray border-gray ";
            echo (( !twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "isLastPage", array())) ? ("hover:bg-gray") : (""));
            echo " ";
            echo twig_escape_filter($this->env, ($context["buttonStyle"] ?? null), "html", null, true);
            echo "\" data-page=\"";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getNextPageNumber", array()), "html", null, true);
            echo "\" ";
            echo ((twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "isLastPage", array())) ? ("disabled") : (""));
            echo ">
        ";
            // line 88
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Next")), "html", null, true);
            echo "
        </a>
</div>
";
        }
        // line 91
        echo " ";
    }

    public function block_bulkActions($context, array $blocks = array())
    {
        echo " ";
        if (($context["bulkActions"] ?? null)) {
            // line 92
            echo "<div class=\"bulkActionPanel hidden absolute top-0 right-0 w-full flex items-center justify-between p-1 pt-2 bg-purple rounded-t z-20\">
    <div class=\"bulkActionCount flex-grow text-white text-sm text-right pr-3\">
        <span>0</span> ";
            // line 94
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Selected")), "html", null, true);
            echo "
    </div>

    ";
            // line 97
            echo twig_get_attribute($this->env, $this->source, ($context["bulkActions"] ?? null), "getOutput", array());
            echo "

    <script>
        {
            {
                bulkActions.getValidationOutput | raw
            }
        }
    </script>
</div>
<div class='float-none'></div>
";
        }
        // line 108
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
        return array (  346 => 108,  331 => 97,  325 => 94,  321 => 92,  313 => 91,  306 => 88,  295 => 87,  280 => 85,  275 => 84,  270 => 83,  266 => 82,  263 => 81,  255 => 80,  252 => 79,  242 => 78,  236 => 76,  232 => 74,  222 => 73,  220 => 72,  215 => 70,  210 => 68,  207 => 67,  202 => 66,  196 => 65,  192 => 63,  184 => 61,  179 => 60,  172 => 59,  167 => 58,  161 => 56,  158 => 55,  155 => 54,  149 => 53,  143 => 51,  139 => 49,  133 => 46,  130 => 45,  127 => 44,  121 => 42,  117 => 40,  114 => 39,  108 => 37,  104 => 35,  102 => 34,  98 => 32,  96 => 31,  90 => 28,  84 => 25,  80 => 23,  77 => 22,  63 => 19,  56 => 15,  49 => 11,  45 => 10,  40 => 9,  15 => 8,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#
<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/
-->#} {% extends \"components/dataTable.twig.html\" %} {% block table %}
<div id=\"{{ table.getID }}\" style=\"width:100%\">
    <div class=\"dataTable\" data-results=\"{{ dataSet.getResultCount }}\">
        {{ parent() }}
    </div>
</div>

{{ block('bulkActions') }}

<script>
     \$(function(){
        \$('#{{ table.getID }}').pupilsightDataTable( '{{ path|raw }}', {{ jsonData|raw }}, '{{ identifier }}');
    });
</script>
{% endblock table %} {% block header %}
<div class=\"flex items-end justify-between pb-2\">
    <div class=\"\">
        {{ block('pageCount') }}
    </div>

    {{ parent() }}
</div>

{% if dataSet.getTotalCount > 0 %}
<div class=\"flex flex-wrap sm:flex-no-wrap items-stretch justify-between\">
    <div class=\"flex items-stretch h-full\">
        {% if pageSize %}
        <div class=\"relative mr-1\">
            <div class=\"absolute caret z-10 mt-3 right-0 mr-5 pointer-events-none\"></div>
            {{ pageSize|raw }}
        </div>
        {% endif %} {% if filterOptions %}
        <div class=\"relative\">
            <div class=\"absolute caret z-10 mt-3 right-0 mr-5 pointer-events-none\"></div>
            {{ filterOptions|raw }}
        </div>
        {% endif %} {% if filterCriteria and filterOptions %}
        <nav class=\"flex cursor-default\" style='height: 36px;line-height: 20px;margin-top: 16px;'>
            {{ block('filters') }}
        </nav>
        {% endif %}
    </div>

    {{ block('pagination') }}
</div>
{% endif %} {% endblock header %} {% block footer %}
<!-- {% if dataSet.getResultCount > dataSet.getPageSize %}
    <div class=\"flex flex-col sm:flex-row sm:items-end justify-end mt-2\">
        {{ block('pagination') }}
    </div>
    {% endif %} -->
{% endblock footer %} {% block filters %} {% for name, label in filterCriteria %}
<a href=\"javascript:void()\" class=\"filter -mx-px py-2 px-3 border border-blue bg-blue hover:bg-blue z-10 text-white font-bold\" data-filter=\"{{ name }}\">
            {{ label|raw }}
        </a> {% endfor %}

<a href=\"javascript:void()\" class=\"filter p-2 rounded-r border border-gray text-white bg-gray font-bold hover:bg-gray clear\">
        {{ __('Clear') }}
    </a> {% endblock filters %} {% block pageCount %} {% if dataSet.getTotalCount > 0 %}
<!-- <div class=\"text-xs\">
        {{ searchText ? __('Search') ~ \" \" }}

        {{ dataSet.isSubset ? __('Results') : __('Records') }}

        {% if dataSet.count > 0 %}
            {{ dataSet.getPageFrom }}-{{ dataSet.getPageTo }} {{ __('of') }}
        {% endif %} 
        
        {{ dataSet.getResultCount|number_format }}
    </div> -->
{% endif %} {% endblock pageCount %} {% block pagination %} {% set buttonStyle = 'border -ml-px px-2 py-1 font-bold leading-loose' %} {% if dataSet.getResultCount > dataSet.getPageSize or filterOptions %}
<div class=\"pagination mb-2\">
    <a href=\"javascript:void()\" class=\"ml-1 padipag2  prv paginate rounded-l text-white bg-gray border-gray {{ not dataSet.isFirstPage ? 'hover:bg-gray'}} {{ buttonStyle }}\" data-page=\"{{ dataSet.getPrevPageNumber }}\" {{ dataSet.isFirstPage ?
        'disabled'}}>
            {{ __('Prev') }}
        </a> {%- for page in dataSet.getPaginatedRange -%} {%- if page == '...' -%}
    <a href=\"javascript:void()\" class=\"ml-1 padipag2  prv {{ buttonStyle }}\" disabled>...</a> {%- else -%}
    <a href=\"javascript:void()\" class=\"ml-1 padipag2  prv paginate {{ buttonStyle }} {{ page == dataSet.getPage ? 'bg-blue border-blue text-white relative z-10' : 'text-gray hover:bg-gray border-gray' }}\" data-page=\"{{ page }}\">{{ page }}</a>    {%- endif -%} {%- endfor -%}

    <a href=\"javascript:void()\" class=\"ml-1 padipag2  prv paginate rounded-r text-gray border-gray {{ not dataSet.isLastPage ? 'hover:bg-gray'}} {{ buttonStyle }}\" data-page=\"{{ dataSet.getNextPageNumber }}\" {{ dataSet.isLastPage ? 'disabled'}}>
        {{ __('Next') }}
        </a>
</div>
{% endif %} {% endblock pagination %} {% block bulkActions %} {% if bulkActions %}
<div class=\"bulkActionPanel hidden absolute top-0 right-0 w-full flex items-center justify-between p-1 pt-2 bg-purple rounded-t z-20\">
    <div class=\"bulkActionCount flex-grow text-white text-sm text-right pr-3\">
        <span>0</span> {{ __('Selected') }}
    </div>

    {{ bulkActions.getOutput|raw }}

    <script>
        {
            {
                bulkActions.getValidationOutput | raw
            }
        }
    </script>
</div>
<div class='float-none'></div>
{% endif %} {% endblock bulkActions %}", "components/paginatedTable.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\components\\paginatedTable.twig.html");
    }
}
