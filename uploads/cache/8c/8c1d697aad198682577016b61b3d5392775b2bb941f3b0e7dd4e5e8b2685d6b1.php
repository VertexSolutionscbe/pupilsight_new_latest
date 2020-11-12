<?php

/* formats/familyAddresses.twig.html */
class __TwigTemplate_9b92927400e9e27f4eda451a34155ab5c624304fc5a615f07811029b8b491367 extends Twig_Template
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
        // line 11
        echo "
";
        // line 12
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["families"] ?? null));
        $context['_iterated'] = false;
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
        foreach ($context['_seq'] as $context["_key"] => $context["family"]) {
            // line 13
            echo "
    ";
            // line 14
            if ((twig_get_attribute($this->env, $this->source, $context["loop"], "length", array()) > 1)) {
                // line 15
                echo "        <u>";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["family"], "name", array()), "html", null, true);
                echo "</u><br/>
    ";
            }
            // line 17
            echo "
    ";
            // line 18
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('formatUsing')->getCallable(), array("address", twig_get_attribute($this->env, $this->source, $context["family"], "homeAddress", array()), twig_get_attribute($this->env, $this->source, $context["family"], "homeAddressDistrict", array()), twig_get_attribute($this->env, $this->source, $context["family"], "homeAddressCountry", array()))), "html", null, true);
            echo "

    ";
            // line 20
            if ( !twig_get_attribute($this->env, $this->source, $context["loop"], "last", array())) {
                echo "<br/><br/>";
            }
            // line 21
            echo "
";
            $context['_iterated'] = true;
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        if (!$context['_iterated']) {
            // line 23
            echo "
    ";
            // line 24
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('formatUsing')->getCallable(), array("address", twig_get_attribute($this->env, $this->source, ($context["person"] ?? null), "address1", array()), twig_get_attribute($this->env, $this->source, ($context["person"] ?? null), "address1District", array()), twig_get_attribute($this->env, $this->source, ($context["person"] ?? null), "address1Country", array()))), "html", null, true);
            echo "

";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['family'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
    }

    public function getTemplateName()
    {
        return "formats/familyAddresses.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  84 => 24,  81 => 23,  67 => 21,  63 => 20,  58 => 18,  55 => 17,  49 => 15,  47 => 14,  44 => 13,  26 => 12,  23 => 11,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/

Page Foot: Outputs the contents of the HTML <head> tag. This includes 
all stylesheets and scripts with a 'head' context.
-->#}

{% for family in families %}

    {% if loop.length > 1 %}
        <u>{{ family.name }}</u><br/>
    {% endif %}

    {{ formatUsing('address', family.homeAddress, family.homeAddressDistrict, family.homeAddressCountry) }}

    {% if not loop.last %}<br/><br/>{% endif %}

{% else %}

    {{ formatUsing('address', person.address1, person.address1District, person.address1Country) }}

{% endfor %}
", "formats/familyAddresses.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\formats\\familyAddresses.twig.html");
    }
}
