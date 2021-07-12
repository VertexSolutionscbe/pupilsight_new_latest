<?php

use Pupilsight\Domain\Calendar\CalendarGateway;
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


function isPost($postid)
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST[$postid])) {
        if (empty($_POST[$postid])) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    return FALSE;
}

function getPost($postid)
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST[$postid])) {
        if (empty($_POST[$postid])) {
            return NULL;
        } else {
            return $_POST[$postid];
        }
    }
    return NULL;
}

function getIntPost($postid)
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST[$postid])) {
        if (empty($_POST[$postid])) {
            return "";
        } else {
            return (int)($_POST[$postid]);
        }
    }
    return "";
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
    $uid = $_SESSION[$guid]['pupilsightPersonID'];
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $ts = date('Y-m-d H:i:s');
    if (isset($_POST['title'])) {

        $title = "'" . trim($_POST['title']) . "'";
        $details = NULL;
        if (!empty($_POST['details'])) {
            $details = htmlentities(htmlspecialchars($_POST['details']));
        }


        //$details = empty($_POST['details']) ? "NULL" : "'" . trim($_POST['details']) . "'";
        $event_type_id = empty($_POST['event_type_id']) ? "NULL" : "'" . trim($_POST['event_type_id']) . "'";
        $location = empty($_POST['location']) ? "NULL" : "'" . trim($_POST['location']) . "'";

        //$is_all_day_event = empty($_POST['is_all_day_event']) ? "NULL" : "'" . trim($_POST['is_all_day_event']) . "'";


        $start_time_unix = strtotime($_POST['start_date']);
        $start_date = date('Y-m-d', $start_time_unix);

        $end_time_unix = strtotime($_POST['end_date']);
        $end_date = date('Y-m-d', $end_time_unix);
        $start_time = NULL;
        $end_time = NULL;

        $is_all_day_event = 2;
        if (!isset($_POST['is_all_day_event'])) {
            $is_all_day_event = 1;

            $start_time = date('H:i:s', strtotime($_POST['start_time']));
            $end_time = date('H:i:s', strtotime($_POST['end_time']));
            //echo "StartDate Time ".$start_date." ".$start_time;
            //echo "EndDate Time ".$end_date." ".$end_time;
            $start_time_unix = strtotime($start_date . " " . $start_time);
            $end_time_unix = strtotime($end_date . " " . $end_time);
        }

        try {
            if (!empty($_POST["id"])) {
                //update
                $id = $_POST["id"];
                $sq = "update calendar_event set title=$title, ";
                $sq .= " details='$details', ";
                $sq .= " event_type_id=$event_type_id, ";
                $sq .= " location=$location, ";
                $sq .= " start_date='$start_date', ";
                $sq .= " start_time='$start_time', ";
                $sq .= " end_date='$end_date', ";
                $sq .= " end_time='$end_time', ";
                $sq .= " start_time_unix=$start_time_unix, ";
                $sq .= " end_time_unix=$end_time_unix, ";
                $sq .= " is_all_day_event=$is_all_day_event, ";
                $sq .= " udt=$ts ";
                $sq .= "where id='" . $id . "' ";
            } else {
                $sq = "insert into calendar_event (title, details, event_type_id, location, start_date, start_time, end_date, end_time, start_time_unix, end_time_unix, is_all_day_event, is_publish, block_attendance, cdt, udt) 
                values($title,'$details',$event_type_id,$location,'$start_date','$start_time','$end_date','$end_time',$start_time_unix,$end_time_unix,$is_all_day_event,1,1,'$ts','$ts')";
            }
            //echo $sq;
            //die();
            $connection2->query($sq);

            $res["status"] = 1;
            $res["msg"] = "Event saved successfully.";
            $_SESSION["notify"] = $res;
            //die();
            header('Location: ' . $_SERVER['REQUEST_URI']);
        } catch (Exception $ex) {
            $res["status"] = 2;
            $res["msg"] = addslashes($ex->getMessage());
            $_SESSION["notify"] = $res;
        }

        //die();
    } else if (isset($_POST['eventid'])) {
        //event publish

        if (!empty($_POST['eventid'])) {
            $res["status"] = 1;
            $res["msg"] = "Event published successfully.";
            $eventid = $_POST['eventid'];
            $delivery_type = getPost('delivery_type'); //bulk target
            $program = getIntPost('program');

            $program_name = getPost('program_name');
            $class_name = getPost('class_name');
            $section_name = getPost('section_name');

            $class = getIntPost('class');
            $section = getIntPost('section');
            $students = $_POST['program'];

            $tag = "";
            $tagid = "";
            $flag = false;
            if ($program) {
                $tagid .= $program;
                $tag .= $program_name;
                $flag = true;
            }

            if ($class) {
                if ($flag) {
                    $tagid .= "-";
                    $tag .= " - ";
                }
                $tagid .= $class;
                $tag .= $class_name;
                $flag = true;
            }

            if ($section) {
                if ($flag) {
                    $tagid .= "-";
                    $tag .= " - ";
                }
                $tagid .= $section;
                $tag .= $section_name;
                $flag = true;
            }

            if (!$flag) {
                $tagid = $delivery_type;
                $tag = $delivery_type;
            }

            $squ = "update calendar_event set tag='" . $tag . "', tagid='" . $tagid . "' ";
            $squ .= ", is_publish='2' ";

            if (isPost("block_attendance")) {
                $squ .= ", block_attendance='2' ";
            } else {
                $squ .= ", block_attendance='1' ";
            }

            if (isPost("sms_notify")) {
                $squ .= ", sms_notify='2' ";
            } else {
                $squ .= ", sms_notify='1' ";
            }

            $squ .= "and udt='" . $ts . "' ";
            $squ .= "where id='" . $eventid . "' ";

            $connection2->query($squ);
            $res["status"] = 1;
            $res["msg"] = "Event published.";
        } else {
            $res["status"] = 2;
            $res["msg"] = "Invalid Event Parameters.";
        }
        $_SESSION["notify"] = $res;
        header('Location: ' . $_SERVER['REQUEST_URI']);
    }
    $page->breadcrumbs->add(__('Manage Events'));

    $calGateway = $container->get(CalendarGateway::class);
    $res = $calGateway->listEventType($connection2);

