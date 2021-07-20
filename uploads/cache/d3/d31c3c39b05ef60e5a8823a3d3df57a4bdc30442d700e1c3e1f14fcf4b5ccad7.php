<?php

/* alert.twig.html */
class __TwigTemplate_2cdba33ceb31e8947b97909acd01408d0047d9953091d6e22c6b0951577abed6 extends Twig_Template
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

<!-- <div class=\"modal fade\" id=\"large-modal-new\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
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
</div> -->

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
            <form id=\"sendEmailSms_Student_Transport\" method=\"post\" enctype=\"multipart/form-data\">
                <div class=\"modal-body emailField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">Subject</h3>
                    <textarea name=\"email_quote\" id=\"emailSubjectQuote_stud_Transport\" rows=\"1\"></textarea></br>
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_stud_Transport\" rows=\"5\"></textarea>
                    <h3 class=\"font-semibold\">Attachments (Max Size 2MB)</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach\" id=\"emailattach_camp_Transport\">
                    <!-- <div style=\"margin-top: 15px;\" id=\"showEmailField\">
                        <input type='checkbox' class='chkType' data-type=\"fatherEmail\" name='father_email' value='1'>
                        Father
                        Email
                        <input type='checkbox' class='chkType' data-type=\"motherEmail\" name='mother_email' value='1'>
                        Mother
                        Email
                        <input type='checkbox' class='chkType' data-type=\"guardianEmail\" name='guardian_email'
                            value='1'>
                        Guardian Email
                    </div> -->
                </div>
                <div class=\"modal-body smsField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_stud_Transport\" class=\"smsQuote_stud\"></textarea>
                    <!-- <div style=\"margin-top: 15px;\" id=\"showMobileField\">
                        <input type='checkbox' class='chkType' data-type=\"fatherMobile\" name='father_mobile' value='1'>
                        Father
                        Mobile
                        <input type='checkbox' class='chkType' data-type=\"motherMobile\" name='mother_mobile' value='1'>
                        Mother
                        Mobile
                        <input type='checkbox' class='chkType' data-type=\"guardianMobile\" name='guardian_mobile'
                            value='1'>
                        Guardian Mobile
                    </div> -->
                    <span></span>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary closeSMPopUp\" data-dismiss=\"modal\"
                        id=\"closeSM\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_Transport\">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-new-invoice_stud\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle_inv\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle_inv\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle_inv\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <form id=\"sendEmailSms_Student_inv\" method=\"post\" enctype=\"multipart/form-data\">
                <div class=\"modal-body emailField_inv\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">Subject</h3>
                    <textarea name=\"email_quote\" id=\"emailSubjectQuote_stud_inv\" rows=\"1\"></textarea></br>
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_stud_inv\" rows=\"5\"></textarea>
                    <h3 class=\"font-semibold\">Attachments (Max Size 2MB)</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach_inv\" id=\"emailattach_camp_inv\">
                    <div class='mt-4' id=\"showEmailField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"fatherEmail\"
                                name='father_email' value='1'>
                            <span class=\"form-check-label\">Father Email</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"motherEmail\"
                                name='mother_email' value='1'>
                            <span class=\"form-check-label\">Father Email</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"guardianEmail\"
                                name='guardian_email' value='1'>
                            <span class=\"form-check-label\">Father Email</span>
                        </label>
                    </div>
                </div>
                <div class=\"modal-body smsField_inv\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_stud_inv\" class=\"smsQuote_stud\"></textarea>
                    <div class='mt-4' id=\"showMobileField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"fatherMobile\"
                                name='father_mobile' value='1'>
                            <span class=\"form-check-label\">Father Mobile</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"motherMobile\"
                                name='mother_mobile' value='1'>
                            <span class=\"form-check-label\">Mother Mobile</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"guardianMobile\"
                                name='guardian_mobile' value='1'>
                            <span class=\"form-check-label\">Guardian Mobile</span>
                        </label>
                    </div>
                    <span></span>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary closeSMPopUp\" data-dismiss=\"modal\"
                        id=\"closeSM\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_stud_invoice\">Send</button>
                </div>
            </form>
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
            <form id=\"sendEmailSms_Student\" method=\"post\" enctype=\"multipart/form-data\">
                <div class=\"modal-body emailField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">Subject</h3>
                    <textarea name=\"email_quote\" id=\"emailSubjectQuote_stud\" rows=\"1\"></textarea></br>
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_stud\" rows=\"5\"></textarea>
                    <h3 class=\"font-semibold\">Attachments (Max Size 2MB)</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach\" id=\"emailattach_camp\">
                    <div class='mt-4' id=\"showEmailField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"fatherEmail\"
                                name='father_email' value='1'>
                            <span class=\"form-check-label\">Father Email</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"motherEmail\"
                                name='mother_email' value='1'>
                            <span class=\"form-check-label\">Mother Email</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"guardianEmail\"
                                name='guardian_email' value='1'>
                            <span class=\"form-check-label\">Guardian Email</span>
                        </label>
                    </div>
                </div>

                <div class=\"modal-body smsField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_stud\" class=\"smsQuote_stud\"></textarea>
                    <div class='mt-4' id=\"showMobileField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"fatherMobile\"
                                name='father_mobile' value='1'>
                            <span class=\"form-check-label\">Father Mobile</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"motherMobile\"
                                name='mother_mobile' value='1'>
                            <span class=\"form-check-label\">Mother Mobile</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"guardianMobile\"
                                name='guardian_mobile' value='1'>
                            <span class=\"form-check-label\">Guardian Mobile</span>
                        </label>
                    </div>
                    <span></span>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary closeSMPopUp\" data-dismiss=\"modal\"
                        id=\"closeSM\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_stud\">Send</button>
                </div>
            </form>
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
            <form id=\"sendEmailSms_Staff\" method=\"post\" enctype=\"multipart/form-data\">
                <div class=\"modal-body\"> Send To - <p id=\"sendTo\"></p>
                </div>
                <div class=\"modal-body emailField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">Subject</h3>
                    <textarea name=\"email_quote\" id=\"emailSubjectQuote_staff\" rows=\"1\"></textarea></br>
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_staff\" rows=\"5\"></textarea>
                    <h3 class=\"font-semibold\">Attachments (Max Size 2MB)</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach_staff\" id=\"emailattach_camp\">
                    <div class='mt-4' id=\"showMobileField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"email\" name='email'
                                value='1'>
                            <span class=\"form-check-label\">Email</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"emailAlternate\"
                                name='emailAlternate' value='1'>
                            <span class=\"form-check-label\">Alternate Email</span>
                        </label>
                    </div>
                </div>
                <div class=\"modal-body smsField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_staff\"></textarea>
                    <div class=\"mt-4\" id=\"showMobileField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"phone1\" name='phone1'
                                value='1'>
                            <span class=\"form-check-label\">Phone 1</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"phone2\" name='phone2'
                                value='1'>
                            <span class=\"form-check-label\">Phone 2</span>
                        </label>
                    </div>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\" id=\"closeSMT\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_staff\">Send</button>
                </div>
            </form>
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

