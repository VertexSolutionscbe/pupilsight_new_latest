<div class="modal fade" id="large-modal-new_stud" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title emailsmsFieldTitle" style="display:none;">Email & SMS Quotes</h5>
                <h5 class="modal-title emailFieldTitle" style="display:none;">Email Quotes</h5>
                <h5 class="modal-title smsFieldTitle" style="display:none;">SMS Quotes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body emailField" style="display:none;">
                <h3 class="font-semibold">Email Quote</h3>
                <textarea name="email_quote" id="emailQuote_stud" rows="5"></textarea>
            </div>
            <div class="modal-body smsField" style="display:none;">
                <h3 class="font-semibold">SMS Quote</h3>
                <textarea name="sms_quote" id="smsQuote_stud" class="smsQuote_stud"></textarea>
                <span></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="footer-btn bg-dark-low" data-dismiss="modal">Close</button>
                <button type="button" class="footer-btn bg-linkedin" id="sendEmailSms_stud">Send</button>
            </div>
        </div>
    </div>
</div>
<script>
    function openEmailAndSms() {
        var stuids = [];
        $.each($("input[name='student_id[]']:checked"), function() {
            stuids.push($(this).val());
        });
        var stuid = stuids.join(",");
        if (stuid) {
            $(".sendButton_stud").removeClass('activestate');
            $(this).addClass('activestate');
            var noti = $(this).attr('data-noti');
            $(".emailsmsFieldTitle").hide();
            $(".emailFieldTitle").hide();
            $(".emailField").hide();
            $(".smsFieldTitle").hide();
            $(".smsField").hide();
            if (noti == '1') {
                $(".emailFieldTitle").show();
                $(".emailField").show();
            } else if (noti == '2') {
                $(".smsFieldTitle").show();
                $(".smsField").show();
            } else if (noti == '3') {
                $(".emailsmsFieldTitle").show();
                $(".emailField").show();
                $(".smsField").show();
            } else {
                $(".emailsmsFieldTitle").show();
                $(".emailField").show();
                $(".smsField").show();
            }
        } else {
            alert('You Have to Select Student First');
            window.setTimeout(function() {
                $("#large-modal-new_stud").removeClass('show');
                $("#chkCounterSession").removeClass('modal-open');
                $(".modal-backdrop").remove();
            }, 10);
        }

    }
</script>