<?php
/*
Pupilsight, Flexible & Open School System
 */

use Pupilsight\Domain\Helper\HelperGateway;
use Pupilsight\Domain\Archive\ArchiveGateway;

function getDomain()
{
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

function expandDirectories($base_dir) {
    $directories = array();
    foreach(scandir($base_dir) as $file) {
        if($file == '.' || $file == '..') continue;
        $dir = $base_dir.DIRECTORY_SEPARATOR.$file;
        if(is_dir($dir)) {
            $directories = array_merge($directories, expandDirectories($dir));
        }else{
            if(strstr($dir,".pdf")){
                $directories []= $dir;
            }
        }
    }
    return $directories;
}

$baseurl = getDomain();

$accessFlag = true;
if ($accessFlag == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $roleid = $_SESSION[$guid]["pupilsightRoleIDPrimary"];
    $page->breadcrumbs->add(__('Manage Archives'));

?>
        
    <!----Report Details---->
    <div class="my-2" id='reportList'>
        <?php
            try{
            
            $helperGateway = $container->get(HelperGateway::class);
            $res = $helperGateway->getArchiveReport($connection2);

            $archiveGateway = $container->get(ArchiveGateway::class);

            $term = $archiveGateway->listFeeInvoiceTerm($connection2);
            $academicYear = $archiveGateway->listFeeInvoiceAcademicYear($connection2);
            $stream = $archiveGateway->listFeeInvoiceStream($connection2);

            $termTrans = $archiveGateway->listFeeTransTerm($connection2);
            $academicYearTrans = $archiveGateway->listFeeTransAcademicYear($connection2);
            $streamTrans = $archiveGateway->listFeeTransStream($connection2);
            
            }catch(Exception $ex){
                echo $ex->getMessage();
            }
        ?>
        

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a href="#archiveList" class="nav-link active">Archive List</a>
            </li>
            <li class="nav-item">
                <a href="#feeTransactions" class="nav-link">Fee Transactions</a>
            </li>
            <li class="nav-item">
                <a href="#feeInvoice" class="nav-link">Fee Invoice</a>
            </li>
            <li class="nav-item">
                <a href="#feeRecipt" class="nav-link">Fee Recipt</a>
            </li>
            <li class="nav-item">
                <a href="#reportCard" class="nav-link">Report Card</a>
            </li>
        </ul>

        <div class="card-bodyNew">
            <div class="tab-content" id='myTabContent'>
                <div class="tab-pane fade active show" id="archiveList">
                    <div class="table-responsive">
                        <table id='reportTable' class="table card-table table-vcenter text-nowrap datatable border-bottom">
                            <thead>
                                <tr>
                                    <th>Archive Name</th>
                                    <th style='width:100px;' class='text-center'>Download</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                if($res){
                                    $len = count($res);
                                    $i = 0;
                                    $str = "";
                                    $repo = array();
                                    while($i<$len){
                                        $str .="\n<tr>";
                                        $str .="\n<td><strong>".ucwords($res[$i]["name"])."</strong><br><span class='text-muted'>".$res[$i]["description"]."</span></td>";
                                        $str .="\n<td><button type='button' class='btn btn-link' onclick=\"downloadReport('".$res[$i]['id']."');\"><i class='mdi mdi-download mr-2'></i>Download</button></td>";
                                        $str .="\n</tr>";
                                        $res[$i]["name"] = ucwords($res[$i]["name"]);
                                        $repo[$res[$i]['id']]=$res[$i];
                                        $i++;
                                    }
                                    echo $str;
                                }
                            ?>  
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id='feeTransactions'>
                    <div class="row my-4">
                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">Academic Year</label>
                            <select id="transYear">
                            <?php
                                echo $archiveGateway->createOption($academicYearTrans,"AcademicYear");
                            ?>
                            </select>
                        </div>

                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">Term</label>
                            <select id="transTerm">
                            <?php
                                echo $archiveGateway->createOption($termTrans,"Term");
                            ?>
                            </select>
                        </div>
                        
                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">Stream</label>
                            <select id="invoiceStream">
                            <?php
                                echo $archiveGateway->createOption($streamTrans, "Stream");
                            ?>
                            </select>
                        </div>

                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">Student Id</label>
                            <input type="text" class="form-control" id="transStudentId" value="">
                        </div>

                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="transStudent" value="">
                        </div>

                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary" onclick="searchTrans()"><i class='mdi mdi-magnify mr-2'></i>Search</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id='feeTransactionsTable' class="mt-2 table card-table table-vcenter text-nowrap datatable border-bottom">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Stake Holder</th>
                                    <th>Full Name</th>
                                    <th>StudentID</th>
                                    <th>Organisation</th>
                                    <th>Program</th>
                                    <th>Stream</th>
                                    <th>Intake</th>
                                    <th>Term</th>
                                    <th>Academic Year</th>
                                    <th>Transaction Id</th>
                                    <th>Receipt No</th>
                                    <th>Instrument Amount</th>
                                    <th>Payment Mode</th>
                                    <th>Bank Name</th>
                                    <th>Instrument No</th>
                                    <th>Instrument Date</th>
                                    <th>Payment Status</th>
                                    <th>Transaction Amount</th>
                                    <th>Payment Received Date</th>
                                    <th>Cheque Received Date</th>
                                    <th>Other Amount</th>
                                    <th>Remarks</th>
                                    <th>Manual Receipt Number</th>
                                    <th>Total Fine Amount</th>
                                    <th>Overpayment Amount</th>
                                    <th>Overpayment Made</th>
                                    <th>Invoice No</th>
                                    <th>Invoice Amount</th>
                                    <th>Calculated Fine Amount</th>
                                    <th>Invoice Title</th>
                                    <th>Invoice Status</th>
                                    <th>Fee Item Name</th>
                                    <th>Fee Item Amount</th>
                                    <th>Fee Item Amount Paid</th>
                                    <th>Is Discount Trans</th>
                                    <th>Discount Amount</th>
                                </tr>
                            </thead>
                            <tbody id='feeTransactionsBody'></tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id='feeInvoice'>
                    <div class="row my-4">
                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">Academic Year</label>
                            <select id="invoiceYear">
                            <?php
                                echo $archiveGateway->createOption($academicYear,"AcademicYear");
                            ?>
                            </select>
                        </div>

                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">Term</label>
                            <select id="invoiceTerm">
                            <?php
                                echo $archiveGateway->createOption($term,"Term");
                            ?>
                            </select>
                        </div>
                        
                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">Stream</label>
                            <select id="invoiceStream">
                            <?php
                                echo $archiveGateway->createOption($stream, "Stream");
                            ?>
                            </select>
                        </div>

                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">Student Id</label>
                            <input type="text" class="form-control" id="invoiceStudentId" value="">
                        </div>

                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="invoiceStudent" value="">
                        </div>

                        <div class="col-md-auto col-sm-12">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary" onclick="searchInvoice()"><i class='mdi mdi-magnify mr-2'></i>Search</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id='feeInvoiceTable' class="mt-2 table card-table table-vcenter text-nowrap datatable border-bottom">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Stakeholder</th>
                                    <th>Name</th>
                                    <th>StudentID</th>
                                    <th>Organization</th>
                                    <th>Program</th>
                                    <th>Stream</th>
                                    <th>Intake</th>
                                    <th>Term</th>
                                    <th>Academic Year</th>
                                    <th>Invoice Title</th>
                                    <th>Final Amount</th>
                                    <th>Amount</th>
                                    <th>Tax</th>
                                    <th>Invoice No</th>
                                    <th>Invoice Status</th>
                                    <th>Invoice Gen Date</th>
                                    <th>Amount Paid</th>
                                    <th>Amount Pending</th>
                                    <th>Due Date</th>
                                    <th>Fine</th>
                                    <th>Fee Item Name</th>
                                    <th>Fee Item Amount</th>
                                    <th>Fee Item Discount</th>
                                    <th>Fee Item Amount Paid</th>
                                    <th>Fee Item Amount Discounted</th>
                                    <th>Fee Item Amount Pending</th>
                                    <th>Invoice Item Status</th>
                                    <th>Fee Item Tax</th>
                                    <th>Fee Item Final Amount</th>
                                    <th>Fee Item Order</th>
                                    <th>Fee Head</th>
                                </tr>
                            </thead>
                            <tbody id='feeInvoiceBody'></tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="feeRecipt">
                    <div class="table-responsive">
                        <table id='feeReciptTable' class="table card-table table-vcenter text-nowrap datatable border-bottom">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Student Name</th>
                                    <th>Student ID</th>
                                    <th>Class</th>
                                    <th>Date</th>
                                    <th>Recipt No</th>
                                    <th style='width:100px;' class='text-center'>Download</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    try{
                                        $fr = $helperGateway->getArchiveFeeRecipt($connection2);
                                        $len = count($fr);
                                        $i = 0;
                                        $cnt = 1;
                                        $str ="";
                                        while($i<$len){
                                            $downLink = $baseurl."/public/archive/fee_receipt/".$fr[$i]["file_html"];
                                            $dates = "";
                                            if($fr[$i]["st_date"]){
                                                $dates = date('d/m/Y',strtotime($fr[$i]["st_date"]));
                                            }

                                            $str .="\n<tr>";
                                            $str .="<td>".$cnt."</td>";
                                            $str .="<td>".$fr[$i]["student_name"]."</td>";
                                            $str .="<td>".$fr[$i]["student_id"]."</td>";
                                            $str .="<td>".$fr[$i]["st_class"]."</td>";
                                            $str .="<td>".$dates."</td>";
                                            $str .="<td>".$fr[$i]["receipt_no"]."</td>";
                                            $str .="<td><a href='".$downLink."' download><i class='mdi mdi-download mr-2'></i>Download</a></td>";
                                            $str .="</tr>";
                                            $cnt++;
                                            $i++;
                                        }
                                        echo $str;
                                    }catch(Exception $ex){
                                        echo $ex->getMessage();
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div> 
                <div class="tab-pane fade" id="reportCard">
                    <div class="table-responsive">
                        <table id='reportCardTable' class="table card-table table-vcenter text-nowrap datatable border-bottom">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Student Name</th>
                                    <th>Academic Year</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>SA</th>
                                    <th style='width:100px;' class='text-center'>Download</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try{
                                    $loc = $_SERVER['DOCUMENT_ROOT']."/public/archive/report_card"; //file location
                                    //$files = glob("$loc/*.{pdf}", GLOB_BRACE); //only html files
                                    $files = expandDirectories($loc);
                                    //print_r($files);
                                    //die();
                                    $len = count($files);
                                    $i = 0;
                                    $cnt = 1;
                                    $result = array();
                                    $str ="";
                                    while($i<$len){
                                        $fileName = basename($files[$i]);
                                        $fn = explode("_",$fileName);
                                        $studentName = $fn[0];
                                        $fld = str_replace($loc, "", $files[$i]);
                                        $fd = explode("/",$fld);
                                        
                                        //print_r($fd);
                                        $fdlen = count($fd);
                                        //echo "fdlen ".$fdlen;
                                        //die();
                                        $year = $fd[1];
                                        $st_class = $fd[2];
                                        $section = "";
                                        $sa = "";
                                        if($fdlen==7){
                                            $section = $fd[3];
                                            $sa = $fd[4];
                                        }
                                        //echo "\n<br>".$fileName." | ".trim($studentName)."|".$year."|".$st_class."|".$section;
                                        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], "", $files[$i]);
                                        $link = $baseurl."".$relativePath;
                                        $str .="\n<tr>";
                                        $str .="<td>".$cnt."</td>";
                                        $str .="<td>".$studentName."</td>";
                                        $str .="<td>".$year."</td>";
                                        $str .="<td>".$st_class."</td>";
                                        $str .="<td>".$section."</td>";
                                        $str .="<td>".$sa."</td>";
                                        $str .="<td><a href='".$link."' download><i class='mdi mdi-download mr-2'></i>Download</a></td>";
                                        $str .="</tr>";
                                        $cnt++;
                                        $i++;
                                    }
                                    echo $str;
                                }catch(Exception $ex){
                                    echo $ex->getMessage();
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>    
            </div>   
        </div>
    </div>

    <button type="button" id='btnReportParam' data-toggle="modal" data-target="#reportParamDialog"></button>

    <!--Report Dialog-->
    <div class="modal fade" id="reportParamDialog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportDialogTitle">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="reportDialogForm" action="<?=$baseurl."/report_download.php"?>" class="needs-validation" novalidate="" method="post" autocomplete="off">
                    <input type="hidden" name="reportid" id="reportid" value="">
                    <div class="row my-2">
                        <div class="col-12 form-label">Choose Report Type</div>
                        <div class="col-auto">
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" checked value="html" name="fd">
                                <span class="form-check-label">HTML</span>
                            </label>
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" value="ihtml" name="fd">
                                <span class="form-check-label">Interactive HTML</span>
                            </label>
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" value="xlsx" name="fd">
                                <span class="form-check-label">XLSX</span>
                            </label>
                        </div>
                    </div>
                    <div id='paramPanel'></div>
                </form>
                
            </div>
            <div class="modal-footer">
                <button type="button" id='closeDialogBtn' class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="verfiyAndFinalDownload();">Download</button>
            </div>
            </div>
        </div>
    </div>

    <script>
    
        function searchTrans(){
            try{
                var academicYear = $("#transYear").val();
                var term = $("#transTerm").val();
                var stream = $("#transStream").val();
                var studentId = $("#transStudentId").val();
                var studentName = $("#transStudent").val();
                
                $.ajax({
                    url: 'ajax_archive.php',
                    type: 'post',
                    data: {
                        type: "feeTransactions",
                        AcademicYear:academicYear,
                        Term:term,
                        Stream:stream,
                        StudentID:studentId,
                        FullName:studentName,
                    },
                    success: function(response) {
                        //console.log(response);
                    if (response) {
                            var obj = jQuery.parseJSON(response);
                            if(obj.status==1){
                                //console.log(obj.data);
                                json2TableTrans(obj.data);
                            }
                        }
                    }
                });
            }catch(ex){
                console.log(ex);
            }
        }
        var data
        var isTableTransCreated = false;
        var tableDataTrans;
        function json2TableTrans(obj){
            //console.log("indixe object");
            var len = obj.length;
            var i = 0;
            var srn = 1; 
            var str = "";
            if(isTableTransCreated){
                $('#feeTransactionsTable').DataTable().destroy();
            }
            while(i<len){
                str +="\n<tr>";
                str +="\n<td>"+srn+"</td>";
                str +=createTd(obj[i]);
                str +="\n</tr>";
                i++;
                srn++;
            }
            tableDataTrans = str;
            if(str!=""){
                //console.log("str not empty");
                $("#feeTransactionsBody").html(str);
                if(!isTableTransCreated){
                    manageTablePaging("feeTransactionsTable");
                    isTableTransCreated = true;
                }else{
                    //console.log("object "+str);
                    $('#feeTransactionsTable').DataTable().draw();
                    $(".dataTables_length").find("select").css("width", "90px");
                    $(".dataTables_length").find("select").css("display", "inline-block");
                }
            }
        }

    </script>

    <script>
        function searchInvoice(){
            try{
                var academicYear = $("#invoiceYear").val();
                var term = $("#invoiceTerm").val();
                var stream = $("#invoiceStream").val();
                var studentId = $("#invoiceStudentId").val();
                var studentName = $("#invoiceStudent").val();
                
                $.ajax({
                    url: 'ajax_archive.php',
                    type: 'post',
                    data: {
                        type: "feeInvoice",
                        AcademicYear:academicYear,
                        Term:term,
                        Stream:stream,
                        StudentID:studentId,
                        Name:studentName,
                    },
                    success: function(response) {
                        //console.log(response);
                        if (response) {
                            var obj = jQuery.parseJSON(response);
                            if(obj.status==1){
                                //console.log(obj.data);
                                json2Table(obj.data);
                            }
                        }
                    }
                });
            }catch(ex){
                console.log(ex);
            }
        }

        var isTableCreated = false;
        function json2Table(obj){
            var len = obj.length;
            var i = 0;
            var srn = 1; 
            var str = "";
            if(isTableCreated){
                $('#feeInvoiceTable').DataTable().destroy();
            }
            while(i<len){
                str +="\n<tr>";
                str +="\n<td>"+srn+"</td>";
                str +=createTd(obj[i]);
                str +="\n</tr>";
                i++;
                srn++;
            }
            if(str!=""){
                $("#feeInvoiceBody").html(str);

                $('#feeInvoiceBody').find('tr').find('td:nth-child(33)').remove();
                $('#feeInvoiceBody').find('tr').find('td:nth-child(34)').remove();
                $('#feeInvoiceBody').find('tr').find('td:last').remove();
                if(!isTableCreated){
                    manageTablePaging("feeInvoiceTable");
                    isTableCreated = true;
                }else{
                    $('#feeInvoiceTable').DataTable().draw();
                    $(".dataTables_length").find("select").css("width", "90px");
                    $(".dataTables_length").find("select").css("display", "inline-block");
                }
            }
        }

        function createTd(obj){
            var str = "";
            for( var key in obj ) {
                var value = obj[key];
                str +="\n<td>"+value+"</td>";
                //console.log(value);
            }
            return str;
        }
    </script>
    <script>
        $(document).ready(function(){
            $(".card-body").removeClass("card-body");
            $(".card-bodyNew").addClass("card-body").removeClass("card-bodyNew");
            
            $(".nav-tabs a").click(function(e){
                e.preventDefault();
                $(this).tab('show');
            });
        });

    </script>    
    <script>
        var baseurl = "<?=$baseurl;?>";
        var report = <?php echo json_encode($repo); ?>;
        var isParamActive = false;
        var activeDownloadId = "";

        function isEmpty(str) {
            return (!str || str.length === 0 );
        }

        function downloadReport(id){
            isParamActive = false;
            var obj = report[id];
            var str = "";
            $("#reportid").val(id);
            activeDownloadId = id;
            $("#reportDialogTitle").text(obj["name"]);
            //date and condition
            str +="<div class='row'>";
            str +=addDate(obj["date1"],"date1");
            str +=addDate(obj["date2"],"date2");
            str +=addDate(obj["date3"],"date3");
            str +=addDate(obj["date4"],"date4");
            str +="</div>";
            str +="<div class='row'>";
            str +=addParam(obj["param1"],"param1");
            str +=addParam(obj["param2"],"param2");
            str +=addParam(obj["param3"],"param3");
            str +=addParam(obj["param4"],"param4");
            str +=addParam(obj["param5"],"param5");
            str +=addParam(obj["param6"],"param6");
            str +=addParam(obj["param7"],"param7");
            str +=addParam(obj["param8"],"param8");
            str +="</div>";
            
            $("#paramPanel").html(str);
            //$('#reportParamDialog').modal('show');
            $("#btnReportParam").click();
            //wait form param input
            
        }

        function addDate(pdate, pdateid){
            var str = "";
            if(!isEmpty(pdate)){
                str +="\n<div class='col-auto mt-2'>";
                str +="<label class='form-label required'>"+pdate+"</label>";
                str +="<input type='date' name='"+pdateid+"' class='form-control reqParam' id='"+pdateid+"'>";
                str +="</div>";
                isParamActive = true;
            }
            return str;
        }

        function verfiyAndFinalDownload(){
            var isDownloadValid = true;
            $('.reqParam').each(function() {
                var currentElement = $(this);
                var value = currentElement.val();
                if (value == "") {
                    alert("Please enter all valid parameters.");
                    currentElement.focus();
                    isDownloadValid = false;
                } // if it is an input/select/textarea field
                // TODO: do something with the value
            });
            if(isDownloadValid){
                finalDownload();
            }
        }

        function finalDownload(){
            if(activeDownloadId){
                try{
                    $("#closeDialogBtn").click();
                    console.log("Your report is downloading..");
                    $('#reportDialogForm').submit();
                }catch(ex){
                    console.log(ex);
                }
            }
        }

        function addParam(param, paramid){
            var str = "";
            if(!isEmpty(param)){
                str +="\n<div class='col-auto mt-2'>";
                str +="<label class='form-label required'>"+param+"</label>";
                str +="<input type='text' name='"+paramid+"' class='form-control reqParam' id='"+paramid+"'>";
                str +="</div>";
                isParamActive = true;
            }
            return str;
        }
    </script>

    <script>
        $(document).ready(function() {
            $("#btnReportParam").hide();
            $("#addReport").hide();
            $('#reportTable, #feeReciptTable, #reportCardTable').DataTable({
                "pageLength": 25,
                "lengthMenu": [
                    [10, 25, 50, 250, -1],
                    [10, 25, 50, 250, "All"]
                ],
                "sDom": '<"top"lpf>rt<"bottom"ipf><"clear">'
            });
            $(".dataTables_length").find("select").css("width", "90px");
            $(".dataTables_length").find("select").css("display", "inline-block");
        });

        function manageTablePaging(tableid){
            $('#'+tableid).DataTable({
                "pageLength": 25,
                "lengthMenu": [
                    [10, 25, 50, 250, -1],
                    [10, 25, 50, 250, "All"]
                ],
                "sDom": '<"top"lpf>rt<"bottom"ipf><"clear">'
            });
            $(".dataTables_length").find("select").css("width", "90px");
            $(".dataTables_length").find("select").css("display", "inline-block");
        }
    </script>
<?php
}
