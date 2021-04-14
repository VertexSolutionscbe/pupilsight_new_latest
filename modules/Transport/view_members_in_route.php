<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/view_members_in_route.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {


    if($_POST){
        
        $Route =  $_POST['Route'];
        $Bus = $_POST['Bus'];

        //echo $Route.'-'.$Bus;exit;
            
        
    } else {
        $Route =  '';
        $Bus = '';
        
    }


    //populate Bus   
    $sqlr = 'SELECT id, name FROM trans_bus_details ';
    $resultr = $connection2->query($sqlr);
    $bus_name = $resultr->fetchAll();
    $bus_id = array();
    $bus_name1 = array('' => 'Select bus name');
    $bus_name2 = array();

    foreach ($bus_name as $dt) {
        $bus_name2[$dt['id']] = $dt['name'];
    }


    $bus_id = $bus_name1 + $bus_name2;

    //Populate Route

    

    $sqlr = 'SELECT route_name, id FROM  trans_routes ';
    $resultr = $connection2->query($sqlr);
    $onwardroute_l = $resultr->fetchAll();
    $onwardroute_list = array();
    $onwardroute_list1 = array('' => 'Select Route');
    $onwardroute_list2 = array();
    foreach ($onwardroute_l as $dt) {
        $onwardroute_list2[$dt['id']] = $dt['route_name'];
    }
    $onwardroute_list = $onwardroute_list1 + $onwardroute_list2;




    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Route', __('Route Name'));
    $col->addSelect('Route')->fromArray($onwardroute_list)->selected($Route); 
    
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Bus', __('Bus Name'));
    $col->addSelect('Bus')->fromArray($bus_id)->selected($Bus); 

    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    
    $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>&nbsp;<a href="index.php?q=/modules/Transport/view_members_in_route.php" class="sendbtn btn btn-primary">Clear</a>');  
    echo $searchform->getOutput();


    //Proceed!
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $page->breadcrumbs->add(__('Assign route'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    echo "<div style='height:50px;'><div class='float-left mb-2'><a  id=''  data-toggle='modal' data-target='#large-modal-new' data-noti='2'  class='sendbtn btn btn-primary'>Send SMS</a>&nbsp;&nbsp;";
    echo "<a  id='' data-toggle='modal' data-noti='1' data-target='#large-modal-new' class='sendbtn btn btn-primary'>Send Email</a></div><div class='float-none'></div></div>";

    $sql = 'SELECT id,route_name FROM trans_routes ';
    $result = $connection2->query($sql);
    // $routename = $resultr->fetchAll();
    $route_name = $result->fetchAll();
    $routes = array();
    foreach ($route_name as $prg) {
        $id = 'type:' . $prg['id'];
        $routes[$id] = $prg['route_name'];
    }
    $TransportGateway = $container->get(TransportGateway::class);
    $criteria = $TransportGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->fromPOST();
    $viewMember = $TransportGateway->getViewMember($criteria, $pupilsightSchoolYearID,$Bus,$Route);
    

    $table = DataTable::createPaginated('FeeStructureManage', $criteria);

    $table->addMetaData('filterOptions', $routes);
    $table->addCheckboxColumn('stuid', __(''))
        ->setClass('chkbox')
        ->notSortable();
    $table->addColumn('route_name', __('Route Name'));

    $table->addColumn('bus_name', __('Bus name'));
    $table->addColumn('student_name', __('Name'));
    $table->addColumn('category', __('Category'));
    $table->addColumn('class', __('Class'));
    $table->addColumn('section', __('Section'));
    //$table->addColumn('onward_route', __('onward Route'));
    $table->addColumn('onward_stop_name', __('Onward stop'));
    //$table->addColumn('return_name', __('return Route'));
    $table->addColumn('return_stop_name', __('Return Stop'));
    $table->addColumn('route_type', __('Type'));
    $table->addColumn('academic_year', __('Academic Year'));


    echo $table->render($viewMember);
}

?>

<script>
    $(document).on('click', '.sendbtn', function () {
        var stuids = [];
        $.each($("input[name='stuid[]']:checked"), function () {
            stuids.push($(this).val());
        });
        var stuid = stuids.join(",");
        if (stuid) {
            $(".sendbtn").removeClass('activestate');
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
                $("#large-modal-new").removeClass('show');
                $("#chkCounterSession").removeClass('modal-open');
                $(".modal-backdrop").remove();
            }, 10);
        }

    });


    $(document).on('click', '#sendEmailSms_Transport', function (e) {
        e.preventDefault();
        $("#preloader").show();
        window.setTimeout(function () {
            var formData = new FormData(document.getElementById("sendEmailSms_Transport"));

            var emailquote = $("#emailQuote_stud_Transport").val();
            var subjectquote = $("#emailSubjectQuote_stud_Transport").val();

            var smsquote = $("#smsQuote_stud_Transport").val();
            var favorite = [];
            $.each($("input[name='stuid[]']:checked"), function () {
                favorite.push($(this).val());
            });
            var stuid = favorite.join(", ");

           

            if (stuid) {
                
                    if (emailquote != '' || smsquote != '') {

                        formData.append('stuid', stuid);
                        formData.append('emailquote', emailquote);
                        formData.append('smsquote', smsquote);
                        formData.append('subjectquote', subjectquote);
                        $.ajax({
                            url: 'modules/Transport/send_stud_email_msg.php',
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
                                $(".closeSMPopUp").click();
                                
                            }
                        });
                    } else {
                        $("#preloader").hide();
                        alert('You Have to Enter Message.');
                    }
                
            } else {
                $("#preloader").hide();
                alert('You Have to Select Applicants.');

            }
        }, 100);


    });
</script>