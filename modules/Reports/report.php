<?php
/*
Pupilsight, Flexible & Open School System
 */

use Pupilsight\Domain\Helper\HelperGateway;
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
$baseurl = getDomain();

$accessFlag = true;
if ($accessFlag == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $roleid = $_SESSION[$guid]["pupilsightRoleIDPrimary"];
    //Proceed!
    //print_r($_SESSION[$guid]);
    //die();
/*
    // Check if SELECT is in the query
if (preg_match('/SELECT/', strtoupper($query)) != 0) {
    // Array with forbidden query parts
    $disAllow = array(
        'INSERT',
        'UPDATE',
        'DELETE',
        'RENAME',
        'DROP',
        'CREATE',
        'TRUNCATE',
        'ALTER',
        'COMMIT',
        'ROLLBACK',
        'MERGE',
        'CALL',
        'EXPLAIN',
        'LOCK',
        'GRANT',
        'REVOKE',
        'SAVEPOINT',
        'TRANSACTION',
        'SET',
    );

    // Convert array to pipe-seperated string
    // strings are appended and prepended with \b
    $disAllow = implode('|',
        array_map(function ($value) {
            return '\b' . $value . '\b';
        }
    ), $disAllow);

    // Check if no other harmfull statements exist
    if (preg_match('/('.$disAllow.')/gai', $query) == 0) {
        // Execute query
    }
}*/

    if (isset($_POST['name'])) {

        $name = "'" . trim($_POST['name']) . "'";

        $description = empty($_POST['description']) ? "NULL" : "'" . trim($_POST['description']) . "'";
        $module = empty($_POST['module']) ? "NULL" : "'" . trim($_POST['module']) . "'";
        $module_id = empty($_POST['module_id']) ? "NULL" : "'" . trim($_POST['module_id']) . "'";
        $sql_query = empty($_POST['sql_query']) ? "NULL" : "'" . htmlspecialchars(trim($_POST['sql_query']),ENT_QUOTES) . "'";
        $api = empty($_POST['api']) ? "NULL" : "'" . htmlspecialchars((trim($_POST['api'])),ENT_QUOTES) . "'";

        $header = empty($_POST['header']) ? "NULL" : "'" . trim($_POST['header']) . "'";
        $total_column = empty($_POST['total_column']) ? "NULL" : "'" . trim($_POST['total_column']) . "'";

        $date1 = empty($_POST['date1']) ? "NULL" : "'" . trim($_POST['date1']) . "'";
        $date2 = empty($_POST['date2']) ? "NULL" : "'" . trim($_POST['date2']) . "'";
        $date3 = empty($_POST['date3']) ? "NULL" : "'" . trim($_POST['date3']) . "'";
        $date4 = empty($_POST['date4']) ? "NULL" : "'" . trim($_POST['date4']) . "'";

        $param1 = empty($_POST['param1']) ? "NULL" : "'" . trim($_POST['param1']) . "'";
        $param2 = empty($_POST['param2']) ? "NULL" : "'" . trim($_POST['param2']) . "'";
        $param3 = empty($_POST['param3']) ? "NULL" : "'" . trim($_POST['param3']) . "'";
        $param4 = empty($_POST['param4']) ? "NULL" : "'" . trim($_POST['param4']) . "'";
        $param5 = empty($_POST['param5']) ? "NULL" : "'" . trim($_POST['param5']) . "'";
        $param6 = empty($_POST['param6']) ? "NULL" : "'" . trim($_POST['param6']) . "'";
        $param7 = empty($_POST['param7']) ? "NULL" : "'" . trim($_POST['param7']) . "'";
        $param8 = empty($_POST['param8']) ? "NULL" : "'" . trim($_POST['param8']) . "'";

        //print_r($_POST);
        try {
            if(!empty($_POST["id"])){
                //update
                $id = $_POST["id"];
                $sq = "update report_manager set name=$name, ";
                $sq .= " description=$description, ";
                $sq .= " module=$module, ";
                $sq .= " module_id=$module_id, ";
                $sq .= " sql_query=$sql_query, ";
                $sq .= " header=$header, ";
                $sq .= " total_column=$total_column, ";
                $sq .= " api=$api, ";
                $sq .= " date1=$date1, ";
                $sq .= " date2=$date2, ";
                $sq .= " date3=$date3, ";
                $sq .= " date4=$date4, ";
                $sq .= " param1=$param1, ";
                $sq .= " param2=$param2, ";
                $sq .= " param3=$param3, ";
                $sq .= " param4=$param4, ";
                $sq .= " param5=$param5, ";
                $sq .= " param6=$param6, ";
                $sq .= " param7=$param7, ";
                $sq .= " param8=$param8 ";
                $sq .= "where id='".$id."' ";
            }else{
                $sq = "insert into report_manager (name, description, module, module_id, sql_query, header, total_column, api, date1, date2, date3, date4, param1, param2, param3, param4, param5, param6, param7, param8,status) 
                values($name,$description,$module,$module_id,$sql_query,$header,$total_column,$api,$date1,$date2,$date3,$date4,$param1,$param2,$param3,$param4,$param5,$param6,$param7,$param8,2)";
            }
            //echo $sq;
            $connection2->query($sq);
            //die();
            header('Location: '.$_SERVER['REQUEST_URI']);
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }

        //die();
    }
    $page->breadcrumbs->add(__('Report List'));

?>
    <div class="card my-2" id='addReport'>
        <div class="card-header">
            <h2>Add / Modify Report</h2>
        </div>
        <div class="card-body">
            <form id="reportForm" action="index.php?q=/modules/Reports/report.php" class="needs-validation" novalidate="" method="post" autocomplete="off">
                <div class="row">
                    <div class="col-md-6 col-sm-12 mt-2">
                        <label class="form-label required">Report Name</label>
                        <input type="hidden" id="id" name="id" value="">
                        <input type="text" id="name" name="name" class="form-control" value="" required>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2">
                        <label class="form-label required">Module</label>
                        <input type="hidden" id="module" name="module" value="">
                        <script>
                            function selectModule() {
                                var module = $("#module_id option:selected").text();
                                $("#module").val(module);
                            }
                        </script>
                        <select id="module_id" name="module_id" class="form-control" onchange="selectModule()" required>
                            <option value="">Select Module</option>
                            <option value="0180">Academics</option>
                            <option value="0015">Activities</option>
                            <option value="0153">Alumni</option>
                            <option value="0145">ATL</option>
                            <option value="0006">Attendance</option>
                            <option value="0155">Badges</option>
                            <option value="0119">Behaviour</option>
                            <option value="0178">Campaign</option>
                            <option value="0012">Crowd Assessment</option>
                            <option value="0008">Data Updater</option>
                            <option value="0004">Departments</option>
                            <option value="0160">Feed</option>
                            <option value="0135">Finance</option>
                            <option value="0016">Formal Assessment</option>
                            <option value="0157">Help Desk</option>
                            <option value="0158">Higher Education</option>
                            <option value="0011">Individual Needs</option>
                            <option value="0130">Library</option>
                            <option value="0007">Markbook</option>
                            <option value="0121">Messenger</option>
                            <option value="0159">Moodle</option>
                            <option value="0009">Planner</option>
                            <option value="0137">Roll Groups</option>
                            <option value="0126">Rubrics</option>
                            <option value="0001">School Admin</option>
                            <option value="0136">Staff</option>
                            <option value="0005">Students</option>
                            <option value="0003">System Admin</option>
                            <option value="0014">Timetable</option>
                            <option value="0013">Timetable Admin</option>
                            <option value="0141">Tracking</option>
                            <option value="0179">Transport</option>
                            <option value="0002">User Admin</option>
                        </select>
                    </div>

                    <div class="col-12 mt-2">
                        <label class="form-label">Report Description</label>
                        <textarea id="description" name="description" class="form-control"></textarea>
                    </div>
                </div>

                <div class="row my-4">
                    <div class="col-12 mt-2">
                        <label class="form-label">Report SQL Query - Note pass variable like <span class='bg-blue-lt'>payment_date=':date1' or payment_amount=':param1'</span> assign variable in sql query</label>
                        <textarea id="sql_query" name="sql_query" data-bs-toggle="autosize" class="form-control"></textarea>
                    </div>

                    <div class="col-12 mt-4 text-center">
                        <label class="form-label bg-blue-lt">OR</label>
                    </div>

                    <div class="col-12 mt-2">
                        <label class="form-label">Report Function Name</label>
                        <div class="input-group">
                            <span class="input-group-text">$Report-></span>
                            <input type="text" id="api" name="api" class="form-control" value="" placeholder="your function name eg. studentreport">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mt-2">
                        <label class="form-label">Total column name by sql query or function call for auto sum</label>
                        <input type="text" id="total_column" name="total_column" class="form-control" value="">
                    </div>

                    <div class="col-12 mt-2">
                        <label class="form-label">Header value coma separated eg like <span class='bg-blue-lt'>Name, Grade, Payment Date</span> For report if you want to change mysql column name</label>
                        <textarea id="header" name="header" rows='2' class="form-control"></textarea>
                    </div>
                </div>


                <div class="row">
                    <div class="col-12 mt-4 text-center">
                        <label class="form-label bg-green-lt">Input Parameters (Optional) used by sql query or function call</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3 mt-1">
                        <label class="form-label">Date1 Parameter</label>
                        <input type="text" id="date1" name="date1" class="form-control param" value="" placeholder="startDate">
                    </div>
                    <div class="col-3 mt-1">
                        <label class="form-label">Date2 Parameter</label>
                        <input type="text" id="date2" name="date2" class="form-control param" value="" placeholder="endDate">
                    </div>

                    <div class="col-3 mt-1">
                        <label class="form-label">Date3 Parameter</label>
                        <input type="text" id="date3" name="date3" class="form-control param" value="" placeholder="specialDate">
                    </div>
                    <div class="col-3 mt-1">
                        <label class="form-label">Date4 Parameter</label>
                        <input type="text" id="date4" name="date4" class="form-control param" value="" placeholder="dob">
                    </div>


                    <div class="col-3 mt-2">
                        <label class="form-label">Parameter1</label>
                        <input type="text" id="param1" name="param1" class="form-control param" value="" placeholder="age">
                    </div>
                    <div class="col-3 mt-2">
                        <label class="form-label">Parameter2</label>
                        <input type="text" id="param2" name="param2" class="form-control param" value="" placeholder="name">
                    </div>
                    <div class="col-3 mt-2">
                        <label class="form-label">Parameter3</label>
                        <input type="text" id="param3" name="param3" class="form-control param" value="" placeholder="">
                    </div>
                    <div class="col-3 mt-2">
                        <label class="form-label">Parameter4</label>
                        <input type="text" id="param4" name="param4" class="form-control param" value="" placeholder="">
                    </div>

                    <div class="col-3 mt-2">
                        <label class="form-label">Parameter5</label>
                        <input type="text" id="param5" name="param5" class="form-control param" value="" placeholder="">
                    </div>
                    <div class="col-3 mt-2">
                        <label class="form-label">Parameter6</label>
                        <input type="text" id="param6" name="param6" class="form-control param" value="" placeholder="">
                    </div>
                    <div class="col-3 mt-2">
                        <label class="form-label">Parameter7</label>
                        <input type="text" id="param7" name="param7" class="form-control param" value="" placeholder="">
                    </div>
                    <div class="col-3 mt-2">
                        <label class="form-label">Parameter8</label>
                        <input type="text" id="param8" name="param8" class="form-control param" value="" placeholder="">
                    </div>
                </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-12 my-3">
                    <button type="button" class="btn btn-primary" onclick="validate();">Save</button>
                    <button type="button" class="btn btn-secondary ml-1" onclick="cancelReport();">Cancel</button>
                </div>
            </div>
            </form>
        </div>
    </div>
    <!----Report Details---->
    <div class="card my-2" id='reportList'>
        <div class="card-header">
        <?php
            $helperGateway = $container->get(HelperGateway::class);
            if($roleid=="001"){
                echo '<button type="button" class="btn btn-primary" onclick="addReport();"><i class="mdi mdi-plus"></i> New Report</button>';
                $res = $helperGateway->getActiveReport($connection2);
            }else{
                $res = $helperGateway->getBasicActiveReport($connection2);
            }
        ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id='reportTable' class="table card-table table-vcenter text-nowrap datatable border-bottom">
                    <thead>
                        <tr>
                            <th>Report Name</th>
                            <th style='width:110px;' class='text-center'>Module Name</th>
                            <th style='width:100px;' class='text-center'>Download</th>
                            <?php
                                if($roleid=="001"){
                                    echo "<th style='width:60px;' class='text-center'>Edit</th>";
                                }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $len = count($res);
                        $i = 0;
                        $str = "";
                        $repo = array();
                        while($i<$len){
                            $str .="\n<tr>";
                            $str .="\n<td><strong>".ucwords($res[$i]["name"])."</strong><br><span class='text-muted'>".$res[$i]["description"]."</span></td>";
                            $str .="\n<td class='text-center'>".$res[$i]["module"]."</td>";
                            $str .="\n<td><button type='button' class='btn btn-link' onclick=\"downloadReport('".$res[$i]['id']."');\"><i class='mdi mdi-download mr-2'></i>Download</button></td>";
                            if($roleid=="001"){
                                if($res[$i]["sql_query"]){
                                    $res[$i]["sql_query"] = htmlspecialchars_decode($res[$i]["sql_query"], ENT_QUOTES);
                                }
                                $str .="\n<td><button type='button' class='btn btn-link' onclick=\"editReport('".$res[$i]['id']."');\"><i class='mdi mdi-edit mr-2'></i>Edit</button></td>";
                            }
                            $str .="\n</tr>";
                            $res[$i]["name"] = ucwords($res[$i]["name"]);
                            $repo[$res[$i]['id']]=$res[$i];
                            $i++;
                        }
                        echo $str;
                    ?>  
                    </tbody>
                </table>
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
    <?php
    if($roleid=="001"){
    ?>
    <script>
        function editReport(id){
            var obj = report[id];
            var elements = ["id","name","header","total_column","module_id","module","description","sql_query","api","date1","date2","date3","date4","param1","param2","param3","param4","param5","param6","param7","param8"];
            var len = elements.length;
            var i = 0;
            while(i<len){
                setVal(elements[i],obj);
                i++;
            }
            autosize($('#sql_query'));
            if(isEmpty(obj["sql_query"])){
                autosize.update(obj["sql_query"]);
            }
            $("#addReport").show(400);
        }

        function setVal(id, obj){
            if(!isEmpty(obj[id])){
                $("#"+id).val(obj[id]);
            }
        }
    </script>
    <?php
    }
    ?>
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
                str +="\n<div class='col-auto'>";
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
                    //return;
                    /*
                    var form = $('#reportDialogForm')[0];
                    var _data = new FormData(form);
                    //data.append("CustomField", "This is some extra data, testing");
 
                    $.ajax({
                        url: 'report_download.php',
                        type: 'post',
                        data: _data,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            console.log(response);
                            if(response){
                                //var res = $.trim(response);
                                //alert(res);
                                try{
                                var obj = JSON.parse(response);
                                if(obj.file){
                                    console.log(obj.file);
                                    //$("#fileDownload").attr("href",obj.file);
                                    //$("#fileDownload")[0].click();
                                    //$("#fileDownload").click();

                                }
                                }catch(ex){
                                    console.log(ex);
                                }
                            }
                        }
                    });*/
                }catch(ex){
                    console.log(ex);
                }
            }
        }

        function addParam(param, paramid){
            var str = "";
            if(!isEmpty(param)){
                str +="\n<div class='col-auto'>";
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
            $('#reportTable').DataTable({
                "pageLength": 25,
                "lengthMenu": [
                    [10, 25, 50, 250, -1],
                    [10, 25, 50, 250, "All"]
                ],
                "sDom": '<"top"lpf>rt<"bottom"ipf><"clear">'
            });
            $(".dataTables_length").find("select").css("width", "90px");
            $(".dataTables_length").find("select").css("display", "inline-block");
            autosize($('#sql_query'));
        });

        function resetAddReport(){
            var elements = ["id","name","header","total_column","module_id","module","description","sql_query","api","date1","date2","date3","date4","param1","param2","param3","param4","param5","param6","param7","param8"];
            var len = elements.length;
            var i = 0;
            while(i<len){
                $("#"+elements[i]).val("");
                i++;
            }
        }

        function addReport() {
            resetAddReport();
            $("#addReport").show(400);
        }

        function cancelReport() {
            resetAddReport();
            $("#addReport").hide(400);
        }

        function validate() {
            var flag = validElement("name", "Enter Module Name");
            if (!flag) {
                return;
            }

            var flag = validElement("module_id", "Select Module");
            if (!flag) {
                return;
            }

            var noquery = false;
            var sql_query = $("#sql_query").val();
            if (sql_query != "") {
                noquery = true;
            }

            var api = $("#api").val();
            if (api != "") {
                noquery = true;
            }
            if (!noquery) {
                alert("You have not entered any sql query or api please enter and continue");
                return;
            }

            var paramFlag = false;
            $('.param').each(function() {
                var currentElement = $(this);
                var value = currentElement.val();
                if (value != "") {
                    paramFlag = true;
                } // if it is an input/select/textarea field
                // TODO: do something with the value
            });

            if (!paramFlag) {
                if (confirm("Are you sure to continue.You have not added any optional parameter for this report")) {
                    $("form#reportForm").submit();
                }
            } else {
                $("form#reportForm").submit();
            }
        }

        function validElement(id, msg) {
            var element = $("#" + id).val();
            if (element == "") {
                alert(msg);
                $("#" + id).focus();
                return false;
            }
            return true;
        }
    </script>
    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function() {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')

            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }

                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>

<?php
}
