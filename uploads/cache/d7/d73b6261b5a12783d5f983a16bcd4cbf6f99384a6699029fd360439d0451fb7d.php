<?php

/* components/form.twig.html */
class __TwigTemplate_bac067d173805ab5b265b6070b4cc5f5d302648ab507cd35cdaec0ce8bbe8ac1 extends Twig_Template
{
    private $source;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 8
        echo "
<form ";
        // line 9
        echo twig_get_attribute($this->env, $this->source, ($context["form"] ?? null), "getAttributeString", array());
        echo " onsubmit=\"pupilsightFormSubmitted(this)\">

    ";
        // line 11
        if (twig_get_attribute($this->env, $this->source, ($context["form"] ?? null), "getTitle", array())) {
            // line 12
            echo "    <h2>";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["form"] ?? null), "getTitle", array()), "html", null, true);
            echo "</h2>
    ";
        }
        // line 14
        echo "
    ";
        // line 15
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["form"] ?? null), "getHiddenValues", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["values"]) {
            // line 16
            echo "    <input type=\"hidden\" name=\"";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["values"], "name", array()), "html", null, true);
            echo "\" value=\"";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["values"], "value", array()), "html", null, true);
            echo "\">
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['values'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 18
        echo "
    ";
        // line 19
        $context["flag"] = false;
        // line 20
        echo "

    ";
        // line 22
        $context["renderStyle"] = (((twig_in_filter("standardForm", twig_get_attribute($this->env, $this->source, ($context["form"] ?? null), "getClass", array())) || twig_in_filter("noIntBorder", twig_get_attribute($this->env, $this->source, ($context["form"] ?? null), "getClass", array())))) ? ("flex") : ("table"));
        // line 23
        echo "


    ";
        // line 26
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["form"] ?? null), "getRows", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["row"]) {
            // line 27
            echo "
    ";
            // line 28
            if ((($context["renderStyle"] ?? null) == "flex")) {
                // line 29
                echo "    ";
                $context["rowClass"] = "flex flex-col sm:flex-row justify-between content-center p-0";
                // line 30
                echo "    ";
            }
            // line 31
            echo "
    ";
            // line 32
            if ((is_string($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 = twig_get_attribute($this->env, $this->source, $context["row"], "getClass", array())) && is_string($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a = "break") && ('' === $__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a || 0 === strpos($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5, $__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a)))) {
                // line 33
                echo "
    ";
                // line 34
                if ((($context["flag"] ?? null) == true)) {
                    // line 35
                    echo "    </div>
    ";
                }
                // line 37
                echo "    <div class='row mb-1' id=\"tbody_";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "getID", array()), "html", null, true);
                echo "\">
        ";
                // line 38
                $context["flag"] = true;
                // line 39
                echo "        ";
                $context["flag"] = true;
                // line 40
                echo "        ";
            }
            // line 41
            echo "
        <div id=\"";
            // line 42
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "getID", array()), "html", null, true);
            echo "\" class=\"row mb-1 ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "getClass", array()), "html", null, true);
            echo "\">

            ";
            // line 44
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["row"], "getElements", array()));
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
            foreach ($context['_seq'] as $context["_key"] => $context["element"]) {
                // line 45
                echo "            ";
                $context["colspan"] = (((twig_get_attribute($this->env, $this->source, $context["loop"], "last", array()) && (twig_get_attribute($this->env, $this->source, $context["loop"], "length", array()) < ($context["totalColumns"] ?? null)))) ? (((($context["totalColumns"] ?? null) + 1) - twig_get_attribute($this->env, $this->source, $context["loop"], "length", array()))) : (0));
                // line 46
                echo "
            ";
                // line 47
                if ((($context["renderStyle"] ?? null) == "flex")) {
                    // line 48
                    echo "            ";
                    if (twig_get_attribute($this->env, $this->source, $context["element"], "isInstanceOf", array(0 => "Pupilsight\\Forms\\Layout\\Label"), "method")) {
                        // line 49
                        echo "            ";
                        $context["class"] = "flex flex-col flex-grow justify-center -mb-1 sm:mb-0 ";
                        // line 50
                        echo "            ";
                    } elseif (twig_get_attribute($this->env, $this->source, $context["element"], "isInstanceOf", array(0 => "Pupilsight\\Forms\\Layout\\Column"), "method")) {
                        // line 51
                        echo "            ";
                        $context["class"] = (((twig_get_attribute($this->env, $this->source, $context["loop"], "last", array()) && (twig_get_attribute($this->env, $this->source, $context["loop"], "length", array()) == 2))) ? ("w-full max-w-full sm:max-w-xs flex justify-end") : ("w-full "));
                        // line 52
                        echo "            ";
                    } elseif ((twig_get_attribute($this->env, $this->source, $context["loop"], "last", array()) && (twig_get_attribute($this->env, $this->source, $context["loop"], "length", array()) == 2))) {
                        // line 53
                        echo "            ";
                        $context["class"] = "w-full max-w-full sm:max-w-xs flex justify-end items-center";
                        // line 54
                        echo "            ";
                    } else {
                        // line 55
                        echo "            ";
                        $context["class"] = "flex-grow justify-center";
                        // line 56
                        echo "            ";
                    }
                    // line 57
                    echo "            ";
                } else {
                    // line 58
                    echo "            ";
                    $context["class"] = "";
                    // line 59
                    echo "            ";
                }
                // line 60
                echo "
            <div class=\"col-sm  ";
                // line 61
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["element"], "getClass", array()), "html", null, true);
                echo "\">
                <div>
                    ";
                // line 63
                echo twig_replace_filter(twig_get_attribute($this->env, $this->source, $context["element"], "getOutput", array()), array("standardWidth" => (((($context["renderStyle"] ?? null) == "flex")) ? ("w-full") : (""))));
                echo "
                </div>

                ";
                // line 66
                if (twig_get_attribute($this->env, $this->source, $context["element"], "instanceOf", array(0 => "Pupilsight\\Forms\\ValidatableInterface"), "method")) {
                    // line 67
                    echo "                <script type=\"text/javascript\">
                    ";
                    // line 68
                    echo twig_get_attribute($this->env, $this->source, $context["element"], "getValidationOutput", array());
                    echo "
                </script>
                ";
                }
                // line 71
                echo "            </div>
            ";
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
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['element'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 73
            echo "
        </div>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['row'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 76
        echo "
        <script type=\"text/javascript\">
            ";
        // line 78
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["javascript"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["code"]) {
            // line 79
            echo "            ";
            echo $context["code"];
            echo "
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['code'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 81
        echo "        </script>
</form>";
    }

    public function getTemplateName()
    {
        return "components/form.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  251 => 81,  242 => 79,  238 => 78,  234 => 76,  226 => 73,  211 => 71,  205 => 68,  202 => 67,  200 => 66,  194 => 63,  189 => 61,  186 => 60,  183 => 59,  180 => 58,  177 => 57,  174 => 56,  171 => 55,  168 => 54,  165 => 53,  162 => 52,  159 => 51,  156 => 50,  153 => 49,  150 => 48,  148 => 47,  145 => 46,  142 => 45,  125 => 44,  118 => 42,  115 => 41,  112 => 40,  109 => 39,  107 => 38,  102 => 37,  98 => 35,  96 => 34,  93 => 33,  91 => 32,  88 => 31,  85 => 30,  82 => 29,  80 => 28,  77 => 27,  73 => 26,  68 => 23,  66 => 22,  62 => 20,  60 => 19,  57 => 18,  46 => 16,  42 => 15,  39 => 14,  33 => 12,  31 => 11,  26 => 9,  23 => 8,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/
-->#}

<form {{ form.getAttributeString|raw }} onsubmit=\"pupilsightFormSubmitted(this)\">

    {% if form.getTitle %}
    <h2>{{ form.getTitle }}</h2>
    {% endif %}

    {% for values in form.getHiddenValues %}
    <input type=\"hidden\" name=\"{{ values.name }}\" value=\"{{ values.value }}\">
    {% endfor %}

    {% set flag = false %}


    {% set renderStyle = \"standardForm\" in form.getClass or \"noIntBorder\" in form.getClass ? 'flex' : 'table' %}



    {% for row in form.getRows %}

    {% if renderStyle == 'flex' %}
    {% set rowClass = 'flex flex-col sm:flex-row justify-between content-center p-0' %}
    {% endif %}

    {% if row.getClass starts with 'break' %}

    {% if flag == true %}
    </div>
    {% endif %}
    <div class='row mb-1' id=\"tbody_{{ row.getID }}\">
        {% set flag = true %}
        {% set flag = true %}
        {% endif %}

        <div id=\"{{ row.getID }}\" class=\"row mb-1 {{ row.getClass }}\">

            {% for element in row.getElements %}
            {% set colspan = loop.last and loop.length < totalColumns ? (totalColumns + 1 - loop.length) : 0  %}

            {% if renderStyle == 'flex' %}
            {% if element.isInstanceOf('Pupilsight\\\\Forms\\\\Layout\\\\Label') %}
            {% set class = 'flex flex-col flex-grow justify-center -mb-1 sm:mb-0 ' %}
            {% elseif element.isInstanceOf('Pupilsight\\\\Forms\\\\Layout\\\\Column') %}
            {% set class = loop.last and loop.length == 2 ? 'w-full max-w-full sm:max-w-xs flex justify-end' : 'w-full ' %}
            {% elseif loop.last and loop.length == 2 %}
            {% set class = 'w-full max-w-full sm:max-w-xs flex justify-end items-center' %}
            {% else %}
            {% set class = 'flex-grow justify-center' %}
            {% endif %}
            {% else %}
            {% set class = '' %}
            {% endif %}

            <div class=\"col-sm  {{ element.getClass }}\">
                <div>
                    {{ element.getOutput|replace({'standardWidth': renderStyle == 'flex' ? 'w-full' : '' })|raw }}
                </div>

                {% if element.instanceOf('Pupilsight\\\\Forms\\\\ValidatableInterface') %}
                <script type=\"text/javascript\">
                    {{ element.getValidationOutput | raw }}
                </script>
                {% endif %}
            </div>
            {% endfor %}

        </div>
        {% endfor %}

        <script type=\"text/javascript\">
            {% for code in javascript %}
            {{ code | raw }}
            {% endfor %}
        </script>
</form>", "components/form.twig.html", "F:\\suhail\\Office\\xampp\\htdocs\\newcode\\pupilsight_new\\resources\\templates\\components\\form.twig.html");
    }
}