?>
    <script>
        $(document).ready(function() {
            <?php
            if (isset($_SESSION["notify"])) {
                if ($_SESSION["notify"]["status"] == 1) {
                    echo "toast('success','" . $_SESSION["notify"]["msg"] . "');";
                } else {
                    echo "toast('error',\"" . $_SESSION["notify"]["msg"] . "\");";
                }
            }
            ?>

        });
    </script>
    <style>
        .only-timepicker .datepicker--nav,
        .only-timepicker .datepicker--content {
            display: none;
        }

        .only-timepicker .datepicker--time {
            border-top: none;
        }

        select[multiple] {
            min-height: 36px !important;
        }
    </style>
    <div class="card my-2" id='addEvent'>
        <div class="card-header">
            <h2>Add / Modify Event</h2>
        </div>
        <div class="card-body">
            <form id="EventForm" action="" class="needs-validation" novalidate="" method="post" autocomplete="off">
                <div class="row">
                    <div class="col-md-12 mt-2">
                        <label class="form-label required">Event Title</label>
                        <input type="hidden" id="id" name="id" value="">
                        <input type="text" id="title" name="title" class="form-control" value="" required>
                    </div>

                    <div class="col-12 mt-2">
                        <label class="form-label">Event Details</label>
                        <textarea id="details" name="details" class="form-control smarteditor" style='resize: none;margin: 0;'></textarea>
                    </div>

                    <div class="col-md-4 col-sm-12 mt-3">
                        <label class="form-label required">Select Event Type</label>
                        <select class="form-control" name='event_type_id' id='event_type_id' required>
                            <option value=""></option>
                            <?php
                            $len = count($res);
                            $i = 0;
                            while ($i < $len) {
                                echo "\n<option value='" . $res[$i]["id"] . "'>" . $res[$i]["title"] . "</option>";
                                $i++;
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-8 col-sm-12 mt-3">
                        <label class="form-label">Event Location</label>
                        <input type="text" id="location" name="location" class="form-control" value="" required>
                    </div>

                    <!--New Row for Date and Time--->
                    <div class="col-md-2 col-sm-12 mt-3">
                        <label class="form-label">&nbsp;</label>
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_all_day_event" name="is_all_day_event" onchange="addDayEventChange();">
                            <span class="form-check-label">IS All Day Event</span>
                        </label>
                    </div>

                    <div class="col-md-2 col-sm-12 mt-2">
                        <label class="form-label required">Start Date</label>
                        <div class="input-icon">
                            <input type="text" class="form-control mt-2 formCheck" id="start_date" name="start_date" value="" data-date-format="dd M yyyy" required>
                            <span class="input-icon-addon">
                                <span class="mdi mdi-calendar-outline"></span>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-12 mt-2 timeDiv" id='startTimeDiv'>
                        <label class="form-label required">Start Time</label>
                        <div class="input-icon">
                            <input type="text" class="form-control formCheck timeInput" id="start_time" name="start_time" value="" data-mask="00:00" data-mask-visible="true" autocomplete="off" required>
                            <span class="input-icon-addon">
                                <span class="mdi mdi-calendar-clock"></span>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12 mt-2">
                        <label class="form-label required">End Date</label>
                        <div class="input-icon">
                            <input type="text" class="form-control mt-2 formCheck" id="end_date" name="end_date" value="" data-date-format="dd M yyyy" required>
                            <span class="input-icon-addon">
                                <span class="mdi mdi-calendar-outline"></span>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-12 mt-2 timeDiv" id='endTimeDiv'>
                        <label class="form-label required">End Time</label>
                        <div class="input-icon">
                            <input type="text" class="form-control formCheck timeInput" id="end_time" name="end_time" value="" data-mask="00:00" data-mask-visible="true" autocomplete="off" required>
                            <span class="input-icon-addon">
                                <span class="mdi mdi-calendar-clock"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mt-5">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary ml-1" onclick="cancelEvent();">Cancel</button>
                    </div>
                </div>
            </form>
        </div>

    </div>
    <!----Event Details---->
    <div class="card my-2" id='eventList'>
        <div class="card-header">
            <?php
            if ($roleid == "001") {
                echo '<button type="button" class="btn btn-primary mr-2" onclick="addEvent();"><i class="mdi mdi-plus-thick mr-1"></i> Event</button>';
                echo '<a href=\'' . $baseurl . '/index.php?q=/modules/Calendar/event_type.php\' class="btn btn-white"><i class="mdi mdi-plus-thick mr-1"></i> Event Type</a>';
            }
            $events = $calGateway->listEvent($connection2);
            ?>

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id='eventListTable' class="table card-table table-vcenter text-nowrap datatable border-bottom">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Event Type</th>
                            <th>Start Date Time</th>
                            <th>End Date Time</th>
                            <th>TAG</th>
                            <th>Attendance</th>
                            <th>Location</th>
                            <th class='text-center'>Publish</th>
                            <?php
                            if ($roleid == "001") {
                                echo "<th style='width:60px;' class='text-center'>Action</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $len = count($events);
                        $i = 0;
                        $str = "";
                        $repo = array();
                        while ($i < $len) {
                            $sdt = date('d M Y  H:i A', $events[$i]["start_time_unix"]);
                            $edt = date('d M Y  H:i A', $events[$i]["end_time_unix"]);

                            $events[$i]['start_date'] = date('d M Y', $events[$i]["start_time_unix"]);
                            $events[$i]['end_date'] = date('d M Y', $events[$i]["end_time_unix"]);

                            $events[$i]['start_time'] = date('h:i a', $events[$i]["start_time_unix"]);
                            $events[$i]['end_time'] = date('h:i a', $events[$i]["end_time_unix"]);

                            $publish = "<button type='button' class='btn btn-link' onclick=\"publish('" . $events[$i]["id"] . "');\">Publish</button>";
                            $attendance = "NA";
                            $rowColor = "";
                            $isDraft = TRUE;
                            if ($events[$i]["is_publish"] == "2") {
                                $publish = "Published";
                                $isDraft = FALSE;
                                $rowColor = "bg-green-lt";
                                if ($events[$i]["block_attendance"] == "2") {
                                    $attendance = "Blocked";
                                }
                            }

                            $delivery = "NA";
                            if (!empty($events[$i]["tag"])) {
                                $delivery = $events[$i]["tag"];
                            }

                            $location = "NA";
                            if (!empty($events[$i]["location"])) {
                                $location = $events[$i]["location"];
                            }
                            if (!empty($events[$i]["details"])) {
                                $events[$i]["details"] = html_entity_decode(htmlspecialchars_decode($events[$i]["details"]));
                            }

                            $str .= "\n<tr class='$rowColor'>";
                            $str .= "\n<td><strong>" . ucwords($events[$i]["title"]) . "</strong></td>";
                            $str .= "\n<td>" . ucwords($events[$i]["event_type_title"]) . "</td>";
                            $str .= "\n<td>" . $sdt . "</td>";
                            $str .= "\n<td>" . $edt . "</td>";
                            $str .= "\n<td>" . $delivery . "</td>";
                            $str .= "\n<td>" . $attendance . "</td>";
                            $str .= "\n<td>" . $location . "</td>";

                            $str .= "\n<td class='text-center'>" . $publish . "</td>";

                            if ($roleid == "001") {
                                if ($isDraft) {
                                    $str .= "\n<td class='text-center'>
                                <button type='button' class='btn btn-warning' onclick=\"editEvent('" . $events[$i]['id'] . "');\"><i class='mdi mdi-18px mdi-pencil-outline'></i></button>
                                <button type='button' class='btn btn-danger mr-1' onclick=\"deleteEvent('" . $events[$i]['id'] . "');\"><i class='mdi mdi-18px mdi-delete'></i></button>
                                </td>";
                                } else {
                                    $str .= "\n<td class='text-center'>NA</td>";
                                }
                            }
                            $str .= "\n</tr>";
                            $repo[$events[$i]['id']] = $events[$i];
                            $i++;
                        }
                        echo $str;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!--Publish Dialog-->
    <div class="modal fade" id="publishDialog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-capitalize" id="publishTitle">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="publishForm" action="" class="needs-validation" novalidate="" method="post" autocomplete="off">
                        <input type="hidden" class='formDia' name="eventid" id="eventid" value="">
                        <?php
                        if ($roleid == "001") {
                            $helperGateway = $container->get(HelperGateway::class);
                            $pro = $helperGateway->getProgram($connection2);

                        ?>
                            <div class="row">
                                <div class='col-md-auto col-sm-12 mt-2'>
                                    <label class="form-label">Bulk Target</label>
                                    <select id='delivery_type' style='min-width:150px;' name='delivery_type' class='form-control' onchange="changeBulkTarget();">
                                        <option value=''>Select</option>
                                        <option value='all'>All</option>
                                        <option value='all_students'>All Students</option>
                                        <option value='all_parents'>All Parents</option>
                                        <option value='all_staff'>All Staff</option>
                                        <option value='individual_staff'>Individual Staff</option>
                                        <option value='individuals'>Individuals</option>
                                    </select>
                                </div>
                                <div class='col-md col-sm-12 mt-2' id='userSelectPanel'>
                                    <label class="form-label">Select User</label>
                                    <select id='userSelect' name="users[]" class='form-control' multiple></select>
                                </div>
                            </div>
                            <div class="row" id="filterDiv">
                                <div class='col-md col-sm-12 indList mt-2'>
                                    <label class="form-label">Select Program</label>
                                    <input type='hidden' class='formDia' name='program_name' id='program_name' value=''>
                                    <select id='programSelect' style='min-width:150px;' name="program" class='form-control' onchange="changeProgram();">
                                        <option value="">Select Program</option>
                                        <?php

                                        $len = count($pro);
                                        $i = 0;
                                        while ($i < $len) {
                                            echo "\n<option value='" . $pro[$i]["pupilsightProgramID"] . "'>" . $pro[$i]["name"] . "</option>";
                                            $i++;
                                        }
                                        ?>
                                    </select>
                                    <script>
                                        function changeProgram() {
                                            var id = $("#programSelect").val();
                                            var type = 'getClass';
                                            $.ajax({
                                                url: 'ajax_data.php',
                                                type: 'post',
                                                data: {
                                                    val: id,
                                                    type: type
                                                },
                                                async: true,
                                                success: function(response) {
                                                    $("#classSelect").html('');
                                                    //$("#pupilsightRollGroupID").html('');
                                                    $("#classSelect").html(response);
                                                }
                                            });
                                        }
                                    </script>
                                </div>

                                <div class='col-md col-sm-12 indList mt-2'>
                                    <label class="form-label">Select Class</label>
                                    <select id='classSelect' name='class' class='form-control' onchange="changeClass();"></select>
                                    <input type='hidden' class='formDia' name='class_name' id='class_name' value=''>
                                    <script>
                                        function changeClass() {
                                            var id = $("#classSelect").val();
                                            var pid = $("#programSelect").val();
                                            var type = 'getSection';
                                            $.ajax({
                                                url: 'ajax_data.php',
                                                type: 'post',
                                                data: {
                                                    val: id,
                                                    type: type,
                                                    pid: pid
                                                },
                                                async: true,
                                                success: function(response) {
                                                    $("#sectionSelect").html('');
                                                    $("#sectionSelect").html(response);
                                                }
                                            });
                                        }
                                    </script>
                                </div>

                                <div class='col-md col-sm-12 indList mt-2'>
                                    <label class="form-label">Select Section</label>
                                    <select id='sectionSelect' name="section" class='form-control' onchange="changeSection();"></select>
                                    <input type='hidden' class='formDia' name='section_name' id='section_name' value=''>
                                    <script>
                                        var pupilsightSchoolYearID = "<?= $pupilsightSchoolYearID; ?>";

                                        function changeSection() {
                                            var id = $("#sectionSelect").val();
                                            var yid = pupilsightSchoolYearID;
                                            var pid = $("#programSelect").val();
                                            var cid = $("#classSelect").val();
                                            var type = 'getStudent';
                                            $.ajax({
                                                url: 'ajax_data.php',
                                                type: 'post',
                                                data: {
                                                    val: id,
                                                    type: type,
                                                    yid: yid,
                                                    pid: pid,
                                                    cid: cid
                                                },
                                                async: true,
                                                success: function(response) {
                                                    console.log(response);
                                                    $('#studentSelect').selectize()[0].selectize.destroy();
                                                    $("#studentSelect").html();
                                                    $("#studentSelect").html(response);
                                                    $('#studentSelect').selectize({
                                                        plugins: ['remove_button'],
                                                    });
                                                }
                                            });

                                        }
                                    </script>
                                </div>
                            </div>
                            <div class="row" id='studentFilterDiv'>
                                <div class='col-md col-sm-12 indList mt-2'>
                                    <label class="form-label">Select Student</label>
                                    <select id='studentSelect' name="students[]" class='form-control' multiple></select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md col-sm-12 mt-3">
                                    <label class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="block_attendance" name="block_attendance">
                                        <span class="form-check-label">Block Attendance</span>
                                    </label>
                                </div>
                                <div class="col-md col-sm-12 mt-3">
                                    <label class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="sms_notify" name="sms_notify">
                                        <span class="form-check-label">Send SMS Notification</span>
                                    </label>
                                </div>
                            </div>
                        <?php
                        }
                        ?>

                    </form>

                </div>
                <div class="modal-footer mt-3">
                    <button type="button" id='closeDialogBtn' class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="verfiyAndPublish();">Publish</button>
                </div>
            </div>
        </div>
    </div>
    <button type="button" class='d-none' id='btnPublishDia' data-toggle="modal" data-target="#publishDialog">Publish</button>

    <?php
    if ($roleid == "001") {
    ?>
        <script>
            function changeBulkTarget() {
                var val = $("#delivery_type").val();
                $("#filterDiv, #studentFilterDiv").show();
                if (val == "individual_staff" || val == "individuals") {
                    $("#userSelectPanel").show();
                } else if (val == "all" || val == "all_students") {
                    $("#filterDiv, #studentFilterDiv").hide();
                } else {
                    $("#userSelectPanel").hide();
                }
            }

            function publish(id) {
                changeBulkTarget();
                $(".formDia").val("");
                resetAddEvent();
                var obj = Event[id];
                $("#publishTitle").text(obj["title"]);
                $("#eventid").val(obj["id"]);
                $("#btnPublishDia").click();
            }

            function verfiyAndPublish() {

                var eventid = $("#eventid").val();
                if (eventid == "") {
                    toast("error", "Invalid Event");
                    return;
                }
                var delivery_type = $("#delivery_type").val();
                if (delivery_type == "") {
                    var program = $("#programSelect").val();
                    if (program == "") {
                        toast("error", "You have not selected any user type to publish");
                        return;
                    } else {
                        var programSelect = $("#programSelect option:selected").text();
                        $("#program_name").val(programSelect);

                        var classSelect = $("#classSelect option:selected").text();
                        $("#class_name").val(classSelect);

                        var sectionSelect = $("#sectionSelect option:selected").text();
                        $("#section_name").val(sectionSelect);
                    }
                }
                $("form#publishForm").submit();
            }
        </script>
        <script>
            function deleteEvent(id) {
                console.log(id);
            }

            function editEvent(id) {
                addEvent();
                var obj = Event[id];
                var elements = ["id", "title", "details", "event_type_id", "location", "start_date", "end_date", "start_time", "end_time"];
                var len = elements.length;
                var i = 0;
                while (i < len) {
                    setVal(elements[i], obj);
                    i++;
                }
                if (obj["is_all_day_event"] == 1) {
                    $("#is_all_day_event").prop('checked', false);
                } else {
                    $("#is_all_day_event").prop('checked', true);
                }
                addDayEventChange();
            }

            function setVal(id, obj) {
                if (!isEmpty(obj[id])) {
                    $("#" + id).val(obj[id]);
                }
            }
        </script>
    <?php
    }
    ?>

    <script>
        function addDayEventChange() {
            if ($("#is_all_day_event").prop("checked")) {
                $(".timeDiv").hide(400);
                $(".timeInput").prop('required', false);
            } else {
                $(".timeInput").prop('required', true);
                $(".timeDiv").show(400);
            }
        }
        //date and time handle here
        var prevDay;
        $('#start_date, #end_date').datepicker({
            language: 'en',
            //startDate: start,
            autoClose: true,
            minDate: new Date(),
            onSelect: function(fd, d, picker) {
                //validateForm();
                // Do nothing if selection was cleared
                if (!d) return;

                var day = d.getDay();

                // Trigger only if date is changed
                if (prevDay != undefined && prevDay == day) return;
                prevDay = day;
            }
        });

        $('#start_time, #end_time').datepicker({
            dateFormat: ' ',
            language: 'en',
            timepicker: true,
            classes: 'only-timepicker',
            onSelect: function(fd, d, picker) {
                //validateForm();
            }
        });
    </script>
    <script>
        var baseurl = "<?= $baseurl; ?>";
        var Event = <?php echo json_encode($repo); ?>;

        function isEmpty(str) {
            return (!str || str.length === 0);
        }
    </script>
    <script>
        $(document).ready(function() {
            $('.smarteditor').trumbowyg({
                autogrow: true
            });

            $("#addEvent").hide();
            $('#eventListTable').DataTable({
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

        function resetAddEvent() {
            var elements = ["id", "title", "details", "event_type_id", "location", "start_date", "end_date", "start_time", "end_time"];
            var len = elements.length;
            var i = 0;
            while (i < len) {
                $("#" + elements[i]).val("");
                i++;
            }
            $("#is_all_day_event").prop('checked', false);
            addDayEventChange();
        }

        function addEvent() {
            resetAddEvent();
            $("#addEvent").show(400);
            $("#eventList").hide(400);
        }

        function cancelEvent() {
            resetAddEvent();
            $("#addEvent").hide(400);
            $("#eventList").show(400);
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
    if (isset($_SESSION["notify"])) {
        if ($_SESSION["notify_exec"] == "1") {
            unset($_SESSION["notify"], $_SESSION["notify_exec"]);
        } else {
            $_SESSION["notify_exec"] = "1";
        }
    }
    //
}
