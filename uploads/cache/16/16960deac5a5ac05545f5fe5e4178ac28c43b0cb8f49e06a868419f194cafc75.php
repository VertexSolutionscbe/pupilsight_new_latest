<?php

/* foot.twig.html */
class __TwigTemplate_0331f4319f675cf1de5d3bd8e2298ca635c78cd61016b4f0eb551e815e18e95e extends Twig_Template
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
        // line 12
        echo "
";
        // line 13
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "extraFoot", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["code"]) {
            // line 14
            echo "    ";
            echo $context["code"];
            echo "
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['code'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 16
        echo "
";
        // line 17
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "scriptsFoot", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["asset"]) {
            // line 18
            echo "    ";
            $context["assetVersion"] = (( !twig_test_empty(twig_get_attribute($this->env, $this->source, $context["asset"], "version", array()))) ? (twig_get_attribute($this->env, $this->source, $context["asset"], "version", array())) : (($context["version"] ?? null)));
            // line 19
            echo "    ";
            if ((twig_get_attribute($this->env, $this->source, $context["asset"], "type", array()) == "inline")) {
                // line 20
                echo "        <script type=\"text/javascript\">";
                echo twig_get_attribute($this->env, $this->source, $context["asset"], "src", array());
                echo "</script>
    ";
            } else {
                // line 22
                echo "        <script type=\"text/javascript\" src=\"";
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["asset"], "src", array()), "html", null, true);
                echo "?v=";
                echo twig_escape_filter($this->env, ($context["assetVersion"] ?? null), "html", null, true);
                echo "\"></script>
    ";
            }
            // line 24
            echo "\t
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['asset'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 26
        echo "
";
    }

    public function getTemplateName()
    {
        return "foot.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  75 => 26,  68 => 24,  58 => 22,  52 => 20,  49 => 19,  46 => 18,  42 => 17,  39 => 16,  30 => 14,  26 => 13,  23 => 12,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#
<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/

Page Foot: Outputs at the bottom, right before the closing </body> tag.
Useful for scripts that need to execute after the page has loaded.
-->#}

{% for code in page.extraFoot %}
    {{ code|raw }}
{% endfor %}

{% for asset in page.scriptsFoot %}
    {% set assetVersion = asset.version is not empty ? asset.version : version %}
    {% if asset.type == 'inline' %}
        <script type=\"text/javascript\">{{ asset.src|raw }}</script>
    {% else %}
        <script type=\"text/javascript\" src=\"{{ absoluteURL }}/{{ asset.src }}?v={{ assetVersion }}\"></script>
    {% endif %}
\t
{% endfor %}

", "foot.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\foot.twig.html");
    }
}
