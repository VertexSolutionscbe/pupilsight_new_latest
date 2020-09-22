<?php

/* components/gridTable.twig.html */
class __TwigTemplate_5392f85928e51f08b47a6fd212a83ecf94b12bd63b0ffd9df9afbb0110325d40 extends Twig_Template
{
    private $source;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        // line 8
        $this->parent = $this->loadTemplate("components/dataTable.twig.html", "components/gridTable.twig.html", 8);
        $this->blocks = array(
            'table' => array($this, 'block_table'),
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
        echo " ";
        $this->displayBlock("header", $context, $blocks);
        echo " ";
        if ((twig_get_attribute($this->env, $this->source, ($context["dataSet"] ?? null), "getResultCount", array()) == 0)) {
            // line 9
            echo "<div class=\"h-24 \">
    ";
            // line 10
            $this->displayBlock("blankslate", $context, $blocks);
            echo "
</div>
";
        } else {
            // line 13
            echo "<div class=\"flex flex-wrap ";
            echo twig_escape_filter($this->env, ((twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getMetaData", array(0 => "gridClass"), "method", true, true)) ? (_twig_default_filter(twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getMetaData", array(0 => "gridClass"), "method"), "py-2")) : ("py-2")), "html", null, true);
            echo "\">
    <div class=\"w-full\">
        ";
            // line 15
            echo ($context["gridHeader"] ?? null);
            echo "
    </div>

    ";
            // line 18
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["dataSet"] ?? null));
            foreach ($context['_seq'] as $context["rowIndex"] => $context["rowData"]) {
                // line 19
                echo "
    <div class=\"flex-col ";
                // line 20
                echo twig_escape_filter($this->env, ((twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getMetaData", array(0 => "gridItemClass"), "method", true, true)) ? (_twig_default_filter(twig_get_attribute($this->env, $this->source, ($context["table"] ?? null), "getMetaData", array(0 => "gridItemClass"), "method"), "w-1/2 sm:w-1/3 text-center")) : ("w-1/2 sm:w-1/3 text-center")), "html", null, true);
                echo "\">
        ";
                // line 21
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["columns"] ?? null));
                foreach ($context['_seq'] as $context["columnIndex"] => $context["column"]) {
                    // line 22
                    echo "
        <div class=\"";
                    // line 23
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["column"], "getClass", array()), "html", null, true);
                    echo "\">
            ";
                    // line 24
                    echo twig_get_attribute($this->env, $this->source, $context["column"], "getOutput", array(0 => $context["rowData"]), "method");
                    echo "
        </div>

        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['columnIndex'], $context['column'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 28
                echo "    </div>

    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['rowIndex'], $context['rowData'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 31
            echo "
    <div class=\"w-full\">
        ";
            // line 33
            echo ($context["gridFooter"] ?? null);
            echo "
    </div>
</div>
";
        }
        // line 36
        echo " ";
        $this->displayBlock("footer", $context, $blocks);
        echo " ";
    }

    public function getTemplateName()
    {
        return "components/gridTable.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  110 => 36,  103 => 33,  99 => 31,  91 => 28,  81 => 24,  77 => 23,  74 => 22,  70 => 21,  66 => 20,  63 => 19,  59 => 18,  53 => 15,  47 => 13,  41 => 10,  38 => 9,  15 => 8,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#
<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/
-->#} {% extends \"components/dataTable.twig.html\" %} {% block table %} {{ block(\"header\") }} {% if dataSet.getResultCount == 0 %}
<div class=\"h-24 \">
    {{ block('blankslate') }}
</div>
{% else %}
<div class=\"flex flex-wrap {{ table.getMetaData('gridClass')|default('py-2') }}\">
    <div class=\"w-full\">
        {{ gridHeader|raw }}
    </div>

    {% for rowIndex, rowData in dataSet %}

    <div class=\"flex-col {{ table.getMetaData('gridItemClass')|default('w-1/2 sm:w-1/3 text-center') }}\">
        {% for columnIndex, column in columns %}

        <div class=\"{{ column.getClass }}\">
            {{ column.getOutput(rowData)|raw }}
        </div>

        {% endfor %}
    </div>

    {% endfor %}

    <div class=\"w-full\">
        {{ gridFooter|raw }}
    </div>
</div>
{% endif %} {{ block(\"footer\") }} {% endblock table %}", "components/gridTable.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\components\\gridTable.twig.html");
    }
}
