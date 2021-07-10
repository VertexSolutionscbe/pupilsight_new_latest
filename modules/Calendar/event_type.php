<?php

use Pupilsight\Domain\Calendar\CalendarGateway;
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
    

    if (isset($_POST['title'])) {

        $title = "'" . trim($_POST['title']) . "'";
        $description = empty($_POST['description']) ? "NULL" : "'" . trim($_POST['description']) . "'";
        $color = empty($_POST['color']) ? "NULL" : "'" . trim($_POST['color']) . "'";
        
        $ts = date('Y-m-d H:i:s');
        //print_r($_POST);
        try {
            if(!empty($_POST["id"])){
                //update
                $id = $_POST["id"];
                $sq = "update calendar_event_type set title=$title, ";
                $sq .= " description=$description, ";
                $sq .= " color=$color ";
                $sq .= "where id='".$id."' ";
            }else{
                $sq = "insert into calendar_event_type (title, description, color, cdt) 
                values($title,$description,$color,'$ts')";
            }
            //echo $sq;
            //die();
            $connection2->query($sq);
            
            $res["status"]=1;
            $res["msg"]="Event type saved successfully.";
            $_SESSION["notify"] = $res;

            header('Location: '.$_SERVER['REQUEST_URI']);
        } catch (Exception $ex) {
            $res["status"]=2;
            $res["msg"]=addslashes($ex->getMessage());
            $_SESSION["notify"] = $res;
        }

        //die();
    }
    $page->breadcrumbs->add(__('Manage Event Type'));

?>
    <script>
        $(document).ready(function() {
            <?php
                if(isset($_SESSION["notify"])){
                    if($_SESSION["notify"]["status"]==1){
                        echo "toast('success','".$_SESSION["notify"]["msg"]."');";
                    }else{
                        echo "toast('error',\"".$_SESSION["notify"]["msg"]."\");";
                    }
                }
            ?>

        });
    </script>
    <div class="card my-2" id='addEventType'>
        <div class="card-header">
            <h2>Add / Modify Event Type</h2>
        </div>
        <div class="card-body">
            <form id="eventTypeForm" action="" class="needs-validation" novalidate="" method="post" autocomplete="off">
                <div class="row">
                    <div class="col-md-10 mt-2">
                        <label class="form-label required">Event Title</label>
                        <input type="hidden" id="id" name="id" value="">
                        <input type="text" id="title" name="title" class="form-control" value="" required>
                    </div>
                    <div class="col-md-2 mt-2">
                        <label class="form-label">Event Color</label>
                        <input type="color" id="color" name="color" class="form-control form-control-color" value="">
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label">Event Description</label>
                        <textarea id="description" name="description" class="form-control" rows='6'></textarea>
                    </div>
                    
                </div>
            </form>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-12 my-3">
                    <button type="button" class="btn btn-primary" onclick="validate();">Save</button>
                    <button type="button" class="btn btn-secondary ml-1" onclick="cancelEventType();">Cancel</button>
                </div>
            </div>
            
        </div>

    </div>
    <!----EventType Details---->
    <div class="card my-2" id='eventTypeList'>
        <div class="card-header">
        <?php
            $calGateway = $container->get(CalendarGateway::class);
            $res = $calGateway->listEventType($connection2);
            if($roleid=="001"){
                echo '<button type="button" class="btn btn-primary" onclick="addEventType();"><i class="mdi mdi-plus-thick mr-1"></i>Event Type</button>';
            }
        ?>
        
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id='eventListTable' class="table card-table table-vcenter text-nowrap datatable border-bottom">
                    <thead>
                        <tr>
                            <th>Event Name</th>
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
                            $str .="\n<td><strong>".ucwords($res[$i]["title"])."</strong><br><span class='text-muted'>".$res[$i]["description"]."</span></td>";
                            
                            if($roleid=="001"){
                                $str .="\n<td><button type='button' class='btn btn-link' onclick=\"editEventType('".$res[$i]['id']."');\"><i class='mdi mdi-edit mr-2'></i>Edit</button></td>";
                            }
                            $str .="\n</tr>";
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

    
    <?php
    if($roleid=="001"){
    ?>
    <script>
        function editEventType(id){
            var obj = EventType[id];
            var elements = ["id","title","description","color"];
            var len = elements.length;
            var i = 0;
            while(i<len){
                setVal(elements[i],obj);
                i++;
            }
            $("#addEventType").show(400);
            $("#eventTypeList").hide(400);
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
        var EventType = <?php echo json_encode($repo); ?>;
        var isParamActive = false;
        var activeDownloadId = "";

        function isEmpty(str) {
            return (!str || str.length === 0 );
        }
    </script>
    <script>
        $(document).ready(function() {
            $("#addEventType").hide();
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

        function resetAddEventType(){
            var elements = ["id","title","description","color"];
            var len = elements.length;
            var i = 0;
            while(i<len){
                $("#"+elements[i]).val("");
                i++;
            }
        }

        function addEventType() {
            resetAddEventType();
            $("#addEventType").show(400);
            $("#eventTypeList").hide(400);
        }

        function cancelEventType() {
            resetAddEventType();
            $("#addEventType").hide(400);
            $("#eventTypeList").show(400);
        }

        function validate() {
            var flag = validElement("title", "Enter Event Title");
            if (!flag) {
                return;
            }
            $("form#eventTypeForm").submit();
        }

        function validElement(id, msg) {
            var element = $("#" + id).val();
            if (element == "") {
                toast("error",msg);
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
if(isset($_SESSION["notify"])){
    if($_SESSION["notify_exec"]=="1"){
        unset($_SESSION["notify"],$_SESSION["notify_exec"]);
    }else{
        $_SESSION["notify_exec"] = "1";
    }
}
//
}
