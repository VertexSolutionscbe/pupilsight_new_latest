<?php

/* formats/familyContacts.twig.html */
class __TwigTemplate_eab91fdc21c0887c1c72d2ce280e3343d681f9aaa56ff41d71ecbbd44c0a71d7 extends Twig_Template
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
        $context['_seq'] = twig_ensure_traversable(($context["familyAdults"] ?? null));
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
        foreach ($context['_seq'] as $context["_key"] => $context["adult"]) {
            // line 13
            echo "
    <u>";
            // line 14
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('formatUsing')->getCallable(), array("name", twig_get_attribute($this->env, $this->source, $context["adult"], "title", array()), twig_get_attribute($this->env, $this->source, $context["adult"], "preferredName", array()), twig_get_attribute($this->env, $this->source, $context["adult"], "surname", array()), "Parent")), "html", null, true);
            echo "</u>
    ";
            // line 15
            if ((twig_get_attribute($this->env, $this->source, $context["adult"], "status", array()) != "Full")) {
                echo "<i>(";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, $context["adult"], "status", array()))), "html", null, true);
                echo ")</i>";
            }
            // line 16
            echo "    <br/>

    ";
            // line 18
            if ((twig_get_attribute($this->env, $this->source, $context["adult"], "childDataAccess", array()) == "N")) {
                // line 19
                echo "        <strong style=\"color: #cc0000\">";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Data Access")), "html", null, true);
                echo ": ";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("No")), "html", null, true);
                echo "</strong><br/>
    ";
            }
            // line 21
            echo "
    ";
            // line 22
            if (twig_get_attribute($this->env, $this->source, $context["adult"], "email", array())) {
                // line 23
                echo "        <i>";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Email")), "html", null, true);
                echo "</i>: ";
                echo call_user_func_array($this->env->getFunction('formatUsing')->getCallable(), array("link", ("mailto:" . twig_get_attribute($this->env, $this->source, $context["adult"], "email", array())), twig_get_attribute($this->env, $this->source, $context["adult"], "email", array())));
                echo "<br/>
    ";
            }
            // line 25
            echo "
    ";
            // line 26
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(range(1, 4));
            foreach ($context['_seq'] as $context["_key"] => $context["i"]) {
                // line 27
                echo "        ";
                if ((($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 = $context["adult"]) && is_array($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5) || $__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 instanceof ArrayAccess ? ($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5[("phone" . $context["i"])] ?? null) : null)) {
                    // line 28
                    echo "            ";
                    echo call_user_func_array($this->env->getFunction('formatUsing')->getCallable(), array("phone", (($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a = $context["adult"]) && is_array($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a) || $__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a instanceof ArrayAccess ? ($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a[("phone" . $context["i"])] ?? null) : null), (($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 = $context["adult"]) && is_array($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57) || $__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 instanceof ArrayAccess ? ($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57[(("phone" . $context["i"]) . "CountryCode")] ?? null) : null), (("<i>" . (($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 = $context["adult"]) && is_array($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9) || $__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 instanceof ArrayAccess ? ($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9[(("phone" . $context["i"]) . "Type")] ?? null) : null)) . "</i>")));
                    echo "<br/>
        ";
                }
                // line 30
                echo "    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['i'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 31
            echo "
    ";
            // line 32
            if (($context["includeCitizenship"] ?? null)) {
                // line 33
                echo "
        ";
                // line 34
                if ((twig_get_attribute($this->env, $this->source, $context["adult"], "citizenship1", array()) || twig_get_attribute($this->env, $this->source, $context["adult"], "citizenship1Passport", array()))) {
                    // line 35
                    echo "        <i>";
                    echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Passport")), "html", null, true);
                    echo "</i>: ";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["adult"], "citizenship1", array()), "html", null, true);
                    echo " ";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["adult"], "citizenship1Passport", array()), "html", null, true);
                    echo "<br/>
        ";
                }
                // line 37
                echo "
        ";
                // line 38
                if (twig_get_attribute($this->env, $this->source, $context["adult"], "nationalIDCardNumber", array())) {
                    // line 39
                    echo "            <i>";
                    echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("National ID Card")), "html", null, true);
                    echo "</i>: ";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["adult"], "nationalIDCardNumber", array()), "html", null, true);
                    echo "<br/>
        ";
                }
                // line 41
                echo "
    ";
            }
            // line 43
            echo "
    ";
            // line 44
            if ( !twig_get_attribute($this->env, $this->source, $context["loop"], "last", array())) {
                echo "<br/>";
            }
            // line 45
            echo "
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
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['adult'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
    }

    public function getTemplateName()
    {
        return "formats/familyContacts.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  149 => 45,  145 => 44,  142 => 43,  138 => 41,  130 => 39,  128 => 38,  125 => 37,  115 => 35,  113 => 34,  110 => 33,  108 => 32,  105 => 31,  99 => 30,  93 => 28,  90 => 27,  86 => 26,  83 => 25,  75 => 23,  73 => 22,  70 => 21,  62 => 19,  60 => 18,  56 => 16,  50 => 15,  46 => 14,  43 => 13,  26 => 12,  23 => 11,);
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

{% for adult in familyAdults %}

    <u>{{ formatUsing('name', adult.title, adult.preferredName, adult.surname, \"Parent\") }}</u>
    {% if adult.status != 'Full' %}<i>({{ __(adult.status) }})</i>{% endif %}
    <br/>

    {% if adult.childDataAccess == 'N' %}
        <strong style=\"color: #cc0000\">{{ __('Data Access') }}: {{ __('No') }}</strong><br/>
    {% endif %}

    {% if adult.email %}
        <i>{{ __('Email') }}</i>: {{ formatUsing('link', \"mailto:\" ~ adult.email, adult.email)|raw }}<br/>
    {% endif %}

    {% for i in 1..4 %}
        {% if adult[\"phone\"~i] %}
            {{ formatUsing('phone', adult[\"phone\"~i], adult[\"phone\"~i~\"CountryCode\"], \"<i>\"~adult[\"phone\"~i~\"Type\"]~\"</i>\")|raw }}<br/>
        {% endif %}
    {% endfor %}

    {% if includeCitizenship %}

        {% if adult.citizenship1 or adult.citizenship1Passport %}
        <i>{{ __('Passport') }}</i>: {{ adult.citizenship1 }} {{ adult.citizenship1Passport }}<br/>
        {% endif %}

        {% if adult.nationalIDCardNumber %}
            <i>{{ __('National ID Card') }}</i>: {{ adult.nationalIDCardNumber }}<br/>
        {% endif %}

    {% endif %}

    {% if not loop.last %}<br/>{% endif%}

{% endfor %}
", "formats/familyContacts.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\formats\\familyContacts.twig.html");
    }
}
