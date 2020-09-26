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

        $program = $rowdata['progname'];
        if (!empty($rowdata['classes'])) {
            $sql = "SELECT pupilsightYearGroupID, name FROM pupilsightYearGroup WHERE pupilsightYearGroupID IN (" . $rowdata['classes'] . ") ";
            $result = $connection2->query($sql);
            $getClass = $result->fetchAll();
        }
?>
        <div style="display:inline-flex; font-weight: 700; font-size:15px; width: 50%; margin-bottom:10px;" class="">
            <input type="hidden" id="pid" value="<?php echo $rowdata['pupilsightProgramID']; ?>">
            <input type="hidden" id="fid" value="<?php echo $rowdata['form_id']; ?>">
            <input type="hidden" id="pupilsightPersonID" value="<?php echo $pupilsightPersonID; ?>">

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
            <!-- <span style="color:red;font-size: 11px;">You Have to Select Class</span> -->
        </div>
    <?php
        echo  '<iframe id="innerForm" data-campid=' . $id . ' src=' . $rowdata['page_link'] . ' style="width:100%;height:120vh;" allowtransparency="true"></iframe>';
        //echo "<script>setTimeout(function(){iframeLoaded('innerForm');},1000);</script>";
    }
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.min.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>
    <script>
        var iframe = document.getElementById("innerForm");

        // Adjusting the iframe height onload event
        iframe.onload = function() {
            iframe.style.height = (Number(iframe.contentWindow.document.body.scrollHeight) + 100) + 'px';
        }

        

        $('#innerForm').load(function() {
            var iframe = $('#innerForm').contents();
            iframe.find(".ff-btn-submit").prop('disabled', true);
            iframe.find("#wpadminbar").hide();
            iframe.find(".section-inner").hide();
            iframe.find("head").append($("<style type='text/css'>  #site-content{margin-top:-100px;}  </style>"));

            iframe.find("input[name=date_of_birth]").change(function(){
           
                var userDate = $(this).val();
                var date_string = moment(userDate, "DD/MM/YYYY").format("MM/DD/YYYY");
                var From_date = new Date(date_string);

                var userDate2 = iframe.find("input[name=as_on_date]").val();
                var date_string2 = moment(userDate2, "DD/MM/YYYY").format("MM/DD/YYYY");
                var To_date = new Date(date_string2);

                var diff_date =  To_date - From_date;

                
                var years = Math.floor(diff_date/31536000000);
                var months = Math.floor((diff_date % 31536000000)/2628000000);
                var days = Math.floor(((diff_date % 31536000000) % 2628000000)/86400000);
                var ageval = years+" years "+months+" months and "+days+" days";
                iframe.find("input[name=age_value]").val(ageval);
                
            });

            iframe.find(".ff-el-form-control").change(function(){
                $.each($(this), function () {
                    val = $("#class option:selected").val();
                    if(val == ''){
                        $("#class").addClass('error').focus();
                        iframe.find(".ff-btn-submit").prop('disabled', true);
                        alert('You Have to Select Class');
                        return false;
                    } else {
                        $("#class").removeClass('error');
                        iframe.find(".ff-btn-submit").prop('disabled', false);
                        return true;
                    }
                });
            });    

            var pid = iframe.find(".fluentform");
            iframe.find("form").submit(function() {
                getPDF(pid);

                setTimeout(function() {
                    var flag = true;
                    iframe.find(".text-danger").each(function() {
                        flag = false;
                    });
                    if (flag) {
                        insertcampaign();
                        iframe.find(".ff-message-success").focus();
                    }
                }, 2000);
            });
        });

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
                            pupilsightPersonID: pupilsightPersonID
                        },
                        async: true,
                        success: function(response) {
                            // $('html, body').animate({
                            //     scrollTop: $("#showdiv").offset().top
                            // }, 2000);
                           
                        }
                    });
                }, 500);
            }
        }
    </script>
<?php
}
?>