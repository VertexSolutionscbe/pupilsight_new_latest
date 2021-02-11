<?php

/* error.twig.html */
class __TwigTemplate_1dc5cde9a0e5df66285a7e1e15a825f755ad479124357bb6ac42452068b911ab extends Twig_Template
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
        echo "<h1>
    ";
        // line 9
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Oh no!")), "html", null, true);
        echo "
</h1>
<p>
    ";
        // line 12
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Something has gone wrong: the Pupilsights have escaped!")), "html", null, true);
        echo "<br/>
    <br/>
    ";
        // line 14
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("An error has occurred. This could mean a number of different things, but generally indicates that you have a misspelt address, or are trying to access a page that you are not permitted to access.")), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("If you cannot solve this problem by retyping the address, or through other means, please contact your system administrator.")), "html", null, true);
        echo "<br/>
</p>
";
    }

    public function getTemplateName()
    {
        return "error.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  37 => 14,  32 => 12,  26 => 9,  23 => 8,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/
-->#}
<h1>
    {{ __('Oh no!') }}
</h1>
<p>
    {{ __('Something has gone wrong: the Pupilsights have escaped!') }}<br/>
    <br/>
    {{ __('An error has occurred. This could mean a number of different things, but generally indicates that you have a misspelt address, or are trying to access a page that you are not permitted to access.') }} {{ __('If you cannot solve this problem by retyping the address, or through other means, please contact your system administrator.') }}<br/>
</p>
", "error.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\error.twig.html");
    }
}
