<?php

/* alert.twig.html */
class __TwigTemplate_15e50498ae5a5d72b54aaa4bf1a33996851b97a5deeeadcd5341d91a58336c90 extends Twig_Template
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
        echo "<div class=\"modal fade\" id=\"large-modal\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendState\">Send</button>
            </div>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-new\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuoteRoute\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuoteRoute\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms\">Send</button>
            </div>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-new_stud\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote_stud\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote_stud\" class=\"smsQuote_stud\"></textarea>
                <span></span>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_stud\">Send</button>
            </div>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-new_staff\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote_staff\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote_staff\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_staff\">Send</button>
            </div>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-new_attendance\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote_att\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote_att\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_attendance\">Send</button>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"large-modal-stud_test_result\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote_stud_result\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote_stud_result\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_stud_test_result\">Send</button>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"large-modal-stud_attendance_rprt\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote_stud_result\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote_stud_rpt\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_stud_attend_rprt\">Send</button>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"testSMSModel\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-sm\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title\">Test SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body\">
                <input type=\"text\" name=\"testMobileNo\" class=\"testMobileNo w-full\" id=\"testMobileNo\"
                    placeholder=\"Enter mobile no\">
                <span class=\"m_err\" style=\"color:red\"></span>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendTestSms\">Send</button>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"testEmailModel\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-sm\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title\">Test Email</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body\">
                <input type=\"text\" name=\"testEmail\" class=\" w-full testEmail\" id=\"testEmail\" placeholder=\"Enter Email\">
                <span class=\"e_err\" style=\"color:red\"></span>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendTestEmail\">Send</button>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"large-modal-campaign_list\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <form id=\"sendEmailSms_campaignForm\" enctype=\"multipart/form-data\">
                <div class=\"modal-header\">
                    <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                    <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                    <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                        <span aria-hidden=\"true\">&times;</span>
                    </button>
                </div>
                <div class=\"modal-body emailField\" style=\"display:none;\">

                    <h3 class=\"font-semibold\">Email Subject</h3>
                    <input type=\"text\" name=\"email_sub\" id=\"emailSubjct_camp\">
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_camp\" rows=\"5\"></textarea>
                    <h3 class=\"font-semibold\" style=\"display:none;\">Attachments</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach\" id=\"emailattach_camp\" style=\"display:none;\">
                    <div style=\"margin-top: 15px;\" id=\"showEmailField\">
                        <!-- <input type='checkbox' name='father_email' value='1'> Father Email
                        <input type='checkbox' name='mother_email' value='1'> Mother Email
                        <input type='checkbox' name='guardian_email' value='1'> Guardian Email -->
                    </div>
                </div>
                <div class=\"modal-body smsField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_camp\"></textarea>
                    <div style=\"margin-top: 15px;\" id=\"showMobileField\">
                        <!-- <input type='checkbox' name='father_mobile' value='1'> Father Mobile
                        <input type='checkbox' name='mother_mobile' value='1'> Mother Mobile
                        <input type='checkbox' name='guardian_mobile' value='1'> Guardian Mobile -->
                    </div>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_campaign\">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-register_list\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <form id=\"sendEmailSms_registerForm\" enctype=\"multipart/form-data\">
                <div class=\"modal-header\">
                    <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                    <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                    <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                        <span aria-hidden=\"true\">&times;</span>
                    </button>
                </div>
                <div class=\"modal-body emailField\" style=\"display:none;\">

                    <h3 class=\"font-semibold\">Email Subject</h3>
                    <input type=\"text\" name=\"email_sub\" id=\"emailSubjct_Register\">
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_Register\" rows=\"5\"></textarea>
                    <!-- <h3 class=\"font-semibold\">Attachments</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach\" id=\"emailattach_camp\"> -->
                </div>
                <div class=\"modal-body smsField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_Register\"></textarea>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSmsContent\">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>";
    }

    public function getTemplateName()
    {
        return "alert.twig.html";
    }

    public function getDebugInfo()
    {
        return array (  23 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("<div class=\"modal fade\" id=\"large-modal\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendState\">Send</button>
            </div>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-new\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuoteRoute\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuoteRoute\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms\">Send</button>
            </div>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-new_stud\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote_stud\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote_stud\" class=\"smsQuote_stud\"></textarea>
                <span></span>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_stud\">Send</button>
            </div>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-new_staff\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote_staff\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote_staff\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_staff\">Send</button>
            </div>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-new_attendance\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote_att\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote_att\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_attendance\">Send</button>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"large-modal-stud_test_result\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote_stud_result\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote_stud_result\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_stud_test_result\">Send</button>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"large-modal-stud_attendance_rprt\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body emailField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">Email Message</h3>
                <textarea name=\"email_quote\" id=\"emailQuote_stud_result\" rows=\"5\"></textarea>
            </div>
            <div class=\"modal-body smsField\" style=\"display:none;\">
                <h3 class=\"font-semibold\">SMS Message</h3>
                <textarea name=\"sms_quote\" id=\"smsQuote_stud_rpt\"></textarea>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_stud_attend_rprt\">Send</button>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"testSMSModel\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-sm\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title\">Test SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body\">
                <input type=\"text\" name=\"testMobileNo\" class=\"testMobileNo w-full\" id=\"testMobileNo\"
                    placeholder=\"Enter mobile no\">
                <span class=\"m_err\" style=\"color:red\"></span>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendTestSms\">Send</button>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"testEmailModel\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-sm\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title\">Test Email</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <div class=\"modal-body\">
                <input type=\"text\" name=\"testEmail\" class=\" w-full testEmail\" id=\"testEmail\" placeholder=\"Enter Email\">
                <span class=\"e_err\" style=\"color:red\"></span>
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\" id=\"sendTestEmail\">Send</button>
            </div>
        </div>
    </div>
</div>
<div class=\"modal fade\" id=\"large-modal-campaign_list\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <form id=\"sendEmailSms_campaignForm\" enctype=\"multipart/form-data\">
                <div class=\"modal-header\">
                    <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                    <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                    <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                        <span aria-hidden=\"true\">&times;</span>
                    </button>
                </div>
                <div class=\"modal-body emailField\" style=\"display:none;\">

                    <h3 class=\"font-semibold\">Email Subject</h3>
                    <input type=\"text\" name=\"email_sub\" id=\"emailSubjct_camp\">
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_camp\" rows=\"5\"></textarea>
                    <h3 class=\"font-semibold\" style=\"display:none;\">Attachments</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach\" id=\"emailattach_camp\" style=\"display:none;\">
                    <div style=\"margin-top: 15px;\" id=\"showEmailField\">
                        <!-- <input type='checkbox' name='father_email' value='1'> Father Email
                        <input type='checkbox' name='mother_email' value='1'> Mother Email
                        <input type='checkbox' name='guardian_email' value='1'> Guardian Email -->
                    </div>
                </div>
                <div class=\"modal-body smsField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_camp\"></textarea>
                    <div style=\"margin-top: 15px;\" id=\"showMobileField\">
                        <!-- <input type='checkbox' name='father_mobile' value='1'> Father Mobile
                        <input type='checkbox' name='mother_mobile' value='1'> Mother Mobile
                        <input type='checkbox' name='guardian_mobile' value='1'> Guardian Mobile -->
                    </div>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_campaign\">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-register_list\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <form id=\"sendEmailSms_registerForm\" enctype=\"multipart/form-data\">
                <div class=\"modal-header\">
                    <h5 class=\"modal-title emailsmsFieldTitle\" style=\"display:none;\">Email & SMS</h5>
                    <h5 class=\"modal-title emailFieldTitle\" style=\"display:none;\">Email</h5>
                    <h5 class=\"modal-title smsFieldTitle\" style=\"display:none;\">SMS</h5>
                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                        <span aria-hidden=\"true\">&times;</span>
                    </button>
                </div>
                <div class=\"modal-body emailField\" style=\"display:none;\">

                    <h3 class=\"font-semibold\">Email Subject</h3>
                    <input type=\"text\" name=\"email_sub\" id=\"emailSubjct_Register\">
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_Register\" rows=\"5\"></textarea>
                    <!-- <h3 class=\"font-semibold\">Attachments</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach\" id=\"emailattach_camp\"> -->
                </div>
                <div class=\"modal-body smsField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_Register\"></textarea>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSmsContent\">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>", "alert.twig.html", "C:\\xampp\\htdocs\\pupilsight\\resources\\templates\\alert.twig.html");
    }
}
