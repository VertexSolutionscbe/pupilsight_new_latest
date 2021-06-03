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
            }catch(Exception $ex){
                echo $ex->getMessage();
            }
        ?>
        

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a href="#archiveList" class="nav-link active">Archive List</a>
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
            autosize($('#sql_query'));
        });
    </script>
<?php
}
