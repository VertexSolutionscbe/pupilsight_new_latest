(function ($) {
    $.datepicker.setDefaults({
        dateFormat: 'dd/mm/yy'
    });

    "use strict";

    $(document).on('click', '.bordercls', function () {
        $(this).children().addClass("borderclass");
        $(".searchhomepagecls").children().children().show();
    });

    $(document).on('click', '.delSeattr', function () {
        var id = $(this).attr('data-id');
        $(".deltr" + id).remove();
        // $(this).parent().parent().parent().parent().parent().remove();
    });
    $(document).on('click', '.delseatmatrix', function () {
        var id = $(this).attr('data-id');
        var sid = $(this).attr('data-sid');
        $.ajax({
            url: 'modules/Admission/deleteseatmatrix.php',
            type: 'post',
            data: { sid: sid },
            async: true,
            success: function (response) {
                $(".deltr" + id).remove();
            }
        });
    });
    $(document).on('click', '.delTransition', function () {
        var id = $(this).attr('data-id');
        if (confirm("Are you sure want to Delete Transition?")) {
            $.ajax({
                url: 'modules/Campaign/transitionDeleteProcess.php',
                type: 'post',
                data: { id: id },
                async: true,
                success: function (response) {
                    //$(".seatdiv" + id).remove();
                    window.location.href = response;
                }
            });
        }
    });

    $(document).on('change', '.kountseat', function () {
        var tseat = 0;
        $(".kountseat").each(function () {
            if ($(this).val() != '') {
                var seat = $(this).val();
            } else {
                var seat = 0;
            }
            tseat += parseInt(seat);
        });
        //$("#seats").val(tseat);
        $("input[name=seats]").val(tseat);
        $(".showSeats").html('Total Seats : ' + tseat);
    });


    $(document).on('click', '#addTransportStops', function () {
        var cid = $(this).attr('data-cid');
        var ncid = parseInt(cid) + 1;
        $(this).attr('data-cid', ncid);
        //var design = ' <tr id="seatdiv" class=" flex flex-col sm:flex-row justify-between content-center p-0 deltr' + ncid + '"><td class="col-sm  newdes " ><div class="input-group stylish-input-group"><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="stop_no" name="stop_no[' + ncid + ']" class="w-full txtfield"></div></div></div></td><td class="col-sm  newdes " ><div class="input-group stylish-input-group"><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="stop_name" name="stop_name[' + ncid + ']" class="w-full txtfield"></div></div></div></td><td class="col-sm  newdes " ><div class="input-group stylish-input-group"><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="pickup_time" name="pickup_time[' + ncid + ']" class="w-full txtfield"></div></div></div></td><td class="w-full max-w-full sm:max-w-xs flex justify-end px-2 border-b-0 sm:border-b border-t-0 newdes  " ><div class="input-group stylish-input-group"><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="drop_time" name="drop_time[' + ncid + ']" class="w-full txtfield kountseat szewdt"></div></div><div class="dte mb-1"  style="font-size: 22px; margin: 6px 0 0px 4px;"><i style="cursor:pointer" class="mdi mdi-close-circle mdi-24px delSeattr" data-id="' + ncid + '"></i></div></div></td></tr>';
        var design = ' <tr id="seatdiv" class="row mb-1 fixedfine mt-3 p-0 deltr' + ncid + '"><td class="col-sm  newdes " ><div class="input-group stylish-input-group"><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="stop_no" name="stop_no[' + ncid + ']" class="w-full txtfield"></div></div></div></td><td class="col-sm  newdes " ><div class="input-group stylish-input-group"><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="stop_name" name="stop_name[' + ncid + ']" class="w-full txtfield"></div></div></div></td><td class="col-sm  newdes " ><div class="input-group stylish-input-group"><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="pickup_time" name="pickup_time[' + ncid + ']" minlength="5" maxlength="5" class="w-full txtfield"></div></div></div></td><td class="col-sm  newdes" ><div class="input-group stylish-input-group"><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="drop_time" name="drop_time[' + ncid + ']" minlength="5" maxlength="5" class="w-full txtfield"></div></div><div class="dte mb-1"  style="font-size: 22px; margin: 6px 0 0px 4px;"><i style="cursor:pointer" class="mdi mdi-close-circle mdi-24px delSeattr" data-id="' + ncid + '"></i></div></div></td></tr>';
        $("#route_stops").before(design);

    });

    $(document).on('click', '#addState', function () {
        var cid = $(this).attr('data-cid');
        var ncid = parseInt(cid) + 1;
        $(this).attr('data-cid', ncid);
        var design = '<div id="seatdiv" class=" row mb-1 deltr' + ncid + '""><div class="col-sm  newdes" ><div class=""><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="number" placeholder="Order" id="code" name="serialorder[' + ncid + ']" class="w-full txtfield"></div></div></div></div><div class="col-sm  newdes" ><div class=""><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="name" placeholder="State Name"  name="statename[' + ncid + ']" class="w-full txtfield" value=""></div></div></div></div><div class="col-sm  newdes" ><div class=""><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="code" placeholder="Code"  name="statecode[' + ncid + ']" class="w-full txtfield"></div></div></div></div><div class="col-sm  newdes" colspan="2"><div class=""><div class=" mb-1 ncls"></div><div class=" txtfield kountseat mb-1"><div class="flex-1 relative" style="display:inline-flex;"><select id="notification" name="notification[' + ncid + ']" class="w-full txtfield kountseat szewdt showTemplate" data-sid="' + ncid + '"><option value >Select Notification</option><option value="1" >Email</option><option value="2" >SMS</option><option value="3" >Both</option></select><i style="cursor:pointer;padding: 8px 10px" class="mdi mdi-close-circle mdi-24px delSeattr" data-id="' + ncid + '"></i></div></div><div class=" mb-1"><a href="fullscreen.php?q=/modules/Campaign/email_sms_template.php&wsid=' + ncid + '&type=" data-hrf="fullscreen.php?q=/modules/Campaign/email_sms_template.php&wsid=' + ncid + '&type=" class="thickbox" id="clickTemplate' + ncid + '" style="display:none;">click</a><input type="hidden" name="pupilsightTemplateIDs[' + ncid + ']" id="pupilsightTemplateID-' + ncid + '" value=""><div id="showTemplateName' + ncid + '" ></div></div></div></div></div><script type="text/javascript">var tb_pathToImage="lib/thickbox/loadingAnimation.gif";</script><script type="text/javascript" src="lib/thickbox/thickbox-compressed.js?v=18.0.01"></script><script type="text/javascript" src="lib/tinymce/tinymce.min.js?v=18.0.01"></script><script type="text/javascript">window.Pupilsight = {"config":{"datepicker":{"locale":"en-GB"},"thickbox":{"pathToImage":"http:\/\/localhost\/pupilsight\/lib\/thickbox\/loadingAnimation.gif"},"tinymce":{"valid_elements":"br[style],strong[style],em[style],span[style],p[style],address[style],pre[style],h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],table[style],thead[style],tbody[style],tfoot[style],tr[style],td[style|colspan|rowspan],ol[style],ul[style],li[style],blockquote[style],a[style|target|href],img[style|class|src|width|height],video[style],source[style],hr[style],iframe[style|width|height|src|frameborder|allowfullscreen],embed[style],div[style],sup[style],sub[style]"},"sessionTimeout":{"sessionDuration":6400,"message":"Your session is about to expire: you will be logged out shortly."}}};</script>';
        $("#lastseatdiv").before(design);

    });

    $(document).on('click', '#addTransition', function () {
        var cid = $(this).attr('data-cid');
        var wid = $(this).attr('data-wid');
        var ncid = parseInt(cid) + 1;
        $(this).attr('data-cid', ncid);
        $.ajax({
            url: 'modules/Campaign/ajax_add_wf_transitions.php',
            type: 'post',
            data: { ncid: ncid, wid: wid },
            async: true,
            success: function (response) {
                $("#lastseatdiv").before(response);
            }
        });


    });
    // $(document).on('click','#addSeats', function(){
    //   var cid = $(this).attr('data-cid');
    //   var ncid = parseInt(cid) + 1;
    //   $(this).attr('data-cid', ncid); 
    //   var design = '<tr id="" class=" flex flex-col sm:flex-row justify-between content-center p-0"><td class="col-sm  newdes" ><div class="input-group stylish-input-group"><div class=" mb-1"><label for="name" class="inline-block mt-4 sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">Name </label></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="name" name="seatname['+ncid+']" class="w-full txtfield"></div></div></div></td><td class="col-sm  newdes" ><div class="input-group stylish-input-group"><div class=" mb-1"><label for="seat" class="inline-block mt-4 sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">Seat</label></div><div class=" txtfield mb-1" style="display:inline-flex;"><div class="flex-1 relative"><input type="number" id="seat" name="seatallocation['+ncid+']" class="w-full txtfield kountseat"></div><div class="" style="font-size: 25px; padding: 0px 0 0 25px;"><i style="cursor:pointer" class="mdi mdi-close-circle mdi-24px delSeattr"></i></div></div></div></td></tr>';
    //   $(this).parent().parent().parent().parent().after(design);

    // });

    /*-------------------------------------
        Sidebar Toggle Menu
      -------------------------------------*/
    $('.sidebar-toggle-view').on('click', '.sidebar-nav-item .nav-link', function (e) {
        if (!$(this).parents('#wrapper').hasClass('sidebar-collapsed')) {
            var animationSpeed = 300,
                subMenuSelector = '.sub-group-menu',
                $this = $(this),
                checkElement = $this.next();
            if (checkElement.is(subMenuSelector) && checkElement.is(':visible')) {
                checkElement.slideUp(animationSpeed, function () {
                    checkElement.removeClass('menu-open');
                });
                checkElement.parent(".sidebar-nav-item").removeClass("active");
            } else if ((checkElement.is(subMenuSelector)) && (!checkElement.is(':visible'))) {
                var parent = $this.parents('ul').first();
                var ul = parent.find('ul:visible').slideUp(animationSpeed);
                ul.removeClass('menu-open');
                var parent_li = $this.parent("li");
                checkElement.slideDown(animationSpeed, function () {
                    checkElement.addClass('menu-open');
                    parent.find('.sidebar-nav-item.active').removeClass('active');
                    parent_li.addClass('active');
                });
            }
            if (checkElement.is(subMenuSelector)) {
                e.preventDefault();
            }
        } else {
            if ($(this).attr('href') === "#") {
                e.preventDefault();
            }
        }
    });

    /*-------------------------------------
        Sidebar Menu Control
      -------------------------------------*/
    $(".sidebar-toggle").on("click", function () {
        window.setTimeout(function () {
            $("#wrapper").toggleClass("sidebar-collapsed");
        }, 500);
    });

    /*-------------------------------------
        Sidebar Menu Control Mobile
      -------------------------------------*/
    $(".sidebar-toggle-mobile").on("click", function () {
        $("#wrapper").toggleClass("sidebar-collapsed-mobile");
        if ($("#wrapper").hasClass("sidebar-collapsed")) {
            $("#wrapper").removeClass("sidebar-collapsed");
        }
    });

    /*-------------------------------------
        jquery Scollup activation code
     -------------------------------------*/
    // $.scrollUp({
    //     scrollText: '<i class="fa fa-angle-up"></i>',
    //     easingType: "linear",
    //     scrollSpeed: 900,
    //     animation: "fade"
    // });

    /*-------------------------------------
        jquery Scollup activation code
      -------------------------------------*/
    $("#preloader").fadeOut("slow", function () {
        //$(this).remove();
    });

    $(function () {
        /*-------------------------------------
              Data Table init
          -------------------------------------*/
        if ($.fn.DataTable !== undefined) {
            $('.data-table').DataTable({
                paging: false,
                searching: false,
                info: false,
                lengthChange: false,
                lengthMenu: [20, 50, 75, 100],
                columnDefs: [{
                    // targets: [0, -1], // column or columns numbers
                    orderable: false // set orderable for selected columns
                }],
                responsive: true,
            });
        }

        /*-------------------------------------
              All Checkbox Checked
          -------------------------------------*/
        $(".checkAll").on("click", function () {
            $(this).parents('.table').find('input:checkbox').prop('checked', this.checked);
        });

        /*-------------------------------------
              Tooltip init
          -------------------------------------*/
        $('[data-toggle="tooltip"]').tooltip();

        /*-------------------------------------
              Select 2 Init
          -------------------------------------*/
        if ($.fn.select2 !== undefined) {
            $('.select2').select2({
                width: '100%'
            });
        }

        /*-------------------------------------
              Date Picker
          -------------------------------------*/
        if ($.fn.datepicker !== undefined) {
            $('.air-datepicker').datepicker({
                language: {
                    days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                    daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    daysMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    today: 'Today',
                    clear: 'Clear',
                    dateFormat: 'dd/mm/yyyy',
                    firstDay: 0
                }
            });
        }

        /*-------------------------------------
              Counter
          -------------------------------------*/
        var counterContainer = $(".counter");
        if (counterContainer.length) {
            counterContainer.counterUp({
                delay: 50,
                time: 1000
            });
        }

        /*-------------------------------------
              Vector Map 
          -------------------------------------*/
        if ($.fn.vectorMap !== undefined) {
            $('#world-map').vectorMap({
                map: 'world_mill',
                zoomButtons: false,
                backgroundColor: 'transparent',

                regionStyle: {
                    initial: {
                        fill: '#0070ba'
                    }
                },
                focusOn: {
                    x: 0,
                    y: 0,
                    scale: 1
                },
                series: {
                    regions: [{
                        values: {
                            CA: '#41dfce',
                            RU: '#f50056',
                            US: '#f50056',
                            IT: '#f50056',
                            AU: '#fbd348'
                        }
                    }]
                }
            });
        }

        /*-------------------------------------
              Line Chart 
          -------------------------------------*/
        if ($("#earning-line-chart").length) {

            var lineChartData = {
                labels: ["", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun", ""],
                datasets: [{
                    data: [0, 5e4, 1e4, 5e4, 14e3, 7e4, 5e4, 75e3, 5e4],
                    backgroundColor: '#ff0000',
                    borderColor: '#ff0000',
                    borderWidth: 1,
                    pointRadius: 0,
                    pointBackgroundColor: '#ff0000',
                    pointBorderColor: '#ffffff',
                    pointHoverRadius: 6,
                    pointHoverBorderWidth: 3,
                    fill: 'origin',
                    label: "Total Collection"
                },
                {
                    data: [0, 3e4, 2e4, 6e4, 7e4, 5e4, 5e4, 9e4, 8e4],
                    backgroundColor: '#417dfc',
                    borderColor: '#417dfc',
                    borderWidth: 1,
                    pointRadius: 0,
                    pointBackgroundColor: '#304ffe',
                    pointBorderColor: '#ffffff',
                    pointHoverRadius: 6,
                    pointHoverBorderWidth: 3,
                    fill: 'origin',
                    label: "Fees Collection"
                }
                ]
            };
            var lineChartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000
                },
                scales: {

                    xAxes: [{
                        display: true,
                        ticks: {
                            display: true,
                            fontColor: "#222222",
                            fontSize: 16,
                            padding: 20
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            color: '#cccccc',
                            borderDash: [5, 5]
                        }
                    }],
                    yAxes: [{
                        display: true,
                        ticks: {
                            display: true,
                            autoSkip: true,
                            maxRotation: 0,
                            fontColor: "#646464",
                            fontSize: 16,
                            stepSize: 25000,
                            padding: 20,
                            callback: function (value) {
                                var ranges = [{
                                    divider: 1e6,
                                    suffix: 'M'
                                },
                                {
                                    divider: 1e3,
                                    suffix: 'k'
                                }
                                ];

                                function formatNumber(n) {
                                    for (var i = 0; i < ranges.length; i++) {
                                        if (n >= ranges[i].divider) {
                                            return (n / ranges[i].divider).toString() + ranges[i].suffix;
                                        }
                                    }
                                    return n;
                                }
                                return formatNumber(value);
                            }
                        },
                        gridLines: {
                            display: true,
                            drawBorder: false,
                            color: '#cccccc',
                            borderDash: [5, 5],
                            zeroLineBorderDash: [5, 5],
                        }
                    }]
                },
                legend: {
                    display: false
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    enabled: true
                },
                elements: {
                    line: {
                        tension: .35
                    },
                    point: {
                        pointStyle: 'circle'
                    }
                }
            };
            var earningCanvas = $("#earning-line-chart").get(0).getContext("2d");
            var earningChart = new Chart(earningCanvas, {
                type: 'line',
                data: lineChartData,
                options: lineChartOptions
            });
        }

        /*-------------------------------------
              Bar Chart 
          -------------------------------------*/
        if ($("#expense-bar-chart").length) {

            var barChartData = {
                labels: ["Jan", "Feb", "Mar"],
                datasets: [{
                    backgroundColor: ["#40dfcd", "#417dfc", "#ffaa01"],
                    data: [125000, 100000, 75000, 50000, 150000],
                    label: "Expenses (millions)"
                },]
            };
            var barChartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000
                },
                scales: {

                    xAxes: [{
                        display: false,
                        maxBarThickness: 100,
                        ticks: {
                            display: false,
                            padding: 0,
                            fontColor: "#646464",
                            fontSize: 14,
                        },
                        gridLines: {
                            display: true,
                            color: '#e1e1e1',
                        }
                    }],
                    yAxes: [{
                        display: true,
                        ticks: {
                            display: true,
                            autoSkip: false,
                            fontColor: "#646464",
                            fontSize: 14,
                            stepSize: 25000,
                            padding: 20,
                            beginAtZero: true,
                            callback: function (value) {
                                var ranges = [{
                                    divider: 1e6,
                                    suffix: 'M'
                                },
                                {
                                    divider: 1e3,
                                    suffix: 'k'
                                }
                                ];

                                function formatNumber(n) {
                                    for (var i = 0; i < ranges.length; i++) {
                                        if (n >= ranges[i].divider) {
                                            return (n / ranges[i].divider).toString() + ranges[i].suffix;
                                        }
                                    }
                                    return n;
                                }
                                return formatNumber(value);
                            }
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            color: '#e1e1e1',
                            zeroLineColor: '#e1e1e1'

                        }
                    }]
                },
                legend: {
                    display: false
                },
                tooltips: {
                    enabled: true
                },
                elements: {}
            };
            var expenseCanvas = $("#expense-bar-chart").get(0).getContext("2d");
            var expenseChart = new Chart(expenseCanvas, {
                type: 'bar',
                data: barChartData,
                options: barChartOptions
            });
        }

        /*-------------------------------------
              Doughnut Chart 
          -------------------------------------*/
        if ($("#student-doughnut-chart").length) {

            var doughnutChartData = {
                labels: ["Female Students", "Male Students"],
                datasets: [{
                    backgroundColor: ["#304ffe", "#ffa601"],
                    data: [45000, 105000],
                    label: "Total Students"
                },]
            };
            var doughnutChartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 65,
                rotation: -9.4,
                animation: {
                    duration: 2000
                },
                legend: {
                    display: false
                },
                tooltips: {
                    enabled: true
                },
            };
            var studentCanvas = $("#student-doughnut-chart").get(0).getContext("2d");
            var studentChart = new Chart(studentCanvas, {
                type: 'doughnut',
                data: doughnutChartData,
                options: doughnutChartOptions
            });
        }

        /*-------------------------------------
              Calender initiate 
          -------------------------------------*/
        if ($.fn.fullCalendar !== undefined) {
            $('#fc-calender').fullCalendar({
                header: {
                    center: 'basicDay,basicWeek,month',
                    left: 'title',
                    right: 'prev,next',
                },
                fixedWeekCount: false,
                navLinks: true, // can click day/week names to navigate views
                editable: true,
                eventLimit: true, // allow "more" link when too many events
                aspectRatio: 1.8,
                events: [{
                    title: 'All Day Event',
                    start: '2019-04-01'
                },

                {
                    title: 'Meeting',
                    start: '2019-04-12T14:30:00'
                },
                {
                    title: 'Happy Hour',
                    start: '2019-04-15T17:30:00'
                },
                {
                    title: 'Birthday Party',
                    start: '2019-04-20T07:00:00'
                }
                ]
            });
        }
    });


    function alertcampaign() {
        var dialog = $('<p>Configure Registration Settings?</p>').dialog({
            resizable: false,
            height: 200,
            width: 350,
            modal: true,
            buttons: [{
                text: "Public",
                "class": 'fw-btn-fill btn-gradient-blue addbtncss fcss bg-dodger-blue',
                click: function () {
                    var type = '1';
                    $.ajax({
                        url: 'modules/Campaign/campaignfor.php',
                        type: 'post',
                        data: { type: type },
                        async: true,
                        success: function (response) {
                            window.location.href = response;
                        }
                    });
                }
            },
            {
                text: "Private",
                "class": 'fw-btn-fill btn-gradient-blue addbtncss fcss bg-light-sea-green',
                click: function () {
                    var type = '2';
                    $.ajax({
                        url: 'modules/Campaign/campaignfor.php',
                        type: 'post',
                        data: { type: type },
                        async: true,
                        success: function (response) {
                            window.location.href = response;
                        }
                    });
                }
            }
            ],

        });
    }

    $(document).on('change', '#academic_id', function () {
        var val = $(this).find('option:selected').text();
        $("input[name=ayear]").val(val);
    });



    $(document).on('change', '.tableName', function () {
        var tabname = $(this).val();
        var rid = $(this).attr('data-rid');
        if (tabname != '') {
            $("#addMoreTransition").attr('data-tname', tabname);
            $.ajax({
                url: 'modules/Campaign/transitionsColumnList.php',
                type: 'post',
                data: { tabname: tabname },
                dataType: "json",
                async: true,
                success: function (response) {
                    //alert(response);
                    $("#columnName" + rid).html(response.col);
                    $("#requireddiv" + rid).html(response.required);
                }
            });
        }
    });

    $(document).on('change', '.campaignName', function () {
        var campformid = $(this).val();
        var rid = $(this).attr('data-rid');
        if (campformid != '') {
            $("#addMoreTransition").attr('data-cname', campformid);
            $.ajax({
                url: 'modules/Campaign/campaignFluentFields.php',
                type: 'post',
                data: { campformid: campformid },
                async: true,
                success: function (response) {
                    $("#fluentForm" + rid).html(response);
                }
            });
        }
    });

    $(document).on('click', '#addMoreTransition', function () {
        var cid = $(this).attr('data-cid');
        var tname = $(this).attr('data-tname');
        var cname = $(this).attr('data-cname');
        var ncid = parseInt(cid) + 1;
        $(this).attr('data-cid', ncid);
        $.ajax({
            url: 'modules/Campaign/ajax_transitions.php',
            type: 'post',
            data: { ncid: ncid, tname: tname, cname: cname },
            async: true,
            success: function (response) {
                $("#lastseatdiv").before(response);
                var tabname = tname;
                var rid = parseInt(cid) + 1;
                var campformid = cname;
                if (tabname != 'none') {
                    // alert(rid);
                    // alert(tabname);
                    $("#addMoreTransition").attr('data-tname', tabname);
                    $.ajax({
                        url: 'modules/Campaign/transitionsColumnList.php',
                        type: 'post',
                        data: { tabname: tabname },
                        dataType: "json",
                        async: true,
                        success: function (response) {
                            //alert(response);
                            $("#columnName" + rid).html(response.col);
                            //$("#requireddiv"+rid).html(response.required);
                        }
                    });
                }
                if (campformid != '') {
                    $("#addMoreTransition").attr('data-cname', campformid);
                    $.ajax({
                        url: 'modules/Campaign/campaignFluentFields.php',
                        type: 'post',
                        data: { campformid: campformid },
                        async: true,
                        success: function (response) {
                            $("#fluentForm" + rid).html(response);
                        }
                    });
                }
            }
        });


    });

    $(document).on('click', '.statesButton', function () {
        $(".statesButton").removeClass('activestate');
        $(this).addClass('activestate');
        var remark = $(this).attr('data-remark');
        if (remark == '1') {
            var cid = $(this).attr('data-cid');
            var sid = $(this).attr('data-sid');
            var sname = $(this).attr('data-name');
            var fid = $(this).attr('data-formid');
            var favorite = [];
            $.each($("input[name='submission_id[]']:checked"), function () {
                favorite.push($(this).val());
            });
            var subid = favorite.join(", ");
            if (subid) {
                var hrf = 'fullscreen.php?q=/modules/Campaign/state_remark.php&cid=' + cid + '&sid=' + sid + '&sname=' + sname + '&fid=' + fid + '&subid=' + subid + '&width=500&height=250';
                $("#clickStateRemark").attr('href', hrf);
                $("#clickStateRemark")[0].click();
            } else {
                alert('You Have to Select Applicants.');
            }
        } else {
            var cid = $(this).attr('data-cid');
            var sid = $(this).attr('data-sid');
            var sname = $(this).attr('data-name');
            var fid = $(this).attr('data-formid');
            var favorite = [];
            $.each($("input[name='submission_id[]']:checked"), function () {
                favorite.push($(this).val());
            });
            var subid = favorite.join(", ");
            //alert(subid);
            if (subid) {
                $("#preloader").show();
                $.ajax({
                    url: 'modules/Campaign/campaignFormStates.php',
                    type: 'post',
                    data: { cid: cid, sid: sid, sname: sname, fid: fid, subid: subid },
                    async: true,
                    success: function (response) {
                        location.reload();
                    }
                });
            } else {
                alert('You Have to Select Applicants.');
            }
        }
    });

    $(document).on('click', '#sendState', function () {

        var cid = $(".activestate").attr('data-cid');
        var sid = $(".activestate").attr('data-sid');
        var sname = $(".activestate").attr('data-name');
        var fid = $(".activestate").attr('data-formid');
        var emailquote = $("#emailQuote").val();
        var smsquote = $("#smsQuote").val();
        var favorite = [];
        $.each($("input[name='submission_id[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var subid = favorite.join(", ");
        //alert(subid);
        if (subid) {
            if (emailquote != '' || smsquote != '') {
                $("#preloader").show();
                $.ajax({
                    url: 'modules/Campaign/campaignFormStates.php',
                    type: 'post',
                    data: { cid: cid, sid: sid, sname: sname, fid: fid, subid: subid, emailquote: emailquote, smsquote: smsquote },
                    async: true,
                    success: function (response) {

                        $("#preloader").fadeOut("slow", function () {
                            //$(this).remove();
                            window.location.href = response;
                        });
                    }
                });
            } else {
                alert('You Have to Enter Quote.');
            }
        } else {
            alert('You Have to Select Applicants.');

        }


    });


    $(document).on('change', '#showfield1', function () {
        $("#showfield2").show();
    });

    $(document).on('change', '#showfield2', function () {
        var val = $(this).val();
        $(".searchby").hide();
        $("." + val + "Open").show();
    });


    $(document).on('click', '#filterCampaign', function () {
        var field = $("#showfield1").val();
        var searchby = $("#showfield2").val();
        var search = $(".searchOpen").val();
        var range1 = $("#range1").val();
        var range2 = $("#range2").val();
        var cid = $("#campaignId").val();
        var fid = $("#formId").val();
        var aid = $("#applicationId").val();
        var stid = $("#applicationStatus option:selected").val();
        var aname = $("#applicationName").val();
        var clid = $("#applicationClass option:selected").val();
        var pid = $("#applicationProg option:selected").val();
        if (field != '' && searchby != '') {
            $.ajax({
                url: 'modules/Campaign/campaignFormListSearch.php',
                type: 'post',
                data: { field: field, searchby: searchby, search: search, range1: range1, range2: range2, cid: cid, fid: fid, aid: aid, stid: stid, aname: aname, clid: clid, pid: pid },
                async: true,
                success: function (response) {
                    $("#expore_tbl").html();
                    $("#expore_tbl").html(response);
                }
            });
        }
    });


    //pdf 
    $('#btnExport').click(function () {
        exportPDF();
    });

    var specialElementHandlers = {
        // element with id of "bypass" - jQuery style selector
        '.no-export': function (element, renderer) {
            // true = "handled elsewhere, bypass text extraction"
            return true;
        }
    };



    function exportPDF() {

        var doc = new jsPDF('p', 'pt', 'a4');
        //A4 - 595x842 pts
        //https://www.gnu.org/software/gv/manual/html_node/Paper-Keywords-and-paper-size-in-points.html


        //Html source 
        var source = document.getElementById('expore_tbl').innerHTML;

        var margins = {
            top: 10,
            bottom: 10,
            left: 10,
            width: 300
        };

        doc.fromHTML(
            source, // HTML string or DOM elem ref.
            margins.left,
            margins.top, {
            'width': margins.width,
            'elementHandlers': specialElementHandlers
        },

            function (dispose) {
                // dispose: object with X, Y of the last line add to the PDF 
                //          this allow the insertion of new lines after html
                doc.save('campaign_submitted_form_list.pdf');
            }, margins);
    }


    // $('#expore_xl').click(function() {

    //     var table = document.getElementById('expore_tbl'); // id of table
    //     var tableHTML = table.outerHTML;
    //     var fileName = 'campaign_submitted_form_list.xls';

    //     var msie = window.navigator.userAgent.indexOf("MSIE ");

    //     // If Internet Explorer
    //     if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
    //         dummyFrame.document.open('txt/html', 'replace');
    //         dummyFrame.document.write(tableHTML);
    //         dummyFrame.document.close();
    //         dummyFrame.focus();
    //         return dummyFrame.document.execCommand('SaveAs', true, fileName);
    //     }
    //     //other browsers
    //     else {
    //         var a = document.createElement('a');
    //         tableHTML = tableHTML.replace(/  /g, '').replace(/ /g, '%20'); // replaces spaces
    //         a.href = 'data:application/vnd.ms-excel,' + tableHTML;
    //         a.setAttribute('download', fileName);
    //         document.body.appendChild(a);
    //         a.click();
    //         document.body.removeChild(a);
    //     }
    // });


    $(document).on('click', '#expore_xl', function () {
        $("#expore_tbl").table2excel({
            name: "Worksheet Name",
            filename: "campaign_submitted_form_list.xls",
            fileext: ".xls",
            exclude: ".checkall",
            exclude_inputs: true,
            columns: [0, 1, 2, 3, 4, 5],
            select: true

        });

    });


    $(document).on('click', '#expore_student_xl', function () {
        //alert("Export success");
        // $("#expore_tbl").table2excel({
        //     name: "Worksheet Name",
        //     filename: "Student_details.xls",
        //     fileext: ".xls",
        //     exclude: ".checkall",
        //     exclude: ".dropdown",
        //     exclude_inputs: true,
        //     exclude_links: true,
        //     columns: [0, 1, 2, 3, 4, 5]

        // });

        //$('#expore_tbl tr').find('td:eq(0),th:eq(0)').remove();
        $("#expore_tbl tr").each(function () {
            $(this).find("th:last").remove();
            $(this).find("td:last").remove();
            $(this).find("th:first").remove();
            $(this).find("td:first").remove();
        });

        $("#expore_tbl").table2excel({
            name: "Worksheet Name",
            filename: "Student_details.xls",
            fileext: ".xls",
            exclude: ".checkall",
            exclude: ".rm_cell",
            exclude_inputs: true,
            columns: [0, 1, 2, 3, 4, 5]

        });
        location.reload();

    });

    $(document).on('click', '#export_not_marks_xl', function () {
        $("#marksNotEntered_excel").table2excel({
            // $("#mytable").find($("tr")).slice(1).remove();
            name: " Student Marks Not Enter",
            filename: "Student_marks_Not_Enter.xls",
            fileext: ".xls",
            exclude: ".checkall",
            exclude_inputs: true,
            exclude_links: true,
            columns: [0, 1, 2, 3, 4, 5]

        });

    });


    $(document).on('click', '#expore_marks_xl', function () {
        //var type = 'studentMarks_excel';
        var type = 'studentMarks_excel_new';
        var section = $('#pupilsightRollGroupIDbyPP').val();
        var cls = $('#pupilsightYearGroupIDbyPP').val();
        var program = $('#pupilsightProgramIDbyPP').val();
        var testId = $('#testId').val();
        var favorite = [];
        $.each($("input[name='stuid[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");
        //alert(subid);
        if (stuid) {
            var stu_id = stuid;

            $.ajax({
                url: 'ajaxSwitch.php',
                type: 'post',
                data: { val: val, type: type, program: program, cls: cls, section: section, testId: testId, stu_id: stu_id },
                async: true,
                success: function (response) {
                    //alert(response);
                    $("#marks_studentExcel").html(response);
                    $("#excelexport").table2excel({
                        name: " Student Marks",
                        filename: "Student_marks.xls",
                        fileext: ".xls",
                        exclude: ".checkall",
                        exclude_inputs: true,
                        exclude_links: true

                    });
                }
            });
        } else {
            alert('Please Select Student First');
        }
    });


    $(document).on('click', '#expore_marks_sub_xl', function () {
        var type = 'subjectMarks_excel';
        var section = $('#pupilsightRollGroupIDbyPP').val();
        var cls = $('#pupilsightYearGroupIDbyPP').val();
        var program = $('#pupilsightProgramIDbyPP').val();
        var testId = $('#testId').val();
        var sub = $('#pupilsightDepartmentID').val();

        //alert(section);

        $.ajax({
            url: 'ajaxSwitch.php',
            type: 'post',
            data: { val: val, type: type, program: program, cls: cls, section: section, testId: testId, sub: sub },
            async: true,
            success: function (response) {
                //console.log(response);
                $("#marks_subjectExcel").html(response);
                $("#subexcelexport").table2excel({
                    name: "subject Marks",
                    filename: "subject_marks.xls",
                    fileext: ".xls",
                    exclude: ".checkall",
                    exclude_inputs: true,
                    exclude_links: true

                });
            }
        });
    });




    //csv

    function download_csv(csv, filename) {
        var csvFile;
        var downloadLink;

        // CSV FILE
        csvFile = new Blob([csv], { type: "text/csv" });

        // Download link
        downloadLink = document.createElement("a");

        // File name
        downloadLink.download = filename;

        // We have to create a link to the file
        downloadLink.href = window.URL.createObjectURL(csvFile);

        // Make sure that the link is not displayed
        downloadLink.style.display = "none";

        // Add the link to your DOM
        document.body.appendChild(downloadLink);

        // Lanzamos
        downloadLink.click();
    }

    function export_table_to_csv(html, filename) {
        var csv = [];
        var rows = document.querySelectorAll("table tr");

        for (var i = 0; i < rows.length; i++) {
            var row = [],
                cols = rows[i].querySelectorAll("td, th");

            for (var j = 0; j < cols.length; j++)
                row.push(cols[j].innerText);

            csv.push(row.join(","));
        }

        // Download CSV
        download_csv(csv.join("\n"), filename);
    }

    function exportTableToCSV($table, filename) {

        // $table.find("th:nth-child(1)").hide();
        // $table.find("td:nth-child(1)").hide();
        var $rows = $table.find('tr:has(td),tr:has(th)'),

            // Temporary delimiter characters unlikely to be typed by keyboard
            // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character

            // actual delimiter characters for CSV format
            colDelim = '","',
            rowDelim = '"\r\n"',

            // Grab text from table into CSV formatted string
            csv = '"' + $rows.map(function (i, row) {
                var $row = jQuery(row),
                    $cols = $row.find('td,th');

                return $cols.map(function (j, col) {
                    var $col = jQuery(col),
                        text = $col.text();

                    return text.replace(/"/g, '""'); // escape double quotes

                }).get().join(tmpColDelim);

            }).get().join(tmpRowDelim)
                .split(tmpRowDelim).join(rowDelim)
                .split(tmpColDelim).join(colDelim) + '"',



            // Data URI
            csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

        //console.log(csv);
        download_csv(csv, filename);

        // if (window.navigator.msSaveBlob) { // IE 10+
        //     //alert('IE' + csv);
        //     window.navigator.msSaveOrOpenBlob(new Blob([csv], {type: "text/plain;charset=utf-8;"}), "csvname.csv")
        // } 
        // else {
        jQuery(this).attr({ 'download': filename, 'href': csvData, 'target': '_blank' });
        // }
    }


    $(document).on('click', '#expore_csv', function () {
        // var html = document.getElementById('expore_tbl').innerHTML;
        // // var html = document.getElementById('expore_tbl').innerHTML;
        // export_table_to_csv(html, "campaign_submitted_form_list.csv");
        exportTableToCSV.apply(this, [jQuery('#expore_tbl'), 'campaign_submitted_form_list.csv']);
        //window.location.reload();

    });

    $("#start_date").datepicker({
        //minDate: 0,
        changeMonth: true,
        changeYear: true,
        onClose: function (selectedDate) {
            $("#end_date").datepicker("option", "minDate", selectedDate);
        }
    });



    $(document).on('change', '#academic_year', function () {
        var val = $(this).val();
        var type = 'getterm';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    $("#schoolterm").html(response);
                }
            });
        }
    });

    $(document).on('change', '#schoolterm', function () {
        var val = $(this).val();
        var type = 'gettermdaterange';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                dataType: "json",
                async: true,
                success: function (response) {
                    var minDate = response.firstDay;
                    var maxDate = response.lastDay;
                    $('#f_date').datepicker('option', 'minDate', new Date(minDate));
                    $('#f_date').datepicker('option', 'maxDate', new Date(maxDate));
                    $('#l_date').datepicker('option', 'minDate', new Date(minDate));
                    $('#l_date').datepicker('option', 'maxDate', new Date(maxDate));

                }
            });
        }
    });

    // var iframe = document.getElementById("innerForm");

    // // Adjusting the iframe height onload event
    // if (iframe) {
    //     iframe.onload = function() {
    //         iframe.style.height = (Number(iframe.contentWindow.document.body.scrollHeight) + 100) + 'px';
    //     }
    // }

    // $('#innerForm').load(function() {
    //     var iframe = $('#innerForm').contents();
    //     iframe.find("#wpadminbar").hide();
    //     iframe.find(".section-inner").hide();

    //     // iframe.find(".ff-btn-submit").hide();

    //     iframe.find("form").submit(function() {
    //         setTimeout(function() {
    //             var flag = true;
    //             iframe.find(".text-danger").each(function() {
    //                 flag = false;
    //             });
    //             if (flag) {
    //                 insertcampaign();
    //             }
    //         }, 10);


    //     });


    // });

    // function insertcampaign() {
    //     var val = $("#innerForm").attr('data-campid');
    //     if (val != '') {
    //         var type = 'insertcampaigndetails';
    //         $.ajax({
    //             url: 'ajax_data.php',
    //             type: 'post',
    //             data: { val: val, type: type },
    //             dataType: "json",
    //             async: true,
    //             success: function(response) {

    //             }
    //         });
    //     }
    // }

    $(document).on('keypress', '#numAllow', function (e) {
        var keyCode = e.which;
        if ((keyCode != 8 || keyCode == 32) && (keyCode < 48 || keyCode > 57)) {
            return false;
        }
    });

    $(document).on('keydown', '.numfield', function (event) {
        if (event.shiftKey == true) {
            event.preventDefault();
        }

        if ((event.keyCode >= 48 && event.keyCode <= 57) ||
            (event.keyCode >= 96 && event.keyCode <= 105) ||
            event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 ||
            event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190 || event.keyCode == 67 || event.keyCode == 86 || event.keyCode == 65) {

        } else {
            event.preventDefault();
        }

        if ($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
            event.preventDefault();
    });

    $("#timefield").focusin(function (evt) {
        $(this).keypress(function () {
            var content = $(this).val();
            var content1 = content.replace(/\:/g, '');
            var length = content1.length;
            if (((length % 2) == 0) && length < 10 && length > 1) {
                $('#timefield').val($('#timefield').val() + ':');
            }
        });
    });


    $(document).on('click', '#addFixedMultipleFineRule', function () {
        var cid = $(this).attr('data-cid');
        var ncid = parseInt(cid) + 1;
        $(this).attr('data-cid', ncid);
        var design = '<div id="seatdiv" class="remdiv seatdiv row mb-1 deltr' + ncid + '"><div class="col-sm  newdes nobrdbtm1"><div class="input-group stylish-input-group" ><div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" data-aid="' + ncid + '" id="from_date' + ncid + '" name="from_date[' + ncid + ']" class="chkfrmdate fdate w-full txtfield" autocomplete="off" maxlength="10"></div></div></div></div><div class="col-sm  newdes nobrdbtm1"><div class="input-group stylish-input-group"><div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" data-aid="' + ncid + '" id="to_date' + ncid + '" name="to_date[' + ncid + ']" class="chktodate tdate w-full txtfield" autocomplete="off" maxlength="10"></div></div></div></div><div class="col-sm  newdes nobrdbtm1"><div class="input-group stylish-input-group"><div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><select id="fixed_rule_item_type" name="fixed_rule_item_type[' + ncid + ']" class="w-full txtfield"><option value="Fixed">Fixed</option><option value="Percentage">Percentage</option></select></div></div></div></div><div class="col-sm  newdes nobrdbtm1"><div class="input-group stylish-input-group" style="display:inline-flex;"><div class="dte mb-1"></div><div class=" txtfield kountseat szewdt mb-1"><div class="flex-1 relative"><input type="text" id="fixed_rule_amt_per" name="fixed_rule_amt_per[' + ncid + ']" class="chkamnt w-full txtfield kountseat szewdt numfield"></div></div><div class="dte mb-1"  style="font-size: 25px; padding:  0px 0 0px 4px; width: 30px"><i style="cursor:pointer" class="mdi mdi-close-circle mdi-24px delSeattr" data-id="' + ncid + '"></i></div></div></div></div>';
        $("#seatdiv2").before(design);

    });

    $(document).on('click', '#addDaySlabFineRule', function () {
        var cid = $(this).attr('data-cid');
        var ncid = parseInt(cid) + 1;
        $(this).attr('data-cid', ncid);
        var design = '<div id="seatdiv2" class="remdiv seatdiv row mb-1 deltr' + ncid + '"><div class="col-sm  newdes nobrdbtm1"><div class="input-group stylish-input-group" ><div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" data-aid="' + ncid + '" id="from_day' + ncid + '" name="from_day[' + ncid + ']" class="chkfrmday w-full txtfield numfield" autocomplete="off" maxlength="10"></div></div></div></div><div class="col-sm  newdes nobrdbtm1"><div class="input-group stylish-input-group"><div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" data-aid="' + ncid + '" id="to_day' + ncid + '" name="to_day[' + ncid + ']" class="chktoday w-full txtfield numfield" autocomplete="off" maxlength="10"></div></div></div></div><div class="col-sm  newdes nobrdbtm1"><div class="input-group stylish-input-group"><div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><select id="day_slab_item_type" name="day_slab_item_type[' + ncid + ']" class="w-full txtfield"><option value="Fixed">Fixed</option><option value="Percentage">Percentage</option></select></div></div></div></div><div class="col-sm  newdes nobrdbtm1"><div class="input-group stylish-input-group" style="display:inline-flex;"><div class="dte mb-1"></div><div class=" txtfield kountseat szewdt mb-1"><div class="flex-1 relative"><input type="text" id="day_slab_amt_per" name="day_slab_amt_per[' + ncid + ']" class="chkdayamnt w-full txtfield kountseat szewdt numfield"></div></div><div class="dte mb-1"  style="font-size: 25px; padding:  0px 0 0px 4px; width: 30px"><i style="cursor:pointer" class="mdi mdi-close-circle mdi-24px delSeattr" data-id="' + ncid + '"></i></div></div></div></div>';
        $("#lastseatdiv").before(design);

    });


    $(document).on('change', '.parentfineType', function () {
        var cid = $(this).val();
        if ($(this).is(':checked')) {
            $(".hdefield").attr("readonly", true);
            $(".remdiv").remove();
            $("#chkd").prop("checked", true);
            $("#firstId").attr("readonly", false);
            $(".fsize").addClass('hidediv');
            if (cid == '1') {
                $("#labelChng").html('');
                $("#labelChng").html('Fixed Fine Rule Type');
                //$(".fixedfine").show();
                $(".fixedfine").removeClass("hidediv");
                $(".fixedfine").removeAttr("style");
                $(".dayslab").removeClass("hidediv");
                $(".dayslab").removeAttr("style");
                $("#addFixedMultipleFineRule").addClass('hidediv');
                $(".seatdiv").addClass('hidediv');
                $(".fsize").addClass('hidediv');
                $(".dayslabfine").addClass('hidediv');
                $(".seatdiv2").addClass('hidediv');
            } else if (cid == '2') {
                $("#labelChng").html('');
                $("#labelChng").html('Daily Fine Rule Type');
                $(".fixedfine").hide();
                $(".dayslab").removeClass("hidediv");
                $(".dayslab").removeAttr("style");
                $(".seatdiv").addClass('hidediv');
                $(".seatdiv2").addClass('hidediv');
                $(".dayslabfine").addClass('hidediv');
            } else {
                $("#labelChng").html('');
                $("#labelChng").html('Day Fine Rule Type');
                $(".fixedfine").hide();
                $(".dayslabfine").removeClass('hidediv');
                $(".dayslab").hide();
                $(".dayslabfine").removeAttr("style");
                $(".seatdiv").addClass('hidediv');
                $(".seatdiv2").addClass('hidediv');

                // $(".dayslabfine").removeClass('hidediv');
                // $("#addFixedMultipleFineRule").removeClass('hidediv');
                // $(".fsize").removeClass('hidediv');
                // $(".seatdiv").removeClass('hidediv');
            }
        }

    });

    $(document).on('change', '.fineType', function () {
        var cid = $(this).val();
        if ($(this).is(':checked')) {
            $(".hdefield").attr("readonly", true);
            if (cid == '1') {
                $("#firstId").attr("readonly", false);
                $("#addFixedMultipleFineRule").addClass('hidediv');
                $(".seatdiv").addClass('hidediv');
                $(".seatdiv2").addClass('hidediv');
                $(".fsize").addClass('hidediv');
            } else if (cid == '2') {
                $("#secondId").attr("readonly", false);
                $("#addFixedMultipleFineRule").addClass('hidediv');
                $(".seatdiv").addClass('hidediv');
                $(".seatdiv2").addClass('hidediv');
                $(".fsize").addClass('hidediv');
            } else if (cid == '3') {
                $("#addFixedMultipleFineRule").removeClass('hidediv');
                $(".fsize").removeClass('hidediv');
                $(".seatdiv").removeClass('hidediv');
            } else {
                $("#addDaySlabFineRule").removeClass('hidediv');
                $(".seatdiv").addClass('hidediv');
                $(".fsize").removeClass('hidediv');
                $(".seatdiv2").removeClass('hidediv');
            }
        }

    });

    $("#fdate").datepicker({
        //minDate: 0,
        onClose: function () {
            var date2 = $(this).datepicker('getDate');
            date2.setDate(date2.getDate() + 1);

            $("#tdate").datepicker("option", "minDate", date2);
        }
    });

    $("#due_date").datepicker({
        //minDate: 0
    });

    $("#tdate").on("change", function () {
        var date2 = $(this).datepicker('getDate', '+1d');
        date2.setDate(date2.getDate() + 1);
        var selected = $.datepicker.formatDate('dd/mm/yy', date2);
        $("input[name=lastDate]").val(selected);
    });

    $(".fdate").live('click', function () {
        var aid = $(this).attr('data-aid');
        $(this).datepicker({
            minDate: $("input[name=lastDate]").val(),
            onClose: function () {
                var date2 = $(this).datepicker('getDate');
                date2.setDate(date2.getDate() + 1);
                var selected = $.datepicker.formatDate('dd/mm/yy', date2);
                $("input[name=startDate]").val(selected);
            }
        }).datepicker("show");
    });

    $(".tdate").live('click', function () {
        var aid = $(this).attr('data-aid');
        $(this).datepicker({
            minDate: $("input[name=startDate]").val(),
            onClose: function () {
                var date2 = $(this).datepicker('getDate');
                date2.setDate(date2.getDate() + 1);
                var selected = $.datepicker.formatDate('dd/mm/yy', date2);
                $("input[name=lastDate]").val(selected);
            }
        }).datepicker("show");
    });

    $(document).on('click', '.delFineRuleType', function () {
        var val = $(this).attr('data-id');
        var type = 'delFineRuleType';
        if (val != '') {
            if (confirm("Are you sure want to Delete")) {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $(".deltr" + val).remove();
                    }
                });
            }
        }
    });

    $(document).on('click', '#addCategoryRule', function () {
        var cid = $(this).attr('data-cid');
        var ncid = parseInt(cid) + 1;
        $(this).attr('data-cid', ncid);
        var type = 'getAjaxDiscountCategory';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: ncid, type: type },
            async: true,
            success: function (response) {
                $("#seatdiv2").before(response);
            }
        });
    });

    $(document).on('click', '#addInvoiceCountRule', function () {
        var cid = $(this).attr('data-cid');
        var ncid = parseInt(cid) + 1;
        $(this).attr('data-cid', ncid);
        var type = 'getAjaxInvoiceCategory';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: ncid, type: type },
            async: true,
            success: function (response) {
                $("#lastseatdiv").before(response);
            }
        });
    });

    $(document).on('change', '.discountfineType', function () {
        var cid = $(this).val();
        if ($(this).is(':checked')) {
            // $(".hdefield").attr("readonly", true); 
            // $(".remdiv").remove();
            // $("#chkd").prop("checked", true);
            // $("#firstId").attr("readonly", false);
            // $(".fsize").addClass('hidediv');
            if (cid == '1') {
                $(".catbutt").removeClass('hidediv');
                $(".invbutt").addClass('hidediv');
                $("#labelChng").html('');
                $("#labelChng").html('Category');
                //$(".fixedfine").show();
                $(".fixedfine").removeClass("hidediv");
                $(".fixedfine").removeAttr("style");
                $("#addCategoryRule").removeClass('hidediv');
                $(".seatdiv").removeClass('hidediv');
                //$(".fsize").removeClass('hidediv');
                $("#addInvoiceCountRule").addClass('hidediv');
                $(".dayslabfine").addClass('hidediv');
                $(".seatdiv2").addClass('hidediv');
            } else if (cid == '2') {
                // $(".fsize").removeClass('hidediv');
                $(".invbutt").removeClass('hidediv');
                $(".catbutt").addClass('hidediv');
                $("#addInvoiceCountRule").removeClass('hidediv');
                $("#addCategoryRule").addClass('hidediv');
                $("#labelChng").html('');
                $("#labelChng").html('Invoice Count');
                $(".fixedfine").hide();
                $(".seatdiv").addClass('hidediv');
                $(".seatdiv2").removeClass('hidediv');
                $(".dayslabfine").removeClass('hidediv');
            }
        }

    });


    $(document).on('click', '.delDiscountRuleType', function () {
        var val = $(this).attr('data-id');
        var type = 'delDiscountRuleType';
        if (val != '') {
            if (confirm("Are you sure want to Delete")) {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $(".deltr" + val).remove();
                    }
                });
            }
        }
    });

    $(document).on('click', '#addFeeStructureItem', function () {
        var cid = $(this).attr('data-cid');
        var ncid = parseInt(cid) + 1;
        $(this).attr('data-cid', ncid);
        var disid = $(this).attr('data-disid');
        var type = 'getAjaxFeeStructureItem';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: ncid, type: type, disid: disid },
            async: true,
            success: function (response) {
                $("#lastseatdiv").before(response);
            }
        });
    });

    $(document).on('click', '.delFeeStructureItem', function () {
        var val = $(this).attr('data-id');
        var type = 'delFeeStructureItem';
        if (val != '') {
            if (confirm("Are you sure want to Delete")) {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $(".deltr" + val).remove();
                    }
                });
            }
        }
    });


    $(document).on('change', '#feeStructureItemDisableId', function (e) {
        e.preventDefault();
        var favorite = [];
        $(".allFeeItemId").each(function () {
            favorite.push($(this).val());
        });
        var getid = favorite.join(",");
        var newdisid = getid.replace(/^,|,$/g, '');
        $("#addFeeStructureItem").attr('data-disid', newdisid);
        $("#addInvoiceItem").attr('data-disid', newdisid);
    });

    $(document).on('click', '#assignStudentPage', function () {
        var url = $(this).attr('data-href');
        var favorite = [];
        $.each($("input[name='stuid[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");
        //alert(subid);
        if (stuid) {
            var val = stuid;
            var type = 'addstudentidInSession';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#clickStudentPage").click();
                    }
                });
            }
        } else {
            alert('You Have to Select Students.');
        }
    });



    $(document).on('change', '#onwardroute', function () {
        var selected_route = $("#onwardroute").val();
        //alert(selected_route);
        if (selected_route) {
            var val = selected_route;
            var type = 'getstopname';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#onward_stops").html('');
                        $("#onward_stops").html(response);

                    }
                });
            }


        } else {
            alert('You Have to Select route.');
        }
    });
    $(document).on('change', '#return_rt', function () {
        var selected_route = $("#return_rt").val();
        if (selected_route) {
            var val = selected_route;
            var type = 'getstopname';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#toward_stops").html('');
                        $("#toward_stops").html(response);

                    }
                });
            }


        } else {
            alert('You Have to Select route.');
        }
    });

    $(document).on('change', '#onward_rt_new', function () {
        var selected_route = $(this).val();
        if (selected_route) {
            var val = selected_route;
            var type = 'getstopname';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#onward_sp_new").html('');
                        $("#onward_sp_new").html(response);
                        if ($("#addReturnRoute").is(':checked')) {
                            $("#return_rt_new").val(val);
                            $("#return_rt_new").trigger('change');
                        }
                    }
                });
            }


        } else {
            alert('You Have to Select route.');
        }
    });

    $(document).on('change', '#return_rt_new', function () {
        var selected_route = $("#return_rt_new").val();
        if (selected_route) {
            var val = selected_route;
            var type = 'getstopname';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#return_sp_new").html('');
                        $("#return_sp_new").html(response);

                    }
                });
            }


        } else {
            alert('You Have to Select route.');
        }
    });

    $(document).on('click', '#assignStudentroute', function () {
        var atype = $(this).attr('data-type');
        var favorite = [];
        var flag = true;
        $.each($("input[name='stuid[]']:checked"), function () {
            var routeid = $(this).attr("routeid");
            if (routeid != "") {
                alert("User is already assigned for the route.");
                flag = false;
                return;
            } else {
                favorite.push($(this).val());
            }
        });

        if (!flag) {
            return;
        }

        var stuid = favorite.join(",");
        if (stuid) {
            var val = stuid;
            var type = 'addstudentidInSession';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#clickStudentroute").click();
                    }
                });
            }
        } else {
            if (atype == 'student') {
                alert('You Have to Select Students.');
            } else {
                alert('You Have to Select Staff.');
            }

        }
    });



    $(document).on('click', '#copyStudentroute', function () {
        var atype = $(this).attr('data-type');
        var favorite = [];
        var flag = true;
        var count = 0
        $.each($("input[name='stuid[]']:checked"), function () {
            var routeid = $(this).attr("routeid");
            if (routeid == "") {
                //alert("Sorry..For copying process you must select student who have Route Details.");
                count++;
                flag = false;
                return;
            } else {
                favorite.push($(this).val());
            }

        });
        if (count > 0) {
            alert("Sorry..For copying process you must select students who have Route Details.");
        }
        if (!flag) {
            return;
        }

        var stuid = favorite.join(",");
        if (stuid) {
            var val = stuid;
            var type = 'addstudentidInSession';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#copyroute").click();
                    }
                });
            }
        } else {
            if (atype == 'student') {
                alert('You Have to Select Students.');
            } else {
                alert('You Have to Select Staff.');
            }

        }
    });

    $(document).on('click', '#btn_transport_assign_route', function () {
        var transFor = $("#transport_for").val();
        if (transFor == "") {
            alert("Select transport oneway or two way.");
            return;
        }
        if (transFor == "oneway") {
            var select_route = $("#select_route").val();
            if (select_route == "") {
                alert("Select route then continue");
                return;
            }

            if (select_route == "onward") {
                var onwardroute = $("#onwardroute").val();
                if (onwardroute == "") {
                    alert("Select Onward route then continue");
                    return;
                }

                var onward_stops = $("#onward_stops").val();
                if (onward_stops == "") {
                    alert("Select Onward stop then continue");
                    return;
                }

            } else {
                //return route check
                var return_rt = $("#return_rt").val();
                if (return_rt == "") {
                    alert("Select Return route then continue");
                    return;
                }
                var toward_stops = $("#toward_stops").val();
                if (toward_stops == "") {
                    alert("Select Return stop then continue");
                    return;
                }

            }

        } else {
            //two way
            var onward_rt_new = $("#onward_rt_new").val();
            if (onward_rt_new == "") {
                alert("Select Onward route then continue");
                return;
            }

            var onward_sp_new = $("#onward_sp_new").val();
            if (onward_sp_new == "") {
                alert("Select Onward stop then continue");
                return;
            }

            var return_rt_new = $("#return_rt_new").val();
            if (return_rt_new == "") {
                alert("Select Return route then continue");
                return;
            }

            var return_sp_new = $("#return_sp_new").val();
            if (return_sp_new == "") {
                alert("Select Return stop then continue");
                return;
            }
        }

        document.getElementById("frm_transport_assign_route").submit();
    });

    $(document).on('click', '#unassignStudentroute', function () {
        var atype = $(this).attr('data-type');
        if (atype == 'student') {
            var url = $(this).attr('data-href');
            var favorite = [];
            var stuname = [];
            var flag = true;
            $.each($("input[name='stuid[]']:checked"), function () {
                var routeid = $(this).attr("routeid");
                var routepaid = $(this).attr("data-chk");
                var routeretpaid = $(this).attr("data-rtchk");
                var name = $(this).attr("data-name");
                if (routeid == "") {
                    if (atype == 'student') {
                        alert("Please remove Student which are not assigned for the route");
                    } else {
                        alert("Please remove Staff which are not assigned for the route");
                    }

                    flag = false;
                    return;
                } else {
                    if (routepaid == 'unpaid' || routeretpaid == 'unpaid') {
                        stuname.push(name);
                        flag = false;
                        return;
                    } else {
                        favorite.push($(this).val());
                    }

                }
            });

            if (stuname != '') {
                alert(stuname + ' are not Paid Transport Fees, So Please remove this Student and then Unassign Route!');
                return false;
            }

            if (!flag) {
                return;
            }

            var stuid = favorite.join(",");
            // alert(stuid);
            if (stuid) {
                if (atype == 'student') {
                    var al = 'You are un-assigning route for Selected Student.';
                } else {
                    var al = 'You are un-assigning route for Selected Staff.';
                }
                if (confirm(al)) {
                    var val = stuid;
                    var type = 'deleteStudentRoutes';
                    if (val != '') {
                        $.ajax({
                            url: 'ajax_data.php',
                            type: 'post',
                            data: { val: val, type: type },
                            async: true,
                            success: function (response) {
                                location.reload();
                            }
                        });
                    }
                }
            } else {
                if (atype == 'student') {
                    alert('You Have to Select Students.');
                } else {
                    alert('You Have to Select Staff.');
                }
            }
        } else {
            var url = $(this).attr('data-href');
            var favorite = [];
            var flag = true;
            $.each($("input[name='stuid[]']:checked"), function () {
                var routeid = $(this).attr("routeid");
                if (routeid == "") {
                    if (atype == 'student') {
                        alert("Please remove Student which are not assigned for the route");
                    } else {
                        alert("Please remove Staff which are not assigned for the route");
                    }

                    flag = false;
                    return;
                } else {
                    favorite.push($(this).val());
                }
            });

            if (!flag) {
                return;
            }

            var stuid = favorite.join(",");
            // alert(stuid);
            if (stuid) {
                if (atype == 'student') {
                    var al = 'You are un-assigning route for Selected Student.';
                } else {
                    var al = 'You are un-assigning route for Selected Staff.';
                }
                if (confirm(al)) {
                    var val = stuid;
                    var type = 'deleteStudentRoutes';
                    if (val != '') {
                        $.ajax({
                            url: 'ajax_data.php',
                            type: 'post',
                            data: { val: val, type: type },
                            async: true,
                            success: function (response) {
                                location.reload();
                            }
                        });
                    }
                }
            } else {
                if (atype == 'student') {
                    alert('You Have to Select Students.');
                } else {
                    alert('You Have to Select Staff.');
                }
            }
        }
    });



    $(document).on('click', '#changeStudentroute', function () {
        var atype = $(this).attr('data-type');

        if (atype == 'student') {
            var checked = $("input[name='stuid[]']:checked").length;
            if (checked > 1) {
                alert("Please Select One Student!");
                return false;
            } else {
                var favorite = [];
                var stuname = [];
                $.each($("input[name='stuid[]']:checked"), function () {
                    var routepaid = $(this).attr("data-chk");
                    var routeretpaid = $(this).attr("data-rtchk");
                    var name = $(this).attr("data-name");
                    if (routepaid == 'unpaid' || routeretpaid == 'unpaid') {
                        stuname.push(name);
                    } else {
                        favorite.push($(this).val());
                    }
                });
                if (stuname != '') {
                    alert(stuname + ' is not Paid Transport Fees, So Please Paid Fees and then Change the Route!');
                    return false;
                }
                var stuid = favorite.join(",");
                // alert(stuid);
                if (stuid) {
                    if (confirm("Are you sure want to Change Route")) {
                        var val = stuid;
                        var type = 'changeStudentRoutes';
                        if (val != '') {
                            $.ajax({
                                url: 'ajax_data.php',
                                type: 'post',
                                data: { val: val, type: type },
                                async: true,
                                success: function (response) {
                                    $("#changeClickStudentroute").click();
                                }
                            });
                        }
                    }
                } else {
                    alert('You Have to Select Students.');
                }
            }
        } else {
            var checked = $("input[name='stuid[]']:checked").length;
            if (checked > 1) {
                if (atype == 'student') {
                    alert("Please Select One Student!");
                } else {
                    alert("Please Select One Staff!");
                }

                return false;
            } else {
                var favorite = [];
                $.each($("input[name='stuid[]']:checked"), function () {
                    favorite.push($(this).val());
                });
                var stuid = favorite.join(",");
                // alert(stuid);
                if (stuid) {
                    if (confirm("Are you sure want to Change Route")) {
                        var val = stuid;
                        var type = 'changeStudentRoutes';
                        if (val != '') {
                            $.ajax({
                                url: 'ajax_data.php',
                                type: 'post',
                                data: { val: val, type: type },
                                async: true,
                                success: function (response) {
                                    $("#changeClickStudentroute").click();
                                }
                            });
                        }
                    }
                } else {
                    if (atype == 'student') {
                        alert('You Have to Select Students.');
                    } else {
                        alert('You Have to Select Staff.');
                    }
                }
            }
        }
    });


    $(document).on('click', '#deleteStudentPage', function () {
        var url = $(this).attr('data-href');
        var favorite = [];
        $.each($("input[name='stuid[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");
        //alert(subid);
        if (stuid) {
            var val = stuid;
            var type = 'addstudentidInSession';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#deleteStudentFeeStructure").click();
                    }
                });
            }
        } else {
            alert('You Have to Select Students.');
        }
    });

    $(document).on('click', '#massDeleteStudentPage', function () {
        var url = $(this).attr('data-href');
        var favorite = [];
        $.each($("input[name='stuid[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");
        //alert(subid);
        if (stuid) {
            var val = stuid;
            var type = 'addstudentidInSession';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#massDeleteStudentFeeStructure").click();
                    }
                });
            }
        } else {
            alert('You Have to Select Students.');
        }
    });

    $(document).on('click', '#addInvoiceItem', function () {
        var cid = $(this).attr('data-cid');
        var ncid = parseInt(cid) + 1;
        $(this).attr('data-cid', ncid);
        var disid = $(this).attr('data-disid');
        var type = 'getAjaxInvoiceItem';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: ncid, type: type, disid: disid },
            async: true,
            success: function (response) {
                $("#lastseatdiv").before(response);
            }
        });
    });

    $(document).on('click', '.delInvoiceItem', function () {
        var val = $(this).attr('data-id');
        var type = 'delInvoiceItem';
        if (val != '') {
            if (confirm("Are you sure want to Delete")) {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $(".deltr" + val).remove();
                    }
                });
            }
        }
    });
    $(document).on('change', '.invoice_studentByClass', function () {
        var val = $(this).val();
        var pid = $(".program_cls option:selected").val();
        var type = 'filterstudentbyclass';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type, pid: pid },
                async: true,
                success: function (response) {
                    $("#filterStudentByClass").html('');
                    $("#filterStudentByClass").html(response);
                }
            });
        }
    });

    $(document).on('change', '#studentByClass', function () {
        var val = $(this).val();
        var type = 'filterstudentbyclass';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    $("#filterStudentByClass").html('');
                    $("#filterStudentByClass").html(response);
                }
            });
        }
    });

    $(document).on('click', '#quickpaymentpage', function () {

        var url = $(this).attr('data-href');
        var favorite = [];
        $.each($("input[name='stuid[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");
        //alert(subid);
        if (stuid) {
            var val = stuid;
            var type = 'addstudentidInSession';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#clickQuickcashpaymentPage").click();
                    }
                });
            }
        } else {
            alert('You Have to Select Applicants.');
        }


    });
    // for quick cash payment 

    $(document).on('keyup', '#program_quick #amount', function () {
        var amount = 0;
        var fine = 0;
        var grndtot = 0;

        var amount = parseInt($(this).val()) || 0;
        $(document).on('keyup', '#fine', function () {
            var fine = parseInt($('#fine').val()) || 0;
            var grndtot = (amount + fine);
            $("#grand_total").val(grndtot);
        });
        var fine = parseInt($('#fine').val()) || 0;
        var grndtot = (amount + fine);
        $("#grand_total").val(grndtot);

    });

    $(document).on('click', '#makePayment', function () {
        $("#collectionForm").show();
        $("#FeeItemManage").show();
        $(".hideFeeItemContent").show();
    });

    $(document).on('click', '#closePayment', function () {
        $(".btn_invoice_link_collection").show();
        $(".addInvoiceLinkCollection").show();
        $(".btn_cancel_invoice_collection").show();
        $(".chkinvoice").show();
        $(".apply_discount_btn").show();
        $('.chkinvoiceM').each(function () {
            this.checked = false;
        });
        $('#chkAllInvoice').removeAttr('checked');
        $("#collectionForm").hide();
        $("#FeeItemManage").hide();
        $(".hideFeeItemContent").hide();
        $(".oCls_1").show();
        $('.icon_1').removeClass('fa-arrow-right');
        $('.icon_1').addClass('fa-arrow-down');
        $(".chkinvoice").prop('checked', false);
    });
    $(document).on('change', '.chkinvoiceM', function () {
        var favorite = [];
        $.each($(".chkinvoiceM:checked"), function () {
            favorite.push($(this).val());
        });
        var l = favorite.length;
        if (l != 0) {
            $(".chkinvoice").show();
            $(".apply_discount_btn").show();
            $(".btn_invoice_link_collection").show();
            $(".addInvoiceLinkCollection").show();
            $(".btn_cancel_invoice_collection").show();
        } else {
            $(".chkinvoice").hide();
            $(".apply_discount_btn").hide();
            $(".btn_invoice_link_collection").hide();
            $(".addInvoiceLinkCollection").hide();
            $(".btn_cancel_invoice_collection").hide();
        }
    });
    $(document).on('change', '#chkAllInvoice', function () {
        if ($(this).is(':checked')) {
            $(".chkinvoice").show();
            $(".apply_discount_btn").show();
            $(".chkinvoiceM").prop('checked', true);
        } else {
            $(".chkinvoice").hide();
            $(".apply_discount_btn").hide();
            $(".chkinvoiceM").prop('checked', false);
        }
        //if ($(this).is(':checked')) {
        //$(".chkinvoiceM").prop('checked', true);
        /*  var favorite = [];
            $.each($(".chkinvoiceM:checked"), function() {
                if ($(this).val() != '0') {
                    favorite.push($(this).val());
                }
            });
            var invids = favorite.join(", ");
            var sid = $("input[name=pupilsightPersonID]").val();
            //var invid = $(this).val();
            // $("#showPaymentButton").show();
            var type = 'invoiceFeeItem';
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: invids, type: type, sid: sid },
                async: true,
                success: function(response) {
                    $(".chkinvoice").hide();
                    $("#getInvoiceFeeItem").html('');
                    $("#getInvoiceFeeItem").append(response);
                    $("input[name=invoice_id]").val(invids);
                    $("#collectionForm").show();
                    $("#FeeItemManage").show();
                    $(".hideFeeItemContent").show();
                     setTimeout(function(){
                            $("#chkAllFeeItem").prop("checked",true).trigger("change");
                        },1000);
                }
            });
        } else {
            $(".chkinvoiceM").prop('checked', false);
            $("#getInvoiceFeeItem").html('');
            addInvoiceFeeAmt();
            $("input[name=invoice_id]").val('');
            $("#closePayment").trigger('click');
        }*/
    });


    $(document).on('click', '.chkinvoice', function () {
        $("#collectionForm")[0].reset();
        $(".ddChequeRow").addClass('hiddencol');
        $("#paymentMode").focus();
        var favorite = [];
        var account_heads = [];
        var series = [];
        var aedt = [];
        var ife = [];
        $.each($(".chkinvoiceM:checked"), function () {
            favorite.push($(this).val());
            account_heads.push($(this).attr("data-h"));
            series.push($(this).attr("data-se"));
            aedt.push($(this).attr("data-amtedt"));
            ife.push($(this).attr("data-ife"));
        });
        var newData = removeDuplicates(account_heads);
        var length1 = newData.length;
        var chkStatus = false;
        if (favorite.length != 0) {
            var sid = $("input[name=pupilsightPersonID]").val();
            if (length1 == "1") {
                chkStatus = true;
            } else {
                var r = confirm("Selected invoice receipt series are different.\n Do you want to make payment?");
                if (r == true) {
                    chkStatus = true;
                } else {
                    chkStatus = false;
                }
            }

            //ajax request
            if (chkStatus == true) {
                var invids = favorite.join(", ");
                var type = 'invoiceFeeItem';
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: invids, type: type, sid: sid },
                    async: true,
                    success: function (response) {
                        $(".btn_invoice_link_collection").hide();
                        $(".addInvoiceLinkCollection").hide();
                        $(".btn_cancel_invoice_collection").hide();
                        $(".chkinvoice").hide();
                        $(".apply_discount_btn").hide();
                        $("#getInvoiceFeeItem").html('');
                        $("#getInvoiceFeeItem").append(response);
                        $("input[name=invoice_id]").val(invids);
                        $("#collectionForm").show();
                        $("#FeeItemManage").show();
                        $(".hideFeeItemContent").show();
                        $('#fn_fees_head_id').val(account_heads[0]);
                        $('#recptSerId').val(series[0]);
                        $(".oCls_0").hide();
                        $('.icon_0').removeClass('mdi mdi-arrow-down-thick');
                        $('.icon_0').addClass('mdi mdi-arrow-right-thick');
                        $(".oCls_1").hide();
                        $('.icon_1').removeClass('mdi mdi-arrow-down-thick');
                        $('.icon_1').addClass('mdi mdi-arrow-right-thick');

                        if (aedt[0] == '1') {
                            $("#amount_paying").attr("readonly", false);
                        } else {
                            $("#amount_paying").attr("readonly", true);
                        }

                        if (ife[0] == '1') {
                            $("#fine").attr("readonly", false);
                        } else {
                            $("#fine").attr("readonly", true);
                        }

                        setTimeout(function () {
                            $("#chkAllFeeItem").prop("checked", true).trigger("change");
                        }, 1000);

                    }
                });
            }
            //ends request
        } else {
            alert('Please select atleast one invoice');
            $("#chkAllInvoice").prop('checked', false);
            $(".invrow" + invid).remove();
            addInvoiceFeeAmt();
            $("input[name=invoice_id]").val(invids);
        }
    });


    $(document).on('change', '#chkAllFeeItem', function () {
        if ($(this).is(':checked')) {
            $(".selFeeItem").prop('checked', true);
            addInvoiceFeeAmt();
        } else {
            $(".selFeeItem").prop('checked', false);
            addInvoiceFeeAmt();
        }

    });

    $(document).on('change', '.selFeeItem', function () {
        /*
        if($(this).is(':checked')){
           addInvoiceFeeAmt();
        } else {
            $("#chkAllFeeItem").prop('checked',false);
            addInvoiceFeeAmt();
        }  
        addInvoiceFeeAmt();
        */
        waitAddInvoiceFeeAmt();
    });

    function waitAddInvoiceFeeAmt() {
        setTimeout(function () {
            addInvoiceFeeAmt();
        }, 100);
    }

    function addInvoiceFeeAmt() {
        var favorite = [];
        var feetotal = 0;
        var desctotal = 0;
        var amtpay = 0;
        var finefeetotal = 0;
        var totalfineamt = 0;
        var invoiceid = 0;
        var tmp = new Array();

        //only for number invoice
        var percentageFlag = false;
        var feeItemLength = $('.selFeeItem').length;
        var chkfeeItemLength = $('.selFeeItem:checked').length;
        if (feeItemLength > chkfeeItemLength) {
            $("input[name='invoice_status'").val("Partial Paid");
        } else {
            $("input[name='invoice_status'").val("Fully Paid");
        }

        $.each($(".selFeeItem:checked"), function () {

            var flag = false;
            var invid = $(this).attr('data-invid');
            var finetype = $(".invoice" + invid).attr('data-ftype');
            if (finetype == 'num') {
                flag = true;
            } else {
                percentageFlag = true;
            }

            if (flag) {
                console.log(this);
                // feetotal += parseFloat($(this).attr('data-amt')) || 0; 
                var tfeetotal = Number($(this).attr('data-totamt')) || 0;
                feetotal += tfeetotal;
                amtpay += Number($(this).attr('data-amt')) || 0;
                favorite.push($(this).val());
                desctotal += Number($(this).attr('data-dis')) || 0;
                var fineamt = $(".invoice" + invid).attr('data-fper');
                if (invoiceid != invid) {

                    totalfineamt += Number(fineamt);
                    console.log(fineamt, totalfineamt, tfeetotal);
                    invoiceid = invid;
                }
            }
        });

        var feetotal1 = 0;
        var desctotal1 = 0;
        var amtpay1 = 0;
        var finefeetotal1 = 0;
        var totalfineamt1 = 0;
        var invoiceid1 = 0;

        //only for percentage invoice
        if (percentageFlag) {
            //console.log("cehck perc");
            $.each($(".selFeeItem:checked"), function () {
                //console.log(this);
                var flag = false;
                var invid = $(this).attr('data-invid');
                var finetype = $(".invoice" + invid).attr('data-ftype');
                if (finetype != 'num') {
                    flag = true;
                }
                if (flag) {
                    // feetotal += parseFloat($(this).attr('data-amt')) || 0; 
                    var tfeetotal1 = Number($(this).attr('data-totamt')) || 0;
                    amtpay1 += Number($(this).attr('data-amt')) || 0;
                    favorite.push($(this).val());
                    desctotal1 += Number($(this).attr('data-dis')) || 0;
                    var fineamt = $(".invoice" + invid).attr('data-fper');
                    //if(invoiceid1 != invid){
                    //totalfineamt1 += Number(fineamt);
                    //var dec = (Number(fineamt) / 100); 
                    totalfineamt1 += (tfeetotal1 * Number(fineamt)) / 100;
                    feetotal1 += tfeetotal1;
                    //console.log(totalfineamt1,fineamt);
                    // invoiceid1 = invid;
                    // }        
                }
            });
            //console.log(feetotal, totalfineamt);
            feetotal += feetotal1;
            totalfineamt += totalfineamt1;
            desctotal += desctotal1;
        }

        //var totalamount = 0;
        //totalamount += totalfineamt;
        //alert(finefeetotal);
        var invitemids = favorite.join(", ");
        $("#fine").val(totalfineamt);
        $("input[name=fineold]").val(totalfineamt);
        var totalamount = Number(feetotal) + totalfineamt;
        if (desctotal != '') {
            var amtpaying = totalamount - desctotal;
        } else {
            var amtpaying = totalamount;
        }

        $("#total_amount_without_fine_discount").val(feetotal);
        $("#transcation_amount").val(totalamount);
        $("#amount_paying").val(amtpaying);
        $("#transcation_amount_old").val(totalamount);
        $("#amount_paying_old").val(amtpaying);
        $("#discount").val(desctotal);
        $("input[name=invoice_item_id]").val(invitemids);
        $("input[name=chkamount]").val(amtpaying);
    }

    $(document).on('click', '#searchInvoice', function () {
        var pupilsightProgramID = $("#pupilsightProgramID").val();
        var pupilsightSchoolYearID = $("#pupilsightSchoolYearID").val();
        var pupilsightYearGroupID = $("#pupilsightYearGroupID").val();
        var pupilsightRollGroupID = $("#pupilsightRollGroupID").val();
        var search = $("#search").val();
        var val = '1';
        var type = 'searchStudentInvoice';
        if (val != '' && pupilsightProgramID != '' && pupilsightSchoolYearID != '' && pupilsightYearGroupID != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type, pupilsightProgramID: pupilsightProgramID, pupilsightSchoolYearID: pupilsightSchoolYearID, pupilsightYearGroupID: pupilsightYearGroupID, pupilsightRollGroupID: pupilsightRollGroupID, search: search },
                async: true,
                success: function (response) {
                    //$("#studentList").html('');
                    $("#studentList").html('');
                    $("#studentList").html(response);
                    //$("#searchInvoice").hide();
                    //$("#submitInvoice").show();
                    $("#hideStudentListContent").show();
                    $("#stuListTable").show();
                    $("#searchCollectionType").val(2);
                }
            });
        } else {
            alert('You Have to Enter All Fields!');
        }
    });

    $(document).on('keydown', '#simplesearch', function (e) {
        if (e.keyCode == 13) {
            var val = '1';
            var type = 'searchStudent';
            var search = $(this).val();
            if (val != '' && search != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type, search: search },
                    async: true,
                    success: function (response) {
                        //$("#studentList").html('');
                        $("#studentList").html('');
                        $("#studentList").html(response);
                        //$("#searchStudent").hide();
                        //$("#simplesubmitInvoice").show();
                        $("#hideStudentListContent").show();
                        $("#stuListTable").show();
                    }
                });
            }
            return false;
        }
    })

    $(document).on('click', '#searchStudent', function () {
        var search = $("#simplesearch").val();
        var val = '1';
        var type = 'searchStudent';
        if (val != '' && search != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type, search: search },
                async: true,
                success: function (response) {
                    //$("#studentList").html('');
                    $("#studentList").html('');
                    $("#studentList").html(response);
                    //$("#searchStudent").hide();
                    //$("#simplesubmitInvoice").show();
                    $("#hideStudentListContent").show();
                    $("#stuListTable").show();
                    $("#searchCollectionType").val(1);
                }
            });
        }
    });

    $(document).on('click', '#advanceSearch', function () {
        $("#advanceSearchRow").removeClass('hiddencol');
        $("#normalSearchRow").addClass('hiddencol');
    });

    $(document).on('click', '#normalSearch', function () {
        $("#advanceSearchRow").addClass('hiddencol');
        $("#normalSearchRow").removeClass('hiddencol');
    });

    $(document).on('click', '#selStudent', function () {
        var sid = $(this).val();
        $("input[name=studentId]").val(sid);
        $("input[name=pupilsightPersonID]").val(sid);
        $("#simplesubmitInvoice").click();
    });



    $(document).on('click', '#refundTransaction', function () {
        var checked = $("input[name='collection_id[]']:checked").length;
        if (checked > 1) {
            alert("Please Select One Transaction!");
            return false;
        } else {
            var favorite = [];
            $.each($("input[name='collection_id[]']:checked"), function () {
                favorite.push($(this).val());
            });
            var stuid = favorite.join(",");
            //alert(subid);
            if (stuid) {
                var val = stuid;
                var type = 'addtransactionidInSession';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type },
                        async: true,
                        success: function (response) {
                            $("#refundTransactionSubmit").click();
                        }
                    });
                }
            } else {
                alert('You Have to Select Transaction.');
            }
        }
    });

    $(document).on('click', '#creatNum', function () {
        $(".creatNum").removeClass('hidediv');
        $(".creatChar").addClass('hidediv');
    });

    $(document).on('click', '#creatChar', function () {
        $(".creatNum").addClass('hidediv');
        $(".creatChar").removeClass('hidediv');
    });

    $(document).on('click', '#addNum', function () {
        var id = $(this).attr('data-id');
        var format = $("#format").val();
        var format1 = $("#formatval").val();
        var startnum1 = $("#start_number").val();
        var startnum2 = $("#no_of_digit").val();
        var len = startnum1.length;
        if (len != startnum2) {
            alert('Start Number is Not Match with No of Digit');
        } else {
            if (startnum2 != '') {
                if (format == '') {
                    var newformat = '{AB}';
                    var newformat1 = '{AB}';
                } else {
                    var newformat = format + '{AB}';
                    var newformat1 = format1 + '$' + '{AB}';
                }
                $("#format").val(newformat);
                $("#formatval").val(newformat1);

                var field1 = '<input class="numfield" type="hidden" name="st_number[' + id + ']" value="' + startnum1 + '">';
                var field2 = '<input class="numfield" type="hidden" name="no_ofdigit[' + id + ']" value="' + startnum2 + '">';
                $("#feeseries").append(field1);
                $("#feeseries").append(field2);
                $(".creatNum").addClass('hidediv');
                $("#start_number").val('');
                $("#no_of_digit").val('');
                var newid = parseInt(id) + 1;
                $(this).attr('data-id', newid);
            }
        }

    });

    $(document).on('click', '#addChar', function () {
        var format = $("#format").val();
        var format1 = $("#formatval").val();
        var char = $("#start_char").val();
        if (char != '') {
            if (format == '') {
                var newformat = char;
                var newformat1 = char;
            } else {
                var newformat = format + char;
                var newformat1 = format1 + '$' + char;
            }

            $("#format").val(newformat);
            $("#formatval").val(newformat1);
            var field1 = '<input class="numfield" type="hidden" name="startchar[]" value="' + char + '">';
            $("#feeseries").append(field1);
            $(".creatChar").addClass('hidediv');
            $("#start_char").val('');
        }
    });

    $(document).on('click', '#delFormatData', function () {
        $("#format").val('');
        $("#formatval").val('');
        ////$(".numfield").remove();
        $("#addNum").attr('data-id', 1);
    });

    $(document).on('change', '#paymentMode', function () {
        //var val = $(this).val();
        $(".ddCashRow").addClass('hiddencol');
        var val = $("#paymentMode option:selected").text();
        val = val.toUpperCase();
        if (val == 'CHEQUE' || val == 'DD') {
            if (val == 'CHEQUE') {
                $("#payment_status").val('Cheque Received');
                $("#cashPaymentStatus").val('Cheque Received');
            }
            if (val == 'DD') {
                $("#payment_status").val('DD Received');
                $("#cashPaymentStatus").val('DD Received');
            }



            $(".ddChequeRow").removeClass('hiddencol');
            $(".neft_cls").addClass('hiddencol');
            $(".ddDepositRow").addClass('hiddencol');
            $("#deposit_account_id").val('').change();
        } else if (val == 'MULTIPLE') {
            // $("#multiplePayment").click(); 
            $(".ddChequeRow").addClass('hiddencol');
            $(".ddDepositRow").addClass('hiddencol');
            $("#deposit_account_id").val('').change();
            var amount = $('#amount_paying').val();
            var type = 'AmountSession';
            if (amount != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: amount, type: type },
                    async: true,
                    success: function (response) {
                        $("#multiplePayment").click();
                    }
                });
            }

        } else if (val == 'DEPOSIT') {
            $(".ddCashRow").removeClass('hiddencol');
            $(".ddChequeRow").addClass('hiddencol');
            $("#cashPaymentStatus").val('Payment Received');
            $("#payment_status").val('Payment Received').change();
            $(".neft_cls").addClass('hiddencol');
            $(".ddDepositRow").removeClass('hiddencol');

        } else if (val == 'NEFT' || val == 'RTGS' || val == 'CREDIT CARD' || val == 'DEBIT CARD') {
            $("#payment_status").val('Payment Received').change();
            $("#cashPaymentStatus").val('Payment Received');
            $(".ddChequeRow").addClass('hiddencol');
            $(".neft_cls").removeClass('hiddencol');
            $(".ddDepositRow").addClass('hiddencol');
            $("#deposit_account_id").val('').change();
        } else {
            $(".ddCashRow").removeClass('hiddencol');
            $("#cashPaymentStatus").val('Payment Received');
            $("#payment_status").val('Payment Received').change();
            $(".neft_cls").addClass('hiddencol');
            $(".ddChequeRow").addClass('hiddencol');
            $(".ddDepositRow").addClass('hiddencol');
            $("#deposit_account_id").val('').change();
        }
    });

    $(document).on('click', '.chkCounter', function () {
        var pageURL = $(location).attr("href");
        var n = pageURL.lastIndexOf('/');
        var result = pageURL.substring(n + 1);
        var url = $(this).attr('href');
        //alert(url);


        if (result == 'manage_marks_entry_by_subject.php') {

            var msg = 'Do you want to save the Changes.';
            var div = $("<div>" + msg + "</div>");
            div.dialog({
                title: "Confirm",
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                buttons: [{
                    text: "Yes",
                    click: function () {
                        div.dialog("close");
                    }
                },
                {
                    text: "No",
                    click: function () {
                        div.dialog("close");
                        window.location = url;
                    }
                }
                ]
            });
            return false;
        } else {
            var chkcounter = $("#chkCounterSession").attr('data-val');
            if (chkcounter != '') {
                if (confirm("Are you sure want to Leave the Counter?")) {
                    $("#chkCounterSession").attr('data-val', '');
                    var type = 'logoutCounter';
                    var id = chkcounter;
                    var hrf = 'fullscreen.php?q=/modules/Finance/fee_counter_check_add.php';
                    $(".collectionMenu").addClass('thickbox');
                    $(".collectionMenu").attr('href', '');
                    $(".collectionMenu").attr('href', hrf);
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: id, type: type },
                        async: true,
                        success: function (response) {
                            return true;
                            //$("#chkCounterSession").attr('data-val', '');
                        }
                    });
                } else {
                    $("#chkCounterSession").attr('data-chkCounter', '1');
                    return true;
                }
            }
        }



    });


    $(document).on('change', '#recptSerId', function () {
        var val = $(this).val();
        if (val != '') {
            $("#receipt_number").attr('readonly', true);
            $("#is_custom").attr('disabled', true);
        } else {
            $("#receipt_number").attr('readonly', false);
            $("#is_custom").attr('disabled', false);
        }
    });

    $(document).on('change', '.taxOptionSelect', function () {
        var val = $(this).val();
        var id = $(this).attr('data-id');
        if (val == 'Y') {
            $("#taxPercent" + id).attr('readonly', false);
        } else {
            $("#taxPercent" + id).val('');
            $("#taxPercent" + id).attr('readonly', true);
        }
    });

    $(document).on('click', '#export_transaction', function () {
        $('#expore_tbl').find('td,th').first().remove();
        $('#expore_tbl ').find('.bulkCheckbox').remove();
        $("#expore_tbl").table2excel({
            name: "Worksheet Name",
            filename: "fee_transaction.xls",
            fileext: ".xls",
            exclude: ".checkall",
            exclude_inputs: true,
            columns: [0, 1, 2, 3, 4, 5]

        });
        location.reload();

    });

    $(document).on('click', '#export_invoice', function () {

        $("#expore_tbl tr").each(function () {
            $(this).find("th:last").remove();
            $(this).find("td:last").remove();
            $(this).find("th:first").remove();
            $(this).find("td:first").remove();
        });

        // $('#expore_tbl').find('td,th').first().remove();
        // $('#expore_tbl').find('td,th').last().remove();
        // $('#expore_tbl ').find('.bulkCheckbox').remove();
        $("#expore_tbl").table2excel({
            name: "Worksheet Name",
            filename: "fee_invoices.xls",
            fileext: ".xls",
            exclude: ".checkall",
            exclude_inputs: true,
            columns: [0, 1, 2, 3, 4, 5]
        });
        location.reload();
    });


    $(document).on('keyup', '.kountAmt', function () {
        var tamt = 0;
        $(".kountAmt").each(function () {
            if ($(this).val() != '') {
                var amt = parseFloat($(this).val());
            } else {
                var amt = 0;
            }
            tamt += parseFloat(amt);
        });
        $("#totalAmount").html(tamt);
    });

    // $('#expore_tbl').DataTable({
    //     pageLength: 10,
    //     filter: true,
    //     deferRender: true,
    //     scrollY: 200,
    //     scrollCollapse: true,
    //     scroller: true
    // });

    $(document).on('change', '#pupilsightProgramID', function () {
        var id = $(this).val();
        var type = 'getClass';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type },
            async: true,
            success: function (response) {

                $("#pupilsightYearGroupID").html('');
                //$("#pupilsightRollGroupID").html('');
                $("#pupilsightYearGroupID").html(response);
            }
        });
    });

    $(document).on('change', '#pupilsightYearGroupID', function () {
        var id = $(this).val();
        var pid = $('#pupilsightProgramID').val();
        var type = 'getSection';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, pid: pid },
            async: true,
            success: function (response) {
                $("#pupilsightRollGroupID").html('');
                $("#pupilsightRollGroupID").html(response);
            }
        });
    });


    // $(document).on('click', '#transactionStatusChange', function() {
    //     var transaction = [];
    //     $.each($("input[name='collection_id[]']:checked"), function() {
    //         transaction.push($(this).val());
    //     });
    //     var transid = transaction.join(",");
    //     var status = $("#transStatus").val();
    //     if (status) {
    //         if (transid) {
    //             var type = 'transStatusChange';
    //             $.ajax({
    //                 url: 'ajax_data.php',
    //                 type: 'post',
    //                 data: { val: transid, type: type, status: status },
    //                 async: true,
    //                 success: function(response) {
    //                     location.reload();
    //                 }
    //             });
    //         } else {
    //             alert('Please Select Transaction for Status Change');
    //         }
    //     } else {
    //         alert('Please Select Status');
    //     }
    // });

    $(document).on('click', '#transactionStatusChange', function () {
        var transaction = [];
        $.each($("input[name='collection_id[]']:checked"), function () {
            transaction.push($(this).val());
        });
        var transid = transaction.join(",");
        if (transid) {
            var type = 'transStatusChangeNew';
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: transid, type: type },
                async: true,
                success: function (response) {
                    $("#updateTransactionStatus").click();
                }
            });
        } else {
            alert('Please Select Transaction for Status Change');
        }
    });

    /* Santrupthi Code*/


    $(document).on('change', '#transport_for', function () {
        $("#oneway_bl,#oneway_bl1,#oneway_bl2,#twoway_bl1,#twoway_bl2,#twoway_bl3").hide();
        var route = $("#transport_for").val();
        if (route == 'oneway') {
            $("#oneway_bl").show();

        } else if (route == 'twoway') {
            $("#oneway_bl,#oneway_bl1,#oneway_bl2").hide();
            $("#twoway_bl1,#twoway_bl3,#twoway_bl2").show();

        }
    })
    $(document).on('change', '#select_route', function () {
        $("#oneway_bl1,#oneway_bl2").hide();
        var oneway = $("#select_route").val();
        if (oneway == 'onward') {
            $("#oneway_bl1").show();

        } else if (oneway == 'return') {
            $("#oneway_bl2").show();

        }
    });

    /* Santrupthi Code*/

    $(document).on('change', '#enableGateway', function () {
        var val = $(this).val();
        // $('#enableSelectGateway').removeAttr('required')

        if (val == 'Y') {
            $("#enableSelectGateway").prop('disabled', false);
            $("#enableSelectGateway").prop('required', true);

        } else {
            $("#enableSelectGateway").prop('disabled', true);
            $("#enableSelectGateway").prop('required', false);
            $("#enableSelectGateway").val('');
        }
    });

    $(document).on('click', '#alertData', function () {
        alert("You Can't Edit or Delete because Invoices are generated for this fee head");
        return false;
    });

    $(document).on('click', '#chkSubmit', function () {
        var sname = $("#series_name").val();
        var fmat = $("#format").val();
        var newformat = '{AB}';

        if (sname == '' || fmat == '') {
            if (sname == '') {
                $("#series_name").addClass('erroralert');
            } else {
                $("#series_name").removeClass('erroralert');
            }

            if (fmat == '') {
                $("#format").addClass('erroralert');
            } else {
                $("#format").removeClass('erroralert');
            }
            return false;
        }

        if (fmat.indexOf(newformat) == -1) {
            alert('You Have to Add Number Also!');
            return false;
        }
    });

    $(document).on('click', '#deactiveCounter', function () {
        if (confirm("Are you sure want to Deactive Counter?")) {
            var type = 'deactiveCounter';
            var val = $(this).attr('data-id');
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });


    $(document).on('click', '.sendButton', function () {
        var stuids = [];
        $.each($("input[name='stuid[]']:checked"), function () {
            stuids.push($(this).val());
        });
        var stuid = stuids.join(",");
        if (stuid) {
            $(".sendButton").removeClass('activestate');
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
            alert('You Have to Select Route First');
            window.setTimeout(function () {
                $("#large-modal-new").removeClass('show');
                $("#chkCounterSession").removeClass('modal-open');
                $(".modal-backdrop").remove();
            }, 10);
        }

    });

    $(document).on('click', '#sendEmailSms', function () {

        var emailquote = $("#emailQuoteRoute").val();
        var smsquote = $("#smsQuoteRoute").val();
        var favorite = [];
        $.each($("input[name='stuid[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(", ");
        //alert(subid);
        if (stuid) {
            if (emailquote != '' || smsquote != '') {
                $("#preloader").show();
                $.ajax({
                    url: 'modules/Transport/send_route_email_msg.php',
                    type: 'post',
                    data: { stuid: stuid, emailquote: emailquote, smsquote: smsquote },
                    async: true,
                    success: function (response) {
                        alert("Message Succesfully Sent");
                        location.reload();
                    }
                });
            } else {
                alert('You Have to Enter Quote.');
            }
        } else {
            alert('You Have to Select Applicants.');
        }

    });

    var ftype = $("#feetype").val();
    if (ftype != '') {
        var val = ftype;
        $('#due_day').empty();
        $('#hiderow').show();
        if (val == '1') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input name='due_date' style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='31'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate'  type='number' value='12' readonly></td><br>");
        } else if (val == '2') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input name='due_date' style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='31'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate'  type='number' value='6' readonly></td><br>");
        } else if (val == '3') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input  name='due_date' style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='31'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate' type='number' value='4' readonly></td><br>");
        } else if (val == '6') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input  name='due_date' style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='31'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate' type='number' value='2' readonly></td><br>");
        } else if (val == '12') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'></td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate' type='number' value='1' readonly></td><br>");
        }
    }

    $(document).on('change', '#feetype', function () {
        var val = $(this).val();
        $('#due_day').empty();
        $('#hiderow').show();
        if (val == '1') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input name='due_date' style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='31'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate'  type='number' value='12' readonly></td><br>");
        } else if (val == '2') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input name='due_date' style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='31'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate'  type='number' value='6' readonly></td><br>");
        } else if (val == '3') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input  name='due_date' style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='31'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate' type='number' value='4' readonly></td><br>");
        } else if (val == '6') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input  name='due_date' style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='31'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate' type='number' value='2' readonly></td><br>");
        } else if (val == '12') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'></td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:65px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate' type='number' value='1' readonly></td><br>");
        }

    });


    $(document).on('change', '#studentByClassTransport', function () {
        var val = $(this).val();
        var type = 'filterstudentbyclassTransport';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    $("#filterStudentByClass").html('');
                    $("#filterStudentByClass").html(response);
                }
            });
        }
    });

    $(document).on('click', '#transportFeeAdd', function () {
        var val = $(this).attr('data-id');
        var rttype = $(this).attr('data-type');
        var newval = Number(val) + 1;
        $(this).attr('data-id', newval);
        if (rttype == 'route') {
            var type = 'getAjaxTransportRouteFee';
        } else {
            var type = 'getAjaxTransportStopFee';
        }

        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: newval, type: type },
                async: true,
                success: function (response) {
                    $("#lastseatdiv").before(response);
                }
            });
        }
    });

    $(document).on('change', '#trans_type', function () {
        var val = $(this).val();
        $(".submtc").removeClass('hiddencol');
        if (val == 'Route') {
            $(".routeClass").removeClass('hiddencol');
            $(".routePrice").remove();
            $(".stopPrice").remove();
            $(".stopClass").addClass('hiddencol');
        } else {
            $(".routePrice").remove();
            $(".routeClass").addClass('hiddencol');
            $(".stopClass").removeClass('hiddencol');
        }
    });

    $(document).on('change', '#stop_route_id', function () {
        var val = $(this).val();
        var type = 'getAjaxTransportStopFee';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    $(".stopPrice").remove();
                    $(".showStopData").after(response);
                }
            });
        }
    });


    $(document).on('click', '.sendButton_stud', function () {
        var stuids = [];
        $.each($("input[name='student_id[]']:checked"), function () {
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
            window.setTimeout(function () {
                $("#large-modal-new_stud").removeClass('show');
                $("#chkCounterSession").removeClass('modal-open');
                $(".modal-backdrop").remove();
            }, 10);
        }

    });

    $(document).on('click', '#sendEmailSms_stud', function (e) {
        e.preventDefault();
        $("#preloader").show();
        window.setTimeout(function () {
            var formData = new FormData(document.getElementById("sendEmailSms_Student"));

            var emailquote = $("#emailQuote_stud").val();
            var subjectquote = $("#emailSubjectQuote_stud").val();

            var smsquote = $("#smsQuote_stud").val();
            var favorite = [];
            $.each($("input[name='student_id[]']:checked"), function () {
                favorite.push($(this).val());
            });
            var stuid = favorite.join(", ");

            var types = [];
            $.each($(".chkType:checked"), function () {
                types.push($(this).attr('data-type'));
            });
            var type = types.join(",");

            if (stuid) {
                if (type != '') {
                    if (emailquote != '' || smsquote != '') {

                        formData.append('stuid', stuid);
                        formData.append('emailquote', emailquote);
                        formData.append('smsquote', smsquote);
                        formData.append('type', type);
                        formData.append('subjectquote', subjectquote);
                        $.ajax({
                            url: 'modules/Students/send_stud_email_msg.php',
                            type: 'post',
                            //data: { stuid: stuid, emailquote: emailquote, smsquote: smsquote, type: type, subjectquote: subjectquote },
                            data: formData,
                            contentType: false,
                            cache: false,
                            processData: false,
                            async: false,
                            success: function (response) {
                                $("#preloader").hide();
                                alert('Your Message Sent Successfully! click Ok to continue ');
                                //location.reload();
                                $("#sendEmailSms_Student")[0].reset();
                                $("#closeSM").click();
                                $(".closeSMPopUp").click();
                            }
                        });
                    } else {
                        $("#preloader").hide();
                        alert('You Have to Enter Message.');
                    }
                } else {
                    $("#preloader").hide();
                    alert('You Have to Select Recipient.');
                }
            } else {
                $("#preloader").hide();
                alert('You Have to Select Applicants.');

            }
        }, 100);


    });


    $(document).on('click', '.sendButton_staff', function () {
        var stuids = [];
        var names = [];
        $.each($("input[name='stuid[]']:checked"), function () {
            stuids.push($(this).val());
            names.push($(this).attr('data-name'));
        });
        var stuid = stuids.join(",");
        var stfnames = names.join(", ");
        if (stuid) {
            $(".sendButton_staff").removeClass('activestate');
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
            $("#sendTo").text(stfnames);
        } else {
            alert('You Have to Select Staff');
            window.setTimeout(function () {
                $("#large-modal-new_staff").removeClass('show');
                $("#chkCounterSession").removeClass('modal-open');
                $(".modal-backdrop").remove();
            }, 10);
        }

    });

    $(document).on('click', '#sendEmailSms_staff', function () {
        $("#preloader").show();
        window.setTimeout(function () {
            var emailquote = $("#emailQuote_staff").val();
            var smsquote = $("#smsQuote_staff").val();
            var subjectquote = $("#emailSubjectQuote_staff").val();
            var favorite = [];
            $.each($("input[name='stuid[]']:checked"), function () {
                favorite.push($(this).val());
            });
            var formData = new FormData(document.getElementById("sendEmailSms_Staff"));
            var stuid = favorite.join(", ");

            var types = [];
            $.each($(".chkType:checked"), function () {
                types.push($(this).attr('data-type'));
            });
            var type = types.join(",");

            if (stuid) {
                if (smsquote != '') {
                    if (type != '') {
                        formData.append('stuid', stuid);
                        formData.append('emailquote', emailquote);
                        formData.append('smsquote', smsquote);
                        formData.append('type', type);
                        formData.append('subjectquote', subjectquote);
                        $.ajax({
                            url: 'modules/Staff/send_staff_email_msg.php',
                            type: 'post',
                            data: formData,
                            contentType: false,
                            cache: false,
                            processData: false,
                            async: false,
                            success: function (response) {
                                $("#preloader").hide();
                                alert('Your Message Sent Successfully! click Ok to continue ');
                                $("#sendEmailSms_Staff")[0].reset();
                                $("#closeSMT").click();
                                // location.reload();
                            }
                        });
                    } else {
                        $("#preloader").hide();
                        alert('You Have to Select Recipient.');
                    }
                } else if (emailquote != '') {
                    if (type != '') {
                        formData.append('stuid', stuid);
                        formData.append('emailquote', emailquote);
                        formData.append('smsquote', smsquote);
                        formData.append('type', type);
                        formData.append('subjectquote', subjectquote);
                        $.ajax({
                            url: 'modules/Staff/send_staff_email_msg.php',
                            type: 'post',
                            data: formData,
                            contentType: false,
                            cache: false,
                            processData: false,
                            async: false,
                            success: function (response) {
                                $("#preloader").hide();
                                alert('Your Message Sent Successfully! click Ok to continue ');
                                $("#sendEmailSms_Staff")[0].reset();
                                $("#closeSMT").click();
                                // location.reload();
                            }
                        });
                    } else {
                        $("#preloader").hide();
                        alert('You Have to Select Recipient.');
                    }

                } else {
                    $("#preloader").hide();
                    alert('You Have to Enter Message.');
                }
            } else {
                $("#preloader").hide();
                alert('You Have to Select Staff.');

            }
        }, 100);
    });

    //assign student to section

    $(document).on('click', '#assignStuSec', function () {


        var atype = $(this).attr('data-type');
        var favorite = [];
        $.each($("input[name='student_id[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");

        //  alert(stuid);
        var checked = $(".stuid:checked").length;
        if (checked >= 1) {
            alert('You Have to Select Enrolled Students.');
            return false;
        } else {
            if (stuid) {
                var val = stuid;
                var type = 'addstudentid_toassign_section';
                if (val != '') {

                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type },
                        async: true,
                        success: function (response) {

                            if (response == 0) {
                                $("#clickStudentsection").click();
                            } else {
                                alert('You Have to Select Students with Same class.');
                            }

                        }
                    });
                }
            } else {
                alert('You Have to Select Students.');
            }
        }
    });

    //assign_section

    $(document).on('click', '.assign_section', function (e) {

        e.preventDefault();
        var atype = $(this).attr('data-type');
        var url = $(this).attr('data-href');
        var section = $('#pupilsightRollGroupID_sel').val();
        var favorite = [];
        $.each($("input[name='stu_id[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");
        if (section != '') {
            if (stuid) {
                if (confirm("Are you sure want to Assign Section")) {
                    var val = stuid;
                    //deleteStudentRoutes
                    var type = 'assign_section';
                    if (val != '') {
                        $.ajax({
                            url: 'ajax_data.php',
                            type: 'post',
                            data: { val: val, type: type, section: section },
                            async: true,
                            success: function (response) {
                                alert('Section Assigned Successfully');
                                location.reload();
                            }
                        });
                    }
                }
            } else {
                alert('You Have to Select Students.');
            }
        } else {
            alert('You Have to Select Section.');
        }
    });

    //remove_section


    $(document).on('click', '.remove_section', function (e) {

        e.preventDefault();
        var atype = $(this).attr('data-type');
        var url = $(this).attr('data-href');
        // var section = $('select[name="pupilsightRollGroupID"] option:selected').val();
        var favorite = [];
        $.each($("input[name='stu_id[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");
        // alert(section);
        if (stuid) {
            if (confirm("Are you sure want to remove Section")) {
                var val = stuid;
                //deleteStudentRoutes
                var type = 'remove_section';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type },
                        async: true,
                        success: function (response) {
                            location.reload();
                        }
                    });
                }
            }
        } else {

            alert('You Have to Select Students.');

        }
    });

    //bulk student register,if students are not assigned or registered previously
    $(document).on('click', '#bulk_student_reg', function () {


        $("#click_bulkStudentregister").click();


    });

    $(document).on('click', '.bulk_reg_students', function (e) {

        e.preventDefault();
        var atype = $(this).attr('data-type');
        var url = $(this).attr('data-href');
        var prgm = $('select[name="pupilsightProgramID"] option:selected').val();
        var class_val = $('select[name="pupilsightYearGroupID"] option:selected').val();
        var favorite = [];
        $.each($("input[name='student_id[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");
        // alert(section);
        if (stuid) {
            if (confirm("Are you sure want to Register ")) {
                var val = stuid;
                //deleteStudentRoutes
                var type = 'bulk_reg_students';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type, prgm: prgm, class_val: class_val, stuid: stuid },
                        async: true,
                        success: function (response) {
                            // alert(response);
                            location.reload();
                        }
                    });
                }
            }
        } else {

            alert('You Have to Select Students.');

        }
    });


    //bulk student register ,if students are not assigned or registered previously


    //register-deregister 
    $(document).on('click', '#register_deregister', function () {


        var atype = $(this).attr('data-type');
        var favorite = [];
        /*  $.each($("input[name='student_id[]']:checked"), function() {
              favorite.push($(this).val());
          });*/
        // var stuid = favorite.join(",");
        var stuid = $(this).attr('data-id');

        if (stuid) {
            var val = stuid;
            var type = 'addstudentidInSession';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#clickStudent_reg_dereg").click();
                    }
                });
            }
        } else {

            alert('You Have to Select Students.');


        }
    });




    $(document).on('change', '#reg_dereg_id', function () {


        $(".dereg_col").show();
        var regdereg = $("#reg_dereg_id").val();
        if (regdereg == 'dereg') {
            $(".dereg_col").show();

        } else {
            $(".dereg_col").hide();

        }
    });

    //register-deregister 
    //attendance type 
    $(document).on('change', '#att_type_id', function () {

        var att_type = $("#att_type_id").val();
        if (att_type == 1) {
            $(".showsessionclick").show();

        } else {
            $(".showsessionclick").hide();
            $("#session_table").hide();


        }
        // alert(att_type);

    });
    //session_add

    $(document).on('change', '#no_of_session', function () {

        var no_of_sessions = $(this).val();

        // alert(no_of_sessions);
        //  var atype = $(this).attr('data-type');
        //  alert(stuid);
        if (no_of_sessions) {
            var val = no_of_sessions;
            var type = 'display_multiple_attend_session';
            if (val != '') {

                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        //  alert(response);
                        $("#session_table").show();

                        $("#session_table").html(response);
                        // $("#clickStudentsubject").click();
                    }
                });
            }
        }
    });


    $(document).on('click', '#assignStusub', function () {


        var atype = $(this).attr('data-type');
        var favorite = [];
        $.each($("input[name='student_id[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");

        //  alert(stuid);
        if (stuid) {
            var val = stuid;
            var type = 'addstudentidInSession';
            if (val != '') {

                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#clickStudentsubject").click();
                    }
                });
            }
        } else {

            alert('You Have to Select Students.');


        }
    });

    $(document).on('click', '#assignStu_elesub', function () {


        var atype = $(this).attr('data-type');
        var favorite = [];
        $.each($("input[name='student_id[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");

        //  alert(favorite.length);
        if (stuid) {

            if (favorite.length == 1) {
                var val = stuid;
                var type = 'addstudentidInSession';
                if (val != '') {

                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type },
                        async: true,
                        success: function (response) {
                            $("#clickStudent_elect_subject").click();
                        }
                    });
                }
            } else {

                alert('You Have to Select One Student at a time.');


            }
        } else {

            alert('You Have to Select Student.');


        }
    });

    $(document).on('click', '.assign_elective_sub', function (e) {
        e.preventDefault();

        //stud_id,pupilsightProgramID,pupilsightYearGroupID,subjects
        // var pgrm_val = $('select[name="pupilsightProgramID"] option:selected').val();
        //  var elect_sub = $('select[name="pupilsightDepartmentIDs[]"] option:selected').val();
        var stud_id = $("input[name='stud_id']").val();
        var pupilsightProgramID = $("input[name='pupilsightProgramID']").val();
        var pupilsightYearGroupID = $("input[name='pupilsightYearGroupID']").val();

        // var selected = $('#issubjects option:selected');

        var favorite = [];


        $.each($("input[name='pupilsightDepartmentIDs[]']:checked"), function () {
            favorite.push($(this).val());
        });

        var subjects = favorite.join(",");
        //  alert(pupilsightProgramID);

        if (subjects) {

            // alert(stud_id);
            // var val = subjects;
            var type = 'assign_elective_subtoclass';
            if (subjects != '') {
                //  alert(subjects);
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { type: type, val: '', subjects: subjects, stud_id: stud_id, pupilsightProgramID: pupilsightProgramID, pupilsightYearGroupID: pupilsightYearGroupID },
                    async: true,
                    success: function (response) {

                        // alert(response);
                        location.reload();
                    }
                });
            }
        } else {

            alert('You Have to Select Subjects.');


        }
    });

    //transfer student

    $(document).on('click', '#transfer_student', function () {
        var atype = $(this).attr('data-type');
        var favorite = [];
        /*  $.each($("input[name='student_id[]']:checked"), function() {
              favorite.push($(this).val());
          });*/
        // var stuid = favorite.join(",");
        var stuid = $(this).attr('data-id');

        if (stuid) {
            var val = stuid;
            var type = 'addstudentidInSession';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#clickStudent_transfer").click();
                    }
                });
            }
        } else {

            alert('You Have to Select Students.');


        }
    });

    // $('#issubjects').multiselect({
    //     includeSelectAllOption: true
    // });

    $(document).on('click', '.assign_core_sub', function (e) {
        e.preventDefault();
        var pgrm_val = $('select[name="pupilsightProgramID_Mnew"] option:selected').val();
        var class_val = $('#showMultiClassByProg option:selected');
        var selected = $('#showMultiSubjectByProgCls option:selected');
        // alert(selected);
        // alert(class_val);
        var message = '';
        var favorite = [];
        var clas_ids = [];

        selected.each(function () {
            //   message += $(this).text() + '' + $(this).val() + '\n';
            favorite.push($(this).val());

        });
        class_val.each(function () {
            //   message += $(this).text() + '' + $(this).val() + '\n';
            clas_ids.push($(this).val());

        });
        var subjects = favorite.join(",");
        var classes = clas_ids.join(",");
        //  alert(subjects);
        var val = '';
        if (subjects) {
            // var val = subjects;
            var type = 'assigncoresubtoclass';
            if (subjects != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { type: type, subjects: subjects, val: val, pgrm_val: pgrm_val, class_val: classes },
                    async: true,
                    success: function (response) {
                        if (response == "success") {
                            alert('Assign subjects to class is successful');
                            window.location.reload();
                        } else {
                            alert(response);
                        }
                    }
                });
            }
        } else {

            alert('You Have to Select Subjects.');


        }
    });

    $(document).on('click', '.assign_sec_sub', function (e) {
        e.preventDefault();
        var pgrm_val = $('select[name="pupilsightProgramID"] option:selected').val();
        var class_val = $('select[name="pupilsightYearGroupID"] option:selected').val();
        var selected = $('#issubjects option:selected');
        var message = '';

        var favorite = [];


        selected.each(function () {
            //   message += $(this).text() + '' + $(this).val() + '\n';

            favorite.push($(this).val());

        });

        var subjects = favorite.join(",");
        //  alert(subjects);

        if (subjects) {
            // var val = subjects;
            var type = 'assignsecndsubtoclass';
            if (subjects != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { type: type, subjects: subjects, pgrm_val: pgrm_val, class_val: class_val },
                    async: true,
                    success: function (response) {

                        location.reload();
                    }
                });
            }
        } else {

            alert('You Have to Select Subjects.');


        }
    });


    $(document).on('click', '.assign_third_sub', function (e) {
        e.preventDefault();
        var pgrm_val = $('select[name="pupilsightProgramID"] option:selected').val();
        var class_val = $('select[name="pupilsightYearGroupID"] option:selected').val();
        var selected = $('#issubjects option:selected');
        var message = '';

        var favorite = [];


        selected.each(function () {
            //   message += $(this).text() + '' + $(this).val() + '\n';

            favorite.push($(this).val());

        });

        var subjects = favorite.join(",");
        //  alert(subjects);

        if (subjects) {
            // var val = subjects;
            var type = 'assignthirdsubtoclass';
            if (subjects != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { type: type, subjects: subjects, pgrm_val: pgrm_val, class_val: class_val },
                    async: true,
                    success: function (response) {

                        location.reload();
                    }
                });
            }
        } else {

            alert('You Have to Select Subjects.');


        }
    });

    $(document).on('change', '#staffstatus', function () {
        $('#reasoninactive').hide();

        var status = $(this).val();

        if (status == 'inactive') {

            $('#reasoninactive').show();
        }

    });

    $(document).on('change', '.select_sub', function () {

        var favorite = [];
        $.each($("input[name='selected_sub[]']:checked"), function () {
            favorite.push($(this).attr('id'));

        });
        var stuid = favorite.join(",");

        var type = 'selectSubject';
        var val = stuid;
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {

                }
            });
        }

    });


    $(document).on('click', '#assignstaff_st', function () {

        var favorite = [];
        $.each($("input[name='stuid[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");
        //alert(stuid);
        if (stuid) {
            var val = stuid;
            var type = 'changestaffstatus';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#clickstaffassign").click();
                    }
                });
            }
        } else {
            if (atype == 'student') {
                alert('You Have to Select Students.');
            } else {
                alert('You Have to Select Staff.');
            }

        }
    });

    $(document).on('click', '#unassignStudentstaff', function () {

        var favorite = [];
        //var stff = [];
        var k = 0;
        $.each($("input[name='stuid[]']:checked"), function () {
            favorite.push($(this).val());
            //stff.push($(this).attr('data-stfid'));
            if ($(this).attr('data-stfid') != '') {
                k++;
            }
        });
        var stuid = favorite.join(",");
        //var stfid = stff.join(",");
        if (k >= 1) {
            if (stuid) {
                if (confirm("Are you sure want to remove staff")) {
                    var val = stuid;
                    var type = 'deletsStaffAssigned';
                    if (val != '') {
                        $.ajax({
                            url: 'ajax_data.php',
                            type: 'post',
                            data: { val: val, type: type },
                            async: true,
                            success: function (response) {
                                location.reload();
                            }
                        });
                    }
                }
            } else {
                alert('You Have to Select Students.');
            }
        } else {
            alert('You Have No Staff Assigned.');
        }
    });

    $(document).on('click', '#change_status', function () {
        // var atype = $(this).attr('data-type');

        var checked = $("input[name='stuid[]']:checked").length;
        //alert(checked);
        if (checked > 1) {
            alert("Please Select One Staff!");

            return false;
        } else {
            var favorite = [];
            $.each($("input[name='stuid[]']:checked"), function () {
                favorite.push($(this).val());
            });
            var stuid = favorite.join(",");

            if (stuid) {
                var val = stuid;

                var type = 'changestaffstatus';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type },
                        async: true,
                        success: function (response) {
                            $("#clickchnagestatus").click();
                        }
                    });
                }

            } else {
                alert('You Have to Select Staff.');
            }
        }
    });

    $(document).on('click', '#select_staff', function () {
        // var atype = $(this).attr('data-type');

        var checked = $("input[name='staffid[]']:checked").length;

        if (checked > 1) {
            alert("Please Select One Staff!");

            return false;
        } else {
            var favorite = [];
            $.each($("input[name='staffid[]']:checked"), function () {
                favorite.push($(this).val());
            });
            var stuid = favorite.join(",");

            if (stuid) {
                var val = stuid;
                var type = 'changestaffstatus';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type },
                        async: true,
                        success: function (response) {
                            $("#clickselectstaff").click();
                        }
                    });
                }

            } else {
                alert('You Have to Select Staff.');
            }
        }
    });
    $(document).on('change', '.select_sstaff', function () {

        var favorite = [];
        $.each($("input[name='selected_sstaff[]']:checked"), function () {
            favorite.push($(this).attr('id'));

        });
        var stuid = favorite.join(",");

        var type = 'selectStaffToSubject';
        var val = stuid;

        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {

                }
            });
        }

    });

    $(document).on('change', '.select_sstaff', function () {

        var favorite = [];
        $.each($("input[name='selected_sstaff[]']:checked"), function () {
            favorite.push($(this).attr('id'));

        });
        var stuid = favorite.join(",");

        var type = 'selectStaffToSubject';
        var val = stuid;

        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {

                }
            });
        }

    });


    $(document).on('click', '#submitMasterDiscount', function () {
        var type = $(".discountfineType:checked").val();

        if (type == '1') {
            var error = '0';
            $(".cat_name,.amtPercent").each(function () {
                if ($(this).val() != "") {
                    error = 1;
                    $(this).removeClass('erroralert');
                    $(this).parent().parent().removeClass('erroralert');
                } else {
                    $(this).addClass('erroralert');
                    $(this).parent().parent().removeClass('erroralert');
                }
            });
            if (error == '1') {
                $("#feeDiscontForm").submit();
            }
        } else {
            var error = '0';
            var error1 = '0';
            var error2 = '0';
            $(".inv_name,.inv_amtPercent").each(function () {
                if ($(this).val() != "") {
                    error = 1;
                    $(this).removeClass('erroralert');
                    $(this).parent().parent().removeClass('erroralert');
                } else {
                    $(this).addClass('erroralert');
                    $(this).parent().parent().removeClass('erroralert');
                }
            });

            $(".min_inv").each(function () {
                if ($(this).val() != "") {
                    error1 = 1;
                    $(this).removeClass('erroralert');
                    $(this).parent().parent().removeClass('erroralert');
                } else {
                    $(this).addClass('erroralert');
                    $(this).parent().parent().removeClass('erroralert');
                }
            });

            $(".max_inv").each(function () {
                if ($(this).val() != "") {
                    error2 = 1;
                    $(this).removeClass('erroralert');
                    $(this).parent().parent().removeClass('erroralert');
                } else {
                    $(this).addClass('erroralert');
                    $(this).parent().parent().removeClass('erroralert');
                }
            });
            if (error == '1' && error1 == '1' && error2 == '1') {
                $("#feeDiscontForm").submit();
            }
        }


    });


    $(document).on('change', '#filterStructureOnAcademicYr', function () {
        var val = $(this).val();
        if (val != '') {
            $.ajax({
                url: 'fullscreen.php?q=/modules/Finance/fee_structure_assign_student_manage_ajax_add.php',
                type: 'post',
                data: { val: val },
                async: true,
                success: function (response) {
                    $("#assignFeeStructure").html('');
                    $("#assignFeeStructure").html(response);
                }
            });
        }
    });


    $(document).on('click', '#copyFeeStructure', function () {
        var name = $("#name").val();
        var acyear = $("#pupilsightSchoolYearID").val();
        var type = "chkFeeStructure";
        if (name != '' && acyear != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: name, acyear: acyear, type: type },
                async: true,
                success: function (response) {
                    if (response == 'exist') {
                        alert('This Fee Structure Already Exist');
                    } else {
                        $("#copyFeeStructureForm").submit();
                    }
                }
            });
        } else {
            alert('You Have to Enter Mandatory Fields!');
        }
    });


    $(document).on('click', '#submitMasterFine', function () {
        var type = $(".parentfineType:checked").val();
        var childtype = $(".fineType:checked").val();
        if (type == '1') {
            if (childtype == '1') {
                $("#secondId").removeClass('erroralert');
                $(".chkfrmdate").removeClass('erroralert');
                $(".chktodate").removeClass('erroralert');
                $(".chkamnt").removeClass('erroralert');
                $(".chkfrmday").removeClass('erroralert');
                $(".chktoday").removeClass('erroralert');
                $(".chkdayamnt").removeClass('erroralert');

                var error = '0';
                if ($("#firstId").val() != '') {
                    error = 1;
                    $("#firstId").removeClass('erroralert');
                } else {
                    $("#firstId").addClass('erroralert');
                }
                if (error == '1') {
                    $("#fineRuleForm").submit();
                }
            } else if (childtype == '2') {
                $("#firstId").removeClass('erroralert');
                $(".chkfrmdate").removeClass('erroralert');
                $(".chktodate").removeClass('erroralert');
                $(".chkamnt").removeClass('erroralert');
                $(".chkfrmday").removeClass('erroralert');
                $(".chktoday").removeClass('erroralert');
                $(".chkdayamnt").removeClass('erroralert');

                var error = '0';
                if ($("#secondId").val() != '') {
                    error = 1;
                    $("#secondId").removeClass('erroralert');
                } else {
                    $("#secondId").addClass('erroralert');
                }
                if (error == '1') {
                    $("#fineRuleForm").submit();
                }
            } else {
                $("#firstId").removeClass('erroralert');
                $("#secondId").removeClass('erroralert');
                $(".chkfrmday").removeClass('erroralert');
                $(".chktoday").removeClass('erroralert');
                $(".chkdayamnt").removeClass('erroralert');

                var error = '0';
                var error1 = '0';
                var error2 = '0';
                $(".chkfrmdate").each(function () {
                    if (!$(this).hasClass('mb-1')) {
                        if ($(this).val() != "") {
                            error = 1;
                            $(this).removeClass('erroralert');
                            $(this).parent().parent().removeClass('erroralert');
                        } else {
                            $(this).addClass('erroralert');
                            $(this).parent().parent().removeClass('erroralert');
                        }
                    }
                });

                $(".chktodate").each(function () {
                    if (!$(this).hasClass('mb-1')) {
                        if ($(this).val() != "") {
                            error1 = 1;
                            $(this).removeClass('erroralert');
                            $(this).parent().parent().removeClass('erroralert');
                        } else {
                            $(this).addClass('erroralert');
                            $(this).parent().parent().removeClass('erroralert');
                        }
                    }
                });

                $(".chkamnt").each(function () {
                    if (!$(this).hasClass('mb-1')) {
                        if ($(this).val() != "") {
                            error2 = 1;
                            $(this).removeClass('erroralert');
                            $(this).parent().parent().removeClass('erroralert');
                        } else {
                            $(this).addClass('erroralert');
                            $(this).parent().parent().removeClass('erroralert');
                        }
                    }
                });

                if (error == '1' && error1 == '1' && error2 == '1' && !$(".chkfrmdate").hasClass('erroralert') && !$(".chktodate").hasClass('erroralert') && !$(".chkamnt").hasClass('erroralert')) {
                    $("#fineRuleForm").submit();
                }


            }


        } else if (type == '2') {
            if (childtype == '1') {
                $("#secondId").removeClass('erroralert');
                $(".chkfrmdate").removeClass('erroralert');
                $(".chktodate").removeClass('erroralert');
                $(".chkamnt").removeClass('erroralert');
                $(".chkfrmday").removeClass('erroralert');
                $(".chktoday").removeClass('erroralert');
                $(".chkdayamnt").removeClass('erroralert');

                var error = '0';
                if ($("#firstId").val() != '') {
                    error = 1;
                    $("#firstId").removeClass('erroralert');
                } else {
                    $("#firstId").addClass('erroralert');
                }
                if (error == '1') {
                    $("#fineRuleForm").submit();
                }
            } else {
                $("#firstId").removeClass('erroralert');
                $(".chkfrmdate").removeClass('erroralert');
                $(".chktodate").removeClass('erroralert');
                $(".chkamnt").removeClass('erroralert');
                $(".chkfrmday").removeClass('erroralert');
                $(".chktoday").removeClass('erroralert');
                $(".chkdayamnt").removeClass('erroralert');

                var error = '0';
                if ($("#secondId").val() != '') {
                    error = 1;
                    $("#secondId").removeClass('erroralert');
                } else {
                    $("#secondId").addClass('erroralert');
                }
                if (error == '1') {
                    $("#fineRuleForm").submit();
                }
            }
        } else {
            if (childtype == '1') {
                $("#secondId").removeClass('erroralert');
                $(".chkfrmdate").removeClass('erroralert');
                $(".chktodate").removeClass('erroralert');
                $(".chkamnt").removeClass('erroralert');
                $(".chkfrmday").removeClass('erroralert');
                $(".chktoday").removeClass('erroralert');
                $(".chkdayamnt").removeClass('erroralert');

                var error = '0';
                if ($("#firstId").val() != '') {
                    error = 1;
                    $("#firstId").removeClass('erroralert');
                } else {
                    $("#firstId").addClass('erroralert');
                }
                if (error == '1') {
                    $("#fineRuleForm").submit();
                }
            } else if (childtype == '2') {
                $("#firstId").removeClass('erroralert');
                $(".chkfrmdate").removeClass('erroralert');
                $(".chktodate").removeClass('erroralert');
                $(".chkamnt").removeClass('erroralert');
                $(".chkfrmday").removeClass('erroralert');
                $(".chktoday").removeClass('erroralert');
                $(".chkdayamnt").removeClass('erroralert');

                var error = '0';
                if ($("#secondId").val() != '') {
                    error = 1;
                    $("#secondId").removeClass('erroralert');
                } else {
                    $("#secondId").addClass('erroralert');
                }
                if (error == '1') {
                    $("#fineRuleForm").submit();
                }
            } else {
                $("#firstId").removeClass('erroralert');
                $("#secondId").removeClass('erroralert');
                $(".chkfrmdate").removeClass('erroralert');
                $(".chktodate").removeClass('erroralert');
                $(".chkamnt").removeClass('erroralert');

                var error = '0';
                var error1 = '0';
                var error2 = '0';
                $(".chkfrmday").each(function () {
                    if ($(this).val() != "") {
                        error = 1;
                        $(this).removeClass('erroralert');
                        $(this).parent().parent().removeClass('erroralert');
                    } else {
                        $(this).addClass('erroralert');
                        $(this).parent().parent().removeClass('erroralert');
                    }
                });

                $(".chktoday").each(function () {
                    if ($(this).val() != "") {
                        error1 = 1;
                        $(this).removeClass('erroralert');
                        $(this).parent().parent().removeClass('erroralert');
                    } else {
                        $(this).addClass('erroralert');
                        $(this).parent().parent().removeClass('erroralert');
                    }
                });

                $(".chkdayamnt").each(function () {
                    if ($(this).val() != "") {
                        error2 = 1;
                        $(this).removeClass('erroralert');
                        $(this).parent().parent().removeClass('erroralert');
                    } else {
                        $(this).addClass('erroralert');
                        $(this).parent().parent().removeClass('erroralert');
                    }
                });
                if (error == '1' && error1 == '1' && error2 == '1' && !$(".chkfrmday").hasClass('erroralert') && !$(".chktoday").hasClass('erroralert') && !$(".chkdayamnt").hasClass('erroralert')) {
                    $("#fineRuleForm").submit();
                }
            }
        }


    });



    $(document).on('change', '.chkfrmday', function (e) {
        e.preventDefault();
        var tday = $("input[name=lastDay]").val();
        var val = $(this).val();
        if (val != '') {
            if (tday == '') {
                if (val != '') {
                    $("input[name=startDay]").val(val);
                }
            } else {
                if (Number(val) < Number(tday) || Number(val) == Number(tday)) {
                    alert('You Cant Enter Less or Equal Day from Previous To Day');
                    $(this).val('').focus();
                } else {
                    $("input[name=startDay]").val(val);
                }
            }
        }
    });

    $(document).on('change', '.chktoday', function (e) {
        e.preventDefault();
        var val = $(this).val();
        var fday = $("input[name=startDay]").val();
        if (val != '') {
            if (Number(val) < Number(fday) || Number(val) == Number(fday)) {
                alert('You Cant Enter Less or Equal Day from From Day');
                $(this).val('').focus();
            } else {
                $("input[name=lastDay]").val(val);
            }
        }

    });

    $(document).on('change', '.min_inv', function (e) {
        e.preventDefault();
        var tday = $("input[name=maxInv]").val();
        var val = $(this).val();
        if (val != '') {
            if (tday == '') {
                if (val != '') {
                    $("input[name=minInv]").val(val);
                }
            } else {
                if (Number(val) < Number(tday) || Number(val) == Number(tday)) {
                    alert('You Cant Enter Less or Equal Invoice from Previous Max Invoice');
                    $(this).val('').focus();
                } else {
                    $("input[name=minInv]").val(val);
                }
            }
        }
    });

    $(document).on('change', '.max_inv', function (e) {
        e.preventDefault();
        var val = $(this).val();
        var fday = $("input[name=minInv]").val();
        if (val != '') {
            if (Number(val) < Number(fday)) {
                alert('You Cant Enter Less or Equal Max Invoice from Min Invoice');
                $(this).val('').focus();
            } else {
                $("input[name=maxInv]").val(val);
            }
        }

    });


    $(document).on('change', '#amount_paying', function (e) {
        e.preventDefault();
        var val = $(this).val();
        var chkval = $("input[name=chkamount]").val();
        if (val != '') {
            if (Number(val) < Number(chkval)) {
                $("input[name='invoice_status'").val("Partial Paid");
            } else {
                $("input[name='invoice_status'").val("Fully Paid");
            }
        }
    });

    $(document).on('click', '#btnTransportAmountConfig', function (e) {
        var type = $("#trans_type").val();
        var flag = true;

        if (type == 'Route') {
            $(".routeid, .onewayprice, .twowayprice").each(function (i) {
                var val = $(this).val();
                if (val == "") {
                    if (flag) {
                        $(this).focus();
                    }
                    flag = false;
                    $(this).parent().parent().removeClass('erroralert');
                    $(this).addClass('erroralert');
                } else {
                    $(this).parent().parent().removeClass('erroralert');
                    if ($(this).hasClass('erroralert')) {
                        $(this).removeClass('erroralert');
                    }
                }
            });
            if (!$(".routeid").hasClass('erroralert') && !$(".onewayprice").hasClass('erroralert') && !$(".twowayprice").hasClass('erroralert')) {
                document.getElementById("frmTransportAmountConfig").submit();
            }
        } else {
            $(".onewaypricestop, .twowaypricestop").each(function (i) {
                var val = $(this).val();
                if (val == "") {
                    if (flag) {
                        $(this).focus();
                    }
                    flag = false;
                    $(this).parent().parent().removeClass('erroralert');
                    $(this).addClass('erroralert');
                } else {
                    $(this).parent().parent().removeClass('erroralert');
                    if ($(this).hasClass('erroralert')) {
                        $(this).removeClass('erroralert');
                    }
                }
            });
            if (!$(".onewaypricestop").hasClass('erroralert') && !$(".twowaypricestop").hasClass('erroralert')) {
                document.getElementById("frmTransportAmountConfig").submit();
            }
        }

    });

    $(document).on('click', '#exportCounterDate', function () {
        $('<table>')

            .append(
                $("#expore_tbl").clone()
            )
            .append(
                $("#expore_tbl_2").html()
            )
            .table2excel({
                name: "Worksheet Name",
                filename: "counter_details.xls",
                fileext: ".xls",
            });

    });

    $(document).on('click', '#alertDataDeposit', function () {
        alert("You Can't Delete because it's Over Payment Default Account");
        return false;
    });

    $(document).on('change', '#onward_sp_new', function () {
        var val = $(this).val();
        if ($("#addReturnRoute").is(':checked')) {
            $("#return_sp_new").val(val);
        }
    });

    $(document).on('change', '#addReturnRoute', function () {
        if ($(this).is(':checked')) {
            //$("#twoway_bl2").hide();
            var onwroute = $("#onward_rt_new option:selected").val();
            var onwroutestp = $("#onward_sp_new option:selected").val();
            $("#return_rt_new").val(onwroute);
            window.setTimeout(function () {
                $("#return_rt_new").trigger('change');
                window.setTimeout(function () {
                    $("#return_sp_new").val(onwroutestp);
                }, 100);
            }, 100);

        } else {
            //$("#twoway_bl2").show();
            $("#return_rt_new").val('');
            $("#return_sp_new").val('');
        }

    });

    $(document).on('click', '#attnSettingsSubmit', function (e) {
        var type = $("#att_type_id").val();
        var flag = true;
        var prg = $("#programId").val();
        if (prg == '' || type == '') {
            alert('Please Fill All Mandatory Fields!');
            if (prg == '') {
                $("#programId").addClass('erroralert');
            } else {
                $("#programId").removeClass('erroralert');
            }
            if (type == '') {
                $("#att_type_id").addClass('erroralert');
            } else {
                $("#att_type_id").removeClass('erroralert');
            }
        } else {
            if (type == '1') {
                var nos = $("#no_of_session").val();
                if (nos == "") {
                    $("#no_of_session").addClass('erroralert');
                    flag = false;
                } else {
                    if ($("#no_of_session").hasClass('erroralert')) {
                        $("#no_of_session").removeClass('erroralert');
                    }
                }

                $(".sessionName").each(function (i) {
                    var val = $(this).val();
                    if (val == "") {
                        $(this).addClass('erroralert');
                        flag = false;
                    } else {
                        if ($(this).hasClass('erroralert')) {
                            $(this).removeClass('erroralert');
                        }
                    }
                });
                if (!$(".sessionName").hasClass('erroralert') && !$("#no_of_session").hasClass('erroralert') && flag) {
                    document.getElementById("attendanceConfigSetting").submit();
                }
            } else {
                document.getElementById("attendanceConfigSetting").submit();
            }
        }

    });

    $(document).on('change', '#pupilsightYearGroupIDTimeTable', function () {
        var id = $(this).val();
        var type = 'getSectionTimeTableWise';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type },
            async: true,
            success: function (response) {
                $("#pupilsightRollGroupID").html();
                $("#pupilsightRollGroupID").html(response);
            }
        });
    });

    $(document).on('change', '.allskillId', function () {
        if ($(this).is(':checked')) {
            $(".skillId").prop('checked', true);
            var skills = [];
            var sknames = [];
            var checked = $(".skillId:checked").length;
            if (checked >= 1) {
                $.each($(".skillId:checked"), function () {
                    skills.push($(this).attr('data-id'));
                    var sid = $(this).attr('data-id');
                    var skname = $("#sname" + sid).val();
                    sknames.push(sid + '-' + skname);
                });
                var skid = skills.join(",");
                var skillname = sknames.join(",");

                // var sub = [];
                // $.each($(".subId:checked"), function () {
                //     sub.push($(this).attr('data-id'));
                // });
                // var subid = sub.join(",");
                var subid = $("#deptId").val();
                var academicId = $("#pupilsightSchoolYearID").val();
                var programId = $("#pupilsightProgramID_MC").val();
                var classId = $("#pupilsightClassID").val();

                if (skid != '' && subid != '') {
                    var type = 'addSubjectSkills';
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: skid, type: type, subid: subid, skillname: skillname, academicId: academicId, programId: programId, classId: classId },
                        async: true,
                        success: function (response) {
                            //$("#clickstaffunassign").click();
                        }
                    });
                }
            } else {
                var subid = $("#deptId").val();
                var academicId = $("#pupilsightSchoolYearID").val();
                var programId = $("#pupilsightProgramID_MC").val();
                var classId = $("#pupilsightClassID").val();
                console.log(skid);
                if (skid != '' && subid != '') {
                    var type = 'delSubjectSkills';
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: subid, type: type, academicId: academicId, programId: programId, classId: classId },
                        async: true,
                        success: function (response) {
                            //$("#clickstaffunassign").click();
                        }
                    });
                }
            }
        } else {
            $(".skillId").prop('checked', false);
        }

    });

    $(document).on('change', '.skillId', function () {
        var skills = [];
        var sknames = [];
        var checked = $(".skillId:checked").length;
        if (checked >= 1) {
            $.each($(".skillId:checked"), function () {
                //console.log(1);
                skills.push($(this).attr('data-id'));
                var sid = $(this).attr('data-id');
                var skname = $("#sname" + sid).val();
                sknames.push(sid + '-' + skname);
            });
            var skid = skills.join(",");
            var skillname = sknames.join(",");

            // var sub = [];
            // $.each($(".subId:checked"), function () {
            //     sub.push($(this).attr('data-id'));
            // });
            // var subid = sub.join(",");
            var subid = $("#deptId").val();
            var academicId = $("#pupilsightSchoolYearID").val();
            var programId = $("#pupilsightProgramID_MC").val();
            var classId = $("#pupilsightClassID").val();
            //console.log(skid);
            if (skid != '' && subid != '') {
                var type = 'addSubjectSkills';
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: skid, type: type, subid: subid, skillname: skillname, academicId: academicId, programId: programId, classId: classId },
                    async: true,
                    success: function (response) {
                        //$("#clickstaffunassign").click();
                    }
                });
            }
        } else {
            var subid = $("#deptId").val();
            var academicId = $("#pupilsightSchoolYearID").val();
            var programId = $("#pupilsightProgramID_MC").val();
            var classId = $("#pupilsightClassID").val();
            //console.log(skid);
            if (skid != '' && subid != '') {
                var type = 'delSubjectSkills';
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: subid, type: type, academicId: academicId, programId: programId, classId: classId },
                    async: true,
                    success: function (response) {
                        //$("#clickstaffunassign").click();
                    }
                });
            }
        }
    });

    $(document).on('click', '.showSkillBySubId', function () {
        // if ($(this).is(':checked')) {
        //     var chk = 'checked';
        // } else {
        //     var chk = 'unchecked';
        // }
        var subid = $(this).attr('data-id');
        var academicId = $("#pupilsightSchoolYearID").val();
        var programId = $("#pupilsightProgramID_MC").val();
        var classId = $("#pupilsightClassID").val();

        if (academicId != '' && programId != '' && classId != '' && subid != '') {
            var type = 'getSubjectSkills';
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                // data: { val: subid, type: type, academicId: academicId, programId: programId, classId: classId, chk: chk },
                data: { val: subid, type: type, academicId: academicId, programId: programId, classId: classId },
                async: true,
                success: function (response) {
                    $("#deptId").val(subid);
                    $("#skillList").html('');
                    $("#skillList").html(response);

                }
            });
        }
    });


    $(document).on('click', '#saveSubjectToClass', function () {
        var sub = [];
        $.each($(".subId:checked"), function () {
            sub.push($(this).attr('data-id'));
        });
        var subid = sub.join(",");
        if (subid != '') {
            $("#subject_to_class_form").submit();
        } else {
            alert('You Have to Select Subject!');
        }
    });


    $(document).on('click', '#copySubjectToClass', function () {
        var sub = [];
        $.each($(".subId:checked"), function () {
            sub.push($(this).attr('data-sid'));
        });
        var subid = sub.join(",");
        if (subid != '') {
            var type = 'subjectToClassId';
            var val = subid;
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#clickSubjectToClass").click();
                    }
                });
            }

        } else {
            alert('You Have to Select Department of THose Who have Curriculum!');
        }
    });

    $(document).on('change', '.changeGradeSystemCondition', function () {
        var val = $(this).val();
        var sid = $(this).attr('data-id');
        if (val != '' && sid != '') {
            var type = 'changeGradeSystemCondition';
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type, sid: sid },
                async: true,
                success: function (response) {
                    alert('Your Pass Fail Condition Saved Successfully');
                }
            });
        } else {
            alert('Yo Have to Choose Pass Fail Condition!');
        }
    });

    $(document).on('click', '#addBulkStudentEnrolment', function () {
        var favorite = [];
        $.each($(".stuid:checked"), function () {
            favorite.push($(this).val());
        });

        var stuid = favorite.join(",");

        var checked = $(".enrollstuid:checked").length;
        if (checked >= 1) {
            alert('You Have to Select Students Not Enrolled.');
            return false;
        } else {
            if (stuid) {
                var val = stuid;
                var type = 'addstudentidInSession';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type },
                        async: true,
                        success: function (response) {
                            $("#submitBulkStudentEnrolment").click();
                        }
                    });
                }
            } else {
                alert('You Have to Select Students Not Enrolled.');
            }
        }
    });

    $(document).on('change', '.enableLinkbychkBox', function () {
        if ($(this).is(':checked')) {
            $("#disableLink").hide();
            $("#enableLink").show();
        } else {
            $("#enableLink").hide();
            $("#disableLink").show();
        }
    });

    $(document).on('click', '#copyElectiveGroup', function () {
        var sub = [];
        $.each($("input[name='id[]']:checked"), function () {
            sub.push($(this).val());
        });
        var subid = sub.join(",");
        if (subid != '') {
            var type = 'electiveGroupId';
            var val = subid;
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#clickElectiveGroup").click();
                    }
                });
            }

        } else {
            alert('You Have to Select Elective Group!');
        }
    });


    $(document).on('change', '.parentChkBox', function () {
        var id = $(this).val();
        if ($(this).is(':checked')) {
            $(".chkChild" + id).prop("checked", true);
        } else {
            $(".chkChild" + id).prop("checked", false);
        }
        chkChildClass();
    });

    $(document).on('change', '.childChkBox', function () {
        var id = $(this).attr('data-par');
        if ($(this).is(':checked')) {
            //$(".chkChild"+id).prop("checked", true);
        } else {
            $("#chkParent" + id).prop("checked", false);
        }
        chkChildClass();
    });

    function chkChildClass() {
        var sub = [];
        $.each($(".childChkBox:checked"), function () {
            sub.push($(this).val());
        });
        var subid = sub.join(",");
        $("input[name='pupilsightMappingID'").val(subid);
    }


    $(document).on('click', '#deleteTestAssignClass', function () {
        var sub = [];
        // $.each($(".assignCls:not(:checked)"), function() {
        $.each($(".assignCls:checked"), function () {

            var tid = $(this).attr('data-tid');
            var prg = $(this).attr('data-par');
            var cls = $(this).attr('data-cls');
            sub.push(tid + '-' + prg + '-' + cls);
        });
        var subid = sub.join(",");
        //alert(subid);
        if (subid != '') {
            var type = 'deleteAssignCls';
            var val = subid;
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        location.reload();
                    }
                });
            }

        } else {
            alert('There are No Test Instance Created for Delete!');
        }
    });

    $(document).on('click', '#saveTestCreate', function () {
        var mapid = $("input[name='pupilsightMappingID").val();

        if ($('#enable_schedule').attr('checked')) {
            var sdate = $("#start_date").val();
            var stime = $("#start_time").val();
            var edate = $("#end_date").val();
            var etime = $("#end_time").val();

            if (sdate == '') {
                $("#start_date").addClass('erroralert');
            } else {
                $("#start_date").removeClass('erroralert');
            }
            if (stime == '') {
                $("#start_time").addClass('erroralert');
            } else {
                $("#start_time").removeClass('erroralert');
            }
            if (edate == '') {
                $("#end_date").addClass('erroralert');
            } else {
                $("#end_date").removeClass('erroralert');
            }
            if (etime == '') {
                $("#end_time").addClass('erroralert');
            } else {
                $("#end_time").removeClass('erroralert');
            }

            if (!$("#start_date").hasClass('erroralert') && !$("#start_time").hasClass('erroralert') && !$("#end_date").hasClass('erroralert') && !$("#end_time").hasClass('erroralert')) {
                if (mapid != '') {
                    $("#testCreate").submit();
                } else {
                    alert('You Have to Select Class From Tree Structure!');
                }
            }

        } else {
            if (mapid != '') {
                $("#testCreate").submit();
            } else {
                alert('You Have to Select Class From Tree Structure!');
            }
        }
    });

    $(document).on('click', '#saveTestSubjectCategory', function () {
        var sub = [];
        $.each($(".subject_type_id:checked"), function () {
            sub.push($(this).val());
        });
        var subid = sub.join(",");
        //alert(subid);
        if (subid != '') {
            $.ajax({
                url: 'modules/Academics/select_sub_categories_addProcess.php',
                type: 'post',
                data: $('#testSubjectCategoryForm').serialize(),
                async: true,
                success: function (response) {
                    $("#TB_closeWindowButton").click();
                }
            });
        } else {
            alert('Please Select at Least One Subject Category');
        }
    });

    $(document).on('change', '#subCatGrade', function () {
        var id = $(this).attr('data-id');
        var val = $(this).val();
        if (val == "Grade") {
            $(".marks" + id).prop('disabled', true);
        } else {
            $(".marks" + id).prop('disabled', false);
        }

    });

    $(document).on('change', '#changeByMethod', function () {
        var val = $(this).val();
        if (val == "Grade") {
            $("#min_marks").prop('disabled', true);
            $("#max_marks").prop('disabled', true);
        } else {
            $("#min_marks").prop('disabled', false);
            $("#max_marks").prop('disabled', false);
        }

    });


    $(document).on('click', '#noAddGeneralTest', function () {
        alert('You Have to Save General Before Configure Test!');
    });

    $(document).on('click', '#copyTestMaster', function () {
        if ($("input[name='id[]']").is(':checked')) {
            var checked = $("input[name='id[]']:checked").length;
            if (checked > 1) {
                alert("Please Select One Test!");
                return false;
            } else {
                var hrf = $(this).attr('data-hrf');
                var id = $("input[name='id[]']:checked").val();
                if (id != '') {
                    var newhrf = hrf + '&tid=' + id;
                    $("#showTestMasterCopyForm").attr('href', newhrf);
                    $("#showTestMasterCopyForm").click();
                } else {
                    alert("Please Select Test!");
                }
            }
        } else {
            alert("Please Select Test!");
        }
    });

    $(document).on('click', '#copyAllData', function () {
        if ($(".copyAll").is(':checked')) {
            var checked = $(".copyAll:checked").length;
            if (checked > 1) {
                alert("Please Select One!");
                return false;
            } else {
                var id = $(".copyAll:checked").val();
                $(".cpyskill").val($("#cpyskill" + id).val());
                if ($("#cpytsted" + id).is(':checked')) {
                    $(".cpytsted").prop('checked', true);
                } else {
                    $(".cpytsted").prop('checked', false);
                }
                $(".cpyassmethod").val($("#cpyassmethod" + id).val());
                $(".cpyassoption").val($("#cpyassoption" + id).val());
                $(".cpymaxmrks").val($("#cpymaxmrks" + id).val());
                $(".cpyminmrks").val($("#cpyminmrks" + id).val());
                if ($("#cpyenbrms" + id).is(':checked')) {
                    $(".cpyenbrms").prop('checked', true);
                } else {
                    $(".cpyenbrms").prop('checked', false);
                }
                $(".cpygrdsys").val($("#cpygrdsys" + id).val());
                $(".cpyexdte").val($("#cpyexdte" + id).val());
                $(".cpyexstme").val($("#cpyexstme" + id).val());
                $(".cpyexetme").val($("#cpyexetme" + id).val());
                $(".cpyrmid").val($("#cpyrmid" + id).val());
                $(".cpystid").val($("#cpystid" + id).val());
                if ($("#cpyaat" + id).is(':checked')) {
                    $(".cpyaat").prop('checked', true);
                } else {
                    $(".cpyaat").prop('checked', false);
                }
            }
        } else {
            alert("Please Select One!");
        }
    });


    $(document).on('change', '#testMasterId', function () {
        var tname = $("#testMasterId  option:selected").text();
        $("#testName").val(tname);
    });

    $(document).on('click', '#updateTestClick', function () {
        if ($("input[name='id[]']").is(':checked')) {
            var checked = $("input[name='id[]']:checked").length;
            if (checked > 1) {
                alert("Please Select One Test!");
                return false;
            } else {
                var hrf = $(this).attr('data-hrf');
                var id = $("input[name='id[]']:checked").val();
                if (id != '') {
                    var newhrf = hrf + '&tid=' + id;
                    $("#updateTest").attr('href', newhrf);
                    window.setTimeout(function () {
                        $("#updateTest")[0].click();
                    }, 10);

                } else {
                    alert("Please Select Test!");
                }
            }
        } else {
            alert("Please Select Test!");
        }
    });

    $(document).on('change', '#updateByColumnType', function () {
        $('.hideUpdateOption').hide();
        var val = $(this).val();
        $('#' + val).show();
    });

    $(document).on('click', '#updateTestBulkWise', function () {
        var sub = [];
        // $.each($(".assignCls:not(:checked)"), function() {
        $.each($(".testid:checked"), function () {
            sub.push($(this).val());
        });
        var subid = sub.join(",");
        //alert(subid);
        if (subid != '') {
            var type = 'updateBulkTest';
            var val = subid;
            var testcol = $('#updateByColumnType').val();
            var testdata = $('#' + testcol).val();
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type, testcol: testcol, testdata: testdata },
                    async: true,
                    success: function (response) {
                        location.reload();
                    }
                });
            }

        } else {
            alert('Please Select Test!');
        }
    });

    $(document).on('click', '#updateTestSettings', function () {
        var sub = [];
        // $.each($(".assignCls:not(:checked)"), function() {
        $.each($(".testid:checked"), function () {
            sub.push($(this).val());
        });
        var subid = sub.join(",");
        //alert(subid);
        if (subid != '') {
            var type = 'updateBulkTestettings';
            var val = subid;
            var testcol = $(this).attr('data-type');
            var testdata = $(this).attr('data-val');
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type, testcol: testcol, testdata: testdata },
                    async: true,
                    success: function (response) {
                        location.reload();
                    }
                });
            }

        } else {
            alert('Please Select Test!');
        }
    });


})(jQuery);



