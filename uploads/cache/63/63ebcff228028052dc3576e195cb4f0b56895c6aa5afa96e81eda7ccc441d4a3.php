<?php

/* components/studentHistory.twig.html */
class __TwigTemplate_2ebb80ebc62743d7b0d130d94ab67e0c5d2087294d685e69c5e8ff12cbdeec58 extends Twig_Template
{
    private $source;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        // line 9
        $this->parent = $this->loadTemplate("components/dataTable.twig.html", "components/studentHistory.twig.html", 9);
        $this->blocks = array(
            'tableInner' => array($this, 'block_tableInner'),
            'footer' => array($this, 'block_footer'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "components/dataTable.twig.html";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 10
        $context["attendance"] = $this;
        // line 9
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 12
    public function block_tableInner($context, array $blocks = array())
    {
        // line 13
        echo "
    <div class=\"flex flex-wrap justify-center md:justify-between rounded bg-gray border\">
        <div class=\"md:flex-1 p-4 text-sm text-gray\">
            <h3 class=\"mt-2 border-b-0\">
                ";
        // line 17
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Summary")), "html", null, true);
        echo "
            </h3>

            ";
        // line 20
        if (twig_get_attribute($this->env, $this->source, ($context["summary"] ?? null), "total", array())) {
            // line 21
            echo "                ";
            if ((twig_get_attribute($this->env, $this->source, ($context["summary"] ?? null), "total", array()) != ((twig_get_attribute($this->env, $this->source, ($context["summary"] ?? null), "present", array()) + twig_get_attribute($this->env, $this->source, ($context["summary"] ?? null), "partial", array())) + twig_get_attribute($this->env, $this->source, ($context["summary"] ?? null), "absent", array())))) {
                // line 22
                echo "                    <div class=\"italic mb-4 text-xs\">
                    ";
                // line 23
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("It appears that this student is missing attendance data for some school days:")), "html", null, true);
                echo "
                    </div>
                ";
            }
            // line 26
            echo "
                <div class=\"leading-snug\">
                    <strong>";
            // line 28
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Total number of school days to date:")), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["summary"] ?? null), "total", array()), "html", null, true);
            echo "</strong><br/>
                    ";
            // line 29
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Total number of school days attended:")), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, (twig_get_attribute($this->env, $this->source, ($context["summary"] ?? null), "present", array()) + twig_get_attribute($this->env, $this->source, ($context["summary"] ?? null), "partial", array())), "html", null, true);
            echo "<br/>
                    ";
            // line 30
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Total number of school days absent:")), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["summary"] ?? null), "absent", array()), "html", null, true);
            echo "<br/>
                </div>
            ";
        }
        // line 33
        echo "        </div>

        ";
        // line 35
        if ( !($context["printView"] ?? null)) {
            // line 36
            echo "        <div class=\" p-4\">
            ";
            // line 37
            echo ($context["chart"] ?? null);
            echo "
        </div>
        ";
        }
        // line 40
        echo "    </div>


    <div id=\"studentHistory\">
    ";
        // line 44
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["dataSet"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["term"]) {
            // line 45
            echo "        <h4>
        ";
            // line 46
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["term"], "name", array()), "html", null, true);
            echo "
        </h4>

        ";
            // line 49
            $context["daysOfWeek"] = twig_first($this->env, twig_get_attribute($this->env, $this->source, $context["term"], "weeks", array()));
            // line 50
            echo "        ";
            $context["blockWidth"] = ("w-1/" . twig_length_filter($this->env, ($context["daysOfWeek"] ?? null)));
            // line 51
            echo "        ";
            $context["dayClass"] = "flex flex-col justify-center border-t border-b border-r py-2 px-1 -mt-px ";
            // line 52
            echo "
        <div class=\"flex flex-wrap border-t border-l border-gray\">

