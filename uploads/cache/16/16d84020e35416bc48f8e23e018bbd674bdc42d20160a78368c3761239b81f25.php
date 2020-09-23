<?php

/* navigation.twig.html */
class __TwigTemplate_4495a0af3ce1a0af6f85299d578f502ce9a8044fe4f36b782751da33d9c27770 extends Twig_Template
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
        // line 1
        if (($context["menuModule"] ?? null)) {
            // line 2
            echo "<div class=\"navbar-expand-md\">
    <div class=\"collapse navbar-collapse\" id=\"navbar-menu\">
        <div class=\"navbar navbar-light\">
            <div class=\"container-fluid\">
                <!--
<div class='position-absolute' style='right:0;top:0;'>
                -->
                <div>
                    <ul class=\"navbar-nav\">
                        ";
            // line 11
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["menuModule"] ?? null));
            foreach ($context['_seq'] as $context["categoryName"] => $context["items"]) {
                echo " ";
                if ($context["categoryName"]) {
                    // line 12
                    echo "                        ";
                    if (((((((($context["categoryName"] == "Manage") || ($context["categoryName"] == "Payment")) || ($context["categoryName"] == "Structure")) || ($context["categoryName"] == "Route")) || ($context["categoryName"] == "Bus")) || ($context["categoryName"] == "Manage Curriculum")) || ($context["categoryName"] == "Examination"))) {
                        // line 13
                        echo "                        ";
                    } else {
                        // line 14
                        echo "

                        <li class=\"nav-item dropdown\">
                            <a class=\"nav-link dropdown-toggle\" href=\"#navbar-base\" data-toggle=\"dropdown\" role=\"button\"
                                aria-expanded=\"false\">
                                <span class=\"nav-link-title\">
                                    ";
                        // line 20
                        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array($context["categoryName"], twig_get_attribute($this->env, $this->source, twig_first($this->env, $context["items"]), "textDomain", array()))), "html", null, true);
                        echo "
                                </span>
                            </a>

                            <ul class=\"dropdown-menu\">
                                ";
                        // line 25
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable($context["items"]);
                        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                            // line 26
                            echo "                                <li>
                                    <a class=\"dropdown-item\" href=\"";
                            // line 27
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                            echo "\">
                                        ";
                            // line 28
                            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), twig_get_attribute($this->env, $this->source, $context["item"], "textDomain", array()))), "html", null, true);
                            echo "
                                    </a>
                                </li>
                                ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 32
                        echo "                            </ul>

                        </li>

                        ";
                    }
                    // line 36
                    echo " ";
                }
                echo " ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['categoryName'], $context['items'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 37
            echo "                    </ul>
                    
                    <!-- Sidebar -->
                    ";
            // line 40
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "extraSidebar", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["code"]) {
                if ((($context["sidebar"] ?? null) && (($context["sidebarPosition"] ?? null) != "bottom"))) {
                    echo " ";
                    if ( !twig_test_empty($context["code"])) {
                        // line 41
                        echo "                    <div class=\"md:column-2 lg:column-1 pt-6 sm:pt-16 lg:pt-6\">
                        ";
                        // line 42
                        echo $context["code"];
                        echo "
                    </div>
                    ";
                    }
                    // line 44
                    echo " ";
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['code'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            echo " ";
            if ((($context["sidebar"] ?? null) && ($context["sidebarContents"] ?? null))) {
                // line 45
                echo "                    <div class=\"md:column-2 lg:column-1 ";
                echo ((twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumbs", array())) ? ("pt-10 lg:pt-0") : (""));
                echo " \">
                        ";
                // line 46
                echo ($context["sidebarContents"] ?? null);
                echo "
                    </div>
                
                    ";
            }
            // line 49
            echo "  ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "extraSidebar", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["code"]) {
                if ((($context["sidebar"] ?? null) && (($context["sidebarPosition"] ?? null) == "bottom"))) {
                    // line 50
                    echo "                    <div class=\"md:column-2 lg:column-1 pt-6 sm:pt-16 lg:pt-0\">
                        ";
                    // line 51
                    echo $context["code"];
                    echo "
                    </div>
                    ";
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['code'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 54
            echo "                    <!-- Sidebar -->
                </div>
                
            </div>
        </div>
    </div>
</div>
";
        }
    }

    public function getTemplateName()
    {
        return "navigation.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  161 => 54,  151 => 51,  148 => 50,  142 => 49,  135 => 46,  130 => 45,  121 => 44,  115 => 42,  112 => 41,  105 => 40,  100 => 37,  92 => 36,  85 => 32,  75 => 28,  71 => 27,  68 => 26,  64 => 25,  56 => 20,  48 => 14,  45 => 13,  42 => 12,  36 => 11,  25 => 2,  23 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{% if menuModule %}
<div class=\"navbar-expand-md\">
    <div class=\"collapse navbar-collapse\" id=\"navbar-menu\">
        <div class=\"navbar navbar-light\">
            <div class=\"container-fluid\">
                <!--
<div class='position-absolute' style='right:0;top:0;'>
                -->
                <div>
                    <ul class=\"navbar-nav\">
                        {% for categoryName, items in menuModule %} {% if categoryName %}
                        {% if categoryName == 'Manage' or categoryName == 'Payment' or categoryName == 'Structure' or categoryName == 'Route' or categoryName == 'Bus' or categoryName == 'Manage Curriculum' or categoryName == 'Examination' %}
                        {% else %}


                        <li class=\"nav-item dropdown\">
                            <a class=\"nav-link dropdown-toggle\" href=\"#navbar-base\" data-toggle=\"dropdown\" role=\"button\"
                                aria-expanded=\"false\">
                                <span class=\"nav-link-title\">
                                    {{ __(categoryName, (items|first).textDomain) }}
                                </span>
                            </a>

                            <ul class=\"dropdown-menu\">
                                {% for item in items %}
                                <li>
                                    <a class=\"dropdown-item\" href=\"{{ item.url }}\">
                                        {{ __(item.name, item.textDomain) }}
                                    </a>
                                </li>
                                {% endfor %}
                            </ul>

                        </li>

                        {% endif %} {% endif %} {% endfor %}
                    </ul>
                    
                    <!-- Sidebar -->
                    {% for code in page.extraSidebar if sidebar and sidebarPosition != 'bottom' %} {% if code is not empty %}
                    <div class=\"md:column-2 lg:column-1 pt-6 sm:pt-16 lg:pt-6\">
                        {{ code|raw }}
                    </div>
                    {% endif %} {% endfor %} {% if sidebar and sidebarContents %}
                    <div class=\"md:column-2 lg:column-1 {{ page.breadcrumbs ? 'pt-10 lg:pt-0' }} \">
                        {{ sidebarContents|raw }}
                    </div>
                
                    {% endif %}  {% for code in page.extraSidebar if sidebar and sidebarPosition == 'bottom' %}
                    <div class=\"md:column-2 lg:column-1 pt-6 sm:pt-16 lg:pt-0\">
                        {{ code|raw }}
                    </div>
                    {% endfor %}
                    <!-- Sidebar -->
                </div>
                
            </div>
        </div>
    </div>
</div>
{% endif %}", "navigation.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\navigation.twig.html");
    }
}