$(document).ready(function () {
    $('#expore_tbl').find('.sortable').each(function () {
        $(this).removeClass("sortable");
    });
});



// $(".fdate").datepicker();

function iframeLoaded(id) {
    var iFrameID = document.getElementById(id);
    if (iFrameID) {
        // here you can make the height, I delete it first, then I make it again           
        iFrameID.height = "";
        iFrameID.height = iFrameID.contentWindow.document.body.scrollHeight + "px";
    }
}

$(document).on('click', '#unassignsubj', function () {

    var favorite = [];
    $.each($("input[name='st_id[]']:checked"), function () {
        favorite.push($(this).val());
    });
    var stuid = favorite.join(",");
    //alert(stuid);
    if (stuid) {
        var checked = $("input[name='st_id[]']:checked").length;
        if (checked > 1) {
            alert("Please Select One Staff!");
            return false;
        } else {
            var val = stuid;
            var type = 'changestaffstatus';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#clickstaffunassign").click();
                    }
                });
            }
        }
    } else {
        alert('You Have to Select Staff.');
    }
});

$(document).on('click', '.delSeattr_trans', function () {
    var wid = $(this).attr('data-wid');
    var id = $(this).attr('data-id');
    var cid = $(this).attr('data-cid');
    // alert(wid + id);

    if (confirm("Are you sure want to Delete Transition?")) {
        $.ajax({
            url: 'modules/Campaign/delete_wf_transition_fromeditpage.php',
            type: 'post',
            data: { id: id, wid: wid, cid: cid },
            async: true,
            success: function (response) {
                //$(".seatdiv" + id).remove();
                $(".deltr" + id).remove();
                // window.location.href = response;
            }
        });
    }
    // $(this).parent().parent().parent().parent().parent().remove();
});