            ";
            // line 56
            echo "            <div class=\"w-full flex items-stretch text-xs text-center text-gray font-bold bg-gray border-b border-r border-gray\">
                ";
            // line 57
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["daysOfWeek"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["day"]) {
                // line 58
                echo "                    <div class=\"";
                echo twig_escape_filter($this->env, ($context["blockWidth"] ?? null), "html", null, true);
                echo " py-1\" title=\"";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, $context["day"], "name", array()))), "html", null, true);
                echo "\">
                        ";
                // line 59
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, $context["day"], "nameShort", array()))), "html", null, true);
                echo "
                    </div>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['day'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 62
            echo "
                ";
            // line 63
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["daysOfWeek"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["day"]) {
                // line 64
                echo "                    <div class=\"hidden md:block ";
                echo twig_escape_filter($this->env, ($context["blockWidth"] ?? null), "html", null, true);
                echo " py-1\" title=\"";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, $context["day"], "name", array()))), "html", null, true);
                echo "\">
                        ";
                // line 65
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, $context["day"], "nameShort", array()))), "html", null, true);
                echo "
                    </div>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['day'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 68
            echo "            </div>

            ";
            // line 71
            echo "            ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["term"], "weeks", array()));
            foreach ($context['_seq'] as $context["weekNumber"] => $context["week"]) {
                // line 72
                echo "                <div class=\"w-full md:w-1/2 flex items-stretch text-xxs text-center text-gray\" style=\"min-height: 55px;\">

                ";
                // line 74
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($context["week"]);
                foreach ($context['_seq'] as $context["_key"] => $context["day"]) {
                    // line 75
                    echo "                    ";
                    if (twig_get_attribute($this->env, $this->source, $context["day"], "outsideTerm", array())) {
                        // line 76
                        echo "                        <div class=\"";
                        echo twig_escape_filter($this->env, ($context["blockWidth"] ?? null), "html", null, true);
                        echo " ";
                        echo twig_escape_filter($this->env, ($context["dayClass"] ?? null), "html", null, true);
                        echo " bg-gray border-gray text-gray\">
                        </div>
                    ";
                    } elseif (twig_get_attribute($this->env, $this->source,                     // line 78
$context["day"], "beforeStartDate", array())) {
                        // line 79
                        echo "                        <div class=\"";
                        echo twig_escape_filter($this->env, ($context["blockWidth"] ?? null), "html", null, true);
                        echo " ";
                        echo twig_escape_filter($this->env, ($context["dayClass"] ?? null), "html", null, true);
                        echo " bg-gray border-gray text-gray\" title=\"";
                        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Before Start Date")), "html", null, true);
                        echo "\">
                            ";
                        // line 80
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["day"], "dateDisplay", array()), "html", null, true);
                        echo "<br/>
                            ";
                        // line 81
                        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Before Start Date")), "html", null, true);
                        echo "
                        </div>
                    ";
                    } elseif (twig_get_attribute($this->env, $this->source,                     // line 83
$context["day"], "afterEndDate", array())) {
                        // line 84
                        echo "                        <div class=\"";
                        echo twig_escape_filter($this->env, ($context["blockWidth"] ?? null), "html", null, true);
                        echo " ";
                        echo twig_escape_filter($this->env, ($context["dayClass"] ?? null), "html", null, true);
                        echo " bg-gray border-gray text-gray\" title=\"";
                        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("After End Date")), "html", null, true);
                        echo "\">
                            ";
                        // line 85
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["day"], "dateDisplay", array()), "html", null, true);
                        echo "<br/>
                            ";
                        // line 86
                        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("After End Date")), "html", null, true);
                        echo "
                        </div>
                    ";
                    } elseif (twig_get_attribute($this->env, $this->source,                     // line 88
$context["day"], "specialDay", array())) {
                        // line 89
                        echo "                        <div class=\"";
                        echo twig_escape_filter($this->env, ($context["blockWidth"] ?? null), "html", null, true);
                        echo " ";
                        echo twig_escape_filter($this->env, ($context["dayClass"] ?? null), "html", null, true);
                        echo " bg-gray border-gray text-gray\" title=\"";
                        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("School Closed")), "html", null, true);
                        echo "\">
                            ";
                        // line 90
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["day"], "specialDay", array()), "html", null, true);
                        echo "
                        </div>
                    ";
                    } elseif (twig_test_empty(twig_get_attribute($this->env, $this->source,                     // line 92
$context["day"], "logs", array()))) {
                        // line 93
                        echo "                        <div class=\"";
                        echo twig_escape_filter($this->env, ($context["blockWidth"] ?? null), "html", null, true);
                        echo " ";
                        echo twig_escape_filter($this->env, ($context["dayClass"] ?? null), "html", null, true);
                        echo " bg-gray border-gray text-gray\" title=\"";
                        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("No Data")), "html", null, true);
                        echo "\">
                            ";
                        // line 94
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["day"], "dateDisplay", array()), "html", null, true);
                        echo "
                        </div>
                    ";
                    } else {
                        // line 97
                        echo "                        <a class=\"";
                        echo twig_escape_filter($this->env, ($context["blockWidth"] ?? null), "html", null, true);
                        echo " ";
                        echo twig_escape_filter($this->env, ($context["dayClass"] ?? null), "html", null, true);
                        echo " ";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, $context["day"], "endOfDay", array()), "statusClass", array()), "html", null, true);
                        echo " relative z-10\" data-log=\"";
                        echo $context["attendance"]->macro_tooltip($context["day"]);
                        echo "\"
                            ";
                        // line 98
                        if (($context["canTakeAttendanceByPerson"] ?? null)) {
                            // line 99
                            echo "                                href=\"";
                            echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                            echo "/index.php?q=/modules/Attendance/attendance_take_byPerson.php&pupilsightPersonID=";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["day"], "pupilsightPersonID", array()), "html", null, true);
                            echo "&currentDate=";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["day"], "date", array()), "html", null, true);
                            echo "\"
                            ";
                        }
                        // line 100
                        echo ">

                            ";
                        // line 102
                        echo $context["attendance"]->macro_badge($context["day"]);
                        echo "

                            <span>";
                        // line 104
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["day"], "dateDisplay", array()), "html", null, true);
                        echo "</span>
                            <span class=\"mt-1 font-bold\">";
                        // line 105
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, $context["day"], "endOfDay", array()), "type", array()), "html", null, true);
                        echo "</span>

                            ";
                        // line 107
                        if (($context["printView"] ?? null)) {
                            // line 108
                            echo "                                <span class=\"mt-1\">
                                ";
                            // line 109
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["day"], "logs", array()));
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
                            foreach ($context['_seq'] as $context["_key"] => $context["log"]) {
                                // line 110
                                echo "                                    ";
                                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["log"], "code", array()), "html", null, true);
                                // line 111
                                echo (( !twig_get_attribute($this->env, $this->source, $context["loop"], "last", array())) ? (" : ") : (""));
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
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['log'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 113
                            echo "                                </span>
                            ";
                        }
                        // line 115
                        echo "                        </a>
                    ";
                    }
                    // line 117
                    echo "                ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['day'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 118
                echo "            </div>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['weekNumber'], $context['week'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 120
            echo "        </div>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['term'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 122
        echo "    </div>


