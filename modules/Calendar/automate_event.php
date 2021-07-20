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

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST)) {
    }
    if ($roleid == 3 || $roleid == 4) {
        //for parents and student
        header('Location: index.php?q=/modules/Calendar/index.php');
        exit('You do not have access to this action.');
    } else {
        $page->breadcrumbs->add(__('Automate Event'));
        //not for student and parents
?>
        <div class="">
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

                    <div class="col-md-2 col-sm-12 mt-3">
                        <label class="form-label required">Select Event Type</label>
                        <select class="form-control" name='event_type_id' id='event_type_id' required>
                            <option value="">Select</option>
                            <?php
                            $res = $calGateway->listEventType($connection2);
                            $len = count($res);
                            $i = 0;
                            while ($i < $len) {
                                echo "\n<option value='" . $res[$i]["id"] . "'>" . $res[$i]["title"] . "</option>";
                                $i++;
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4 col-sm-12 mt-3">
                        <label class="form-label">Event Location</label>
                        <input type="text" id="location" name="location" class="form-control" value="" required>
                    </div>

                    <div class="col-md-3 col-sm-12 mt-3">
                        <label class="form-label">Attachment</label>
                        <input type="file" id="attachment" name="attachment" class="form-control" value="">
                    </div>

                    <div class="col-md-3 col-sm-12 mt-3">
                        <label class="form-label">Image Attachment</label>
                        <input type="file" id="img_attachment" name="img_attachment" class="form-control" value="">
                    </div>

                    <!--New Row for Date and Time--->
                    <div class="col-md-6 col-sm-12 mt-2">
                        <label class="form-label required">Select Multiple Dates</label>
                        <div class="input-icon">
                            <input type="text" class="form-control mt-2 formCheck" id="start_date" name="start_date" value="" data-date-format="dd M yyyy" data-language='en' data-multiple-dates="10" data-multiple-dates-separator=", " data-position='top left' required>
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

                    <div class="col-md-2 col-sm-12 mt-2 timeDiv" id='endTimeDiv'>
                        <label class="form-label required">End Time</label>
                        <div class="input-icon">
                            <input type="text" class="form-control formCheck timeInput" id="end_time" name="end_time" value="" data-mask="00:00" data-mask-visible="true" autocomplete="off" required>
                            <span class="input-icon-addon">
                                <span class="mdi mdi-calendar-clock"></span>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12 mt-2 timeDiv" id='endTimeDiv'>
                        <label class="form-label">Interval in Minutes</label>
                        <div class="input-group">
                            <input type="text" class="form-control text-right" onkeypress='validate(event)' maxlength="2" id="interval" name="interval" value="">
                            <span class="input-group-text">Minutes</span>
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


        <link rel="stylesheet" href="<?= $baseurl; ?>/assets/libs/airdatepicker/dist/css/datepicker.min.css" type="text/css" media="all" />
        <script src="<?= $baseurl; ?>/assets/libs/airdatepicker/dist/js/datepicker.min.js"></script>
        <script src="<?= $baseurl; ?>/assets/libs/airdatepicker/dist/js/i18n/datepicker.en.js"></script>

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
            $(document).ready(function() {

                $('.smarteditor').trumbowyg({
                    autogrow: true
                });

                $('#start_date').datepicker({
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
            });
        </script>
        <script>
            var baseurl = "<?= $baseurl; ?>";

            function isEmpty(str) {
                return (!str || str.length === 0);
            }

            function resetAddEvent() {
                var elements = ["id", "title", "details", "event_type_id", "location", "start_date", "start_time", "end_time", "interval"];
                var len = elements.length;
                var i = 0;
                while (i < len) {
                    $("#" + elements[i]).val("");
                    i++;
                }
                $("#is_all_day_event").prop('checked', false);
                addDayEventChange();
            }

            //<input type='text' onkeypress='validate(event)' />
            function validate(evt) {
                var theEvent = evt || window.event;

                // Handle paste
                if (theEvent.type === 'paste') {
                    key = event.clipboardData.getData('text/plain');
                } else {
                    // Handle key press
                    var key = theEvent.keyCode || theEvent.which;
                    key = String.fromCharCode(key);
                }
                var regex = /[0-9]|\./;
                if (!regex.test(key)) {
                    theEvent.returnValue = false;
                    if (theEvent.preventDefault) theEvent.preventDefault();
                }
            }
        </script>
<?php
    }
}