//attendance type 
$(document).on('change', '#att_type_id', function () {

    var att_type = $("#att_type_id").val();
    if (att_type == 1) {
        $(".showsessionclick").show();

    } else {
        $(".showsessionclick").hide();
        $("#session_table").hide();


    }
    // alert(att_type);

});
//session_add 

$(document).on('click', '#session_add', function () {

    var no_of_sessions = $("#no_of_session").val();

    // alert(no_of_sessions);
    //  var atype = $(this).attr('data-type');
    //  alert(stuid);
    if (no_of_sessions) {
        var val = no_of_sessions;
        var type = 'display_multiple_attend_session';
        if (val != '') {

            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    //  alert(response);
                    $("#session_table").show();

                    $("#session_table").html(response);
                    // $("#clickStudentsubject").click();
                }
            });
        }
    }
});

$(document).on('change', '#pupilsightRollGroupID', function () {
    var id = $(this).val();
    var yid = $('#pupilsightSchoolYearID').val();
    var pid = $('#pupilsightProgramID').val();
    var cid = $('#pupilsightYearGroupID').val();
    var type = 'getStudent';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, yid: yid, pid: pid, cid: cid },
        async: true,
        success: function (response) {
            $("#pupilsightPersonID").html();
            $("#pupilsightPersonID").html(response);
        }
    });
});
$(document).on('change', '.getClasses', function () {

    var id = $(this).val();
    var yid = $('#pupilsightSchoolYearID').val();

    var pid = $('#filterClassByprogramId').val();

    var cid = $('#fetchClassByprogramId').val();
    var favorite = [];
    $.each($("input[name='pupilsightRollGroupID[]']:checked"), function () {
        favorite.push($(this).attr('data-section'));
    });
    var sid = favorite.join(",");

    var type = 'getMultiStudent';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, yid: yid, pid: pid, cid: cid, sid: sid },
        async: true,
        success: function (response) {
            $("#pupilsightPersonID").html();
            $("#pupilsightPersonID").html(response);
            $("#pupilsightPersonIDs").html();
            $("#pupilsightPersonIDs").html(response);



        }
    });

});


