<?php

/* fullscreen.twig.html */
class __TwigTemplate_d703a585e7c285a1136c8ff62fe30c493c472f7af1fe14744bedc55cdc3f859f extends Twig_Template
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
        echo "<!DOCTYPE html>
<html>
    <head>
        
    </head>
    <body style='background-image: none'>

        ";
        // line 15
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "alerts", array()));
        foreach ($context['_seq'] as $context["type"] => $context["alerts"]) {
            // line 16
            echo "            ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($context["alerts"]);
            foreach ($context['_seq'] as $context["_key"] => $context["text"]) {
                // line 17
                echo "                <div class=\"";
                echo twig_escape_filter($this->env, $context["type"], "html", null, true);
                echo "\">";
                echo $context["text"];
                echo "</div>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['text'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 19
            echo "        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['type'], $context['alerts'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 20
        echo "
        ";
        // line 21
        echo twig_join_filter(($context["content"] ?? null), "
");
        echo "
    </body>
</html>
";
    }

    public function getTemplateName()
    {
        return "fullscreen.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  61 => 21,  58 => 20,  52 => 19,  41 => 17,  36 => 16,  32 => 15,  23 => 8,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/
-->#}
<!DOCTYPE html>
<html>
    <head>
        
    </head>
    <body style='background-image: none'>

        {% for type, alerts in page.alerts %}
            {% for text in alerts %}
                <div class=\"{{ type }}\">{{ text|raw }}</div>
            {% endfor %}
        {% endfor %}

        {{ content|join(\"\\n\")|raw }}
    </body>
</html>
", "fullscreen.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\fullscreen.twig.html");
    }
}
