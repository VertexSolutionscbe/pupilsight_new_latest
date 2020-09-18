<?php

/* welcome.twig.html */
class __TwigTemplate_a389ee9ac33b75dc87345441c2cbd2ae1e81f4bc26b7354628f5d2cf8028de3d extends Twig_Template
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
        echo "<h2>";
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Welcome")), "html", null, true);
        echo "</h2>
<p>
    ";
        // line 10
        echo ($context["indexText"] ?? null);
        echo "
</p>

";
        // line 13
        if (($context["publicStudentApplications"] ?? null)) {
            // line 14
            echo "    <h2 style='margin-top: 30px'>";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Student Applications")), "html", null, true);
            echo "</h2>
    <p>
        ";
            // line 16
            $context["url"] = (($context["absoluteURL"] ?? null) . "/?q=/modules/Students/applicationForm.php");
            // line 17
            echo "        ";
            echo sprintf(call_user_func_array($this->env->getFunction('__')->getCallable(), array("Parents of students interested in study at %1\$s may use our %2\$s online form%3\$s to initiate the application process.")), ($context["organisationName"] ?? null), (("<a href=\"" . ($context["url"] ?? null)) . "\">"), "</a>");
            echo "
    </p>
";
        }
        // line 20
        echo "
";
        // line 21
        if (($context["publicStaffApplications"] ?? null)) {
            // line 22
            echo "    <h2 style='margin-top: 30px'>";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Staff Applications")), "html", null, true);
            echo "</h2>
    <p>
        ";
            // line 24
            $context["url"] = (($context["absoluteURL"] ?? null) . "/?q=/modules/Staff/applicationForm_jobOpenings_view.php");
            // line 25
            echo "        ";
            echo sprintf(call_user_func_array($this->env->getFunction('__')->getCallable(), array("Individuals interested in working at %1\$s may use our %2\$s online form%3\$s to view job openings and begin the recruitment process.")), ($context["organisationName"] ?? null), (("<a href=\"" . ($context["url"] ?? null)) . "\">"), "</a>");
            echo "
    </p>
";
        }
        // line 28
        echo "
";
        // line 29
        if (($context["makeDepartmentsPublic"] ?? null)) {
            // line 30
            echo "    <h2 style='margin-top: 30px'>";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Departments")), "html", null, true);
            echo "</h2>
    <p>
        ";
            // line 32
            $context["url"] = (($context["absoluteURL"] ?? null) . "/?q=/modules/Departments/departments.php");
            // line 33
            echo "        ";
            echo sprintf(call_user_func_array($this->env->getFunction('__')->getCallable(), array("Please feel free to %1\$sbrowse our departmental information%2\$s, to learn more about %3\$s.")), (("<a href=\"" . ($context["url"] ?? null)) . "\">"), "</a>", ($context["organisationName"] ?? null));
            echo "
    </p>
";
        }
        // line 36
        echo "
";
        // line 37
        if (($context["makeUnitsPublic"] ?? null)) {
            // line 38
            echo "    <h2 style='margin-top: 30px'>";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Learn With Us")), "html", null, true);
            echo "</h2>
    <p>
        ";
            // line 40
            $context["url"] = (($context["absoluteURL"] ?? null) . "/?q=/modules/Planner/units_public.php&sidebar=false");
            // line 41
            echo "        ";
            echo sprintf(call_user_func_array($this->env->getFunction('__')->getCallable(), array("We are sharing some of our units of study with members of the public, so you can learn with us. Feel free to %1\$sbrowse our public units%2\$s.")), (("<a href=\"" . ($context["url"] ?? null)) . "\">"), "</a>", ($context["organisationName"] ?? null));
            echo "
    </p>
";
        }
        // line 44
        echo "
";
        // line 45
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["indexHooks"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["hook"]) {
            // line 46
            echo "    <h2 style='margin-top: 30px'>";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["hook"], "title", array()), "html", null, true);
            echo "</h2>
    <p>
        ";
            // line 48
            echo twig_get_attribute($this->env, $this->source, $context["hook"], "text", array());
            echo "
    </p>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['hook'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
    }

    public function getTemplateName()
    {
        return "welcome.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  125 => 48,  119 => 46,  115 => 45,  112 => 44,  105 => 41,  103 => 40,  97 => 38,  95 => 37,  92 => 36,  85 => 33,  83 => 32,  77 => 30,  75 => 29,  72 => 28,  65 => 25,  63 => 24,  57 => 22,  55 => 21,  52 => 20,  45 => 17,  43 => 16,  37 => 14,  35 => 13,  29 => 10,  23 => 8,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/
-->#}
<h2>{{ __('Welcome') }}</h2>
<p>
    {{ indexText | raw }}
</p>

{% if publicStudentApplications %}
    <h2 style='margin-top: 30px'>{{ __('Student Applications') }}</h2>
    <p>
        {% set url = absoluteURL ~ \"/?q=/modules/Students/applicationForm.php\" %}
        {{ __('Parents of students interested in study at %1\$s may use our %2\$s online form%3\$s to initiate the application process.')|format(organisationName, '<a href=\"' ~ url ~ '\">', '</a>')|raw }}
    </p>
{% endif %}

{% if publicStaffApplications %}
    <h2 style='margin-top: 30px'>{{ __('Staff Applications') }}</h2>
    <p>
        {% set url = absoluteURL ~ \"/?q=/modules/Staff/applicationForm_jobOpenings_view.php\" %}
        {{ __('Individuals interested in working at %1\$s may use our %2\$s online form%3\$s to view job openings and begin the recruitment process.')|format(organisationName, '<a href=\"' ~ url ~ '\">', '</a>')|raw }}
    </p>
{% endif %}

{% if makeDepartmentsPublic %}
    <h2 style='margin-top: 30px'>{{ __('Departments') }}</h2>
    <p>
        {% set url = absoluteURL ~ \"/?q=/modules/Departments/departments.php\" %}
        {{ __('Please feel free to %1\$sbrowse our departmental information%2\$s, to learn more about %3\$s.')|format('<a href=\"' ~ url ~ '\">', '</a>', organisationName)|raw }}
    </p>
{% endif %}

{% if makeUnitsPublic %}
    <h2 style='margin-top: 30px'>{{ __('Learn With Us') }}</h2>
    <p>
        {% set url = absoluteURL ~ \"/?q=/modules/Planner/units_public.php&sidebar=false\" %}
        {{ __('We are sharing some of our units of study with members of the public, so you can learn with us. Feel free to %1\$sbrowse our public units%2\$s.')|format('<a href=\"' ~ url ~ '\">', '</a>', organisationName)|raw }}
    </p>
{% endif %}

{% for hook in indexHooks %}
    <h2 style='margin-top: 30px'>{{ hook.title }}</h2>
    <p>
        {{ hook.text|raw }}
    </p>
{% endfor %}
", "welcome.twig.html", "C:\\xampp\\htdocs\\pupilsightnew\\resources\\templates\\welcome.twig.html");
    }
}