$(document).on('click', '.periodcss', function () {

    var favorite = [];
    $.each($("input[name='pd_id[]']:checked"), function () {
        favorite.push($(this).attr('id'));
    });
    var pdid = favorite.join(",");
    if (pdid) {
        var val = pdid;
        var type = 'attendancePeriod';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    //$("#clickstaffunassign").click();
                }
            });
        }
    } else {
        alert('You Have to Select Atleast One Period.');
    }
});

$(document).on('click', '.sendButton_attendance', function () {
    var stuids = [];
    $.each($("input[name='student_id[]']:checked"), function () {
        stuids.push($(this).attr('id'));
    });
    var stuid = stuids.join(",");

    if (stuid) {
        $(".sendButton_attendance").removeClass('activestate');
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
        alert('You Have to Select Applicant');
        window.setTimeout(function () {
            $("#large-modal-new_attendance").removeClass('show');
            $("#chkCounterSession").removeClass('modal-open');
            $(".modal-backdrop").remove();
        }, 10);
    }

});


$(document).on('click', '#sendEmailSms_attendance', function () {
    var emailquote = $("#emailQuote_att").val();
    var smsquote = $("#smsQuote_att").val();
    var favorite = [];
    $.each($("input[name='student_id[]']:checked"), function () {
        favorite.push($(this).attr('id'));
    });
    var stuid = favorite.join(", ");
    if (stuid) {

        if (emailquote != '' || smsquote != '') {
            $("#preloader").show();
            $.ajax({
                url: 'modules/Attendance/send_attendance_email_sms.php',
                type: 'post',
                data: { stuid: stuid, emailquote: emailquote, smsquote: smsquote },
                async: true,
                success: function (response) {
                    alert('Your Message Sent Successfully! click Ok to continue ');
                    location.reload();
                }
            });
        } else {
            alert('You Have to Enter Quote.');
        }
    } else {
        alert('You Have to Select Applicant.');

    }
});

