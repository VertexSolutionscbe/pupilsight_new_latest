<?php

/* head.twig.html */
class __TwigTemplate_937c6eee23409fb1b144736c26943679ff1d988a7d4159928ba746395a4b7f46 extends Twig_Template
{
    private $source;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = array(
            'meta' => array($this, 'block_meta'),
            'styles' => array($this, 'block_styles'),
            'scripts' => array($this, 'block_scripts'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 12
        echo "
";
        // line 13
        $this->displayBlock('meta', $context, $blocks);
        // line 23
        echo "

";
        // line 25
        $this->displayBlock('styles', $context, $blocks);
        // line 36
        echo "

";
        // line 38
        $this->displayBlock('scripts', $context, $blocks);
    }

    // line 13
    public function block_meta($context, array $blocks = array())
    {
        // line 14
        echo "    
\t<meta charset=\"utf-8\">
    <meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\">
    <title>";
        // line 17
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "title", array()), "html", null, true);
        echo "</title>
    <meta name=\"description\" content=\"\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <!-- Favicon -->
    <link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"./favicon.ico\">
";
    }

    // line 25
    public function block_styles($context, array $blocks = array())
    {
        // line 26
        echo "    ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "stylesheets", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["asset"]) {
            // line 27
            echo "        ";
            $context["assetVersion"] = (( !twig_test_empty(twig_get_attribute($this->env, $this->source, $context["asset"], "version", array()))) ? (twig_get_attribute($this->env, $this->source, $context["asset"], "version", array())) : (($context["version"] ?? null)));
            // line 28
            echo "        ";
            if ((twig_get_attribute($this->env, $this->source, $context["asset"], "type", array()) == "inline")) {
                // line 29
                echo "            <style type=\"text/css\" >";
                echo twig_get_attribute($this->env, $this->source, $context["asset"], "src", array());
                echo "</style>
        ";
            } else {
                // line 31
                echo "            <link rel=\"stylesheet\" href=\"";
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["asset"], "src", array()), "html", null, true);
                echo "?v=";
                echo twig_escape_filter($this->env, ($context["assetVersion"] ?? null), "html", null, true);
                echo "\" type=\"text/css\" media=\"";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["asset"], "media", array()), "html", null, true);
                echo "\" />
        ";
            }
            // line 33
            echo "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['asset'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 34
        echo "\t
";
    }

    // line 38
    public function block_scripts($context, array $blocks = array())
    {
        // line 39
        echo "    ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "scriptsHead", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["asset"]) {
            // line 40
            echo "        ";
            $context["assetVersion"] = (( !twig_test_empty(twig_get_attribute($this->env, $this->source, $context["asset"], "version", array()))) ? (twig_get_attribute($this->env, $this->source, $context["asset"], "version", array())) : (($context["version"] ?? null)));
            // line 41
            echo "        ";
            if ((twig_get_attribute($this->env, $this->source, $context["asset"], "type", array()) == "inline")) {
                // line 42
                echo "            <script type=\"text/javascript\">";
                echo twig_get_attribute($this->env, $this->source, $context["asset"], "src", array());
                echo "</script>
        ";
            } else {
                // line 44
                echo "            <script type=\"text/javascript\" src=\"";
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["asset"], "src", array()), "html", null, true);
                echo "?v=";
                echo twig_escape_filter($this->env, ($context["assetVersion"] ?? null), "html", null, true);
                echo "\"></script>
        ";
            }
            // line 46
            echo "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['asset'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 47
        echo "
    ";
        // line 48
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "extraHead", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["code"]) {
            // line 49
            echo "        ";
            echo $context["code"];
            echo "
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['code'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
    }

    public function getTemplateName()
    {
        return "head.twig.html";
    }

    public function getDebugInfo()
    {
        return array (  149 => 49,  145 => 48,  142 => 47,  136 => 46,  126 => 44,  120 => 42,  117 => 41,  114 => 40,  109 => 39,  106 => 38,  101 => 34,  95 => 33,  83 => 31,  77 => 29,  74 => 28,  71 => 27,  66 => 26,  63 => 25,  53 => 17,  48 => 14,  45 => 13,  41 => 38,  37 => 36,  35 => 25,  31 => 23,  29 => 13,  26 => 12,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#
<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/

Page Foot: Outputs the contents of the HTML <head> tag. This includes 
all stylesheets and scripts with a 'head' context.
-->#}

{% block meta %}
    
\t<meta charset=\"utf-8\">
    <meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\">
    <title>{{ page.title }}</title>
    <meta name=\"description\" content=\"\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <!-- Favicon -->
    <link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"./favicon.ico\">
{% endblock meta %}


{% block styles %}
    {% for asset in page.stylesheets %}
        {% set assetVersion = asset.version is not empty ? asset.version : version %}
        {% if asset.type == 'inline' %}
            <style type=\"text/css\" >{{ asset.src|raw }}</style>
        {% else %}
            <link rel=\"stylesheet\" href=\"{{ absoluteURL }}/{{ asset.src }}?v={{ assetVersion }}\" type=\"text/css\" media=\"{{ asset.media }}\" />
        {% endif %}
    {% endfor %}
\t
{% endblock styles %}


{% block scripts %}
    {% for asset in page.scriptsHead %}
        {% set assetVersion = asset.version is not empty ? asset.version : version %}
        {% if asset.type == 'inline' %}
            <script type=\"text/javascript\">{{ asset.src|raw }}</script>
        {% else %}
            <script type=\"text/javascript\" src=\"{{ absoluteURL }}/{{ asset.src }}?v={{ assetVersion }}\"></script>
        {% endif %}
    {% endfor %}

    {% for code in page.extraHead %}
        {{ code|raw }}
    {% endfor %}
{% endblock scripts %}
", "head.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\head.twig.html");
    }
}
