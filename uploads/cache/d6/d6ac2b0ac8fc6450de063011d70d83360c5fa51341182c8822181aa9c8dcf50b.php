<?php

/* welcome.twig.html */
class __TwigTemplate_7f4629c99bd3fbb49bad3d0ac0d57fd7ddb78aa20ecbafd0b9b33e86bd15d363 extends Twig_Template
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
        // line 9
        echo "<h2>";
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Welcome")), "html", null, true);
        echo "</h2>
<p>
    ";
        // line 11
        echo ($context["indexText"] ?? null);
        echo "
</p>

";
        // line 14
        if (($context["publicStudentApplications"] ?? null)) {
            // line 15
            echo "<h2 style='margin-top: 30px'>";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Student Applications")), "html", null, true);
            echo "</h2>
<p>
    ";
            // line 17
            $context["url"] = (($context["absoluteURL"] ?? null) . "/?q=/modules/Students/applicationForm.php");
            // line 18
            echo "    ";
            echo sprintf(call_user_func_array($this->env->getFunction('__')->getCallable(), array("Parents of students interested in study at %1\$s may use our %2\$s online form%3\$s to initiate the application
    process.")),             // line 19
($context["organisationName"] ?? null), (("<a href=\"" . ($context["url"] ?? null)) . "\">"), "</a>");
            echo "
</p>
";
        }
        // line 22
        echo "
";
        // line 23
        if (($context["publicStaffApplications"] ?? null)) {
            // line 24
            echo "<h2 style='margin-top: 30px'>";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Staff Applications")), "html", null, true);
            echo "</h2>
<p>
    ";
            // line 26
            $context["url"] = (($context["absoluteURL"] ?? null) . "/?q=/modules/Staff/applicationForm_jobOpenings_view.php");
            // line 27
            echo "    ";
            echo sprintf(call_user_func_array($this->env->getFunction('__')->getCallable(), array("Individuals interested in working at %1\$s may use our %2\$s online form%3\$s to view job openings and begin the
    recruitment process.")),             // line 28
($context["organisationName"] ?? null), (("<a href=\"" . ($context["url"] ?? null)) . "\">"), "</a>");
            echo "
</p>
";
        }
        // line 31
        echo "
";
        // line 32
        if (($context["makeDepartmentsPublic"] ?? null)) {
            // line 33
            echo "<h2 style='margin-top: 30px'>";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Departments")), "html", null, true);
            echo "</h2>
<p>
    ";
            // line 35
            $context["url"] = (($context["absoluteURL"] ?? null) . "/?q=/modules/Departments/departments.php");
            // line 36
            echo "    ";
            echo sprintf(call_user_func_array($this->env->getFunction('__')->getCallable(), array("Please feel free to %1\$sbrowse our departmental information%2\$s, to learn more about %3\$s.")), (("<a
        href=\"" .             // line 37
($context["url"] ?? null)) . "\">"), "</a>", ($context["organisationName"] ?? null));
            echo "
</p>
";
        }
        // line 40
        echo "
";
        // line 41
        if (($context["makeUnitsPublic"] ?? null)) {
            // line 42
            echo "<h2 style='margin-top: 30px'>";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Learn With Us")), "html", null, true);
            echo "</h2>
<p>
    ";
            // line 44
            $context["url"] = (($context["absoluteURL"] ?? null) . "/?q=/modules/Planner/units_public.php&sidebar=false");
            // line 45
            echo "    ";
            echo sprintf(call_user_func_array($this->env->getFunction('__')->getCallable(), array("We are sharing some of our units of study with members of the public, so you can learn with us. Feel free to
    %1\$sbrowse our public units%2\$s.")), (("<a href=\"" .             // line 46
($context["url"] ?? null)) . "\">"), "</a>", ($context["organisationName"] ?? null));
            echo "
</p>
";
        }
        // line 49
        echo "
";
        // line 50
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["indexHooks"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["hook"]) {
            // line 51
            echo "<h2 style='margin-top: 30px'>";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["hook"], "title", array()), "html", null, true);
            echo "</h2>
<p>
    ";
            // line 53
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
        return array (  133 => 53,  127 => 51,  123 => 50,  120 => 49,  114 => 46,  111 => 45,  109 => 44,  103 => 42,  101 => 41,  98 => 40,  92 => 37,  89 => 36,  87 => 35,  81 => 33,  79 => 32,  76 => 31,  70 => 28,  67 => 27,  65 => 26,  59 => 24,  57 => 23,  54 => 22,  48 => 19,  45 => 18,  43 => 17,  37 => 15,  35 => 14,  29 => 11,  23 => 9,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#
<!--
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
    {{ __('Parents of students interested in study at %1\$s may use our %2\$s online form%3\$s to initiate the application
    process.')|format(organisationName, '<a href=\"' ~ url ~ '\">', '</a>')|raw }}
</p>
{% endif %}

{% if publicStaffApplications %}
<h2 style='margin-top: 30px'>{{ __('Staff Applications') }}</h2>
<p>
    {% set url = absoluteURL ~ \"/?q=/modules/Staff/applicationForm_jobOpenings_view.php\" %}
    {{ __('Individuals interested in working at %1\$s may use our %2\$s online form%3\$s to view job openings and begin the
    recruitment process.')|format(organisationName, '<a href=\"' ~ url ~ '\">', '</a>')|raw }}
</p>
{% endif %}

{% if makeDepartmentsPublic %}
<h2 style='margin-top: 30px'>{{ __('Departments') }}</h2>
<p>
    {% set url = absoluteURL ~ \"/?q=/modules/Departments/departments.php\" %}
    {{ __('Please feel free to %1\$sbrowse our departmental information%2\$s, to learn more about %3\$s.')|format('<a
        href=\"' ~ url ~ '\">', '</a>', organisationName)|raw }}
</p>
{% endif %}

{% if makeUnitsPublic %}
<h2 style='margin-top: 30px'>{{ __('Learn With Us') }}</h2>
<p>
    {% set url = absoluteURL ~ \"/?q=/modules/Planner/units_public.php&sidebar=false\" %}
    {{ __('We are sharing some of our units of study with members of the public, so you can learn with us. Feel free to
    %1\$sbrowse our public units%2\$s.')|format('<a href=\"' ~ url ~ '\">', '</a>', organisationName)|raw }}
</p>
{% endif %}

{% for hook in indexHooks %}
<h2 style='margin-top: 30px'>{{ hook.title }}</h2>
<p>
    {{ hook.text|raw }}
</p>
{% endfor %}", "welcome.twig.html", "F:\\suhail\\Office\\xampp\\htdocs\\newcode\\pupilsight_new\\resources\\templates\\welcome.twig.html");
    }
}