$(document).on('click', '.include_core', function () {

    if ($(this).attr('checked')) {
        $(this).val(1);
    } else {
        $(this).val(0);
    }


});

$(document).on('click', '#add_student_to_subject', function () {


    var url = $(this).attr('data-href');

    var favorite = [];
    $.each($("input[name='student_id[]']:checked"), function () {

        favorite.push($(this).val());
    });
    var stuid = favorite.join(",");
    //alert(stuid);
    if (stuid) {
        var val = stuid;
        var type = 'addstudentidInSession';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    $("#clickstudent_to_subject").click();
                }
            });
        }
    } else {
        alert('You Have to Select Students.');
    }
});
$(document).on('click', '.tick_icon', function () {
    var ths = $(this);
    var stid = $(this).attr('data-stid');
    var sid = $(this).attr('data-sid');
    var eid = $(this).attr('data-eid');
    var classId = $("#classId").val();
    var progId = $("#progId").val();
    var maxsel = $(this).attr('data-maxsel');



    if ($(this).hasClass("greyicon")) {
        $(this).toggleClass("greenicon");
        if ($(".tick_icon").hasClass("greenicon")) {
            $(this).toggleClass("greyicon");
        }

        var kount = 0;
        $.each($(".chkcls" + eid + '-' + stid), function () {
            if ($(this).hasClass("greenicon")) {
                kount = parseInt(kount) + 1;
            }
        });
        if (kount > maxsel) {
            alert("You Can't Assign More Subject");
            $(this).removeClass("greenicon");
            $(this).addClass("greyicon");
        } else {
            var type = 'addElectiveSubjectToStudent';
            val = stid;
            if (val != '' && sid != '' && eid != '' && classId != '' && progId != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type, sid: sid, eid: eid, classId: classId, progId: progId, chktype: 'add' },
                    async: true,
                    success: function (response) {
                        //alert('Elective Subject Assign Succesfully');
                    }
                });
            }
        }
    } else {
        if (confirm("Are you sure want to Delete Elective Subject to Particular Student")) {
            var type = 'addElectiveSubjectToStudent';
            val = stid;
            if (val != '' && sid != '' && eid != '' && classId != '' && progId != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type, sid: sid, eid: eid, classId: classId, progId: progId, chktype: 'delete' },
                    async: true,
                    success: function (response) {
                        //alert('Elective Subject Deleted Succesfully');
                        ths.removeClass("greenicon");
                        ths.addClass("greyicon");
                    }
                });
            }
        } else {
            return false;
        }
    }


});



$(document).on('click', '.copy_test_cls', function () {
    var next_acyr = $("#next_acyr").val();

    var favorite = [];
    $.each($("input[name='id[]']:checked"), function () {
        favorite.push($(this).val());
    });
    var testid = favorite.join(",");
    //  alert(next_acyr);

    if (testid) {
        var val = testid;
        var type = 'copytesttonextyear';
        if (val != '') {
            if (confirm("Are you sure want to Continue")) {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type, next_acyr: next_acyr },
                    async: true,
                    success: function (response) {
                        // alert(response);
                        // parent.history.back();
                        window.location = document.referrer;
                    }
                });
            } else {
                return false;
            }
        }
    } else {
        alert('You Have to Select Test.');
        return false;
    }
});

$(document).on('click', '#electiveSub', function () {
    var stuids = [];
    $.each($("input[name='id[]']:checked"), function () {
        stuids.push($(this).val());
    });
    var stuid = stuids.join(",");
    if (stuid) {
        var val = stuid;
        var type = 'selectSub';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    $("#TB_closeWindowButton").click();
                }
            });
        }
    } else {
        alert('You Have to Select Subject.');
    }
});


$(document).on('click', '#electiveSection', function () {
    var stuids = [];
    $.each($("input[name='id[]']:checked"), function () {
        stuids.push($(this).val());
    });
    var stuid = stuids.join(",");
    // alert(stuid);
    if (stuid) {
        var val = stuid;
        var type = 'selectSection';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    $("#TB_closeWindowButton").click();
                }
            });
        }
    } else {
        alert('You Have to Select Elective Section.');
    }
});


$(document).on('click', '.col_header', function () {
    $(this).parent().parent().nextUntil('tr.col_header_new').slideToggle();
});



$(document).on('click', '.rotate', function () {
    $(this).toggleClass("down");
});


$(document).on('change', '#select_sub', function () {
    var option = $(this).val();
    if (option == '2') {
        $("#seletcategories").click();
        $(".enb_dis").prop('disabled', true);
    } else {
        $(".enb_dis").prop('disabled', false);
    }
});

$(document).on('click', '#modify_test_btn', function () {
    var atype = $(this).attr('data-type');
    var favorite = [];
    $.each($("input[name='id[]']:checked"), function () {
        favorite.push($(this).val());
    });
    var testid = favorite.join(",");
    if (testid) {
        var val = testid;
        var type = 'addtestIdinSession';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    $("#clickmodify_test").click();
                }
            });
        }
    } else {

        alert('You Have to Select Test.');
    }
});

$(document).ready(function () {

    if ($('#enable_schedule').attr('checked')) {
        $(".show_test_schedule").show();

    } else {
        $(".show_test_schedule").hide();
    }

});

$(document).on('click', '#enable_schedule', function () {

    if ($(this).attr('checked')) {

        $(".show_test_schedule").show();
    } else {
        $(".show_test_schedule").hide();
    }
});

