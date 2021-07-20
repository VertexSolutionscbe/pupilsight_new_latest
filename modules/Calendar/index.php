<?php

use Pupilsight\Domain\Calendar\CalendarGateway;

include "core.php";
$baseurl = getDomain();

$accessFlag = true;
if ($accessFlag == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //print_r($_SESSION[$guid]);

    $roleid = (int)$_SESSION[$guid]["pupilsightRoleIDPrimary"];
    $calGateway = $container->get(CalendarGateway::class);
    $schoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $uid = $_SESSION[$guid]['pupilsightPersonID'];
    if ($roleid == 4) {
        //for parents extract student id
        $childs = $calGateway->getChildListForParent($connection2, $uid);
        if ($childs) {
            if (isset($_GET['cid'])) {
                $studentid = $_GET['cid'];
            } else {
                $studentid = $childs[0]["pupilsightPersonID"];
            }
            $rescal = $calGateway->getMyEvent($connection2, 2, $studentid, $schoolYearID, $uid);
        }
    } else {
        //for other direct role type
        $rescal = $calGateway->getMyEvent($connection2, $roleid, $uid, $schoolYearID);
    }

    $cal = array();
    if ($rescal) {
        $len = count($rescal);
        $i = 0;

        while ($i < $len) {
            $rs = $rescal[$i];
            $dt = array();
            $dt["groupId"] = $rs["id"];
            $dt["title"] = ucwords($rs["title"]);
            $description = $dt["title"];
            if (!empty($rs["details"])) {
                $description = htmlspecialchars_decode($rs["details"]);
            }
            $dt["description"] = $description;
            //start: '2020-09-03T13:00:00',

            $dt["start"] = date('Y-m-d', $rs["start_time_unix"]);
            if (!empty($rs["start_time"])) {
                $dt["start"] = $dt["start"] . "T" . date('H:i:s', $rs["start_time_unix"]);
            }

            $dt["end"] = date('Y-m-d', $rs["end_time_unix"]);
            if (!empty($rs["start_time"])) {
                $dt["end"] = $dt["end"] . "T" . date('H:i:s', $rs["end_time_unix"]);
            }

            //$dt["display"] = 'background';
            $dt["overlap"] = false;
            $dt["rendering"] = 'background';
            if ($rs["color"]) {
                $dt["color"] = $rs["color"];
            }
            $cal[$i] = $dt;
            $i++;
        }
    }

    $page->breadcrumbs->add(__('Events'));

    if ($roleid < 3 || $roleid > 4) {
        //not for student and parents
?>
        <div class="row border-bottom">
            <div class="col-auto ml-auto px-5 py-3">
                <div class="btn-list">
                    <a href="<?= $baseurl; ?>/index.php?q=/modules/Calendar/automate_event.php" class="btn btn-white"><i class="mdi mdi-android-auto mr-1"></i> Automate Event</a>
                    <a href="<?= $baseurl; ?>/index.php?q=/modules/Calendar/event_manage.php" class="btn btn-white"><i class="mdi mdi-plus-thick mr-1"></i> Event</a>
                    <a href="<?= $baseurl; ?>/index.php?q=/modules/Calendar/event_type.php" class="btn btn-white"><i class="mdi mdi-plus-thick mr-1"></i> Event Type</a>
                </div>
            </div>
        </div>
    <?php
    } else if ($roleid == 4) {
        //for parents
        $str = '<div class="row border-bottom">
            <div class="col-auto px-5 py-3">';
        $str .= '<select id="childSel" class="form-control">';
        foreach ($childs as $stu) {
            $selected = '';
            if (!empty($_GET['cid'])) {
                if ($_GET['cid'] == $stu['pupilsightPersonID']) {
                    $selected = 'selected';
                }
            }
            $str .= '<option value=' . $stu['pupilsightPersonID'] . '  ' . $selected . '>' . $stu['officialName'] . '</option>';
        }
        $str .= '</select>';
        $str .= "</div></div>";
        echo $str;
    }
    ?>
    <div id='calendar' class='p-4'></div>

    <link rel="stylesheet" href="<?= $baseurl; ?>/assets/libs/fullcalendar-5.7.2/lib/main.min.css" type="text/css" media="all" />
    <script src="<?= $baseurl; ?>/assets/libs/fullcalendar-5.7.2/lib/main.min.js"></script>
    <script>
        $(document).on('change', '#childSel', function() {
            var id = $(this).val();
            var hrf = 'index.php?q=/modules/Calendar/index.php&cid=' + id;
            window.location.href = hrf;
        });

        $(function() {
            $(".card-body").removeClass("card-body");
        });
    </script>
    <script>
        var event = <?php echo json_encode($cal, TRUE); ?>;
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                initialDate: '<?= date("Y-m-d"); ?>',
                navLinks: true, // can click day/week names to navigate views
                editable: false,
                selectable: true,
                events: event
            });
            calendar.render();
        });
    </script>
    <style>
        #calendar {
            max-width: 1100px;
            margin: 0 auto;
        }
    </style>
<?php
}
