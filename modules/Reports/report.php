<?php
/*
Pupilsight, Flexible & Open School System
 */

$accessFlag = true;
if ($accessFlag == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Report List'));

    ?>
<div class="card my-2" id='addReport'>
    <div class="card-header">
        <h2>Add / Modify Report</h2>
    </div>
  <div class="card-body">
    <form action="." class="needs-validation" novalidate="" method="post" autocomplete="off">
        <div class="row">
            <div class="col-md-6 col-sm-12 mt-2">
                <label class="form-label">Report Name</label>
                <input type="hidden" id="id" name="id" value="">
                <input type="text" id="name" name="name" class="form-control" value="">
            </div>
            <div class="col-md-6 col-sm-12 mt-2">
                <label class="form-label">Module</label>
                <select id="module" name="module" class="form-control">
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

            <div class="col-3 mt-2">
                <label class="form-label">Date1 Parameter</label>
                <input type="text" id="date1" name="date" class="form-control" value="" placeholder="startDate">
            </div>
            <div class="col-3 mt-2">
                <label class="form-label">Date2 Parameter</label>
                <input type="text" id="date1" name="date" class="form-control" value="" placeholder="endDate">
            </div>
            <div class="col-3 mt-2">
                <label class="form-label">Date3 Parameter</label>
                <input type="text" id="date1" name="date" class="form-control" value="" placeholder="specialDate">
            </div>
            <div class="col-3 mt-2">
                <label class="form-label">Date4 Parameter</label>
                <input type="text" id="date1" name="date" class="form-control" value="" placeholder="dob">
            </div>


            <div class="col-3 mt-2">
                <label class="form-label">Parameter1</label>
                <input type="text" id="parameter1" name="parameter1" class="form-control" value="" placeholder="age">
            </div>
            <div class="col-3 mt-2">
                <label class="form-label">Parameter2</label>
                <input type="text" id="parameter2" name="parameter2" class="form-control" value="" placeholder="name">
            </div>
            <div class="col-3 mt-2">
                <label class="form-label">Parameter3</label>
                <input type="text" id="parameter3" name="parameter3" class="form-control" value="" placeholder="">
            </div>
            <div class="col-3 mt-2">
                <label class="form-label">Parameter4</label>
                <input type="text" id="parameter4" name="parameter4" class="form-control" value="" placeholder="">
            </div>

            <div class="col-3 mt-2">
                <label class="form-label">Parameter5</label>
                <input type="text" id="parameter5" name="parameter5" class="form-control" value="" placeholder="">
            </div>
            <div class="col-3 mt-2">
                <label class="form-label">Parameter6</label>
                <input type="text" id="parameter6" name="parameter6" class="form-control" value="" placeholder="">
            </div>
            <div class="col-3 mt-2">
                <label class="form-label">Parameter7</label>
                <input type="text" id="parameter7" name="parameter7" class="form-control" value="" placeholder="">
            </div>
            <div class="col-3 mt-2">
                <label class="form-label">Parameter8</label>
                <input type="text" id="parameter8" name="parameter8" class="form-control" value="" placeholder="">
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-12 my-3">
                <button type="submit" class="btn btn-primary">Submit</button>
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
                <th>Report Description</th>
                <th>Download</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                Test Report
                </td>
                <td>
                Test report for testing
                </td>
                <td>
                <button type='button' class="btn btn-white"><i class="mdi mdi-download mr-2"></i>Download</button
                </td>
            </tr>
            </tbody>
        </table>
    </div>
  </div>
</div>

<script>
    $( document ).ready(function() {
    $("#addReport").hide();
});
    function addReport(){
        $("#addReport").show(400);
    }

    function cancelReport(){
        $("#addReport").hide(400);
    }
</script>

<?php
}