$(document).on('change', '#pupilsightProgramID_check', function () {
    var id = $(this).val();
    var type = 'getClass';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type },
        async: true,
        success: function (response) {
            $("#pupilsightYearGroupID_check").html();
            $("#pupilsightYearGroupID_check").html(response);
        }
    });
});

$(document).on('change', '#fetchClassByprogramId', function () {
    var id = $(this).val();

    var pid = $('#filterClassByprogramId').val();

    var type = 'getSection_checkbox';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pid: pid },
        async: true,
        success: function (response) {

            $("#pupilsightRollGroupID_check").html();
            $("#pupilsightRollGroupID_check").html(response);
        }
    });
});

$(document).on('click', '#copy_test_class_section_wise', function () {
    var atype = $(this).attr('data-type');
    var favorite = [];
    $.each($("input[name='id[]']:checked"), function () {
        favorite.push($(this).val());
    });
    var testid = favorite.join(",");
    if (testid) {
        if (favorite.length == 1) {
            var val = testid;
            var type = 'addtestIdinSession';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        $("#clickcopy_test_to_sections").click();
                    }
                });
            }
        } else {
            alert('Please Select One test at a Time!');
        }
    } else {

        alert('You Have to Select Test.');
    }
});
$(document).on('click', '#modifyMarksEntry', function () {

    var favorite = [];
    var test_id = [];
    var test_status = [];
    var checked = $("input[name='student_id[]']:checked").length;
    if (checked > 1) {
        alert('You Have to Select only one Student.');

    } else {
        // alert($(this).attr('data_testid'));
        $.each($("input[name='student_id[]']:checked"), function () {
            favorite.push($(this).val());
            test_id.push($(this).attr('data_testid'));
            test_status.push($(this).attr('data_status'));

        });
        //var test_ids = test_id.join(",");
        var testid = favorite.join(",");
        var t_status = test_status.join(",")

        if (testid) {
            if (t_status == 0) {
                var val = testid;
                var type = 'stdMarksEntry';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type, test_id: test_id },
                        async: true,
                        success: function (response) {
                            window.location.href = response;
                            $("#modifyMarks").click();
                            // var val = test_ids;
                            // var type = 'storetestId';
                            // $.ajax({
                            //     url: 'ajax_data.php',
                            //     type: 'post',
                            //     data: { val: val, type: type },
                            //     async: true,
                            //     success: function (response) {
                            //         window.location.href = response;
                            //         $("#modifyMarks").click();
                            //     }
                            // });
                        }
                    });
                }
            } else {
                alert('Student Marks is locked.');
            }
        } else {
            alert('You Have to Select Student.');
        }
    }
});

$(document).on('click', '#studentMarksEntry', function () {
    var checked = $("input[name='stuid[]']:checked").length;
    if (checked > 1) {
        alert('You Have to Select only one Student.');
    } else {
        var favorite = [];
        $.each($("input[name='stuid[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var test_id = $("#testId").val();
        var studentid = favorite.join(",");
        //  alert(testid);
        if (studentid) {
            var val = studentid;
            var type = 'stdMarksEntry';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type, test_id: test_id },
                    async: true,
                    success: function (response) {
                        window.location.href = response;
                        $("#marksentry").click();
                    }
                });
            }
        } else {

            alert('You Have to Select Student.');
        }
    }
});


$(document).on('click', '.previous_std_data', function () {
    $("#preloader").show();
    var favorite = [];
    $(".chkData").each(function () {
        favorite.push($(this).val());
    });
    var getdata = favorite.join(",");
    var chkChng = $("#chkMarksSaveData").val();
    if (chkChng == 1) {
        if (getdata != '') {
            if (confirm("Do you want to Save the Data")) {
                $("#preloader").hide();
                $("#marksbyStudent").submit();
                var current_id = $(this).attr('id');
                var test_id = $(this).attr('data-tid');
                if (current_id) {
                    var val = current_id;
                    var type = 'pre_stdMarksEntry';
                    if (val != '') {
                        $.ajax({
                            url: 'ajax_data.php',
                            type: 'post',
                            data: { val: val, type: type, test_id: test_id },
                            async: true,
                            success: function (response) {
                                location.reload();
                            }
                        });
                    }
                } else {
                    $("#preloader").hide();
                    alert('You Have to Select Student.');
                }
            } else {
                var current_id = $(this).attr('id');
                var test_id = $(this).attr('data-tid');
                if (current_id) {
                    var val = current_id;
                    var type = 'pre_stdMarksEntry';
                    if (val != '') {
                        $.ajax({
                            url: 'ajax_data.php',
                            type: 'post',
                            data: { val: val, type: type, test_id: test_id },
                            async: true,
                            success: function (response) {
                                location.reload();
                            }
                        });
                    }
                } else {
                    $("#preloader").hide();
                    alert('You Have to Select Student.');
                }
            }
        } else {
            var current_id = $(this).attr('id');
            var test_id = $(this).attr('data-tid');
            if (current_id) {
                var val = current_id;
                var type = 'pre_stdMarksEntry';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type, test_id: test_id },
                        async: true,
                        success: function (response) {
                            location.reload();
                        }
                    });
                }
            } else {
                $("#preloader").hide();
                alert('You Have to Select Student.');
            }
        }
    } else {
        var current_id = $(this).attr('id');
        var test_id = $(this).attr('data-tid');
        if (current_id) {
            var val = current_id;
            var type = 'pre_stdMarksEntry';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type, test_id: test_id },
                    async: true,
                    success: function (response) {
                        location.reload();
                    }
                });
            }
        } else {
            $("#preloader").hide();
            alert('You Have to Select Student.');
        }
    }

});

$(document).on('click', '.next_std_data', function () {
    $("#preloader").show();
    var favorite = [];
    $(".chkData").each(function () {
        favorite.push($(this).val());
    });
    var getdata = favorite.join(",");
    var chkChng = $("#chkMarksSaveData").val();
    if (chkChng == 1) {
        if (getdata != '') {
            if (confirm("Do you want to Save the Data")) {
                $("#preloader").hide();
                $("#marksbyStudent").submit();
                var current_id = $(this).attr('id');
                if (current_id) {
                    var val = current_id;
                    var type = 'next_stdMarksEntry';
                    if (val != '') {
                        $.ajax({
                            url: 'ajax_data.php',
                            type: 'post',
                            data: { val: val, type: type },
                            async: true,
                            success: function (response) {
                                location.reload();
                            }
                        });
                    }
                } else {
                    $("#preloader").hide();
                    alert('You Have to Select Student.');
                }
            } else {
                var current_id = $(this).attr('id');
                if (current_id) {
                    var val = current_id;
                    var type = 'next_stdMarksEntry';
                    if (val != '') {
                        $.ajax({
                            url: 'ajax_data.php',
                            type: 'post',
                            data: { val: val, type: type },
                            async: true,
                            success: function (response) {
                                location.reload();
                            }
                        });
                    }
                } else {
                    $("#preloader").hide();
                    alert('You Have to Select Student.');
                }
            }
        } else {
            var current_id = $(this).attr('id');
            if (current_id) {
                var val = current_id;
                var type = 'next_stdMarksEntry';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type },
                        async: true,
                        success: function (response) {
                            location.reload();
                        }
                    });
                }
            } else {
                $("#preloader").hide();
                alert('You Have to Select Student.');
            }
        }
    } else {
        var current_id = $(this).attr('id');
        if (current_id) {
            var val = current_id;
            var type = 'next_stdMarksEntry';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        location.reload();
                    }
                });
            }
        } else {
            $("#preloader").hide();
            alert('You Have to Select Student.');
        }
    }

});


$(document).on('click', '.sendButton_test_result', function () {
    var stuids = [];
    $.each($("input[name='student_id[]']:checked"), function () {
        stuids.push($(this).val());
    });
    var stuid = stuids.join(",");
    if (stuid) {
        $(".sendButton_test_result").removeClass('activestate');
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
        window.setTimeout(function () {
            $("#large-modal-stud_test_result").removeClass('show');
            $("#chkCounterSession").removeClass('modal-open');
            $(".modal-backdrop").remove();
        }, 10);
    }

});

$(document).on('click', '#sendEmailSms_stud_test_result', function () {

    var emailquote = $("#emailQuote_stud_result").val();
    var smsquote = $("#smsQuote_stud_result").val();
    var favorite = [];
    $.each($("input[name='student_id[]']:checked"), function () {
        favorite.push($(this).val());
    });
    var stuid = favorite.join(", ");
    // alert(emailquote);
    if (stuid) {
        if (emailquote != '' || smsquote != '') {
            $("#preloader").show();
            $.ajax({
                url: 'modules/School Admin/send_stud_test_result_email_msg.php',
                type: 'post',
                data: { stuid: stuid, emailquote: emailquote, smsquote: smsquote },
                async: true,
                success: function (response) {
                    alert('Your Message Sent Successfully! click Ok to continue ');
                    location.reload();
                }
            });
        } else {
            alert('You Have to Enter Quote.');
        }
    } else {
        alert('You Have to Select Applicants.');

    }


});


//lock & unlock marks entry
$(document).on('click', '.lock_me_btn', function (e) {

    e.preventDefault();
    var atype = $(this).attr('data-type');
    var url = $(this).attr('data-href');
    var favorite = [];
    var entry_ids = [];
    $.each($("input[name='student_id[]']:checked"), function () {
        var entryid = $(this).attr('data_tid');
        entry_ids.push(entryid);
        favorite.push($(this).val());
    });
    var stuid = favorite.join(",");
    var entids = entry_ids.join(",");
    //  alert(entids);
    if (atype == 'lock') {
        var msg = 'Lock';
        var typeval = 'lock_mark_entry';
    } else {
        var typeval = 'unlock_mark_entry';
        var msg = 'UnLock';

    }
    if (entids) {
        if (confirm("Are you sure want to continue? ")) {
            var val = entids;
            //deleteStudentRoutes
            var action_type = typeval;
            var type = 'lock_unlock_mark_entry';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type, action_type: action_type },
                    async: true,
                    success: function (response) {
                        //  alert("You Have Locked the Entry Successfully!");
                        location.reload();
                    }
                });
            }
        }
    } else {

        alert('You Have to Select Students.');

    }
});

$(document).on('change', '#pupilsightProgramIDbyPP', function () {
    var id = $(this).val();
    var type = 'getClass';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type },
        async: true,
        success: function (response) {
            $("#pupilsightYearGroupIDbyPP").html();
            $("#pupilsightYearGroupIDbyPP").html(response);
        }
    });
});

$(document).on('change', '#pupilsightYearGroupIDbyPP', function () {
    var id = $(this).val();
    var pid = $('#pupilsightProgramIDbyPP').val();
    var type = 'getSection';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pid: pid },
        async: true,
        success: function (response) {
            $("#pupilsightRollGroupIDbyPP").html();
            $("#pupilsightRollGroupIDbyPP").html(response);
        }
    });
});



$(document).on('change', '.chkAll', function () {
    if ($(this).is(':checked')) {
        $(".chkChild").prop("checked", true);
    } else {
        $(".chkChild").prop("checked", false);
    }
});

$(document).on('change', '.chkChild', function () {
    if ($(this).is(':checked')) {
        //$(".chkChild"+id).prop("checked", true);
    } else {
        $(".chkAll").prop("checked", false);
    }
});

$(document).on('change', '#changeFeeStrByProgId', function () {
    var id = $(this).val();
    $.ajax({
        url: 'fullscreen.php?q=/modules/Finance/invoice_assign_manage_ajax_add.php',
        type: 'post',
        data: { id: id },
        async: true,
        success: function (response) {
            $("#changeFeeStructure").html();
            $("#changeFeeStructure").html(response);
        }
    });
});


$(document).on('change', '#program_class', function () {
    var id = $(this).val();
    var aid = $("#aid").val();
    $.ajax({
        url: 'fullscreen.php?q=/modules/Finance/invoice_generatedBy_class_ajax_add.php',
        type: 'post',
        data: { id: id, aid: aid },
        async: true,
        success: function (response) {
            $("#changeFeeStructure").html();
            $("#changeFeeStructure").html(response);
        }
    });
});


$(document).on('click', '.sendButton_attendance_rprt', function () {
    var stuids = [];
    $.each($("input[name='student_id[]']:checked"), function () {
        stuids.push($(this).val());
    });
    var stuid = stuids.join(",");
    if (stuid) {
        $(".sendButton_test_result").removeClass('activestate');
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
        window.setTimeout(function () {
            $("#large-modal-stud_attendance_rprt").removeClass('show');
            $("#chkCounterSession").removeClass('modal-open');
            $(".modal-backdrop").remove();
        }, 10);
    }

});

$(document).on('click', '#sendEmailSms_stud_attend_rprt', function () {

    var emailquote = $("#emailQuote_stud_result").val();
    var smsquote = $("#smsQuote_stud_rpt").val();
    var favorite = [];
    $.each($("input[name='student_id[]']:checked"), function () {
        favorite.push($(this).val());
    });
    var stuid = favorite.join(", ");

    if (stuid) {
        if (emailquote != '' || smsquote != '') {
            var r = confirm("Are u sure want to send sms ?");
            if (r == true) {
                $("#preloader").show();
                $.ajax({
                    url: 'modules/Attendance/send_attendance_email_sms.php',
                    type: 'post',
                    data: { stuid: stuid, emailquote: emailquote, smsquote: smsquote },
                    async: true,
                    success: function (response) {
                        alert('Your Message Sent Successfully! click Ok to continue ');
                        location.reload();
                    }
                });
            }
        } else {
            alert('You Have to Enter Quote.');
        }
    } else {
        alert('You Have to Select Applicants.');

    }

});

$(document).on('click', '#saveMarksByStudent', function () {
    $("#marksbyStudent").submit();
});

$(document).on('change', '#classId', function () {
    var id = $(this).val();
    var aid = $("#pupilsightSchoolYearID").val();
    var pid = $("#programId").val();
    var type = 'getNewSectionByClassProg';
    if (pid != "") {
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, pid: pid, aid: aid },
            async: true,
            success: function (response) {
                $("#sectionId").html('');
                $("#sectionId").html(response);
            }
        });
    } else {
        alert('Please Select Program');
    }
});

$(document).on('change', '#programId', function () {
    var pid = $(this).val();
    var aid = $("#pupilsightSchoolYearID").val();
    var id = $("#classId").val();
    var type = 'getNewSectionByClassProg';
    if (pid != "" && id != '' && id != 'Select Class') {
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, pid: pid, aid: aid },
            async: true,
            success: function (response) {
                $("#sectionId").html('');
                $("#sectionId").html(response);
            }
        });
    }
});




$(document).on('change', '#Staff_program', function () {
    var pid = $(this).val();

    var id = $("#staff_id").val();
    //   alert(pid + '' + id);
    var type = 'getClasses_assignedtoStaff';
    if (pid != "" && id != '' && pid != 'Select Program') {
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, pid: pid },
            async: true,
            success: function (response) {
                $("#Staff_class").html();
                $("#Staff_class").html(response);
            }
        });
    }
});
$(document).on('change', '#Staff_class', function () {
    var cid = $(this).val();

    var id = $("#staff_id").val();
    // alert(cid + '' + id);
    var type = 'getsections_assignedtoStaff';
    if (cid != "" && id != '' && cid != 'Select Class') {
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, cid: cid },
            async: true,
            success: function (response) {
                //  alert(response);
                $("#Staff_section").html();
                $("#Staff_section").html(response);
            }
        });
    }
});

/* Custom Field Creation */
var customField = new CustomField();
$(function () {
    customField.load();
});

var pcdt;

function CustomField() {
    var _this = this;

    this.load = function () {
        try {
            var link = "ajax_custom_data.php";
            var val = _this.getPageNames();
            var relation = _this.getParameterByName("relation");
            //console.log("relation: ", relation);
            //var val = "user_manage_edit.php";
            if (val) {
                var type = "getCustomControl";
                $.ajax({
                    type: "POST",
                    url: link,
                    data: {
                        val: val,
                        type: type,
                        relation: relation
                    },
                }).done(function (msg) {
                    if (msg) {
                        var obj = jQuery.parseJSON(msg);
                        //console.log(obj);
                        _this.loadAction(obj);
                    }
                });
            }
        } catch (ex) {
            console.log(ex);
        }
    };

    this.getPreviousTab = function (tabName, obj) {
        var tabs = obj["data"][0]["tabs"].split(",");
        var len = tabs.length;
        var i = 0;
        var lastTab = "";
        while (i < len) {
            if (tabs[i] == tabName) {
                break;
            }
            if ($("#" + tabs[i]).length > 0) {
                lastTab = tabs[i];
            }
            i++;
        }
        return lastTab;
    }

    this.loadAction = function (obj) {
        try {
            // console.log(obj["data"]);

            var len = obj["data"].length;
            var i = 0;
            var deactivateIds = "";
            //first create tab
            while (i < len) {
                //console.log(obj["data"][i].tab, ":", $("#" + obj["data"][i].tab).length);
                if (obj["data"][i].tab) {
                    var tabName = obj["data"][i].tab;
                    var lastTab = _this.getPreviousTab(tabName, obj);
                    if (obj.view) {
                        _this.createViewTab(tabName, lastTab);
                    } else {
                        //console.log("create Edit tab ", lastTab, " tabName ", tabName);
                        _this.createEditTab(tabName, lastTab);
                    }
                }
                i++;
            }


            /*
            if (obj["data"][0].tabs) {
                if (obj.view) {
                    _this.viewCustomOrderModule(obj["data"][0].tabs);
                } else {
                    
                    //$("select").blur();
                    //$("input").blur();
                    //$("textarea").blur();
                    //$("#gender").blur();
                    //_this.editCustomOrderModule(obj["data"][0].tabs);
                }
            }*/

            //create element
            i = 0;
            while (i < len) {
                if (obj["data"][i].field_type != "tab") {
                    if (obj["data"][i].active == "Y") {
                        //add Field
                        //console.log("view ", obj.view);
                        if (obj.view) {
                            //show
                            //console.log("create view ", obj["data"][i]);
                            _this.createView(obj["data"][i]);
                        } else {
                            //create input
                            //console.log(obj["data"][i]);
                            _this.createInput(obj["data"][i]);
                        }
                    } else {
                        if (deactivateIds) {
                            deactivateIds += ",";
                        }
                        if (obj.view) {
                            deactivateIds += "#" + obj["data"][i].field_name;
                        } else {
                            deactivateIds += "#row_" + obj["data"][i].field_name;
                        }
                    }
                }
                i++;
            }

            if (_this.isRowActive) {
                $("#" + _this.activeElement).append(_this.colActiveStr);
                _this.colActiveStr = "";
            }
            //console.log("deactivateIds: ",deactivateIds);
            if (deactivateIds) {
                if (obj.view) {
                    $(deactivateIds).html("");
                } else {
                    //$(deactivateIds).hide();
                    //$(deactivateIds).remove();
                }

            }

            if (_this.activeColManage) {
                var len = _this.activeColManage.length;
                var i = 0;
                while (i < len) {
                    var el = _this.activeColManage[i];
                    var element = el["element"];
                    var row = el["row"];
                    var col = el["col"];

                    var colCount = (document.getElementById(element).rows[0].cells.length) - 1;
                    currentCol = (document.getElementById(element).rows[row].cells.length) - 1;
                    if (currentCol == col) {
                        var j = 0;
                        var str = "";
                        var colCount1 = colCount - 1;
                        while (j < colCount) {
                            if (j == colCount1) {
                                str += "<td class='tdborder'>&nbsp;</td>";
                            } else {
                                str += "<td>&nbsp;</td>";
                            }
                            j++;
                        }
                        //console.log(str);
                        $('#' + element + ' tr:last').append(str);
                    }
                    i++;
                }
            }

        } catch (ex) {
            console.log(ex);
        }
    };

    this.editCustomOrderModule = function (tabs) {
        var st = tabs.split(",");
        var len = st.length;
        var i = 0;
        var elements = new Array();
        //var elementsId = new Array();
        var elementHeader = new Array();
        var elementsStr = "";
        var isMasterTableClassAdded = true;

        while (i < len) {
            var id = "tbody_" + st[i];
            if ($("#" + id).length > 0) {

                if (isMasterTableClassAdded) {
                    $("#" + id).parent().addClass("editMasterTableNewOrder");
                    isMasterTableClassAdded = false;
                }
                elements.push($("#" + id).clone(true));
                if (elementsStr) {
                    elementsStr += ",";
                }
                elementsStr += "#" + id;
            }
            i++;
        }

        $(elementsStr).remove();
        len = elements.length;
        i = 0;

        while (i < len) {
            $(".editMasterTableNewOrder").append(elements[i]);
            i++;
        }
    }

    this.viewCustomOrderModule = function (tabs) {
        var st = tabs.split(",");
        var len = st.length;
        var i = 0;
        var elements = new Array();
        //var elementsId = new Array();
        var elementHeader = new Array();
        var elementsStr = "";

        while (i < len) {
            if ($("#" + st[i]).length > 0) {
                var htag = _this.tagH4Class(st[i]);
                elementHeader.push(htag);

                elements.push($("#" + st[i]).clone(true));
                //elementsId.push(st[i]);
                if (elementsStr) {
                    elementsStr += ",";
                }
                elementsStr += "#" + st[i];
            }
            i++;
        }

        /*
        console.log("elementHeader: ",elementHeader);
        console.log("st: ",st);
        console.log("elementsStr: ", elementsStr);
        console.log("elementsId: ",elementsId);
        console.log("elements: ",elements);*/

        $(elementsStr).remove();
        $(".removeHtag").remove();

        len = elements.length;
        i = 0;

        while (i < len) {
            //$("#"+lastId).after(elements[i]);
            $(".card-body").append("<h4>" + elementHeader[i] + "</h4>");
            $(".card-body").append(elements[i]);
            i++;
        }
    }

    this.tagH4Class = function (id) {
        var htag = "";
        try {
            if ($("#" + id).prev('h4').text()) {
                htag = $("#" + id).prev('h4').text();
                $("#" + id).next('h4').addClass("removeHtag");
                $("#" + id).prev('h4').addClass("removeHtag");
            } else if ($("#" + id).prev('h2').text()) {
                htag = $("#" + id).prev('h2').text();
                $("#" + id).next('h2').addClass("removeHtag");
                $("#" + id).prev('h2').addClass("removeHtag");
            } else {
                htag = $("#" + id).prev('div').prev("h2").text();
                $("#" + id).next('div').next('h2').addClass("removeHtag");
                $("#" + id).prev('div').prev("h2").addClass("removeHtag");
            }
        } catch (ex) {
            console.log(ex);
        }
        return htag;
    }

    /*
    this.createViewTab = function (obj) {
        var str = "<h4>" + obj.field_title + "</h4>";
        str += "<table id='" + obj.field_name + "' class='smallIntBorder' cellspacing='0' style='width: 100%'>";
        str += "<tbody></tbody>";
        str += "</table>";
        $("#" + obj.tab).after(str);
    };
    
    this.createEditTab = function (obj) {
        var str = "<tbody id='tbody_" + obj.field_name + "'>";
        str += "<tr id='" + obj.field_name + "' class='break flex flex-col sm:flex-row justify-between content-center p-0'>";
        str += "<td class='flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0 ' colspan='2'>";
        str += "<div class='input-group stylish-input-group'>";
        str += "<h3>" + obj.field_title + "</h3>";
        str += "</div>";
        str += "</td>";
        str += "</tr>";
        str += "</tbody>";
        $("#tbody_" + obj.tab).after(str);
    };
    */

    this.createViewTab = function (tab_name, lastTab) {
        try {
            //console.log("Is new Tab ", $(document.getElementById(lastTab)).length);
            if ($(document.getElementById(tab_name)).length == 0) {
                var tabTitle = _this.titleCase(tab_name.replace(/_/g, ' '));
                var str = "<h4>" + tabTitle + "</h4>";
                str += "<table id='" + tab_name + "' class='table'>";
                str += "<tbody></tbody>";
                str += "</table>";
                $(document.getElementById(lastTab)).after(str);
            }
        } catch (ex) {
            console.log("Tab creation isssue tab_name ", tab_name, " lasttab ", lastTab);
            console.log(ex);
        }
    };

    this.createEditTab = function (tab_name, lastTab) {
        //console.log("Is new Tab ", $(document.getElementById(tab_name)).length);
        if ($(document.getElementById(tab_name)).length == 0) {
            var tabTitle = _this.titleCase(tab_name.replace(/_/g, ' '));
            var str = "<div class='row mb-1' id='tbody_" + tab_name + "'>";
            str += "<div id='" + tab_name + "' class='row mb-1 break'>";
            str += "<div class='col-sm'>";
            str += "<div><h3>" + tabTitle + "</h3></div>";
            str += "</div>";
            str += "</div>";
            str += "</div>";
            //console.log("tab_id", tab_name);
            //console.log(str);
            $("#tbody_" + lastTab).after(str);
        }
    };

    this.titleCase = function (str) {
        str = str.toLowerCase().split(' ');
        for (var i = 0; i < str.length; i++) {
            str[i] = str[i].charAt(0).toUpperCase() + str[i].slice(1);
        }
        return str.join(' ');
    }


    this.getPageNames = function () {
        var tm = _this.getParameterByName("q");
        var pageName = "";
        if (tm) {
            var st = tm.split("/");
            var len = st.length;
            var i = 0;
            while (i < len) {
                if (st[i]) {
                    var n = st[i].indexOf(".php");
                    if (n > 0) {
                        pageName = st[i];
                        break;
                    }
                }
                i++;
            }
        }
        return pageName;
    };

    this.getParameterByName = function (name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    };

    this.isEmpty = function (value) {
        return (
            // null or undefined
            (value == null) ||

            // has length and it's zero
            (value.hasOwnProperty('length') && value.length === 0) ||

            // is an Object and has no keys
            (value.constructor === Object && Object.keys(value).length === 0)
        )
    }

    _this.colCurrent = 0;
    _this.isRowActive = false;
    _this.colActiveStr = "";
    _this.activeElement = "";
    _this.activeColManage = [];
    this.createView = function (obj) {

        var fieldTitle = "";
        var fieldName = "";
        if (obj.field_title) {
            fieldTitle = obj.field_title;
            fieldName = obj.field_name;
        }
        _this.activeColManage = [];
        var elementVal = "";
        try {
            if (pcdt) {
                elementVal = pcdt.dt[obj.field_name];
                if (!elementVal) {
                    elementVal = "&nbsp;";
                } else if (obj.field_type == "file") {
                    elementVal = "<a href='" + pcdt.dt[obj.field_name] + "' download>Download</a>";
                } else if (obj.field_type == "image") {
                    elementVal = "<a href='" + pcdt.dt[obj.field_name] + "' download title='download image'><img src='" + pcdt.dt[obj.field_name] + "' class='img-thumbnail'/></a>";
                }

                if (obj.field_type == "checkboxes") {
                    elementVal = _this.createCheckboxView(obj);
                } else if (obj.field_type == "radioboxes") {
                    elementVal = _this.createRadioView(obj);
                }
            }
        } catch (ex) {
            console.log(ex);
        }


        var validElement = false;
        var element = "content";
        if ($("#" + obj.tab).length > 0) {
            element = obj.tab;
            validElement = true;
        } else {
            return;
        }

        //console.log("element", obj.tab, obj);

        if (validElement) {
            //console.log(obj.field_name, " : ", elementVal);

            var colCount = 0;
            var currentRow = 0;
            var currentCol = 0;

            if (document.getElementById(element).rows.length > 0) {
                colCount = (document.getElementById(element).rows[0].cells.length) - 1;
                currentRow = document.getElementById(element).rows.length - 1;
                currentCol = (document.getElementById(element).rows[currentRow].cells.length) - 1;
            }

            var str = `<td id="` + fieldName + `" style="width: 34%; vertical-align: top">
                <span class="form-label">` + fieldTitle + `</span>
                <div>` + elementVal + `</div></td>`;
            //console.log(obj.field_name, currentRow, currentCol, colCount);
            var isData = $('#' + element + ' tr:last td:last').text();
            var isNotAdded = true;

            if (isData == "") {
                isNotAdded = false;
                str = `<span class="form-label">` + fieldTitle + `</span>
                <div>` + elementVal + `</div>`;
                $('#' + element + ' tr:last td:last').html(str);
            } else if (currentCol != 0 && currentCol == colCount) {
                //console.log("here??");
                $("#" + element).append("<tr></tr>");
            }
            if (isNotAdded) {
                $('#' + element + ' tr:last').append(str);
            }
            if (document.getElementById(element).rows.length > 0) {
                var crow = document.getElementById(element).rows.length - 1;
                var ccol = document.getElementById(element).rows[crow].cells.length - 1;
                //console.log(crow, ccol);
                if (ccol < colCount) {
                    var ts = { "element": element, "row": crow, "col": ccol };
                    _this.activeColManage.push(ts);
                }
            }

        } else {
            var str = `<table class="smallIntBorder" cellspacing="0" style="width: 100%"><tr>
                <td id="` + fieldName + `" style="width: 34%; vertical-align: top" colspan="` + colCount + `">
                <span class="form-label">` + fieldTitle + `</span>
                <div>` + elementVal + `</div></td>
                </tr></table>`;
            $("#" + element).append(str);
        }
    };

    this.createInput = function (obj) {
        if (obj.field_type) {
            //'varchar','text','date','url','select','checkboxes','radioboxes'
            //console.log("obj.field_type: ", obj.field_type);
            if (obj.field_type == "tinytext" || obj.field_type == "mobile" || obj.field_type == "varchar" || obj.field_type == "date" || obj.field_type == "email" || obj.field_type == "file" || obj.field_type == "image") {
                _this.createTextField(obj);
            } else if (obj.field_type == "text") {
                _this.createTextArea(obj);
            } else if (obj.field_type == "dropdown") {
                _this.createDropDown(obj);
            } else if (obj.field_type == "number") {
                _this.createNumberField(obj);
            } else if (obj.field_type == "radioboxes") {
                _this.createRadio(obj);
            } else if (obj.field_type == "checkboxes") {
                _this.createCheckbox(obj);
            }

            /*
            else if (obj.field_type == "date") {
                _this.createDateField(obj);
            } 
            */
        }
    };

    this.createName = function (obj) {
        return " name =\"custom[" + obj.table_name + "][" + obj.field_type + "][" + obj.field_name + "]\" ";
    }

    this.createOnlyName = function (obj) {
        return "custom[" + obj.table_name + "][" + obj.field_type + "][" + obj.field_name + "]";
    }

    this.createTextField = function (obj) {
        var required = "";
        var requiredStr = "";
        if (obj.required == "Y") {
            required = " required ";
            requiredStr = "<span class='ml-1'> * </span>";
        }

        var tfVal = "";
        if (pcdt) {
            tfVal = pcdt.dt[obj.field_name];
            if (_this.isEmpty(tfVal)) {
                tfVal = "";
            }
            //console.log(obj.field_name, tfval);
        }

        var description = "";
        if (obj.field_description) {
            description = obj.field_description;
        }

        var file_type = "text";
        var acceptType = "";
        if (obj.field_type == "email") {
            file_type = "email";
        } else if (obj.field_type == "date") {
            file_type = "date";
        } else if (obj.field_type == "file") {
            file_type = "file";
            if (!tfVal) {
                tfVal = "";
            }
        } else if (obj.field_type == "image") {
            file_type = "file";
            acceptType = " accept='image/x-png,image/jpeg,image/jpg' ";
            if (!tfVal) {
                tfVal = "";
            }
        }

        mobile = "";
        if (obj.field_type == "mobile") {
            mobile = " maxlength=10; minlength=10; pattern='[6789][0-9]{9}'";
        }

        var fileDownloadStr = "";
        if (file_type == "file" && (tfVal != "")) {
            fileDownloadStr = '<div class="col-sm-auto"><a href="' + tfVal + '" class="btn btn-secondary" download><span class="mdi mdi-download"></span></a></div>';
            tfVal = "";
            required = "";
            requiredStr = "";
        }

        //console.log("file_type: ", file_type);
        var elementName = _this.createName(obj);
        var str = `<div class="row mb-1">                            
        <div class="col-sm">
        <div>
            <label for="` + obj.field_name + `" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">` + obj.field_title + requiredStr + ` 
            <span class="text-xxs text-gray italic font-normal mt-1 sm:mt-0">` + description + `</span>
            </label>
        </div>
        </div>
        `+ fileDownloadStr + `
        <div class="col-sm  standardWidth">
        <div>
            <div class="flex-1 relative">
            <input type="`+ file_type + `" id="` + obj.field_name + `" ` + elementName + ` class="w-full form-control" value = "` + tfVal + `" ` + required + ` ` + mobile + `  ` + acceptType + `>
            </div>
        </div>
        </div></div> `;

        if (obj.tab) {
            $("#tbody_" + obj.tab).append(str);
        }
    };

    this.createNumberField = function (obj) {
        var required = "";
        var requiredStr = "";
        if (obj.required == "Y") {
            required = " required ";
            requiredStr = "<span class='ml-1'> * </span>";
        }

        var tfVal = "";
        if (pcdt) {
            tfVal = pcdt.dt[obj.field_name];
            if (_this.isEmpty(tfVal)) {
                tfVal = "";
            }
        }

        var description = "";
        if (obj.field_description) {
            description = obj.field_description;
        }

        var length = obj.field_length;
        var maxlength = "";
        if (length) {
            maxlength = " maxlength='" + length + "' ";
        }
        //console.log("file_type: ", file_type);
        var elementName = _this.createName(obj);
        var str = `<div class="row mb-1">                            
        <div class="col-sm">
        <div>
            <label for="` + obj.field_name + `" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">` + obj.field_title + requiredStr + ` 
            <span class="text-xxs text-gray italic font-normal mt-1 sm:mt-0">` + description + `</span>
            </label>
        </div>
        </div>
        
        <div class="col-sm  standardWidth">
        <div>
            <div class="flex-1 relative">
            <input type="text" id="` + obj.field_name + `" ` + elementName + ` class="w-full form-control" value = "` + tfVal + `" ` + required + ` onblur="customField.fixLength(this.id,'` + obj.field_name + `',` + length + `);" ` + maxlength + `" onkeypress="return customField.validatenumerics(event);">
            <div id="` + obj.field_name + `_error" class="invalid-feedback">Invalid Element Length</div>
            </div>
        </div>
        </div></div> `;

        if (obj.tab) {
            $("#tbody_" + obj.tab).append(str);
        }
    };

    this.fixLength = function (id, name, length) {
        //console.log(id);
        if (length) {
            var ele = document.getElementById(id);
            //console.log(ele.value.length, length);
            $('#' + name + '_error').hide();
            if (!(ele.value.length == length)) {
                //throw validation error
                if (ele.value) {
                    ele.focus();
                    $('#' + name + '_error').show();
                }
            }
        }
    }

    this.validatenumerics = function (key) {
        //getting key code of pressed key
        var keycode = (key.which) ? key.which : key.keyCode;
        //comparing pressed keycodes

        if (keycode > 31 && (keycode < 48 || keycode > 57)) {
            //alert(" You can enter only characters 0 to 9 ");
            return false;
        }
        else return true;
    }

    this.createTextArea = function (obj) {
        var required = "";
        var requiredStr = "";
        if (obj.required == "Y") {
            required = " required ";
            requiredStr = " * ";
        }

        var tfVal = "";
        if (pcdt) {
            tfVal = pcdt.dt[obj.field_name];
            if (_this.isEmpty(tfVal)) {
                tfVal = "";
            }
        }

        var description = "";
        if (obj.field_description) {
            description = obj.field_description;
        }

        var elementName = _this.createName(obj);
        var str = `<div class="row mb-1">            
            <div class="col-sm">
                <div>
                    <label for="` + obj.field_name + `" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">` + obj.field_title + requiredStr + `
                    <span class="text-xxs text-gray italic font-normal mt-1 sm:mt-0">` + description + `</span>
                    </label>
                </div>
            </div>                                          
            <div class="col-sm  standardWidth">
                <div>
                    <textarea rows="4" id="`+ obj.field_name + `" ` + elementName + ` class="w-full" ` + required + `>` + tfVal + `</textarea>
                </div>
            </div>
        </div> `;

        if (obj.tab) {
            $("#tbody_" + obj.tab).append(str);
        }
    };

    this._createDateField = function (obj) {
        var required = "";
        var requiredStr = "";
        if (obj.required == "Y") {
            required = " required ";
            requiredStr = " * ";
        }

        var tfVal = "";
        if (pcdt) {
            tfVal = pcdt.dt[obj.field_name];
            if (_this.isEmpty(tfVal)) {
                tfVal = "";
            }
        }

        var str = `< tr id = "" class=" flex flex-col sm:flex-row justify-between content-center p-0" >
            <td class="flex flex-col flex-grow justify-center -mb-1 sm:mb-0  px-2 border-b-0 sm:border-b border-t-0 ">
                <div class="input-group stylish-input-group">
                    <label for="` + obj.field_name + `" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">` + obj.field_title + requiredStr + `<br><span class="text-xxs text-gray-600 italic font-normal mt-1 sm:mt-0">` + obj.field_description + `</span></label>
        </div>
        </td>
                <td class="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0 ">
                    <div class="input-group stylish-input-group">
                        <div class="flex-1 relative"><input type="text" id="` + obj.field_name + `" name="custom[][` + obj.table_name + `][` + obj.field_name + `]" class="w-full hasDatepicker" value="` + tfVal + `" ` + required + ` autocomplete="off" maxlength="10"><span class=" LV_validation_message LV_valid"></span>
                        </div>
                    </div>
                </td>
        </tr>`;
        //<div class="flex-1 relative"><input type="text" id="` + obj.field_name + `" name="custom_` + obj.table_name + `_` + obj.field_name + `" class="w-full hasDatepicker" value="` + tfVal + `" ` + required + ` autocomplete="off" maxlength="10"><span class=" LV_validation_message LV_valid"></span>

        if (obj.tab) {
            $("#tbody_" + obj.tab).append(str);
            var dateField = window[obj.field_name];
            dateField = $("#" + obj.field_name).datepicker({ onSelect: function () { $(this).blur(); }, onClose: function () { $(this).change(); } });
        }
    };

    this.createDropDown = function (obj) {
        var required = "";
        var requiredStr = "";
        if (obj.required == "Y") {
            required = " required ";
            requiredStr = " * ";
        }

        var tfVal = "";
        if (pcdt) {
            tfVal = pcdt.dt[obj.field_name];
            if (_this.isEmpty(tfVal)) {
                tfVal = "";
            } else {
                tfVal = $.trim(tfVal);
            }
        }

        var opt = new Array();

        if (obj.options) {
            opt = (obj.options).split(",");
        }

        var len = opt.length;
        var i = 0;

        var description = "";
        if (obj.field_description) {
            description = obj.field_description;
        }

        var elementName = _this.createName(obj);
        var selectStr = "";
        var str = `<div class="row mb-1">                       
            <div class="col-sm">
                <div>
                    <label for="` + obj.field_name + `" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">` + obj.field_title + requiredStr + `</label>
                </div>
            </div>                                          
            <div class="col-sm  standardWidth">
                <div>
                    <div class="flex-1 relative">
                    <select id="`+ obj.field_name + `" ` + elementName + ` class="w-full">
                    <option></option>`
        while (i < len) {
            selectStr = "";
            if (tfVal == $.trim(opt[i])) {
                selectStr = " selected ";
            }
            str += `<option value="` + opt[i] + `" ` + selectStr + `>` + opt[i] + `</option>`;
            i++;
        }
        `</select>
                    </div>
                </div>
            </div>
        </div>`;

        if (obj.tab) {
            $("#tbody_" + obj.tab).append(str);
        }
    };

    this.createRadio = function (obj) {
        var required = "";
        var requiredStr = "";
        if (obj.required == "Y") {
            required = " required ";
            requiredStr = " * ";
        }

        var tfVal = "";
        if (pcdt) {
            tfVal = pcdt.dt[obj.field_name];
            if (_this.isEmpty(tfVal)) {
                tfVal = "";
            } else {
                tfVal = $.trim(tfVal);
            }
        }

        var opt = new Array();

        if (obj.options) {
            opt = (obj.options).split(",");
        }

        var len = opt.length;
        var i = 0;

        var description = "";
        if (obj.field_description) {
            description = obj.field_description;
        }

        var elementName = _this.createOnlyName(obj);
        var selectStr = "";
        var radioid = "";
        var str = `<div class="row mb-1">                       
            <div class="col-sm">
                <div>
                    <label for="` + obj.field_name + `" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">` + obj.field_title + requiredStr + `</label>
                </div>
            </div>                                          
            <div class="col-sm  standardWidth">`
        while (i < len) {
            radioid = elementName + "_rd_" + i;
            selectStr = "";
            if (tfVal == $.trim(opt[i])) {
                selectStr = " checked ";
            }
            str += `<input type='radio' name="` + elementName + `" id="` + radioid + `" value="` + opt[i] + `" ` + selectStr + `><label for="` + radioid + `" class='mx-2 font16'>` + opt[i] + `</label>`;
            i++;
        }
        `</div>
        </div>`;
        //console.log("radio str", str);
        if (obj.tab) {
            $("#tbody_" + obj.tab).append(str);
        }
    };

    this.createRadioView = function (obj) {
        var tfVal = "";
        if (pcdt) {
            tfVal = pcdt.dt[obj.field_name];
            if (_this.isEmpty(tfVal)) {
                tfVal = "";
            } else {
                tfVal = $.trim(tfVal);
            }
        }

        var opt = new Array();

        if (obj.options) {
            opt = (obj.options).split(",");
        }

        var len = opt.length;
        var i = 0;

        var description = "";
        if (obj.field_description) {
            description = obj.field_description;
        }

        var strDisabled = " disabled";


        var elementName = _this.createOnlyName(obj);
        var selectStr = "";
        var radioid = "";
        var str = `<div class="row mb-1">                       
            <div class="col-sm  standardWidth">`
        while (i < len) {
            radioid = elementName + "_rd_" + i;
            selectStr = "";
            if (tfVal == $.trim(opt[i])) {
                selectStr = " checked ";
            }
            str += `<input type='radio' id="` + radioid + `" value="` + opt[i] + `" ` + selectStr + strDisabled + `><label for="` + radioid + `" class='mx-2 font16'>` + opt[i] + `</label>`;
            i++;
        }
        `</div>
        </div>`;
        return str;
    };

    this.createCheckbox = function (obj) {

        var required = "";
        var requiredStr = "";
        if (obj.required == "Y") {
            required = " required ";
            requiredStr = " * ";
        }

        var tfVal = "";
        if (pcdt) {
            tfVal = pcdt.dt[obj.field_name];
            if (_this.isEmpty(tfVal)) {
                tfVal = "";
            } else {
                tfVal = $.trim(tfVal);
            }
        }


        var opt = new Array();

        if (obj.options) {
            opt = (obj.options).split(",");
        }

        var len = opt.length;
        var i = 0;

        var description = "";
        if (obj.field_description) {
            description = obj.field_description;
        }

        var elementName = _this.createOnlyName(obj);
        var selectStr = "";
        var radioid = "";

        var str = `<div class="row mb-1">                       
            <div class="col-sm">
                <div>
                    <label for="` + obj.field_name + `" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">` + obj.field_title + requiredStr + `</label>
                    ` + description + `
                </div>
            </div>                                          
            <div class="col-sm  standardWidth">`
        while (i < len) {
            radioid = elementName + "_ch_" + i;
            selectStr = "";
            var val = $.trim(opt[i]);
            if (tfVal) {
                if (tfVal.indexOf(val) !== -1) {
                    selectStr = " checked ";
                }
            }
            str += `<input type='checkbox' name="` + elementName + `[]" id="` + radioid + `" value="` + val + `" ` + selectStr + ` ><label for="` + radioid + `" class='mx-2 font16'>` + val + `</label>`;
            i++;
        }
        `</div>
        </div>`;

        if (obj.tab) {
            $("#tbody_" + obj.tab).append(str);
        }
    };

    this.createCheckboxView = function (obj) {

        var tfVal = "";
        if (pcdt) {
            tfVal = pcdt.dt[obj.field_name];
            if (_this.isEmpty(tfVal)) {
                tfVal = "";
            } else {
                tfVal = $.trim(tfVal);
            }
        }


        var opt = new Array();

        if (obj.options) {
            opt = (obj.options).split(",");
        }

        var len = opt.length;
        var i = 0;

        var description = "";
        if (obj.field_description) {
            description = obj.field_description;
        }

        var elementName = _this.createOnlyName(obj);
        var selectStr = "";
        var radioid = "";
        var strDisabled = " disabled";

        var str = `<div class="row mb-1">                       
            <div class="col-sm  standardWidth">`
        while (i < len) {
            radioid = elementName + "_ch_" + i;
            selectStr = "";
            var val = $.trim(opt[i]);
            if (tfVal) {
                if (tfVal.indexOf(val) !== -1) {
                    selectStr = " checked ";
                }
            }
            str += `<input type='checkbox' id="` + radioid + `" value="` + val + `" ` + selectStr + strDisabled + ` ><label for="` + radioid + `" class='mx-2 font16'>` + val + `</label>`;
            i++;
        }
        `</div>
        </div>`;
        return str;

    };
}

