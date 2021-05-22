<?php
/*
Pupilsight, Flexible & Open School System
 */

use Pupilsight\Domain\Helper\HelperGateway;

$accessFlag = true;
if ($accessFlag == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!


    if (isset($_POST['name'])) {

        $name = "'" . trim($_POST['name']) . "'";

        $description = empty($_POST['description']) ? "NULL" : "'" . trim($_POST['description']) . "'";
        $module = empty($_POST['module']) ? "NULL" : "'" . trim($_POST['module']) . "'";
        $module_id = empty($_POST['module_id']) ? "NULL" : "'" . trim($_POST['module_id']) . "'";
        $sql_query = empty($_POST['sql_query']) ? "NULL" : "'" . addslashes(htmlspecialchars(trim($_POST['sql_query']))) . "'";
        $api = empty($_POST['api']) ? "NULL" : "'" . addslashes(htmlspecialchars((trim($_POST['api'])))) . "'";

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
            $sq = "insert into report_manager (name, description, module, module_id, sql_query, api, date1, date2, date3, date4, param1, param2, param3, param4, param5, param6, param7, param8,status) 
            values($name,$description,$module,$module_id,$sql_query,$api,$date1,$date2,$date3,$date4,$param1,$param2,$param3,$param4,$param5,$param6,$param7,$param8,2)";
            //echo $sq;
            $connection2->query($sq);
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
            <form id="reportForm" action="index.php?q=/modules/Reports/index.php" class="needs-validation" novalidate="" method="post" autocomplete="off">
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
                        <label class="form-label">Report SQL Query</label>
                        <textarea id="sqlquery" name="sqlquery" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="col-12 mt-4 text-center">
                        <label class="form-label bg-blue-lt">OR</label>
                    </div>

                    <div class="col-12 mt-2">
                        <label class="form-label">Report API</label>
                        <div class="input-group">
                            <span class="input-group-text">https://testchristacademy.pupilpod.net/</span>
                            <input type="text" id="api" name="api" class="form-control" value="" placeholder="relative path eg. ajaxdata.php?val=123">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mt-4 text-center">
                        <label class="form-label bg-green-lt">Input Parameters (Optional)</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mt-2 col-sm-12">
                        <label class="form-label text-center">Date1 Parameter and Date2 Parameter</label>
                    </div>
                    <div class="col-3 mt-2">
                        <label class="form-label">Date3 Parameter</label>
                    </div>
                    <div class="col-3 mt-2">
                        <label class="form-label">Date4 Parameter</label>
                    </div>

                    <div class="col-3 mt-1">
                        <input type="text" id="date1" name="date1" class="form-control param" value="" placeholder="startDate">
                    </div>
                    <div class="col-3 mt-1">
                        <input type="text" id="date1" name="date2" class="form-control param" value="" placeholder="endDate">
                    </div>

                    <div class="col-3 mt-1">
                        <input type="text" id="date1" name="date3" class="form-control param" value="" placeholder="specialDate">
                    </div>
                    <div class="col-3 mt-1">
                        <input type="text" id="date1" name="date4" class="form-control param" value="" placeholder="dob">
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
                    <button type="button" class="btn btn-primary" onclick="validate();">Submit</button>
                    <button type="button" class="btn btn-secondary ml-1" onclick="cancelReport();">Cancel</button>
                </div>
            </div>
            </form>
        </div>
    </div>
    <!----Report Details---->
    <div class="card my-2" id='reportList'>
        <div class="card-header text-right">
            <button type="button" class="btn btn-primary" onclick="addReport();"><i class="mdi mdi-plus"></i> New Report</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap datatable border-bottom">
                    <thead>
                        <tr>
                            <th>Report Name</th>
                            <th>Module Name</th>
                            <th>Download</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $helperGateway = $container->get(HelperGateway::class);
                        $res = $helperGateway->getBasicActiveReport($connection2);
                        $len = count($res);
                        $i = 0;
                        $str = "";
                        while($i<$len){
                            $str .="\n<tr>";
                            $str .="\n<td>".$res[$i]["name"]."<br>".$res[$i]["description"]."</td>";
                            $str .="\n<td>".$res[$i]["module"]."</td>";
                            $str .="\n<td><button type='button' class='btn btn-white' onclick=\"downloadReport('".$res[$i]['id']."');\"><i class='mdi mdi-download mr-2'></i>Download</button></td>";
                            $str .="\n</tr>";
                            $i++;
                        }
                    ?>  
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        function downloadReport(id){
            alert(id);
        }
    </script>
    <script>
        $(document).ready(function() {
            $("#addReport").hide();
        });

        function addReport() {
            $("#addReport").show(400);
        }

        function cancelReport() {
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
            var sqlquery = $("#sqlquery").val();
            if (sqlquery != "") {
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