";
    }

    // line 172
    public function block_footer($context, array $blocks = array())
    {
        // line 173
        echo "

";
        // line 179
        echo "<style>
    .tooltip-reset {
        background: #ffffff !important;
        min-width: 16rem;
    }
    .w-1\\/7 {
        width: 14.28%;
    }
</style>
<script>
\$('#studentHistory').tooltip({
    items: \"a[data-log]\",
    show: 800,
    hide: false,
    content: function () {
        return \$(this).data('log');
    },
    tooltipClass: \"tooltip-reset\",
    position: {
        my: \"center bottom-5\",
        at: \"center top\",
        using: function (position, feedback) {
            \$(this).css(position);
            \$(\"<div>\").
                addClass(\"arrow\").
                addClass(feedback.vertical).
                addClass(feedback.horizontal).
                appendTo(this);
        }
    }
});
</script>

";
    }

    // line 131
    public function macro_tooltip($__day__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "day" => $__day__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 132
            echo "    <section class='-mx-2 p-4 border text-center ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "endOfDay", array()), "statusClass", array()), "html", null, true);
            echo "'>
        ";
            // line 133
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "dateDisplay", array()), "html", null, true);
            echo "<br/>
        
        <span class='font-bold text-base leading-normal'>";
            // line 135
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "endOfDay", array()), "type", array()))), "html", null, true);
            echo "</span><br/>

        ";
            // line 137
            if (twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "endOfDay", array()), "reason", array())) {
                // line 138
                echo "            <span class='mt-1 text-xs'>";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "endOfDay", array()), "reason", array()))), "html", null, true);
                echo "</span><br/>
        ";
            }
            // line 140
            echo "
        <ul class='list-none ml-0 mt-4 text-xs text-left'>
        ";
            // line 142
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "logs", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["log"]) {
                // line 143
                echo "            <li class='";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["log"], "statusClass", array()), "html", null, true);
                echo " leading-relaxed'>
                ";
                // line 144
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, $context["log"], "timestampTaken", array()), (((twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, $context["log"], "timestampTaken", array()), "Y-m-d") == twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "date", array()))) ? ("H:i") : ("H:i Y-m-d"))), "html", null, true);
                echo " -
                ";
                // line 145
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, $context["log"], "type", array()))), "html", null, true);
                echo twig_escape_filter($this->env, ((twig_get_attribute($this->env, $this->source, $context["log"], "reason", array())) ? ((", " . call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, $context["log"], "reason", array()))))) : ("")), "html", null, true);
                echo " - 
                ";
                // line 146
                echo twig_escape_filter($this->env, ((twig_get_attribute($this->env, $this->source, $context["log"], "contextName", array())) ? (twig_get_attribute($this->env, $this->source, $context["log"], "contextName", array())) : (call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, $context["log"], "context", array()))))), "html", null, true);
                echo "
            </li>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['log'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 149
            echo "    </section>
";

            return ('' === $tmp = ob_get_contents()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        } finally {
            ob_end_clean();
        }
    }

    // line 157
    public function macro_badge($__day__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "day" => $__day__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 158
            echo "    ";
            if ((((twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "absentCount", array()) > 0) || (twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "partialCount", array()) > 0)) && twig_in_filter("Present", twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "endOfDay", array()), "type", array())))) {
                // line 159
                echo "    <div class=\"absolute top-0 right-0 mt-1 mr-1 z-10 rounded-full bg-gray text-white no-underline leading-tight font-sans\" style=\"padding: 1px 3px; font-size: 8px\">
        ";
                // line 160
                echo twig_escape_filter($this->env, (twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "absentCount", array()) + twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "partialCount", array())), "html", null, true);
                echo "
    </div>
    ";
            }
            // line 163
            echo "
    ";
            // line 164
            if ((((twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "presentCount", array()) > 0) || (twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "partialCount", array()) > 0)) && twig_in_filter("Absent", twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "endOfDay", array()), "type", array())))) {
                // line 165
                echo "    <div class=\"absolute top-0 right-0 mt-1 mr-1 z-10 rounded-full bg-gray text-white no-underline leading-tight font-sans\" style=\"padding: 1px 3px; font-size: 8px\">
        ";
                // line 166
                echo twig_escape_filter($this->env, (twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "presentCount", array()) + twig_get_attribute($this->env, $this->source, ($context["day"] ?? null), "partialCount", array())), "html", null, true);
                echo "
    </div>
    ";
            }

            return ('' === $tmp = ob_get_contents()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        } finally {
            ob_end_clean();
        }
    }

    public function getTemplateName()
    {
        return "components/studentHistory.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  550 => 166,  547 => 165,  545 => 164,  542 => 163,  536 => 160,  533 => 159,  530 => 158,  518 => 157,  508 => 149,  499 => 146,  494 => 145,  490 => 144,  485 => 143,  481 => 142,  477 => 140,  471 => 138,  469 => 137,  464 => 135,  459 => 133,  454 => 132,  442 => 131,  405 => 179,  401 => 173,  398 => 172,  391 => 122,  384 => 120,  377 => 118,  371 => 117,  367 => 115,  363 => 113,  349 => 111,  346 => 110,  329 => 109,  326 => 108,  324 => 107,  319 => 105,  315 => 104,  310 => 102,  306 => 100,  296 => 99,  294 => 98,  283 => 97,  277 => 94,  268 => 93,  266 => 92,  261 => 90,  252 => 89,  250 => 88,  245 => 86,  241 => 85,  232 => 84,  230 => 83,  225 => 81,  221 => 80,  212 => 79,  210 => 78,  202 => 76,  199 => 75,  195 => 74,  191 => 72,  186 => 71,  182 => 68,  173 => 65,  166 => 64,  162 => 63,  159 => 62,  150 => 59,  143 => 58,  139 => 57,  136 => 56,  131 => 52,  128 => 51,  125 => 50,  123 => 49,  117 => 46,  114 => 45,  110 => 44,  104 => 40,  98 => 37,  95 => 36,  93 => 35,  89 => 33,  81 => 30,  75 => 29,  69 => 28,  65 => 26,  59 => 23,  56 => 22,  53 => 21,  51 => 20,  45 => 17,  39 => 13,  36 => 12,  32 => 9,  30 => 10,  15 => 9,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/
-->#}

{% extends \"components/dataTable.twig.html\" %}
{% import _self as attendance  %}

{% block tableInner %}

    <div class=\"flex flex-wrap justify-center md:justify-between rounded bg-gray border\">
        <div class=\"md:flex-1 p-4 text-sm text-gray\">
            <h3 class=\"mt-2 border-b-0\">
                {{ __('Summary') }}
            </h3>

            {% if summary.total %}
                {% if summary.total != (summary.present + summary.partial + summary.absent) %}
                    <div class=\"italic mb-4 text-xs\">
                    {{ __('It appears that this student is missing attendance data for some school days:') }}
                    </div>
                {% endif %}

                <div class=\"leading-snug\">
                    <strong>{{ __('Total number of school days to date:') }} {{ summary.total }}</strong><br/>
                    {{ __('Total number of school days attended:') }} {{ summary.present + summary.partial }}<br/>
                    {{ __('Total number of school days absent:') }} {{ summary.absent }}<br/>
                </div>
            {% endif %}
        </div>

        {% if not printView %}
        <div class=\" p-4\">
            {{ chart|raw }}
        </div>
        {% endif %}
    </div>


    <div id=\"studentHistory\">
    {% for term in dataSet %}
        <h4>
        {{ term.name }}
        </h4>

        {% set daysOfWeek = term.weeks|first %}
        {% set blockWidth = \"w-1/\" ~ daysOfWeek|length %}
        {% set dayClass = \"flex flex-col justify-center border-t border-b border-r py-2 px-1 -mt-px \" %}

        <div class=\"flex flex-wrap border-t border-l border-gray\">

            {#<!-- Days of the Week Header: only shows one week on mobile -->#}
            <div class=\"w-full flex items-stretch text-xs text-center text-gray font-bold bg-gray border-b border-r border-gray\">
                {% for day in daysOfWeek %}
                    <div class=\"{{ blockWidth }} py-1\" title=\"{{ __(day.name) }}\">
                        {{ __(day.nameShort) }}
                    </div>
                {% endfor %}

                {% for day in daysOfWeek %}
                    <div class=\"hidden md:block {{ blockWidth }} py-1\" title=\"{{ __(day.name) }}\">
                        {{ __(day.nameShort) }}
                    </div>
                {% endfor %}
            </div>

            {#<!-- Attendance Days: grouped by week -->#}
            {% for weekNumber, week in term.weeks %}
                <div class=\"w-full md:w-1/2 flex items-stretch text-xxs text-center text-gray\" style=\"min-height: 55px;\">

                {% for day in week %}
                    {% if day.outsideTerm %}
                        <div class=\"{{ blockWidth }} {{ dayClass }} bg-gray border-gray text-gray\">
                        </div>
                    {% elseif day.beforeStartDate %}
                        <div class=\"{{ blockWidth }} {{ dayClass }} bg-gray border-gray text-gray\" title=\"{{ __('Before Start Date') }}\">
                            {{ day.dateDisplay }}<br/>
                            {{ __('Before Start Date') }}
                        </div>
                    {% elseif day.afterEndDate %}
                        <div class=\"{{ blockWidth }} {{ dayClass }} bg-gray border-gray text-gray\" title=\"{{ __('After End Date') }}\">
                            {{ day.dateDisplay }}<br/>
                            {{ __('After End Date') }}
                        </div>
                    {% elseif day.specialDay %}
                        <div class=\"{{ blockWidth }} {{ dayClass }} bg-gray border-gray text-gray\" title=\"{{ __('School Closed') }}\">
                            {{ day.specialDay }}
                        </div>
                    {% elseif day.logs is empty %}
                        <div class=\"{{ blockWidth }} {{ dayClass }} bg-gray border-gray text-gray\" title=\"{{ __('No Data') }}\">
                            {{ day.dateDisplay }}
                        </div>
                    {% else %}
                        <a class=\"{{ blockWidth }} {{ dayClass }} {{ day.endOfDay.statusClass }} relative z-10\" data-log=\"{{ attendance.tooltip(day) }}\"
                            {% if canTakeAttendanceByPerson %}
                                href=\"{{ absoluteURL }}/index.php?q=/modules/Attendance/attendance_take_byPerson.php&pupilsightPersonID={{ day.pupilsightPersonID }}&currentDate={{ day.date }}\"
                            {% endif %}>

                            {{ attendance.badge(day) }}

                            <span>{{ day.dateDisplay }}</span>
                            <span class=\"mt-1 font-bold\">{{ day.endOfDay.type }}</span>

                            {% if printView %}
                                <span class=\"mt-1\">
                                {% for log in day.logs %}
                                    {{ log.code }}
                                    {{- not loop.last ? \" : \" -}}
                                {% endfor %}
                                </span>
                            {% endif %}
                        </a>
                    {% endif %}
                {% endfor %}
            </div>
            {% endfor %}
        </div>
    {% endfor %}
    </div>


{% endblock tableInner %}

{#<!--
    Tooltip Macro: 
    Display a tooltip of attendance data for a single day. Should not contain \" double quotes.
-->#}
{% macro tooltip(day) %}
    <section class='-mx-2 p-4 border text-center {{ day.endOfDay.statusClass }}'>
        {{ day.dateDisplay }}<br/>
        
        <span class='font-bold text-base leading-normal'>{{ __(day.endOfDay.type) }}</span><br/>

        {% if day.endOfDay.reason %}
            <span class='mt-1 text-xs'>{{ __(day.endOfDay.reason) }}</span><br/>
        {% endif %}

        <ul class='list-none ml-0 mt-4 text-xs text-left'>
        {% for log in day.logs %}
            <li class='{{ log.statusClass }} leading-relaxed'>
                {{ log.timestampTaken|date( log.timestampTaken|date('Y-m-d') == day.date ? 'H:i' : 'H:i Y-m-d') }} -
                {{ __(log.type) }} {{- log.reason ? ', ' ~ __(log.reason) }} - 
                {{ log.contextName ? log.contextName : __(log.context) }}
            </li>
        {% endfor %}
    </section>
{% endmacro tooltip %}


{#<!--
    Badge Macro:
    Display a badge number for attendance days with partial absence / presence.
-->#}
{% macro badge(day) %}
    {% if (day.absentCount > 0 or day.partialCount > 0) and \"Present\" in day.endOfDay.type %}
    <div class=\"absolute top-0 right-0 mt-1 mr-1 z-10 rounded-full bg-gray text-white no-underline leading-tight font-sans\" style=\"padding: 1px 3px; font-size: 8px\">
        {{ day.absentCount + day.partialCount }}
    </div>
    {% endif %}

    {% if (day.presentCount > 0 or day.partialCount > 0) and \"Absent\" in day.endOfDay.type %}
    <div class=\"absolute top-0 right-0 mt-1 mr-1 z-10 rounded-full bg-gray text-white no-underline leading-tight font-sans\" style=\"padding: 1px 3px; font-size: 8px\">
        {{ day.presentCount + day.partialCount }}
    </div>
    {% endif %}
{% endmacro badge %}


{% block footer %}


{#<!--
    Configure a custom tooltip for Student History. This ensures it doesn't 
    conflict with existing tooltips, and also displays on mobile devices.
-->#}
<style>
    .tooltip-reset {
        background: #ffffff !important;
        min-width: 16rem;
    }
    .w-1\\/7 {
        width: 14.28%;
    }
</style>
<script>
\$('#studentHistory').tooltip({
    items: \"a[data-log]\",
    show: 800,
    hide: false,
    content: function () {
        return \$(this).data('log');
    },
    tooltipClass: \"tooltip-reset\",
    position: {
        my: \"center bottom-5\",
        at: \"center top\",
        using: function (position, feedback) {
            \$(this).css(position);
            \$(\"<div>\").
                addClass(\"arrow\").
                addClass(feedback.vertical).
                addClass(feedback.horizontal).
                appendTo(this);
        }
    }
});
</script>

{% endblock footer %}
", "components/studentHistory.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\components\\studentHistory.twig.html");
    }
}