/* Custom Field Creation close */
$(document).on('change', '#Staff_section', function () {
    var id = $(this).val();
    var yid = $('#pupilsightSchoolYearID').val();
    var pid = $('#Staff_program').val();
    var cid = $('#Staff_class').val();
    var type = 'getStudent';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, yid: yid, pid: pid, cid: cid },
        async: true,
        success: function (response) {
            //  alert(response);
            $("#pupilsightPersonID").html();
            $("#pupilsightPersonID").html(response);
        }
    });
});

//get session based on program
$(document).on('change', '.program_class', function () {
    var id = $('.program_class option:selected').val();
    var type = 'getsession';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type },
        async: true,
        success: function (response) {
            $("#session").html();
            $("#session").html(response);
        }
    });
});

$(document).on('click', "input[name='submission_id[]']:checked", function () {
    var id = $(this).val();
    var cid = $("#campId").val();
    var fid = $("#formId").val();
    var type = 'getCampaignStatusButton';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, cid: cid, fid: fid },
        async: true,
        success: function (response) {
            $("#statusButton").html();
            $("#statusButton").html(response);
        }
    });
});


$(document).on('click', '.sendButton_campaign_list', function () {
    var submit_ids = [];
    $.each($("input[name='submission_id[]']:checked"), function () {
        submit_ids.push($(this).val());
    });
    var submt_id = submit_ids.join(",");
    if (submt_id) {
        $(".sendButton_campaign_list").removeClass('activestate');
        $(this).addClass('activestate');
        var noti = $(this).attr('data-noti');
        var cid = $(this).attr('data-cid');
        var fid = $(this).attr('data-fid');
        var type = 'chkCampaignFromField';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: cid, fid: fid, type: type },
            dataType: "json",
            async: true,
            success: function (response) {
                $("#showMobileField").html(response.mobile);
                $("#showEmailField").html(response.email);
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
            }
        });

    } else {
        alert('You Have to Select Applicants First');
        window.setTimeout(function () {
            $("#large-modal-campaign_list").removeClass('show');
            $("#chkCounterSession").removeClass('modal-open');
            $(".modal-backdrop").remove();
        }, 10);
    }

});

$(document).on('click', '#sendEmailSms_campaign', function (e) {
    // $('#sendEmailSms_campaignForm').on('submit', (function (e) {
    e.preventDefault();
    $("#preloader").show();
    var formData = new FormData(document.getElementById("sendEmailSms_campaignForm"));
    var emailquote = $("#emailQuote_camp").val();
    var emailSubjct_camp = $("#emailSubjct_camp").val();
    var emailAttachment = $('input[name=email_attach]')[0].files[0];
    var smsquote = $("#smsQuote_camp").val();
    var form_id = $("#form_id").val();
    var camp_id = $("#form_id").attr('data-cid');
    var favorite = [];
    $.each($("input[name='submission_id[]']:checked"), function () {
        favorite.push($(this).val());
    });
    var submit_id = favorite.join(", ");
    //   alert(submit_id + '-' + form_id + '-' + camp_id);
    if (submit_id) {
        if (emailquote != '' || smsquote != '') {
            formData.append('submit_id', submit_id);
            formData.append('emailquote', emailquote);
            formData.append('emailSubjct_camp', emailSubjct_camp);
            formData.append('smsquote', smsquote);

            $.ajax({
                url: "modules/Campaign/send_camp_email_msg.php",
                type: "POST",
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                async: false,
                success: function (data) {
                    alert("Message Sent.");
                    location.reload();
                    console.log(data);
                    $("#preloader").hide();
                }
            });
        } else {
            alert('You Have to Enter Message.');
        }
    } else {
        alert('You Have to Select Applicants.');

    }
});

// $("#expore_xl_campaign").click(function (e) {
$(document).on('click', '#expore_xl_campaign', function () {
    var submit_ids = [];
    $.each($("input[name='submission_id[]']:checked"), function () {
        submit_ids.push($(this).val());
    });
    var submt_id = submit_ids.join(",");

    if (submt_id == '') {
        alert('You Have to Select Application.');
    } else {
        $('#expore_tbl tr').find('td:eq(0),th:eq(0)').remove();
        $("#expore_tbl").table2excel({
            name: "Worksheet Name",
            filename: "campaign_submitted_form_list.xls",
            fileext: ".xls",
            exclude: ".checkall",
            exclude: ".rm_cell",
            exclude_inputs: true,
            columns: [0, 1, 2, 3, 4, 5]

        });
        location.reload();
    }
});

$(document).on('change', '.showFeeSettingButton', function () {
    var val = $(this).val();
    var id = $(this).attr('data-sfid');
    if (val == '1') {
        $("#sfid" + id).show();
        $(".feeSetting").removeClass('hiddencol');
    } else {
        $("#sfid" + id).hide();
    }
});

$(document).on('click', '#saveAdmissionFess', function () {
    var sub = [];
    $.each($(".feestrid:checked"), function () {
        sub.push($(this).val());
    });
    var subid = sub.join(",");
    var kid = $("#kid").val();
    if (subid != '') {
        //$("#admissionForm").submit();
        $.ajax({
            url: 'index.php?q=/modules/Campaign/fee_setting_addProcess.php',
            type: 'post',
            data: $('#admissionForm').serialize(),
            async: true,
            success: function (response) {
                $("#TB_overlay").remove();
                $("#TB_window").remove();
                $("#feeSettingId-" + kid).val(response);
            }
        });
    } else {
        alert('You Have to Select Fee Group!');
    }
});

$(document).on('click', '#fees_id', function () {
    var checked = $("input[name='fees_id[]']:checked").length;
    if (checked > 1) {
        alert("Please Select One Invoice!");
        location.reload();
    }
    $(".testcss").removeClass('hidediv');

});

$(document).on('change', '#chkAllInvoiceApplicant', function () {
    if ($(this).is(':checked')) {
        $(".chkinvoiceApplicant").prop('checked', true);
        var favorite = [];
        $.each($(".chkinvoiceApplicant:checked"), function () {
            if ($(this).val() != '0') {
                favorite.push($(this).val());
            }

        });
        var invids = favorite.join(", ");
        var sid = $("input[name=pupilsightPersonID]").val();
        //var invid = $(this).val();
        // $("#showPaymentButton").show();
        var type = 'applicantInvoiceFeeItem';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: invids, type: type, sid: sid },
            async: true,
            success: function (response) {
                $("#getInvoiceFeeItem").html('');
                $("#getInvoiceFeeItem").append(response);
                $("input[name=invoice_id]").val(invids);
                $("#collectionForm").show();
                $("#FeeItemManage").show();
                $(".hideFeeItemContent").show();
                // $("#chkAllFeeItem").prop('checked', true);
            }
        });
    } else {
        $(".chkinvoiceApplicant").prop('checked', false);
        $("#getInvoiceFeeItem").html('');
        addInvoiceFeeAmt();
        $("input[name=invoice_id]").val('');
    }
});

// $(document).on('click', '.chkinvoiceApplicant', function () {
//     $("#collectionForm")[0].reset();
//     $(".ddChequeRow").addClass('hiddencol');
//     var favorite = [];
//     var account_heads = [];
//     var series = [];
//     var aedt = [];
//     var ife = [];
//     $.each($(".chkinvoiceApplicant:checked"), function () {
//         favorite.push($(this).val());
//         account_heads.push($(this).attr("data-h"));
//         series.push($(this).attr("data-se"));
//         aedt.push($(this).attr("data-amtedt"));
//         ife.push($(this).attr("data-ife"));
//     });
//     var newData = removeDuplicates(account_heads);
//     var length1 = newData.length;
//     var chkStatus = false;
//     if (favorite.length != 0) {
//         var sid = $("input[name=submission_id]").val();
//         if (length1 == "1") {
//             chkStatus = true;
//         } else {
//             var r = confirm("Selected invoice receipt series are different.\n Do you want to make payment?");
//             if (r == true) {
//                 chkStatus = true;
//             } else {
//                 chkStatus = false;
//             }
//         }

//         //ajax request
//         if (chkStatus == true) {
//             var invids = favorite.join(", ");
//             var type = 'applicantInvoiceFeeItem';
//             $.ajax({
//                 url: 'ajax_data.php',
//                 type: 'post',
//                 data: { val: invids, type: type, sid: sid },
//                 async: true,
//                 success: function (response) {
//                     $(".btn_invoice_link_collection").hide();
//                     $(".addInvoiceLinkCollection").hide();
//                     $(".btn_cancel_invoice_collection").hide();
//                     $(".chkinvoice").hide();
//                     $(".apply_discount_btn").hide();
//                     $("#getInvoiceFeeItem").html('');
//                     $("#getInvoiceFeeItem").append(response);
//                     $("input[name=invoice_id]").val(invids);
//                     $("#collectionForm").show();
//                     $("#FeeItemManage").show();
//                     $(".hideFeeItemContent").show();
//                     $('#fn_fees_head_id').val(account_heads[0]);
//                     $('#recptSerId').val(series[0]);
//                     $(".oCls_0").hide();
//                     $('.icon_0').removeClass('fa-arrow-down');
//                     $('.icon_0').addClass('fa-arrow-right');
//                     $(".oCls_1").hide();
//                     $('.icon_1').removeClass('fa-arrow-down');
//                     $('.icon_1').addClass('fa-arrow-right');

//                     if (aedt[0] == '1') {
//                         $("#amount_paying").attr("readonly", false);
//                     } else {
//                         $("#amount_paying").attr("readonly", true);
//                     }

//                     if (ife[0] == '1') {
//                         $("#fine").attr("readonly", false);
//                     } else {
//                         $("#fine").attr("readonly", true);
//                     }

//                     setTimeout(function () {
//                         $("#chkAllFeeItem").prop("checked", true).trigger("change");
//                     }, 1000);

//                 }
//             });
//         }
//         //ends request
//     } else {
//         alert('Please select atleast one invoice');
//         $("#chkAllInvoice").prop('checked', false);
//         $(".invrow" + invid).remove();
//         addInvoiceFeeAmt();
//         $("input[name=invoice_id]").val(invids);
//     }
// });
// $(document).on('click', '.chkinvoiceApplicant', function () {
//     var favorite = [];
//     $.each($(".chkinvoiceApplicant:checked"), function () {
//         favorite.push($(this).val());
//     });
//     var invids = favorite.join(", ");
//     var sid = $("input[name=submission_id]").val();
//     var invid = $(this).val();
//     //$("#showPaymentButton").show();
//     if ($(this).is(':checked')) {
//         if (invid != '') {
//             var type = 'applicantInvoiceFeeItem';
//             $.ajax({
//                 url: 'ajax_data.php',
//                 type: 'post',
//                 data: { val: invid, type: type, sid: sid },
//                 async: true,
//                 success: function (response) {
//                     $("#getInvoiceFeeItem").append(response);
//                     $("input[name=invoice_id]").val(invids);
//                     $("#collectionForm").show();
//                     $("#FeeItemManage").show();
//                     $(".hideFeeItemContent").show();
//                     $("#chkAllFeeItem").trigger('click');
//                     // $(".selFeeItem").prop('checked', true);
//                 }
//             });
//         }
//     } else {
//         $("#chkAllInvoiceApplicant").prop('checked', false);
//         $(".invrow" + invid).remove();
//         addInvoiceFeeAmt();
//         $("input[name=invoice_id]").val(invids);
//     }

// });

$(document).on('change', '.showTemplate', function () {
    var val = $(this).val();
    var id = $(this).attr('data-sid');
    if (id != '' && val != '') {
        var hrf = $("#clickTemplate" + id).attr('data-hrf');
        var newhrf = hrf + val + '&width=900';
        $("#clickTemplate" + id).attr('href', newhrf);
        window.setTimeout(function () {
            $("#clickTemplate" + id).click();
        }, 10);

    }
});

$(document).on('click', '#configureTemplate', function () {
    var id = $(this).attr('data-id');
    var type = $(this).attr('data-type');
    if (type == '3') {
        var flag = true;
        var favorite1 = [];
        $.each($(".email-pupilsightTemplateID:checked"), function () {
            favorite1.push($(this).val());
        });
        var tid = favorite1.join(",");
        if (tid) {
            var checked = $(".email-pupilsightTemplateID:checked").length;
            if (checked > 1) {
                alert("Please Select One Email Template!");
                flag = false;
            } else {
                var name = $(".email-pupilsightTemplateID:checked").attr('data-nme');
                var estoretid = tid;
                var estoretname = name;
                // if (confirm("You Have Selected "+name)) {
                //     $("#pupilsightTemplateID-"+id).val(tid);
                //     $("#TB_overlay").remove();
                //     $("#TB_window").remove();
                // }
            }
        } else {
            alert('You Have to Select Email Template.');
            flag = false;
        }

        var favorite2 = [];
        $.each($(".sms-pupilsightTemplateID:checked"), function () {
            favorite2.push($(this).val());
        });
        var stid = favorite2.join(",");
        if (stid) {
            var checked = $(".sms-pupilsightTemplateID:checked").length;
            if (checked > 1) {
                alert("Please Select One Sms Template!");
                flag = false;
            } else {
                var sname = $(".sms-pupilsightTemplateID:checked").attr('data-nme');
                var sstoretid = stid;
                var sstoretname = sname;
            }
        } else {
            alert('You Have to Select Sms Template.');
            flag = false;
        }

        if (!flag) {
            return;
        }

        var nname = estoretname + ' , ' + sstoretname;
        if (confirm("You Have Selected " + nname)) {
            var ntid = estoretid + ',' + sstoretid;
            $("#pupilsightTemplateID-" + id).val(ntid);
            $("#TB_overlay").remove();
            $("#TB_window").remove();
        }

    } else {
        var favorite = [];
        $.each($(".pupilsightTemplateID:checked"), function () {
            favorite.push($(this).val());
        });
        var tid = favorite.join(",");
        if (tid) {
            var checked = $(".pupilsightTemplateID:checked").length;
            if (checked > 1) {
                alert("Please Select One Template!");
            } else {
                var name = $(".pupilsightTemplateID:checked").attr('data-nme');
                if (confirm("You Have Selected " + name)) {
                    $("#pupilsightTemplateID-" + id).val(tid);
                    $("#TB_overlay").remove();
                    $("#TB_window").remove();
                }
            }
        } else {
            alert('You Have to Select Template.');
        }
    }

});


$(document).on('click', '.clickOnStaffDIv', function () {
    var id = $(this).attr('data-id');
    $("#showTypeStaff" + id).show();
});


// $(document).on('keyup', '.getAllStaff', function() {
//     var val = $(this).val();
//     var id = $(this).attr('data-id');
//     var len = val.length;
//     if (val != '' && len > 2) {
//         var type = 'getAllStaff';
//         $.ajax({
//             url: 'ajax_data.php',
//             type: 'post',
//             data: { val: val, type: type, id: id },
//             async: true,
//             success: function(response) {
//                 $(".showAllStaff-" + id).html('');
//                 $(".showAllStaff-" + id).html(response);
//             }
//         });
//     }
// });

// $(document).on('click', '.clickGetStaff', function() {
//     var name = $(this).attr('data-name');
//     var id = $(this).attr('data-id');
//     var mid = $(this).attr('data-mid');
//     var prevname = $("#selmulusername" + id).val();
//     var previd = $("#selmuluserid" + id).val();
//     if (id != '') {
//         var nme = '<span class="spanStaff" id="delSpanStaff' + id + '">' + name + '<i class="fa fa-times delStaffSpan" data-id=' + id + '></i></span>';
//         $("#AllStaffDiv" + mid).html(nme);
//     }
// });

$(document).on('click', '#showManualReceipt', function () {
    if ($(this).is(':checked')) {
        $("#divManualReceipt").prop('disabled', false);
    } else {
        $("#divManualReceipt").prop('disabled', true);
    }
});

$(document).on('change', '#feeItems', function () {
    var selected_year = $("#feeItems").val();
    $(".before_academic").hide();
    $(".after_academic").removeClass("hidediv");
    $(".after_academic").removeAttr("style");
    if (selected_year) {
        var val = selected_year;
        var type = 'getfeeitems';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    $("#feeitemType").html('');
                    $("#feeitemType").html(response);

                }
            });
        }


    }
});


window.onload = function () {
    $(".receipt_none").attr("style", "background-color:#e0e0e0 !important")
};


$(document).on('change', '.disable_Reciept', function () {

    $('#receipt_number').val("");

    $checked = $('.disable_Reciept:checked').length;
    if ($checked > 0) {

        $(".receiptnumber").removeClass("receipt_none");
        $(".receiptnumber").removeAttr("style");
        $(".receipt_none").attr("style", "background-color:#fff");

    } else {
        $(".receipt_none").attr("style", "background-color:#e0e0e0 !important");
        $(".receiptnumber").addClass("receipt_none");

    }

});
$(document).on('click', '#cop_feeitem', function () {
    var url = $(this).attr('data-href');
    var favorite = [];
    $.each($("input[name='id[]']:checked"), function () {
        favorite.push($(this).val());
    });
    var feeId = favorite.join(",");
    //alert(subid);
    if (feeId) {
        var val = feeId;
        var type = 'copyfeeItemSession';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    $("#feeitem_copy").click();
                }
            });
        }
    } else {
        alert('You Have to Select Invoice.');
    }
});

$(document).on('click', '#showfee_assign_class', function () {
    alert('trst')
});

$(document).on('click', '#fee_assign_class', function () {

    var url = $(this).attr('data-href');
    var favorite = [];
    $.each($("input[name='id[]']:checked"), function () {
        favorite.push($(this).val());
    });
    var feeId = favorite.join(",");
    if (feeId) {

        var val = feeId;
        var type = 'feeItemSession';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    //  window.location.href = response.redirecturl;
                    $("#showfee_assign_class").click();
                }
            });
        }
    } else {
        alert('You Have to Select atleast one fee Strucure.');
    }

});




$(document).on('click', '.chkinvoice_parent', function () {
    var invids = $(this).attr('id');
    //var sid = $(this).attr('name');

    if (invids != '') {
        var type = 'invoiceFeeItem_parent';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: invids, type: type },
            async: true,
            success: function (response) {
                $('#chk_feeID').click();

            }
        });
    }


});


$(document).on('click', '.cancelReceiptPaymentHistory', function () {
    var submit_ids = [];
    $.each($("input[name='paymentHistory[]']:checked"), function () {
        submit_ids.push($(this).val());
    });
    var submt_id = submit_ids.join(",");
    if (submt_id) {
        var val = submt_id;
        var type = 'addtransactionidInSession';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    //  window.location.href = response.redirecturl;
                    $('#cancelReceiptSubmit').click();
                }
            });
        }
    } else {
        alert('You Have to Select atleast one Payment.');
    }
});