<!-- <div class=\"modal fade\" id=\"large-modal-new\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
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
</div> -->

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
            <form id=\"sendEmailSms_Student_Transport\" method=\"post\" enctype=\"multipart/form-data\">
                <div class=\"modal-body emailField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">Subject</h3>
                    <textarea name=\"email_quote\" id=\"emailSubjectQuote_stud_Transport\" rows=\"1\"></textarea></br>
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_stud_Transport\" rows=\"5\"></textarea>
                    <h3 class=\"font-semibold\">Attachments (Max Size 2MB)</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach\" id=\"emailattach_camp_Transport\">
                    <!-- <div style=\"margin-top: 15px;\" id=\"showEmailField\">
                        <input type='checkbox' class='chkType' data-type=\"fatherEmail\" name='father_email' value='1'>
                        Father
                        Email
                        <input type='checkbox' class='chkType' data-type=\"motherEmail\" name='mother_email' value='1'>
                        Mother
                        Email
                        <input type='checkbox' class='chkType' data-type=\"guardianEmail\" name='guardian_email'
                            value='1'>
                        Guardian Email
                    </div> -->
                </div>
                <div class=\"modal-body smsField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_stud_Transport\" class=\"smsQuote_stud\"></textarea>
                    <!-- <div style=\"margin-top: 15px;\" id=\"showMobileField\">
                        <input type='checkbox' class='chkType' data-type=\"fatherMobile\" name='father_mobile' value='1'>
                        Father
                        Mobile
                        <input type='checkbox' class='chkType' data-type=\"motherMobile\" name='mother_mobile' value='1'>
                        Mother
                        Mobile
                        <input type='checkbox' class='chkType' data-type=\"guardianMobile\" name='guardian_mobile'
                            value='1'>
                        Guardian Mobile
                    </div> -->
                    <span></span>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary closeSMPopUp\" data-dismiss=\"modal\"
                        id=\"closeSM\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_Transport\">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"large-modal-new-invoice_stud\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title emailsmsFieldTitle_inv\" style=\"display:none;\">Email & SMS</h5>
                <h5 class=\"modal-title emailFieldTitle_inv\" style=\"display:none;\">Email</h5>
                <h5 class=\"modal-title smsFieldTitle_inv\" style=\"display:none;\">SMS</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
            <form id=\"sendEmailSms_Student_inv\" method=\"post\" enctype=\"multipart/form-data\">
                <div class=\"modal-body emailField_inv\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">Subject</h3>
                    <textarea name=\"email_quote\" id=\"emailSubjectQuote_stud_inv\" rows=\"1\"></textarea></br>
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_stud_inv\" rows=\"5\"></textarea>
                    <h3 class=\"font-semibold\">Attachments (Max Size 2MB)</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach_inv\" id=\"emailattach_camp_inv\">
                    <div class='mt-4' id=\"showEmailField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"fatherEmail\"
                                name='father_email' value='1'>
                            <span class=\"form-check-label\">Father Email</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"motherEmail\"
                                name='mother_email' value='1'>
                            <span class=\"form-check-label\">Father Email</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"guardianEmail\"
                                name='guardian_email' value='1'>
                            <span class=\"form-check-label\">Father Email</span>
                        </label>
                    </div>
                </div>
                <div class=\"modal-body smsField_inv\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_stud_inv\" class=\"smsQuote_stud\"></textarea>
                    <div class='mt-4' id=\"showMobileField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"fatherMobile\"
                                name='father_mobile' value='1'>
                            <span class=\"form-check-label\">Father Mobile</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"motherMobile\"
                                name='mother_mobile' value='1'>
                            <span class=\"form-check-label\">Mother Mobile</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType_inv form-check-input' data-type=\"guardianMobile\"
                                name='guardian_mobile' value='1'>
                            <span class=\"form-check-label\">Guardian Mobile</span>
                        </label>
                    </div>
                    <span></span>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary closeSMPopUp\" data-dismiss=\"modal\"
                        id=\"closeSM\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_stud_invoice\">Send</button>
                </div>
            </form>
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
            <form id=\"sendEmailSms_Student\" method=\"post\" enctype=\"multipart/form-data\">
                <div class=\"modal-body emailField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">Subject</h3>
                    <textarea name=\"email_quote\" id=\"emailSubjectQuote_stud\" rows=\"1\"></textarea></br>
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_stud\" rows=\"5\"></textarea>
                    <h3 class=\"font-semibold\">Attachments (Max Size 2MB)</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach\" id=\"emailattach_camp\">
                    <div class='mt-4' id=\"showEmailField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"fatherEmail\"
                                name='father_email' value='1'>
                            <span class=\"form-check-label\">Father Email</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"motherEmail\"
                                name='mother_email' value='1'>
                            <span class=\"form-check-label\">Mother Email</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"guardianEmail\"
                                name='guardian_email' value='1'>
                            <span class=\"form-check-label\">Guardian Email</span>
                        </label>
                    </div>
                </div>

                <div class=\"modal-body smsField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_stud\" class=\"smsQuote_stud\"></textarea>
                    <div class='mt-4' id=\"showMobileField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"fatherMobile\"
                                name='father_mobile' value='1'>
                            <span class=\"form-check-label\">Father Mobile</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"motherMobile\"
                                name='mother_mobile' value='1'>
                            <span class=\"form-check-label\">Mother Mobile</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"guardianMobile\"
                                name='guardian_mobile' value='1'>
                            <span class=\"form-check-label\">Guardian Mobile</span>
                        </label>
                    </div>
                    <span></span>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary closeSMPopUp\" data-dismiss=\"modal\"
                        id=\"closeSM\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_stud\">Send</button>
                </div>
            </form>
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
            <form id=\"sendEmailSms_Staff\" method=\"post\" enctype=\"multipart/form-data\">
                <div class=\"modal-body\"> Send To - <p id=\"sendTo\"></p>
                </div>
                <div class=\"modal-body emailField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">Subject</h3>
                    <textarea name=\"email_quote\" id=\"emailSubjectQuote_staff\" rows=\"1\"></textarea></br>
                    <h3 class=\"font-semibold\">Email Message</h3>
                    <textarea name=\"email_quote\" id=\"emailQuote_staff\" rows=\"5\"></textarea>
                    <h3 class=\"font-semibold\">Attachments (Max Size 2MB)</h3>
                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"15728640\" />
                    <input type='file' name=\"email_attach_staff\" id=\"emailattach_camp\">
                    <div class='mt-4' id=\"showMobileField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"email\" name='email'
                                value='1'>
                            <span class=\"form-check-label\">Email</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"emailAlternate\"
                                name='emailAlternate' value='1'>
                            <span class=\"form-check-label\">Alternate Email</span>
                        </label>
                    </div>
                </div>
                <div class=\"modal-body smsField\" style=\"display:none;\">
                    <h3 class=\"font-semibold\">SMS Message</h3>
                    <textarea name=\"sms_quote\" id=\"smsQuote_staff\"></textarea>
                    <div class=\"mt-4\" id=\"showMobileField\">
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"phone1\" name='phone1'
                                value='1'>
                            <span class=\"form-check-label\">Phone 1</span>
                        </label>
                        <label class=\"form-check form-check-inline\">
                            <input type='checkbox' class='chkType form-check-input' data-type=\"phone2\" name='phone2'
                                value='1'>
                            <span class=\"form-check-label\">Phone 2</span>
                        </label>
                    </div>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\" id=\"closeSMT\">Close</button>
                    <button type=\"button\" class=\"btn btn-primary\" id=\"sendEmailSms_staff\">Send</button>
                </div>
            </form>
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
</div>", "alert.twig.html", "F:\\suhail\\Office\\xampp\\htdocs\\newcode\\pupilsight_new\\resources\\templates\\alert.twig.html");
    }
}
