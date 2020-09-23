<?php

/* menu.twig.html */
class __TwigTemplate_e66dd5ee8e6e385f47556f0575b925b00bcef680fa07e41b984a45ab84bc5e20 extends Twig_Template
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
        echo " ";
        if (($context["isLoggedIn"] ?? null)) {
            // line 11
            echo "

<div class=\"mobile-sidebar-header d-md-none\">
    <div class=\"\">
        <a href=\"";
            // line 15
            echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
            echo "/index.php\"><img class=\"headerlogo\" src=\"";
            echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
            echo "/";
            echo twig_escape_filter($this->env, (((isset($context["organisationLogo"]) || array_key_exists("organisationLogo", $context))) ? (_twig_default_filter(($context["organisationLogo"] ?? null), " /themes/Default/img/logo.png ")) : (" /themes/Default/img/logo.png ")), "html", null, true);
            echo "\" alt=\"logo\"></a>
        <a id=\"\" class=\"d-lg-none\">
            <i class=\"far fa-window-close close_sidebar sidebar-toggle-mobile\"></i>
        </a>
    </div>
</div>
<div class=\"sidebar-menu-content\">
    <ul class=\"nav nav-sidebar-menu sidebar-toggle-view\">
        ";
            // line 23
            if ((($context["parentrole"] ?? null) != "033")) {
                // line 24
                echo "        <li class=\"nav-item \">
            <a href=\"";
                // line 25
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/index.php\" class=\"nav-link chkCounter\"><i
                    class=\"flaticon-dashboard \"></i><span>";
                // line 26
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array("Home")), "html", null, true);
                echo "</span></a>

        </li>

        ";
            }
            // line 30
            echo " ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["menuMain"] ?? null));
            foreach ($context['_seq'] as $context["categoryName"] => $context["items"]) {
                echo " ";
                if (($context["categoryName"] == ($context["parentMenuSelect"] ?? null))) {
                    // line 31
                    echo "


        <li class=\"nav-item sidebar-nav-item active\">


            ";
                } else {
                    // line 38
                    echo "            <li class=\"nav-item sidebar-nav-item \">

                ";
                }
                // line 41
                echo "
                <a href=\"#\" class=\"nav-link\">
                    ";
                // line 43
                if ((($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 = ($context["menuMainIcon"] ?? null)) && is_array($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5) || $__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5 instanceof ArrayAccess ? ($__internal_7cd7461123377b8c9c1b6a01f46c7bbd94bd12e59266005df5e93029ddbc0ec5[$context["categoryName"]] ?? null) : null)) {
                    // line 44
                    echo "                    <i class=\"";
                    echo twig_escape_filter($this->env, (($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a = ($context["menuMainIcon"] ?? null)) && is_array($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a) || $__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a instanceof ArrayAccess ? ($__internal_3e28b7f596c58d7729642bcf2acc6efc894803703bf5fa7e74cd8d2aa1f8c68a[$context["categoryName"]] ?? null) : null), "html", null, true);
                    echo "\"></i>
                    ";
                } else {
                    // line 46
                    echo "                    <i class=\"flaticon-more-button-of-three-dots\"></i>
                    ";
                }
                // line 48
                echo "
                    <span>";
                // line 49
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array($context["categoryName"])), "html", null, true);
                echo "</span>
                </a> ";
                // line 50
                if (($context["categoryName"] == ($context["parentMenuSelect"] ?? null))) {
                    // line 51
                    echo "                <ul class=\"nav sub-group-menu menu-open\" style=\"display:block;\">
                    ";
                } else {
                    // line 53
                    echo "                    <ul class=\"nav sub-group-menu\">
                        ";
                }
                // line 54
                echo " ";
                if (($context["categoryName"] == "Finance")) {
                    echo " ";
                    if (((($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 = (($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 = $context["items"]) && is_array($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9) || $__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9 instanceof ArrayAccess ? ($__internal_81ccf322d0988ca0aa9ae9943d772c435c5ff01fb50b956278e245e40ae66ab9[0] ?? null) : null)) && is_array($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57) || $__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57 instanceof ArrayAccess ? ($__internal_b0b3d6199cdf4d15a08b3fb98fe017ecb01164300193d18d78027218d843fc57["fn"] ?? null) : null) == "active")) {
                        // line 55
                        echo "
                        <li class=\"nav-item sidebar-nav-item \">
                            <a href=\"#\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Master</a>
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item \">
                                    <a href=\"index.php?q=/modules/Finance/fee_series_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Series</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_head_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Head</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_fine_rule_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fine Rule</a>
                                </li>
                                
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_item_type_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Item Type</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_receipts_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Receipts Template</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_item_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Item</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/deposit_account_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Deposit Account</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_discount_rule_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Discount Rule</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_counter_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Counter</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a style=\"font-size: 13px !important;\" href=\"index.php?q=/modules/Finance/fee_master_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Banks & Payment Mode</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_payment_gateway_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Payment Gateway</a>
                                </li>

                            </ul>
                        </li>
                        <li class=\"nav-item \">
                            <a href=\"index.php?q=/modules/Finance/fee_structure_manage.php\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>Structure</a>
                        </li>
                        <li class=\"nav-item sidebar-nav-item \">
                            <a href=\"#\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Payment</a>
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/invoice_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Manage Invoice</a>
                                </li>
                                <li class=\"nav-item\">
                                    ";
                        // line 106
                        if ((($context["counterid"] ?? null) == "")) {
                            // line 107
                            echo "                                    <a href=\"fullscreen.php?q=/modules/Finance/fee_counter_check_add.php\" class=\"thickbox nav-link\"><i class=\"fas fa-angle-right\"></i>Collection</a> ";
                        } else {
                            // line 108
                            echo "                                    <a href=\"index.php?q=/modules/Finance/fee_collection_manage.php\" class=\" nav-link\"><i class=\"fas fa-angle-right\"></i>Collection</a> ";
                        }
                        // line 109
                        echo "                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_transaction_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Transaction</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_transaction_cancel_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Cancel Transaction</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_transaction_refund_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Refund Transaction</a>
                                </li>
                            </ul>
                        </li>
                        ";
                    } else {
                        // line 121
                        echo " ";
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable($context["items"]);
                        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                            // line 122
                            echo "                        <li class=\"nav-item\">
                            ";
                            // line 123
                            if ((twig_get_attribute($this->env, $this->source, $context["item"], "name", array()) == ($context["childMenuSelect"] ?? null))) {
                                // line 124
                                echo "                            <a href=\"";
                                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                                echo "\" class=\"nav-link menu-active chkCounter\"><i class=\"fas fa-angle-right\"></i>View Invoices</a> ";
                            } else {
                                // line 125
                                echo "                            <a href=\"";
                                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                                echo "\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>View Invoices</a> ";
                            }
                            // line 126
                            echo "                        </li>
                        ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 127
                        echo " ";
                    }
                    echo " 
                        
                        ";
                } elseif ((                // line 129
$context["categoryName"] == "Transport")) {
                    echo " ";
                    if (((($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217 = (($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105 = $context["items"]) && is_array($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105) || $__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105 instanceof ArrayAccess ? ($__internal_128c19eb75d89ae9acc1294da2e091b433005202cb9b9351ea0c5dd5f69ee105[0] ?? null) : null)) && is_array($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217) || $__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217 instanceof ArrayAccess ? ($__internal_add9db1f328aaed12ef1a33890510da978cc9cf3e50f6769d368473a9c90c217["tr"] ?? null) : null) == "active")) {
                        // line 130
                        echo "                        <li class=\"nav-item\">
                            <a href=\"index.php?q=/modules/Transport/bus_manage.php\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>Bus Details</a>
                        </li>
                        <li class=\"nav-item\">
                            <a href=\"#\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Routes</a>
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Transport/routes.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Manage Route</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Transport/assign_route.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Assign to Student</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Transport/assign_staff_route_manage.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Assign to Staff</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Transport/view_members_in_route.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>View Member in Route</a>
                                </li>
                            </ul>
                        </li>
                        <li class=\"nav-item\">
                            <a href=\"index.php?q=/modules/Transport/transport_fee.php\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>Transport Fee</a>
                        </li>

                        ";
                    }
                    // line 154
                    echo " 
                        ";
                } elseif ((                // line 155
$context["categoryName"] == "Academics")) {
                    echo " ";
                    if (((($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779 = (($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1 = $context["items"]) && is_array($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1) || $__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1 instanceof ArrayAccess ? ($__internal_3e040fa9f9bcf48a8b054d0953f4fffdaf331dc44bc1d96f1bb45abb085e61d1[0] ?? null) : null)) && is_array($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779) || $__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779 instanceof ArrayAccess ? ($__internal_921de08f973aabd87ecb31654784e2efda7404f12bd27e8e56991608c76e7779["tr"] ?? null) : null) == "active")) {
                        // line 156
                        echo "                        
                        <li class=\"nav-item\">
                            <a href=\"#\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Curriculum</a>
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/department_manage.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Subject Master</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/ac_manage_skill.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Skill Master</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/subject_to_class_manage.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Subject To Class</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_elective_group.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Manage Elective Group</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/ac_manage_remarks.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Remarks Master</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/descriptive_indicator_config.php\" class=\"nav-link \" style=\"line-break: anywhere;\"><i class=\"fas fa-angle-right\"></i>DI Mode</a>
                                </li>
                                
                            </ul>
                        </li>
                        

                        <li class=\"nav-item\">
                            <a href=\"#\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Test</a>
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/grade_system_manage.php\" class=\"nav-link \" style=\"line-break: anywhere;\"><i class=\"fas fa-angle-right\"></i>Grading System</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/examination_room_manage.php\" class=\"nav-link \" style=\"line-break: anywhere;\"><i class=\"fas fa-angle-right\"></i>Manage Test Room</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/examination_report_template_manage.php\" class=\"nav-link \" style=\"line-break: anywhere;\"><i class=\"fas fa-angle-right\"></i>Reports Template</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/test_home.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Test Home</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_edit_test.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Edit Test</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_test.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Manage Test</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_marks_entry_by_subject.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Marks Entry by Subject</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/marks_by_student.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Marks Entry by Student</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_enter_aat.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Enter A.A.T</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/test_marks_upload.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Marks Upload</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/marks_not_entered.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Marks not Entered</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_test_results.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Test Results</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/sketch_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Sketch</a>
                                </li>

                            </ul>
                        </li>
                        ";
                    }
                    // line 228
                    echo " 
                        ";
                } elseif ((                // line 229
$context["categoryName"] == "Attendance")) {
                    echo " ";
                    if (((($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0 = (($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866 = $context["items"]) && is_array($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866) || $__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866 instanceof ArrayAccess ? ($__internal_602f93ae9072ac758dc9cd47ca50516bbc1210f73d2a40b01287f102c3c40866[0] ?? null) : null)) && is_array($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0) || $__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0 instanceof ArrayAccess ? ($__internal_bd1cf16c37e30917ff4f54b7320429bcc2bb63615cd8a735bfe06a3f1b5c82a0["tr"] ?? null) : null) == "active")) {
                        // line 230
                        echo "                        
                        <li class=\"nav-item\">
                            <a href=\"index.php?q=/modules/Attendance/attendance.php\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Attendance</a>
                        </li> 
                        
                        

                        ";
                    } else {
                        // line 237
                        echo " ";
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable($context["items"]);
                        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                            // line 238
                            echo "                        <li class=\"nav-item\">
                            ";
                            // line 239
                            if ((twig_get_attribute($this->env, $this->source, $context["item"], "name", array()) == ($context["childMenuSelect"] ?? null))) {
                                // line 240
                                echo "                            <a href=\"";
                                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                                echo "\" class=\"nav-link menu-active chkCounter\"><i class=\"fas fa-angle-right\"></i>Student Bus Route</a> ";
                            } else {
                                // line 241
                                echo "                            <a href=\"";
                                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                                echo "\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>Student Bus Route</a> ";
                            }
                            // line 242
                            echo "                        </li>
                        ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 243
                        echo " ";
                    }
                    // line 244
                    echo "                        <!-- <li class=\"nav-item\">
                            <a href=\"#\" class=\"nav-link hidearrow\"><i class=\"fas fa-angle-right\"></i>Assign Routes</a> 
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item\"></li>
                                    <a href=\"index.php?q=/modules/Transport/assign_route.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Assign to Student</a> 
                                </li>
                                <li class=\"nav-item\"></li>
                                    <a href=\"index.php?q=/modules/Transport/assign_staff_route_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Assign to Staff</a> 
                                </li>
                            </ul>
                        </li>         -->
                        ";
                } else {
                    // line 255
                    echo " ";
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($context["items"]);
                    foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                        // line 256
                        echo "                        <li class=\"nav-item\">
                            ";
                        // line 257
                        if ((twig_get_attribute($this->env, $this->source, $context["item"], "name", array()) == ($context["childMenuSelect"] ?? null))) {
                            // line 258
                            echo "                            <a href=\"";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                            echo "\" class=\"nav-link menu-active chkCounter\"><i class=\"fas fa-angle-right\"></i>";
                            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), twig_get_attribute($this->env, $this->source, $context["item"], "textDomain", array()))), "html", null, true);
                            echo "</a> ";
                        } else {
                            // line 259
                            echo "                            <a href=\"";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "url", array()), "html", null, true);
                            echo "\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>";
                            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('__')->getCallable(), array(twig_get_attribute($this->env, $this->source, $context["item"], "name", array()), twig_get_attribute($this->env, $this->source, $context["item"], "textDomain", array()))), "html", null, true);
                            echo "</a> ";
                        }
                        // line 260
                        echo "                        </li>
                        ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 261
                    echo " ";
                }
                // line 262
                echo "                    </ul>
            </li>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['categoryName'], $context['items'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 264
            echo " ";
            if ((($context["parentrole"] ?? null) == "001")) {
                // line 265
                echo "
            <li class=\"nav-item \">
                <a href=\"";
                // line 267
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/lms.php\" class=\"nav-link chkCounter\"><i
                    class=\"flaticon-maths-class-materials-cross-of-a-pencil-and-a-ruler\"></i><span>LMS</span></a>
            </li>

            <li class=\"nav-item \">
                <a href=\"";
                // line 272
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/cms.php\" class=\"nav-link chkCounter\"><i class=\"flaticon-script\"></i><span>CMS</span></a>

            </li>

            <li class=\"nav-item \">
                <a href=\"";
                // line 277
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/forms_reports.php\" class=\"nav-link chkCounter\"><i class=\"flaticon-script\"></i><span>Forms & Reports</span></a>
            </li>
            <!--
            <li class=\"nav-item \">
                <a href=\"";
                // line 281
                echo twig_escape_filter($this->env, ($context["absoluteURL"] ?? null), "html", null, true);
                echo "/reports.php\" class=\"nav-link chkCounter\"><i class=\"flaticon-checklist\"></i><span>Forms &
                    Reports</span></a>

            </li>
            -->
            ";
            }
            // line 287
            echo "
            </ul>
</div>

";
        }
    }

    public function getTemplateName()
    {
        return "menu.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  487 => 287,  478 => 281,  471 => 277,  463 => 272,  455 => 267,  451 => 265,  448 => 264,  440 => 262,  437 => 261,  430 => 260,  423 => 259,  416 => 258,  414 => 257,  411 => 256,  406 => 255,  392 => 244,  389 => 243,  382 => 242,  377 => 241,  372 => 240,  370 => 239,  367 => 238,  362 => 237,  352 => 230,  348 => 229,  345 => 228,  270 => 156,  266 => 155,  263 => 154,  236 => 130,  232 => 129,  226 => 127,  219 => 126,  214 => 125,  209 => 124,  207 => 123,  204 => 122,  199 => 121,  184 => 109,  181 => 108,  178 => 107,  176 => 106,  123 => 55,  118 => 54,  114 => 53,  110 => 51,  108 => 50,  104 => 49,  101 => 48,  97 => 46,  91 => 44,  89 => 43,  85 => 41,  80 => 38,  71 => 31,  64 => 30,  56 => 26,  52 => 25,  49 => 24,  47 => 23,  32 => 15,  26 => 11,  23 => 10,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{#
<!--
Pupilsight, Flexible & Open School System


This is a Pupilsight template file, written in HTML and Twig syntax.
For info about editing, see: https://twig.symfony.com/doc/2.x/

Main Menu: Displays the top-level categories and active modules.
-->#} {% if isLoggedIn %}


<div class=\"mobile-sidebar-header d-md-none\">
    <div class=\"\">
        <a href=\"{{ absoluteURL }}/index.php\"><img class=\"headerlogo\" src=\"{{ absoluteURL }}/{{ organisationLogo|default(\" /themes/Default/img/logo.png \") }}\" alt=\"logo\"></a>
        <a id=\"\" class=\"d-lg-none\">
            <i class=\"far fa-window-close close_sidebar sidebar-toggle-mobile\"></i>
        </a>
    </div>
</div>
<div class=\"sidebar-menu-content\">
    <ul class=\"nav nav-sidebar-menu sidebar-toggle-view\">
        {% if parentrole != '033' %}
        <li class=\"nav-item \">
            <a href=\"{{ absoluteURL }}/index.php\" class=\"nav-link chkCounter\"><i
                    class=\"flaticon-dashboard \"></i><span>{{ __('Home') }}</span></a>

        </li>

        {% endif %} {% for categoryName, items in menuMain %} {% if categoryName == parentMenuSelect %}



        <li class=\"nav-item sidebar-nav-item active\">


            {% else %}
            <li class=\"nav-item sidebar-nav-item \">

                {% endif %}

                <a href=\"#\" class=\"nav-link\">
                    {% if menuMainIcon[categoryName] %}
                    <i class=\"{{ menuMainIcon[categoryName] }}\"></i>
                    {% else %}
                    <i class=\"flaticon-more-button-of-three-dots\"></i>
                    {% endif %}

                    <span>{{ __(categoryName) }}</span>
                </a> {% if categoryName == parentMenuSelect %}
                <ul class=\"nav sub-group-menu menu-open\" style=\"display:block;\">
                    {% else %}
                    <ul class=\"nav sub-group-menu\">
                        {% endif %} {% if categoryName == 'Finance' %} {% if items[0][\"fn\"] == 'active' %}

                        <li class=\"nav-item sidebar-nav-item \">
                            <a href=\"#\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Master</a>
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item \">
                                    <a href=\"index.php?q=/modules/Finance/fee_series_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Series</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_head_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Head</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_fine_rule_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fine Rule</a>
                                </li>
                                
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_item_type_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Item Type</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_receipts_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Receipts Template</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_item_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Item</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/deposit_account_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Deposit Account</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_discount_rule_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Discount Rule</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_counter_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Counter</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a style=\"font-size: 13px !important;\" href=\"index.php?q=/modules/Finance/fee_master_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Banks & Payment Mode</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_payment_gateway_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Fee Payment Gateway</a>
                                </li>

                            </ul>
                        </li>
                        <li class=\"nav-item \">
                            <a href=\"index.php?q=/modules/Finance/fee_structure_manage.php\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>Structure</a>
                        </li>
                        <li class=\"nav-item sidebar-nav-item \">
                            <a href=\"#\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Payment</a>
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/invoice_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Manage Invoice</a>
                                </li>
                                <li class=\"nav-item\">
                                    {% if counterid == '' %}
                                    <a href=\"fullscreen.php?q=/modules/Finance/fee_counter_check_add.php\" class=\"thickbox nav-link\"><i class=\"fas fa-angle-right\"></i>Collection</a> {% else %}
                                    <a href=\"index.php?q=/modules/Finance/fee_collection_manage.php\" class=\" nav-link\"><i class=\"fas fa-angle-right\"></i>Collection</a> {% endif %}
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_transaction_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Transaction</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_transaction_cancel_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Cancel Transaction</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Finance/fee_transaction_refund_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Refund Transaction</a>
                                </li>
                            </ul>
                        </li>
                        {% else %} {% for item in items %}
                        <li class=\"nav-item\">
                            {% if item.name == childMenuSelect %}
                            <a href=\"{{ item.url }}\" class=\"nav-link menu-active chkCounter\"><i class=\"fas fa-angle-right\"></i>View Invoices</a> {% else %}
                            <a href=\"{{ item.url }}\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>View Invoices</a> {% endif %}
                        </li>
                        {% endfor %} {% endif %} 
                        
                        {% elseif categoryName == 'Transport' %} {% if items[0][\"tr\"] == 'active' %}
                        <li class=\"nav-item\">
                            <a href=\"index.php?q=/modules/Transport/bus_manage.php\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>Bus Details</a>
                        </li>
                        <li class=\"nav-item\">
                            <a href=\"#\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Routes</a>
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Transport/routes.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Manage Route</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Transport/assign_route.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Assign to Student</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Transport/assign_staff_route_manage.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Assign to Staff</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Transport/view_members_in_route.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>View Member in Route</a>
                                </li>
                            </ul>
                        </li>
                        <li class=\"nav-item\">
                            <a href=\"index.php?q=/modules/Transport/transport_fee.php\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>Transport Fee</a>
                        </li>

                        {% endif %} 
                        {% elseif categoryName == 'Academics' %} {% if items[0][\"tr\"] == 'active' %}
                        
                        <li class=\"nav-item\">
                            <a href=\"#\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Curriculum</a>
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/department_manage.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Subject Master</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/ac_manage_skill.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Skill Master</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/subject_to_class_manage.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Subject To Class</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_elective_group.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Manage Elective Group</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/ac_manage_remarks.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Remarks Master</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/descriptive_indicator_config.php\" class=\"nav-link \" style=\"line-break: anywhere;\"><i class=\"fas fa-angle-right\"></i>DI Mode</a>
                                </li>
                                
                            </ul>
                        </li>
                        

                        <li class=\"nav-item\">
                            <a href=\"#\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Test</a>
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/grade_system_manage.php\" class=\"nav-link \" style=\"line-break: anywhere;\"><i class=\"fas fa-angle-right\"></i>Grading System</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/examination_room_manage.php\" class=\"nav-link \" style=\"line-break: anywhere;\"><i class=\"fas fa-angle-right\"></i>Manage Test Room</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/examination_report_template_manage.php\" class=\"nav-link \" style=\"line-break: anywhere;\"><i class=\"fas fa-angle-right\"></i>Reports Template</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/test_home.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Test Home</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_edit_test.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Edit Test</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_test.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Manage Test</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_marks_entry_by_subject.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Marks Entry by Subject</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/marks_by_student.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Marks Entry by Student</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_enter_aat.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Enter A.A.T</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/test_marks_upload.php\" class=\"nav-link \"><i class=\"fas fa-angle-right\"></i>Marks Upload</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/marks_not_entered.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Marks not Entered</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/manage_test_results.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Test Results</a>
                                </li>
                                <li class=\"nav-item\">
                                    <a href=\"index.php?q=/modules/Academics/sketch_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Sketch</a>
                                </li>

                            </ul>
                        </li>
                        {% endif %} 
                        {% elseif categoryName == 'Attendance' %} {% if items[0][\"tr\"] == 'active' %}
                        
                        <li class=\"nav-item\">
                            <a href=\"index.php?q=/modules/Attendance/attendance.php\" class=\"nav-link hidearrow chkCounter\"><i class=\"fas fa-angle-right\"></i>Attendance</a>
                        </li> 
                        
                        

                        {% else %} {% for item in items %}
                        <li class=\"nav-item\">
                            {% if item.name == childMenuSelect %}
                            <a href=\"{{ item.url }}\" class=\"nav-link menu-active chkCounter\"><i class=\"fas fa-angle-right\"></i>Student Bus Route</a> {% else %}
                            <a href=\"{{ item.url }}\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>Student Bus Route</a> {% endif %}
                        </li>
                        {% endfor %} {% endif %}
                        <!-- <li class=\"nav-item\">
                            <a href=\"#\" class=\"nav-link hidearrow\"><i class=\"fas fa-angle-right\"></i>Assign Routes</a> 
                            <ul class=\"nav sub-group-menu\">
                                <li class=\"nav-item\"></li>
                                    <a href=\"index.php?q=/modules/Transport/assign_route.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Assign to Student</a> 
                                </li>
                                <li class=\"nav-item\"></li>
                                    <a href=\"index.php?q=/modules/Transport/assign_staff_route_manage.php\" class=\"nav-link\"><i class=\"fas fa-angle-right\"></i>Assign to Staff</a> 
                                </li>
                            </ul>
                        </li>         -->
                        {% else %} {% for item in items %}
                        <li class=\"nav-item\">
                            {% if item.name == childMenuSelect %}
                            <a href=\"{{ item.url }}\" class=\"nav-link menu-active chkCounter\"><i class=\"fas fa-angle-right\"></i>{{ __(item.name, item.textDomain) }}</a> {% else %}
                            <a href=\"{{ item.url }}\" class=\"nav-link chkCounter\"><i class=\"fas fa-angle-right\"></i>{{ __(item.name, item.textDomain) }}</a> {% endif %}
                        </li>
                        {% endfor %} {% endif %}
                    </ul>
            </li>
            {% endfor %} {% if parentrole == '001' %}

            <li class=\"nav-item \">
                <a href=\"{{ absoluteURL }}/lms.php\" class=\"nav-link chkCounter\"><i
                    class=\"flaticon-maths-class-materials-cross-of-a-pencil-and-a-ruler\"></i><span>LMS</span></a>
            </li>

            <li class=\"nav-item \">
                <a href=\"{{ absoluteURL }}/cms.php\" class=\"nav-link chkCounter\"><i class=\"flaticon-script\"></i><span>CMS</span></a>

            </li>

            <li class=\"nav-item \">
                <a href=\"{{ absoluteURL }}/forms_reports.php\" class=\"nav-link chkCounter\"><i class=\"flaticon-script\"></i><span>Forms & Reports</span></a>
            </li>
            <!--
            <li class=\"nav-item \">
                <a href=\"{{ absoluteURL }}/reports.php\" class=\"nav-link chkCounter\"><i class=\"flaticon-checklist\"></i><span>Forms &
                    Reports</span></a>

            </li>
            -->
            {% endif %}

            </ul>
</div>

{% endif %}", "menu.twig.html", "D:\\xampp\\htdocs\\pupilsight\\resources\\templates\\menu.twig.html");
    }
}