$(document).on('change', '#chkAllPaymentHistory', function () {
    if ($(this).is(':checked')) {
        $(".selPayHistory").prop('checked', true);
        addInvoiceFeeAmt();
    } else {
        $(".selPayHistory").prop('checked', false);
        addInvoiceFeeAmt();
    }

});
$(document).on('click', '.apply_discount_btn', function () {
    var favorite = [];
    $.each($(".chkinvoiceM:checked"), function () {
        favorite.push($(this).val());
    });
    var invoiceids = favorite.join(",");
    var type = "apply_discount_session";
    var p_stuId = $(".p_stuId").val();
    var pSyd = $(".pSyd").val();
    if (favorite.length != 0) {
        $.ajax({
            url: 'ajaxSwitch.php',
            type: 'post',
            data: { ids: invoiceids, p_stuId: p_stuId, pSyd: pSyd, type: type },
            async: true,
            success: function (response) {
                $("#apply_discount_popup").click();
            }
        });
    } else {
        alert('Please select atleast one invoice');
    }
});
$(document).on('change', '#discount_type_change', function () {
    var d_type = $(this).val();
    var sid = $("input[name=a_stuid]").val();
    var yid = $("input[name=a_yid]").val();
    var ids = $("input[name=a_invoices_ids]").val();
    var type = "get_dicount_type_change";
    $.ajax({
        url: 'ajaxSwitch.php',
        type: 'post',
        data: { d_type: d_type, sid: sid, yid: yid, ids: ids, type: type },
        async: true,
        success: function (response) {
            $(".discount_type_change_results").html(response);
        }
    });
});
$(document).on('change', '.chkinvoice_discount', function () {
    var inv_id = $(this).attr('data-id');
    if ($(this).is(':checked')) {
        $(".inid_" + inv_id).removeAttr('readonly');
    } else {
        $(".inid_" + inv_id).attr('readonly', 'readonly');
    }
});
$(document).on('change', '.a_selFeeItem', function () {
    var it_id = $(this).attr('data-id');
    if ($(this).is(':checked')) {
        $(".itid_" + it_id).removeAttr('readonly');
    } else {
        $(".itid_" + it_id).attr('readonly', 'readonly');
    }
});
$(document).on('click', '.btn_invoice_link_collection', function () {

    var favorite = [];
    var favorite1 = [];
    $.each($(".chkinvoiceM:checked"), function () {
        favorite.push($(this).attr('data-inv'));
        favorite1.push($(this).attr('data-stu'));
    });
    if (favorite.length != 0) {
        var val = favorite.join(",");
        var type = 'setSessionEditInvoice';
        var stu = favorite1.join(",");
        if (favorite.length == 1) {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type, stu: stu },
                async: true,
                success: function (response) {
                    //alert(response);
                    //  window.location.href = response.redirecturl;
                    $('#edit_invoice_collection_form').click();
                }
            });
        } else {
            alert("Please select only one invoice");
        }
    } else {
        alert('You Have to Select atleast one Invoice.');
    }
});
$(document).on('click', '.btn_cancel_invoice_collection', function () {
    var favorite = [];
    var favorite1 = [];
    $.each($(".chkinvoiceM:checked"), function () {
        favorite.push($(this).attr('data-inv'));
        favorite1.push($(this).attr('data-stu'));
    });
    //alert(favorite);
    if (favorite.length != 0) {
        if (favorite.length == 1) {
            var val = favorite.join(",");
            var type = 'setSessionEditInvoice';
            var stu = favorite1.join(",");
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type, stu: stu },
                async: true,
                success: function (response) {
                    //alert(response);
                    //  window.location.href = response.redirecturl;
                    $('#cancel_invoice_collection').click();
                }
            });
        } else {
            alert('Please Select One Invoice at a Time');
        }
    } else {
        alert('You Have to Select atleast one Invoice.');
    }
});

function removeDuplicates(data) {
    return [...new Set(data)]
}

$(document).on('click', '#addMultiPaymentItem', function () {
    var cid = $(this).attr('data-cid');
    var ncid = parseInt(cid) + 1;
    $(this).attr('data-cid', ncid);
    var disid = $(this).attr('data-disid');
    var type = 'getAjaxMultiPlayment';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: ncid, type: type, disid: disid },
        async: true,
        success: function (response) {
            $("#lastseatdiv").before(response);
        }
    });
});

$(document).on('click', '#MultiPayment', function () {
    var total_amount = $('#fullamount').val();
    var paying_amount = $('#totalAmount').text();
    var chk = 0;
    var bnk = 0;
    var ink = 0;
    var idk = 0;

    var formData = $("#multiPaymentForm").serialize();
    var chkamt = 0;
    var pmode = 0;

    $.each($("input[name='amount[]']"), function () {
        if ($(this).val() == '') {
            chkamt++;
            $(this).addClass('erroralert');
        } else {
            $(this).removeClass('erroralert');
        }
    });

    if (chkamt) {
        alert('You Have to Enter Amount');
        return false;
    }

    if (total_amount == paying_amount) {
        $.each($(".payment_slt_mode"), function () {
            $(this).removeClass('erroralert');
            var did = '';
            var val = '';
            did = $(this).attr('data-id');

            val = $(this).val();
            if (val) {
                //alert('bank_' + did + ' option:selected');
                if (val == '3' || val == '2') {
                    //alert($("select.bank_" +did+ " option:selected").val());
                    var selected = $(".bank_" + did + " option:selected");
                    if (selected.val() == '') {
                        chk++;
                        bnk++;
                        //alert('You Have to Select Bank');
                        $("select.bank_" + did).addClass('erroralert');
                    } else {
                        $("select.bank_" + did).removeClass('erroralert');
                    }

                    if ($(".ref_" + did).val() == '') {
                        chk++;
                        ink++;
                        //alert('You Have to give Instrument No');
                        $(".ref_" + did).addClass('erroralert');
                    } else {
                        $(".ref_" + did).removeClass('erroralert');
                    }

                    if ($(".due_" + did).val() == '') {
                        chk++;
                        idk++;
                        //alert('You Have to give Instrument Date');
                        $(".due_" + did).addClass('erroralert');
                    } else {
                        $(".due_" + did).removeClass('erroralert');
                    }
                    $(this).removeClass('erroralert');
                }

                if (val == '5') {

                    if ($(".ref_" + did).val() == '') {
                        chk++;
                        ink++;
                        //alert('You Have to give Instrument No');
                        $(".ref_" + did).addClass('erroralert');
                    } else {
                        $(".ref_" + did).removeClass('erroralert');
                    }

                    if ($(".due_" + did).val() == '') {
                        chk++;
                        idk++;
                        //alert('You Have to give Instrument Date');
                        $(".due_" + did).addClass('erroralert');
                    } else {
                        $(".due_" + did).removeClass('erroralert');
                    }
                    $(this).removeClass('erroralert');
                }
            } else {
                pmode++;
                $(this).addClass('erroralert');
            }
        });
        if (pmode != '') {
            alert('You Have to Select Payment Mode');
            return false;
        }

        if (bnk != '') {
            alert('You Have to Select Bank');
            return false;
        }

        if (ink != '') {
            alert('You Have to give Instrument No');
            return false;
        }

        if (idk != '') {
            alert('You Have to give Instrument Date');
            return false;
        }

        if (chk == 0) {
            $.ajax({
                url: 'ajaxSwitch.php',
                type: 'post',
                data: formData,
                async: true,
                success: function (response) {
                    $("#checkmode").val('multiple');
                    $("#TB_closeWindowButton").click();
                    // $("#lastseatdiv").before(response);
                }
            });
        }


    } else if (total_amount > paying_amount) {
        alert('Amount is not matching')
    } else {
        alert('Your Amount Is More than The Current Invoice Amount')
    }


});
$(document).on('change', '.payment_slt_mode', function () {
    var id = $(this).attr('data-id');
    var txt = $("#py_mode" + id + " option:selected").text();
    txt = txt.toUpperCase();
    if (txt == "CASH") {
        $(".crdit_" + id).hide();
        $(".d_crdit_" + id).show();
        $(".bank_" + id).hide();
        $(".d_bank_" + id).show();
        $('.ref_' + id).prop("readonly", true);
        $('.due_' + id).prop("readonly", true);
    } else {
        $(".d_crdit_" + id).hide();
        $(".crdit_" + id).show();
        $(".d_bank_" + id).hide();
        $(".bank_" + id).show();
        $('.ref_' + id).prop("readonly", false);
        $('.due_' + id).prop("readonly", false);
    }
});
$(document).on('change', '#pupilsightProgramID_MC', function () {
    var id = $(this).val();
    var pid = $('#pupilsightProgramID_MC').val();
    var aid = $("#pupilsightSchoolYearID").val();
    var type = 'getClass_new';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pid: pid, aid: aid },
        async: true,
        success: function (response) {
            $('#pupilsightClassID').empty();
            $('#pupilsightClassID').append(response);
            //$('#pupilsightClassID').multiselect('rebuild');
        }
    });
});

$(document).on('click', '.show_div_marks', function () {
    var id = $(this).attr('data-id');
    $(".t_doby_" + id).slideToggle();
});

$(document).on('change', '#pupilsightDepartmentIDbyPP', function () {
    var id = $(this).val();
    var pid = $('#pupilsightProgramIDbyPP').val();
    var cid = $('#pupilsightYearGroupIDbyPP').val();
    var sid = $('#pupilsightRollGroupIDbyPP').val();
    var type = 'getSkillBySubject';
    $('#testId').selectize()[0].selectize.destroy();
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pid: pid, cid: cid, sid: sid },
        async: true,
        success: function (response) {
            $('#skill_id').empty();
            $('#skill_id').append(response);
            //$('#pupilsightClassID').multiselect('rebuild');
            var ntype = 'getTestBySubject';
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: id, type: ntype, pid: pid, cid: cid, sid: sid },
                async: true,
                success: function (response) {
                    $("#testId").html();
                    $("#testId").html(response);
                    $("#testId").parent().children('.LV_validation_message').remove();
                    $('#testId').selectize({
                        plugins: ['remove_button'],
                    });
                }
            });
        }
    });

});


$(document).on('click', '#saveAttrPlugin', function () {
    var sub = [];
    $.each($(".pluginId:checked"), function () {
        sub.push($(this).val());
    });
    var subid = sub.join(",");
    if (subid != '') {
        $.ajax({
            url: 'modules/Academics/sketch_manage_attribute_pluginProcess.php',
            type: 'post',
            data: $('#sketchPLuginForm').serialize(),
            async: true,
            success: function (response) {
                $("#TB_overlay").remove();
                $("#TB_window").remove();
                alert('Plugin Added Successfully');
                //location.reload();
            }
        });
    } else {
        alert('Please Select Checkbox');
    }
});


$(document).on('click', '#cancelTransaction', function () {
    var favorite = [];
    $.each($("input[name='collection_id[]']:checked"), function () {
        favorite.push($(this).val());
    });
    var stuid = favorite.join(",");
    //alert(subid);
    if (stuid) {
        var val = stuid;
        var type = 'addtransactionidInSession';
        if (val != '') {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val, type: type },
                async: true,
                success: function (response) {
                    $("#cancelTransactionSubmit").click();
                }
            });
        }
    } else {
        alert('You Have to Select Transaction.');
    }
});

$(document).on('change', '#attrcat', function () {
    var id = $(this).val();
    var type = 'getSketchLabel';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type },
        async: true,
        success: function (response) {
            $('#labeldata').empty();
            $('#labeldata').append(response);
            //$('#pupilsightClassID').multiselect('rebuild');
        }
    });
});

$(document).on('keydown', '.numMarksfield', function (event) {
    if (event.shiftKey == true) {
        event.preventDefault();
    }

    if ((event.keyCode >= 48 && event.keyCode <= 57) ||
        (event.keyCode >= 96 && event.keyCode <= 105) ||
        event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 ||
        event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

    } else {
        event.preventDefault();
    }

    if ($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
        event.preventDefault();
});

$(document).on('click', '#alertInvoiceEditData', function () {
    alert("You Can't Edit Invoice because Invoice is already Paid");
    return false;
});

$(document).on('click', '#alertInvoiceDeleteData', function () {
    alert("You Can't Delete Invoice because Invoice is already Paid");
    return false;
});


$(document).on('keyup', '#fine', function () {
    var transcation_amount = parseInt($("#transcation_amount_old").val());
    var amount_paying = parseInt($("#amount_paying_old").val());
    var fine = parseInt($('#fine').val()) || 0;
    var fineold = parseInt($("input[name=fineold]").val());

    var newtamt = transcation_amount - fineold;
    var newamtp = amount_paying - fineold;

    var ftamt = newtamt + fine;
    var famtp = newamtp + fine;

    $("#transcation_amount").val(ftamt);
    $("#amount_paying").val(famtp);

});

$(document).on('keyup', '.smsQuote_att', function () {
    var txt = $(this).val();
    var count = txt.length;
    var dis = txt.replace(/\"/g, "");
    var sms_count = count / 160;
    var sms_count = parseInt(sms_count) + 1;
    $(this).nextAll('span:first').html('Characters : ' + count + " (<i class='fa fa-eye' aria-hidden='true'></i>) : " + sms_count + " SMS Count(s)");
    $(this).nextAll('span:first').attr("title", dis);
});


$(document).on('change', '#getMultiClassByProg', function () {
    var id = $(this).val();
    var type = 'getClass';
    $('#showMultiClassByProg').selectize()[0].selectize.destroy();
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type },
        async: true,
        success: function (response) {
            $("#showMultiClassByProg").html();
            $("#showMultiClassByProg").html(response);
            $("#showMultiClassByProg").parent().children('.LV_validation_message').remove();
            $('#showMultiClassByProg').selectize({
                plugins: ['remove_button'],
            });
        }
    });
});

$(document).on('change', '#showMultiClassByProg', function () {
    var id = $(this).val();
    var pid = $('#getMultiClassByProg').val();
    var type = 'getMultiSection';
    $('#showMultiSecByProgCls').selectize()[0].selectize.destroy();
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pid: pid },
        async: true,
        success: function (response) {
            $("#showMultiSecByProgCls").html();
            $("#showMultiSecByProgCls").html(response);
            $('#showMultiSecByProgCls').selectize({
                plugins: ['remove_button'],
            });
        }
    });
});

$(document).on('change', '.showTemplateName', function () {
    var checked = [];
    $.each($(".showTemplateName:checked"), function () {
        checked.push($(this).attr('data-nme'));
    });
    var tname = checked.join(", ");
    var wid = $(this).attr('data-wid');
    $("#showTemplateName" + wid).html(tname);
});

$(document).on('change', '.changeForm', function () {
    if ($(this).is(':checked')) {
        $("#onlineClick")[0].click();
    } else {
        $("#offlineClick")[0].click();
    }
});

$(document).on('click', '.sendButton_campaign_listNew', function () {
    var submit_ids = [];
    $.each($("input[name='id[]']:checked"), function () {
        submit_ids.push($(this).val());
    });
    var submt_id = submit_ids.join(",");
    if (submt_id) {
        $(".sendButton_campaign_listNew").removeClass('activestate');
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
        alert('You Have to Select User First');
        window.setTimeout(function () {
            $("#large-modal-register_list").removeClass('show');
            $("#chkCounterSession").removeClass('modal-open');
            $(".modal-backdrop").remove();
        }, 10);
    }

});

//$("#sendEmailSms_registerForm").submit(function (e) {
$(document).on('click', '#sendEmailSmsContent', function (e) {

    e.preventDefault();
    var formData = new FormData(document.getElementById("sendEmailSms_registerForm"));
    var emailquote = $("#emailQuote_Register").val();
    var emailSubjct_camp = $("#emailSubjct_Register").val();
    var emailAttachment = $('input[name=email_attach]')[0].files[0];
    var smsquote = $("#smsQuote_Register").val();
    var form_id = $("#form_id").val();
    var camp_id = $("#form_id").attr('data-cid');
    var favorite = [];
    $.each($("input[name='id[]']:checked"), function () {
        favorite.push($(this).val());
    });
    var submit_id = favorite.join(", ");
    //   alert(submit_id + '-' + form_id + '-' + camp_id);
    if (submit_id) {
        if (emailquote != '' || smsquote != '') {
            formData.append('submit_id', submit_id);
            formData.append('emailquote', emailquote);
            formData.append('emailSubjct_camp', emailSubjct_camp);
            formData.append('smsquote', smsquote);
            $("#preloader").show();
            $.ajax({
                url: "modules/Campaign/send_register_users_email_msg.php",
                type: "POST",
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                async: false,
                success: function (data) {
                    alert("Message Sent.");
                    location.reload();
                    console.log(data);
                    $("#preloader").hide();
                }
            });
        } else {
            alert('You Have to Enter Message.');
        }
    } else {
        alert('You Have to Select User.');

    }
});

$(document).on('click', '#addSeats', function () {
    var cid = $(this).attr('data-cid');
    var pid = $('#getMultiClassByProgCamp').val();
    var clid = $("#showMultiClassByProg").val();
    var type = 'getAjaxCampSeats';
    var ncid = parseInt(cid) + 1;
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: ncid, type: type, pid: pid, clid: clid },
        async: true,
        success: function (response) {
            $('#addSeats').attr('data-cid', ncid);
            $("#lastseatdiv").before(response);
        }
    });
    // var ncid = parseInt(cid) + 1;
    // $(this).attr('data-cid', ncid);
    // var design = ' <div id="seatdiv" class=" row mb-1 deltr' + ncid + '"><div class="col-sm  newdes " ><div class=""><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="seatname" name="seatname[' + ncid + ']" class="w-full txtfield"></div></div></div></div><div class="col-sm  newdes" colspan="2"><div class=""><div class="dte mb-1"></div><div class=" txtfield kountseat mb-1"><div class="flex-1 relative" style="display:inline-flex;"><input type="number" id="seatallocation" name="seatallocation[' + ncid + ']" class="w-full txtfield kountseat szewdt"><i style="cursor:pointer;padding: 8px 10px;" class="mdi mdi-close-circle mdi-24px delSeattr" data-id="' + ncid + '"></i></div></div></div></div></div>';
    // $("#lastseatdiv").before(design);

});


$(document).on('change', '#progID', function () {
    var aid = $("#pupilsightSchoolYearID").val();
    var id = $(this).val();
    var type = 'getSchoolClass';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, aid: aid },
        async: true,
        success: function (response) {

            $("#clsID").html();
            $("#clsID").html(response);
        }
    });
});

$(document).on('change', '#clsID', function () {
    var aid = $("#pupilsightSchoolYearID").val();
    var id = $(this).val();
    var pid = $('#progID').val();
    var type = 'getSchoolSection';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pid: pid, aid: aid },
        async: true,
        success: function (response) {
            $("#secID").html();
            $("#secID").html(response);
        }
    });
});

$(document).on('click', '#simplesubmitInvoice', function () {
    var val = $("#simplesearch").val();
    if (val == '') {
        $("#simplesearch").val('');
        $("#search").val('');
        $("#searchForm").submit();
    } else {
        $("#pupilsightProgramID option:selected").prop("selected", false);
        $("#pupilsightYearGroupID option:selected").prop("selected", false);
        $("#pupilsightRollGroupID option:selected").prop("selected", false);
        $("#searchfield option:selected").prop("selected", false);
        $("#search").val('');
        $(".searchType").val('1');
        $("#searchForm").submit();
    }

});


$(document).on('click', '#advancesubmitInvoice', function () {
    $("#simplesearch").val('');
    $(".searchType").val('2');
    $("#searchForm").submit();
});


function addInvoiceFeeAmtNew() {
    var favorite = [];
    var feetotal = 0;
    var desctotal = 0;
    var amtpay = 0;
    var finefeetotal = 0;
    var totalfineamt = 0;
    var invoiceid = 0;
    var tmp = new Array();

    //only for number invoice
    var percentageFlag = false;
    $.each($(".selFeeItem:checked"), function () {

        var flag = false;
        var invid = $(this).attr('data-invid');
        var finetype = $(".invoice" + invid).attr('data-ftype');
        if (finetype == 'num') {
            flag = true;
        } else {
            percentageFlag = true;
        }

        if (flag) {
            console.log(this);
            // feetotal += parseFloat($(this).attr('data-amt')) || 0; 
            var tfeetotal = Number($(this).attr('data-totamt')) || 0;
            feetotal += tfeetotal;
            amtpay += Number($(this).attr('data-amt')) || 0;
            favorite.push($(this).val());
            desctotal += Number($(this).attr('data-dis')) || 0;
            var fineamt = $(".invoice" + invid).attr('data-fper');
            if (invoiceid != invid) {

                totalfineamt += Number(fineamt);
                console.log(fineamt, totalfineamt, tfeetotal);
                invoiceid = invid;
            }
        }
    });

    var feetotal1 = 0;
    var desctotal1 = 0;
    var amtpay1 = 0;
    var finefeetotal1 = 0;
    var totalfineamt1 = 0;
    var invoiceid1 = 0;

    //only for percentage invoice
    if (percentageFlag) {
        //console.log("cehck perc");
        $.each($(".selFeeItem:checked"), function () {
            //console.log(this);
            var flag = false;
            var invid = $(this).attr('data-invid');
            var finetype = $(".invoice" + invid).attr('data-ftype');
            if (finetype != 'num') {
                flag = true;
            }
            if (flag) {
                // feetotal += parseFloat($(this).attr('data-amt')) || 0; 
                var tfeetotal1 = Number($(this).attr('data-totamt')) || 0;
                amtpay1 += Number($(this).attr('data-amt')) || 0;
                favorite.push($(this).val());
                desctotal1 += Number($(this).attr('data-dis')) || 0;
                var fineamt = $(".invoice" + invid).attr('data-fper');
                //if(invoiceid1 != invid){
                //totalfineamt1 += Number(fineamt);
                //var dec = (Number(fineamt) / 100); 
                totalfineamt1 += (tfeetotal1 * Number(fineamt)) / 100;
                feetotal1 += tfeetotal1;
                //console.log(totalfineamt1,fineamt);
                // invoiceid1 = invid;
                // }        
            }
        });
        //console.log(feetotal, totalfineamt);
        feetotal += feetotal1;
        totalfineamt += totalfineamt1;
        desctotal += desctotal1;
    }

    //var totalamount = 0;
    //totalamount += totalfineamt;
    //alert(finefeetotal);
    var invitemids = favorite.join(", ");
    $("#fine").val(totalfineamt);
    $("input[name=fineold]").val(totalfineamt);
    var totalamount = Number(feetotal) + totalfineamt;
    if (desctotal != '') {
        var amtpaying = totalamount - desctotal;
    } else {
        var amtpaying = totalamount;
    }

    $("#total_amount_without_fine_discount").val(feetotal);
    $("#transcation_amount").val(totalamount);
    $("#amount_paying").val(amtpaying);
    //$("#transcation_amount_old").val(totalamount);
    $("#amount_paying_old").val(amtpaying);
    $("#discount").val(desctotal);
    $("input[name=invoice_item_id]").val(invitemids);
    $("input[name=chkamount]").val(amtpaying);
}


$(document).on('click', '#exportExcel', function () {
    var type = 'subjectMarks_excelNew';
    var section = $('#pupilsightRollGroupIDbyPP').val();
    var cls = $('#pupilsightYearGroupIDbyPP').val();
    var program = $('#pupilsightProgramIDbyPP').val();
    var testId = $('#testId').val();
    var sub = $('#pupilsightDepartmentIDbyPP').val();
    var val = '1';
    //alert(section);

    $.ajax({
        url: 'ajaxSwitch.php',
        type: 'post',
        data: { val: val, type: type, program: program, cls: cls, section: section, testId: testId, sub: sub },
        async: true,
        success: function (response) {
            //console.log(response);
            $("#marks_subjectExcel").html(response);
            $("#subexcelexport").table2excel({
                name: "subject Marks",
                filename: "subject_marks.xls",
                fileext: ".xls",
                exclude: ".checkall",
                exclude_inputs: true,
                exclude_links: true

            });
        }
    });
});





$(document).on('change', '#pupilsightProgramIDSchool', function () {
    var id = $(this).val();
    var aid = $("#pupilsightSchoolYearIDSchool").val();
    var type = 'getClassData';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, aid: aid },
        async: true,
        success: function (response) {

            $("#pupilsightYearGroupIDSchool").html('');
            $("#pupilsightRollGroupIDSchool").html('');
            $("#pupilsightYearGroupIDSchool").html(response);
        }
    });
});

$(document).on('change', '#pupilsightYearGroupIDSchool', function () {
    var id = $(this).val();
    var aid = $("#pupilsightSchoolYearIDSchool").val();
    var pid = $('#pupilsightProgramIDSchool').val();
    var type = 'getSectionData';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pid: pid, aid: aid },
        async: true,
        success: function (response) {
            $("#pupilsightRollGroupIDSchool").html('');
            $("#pupilsightRollGroupIDSchool").html(response);
        }
    });
});


$(document).on('click', '#saveImgData', function (e) {
    // $('#sendEmailSms_campaignForm').on('submit', (function (e) {
    e.preventDefault();
    var formData = new FormData(document.getElementById("imgForm"));

    $.ajax({
        url: "modules/Academics/sketch_report_template_configure_imageProcess.php",
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        async: false,
        success: function (data) {
            alert('Parameter Saved Successfully!');
            $("#TB_overlay").remove();
            $("#TB_window").remove();
        }
    });
});

$(document).on('click', '#delImgData', function (e) {
    e.preventDefault();
    var skid = $(this).attr('data-sk');
    var atrid = $(this).attr('data-atr');
    var type = 'deleteImageTemplateConfig';
    $.ajax({
        url: "ajax_data.php",
        type: "POST",
        data: { type: type, val: skid, atrid: atrid },
        async: true,
        success: function (data) {
            alert('Parameter Deleted Successfully!');
            $("#TB_overlay").remove();
            $("#TB_window").remove();
        }
    });
});

$(document).on('click', '#saveCampImgData', function (e) {
    // $('#sendEmailSms_campaignForm').on('submit', (function (e) {
    e.preventDefault();
    var formData = new FormData(document.getElementById("imgForm"));

    $.ajax({
        url: "modules/Campaign/campaign_template_configure_imageProcess.php",
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        async: false,
        success: function (data) {
            alert('Parameter Saved Successfully!');
            $("#TB_overlay").remove();
            $("#TB_window").remove();
        }
    });
});

$(document).on('click', '#delCamImgData', function (e) {
    e.preventDefault();
    var cid = $(this).attr('data-cid');
    var fname = $(this).attr('data-fname');
    var typ = $(this).attr('data-type');
    var type = 'deleteCampaignImageTemplateConfig';
    $.ajax({
        url: "ajax_data.php",
        type: "POST",
        data: { type: type, val: cid, fname: fname, typ: typ },
        async: true,
        success: function (data) {
            alert('Parameter Deleted Successfully!');
            $("#TB_overlay").remove();
            $("#TB_window").remove();
        }
    });
});


$(document).on('click', '#editInvoice', function () {
    var favorite = [];
    $.each($(".chkinvoiceApplicant:checked"), function () {
        favorite.push($(this).val());
    });

    if (favorite.length != 0) {
        var invids = favorite.join(", ");
        var sid = $("input[name=submission_id]").val();
        $.ajax({
            url: 'fullscreen.php?q=/modules/Campaign/edit_invoice_collection_form.php&inv_id=' + invids + '&sid=' + sid,
            type: "GET",
            data: null,
            async: true,
            success: function (data) {
                $("#invoiceEdit").html(data);
            }
        });
    } else {
        alert('Please select atleast one invoice');
    }
});


$(document).on('click', '#updateAdmissionInvoiceStnButton', function () {
    var url = $('#edit_invoice_save_form').attr('action');
    var py = $(".pSyd").val();
    var pstid = $(".p_stuId").val();
    var formData = "pstid=" + pstid + "&" + "yid=" + py + "&" + $("#edit_invoice_save_form").serialize();
    var err = 0;
    var title = $("#title").val();
    if (title.trim() != "") {
        $("#title").removeClass('LV_invalid_field');
    } else {
        err++;
        $("#title").addClass('LV_invalid_field');
    }


    if ($("#fnFeesHeadId").val() == '') {
        err++;
        $("#fnFeesHeadId").addClass('erroralert');
    } else {
        $("#fnFeesHeadId").removeClass('erroralert');
    }

    if ($("#inv_fn_fee_series_id").val() == '') {
        err++;
        $("#inv_fn_fee_series_id").addClass('erroralert');
    } else {
        $("#inv_fn_fee_series_id").removeClass('erroralert');
    }

    if ($("#rec_fn_fee_series_id").val() == '') {
        err++;
        $("#rec_fn_fee_series_id").addClass('erroralert');
    } else {
        $("#rec_fn_fee_series_id").removeClass('erroralert');
    }

    if (err == 0) {
        var val = $("#fn_fees_fine_rule_id").val();
        if (val != '') {
            var ddate = $("#due_date").val();
            if (ddate == '') {
                $("#due_date").addClass('erroralert');
                alert('You have to Add Due Date');
                return false;
            } else {
                $("#due_date").removeClass('erroralert');
                $.ajax({
                    url: url,
                    type: 'post',
                    data: formData,
                    async: true,
                    success: function (response) {
                        if (response == "success") {
                            alert("Invoice updated successfully");
                            $("#invoiceEdit").html('');
                            $("#TB_closeWindowButton").click();
                            window.setTimeout(function () {
                                $("#admissionFeePayment").trigger('click');
                            }, 500);

                        } else {
                            alert(response);
                        }
                    }
                });
            }
        } else {
            $.ajax({
                url: url,
                type: 'post',
                data: formData,
                async: true,
                success: function (response) {
                    if (response == "success") {
                        alert("Invoice updated successfully");
                        $("#invoiceEdit").html('');
                        $("#TB_closeWindowButton").click();
                        window.setTimeout(function () {
                            $("#admissionFeePayment").trigger('click');
                        }, 500);
                    } else {
                        alert(response);
                    }
                }
            });
        }

    }
});

$(document).ready(function () {
    $('#checkall').parent().parent().parent().addClass('no-sort');
});

$(document).on('change', '#pupilsightProgramIDbyPPbyMarks', function () {
    var id = $(this).val();
    var type = 'getClassForAcademic';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type },
        async: true,
        success: function (response) {
            $("#pupilsightYearGroupIDbyPPbyMarks").html();
            $("#pupilsightYearGroupIDbyPPbyMarks").html(response);
        }
    });
});

$(document).on('change', '#pupilsightYearGroupIDbyPPbyMarks', function () {
    var id = $(this).val();
    var pid = $('#pupilsightProgramIDbyPPbyMarks').val();
    var type = 'getSectionForAcademic';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pid: pid },
        async: true,
        success: function (response) {
            $("#pupilsightRollGroupIDbyPPbyMarks").html();
            $("#pupilsightRollGroupIDbyPPbyMarks").html(response);
        }
    });
});

$(document).on('change', '#pupilsightDepartmentIDbyPPbyMarks', function () {
    var id = $(this).val();
    var pid = $('#pupilsightProgramIDbyPPbyMarks').val();
    var cid = $('#pupilsightYearGroupIDbyPPbyMarks').val();
    var sid = $('#pupilsightRollGroupIDbyPPbyMarks').val();
    var type = 'getSkillBySubject';
    $('#testId').selectize()[0].selectize.destroy();
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pid: pid, cid: cid, sid: sid },
        async: true,
        success: function (response) {
            $('#skill_id').empty();
            $('#skill_id').append(response);
            //$('#pupilsightClassID').multiselect('rebuild');
            var ntype = 'getTestBySubject';
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: id, type: ntype, pid: pid, cid: cid, sid: sid },
                async: true,
                success: function (response) {
                    $("#testId").html();
                    $("#testId").html(response);
                    $("#testId").parent().children('.LV_validation_message').remove();
                    $('#testId').selectize({
                        plugins: ['remove_button'],
                    });
                }
            });
        }
    });

});

$(document).on('change', '#skill_id', function () {
    var id = $(this).val();
    if (id != 0) {
        var subid = $("#pupilsightDepartmentIDbyPPbyMarks").val();
        var pid = $('#pupilsightProgramIDbyPPbyMarks').val();
        var cid = $('#pupilsightYearGroupIDbyPPbyMarks').val();
        var sid = $('#pupilsightRollGroupIDbyPPbyMarks').val();
        $('#testId').selectize()[0].selectize.destroy();
        var ntype = 'getTestBySubjectSkill';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: ntype, pid: pid, cid: cid, sid: sid, subid: subid },
            async: true,
            success: function (response) {
                $("#testId").html();
                $("#testId").html(response);
                $("#testId").parent().children('.LV_validation_message').remove();
                $('#testId').selectize({
                    plugins: ['remove_button'],
                });
            }
        });
    } else {
        $("#pupilsightDepartmentIDbyPPbyMarks").trigger('change');
    }
});