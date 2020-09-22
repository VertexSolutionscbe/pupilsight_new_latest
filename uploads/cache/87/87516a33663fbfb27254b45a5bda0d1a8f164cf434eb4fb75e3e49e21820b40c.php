<?php

/* components/editor.twig.html */
class __TwigTemplate_9156739d01237f14d918173256fb39d4fb744a1eb4130bb44f7be6337d149475 extends Twig_Template
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
        // line 10
        echo "
";
        // line 11
        $context["resourceAlphaSort"] = ((($context["resourceAlphaSort"] ?? null)) ? ("true") : ("false"));
        // line 12
        echo "
<a name=\"";
        // line 13
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "editor\"></a>

<div class=\"editor-toolbar flex flex-wrap sm:flex-no-wrap justify-between text-xs\">

    ";
        // line 17
        if (($context["showMedia"] ?? null)) {
            // line 18
            echo "    <div id=\"";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "mediaOuter\" class=\"h-6\">
        <div id=\"";
            // line 19
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "mediaInner\" class=\"flex items-center py-1\">
            <script type=\"text/javascript\">
            \$(document).ready(function(){
                \$(\".";
            // line 22
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceSlider, .";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceAddSlider, .";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceQuickSlider\").hide();
                \$(\".";
            // line 23
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "show_hide\").unbind('click').click(function(){
                    \$(\".";
            // line 24
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceSlider\").slideToggle();
                    \$(\".";
            // line 25
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceAddSlider, .";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceQuickSlider\").hide();
                    if (tinyMCE.get('";
            // line 26
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "').selection.getRng().startOffset < 1) {
                        tinyMCE.get('";
            // line 27
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "').focus();
                    }
                });
                \$(\".";
            // line 30
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "show_hideAdd\").unbind('click').click(function(){
                    \$(\".";
            // line 31
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceAddSlider\").slideToggle();
                    \$(\".";
            // line 32
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceSlider, .";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceQuickSlider\").hide();
                    if (tinyMCE.get('";
            // line 33
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "').selection.getRng().startOffset < 1) {
                        tinyMCE.get('";
            // line 34
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "').focus();
                    }
                });
                \$(\".";
            // line 37
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "show_hideQuickAdd\").unbind('click').click(function(){
                \$(\".";
            // line 38
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceQuickSlider\").slideToggle();
                \$(\".";
            // line 39
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceSlider, .";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceAddSlider\").hide();
                if (tinyMCE.get('";
            // line 40
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "').selection.getRng().startOffset < 1) {
                    tinyMCE.get('";
            // line 41
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "').focus();
                }
                });
            });
            </script>

            <div class=\"mr-2 flex items-center\">
                <span>";
            // line 48
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Shared Resources")), "html", null, true);
            echo ":</span> 
        
                <a title=\"";
            // line 50
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Insert Existing Resource")), "html", null, true);
            echo "\" class=\"";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "show_hide mx-1\" onclick='\$(\".";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceSlider\").load(\"";
            echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
            echo "/modules/Planner/resources_insert_ajax.php?alpha=";
            echo twig_escape_filter($this->env, ($context["resourceAlphaSort"] ?? null), "html", null, true);
            echo "&";
            echo twig_escape_filter($this->env, ($context["initialFilter"] ?? null), "html", null, true);
            echo "\",\"id=";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "&allowUpload=";
            echo twig_escape_filter($this->env, ($context["allowUpload"] ?? null), "html", null, true);
            echo "\");'>
                    <img src=\"";
            // line 51
            echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
            echo "/themes/Default/img/search_mini.png\" alt=\"";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Insert Existing Resource")), "html", null, true);
            echo "\" onclick=\"return false;\" />
                </a>
            
                ";
            // line 54
            if (($context["allowUpload"] ?? null)) {
                // line 55
                echo "                <a title=\"";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Create & Insert New Resource")), "html", null, true);
                echo "\" class=\"";
                echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
                echo "show_hideAdd mx-1\" onclick='\$(\".";
                echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
                echo "resourceAddSlider\").load(\"";
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/modules/Planner/resources_add_ajax.php?alpha=";
                echo twig_escape_filter($this->env, ($context["resourceAlphaSort"] ?? null), "html", null, true);
                echo "&";
                echo twig_escape_filter($this->env, ($context["initialFilter"] ?? null), "html", null, true);
                echo "\",\"id=";
                echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
                echo "&allowUpload=";
                echo twig_escape_filter($this->env, ($context["allowUpload"] ?? null), "html", null, true);
                echo "\");'>
                    <img src=\"";
                // line 56
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/themes/Default/img/upload_mini.png\" alt=\"";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Create & Insert New Resource")), "html", null, true);
                echo "\" onclick=\"return false;\" />
                </a>
                ";
            }
            // line 59
            echo "            </div>
            
            ";
            // line 61
            if (($context["allowUpload"] ?? null)) {
                // line 62
                echo "            <div class=\"mr-2 flex items-center\">
                <span>";
                // line 63
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Quick File Upload")), "html", null, true);
                echo ":</span> 

                <a title=\"";
                // line 65
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Quick Add")), "html", null, true);
                echo "\" class=\"";
                echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
                echo "show_hideQuickAdd mx-1\" onclick='\$(\".";
                echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
                echo "resourceQuickSlider\").load(\"";
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/modules/Planner/resources_addQuick_ajax.php?alpha=";
                echo twig_escape_filter($this->env, ($context["resourceAlphaSort"] ?? null), "html", null, true);
                echo "&";
                echo twig_escape_filter($this->env, ($context["initialFilter"] ?? null), "html", null, true);
                echo "\",\"id=";
                echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
                echo "&allowUpload=";
                echo twig_escape_filter($this->env, ($context["allowUpload"] ?? null), "html", null, true);
                echo "\");'>
                    <img src=\"";
                // line 66
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/themes/Default/img/page_new_mini.png\" alt=\"";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Quick Add")), "html", null, true);
                echo "\" onclick=\"return false;\" />
                </a>
            </div>
            ";
            }
            // line 70
            echo "        </div>
    </div>
    ";
        }
        // line 73
        echo "
    <div class=\"editor-tabs flex flex-grow justify-end items-end\">
        <a id=\"";
        // line 75
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "edButtonPreview\" class=\"active hide-if-no-js block cursor-pointer bg-gray text-gray border border-b-0 rounded-t px-4 pt-2 pb-1 mx-1 font-bold z-10\">
            ";
        // line 76
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Visual")), "html", null, true);
        echo "
        </a>
        <a id=\"";
        // line 78
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "edButtonHTML\" class=\"hide-if-no-js block cursor-pointer bg-gray text-gray border border-b-0 rounded-t px-4 pt-2 pb-1 mx-1 font-bold z-10\">
            HTML
        </a>
    </div>
</div>


";
        // line 85
        if (($context["showMedia"] ?? null)) {
            // line 86
            echo "    ";
            // line 87
            echo "    <div class=\"";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceSlider hidden w-full\">
        <div class=\"w-full text-center h-20 p-6\">
            <img src=\"";
            // line 89
            echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
            echo "/themes/Default/img/loading.gif\" alt=\"";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Loading")), "html", null, true);
            echo "\" onclick=\"return false;\" /><br/>
            ";
            // line 90
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Loading")), "html", null, true);
            echo "
        </div>
    </div>
";
        }
        // line 94
        echo "
";
        // line 95
        if ((($context["showMedia"] ?? null) && ($context["allowUpload"] ?? null))) {
            // line 96
            echo "    ";
            // line 97
            echo "    <div class=\"";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceQuickSlider hidden w-full\">
        <div class=\"w-full text-center h-20 p-6\">
            <img src=\"";
            // line 99
            echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
            echo "/themes/Default/img/loading.gif\" alt=\"";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Loading")), "html", null, true);
            echo "\" onclick=\"return false;\" /><br/>
            ";
            // line 100
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Loading")), "html", null, true);
            echo "
        </div>
    </div>

    ";
            // line 105
            echo "    <div class=\"";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "resourceAddSlider hidden w-full\">
        <div class=\"w-full text-center h-20 p-6\">
            <img src=\"";
            // line 107
            echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
            echo "/themes/Default/img/loading.gif\" alt=\"";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Loading")), "html", null, true);
            echo "\" onclick=\"return false;\" /><br/>
            ";
            // line 108
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Loading")), "html", null, true);
            echo "
        </div>
    </div>
";
        }
        // line 112
        echo "

<div id=\"editorcontainer\" class=\"relative\">
    <textarea class=\"tinymce w-full ml-0 float-none focus:shadow-none focus:border-gray\" name=\"";
        // line 115
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "\" id=\"";
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "\" style=\"height: ";
        echo twig_escape_filter($this->env, (($context["rows"] ?? null) * 18), "html", null, true);
        echo "px;\">";
        // line 116
        echo twig_escape_filter($this->env, ($context["value"] ?? null), "html", null, true);
        // line 117
        echo "</textarea>

    ";
        // line 119
        if (($context["required"] ?? null)) {
            // line 120
            echo "        <script type=\"text/javascript\">
        var ";
            // line 121
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo " = new LiveValidation('";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "');
        ";
            // line 122
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo ".add(Validate.Presence, { tinymce: true, tinymceField: '";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "'});
        ";
            // line 123
            if (($context["initiallyHidden"] ?? null)) {
                // line 124
                echo "            ";
                echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
                echo ".disable();
        ";
            }
            // line 126
            echo "        </script>
    ";
        }
        // line 128
        echo "</div>

<script type=\"text/javascript\">
\$(document).ready(function(){
    ";
        // line 132
        if (($context["tinymceInit"] ?? null)) {
            // line 133
            echo "        tinyMCE.execCommand('mceAddControl', false, '";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "');
    ";
        }
        // line 135
        echo "
    \$('#";
        // line 136
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "edButtonPreview').addClass('active') ;
    \$('#";
        // line 137
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "edButtonHTML').click(function(){
        tinyMCE.execCommand('mceRemoveEditor', false, '";
        // line 138
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "');
        \$('#";
        // line 139
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "edButtonHTML').addClass('active') ;
        \$('#";
        // line 140
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "edButtonPreview').removeClass('active') ;
        \$(\".";
        // line 141
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "resourceSlider\").hide();
        \$(\"#";
        // line 142
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "mediaInner\").hide();
        ";
        // line 143
        if (($context["required"] ?? null)) {
            // line 144
            echo "            ";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo ".destroy();
            \$('.LV_validation_message').css('display','none');
            ";
            // line 146
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "=new LiveValidation('";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "');
            ";
            // line 147
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo ".add(Validate.Presence);
        ";
        }
        // line 149
        echo "    }) ;
    \$('#";
        // line 150
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "edButtonPreview').click(function(){
        tinyMCE.execCommand('mceAddEditor', false, '";
        // line 151
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "');
        \$('#";
        // line 152
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "edButtonPreview').addClass('active');
        \$('#";
        // line 153
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "edButtonHTML').removeClass('active'); 
        \$(\"#";
        // line 154
        echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "mediaInner\").show();
        ";
        // line 155
        if (($context["required"] ?? null)) {
            // line 156
            echo "            ";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo ".destroy();
            \$('.LV_validation_message').css('display','none');
            ";
            // line 158
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "=new LiveValidation('";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "');
            ";
            // line 159
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo ".add(Validate.Presence, { tinymce: true, tinymceField: '";
            echo twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
            echo "'});
        ";
        }
        // line 161
        echo "    });
});
</script>
";
    }

    public function getTemplateName()
    {
        return "components/editor.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  487 => 161,  480 => 159,  474 => 158,  468 => 156,  466 => 155,  462 => 154,  458 => 153,  454 => 152,  450 => 151,  446 => 150,  443 => 149,  438 => 147,  432 => 146,  426 => 144,  424 => 143,  420 => 142,  416 => 141,  412 => 140,  408 => 139,  404 => 138,  400 => 137,  396 => 136,  393 => 135,  387 => 133,  385 => 132,  379 => 128,  375 => 126,  369 => 124,  367 => 123,  361 => 122,  355 => 121,  352 => 120,  350 => 119,  346 => 117,  344 => 116,  337 => 115,  332 => 112,  325 => 108,  319 => 107,  313 => 105,  306 => 100,  300 => 99,  294 => 97,  292 => 96,  290 => 95,  287 => 94,  280 => 90,  274 => 89,  268 => 87,  266 => 86,  264 => 85,  254 => 78,  249 => 76,  245 => 75,  241 => 73,  236 => 70,  227 => 66,  209 => 65,  204 => 63,  201 => 62,  199 => 61,  195 => 59,  187 => 56,  168 => 55,  166 => 54,  158 => 51,  140 => 50,  135 => 48,  125 => 41,  121 => 40,  115 => 39,  111 => 38,  107 => 37,  101 => 34,  97 => 33,  91 => 32,  87 => 31,  83 => 30,  77 => 27,  73 => 26,  67 => 25,  63 => 24,  59 => 23,  51 => 22,  45 => 19,  40 => 18,  38 => 17,  31 => 13,  28 => 12,  26 => 11,  23 => 10,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/

Rich Text Editor
-->#}

{% set resourceAlphaSort = resourceAlphaSort ? 'true' : 'false' %}

<a name=\"{{ id }}editor\"></a>

<div class=\"editor-toolbar flex flex-wrap sm:flex-no-wrap justify-between text-xs\">

    {% if showMedia %}
    <div id=\"{{ id }}mediaOuter\" class=\"h-6\">
        <div id=\"{{ id }}mediaInner\" class=\"flex items-center py-1\">
            <script type=\"text/javascript\">
            \$(document).ready(function(){
                \$(\".{{ id }}resourceSlider, .{{ id }}resourceAddSlider, .{{ id }}resourceQuickSlider\").hide();
                \$(\".{{ id }}show_hide\").unbind('click').click(function(){
                    \$(\".{{ id }}resourceSlider\").slideToggle();
                    \$(\".{{ id }}resourceAddSlider, .{{ id }}resourceQuickSlider\").hide();
                    if (tinyMCE.get('{{ id }}').selection.getRng().startOffset < 1) {
                        tinyMCE.get('{{ id }}').focus();
                    }
                });
                \$(\".{{ id }}show_hideAdd\").unbind('click').click(function(){
                    \$(\".{{ id }}resourceAddSlider\").slideToggle();
                    \$(\".{{ id }}resourceSlider, .{{ id }}resourceQuickSlider\").hide();
                    if (tinyMCE.get('{{ id }}').selection.getRng().startOffset < 1) {
                        tinyMCE.get('{{ id }}').focus();
                    }
                });
                \$(\".{{ id }}show_hideQuickAdd\").unbind('click').click(function(){
                \$(\".{{ id }}resourceQuickSlider\").slideToggle();
                \$(\".{{ id }}resourceSlider, .{{ id }}resourceAddSlider\").hide();
                if (tinyMCE.get('{{ id }}').selection.getRng().startOffset < 1) {
                    tinyMCE.get('{{ id }}').focus();
                }
                });
            });
            </script>

            <div class=\"mr-2 flex items-center\">
                <span>{{ __('Shared Resources') }}:</span> 
        
                <a title=\"{{ __('Insert Existing Resource') }}\" class=\"{{ id }}show_hide mx-1\" onclick='\$(\".{{ id }}resourceSlider\").load(\"{{ absoluteURL }}/modules/Planner/resources_insert_ajax.php?alpha={{ resourceAlphaSort }}&{{ initialFilter }}\",\"id={{ id }}&allowUpload={{ allowUpload }}\");'>
                    <img src=\"{{ absoluteURL  }}/themes/Default/img/search_mini.png\" alt=\"{{ __('Insert Existing Resource') }}\" onclick=\"return false;\" />
                </a>
            
                {% if allowUpload %}
                <a title=\"{{ __('Create & Insert New Resource') }}\" class=\"{{ id }}show_hideAdd mx-1\" onclick='\$(\".{{ id }}resourceAddSlider\").load(\"{{ absoluteURL }}/modules/Planner/resources_add_ajax.php?alpha={{ resourceAlphaSort }}&{{ initialFilter }}\",\"id={{ id }}&allowUpload={{ allowUpload }}\");'>
                    <img src=\"{{ absoluteURL  }}/themes/Default/img/upload_mini.png\" alt=\"{{ __('Create & Insert New Resource') }}\" onclick=\"return false;\" />
                </a>
                {% endif %}
            </div>
            
            {% if allowUpload %}
            <div class=\"mr-2 flex items-center\">
                <span>{{ __('Quick File Upload') }}:</span> 

                <a title=\"{{ __('Quick Add') }}\" class=\"{{ id }}show_hideQuickAdd mx-1\" onclick='\$(\".{{ id }}resourceQuickSlider\").load(\"{{ absoluteURL }}/modules/Planner/resources_addQuick_ajax.php?alpha={{ resourceAlphaSort }}&{{ initialFilter }}\",\"id={{ id }}&allowUpload={{ allowUpload }}\");'>
                    <img src=\"{{ absoluteURL  }}/themes/Default/img/page_new_mini.png\" alt=\"{{ __('Quick Add') }}\" onclick=\"return false;\" />
                </a>
            </div>
            {% endif %}
        </div>
    </div>
    {% endif %}

    <div class=\"editor-tabs flex flex-grow justify-end items-end\">
        <a id=\"{{ id }}edButtonPreview\" class=\"active hide-if-no-js block cursor-pointer bg-gray text-gray border border-b-0 rounded-t px-4 pt-2 pb-1 mx-1 font-bold z-10\">
            {{ __('Visual') }}
        </a>
        <a id=\"{{ id }}edButtonHTML\" class=\"hide-if-no-js block cursor-pointer bg-gray text-gray border border-b-0 rounded-t px-4 pt-2 pb-1 mx-1 font-bold z-10\">
            HTML
        </a>
    </div>
</div>


{% if showMedia %}
    {## Define: Insert Existing Resource ##}
    <div class=\"{{ id }}resourceSlider hidden w-full\">
        <div class=\"w-full text-center h-20 p-6\">
            <img src=\"{{ absoluteURL  }}/themes/Default/img/loading.gif\" alt=\"{{ __('Loading') }}\" onclick=\"return false;\" /><br/>
            {{ __('Loading') }}
        </div>
    </div>
{% endif %}

{% if showMedia and allowUpload %}
    {## Define: Quick File Upload ##}
    <div class=\"{{ id }}resourceQuickSlider hidden w-full\">
        <div class=\"w-full text-center h-20 p-6\">
            <img src=\"{{ absoluteURL  }}/themes/Default/img/loading.gif\" alt=\"{{ __('Loading') }}\" onclick=\"return false;\" /><br/>
            {{ __('Loading') }}
        </div>
    </div>

    {## Define: Create & Insert New Resource ##}
    <div class=\"{{ id }}resourceAddSlider hidden w-full\">
        <div class=\"w-full text-center h-20 p-6\">
            <img src=\"{{ absoluteURL  }}/themes/Default/img/loading.gif\" alt=\"{{ __('Loading') }}\" onclick=\"return false;\" /><br/>
            {{ __('Loading') }}
        </div>
    </div>
{% endif %}


<div id=\"editorcontainer\" class=\"relative\">
    <textarea class=\"tinymce w-full ml-0 float-none focus:shadow-none focus:border-gray\" name=\"{{ id }}\" id=\"{{ id }}\" style=\"height: {{ rows * 18 }}px;\">
        {{- value -}}
    </textarea>

    {% if required %}
        <script type=\"text/javascript\">
        var {{ id }} = new LiveValidation('{{ id }}');
        {{ id }}.add(Validate.Presence, { tinymce: true, tinymceField: '{{ id }}'});
        {% if initiallyHidden %}
            {{ id }}.disable();
        {% endif %}
        </script>
    {% endif %}
</div>

<script type=\"text/javascript\">
\$(document).ready(function(){
    {% if tinymceInit %}
        tinyMCE.execCommand('mceAddControl', false, '{{ id }}');
    {% endif %}

    \$('#{{ id }}edButtonPreview').addClass('active') ;
    \$('#{{ id }}edButtonHTML').click(function(){
        tinyMCE.execCommand('mceRemoveEditor', false, '{{ id }}');
        \$('#{{ id }}edButtonHTML').addClass('active') ;
        \$('#{{ id }}edButtonPreview').removeClass('active') ;
        \$(\".{{ id }}resourceSlider\").hide();
        \$(\"#{{ id }}mediaInner\").hide();
        {% if required %}
            {{ id }}.destroy();
            \$('.LV_validation_message').css('display','none');
            {{ id }}=new LiveValidation('{{ id }}');
            {{ id }}.add(Validate.Presence);
        {% endif %}
    }) ;
    \$('#{{ id }}edButtonPreview').click(function(){
        tinyMCE.execCommand('mceAddEditor', false, '{{ id }}');
        \$('#{{ id }}edButtonPreview').addClass('active');
        \$('#{{ id }}edButtonHTML').removeClass('active'); 
        \$(\"#{{ id }}mediaInner\").show();
        {% if required %}
            {{ id }}.destroy();
            \$('.LV_validation_message').css('display','none');
            {{ id }}=new LiveValidation('{{ id }}');
            {{ id }}.add(Validate.Presence, { tinymce: true, tinymceField: '{{ id }}'});
        {% endif %}
    });
});
</script>
", "components/editor.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\components\\editor.twig.html");
    }
}
