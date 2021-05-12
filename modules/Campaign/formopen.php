<?php
echo "<style>

.custom_row{
 border:1px solid black;
 margin:30px;

}
.custom_col{
    margin:10px;
}

.error {
    border : 2px solid red;
}



</style>";
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Admission\AdmissionGateway;




// Module includes
require_once __DIR__ . '/moduleFunctions.php';
//include '../../pupilsight.php';


if (isActionAccessible($guid, $connection2, '/modules/Campaign/formopen.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];

    $sqlq = 'SELECT a.*,b.name as progname FROM campaign AS a LEFT JOIN pupilsightProgram AS b ON a.pupilsightProgramID = b.pupilsightProgramID where a.id = ' . $id . ' ';
    //echo $sqlq;
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetch();
    if (empty($rowdata["form_id"])) {
        echo "<div class='text-danger'>Form is not attached.</div>";
    } else {

        $sqlchk = "SELECT a.id, b.pupilsightProgramID, b.name FROM campaign_prog_class AS a LEFT JOIN pupilsightProgram AS b ON a.pupilsightProgramID = b.pupilsightProgramID  WHERE a.campaign_id = " . $id . " GROUP BY a.pupilsightProgramID ";
        $resultchk = $connection2->query($sqlchk);
        $cmpProClsChkData = $resultchk->fetchAll();

        $programData = array();
        if (!empty($cmpProClsChkData)) {
            $programData = $cmpProClsChkData;
        } else {
            $program = $rowdata['progname'];
            if (!empty($rowdata['classes'])) {
                $sql = "SELECT pupilsightYearGroupID, name FROM pupilsightYearGroup WHERE pupilsightYearGroupID IN (" . $rowdata['classes'] . ") ";
                $result = $connection2->query($sql);
                $getClass = $result->fetchAll();
            }
        }


?>
        <a id="downloadLink" href="index.php?q=/modules/Campaign/ajaxfile_parent.php&cid=<?php echo $id; ?>" class="" style="display:none;">Download Receipts</a>
        <center>
            <!--<div  >-->
            <div style="display:inline-flex; font-weight: 700; font-size:15px; width: 50%; margin-bottom:10px;">
                <input type="hidden" id="cmpid" value="<?php echo $id; ?>">
                <input type="hidden" id="fid" value="<?php echo $rowdata['form_id']; ?>">
                <input type="hidden" id="pupilsightPersonID" value="<?php echo $pupilsightPersonID; ?>">
                <input type="hidden" id="chkfeesett" value="<?php echo $rowdata['is_fee_generate']; ?>">
                <?php if (!empty($programData)) { ?>
                    <div id="progClassDiv" style="display:inline-flex;width:100%">
                        <span style="width: 40%;">Program<span style="color:red;">*</span> : </span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <select id="pid">
                            <option value="">Select Program</option>
                            <?php if (!empty($programData)) {
                                foreach ($programData as $prg) {
                            ?>
                                    <option value="<?php echo  $prg['pupilsightProgramID']; ?>"><?php echo  $prg['name']; ?></option>
                            <?php }
                            } ?>
                        </select>
                        <span style="width: 40%;" class="ml-2">Class <span style="color:red;">*</span> : </span>
                        <select id="class">
                            <option value="">Select Class</option>
                        </select>
                        <input type="hidden" id="chkProg" value="1">
                    </div>
                <?php } else { ?>
                    <div id="progClassDiv" style="display:inline-flex;width:100%">
                        <input type="hidden" id="chkProg" value="2">
                        <input type="hidden" id="pid" value="<?php echo $rowdata['pupilsightProgramID']; ?>">
                        <span style="width: 40%;">Program: <?php echo $program; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <span style="width: 20%;">Class <span style="color:red;">*</span> : </span>
                        <select id="class">
                            <option value="">Select Class</option>
                            <?php if (!empty($getClass)) {
                                foreach ($getClass as $cls) {
                            ?>
                                    <option value="<?php echo  $cls['pupilsightYearGroupID']; ?>"><?php echo  $cls['name']; ?></option>
                            <?php }
                            } ?>
                        </select>
                    </div>
                <?php } ?>
                <!-- <span style="color:red;font-size: 11px;">You Have to Select Class</span> -->

            </div>
        </center>
    <?php
        echo  '<iframe id="innerForm" class="mt-4" data-campid=' . $id . ' src=' . $rowdata['page_link'] . ' style="width:100%;height:120vh;border:0;" allowtransparency="true"></iframe>';
        //echo "<script>setTimeout(function(){iframeLoaded('innerForm');},1000);</script>";
    ?>

        <?php if (!empty($rowdata['fn_fee_structure_id']) && $rowdata['is_fee_generate'] == '2') {
            $sql = "SELECT SUM(total_amount) AS amt FROM fn_fee_structure_item WHERE fn_fee_structure_id = " . $rowdata['fn_fee_structure_id'] . " ";
            $results = $connection2->query($sql);
            $result = $results->fetch();
            $applicationAmount = $result['amt'] * 100;

            $random_number = mt_rand(1000, 9999);
            $today = time();
            $orderId = $today . $random_number;

            $sqlfh = "SELECT fn_fees_head_id FROM fn_fee_structure WHERE id =".$rowdata['fn_fee_structure_id']." ";
            $results1 = $connection2->query($sqlfh);
            $resultfh = $results1->fetch();
            

            $fn_fees_head_id = $resultfh['fn_fees_head_id'];

            $sql = 'SELECT b.* FROM fn_fees_head AS a LEFT JOIN fn_fee_payment_gateway AS b ON a.payment_gateway_id = b.id WHERE a.id = '.$fn_fees_head_id.' ';
            $result = $connection2->query($sql);
            $gatewayData = $result->fetch();
            
            $terms = $gatewayData['terms_and_conditions'];
            $gatewayID = $gatewayData['id'];
            $gateway = $gatewayData['name'];

            if (!empty($gateway)) {
                if ($gateway == 'WORLDLINE') {

                    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                    $responseLink = $base_url . "/thirdparty/payment/worldline/skit/meTrnSuccess.php";
                ?>
                    <form id="admissionPay" action="<?php echo $base_url;?>/thirdparty/payment/worldline/skit/meTrnPay.php" method="post" style="text-align:center;">
                        <input type="hidden" name="payment_gateway_id" value="<?php echo $gatewayID; ?>">
                        <input type="hidden" value="<?php echo $orderId; ?>" id="OrderId" name="OrderId">
                        <input type="hidden" name="amount" value="<?php echo $applicationAmount; ?>">
                        <input type="hidden" value="INR" id="currencyName" name="currencyName">
                        <input type="hidden" value="S" id="meTransReqType" name="meTransReqType">
                        <input type="hidden" name="mid" id="mid" value="<?php echo $gatewayData['mid']; ?>">
                        <input type="hidden" name="enckey" id="enckey" value="<?php echo $gatewayData['key_id']; ?>">
                        <input type="hidden" name="campaignid" value="<?php echo $id; ?>">
                        <input type="hidden" name="sid" value="0">
                        <input type="hidden" class="applicantName" name="name" value="">
                        <input type="hidden" class="applicantEmail" name="email" value="">
                        <input type="hidden" class="applicantPhone" name="phone" value="">

                        <input type="hidden" name="responseUrl" id="responseUrl" value="<?php echo $responseLink; ?>" />

                        <button type="submit" class="btnPay btn btn-primary" style="display:none;" id="payAdmissionFee">Pay</button>
                    </form>
                <?php } elseif ($gateway == 'RAZORPAY') {
                    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                    // $responseLink = $base_url . "/cms/index.php?return=1";
                    $responseLink = $base_url . "/home.php";

                ?>
                    <form id="admissionPay" action="<?php echo $base_url;?>/thirdparty/paymentadm/razorpay/pay.php" method="post" style="text-align:center;">
                        <input type="hidden" name="payment_gateway_id" value="<?php echo $gatewayID; ?>">
                        <input type="hidden" value="<?php echo $orderId; ?>" id="OrderId" name="OrderId">
                        <input type="hidden" name="amount" value="<?php echo $applicationAmount; ?>">

                        <input type="hidden" name="mid" id="mid" value="WL0000000009424">
                        <input type="hidden" name="enckey" id="enckey" value="4d6428bf5c91676b76bb7c447e6546b8">
                        <input type="hidden" name="campaignid" value="<?php echo $id; ?>">
                        <input type="hidden" name="sid" value="0">
                        <input type="hidden" class="applicantName" name="name" value="">
                        <input type="hidden" class="applicantEmail" name="email" value="">
                        <input type="hidden" class="applicantPhone" name="phone" value="">

                        <input type="hidden" name="callbackurl" id="responseUrl" value="<?= $responseLink ?>">
                        <input type="hidden" value="<?php echo $orgData['title']; ?>" id="organisationName" name="organisationName">
                        <input type="hidden" value="<?php echo $orgData['logo_image']; ?>" id="organisationLogo" name="organisationLogo">

                        <button type="submit" class="btnPay btn btn-primary" style="display:none;" id="payAdmissionFee">Pay</button>
                    </form>

                <?php } elseif ($gateway == 'PAYU') {
                    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                    //$responseLink = $base_url . "/cms/index.php?return=1";
                    $responseLink = $base_url . "/home.php";
                ?>
                    <form id="admissionPay" action="<?php echo $base_url;?>/thirdparty/payment/payu/checkout.php" method="post" style="text-align:center;">
                        <input type="hidden" name="payment_gateway_id" value="<?php echo $gatewayID; ?>">
                        <input type="hidden" value="<?php echo $orderId; ?>" id="OrderId" name="OrderId">
                        <input type="hidden" name="amount" value="<?php echo $applicationAmount; ?>">

                        <input type="hidden" name="campaignid" value="<?php echo $id; ?>">
                        <input type="hidden" name="sid" value="0">
                        <input type="hidden" class="applicantName" name="name" value="">
                        <input type="hidden" class="applicantEmail" name="email" value="">
                        <input type="hidden" class="applicantPhone" name="phone" value="">

                        <input type="hidden" name="callbackurl" id="responseUrl" value="<?= $responseLink ?>">
                        <input type="hidden" value="<?php echo $orgData['title']; ?>" id="organisationName" name="organisationName">
                        <input type="hidden" value="<?php echo $orgData['logo_image']; ?>" id="organisationLogo" name="organisationLogo">

                        <button type="submit" class="btnPay btn btn-primary" style="display:none;" id="payAdmissionFee">Pay</button>
                    </form>
                <?php } elseif ($gateway == 'AIRPAY') {
                    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                    //$responseLink = $base_url . "/cms/index.php?return=1";
                    $responseLink = $base_url . "/home.php";
                    $airpayamount = number_format($applicationAmount, 2, '.', '');
                ?>
                    <form id="admissionPay" action="<?php echo $base_url;?>/thirdparty/payment/airpay/sendtoairpay.php" method="post" style="text-align:center;">
                        <input type="hidden" name="payment_gateway_id" value="<?php echo $gatewayID; ?>">
                        <input type="hidden" value="<?php echo $orderId; ?>" id="OrderId" name="orderid">
                        <input type="hidden" name="amount" value="<?php echo $airpayamount; ?>">

                        <input type="hidden" name="campaignid" value="<?php echo $id; ?>">
                        <input type="hidden" name="sid" value="0">
                        <input type="hidden" class="applicantName" name="buyerFirstName" value="">
                        <input type="hidden" class="applicantName" name="buyerLastName" value="">
                        <input type="hidden" class="applicantEmail" name="buyerEmail" value="">
                        <input type="hidden" class="applicantAirPayPhone" name="buyerPhone" value="">

                        <input type="hidden" class="buyerAddress" name="buyerAddress" value="">
                        <input type="hidden" class="buyerCity" name="buyerCity" value="">
                        <input type="hidden" class="buyerState" name="buyerState" value="">
                        <input type="hidden" class="buyerPinCode" name="buyerPinCode" value="">
                        <input type="hidden" class="buyerCountry" name="buyerCountry" value="">
                        <input type="hidden" class="ptype" name="ptype" value="admission">

                        <input type="hidden" name="callbackurl" id="responseUrl" value="<?= $responseLink ?>">
                        <input type="hidden" value="<?php echo $orgData['title']; ?>" id="organisationName" name="organisationName">
                        <input type="hidden" value="<?php echo $orgData['logo_image']; ?>" id="organisationLogo" name="organisationLogo">

                        <button type="submit" class="btnPay btn btn-primary" style="display:none;" id="payAdmissionFee">Pay</button>
                    </form>
        <?php   }
            }
        } 
    }
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.min.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>
    <script>
        var iframe = document.getElementById("innerForm");

        // Adjusting the iframe height onload event
        // iframe.onload = function() {
        //     iframe.style.height = (Number(iframe.contentWindow.document.body.scrollHeight) + 100) + 'px';
        // }



        $('#innerForm').load(function() {
            var iframe = $('#innerForm').contents();
            iframe.find(".ff-btn-submit").prop('disabled', true);
            iframe.find("#wpadminbar").hide();
            iframe.find(".section-inner").hide();
            iframe.find("input[name=age_value]").prop('readonly', true);
            iframe.find("input[name=dob_in_words]").prop('readonly', true);
            iframe.find("head").append($("<style type='text/css'>  #site-content{margin-top:-90px;}  </style>"));

            iframe.find("input[name=date_of_birth]").change(function() {

                var userDate = $(this).val();
                var date_string = moment(userDate, "DD/MM/YYYY").format("MM/DD/YYYY");
                var From_date = new Date(date_string);

                var userDate2 = iframe.find("input[name=as_on_date]").val();
                var date_string2 = moment(userDate2, "DD/MM/YYYY").format("MM/DD/YYYY");
                var To_date = new Date(date_string2);

                var diff_date = To_date - From_date;


                var years = Math.floor(diff_date / 31536000000);
                var months = Math.floor((diff_date % 31536000000) / 2628000000);
                var days = Math.floor(((diff_date % 31536000000) % 2628000000) / 86400000);
                var ageval = years + " years " + months + " months and " + days + " days";
                iframe.find("input[name=age_value]").val(ageval);

                var dateTime = new Date(From_date);
                var month = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                var date = ['First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth', 'Seventh', 'Eighth', 'Ninth', 'Tenth', 'Eleventh', 'Twelfth', 'Thirteenth', 'Fourteenth', 'Fifteenth', 'Sixteenth', 'Seventeenth', 'Eighteenth', 'Nineteenth', 'Twentieth', 'Twenty-First', 'Twenty-Second', 'Twenty-Third', 'Twenty-Fourth', 'Twenty-Fifth', 'Twenty-Sixth', 'Twenty-Seventh', 'Twenty-Eighth', 'Twenty-Ninth', 'Thirtieth', 'Thirty-First'];
                var strDateTime = date[dateTime.getDate() - 1] + " " + month[dateTime.getMonth()] + " " + toWords(dateTime.getFullYear());
                iframe.find("input[name=dob_in_words]").val(strDateTime);

            });

            iframe.find(".ff-el-form-control").change(function() {
                $.each($(this), function() {
                    chkprog = $("#chkProg").val();
                    var val = $("#class option:selected").val();
                    if (val == '') {
                        $("#class").addClass('error').focus();
                        iframe.find(".ff-btn-submit").prop('disabled', true);
                        alert('You Have to Select Class');
                        if (chkprog == '1') {
                            var pval = $("#pid option:selected").val();
                            if (pval == '') {
                                $("#pid").addClass('error').focus();
                                iframe.find(".ff-btn-submit").prop('disabled', true);
                                alert('You Have to Select Program');
                                return false;
                            } else {
                                $("#pid").removeClass('error');
                                iframe.find(".ff-btn-submit").prop('disabled', false);
                                return true;
                            }
                        }
                        return false;
                    } else {
                        $("#class").removeClass('error');
                        iframe.find(".ff-btn-submit").prop('disabled', false);
                        if (chkprog == '1') {
                            var pval = $("#pid option:selected").val();
                            if (pval == '') {
                                $("#pid").addClass('error').focus();
                                iframe.find(".ff-btn-submit").prop('disabled', true);
                                alert('You Have to Select Program');
                                return false;
                            } else {
                                $("#pid").removeClass('error');
                                iframe.find(".ff-btn-submit").prop('disabled', false);
                                return true;
                            }
                        }
                        return true;
                    }
                });
            });

            var pid = iframe.find(".fluentform");
            iframe.find("form").submit(function() {
                //getPDF(pid);

                setTimeout(function() {
                    var flag = true;
                    iframe.find(".text-danger").each(function() {
                        flag = false;
                    });
                    if (flag) {
                        iframe.find(".ff-message-success").focus();
                        insertcampaign();
                    }
                }, 2000);
            });
        });

        function toWords(s) {
            var th = ['', 'Thousand', 'Million', 'Billion', 'Trillion'];
            var dg = ['Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
            var tn = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
            var tw = ['Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
            s = s.toString();
            s = s.replace(/[\, ]/g, '');
            if (s != parseFloat(s)) {
                return 'not a number';
            }
            var x = s.indexOf('.');
            if (x == -1) x = s.length;
            if (x > 15) return 'too big';
            var n = s.split('');
            var str = '';
            var sk = 0;
            for (var i = 0; i < x; i++) {
                if ((x - i) % 3 == 2) {
                    if (n[i] == '1') {
                        str += tn[Number(n[i + 1])] + ' ';
                        i++;
                        sk = 1;
                    } else if (n[i] != 0) {
                        str += tw[n[i] - 2] + ' ';
                        sk = 1;
                    }
                } else if (n[i] != 0) {
                    str += dg[n[i]] + ' ';
                    if ((x - i) % 3 == 0) str += 'hundred ';
                    sk = 1;
                }
                if ((x - i) % 3 == 1) {
                    if (sk) str += th[(x - i - 1) / 3] + ' ';
                    sk = 0;
                }
            }
            if (x != s.length) {
                var y = s.length;
                str += 'point ';
                for (var i = x + 1; i < y; i++) str += dg[n[i]] + ' ';
            }
            return str.replace(/\s+/g, ' ');
        }

        function getPDF(pid) {

            var HTML_Width = pid.width();
            var HTML_Height = pid.height();
            var top_left_margin = 15;
            var PDF_Width = HTML_Width + (top_left_margin * 2);
            var PDF_Height = (PDF_Width * 1.5) + (top_left_margin * 2);
            var canvas_image_width = HTML_Width;
            var canvas_image_height = HTML_Height;

            var totalPDFPages = Math.ceil(HTML_Height / PDF_Height) - 1;


            html2canvas(pid[0], {
                allowTaint: true
            }).then(function(canvas) {
                canvas.getContext('2d');

                console.log(canvas.height + "  " + canvas.width);


                var imgData = canvas.toDataURL("image/jpeg", 1.0);
                var pdf = new jsPDF('p', 'pt', [PDF_Width, PDF_Height]);
                pdf.addImage(imgData, 'JPG', top_left_margin, top_left_margin, canvas_image_width, canvas_image_height);


                for (var i = 1; i <= totalPDFPages; i++) {
                    pdf.addPage(PDF_Width, PDF_Height);
                    pdf.addImage(imgData, 'JPG', top_left_margin, -(PDF_Height * i) + (top_left_margin * 4), canvas_image_width, canvas_image_height);
                }

                //pdf.save("HTML-Document.pdf");
                //pdf.save();
                var blob = btoa(pdf.output());
                // var blob = pdf.output('blob');
                var formData = new FormData();
                var type = 'saveApplicantForm';
                formData.append('pdf', blob);
                formData.append('type', type);
                formData.append('val', 'pdfdata');
                $.ajax({
                    url: 'ajax_data.php', // not an actual good naming 
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log(response)
                    },
                    error: function(err) {
                        console.log(err)
                    }
                });
            });
        };

        function insertcampaign() {
            var val = $("#innerForm").attr('data-campid');
            var pid = $("#pid").val();
            var fid = $("#fid").val();
            var clid = $("#class").val();
            var pupilsightPersonID = $("#pupilsightPersonID").val();
            var chkfeeSett = $("#chkfeesett").val();
            if (val != '') {
                var type = 'updateApplicantData';
                setTimeout(function() {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            val: val,
                            type: type,
                            pid: pid,
                            fid: fid,
                            clid: clid,
                            pupilsightPersonID: pupilsightPersonID,
                            chkfeeSett: chkfeeSett
                        },
                        async: true,
                        success: function(response) {
                            // $('html, body').animate({
                            //     scrollTop: $("#showdiv").offset().top
                            // }, 2000);
                            if (chkfeeSett == '2') {
                                $("#progClassDiv").remove();
                                $("#payAdmissionFee").show();
                            } else {
                                $("#downloadLink")[0].click();
                                alert('Your Application Submitted Successfully, We Will get back to you Soon!');
                                window.location.href = 'index.php?q=/modules/Campaign/check_status.php';
                            }
                        }
                    });
                }, 500);
            }
        }

        $(document).on('change', '#pid', function() {
            var val = $(this).val();
            var cid = $("#cmpid").val();
            if (val != '') {
                var type = 'getCampClass';
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: {
                        val: val,
                        type: type,
                        cid: cid
                    },
                    async: true,
                    success: function(response) {
                        $("#class").html('');
                        $("#class").html(response);
                    }
                });
            }
        });
    </script>
<?php
}
?>