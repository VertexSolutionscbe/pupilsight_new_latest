<?php

/* finder.twig.html */
class __TwigTemplate_f54df4105df4a45755a359043c2d96352b72acebda1206e3dbaf018b2a9d9449 extends Twig_Template
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
        echo "<style>
    .mbl_searchicon {
        height: 35px;
        width: 35px;
        border-radius: 50% !important;
        margin-bottom: 0px;
        margin-top: 10px;
        font-size: 14px;
    }
    /* .mbl_searchinputs{
        line-height: 50px !important;
    font-size: 16px;
    top: 58px;
    min-width: 260px;
    padding: 15px 10px;
    border-radius: 4px;
    -webkit-box-shadow: 0px 0px 10px 0px rgba(33, 30, 30, 0.15);
    box-shadow: 0px 0px 10px 0px rgba(33, 30, 30, 0.15);
    } */
</style>
<button data-toggle=\"#fastFinder\" class=\"mbl_searchicon flex md:hidden items-center rounded bg-gray mr-4 px-4 py-3 text-base\">
    <span class=\"hidden sm:inline text-gray text-xs font-bold uppercase pr-2\">";
        // line 32
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Fast Finder")), "html", null, true);
        echo "</span>
    <!-- <img src=\"";
        // line 33
        echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
        echo "/themes/";
        echo twig_escape_filter($this->env, ($context["pupilsightThemeName"] ?? null), "html", null, true);
        echo "/img/search.png\" width=\"25\" height=\"25\"> -->
    <i class=\"fas fa-search \"></i>
</button>

<div id=\"fastFinder \" class=\"hidden-1 md:block md:static top-0 left-0 w-full md:max-w-md p-2 sm:p-4\">
    <div class=\"z-10 rounded \">
        <!-- 
        <a data-toggle=\"#fastFinder \" class=\"p-2 pl-4 float-right text-xs underline md:hidden \" href=\"# \">";
        // line 40
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("close")), "html", null, true);
        echo "</a> -->

        <!-- <div class=\"py-2 md:py-1 px-2 border-solid border-0 border-b border-gray-300 md:text-right text-gray text-xxs font-bold uppercase\">
            ";
        // line 43
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Fast Finder")), "html", null, true);
        echo ": 
            ";
        // line 44
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Actions")), "html", null, true);
        // line 45
        echo twig_escape_filter($this->env, ((($context["classIsAccessible"] ?? null)) ? ((", " . call_user_func_array($this->env->getFunction('__')->getCallable(), array("Classes")))) : ("")), "html", null, true);
        // line 46
        echo twig_escape_filter($this->env, ((($context["studentIsAccessible"] ?? null)) ? ((", " . call_user_func_array($this->env->getFunction('__')->getCallable(), array("Students")))) : ("")), "html", null, true);
        // line 47
        echo twig_escape_filter($this->env, ((($context["staffIsAccessible"] ?? null)) ? ((", " . call_user_func_array($this->env->getFunction('__')->getCallable(), array("Staff")))) : ("")), "html", null, true);
        echo "
        </div>-->

        <div class=\"w-full px-2 sm:py-2\">
            ";
        // line 51
        echo ($context["form"] ?? null);
        echo "
        </div>

        ";
        // line 54
        if (((($context["roleCategory"] ?? null) == "Staff") && ($context["enrolmentCount"] ?? null))) {
            // line 55
            echo "        <!--<div class=\"py-1 px-2 text-right text-gray text-xxs font-normal italic \">
            ";
            // line 56
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Total Student Enrolment:")), "html", null, true);
            echo "
            ";
            // line 57
            echo twig_escape_filter($this->env, ($context["enrolmentCount"] ?? null), "html", null, true);
            echo "
        </div>-->
        ";
        }
        // line 60
        echo "    </div>
</div>";
    }

    public function getTemplateName()
    {
        return "finder.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  106 => 60,  100 => 57,  96 => 56,  93 => 55,  91 => 54,  85 => 51,  78 => 47,  76 => 46,  74 => 45,  72 => 44,  68 => 43,  62 => 40,  50 => 33,  46 => 32,  23 => 11,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#
<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/

Fast Finder
-->#}
<style>
    .mbl_searchicon {
        height: 35px;
        width: 35px;
        border-radius: 50% !important;
        margin-bottom: 0px;
        margin-top: 10px;
        font-size: 14px;
    }
    /* .mbl_searchinputs{
        line-height: 50px !important;
    font-size: 16px;
    top: 58px;
    min-width: 260px;
    padding: 15px 10px;
    border-radius: 4px;
    -webkit-box-shadow: 0px 0px 10px 0px rgba(33, 30, 30, 0.15);
    box-shadow: 0px 0px 10px 0px rgba(33, 30, 30, 0.15);
    } */
</style>
<button data-toggle=\"#fastFinder\" class=\"mbl_searchicon flex md:hidden items-center rounded bg-gray mr-4 px-4 py-3 text-base\">
    <span class=\"hidden sm:inline text-gray text-xs font-bold uppercase pr-2\">{{ __('Fast Finder') }}</span>
    <!-- <img src=\"{{ absoluteURL }}/themes/{{ pupilsightThemeName }}/img/search.png\" width=\"25\" height=\"25\"> -->
    <i class=\"fas fa-search \"></i>
</button>

<div id=\"fastFinder \" class=\"hidden-1 md:block md:static top-0 left-0 w-full md:max-w-md p-2 sm:p-4\">
    <div class=\"z-10 rounded \">
        <!-- 
        <a data-toggle=\"#fastFinder \" class=\"p-2 pl-4 float-right text-xs underline md:hidden \" href=\"# \">{{ __('close') }}</a> -->

        <!-- <div class=\"py-2 md:py-1 px-2 border-solid border-0 border-b border-gray-300 md:text-right text-gray text-xxs font-bold uppercase\">
            {{ __('Fast Finder') }}: 
            {{ __('Actions') }}
            {{- classIsAccessible ? \", \" ~ __('Classes') }}
            {{- studentIsAccessible ? \", \" ~ __('Students') }}
            {{- staffIsAccessible ? \", \" ~ __('Staff') }}
        </div>-->

        <div class=\"w-full px-2 sm:py-2\">
            {{ form|raw }}
        </div>

        {% if roleCategory == 'Staff' and enrolmentCount %}
        <!--<div class=\"py-1 px-2 text-right text-gray text-xxs font-normal italic \">
            {{ __('Total Student Enrolment:') }}
            {{ enrolmentCount }}
        </div>-->
        {% endif %}
    </div>
</div>", "finder.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\finder.twig.html");
    }
}
