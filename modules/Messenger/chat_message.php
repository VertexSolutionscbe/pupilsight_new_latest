<style>
  .text-truncate {
    height: 26px;
  }

  select[multiple] {
    min-height: auto !important;
  }
</style>
<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Helper\HelperGateway;
use Pupilsight\Domain\Messenger\ChatGateway;

?>

<?php
require_once __DIR__ . '/moduleFunctions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page->breadcrumbs->add(__('Chat Message'));
$accessFlag = true;
$uid = $_SESSION[$guid]['pupilsightPersonID'];
$pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

/*
if (isActionAccessible(
        $guid,
        $connection2,
        "/modules/Messenger/messenger_post.php"
    ) == false
) {
    //Acess denied
    print "<div class='alert alert-danger'>";
    print __("You do not have access to this action.");
    print "</div>";
}*/

if ($accessFlag) {

  $isPostActive = true;
  $helperGateway = $container->get(HelperGateway::class);
  $chatGateway = $container->get(ChatGateway::class);
  $roleid = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
  $chat_tabs = $chatGateway->getRoleTabs($connection2, $roleid);
  //print_r($chat_tabs);

  $isPostAllow = true;
  $isStParent = false; // student and parent post
  if ($roleid == '003') {
    $isPostAllow = false;
    $isStParent = true;
  } elseif ($roleid == '004') {
    $isPostAllow = false;
    $isStParent = true;
    $isPostActive = false;
  }

  if (!$isPostActive) {

    //student list for parents
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];

    $sqlf = 'SELECT pupilsightFamilyID FROM pupilsightFamilyAdult WHERE pupilsightPersonID= ' . $cuid . ' ';
    $resultf = $connection2->query($sqlf);
    $fdata = $resultf->fetch();
    $pupilsightFamilyID = $fdata['pupilsightFamilyID'];

    if (!empty($_GET['cid'])) {
      $chkchilds = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a 
        LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID 
        WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID = ' . $_GET['cid'] . ' ';
      $resultachk = $connection2->query($chkchilds);
      $chkstuData = $resultachk->fetch();

      if (!empty($chkstuData)) {
        $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a 
          LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID 
          WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" ';
        $resulta = $connection2->query($childs);
        $stuData = $resulta->fetchAll();
        $students = $chkstuData;
        $stuId = $_GET['cid'];
      } else {
        echo '<h1>No Child</h1>';
      }
    } else {
      $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a 
        LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID 
        WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" ';
      $resulta = $connection2->query($childs);
      $stuData = $resulta->fetchAll();
      $students = $stuData[0];
      $stuId = $students['pupilsightPersonID'];
    }
    $_SESSION['student_id'] = $stuId;

    $tab = '';
    if (!empty($stuData) && count($stuData) > 1) {
      $tab = '<div style="display:inline-flex;width:25%" class="mb-2">
      <span style="width:25%">Child : </span>
      <select id="childSel" class="form-control" style="width:100%">';
      foreach ($stuData as $stu) {
        $selected = '';
        if (!empty($_GET['cid'])) {
          if ($_GET['cid'] == $stu['pupilsightPersonID']) {
            $selected = 'selected';
          }
        }
        $tab .= '<option value=' . $stu['pupilsightPersonID'] . '  ' . $selected . '>' . $stu['officialName'] . '</option>';
      }
      $tab .= '</select></div>';
    }
    echo $tab;
?>
    <script>
      $(document).on('change', '#childSel', function() {
        var id = $(this).val();
        var hrf = 'index.php?q=/modules/Messenger/chat_message.php&cid=' + id;
        window.location.href = hrf;
      });
    </script>
  <?php
  }
  if ($isStParent) {

    if ($roleid == "004") {
      $uid = $_SESSION['student_id'];
    }

    $stSubList = $helperGateway->getClassTeacher($connection2, $pupilsightSchoolYearID, $uid);
    $groupList = $helperGateway->getGroupList($connection2, $pupilsightSchoolYearID);
  }
  if ($isPostAllow) { ?>

    <!---Chat Post Widget---->
    <div class="card" id='chatPostWidget'>
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 my-2">
            <?php
            if ($chat_tabs) {
              $len = count($chat_tabs);
              $i = 0;
              $str = "";
              $strcheck = "checked";

              while ($i < $len) {
                $tabName = ucwords($chat_tabs[$i]["name"]);
                $tabID = $chat_tabs[$i]["id"];
                $str .= '<label class="form-check form-check-inline">';
                $str .= '<input class="form-check-input" type="radio" name="rdtabid" value="' . $tabID . '|$|' . $chat_tabs[$i]["name"] . '" ' . $strcheck . '>';
                $str .= '<span class="form-check-label">' . $tabName . '</span></label>';
                $i++;
                $strcheck = "";
              }
              echo $str;
            }
            ?>
          </div>
          <div class='hr-text'>Select Groups or Users</div>
        </div>
        <?php
        $postFunctionStr = "postMessage();";
        if ($roleid == "002" || $roleid == "034" || $roleid == "035") {
          //teacher post widget data
          $postFunctionStr = "postClassTeacherMessage();";
          $staffid = "";
          if (isset($_SESSION["staffid"])) {
            $staffid = $_SESSION["staffid"];
          } else {
            $staffid = $helperGateway->getStaffID($connection2, $uid);
            $_SESSION["staffid"] = $staffid;
          }
          $sct = $helperGateway->getTeacherClassAndSection($connection2, $pupilsightSchoolYearID, $staffid);

        ?>
          <div class="row">
            <div class="col-md-3 col-sm-12">
              <label>Select Class Group</label>
              <select id='teacherSelect' onchange="loadCtStudentList();">
                <?php
                $len = count($sct);
                $i = 0;
                while ($i < $len) {
                  $sc = $sct[$i];
                  $label = $sc["class_name"] . " - " . $sc["section_name"] . " - " . $sc["subject_name"];
                  //$mixid = $sc["clsid"] . "|" . $sc["secid"] . "|" . $sc["subid"];
                  $mixid = $sc["pupilsightSchoolYearID"] . "-" . $sc["pupilsightProgramID"] . "-" . $sc["clsid"] . "-" . $sc["secid"];
                  $tagmixid = $sc["pupilsightSchoolYearID"] . "-" . $sc["pupilsightProgramID"] . "-" . $sc["clsid"] . "-" . $sc["secid"] . "-" . $sc["subid"];
                  echo "\n<option value='" . $mixid . "' tag='" . $label . "' tagid='" . $tagmixid . "' classid='" . $sc["clsid"] . "' sectid='" . $sc["secid"] . "' subid='" . $sc["subid"] . "'>" . $label . "</option>";
                  $i++;
                }
                ?>
              </select>
            </div>
            <div class="col-md-9 col-sm-12">
              <label>Select Individual Student</label>
              <select id='ctStudentList' name='people[]' class='form-control' multiple></select>
              <script>
                var pupilsightSchoolYearID = "<?= $pupilsightSchoolYearID; ?>";
                var pupilsightSchoolYearID = "<?= $pupilsightSchoolYearID; ?>";

                function loadCtStudentList() {
                  var classid = $("#teacherSelect").find(':selected').attr('classid');
                  var sectid = $("#teacherSelect").find(':selected').attr('sectid');
                  if (classid && sectid) {
                    $.ajax({
                      url: 'ajax_chat.php',
                      type: 'post',
                      data: {
                        type: "class_section_student_list",
                        classid: classid,
                        sectid: sectid,
                        pupilsightSchoolYearID: pupilsightSchoolYearID
                      },
                      async: true,
                      success: function(response) {
                        if (response) {
                          $('#ctStudentList').selectize()[0].selectize.destroy();
                          $('#ctStudentList').html(response);
                          $('#ctStudentList').selectize({
                            plugins: ['remove_button'],
                          });
                        }
                      }
                    });
                  }
                }
                $(function() {
                  loadCtStudentList();
                });
              </script>
            </div>
          </div>
        <?php
        } else {
          $pro = $helperGateway->getProgram($connection2);

        ?>
          <div class="row">
            <div class='col-md-2 col-sm-12'>
              <div class="form-label">Bulk or Individual Type</div>
              <select id='delivery_type' name='delivery_type' class='form-control' onchange="changeDeliveryType();">
                <option value=''>Select</option>
                <option value='individual'>Individual</option>
                <option value='all'>All</option>
                <option value='all_students'>All Students</option>
                <option value='all_parents'>All Parents</option>
                <option value='all_staff'>All Staff</option>
              </select>
            </div>
            <div class="col-md-10 col-sm-12" id='individualList'>
              <div class="row">
                <div class='col-md-3 col-sm-12'>
                  <div class="form-label">User Type</div>
                  <select id='userType' name='userType' class='form-control' onchange="changeUserType();">
                    <option value=''>Select</option>
                    <option value='all'>All</option>
                    <option value='003'>Students</option>
                    <option value='004'>Parent</option>
                    <option value='staff'>Staff</option>
                  </select>
                </div>
                <div class='col-md-9 col-sm-12'>
                  <div class="form-label">Select User</div>
                  <select id='studentList' name='people[]' class='form-control' multiple></select>
                </div>
              </div>
            </div>
          </div>
          <div class="row mt-3">
            <div class='col-md-2 col-sm-12'>
              <label>Select Program</label>
              <select id='programSelect' class='form-control' onchange="changeProgram();">
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
            <div class='col-md-2 col-sm-12'>
              <label>Select Class</label>
              <select id='classSelect' class='form-control' onchange="changeClass();"></select>
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
            <div class='col-md-2 col-sm-12'>
              <label>Select Section</label>
              <select id='sectionSelect' class='form-control' onchange="changeSection();">

              </select>
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
                      //console.log(response);
                      $('#studentSelect').selectize()[0].selectize.destroy();
                      $("#studentSelect").html(response);
                      $('#studentSelect').selectize({
                        plugins: ['remove_button'],
                      });
                    }
                  });

                }
              </script>
            </div>
            <div class='col-md-6 col-sm-12'>
              <label>Select Student</label>
              <select id='studentSelect' name="people[]" class='form-control' multiple></select>
            </div>

          </div>
        <?php
        }
        ?>

        <div class="row">
          <div class="col-12 my-3">
            <textarea class="form-control" id="chat_message" name="chat_message" rows="6" placeholder="Write Message Here"></textarea>
          </div>

          <div class="col-12 my-1">
            <div class="form-label">Attachment</div>
            <form enctype="multipart/form-data" id="post_form">
              <input type="file" id='post_attachment' name="attachment" class='form-control'>
            </form>
          </div>
        </div>

        <div class="col-12 my-2">

          <div class="form-label">Message Type</div>
          <label class="form-check form-check-inline">
            <input class="form-check-input" id="msg_type1" name='msg_type' type="radio" checked value="1">
            <span class="form-check-label">One Way</span>
          </label>

          <label class="form-check form-check-inline">
            <input class="form-check-input" id="msg_type2" name="msg_type" type="radio" value="2">
            <span class="form-check-label">Two Way</span>
          </label>
        </div>



        <div class="col-12 mt-4">
          <button type="button" class="btn btn-primary" id='postBtn' onclick="<?= $postFunctionStr; ?>">Submit</button>
          <button type="button" class="btn btn-secondary ml-2" onclick="closeChatBox();">Cancel</button>
        </div>
      </div>
    </div>

  <?php } elseif ($isStParent) { ?>
    <div class="card" id='chatStPostWidget'>
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 my-2">
            <?php
            if ($chat_tabs) {
              $len = count($chat_tabs);
              $i = 0;
              $str = "";
              $strcheck = "checked";

              while ($i < $len) {
                $tabName = ucwords($chat_tabs[$i]["name"]);
                $tabID = $chat_tabs[$i]["id"];
                $str .= '<label class="form-check form-check-inline">';
                $str .= '<input class="form-check-input" type="radio" name="rdtabid" value="' . $tabID . '|$|' . $chat_tabs[$i]["name"] . '" ' . $strcheck . '>';
                $str .= '<span class="form-check-label">' . $tabName . '</span></label>';
                $i++;
                $strcheck = "";
              }
              echo $str;
            }
            ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4 col-sm-12">
            <select id='stGroup' onchange="stGroupChange()">
              <option value="">Select Type</option>
              <?php
              if ($stSubList['pupilsightPersonID']) {
                echo "<option value='" .
                  $stSubList['pupilsightPersonID'] .
                  "' groupid='' groupname='Class Teacher'>Class Teacher</option>";
              }
              echo "<option value='subject_teacher' groupid='' groupname='Subject Teacher'>Subject Teacher(s)</option>";

              if ($groupList) {
                $len = count($groupList);
                $i = 0;
                while ($i < $len) {
                  echo "<option value='" . $groupList[$i]['uid'] . "' groupid='" . $groupList[$i]['groupid'] . "' groupname='" . $groupList[$i]['name'] . "'>" . $groupList[$i]['name'] . '</option>';
                  $i++;
                }
              }
              ?>
            </select>
          </div>

          <div class="col-md-4 col-sm-12">
            <select id='stSubject'>
              <?php
              $sublist = $stSubList['sublist'];
              $len = count($sublist);
              $i = 0;
              while ($i < $len) {
                echo "<option value='" .
                  $sublist[$i]['pupilsightPersonID'] .
                  "'>" .
                  $sublist[$i]['subject_display_name'] .
                  '</option>';
                $i++;
              }
              ?>
            </select>
          </div>

          <div class="col-12 my-3">
            <textarea class="form-control" id="st_chat_message" name="chat_message" rows="6" placeholder="Write Message Here"></textarea>
          </div>

          <div class="col-12 my-1">
            <div class="form-label">Attachment</div>
            <form enctype="multipart/form-data" id="st_post_form">
              <input type="file" id='st_post_attachment' name="attachment" class='form-control'>
            </form>
          </div>

          <div class="col-12 mt-4">
            <button type="button" class="btn btn-primary" id='postStBtn' onclick="postStMessage();">Submit</button>
            <button type="button" class="btn btn-secondary ml-2" onclick="closeStChatBox();">Cancel</button>
          </div>
        </div>

      </div>
    </div>
  <?php }
  ?>
  </div>

  <!--Chat Area Details--->
  <div class="">

    <div class=''>
      <div class='container'>
        <div class='row'>
          <div class='col-auto ml-auto'>
            <?php if ($isPostAllow) {
              echo "<button class='btn btn-primary' onclick='openChatBox();'>New Post</button>";
            } elseif ($isStParent) {
              echo "<button class='btn btn-primary' onclick='openStChatBox();'>New Post</button>";
            } ?>
          </div>
        </div>
      </div>
    </div>

    <!--Card Message Check-->
    <ul class="nav nav-tabs">
      <?php
      if ($chat_tabs) {
        $len = count($chat_tabs);
        $i = 0;
        $strActive = "active";
        $str = "";
        $strTab = "";
        $strTabActive = "active show";
        while ($i < $len) {
          $tabName = ucwords($chat_tabs[$i]["name"]);
          $tabID = $chat_tabs[$i]["id"];
          $str .= '<li class="nav-item"><a href="#cw_' . $tabID . '" tab-id="' . $tabID . '" tab-name="' . $tabName . '" class="nav-link ' . $strActive . '">' . $tabName . '</a></li>';
          $strTab .= '<div class="tab-pane fade ' . $strTabActive . '" id="cw_' . $tabID . '"><div class="cardMessage px-4 py-2" id="mw_' . $tabID . '"></div></div>';
          $i++;
          $strTabActive = "";
          $strActive = "";
        }
        echo $str;
      }
      ?>
    </ul>
    <div class="tab-content" id='myTabContent'>
      <?= $strTab; ?>
    </div>

  </div>
  <!--Reply Dialog-->
  <div class="modal fade" id="replyDialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reportDialogTitle">Reply Message</h5>
          <button id='btnReplyDiaCancel' type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          <div class="row">
            <div class="col-12 my-3">
              <textarea class="form-control" id="reply_message" name="chat_message" rows="6" placeholder="Write Message Here"></textarea>
              <input type='hidden' id='chat_parent_id' value="">
              <input type='hidden' id='post_cuid' value="">
              <input type='hidden' id='reply_delivery_type' value="">
            </div>
            <div class="col-12 mb-3">
              <div class="form-label">Attachment</div>
              <form enctype="multipart/form-data" id="reply_form">
                <input type="file" id='reply_attachment' name="attachment" class='form-control'>
              </form>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <div class="col-12">
            <button type="button" class="btn btn-primary" id='replyBtn' onclick="replyMessage();">Submit</button>
            <button type="button" class="btn btn-secondary ml-2" class="close" data-dismiss="modal" aria-label="Close" onclick="closeReplyBox();">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
  <button type="button" id='btnReplyDia' data-toggle="modal" data-target="#replyDialog"></button>

  <div id='dataHandler' style='line-height:18px;'></div>
  <script>
    var tabid = "<?= $chat_tabs[0]["id"]; ?>";
    var tabname = "<?= $chat_tabs[0]["name"]; ?>";

    $(function() {
      $(".nav-tabs a").click(function(e) {
        e.preventDefault();
        $(this).tab('show');
        tabid = $(this).attr('tab-id');
        tabname = $(this).attr('tab-name');
        if (chatMsgTab[tabid] === undefined) {
          loadMessage();
        }
      });
    });

    var interval;
    $(function() {
      loadPeople('all');
      loadMessage();
      interval = setInterval(() => {
        loadMessage();
      }, 10000);
      //#chatReplyWidget, 
      $("#chatPostWidget, #stSubject, #chatStPostWidget, #dataHandler, #btnReplyDia").hide();
    });
  </script>
  <script>
    function openStChatBox() {
      $("#chatStPostWidget").show(400);
      $("#st_chat_message").val("");
      $("#st_post_attachment").val("");
    }

    function closeStChatBox() {
      $("#chatStPostWidget").hide(400);
      $("#st_chat_message").val("");
      $("#st_post_attachment").val("");
    }

    function stGroupChange() {
      var stgroup = $("#stGroup").val();
      if (stgroup == "subject_teacher") {
        $("#stSubject").show(400);
      } else {
        $("#stSubject").hide(400);
      }
    }

    function postStMessage() {
      var msg = $("#st_chat_message").val();
      if (msg == "") {
        toast("error", "Please enter your message");
        return;
      }

      var stGroup = $("#stGroup").val();
      if (stGroup == "") {
        toast("error", "Please select group type or class teacher");
        return;
      }

      var people = stGroup;
      if (stGroup == "subject_teacher") {
        people = $("#stSubject").val();
      }

      var delivery_type = "individual";

      var groupid = $("#stGroup").find(':selected').attr('groupid');
      var groupName = $("#stGroup").find(':selected').attr('groupname');

      var tabs = $('input[name="rdtabid"]:checked').val();
      var sttab = tabs.split("|$|");
      var tabid = sttab[0];
      var tab = sttab[1];


      var data = new FormData(document.getElementById("st_post_form"));
      data.append("type", "postMessage");
      data.append("msg_type", "2");
      data.append("people", people);
      data.append("group_id", groupid);
      data.append("group_name", groupName);
      data.append("tabid", tabid);
      data.append("tab", tab);
      data.append("delivery_type", delivery_type);
      data.append("msg", msg);
      //console.log(data);

      $("#postStBtn").prop('disabled', true);
      $.ajax({
        url: 'ajax_chat.php',
        type: 'post',
        contentType: false,
        cache: false,
        processData: false,
        async: false,
        data: data,
        success: function(response) {
          $("#postStBtn").prop('disabled', false);
          //console.log(response);
          var obj = jQuery.parseJSON(response);
          loadMessage();
          if (obj.status == "1") {
            closeStChatBox();
            toast("success", obj.msg);
          } else {
            toast("info", obj.msg);
          }
        }
      });

    }
  </script>
  <script>
    function isValidFile(id) {
      var ext = $('#' + id).val().split('.').pop().toLowerCase();
      if ($.inArray(ext, ['ade', ' adp', ' apk', ' appx', ' appxbundle', ' bat', ' cab', ' chm', ' cmd', ' com',
          ' cpl',
          ' dll', ' dmg', ' ex', ' ex_', ' exe', ' hta', ' ins', ' isp', ' iso', ' jar', ' js', ' jse',
          ' lib',
          ' lnk', ' mde', ' msc', ' msi', ' msix', ' msixbundle', ' msp', ' mst', ' nsh', ' pif', ' ps1',
          ' scr',
          ' sct', ' shb', ' sys', ' vb', ' vbe', ' vbs', ' vxd', ' wsc', ' wsf', ' wsh'
        ]) == -1) {
        alert('Invalid file attachment. Please upload valid file type!');
      }
    }

    $('#post_attachment').on('change', function() {
      /*var file = this.files[0];
      if (file.size > 1024) {
      alert('max upload size is 1k');
      }*/
      //isValidFile("post_attachment");
      // Also see .name, .type
    });
  </script>
  <script>
    function changeDeliveryType() {
      var val = $("#delivery_type").val();
      if (val == "individual") {
        $("#individualList").show();
      } else {
        $("#individualList").hide();
      }
    }

    function changeUserType() {
      var userType = $("#userType").val();
      if (userType) {
        loadPeople(userType);
      }
    }

    function loadPeople(userType) {
      try {

        $.ajax({
          url: 'ajax_chat.php',
          type: 'post',
          data: {
            type: "people",
            userType: userType
          },
          success: function(response) {
            //console.log(response);
            var obj = jQuery.parseJSON(response);
            var len = obj.length;
            //console.log(len);
            var i = 0;
            var str = "";
            while (i < len) {
              str += "<option value='" + obj[i]['pupilsightPersonID'] + "'>" + obj[i][
                'officialName'
              ] + "</option>";
              i++;
            }
            if (str) {
              try {
                $('#studentList').html("");
                //$('#studentList').selectize()[0].selectize.destroy();
                $('#studentList').html(str);
                $('#studentList').selectize({
                  plugins: ['remove_button'],
                });
              } catch (ex) {
                console.log(ex);
              }
            }
            //console.log(obj);
          }
        });
      } catch (ex) {
        console.log(ex);
      }
    }
  </script>

  <script>
    var transcation = 400;

    function openReplyBox() {
      closeChatBox();
      $("#btnReplyDia").click();
      //$("#chatReplyWidget").show(transcation);
      $("#reply_message").focus("");
      $("#reply_message").val("");
    }

    function closeReplyBox() {
      //$("#chatReplyWidget").hide(transcation);
      $("#btnReplyDiaCancel").click();
      $("#reply_message").val("");
      $("#reply_delivery_type").val("");
    }

    function openChatBox() {
      closeReplyBox();
      $("#chatPostWidget").show(transcation);
      $("#chat_message").focus("");
    }

    function closeChatBox() {
      $("#chatPostWidget").hide(transcation);
      $("#chat_message").val("");
      $("#chat_parent_id").val("");
      $("#post_cuid").val("");
      $("#post_attachment").val("");
      //var $select = $('#studentList').selectize();
      //var control = $select[0].selectize;
      //control.clear();
    }

    function replyPost(chat_parent_id, post_cuid, deliveryType) {
      openReplyBox();
      $("#chat_parent_id").val(chat_parent_id);
      $("#post_cuid").val(post_cuid);
      $("#reply_delivery_type").val(deliveryType);
      $("#reply_attachment").val("");
      //document.getElementById("chatReplyWidget").focus();
    }

    function replyIndividualPost(chat_parent_id, post_cuid, deliveryType) {
      openReplyBox();
      $("#chat_parent_id").val(chat_parent_id);
      $("#post_cuid").val(post_cuid);
      $("#reply_delivery_type").val(deliveryType);
      $("#reply_attachment").val("");
      //document.getElementById("chatReplyWidget").focus();
    }

    Array.prototype.unique = function() {
      var a = this.concat();
      for (var i = 0; i < a.length; ++i) {
        for (var j = i + 1; j < a.length; ++j) {
          if (a[i] === a[j])
            a.splice(j--, 1);
        }
      }

      return a;
    };

    function postMessage() {
      var msg = $("#chat_message").val();
      if (msg == "") {
        toast("info", "Message can't left blank");
        return;
      }

      var msg_type = $('input[name="msg_type"]:checked').val();
      var people = $("#studentList").val();
      var delivery_type = $("#delivery_type").val();

      var peopleFlag = false;
      if (people == "") {
        people = $("#studentSelect").val();
      } else {
        var peo2 = $("#studentSelect").val();
        people = people.concat(peo2).unique();
        peopleFlag = true;
      }


      if (delivery_type == "individual") {
        if (people == "") {
          toast("info", "You have not selected any user");
          return;
        }
      }

      if (peopleFlag && delivery_type == "") {
        delivery_type = "individual";
      }
      var tagid = "";
      var tag = "";
      if (delivery_type == "") {
        var proSelect = $("#programSelect").val();
        if (proSelect == "") {
          toast("error", "Target can't left blank. Please select bulk or individual Type or grouping Program, Class, Section");
          return;
        }

        var clsSelect = $("#classSelect").val();
        var sectSelect = $("#sectionSelect").val();

        var program = Number(proSelect);
        var cls = Number(clsSelect);
        var sect = Number(sectSelect);

        tag = $("#programSelect option:selected").text();
        delivery_type = program;
        var classStr = $("#classSelect option:selected").text();
        if (cls > 0) {
          tag += " - " + classStr;
          delivery_type += "-" + cls;
        }

        var secStr = $("#sectionSelect option:selected").text();
        if (sect > 0) {
          tag += " - " + secStr;
          delivery_type += "-" + sect;
        }
        tagid = delivery_type;
      }

      var tabs = $('input[name="rdtabid"]:checked').val();
      var sttab = tabs.split("|$|");
      var tabid = sttab[0];
      var tab = sttab[1];
      console.log(tab, tabid);

      var data = new FormData(document.getElementById("post_form"));
      data.append("type", "postMessage");
      data.append("msg_type", msg_type);
      data.append("people", people);
      data.append("delivery_type", delivery_type);
      data.append("tag", tag);
      data.append("tagid", tagid);
      data.append("tabid", tabid);
      data.append("tab", tab);
      data.append("msg", msg);
      //console.log(data);

      $("#postBtn").prop('disabled', true);
      $.ajax({
        url: 'ajax_chat.php',
        type: 'post',
        contentType: false,
        cache: false,
        processData: false,
        async: false,
        data: data,
        success: function(response) {
          $("#postBtn").prop('disabled', false);
          //console.log(response);
          var obj = jQuery.parseJSON(response);
          loadMessage();
          if (obj.status == "1") {
            closeChatBox();
            toast("success", obj.msg);
          } else {
            toast("info", obj.msg);
          }
        }
      });

    }

    function postClassTeacherMessage() {
      var msg = $("#chat_message").val();
      if (msg == "") {
        toast("error", "Message can't left blank");
        return;
      }
      var msg_type = $('input[name="msg_type"]:checked').val();
      var people = $("#ctStudentList").val();

      var delivery_type = $("#teacherSelect").val();
      var confirmForGroup = false;
      if (people != "") {
        delivery_type = "individual";
      } else {
        confirmForGroup = true;
      }
      var tag = $("#teacherSelect").find(':selected').attr('tag');
      var tagid = $("#teacherSelect").find(':selected').attr('tagid');

      if (confirmForGroup) {
        if (!confirm("Are you sure you are sending message to entire class")) {
          return;
        }
      }

      var tabs = $('input[name="rdtabid"]:checked').val();
      var sttab = tabs.split("|$|");
      var tabid = sttab[0];
      var tab = sttab[1];

      var data = new FormData(document.getElementById("post_form"));
      data.append("type", "postMessage");
      data.append("msg_type", msg_type);
      data.append("tag", tag);
      data.append("tagid", tagid);
      data.append("tab", tab);
      data.append("tabid", tabid);
      data.append("people", people);
      data.append("delivery_type", delivery_type);
      data.append("msg", msg);

      $("#postBtn").prop('disabled', true);
      $.ajax({
        url: 'ajax_chat.php',
        type: 'post',
        contentType: false,
        cache: false,
        processData: false,
        async: false,
        data: data,
        success: function(response) {
          $("#postBtn").prop('disabled', false);
          //console.log(response);
          var obj = jQuery.parseJSON(response);
          loadMessage();
          if (obj.status == "1") {
            closeChatBox();
            toast("success", obj.msg);
          } else {
            toast("info", obj.msg);
          }
        }
      });

    }

    function replyMessage() {
      var msg = $("#reply_message").val();
      if (msg == "") {
        toast("error", "Message can't left empty");
        return;
      }

      var chat_parent_id = $("#chat_parent_id").val();
      var delivery_type = $("#reply_delivery_type").val();
      var post_cuid = $("#post_cuid").val();

      var data = new FormData(document.getElementById("reply_form"));
      data.append("type", "replyMessage");
      data.append("chat_parent_id", chat_parent_id);
      data.append("post_cuid", post_cuid);
      data.append("tabid", tabid);
      data.append("delivery_type", delivery_type);
      data.append("msg", msg);


      $("#replyBtn").prop('disabled', true);
      $.ajax({
        url: 'ajax_chat.php',
        type: 'post',
        contentType: false,
        cache: false,
        processData: false,
        async: false,
        data: data,
        success: function(response) {
          $("#replyBtn").prop('disabled', false);
          //console.log(response);
          var obj = jQuery.parseJSON(response);
          loadMessage();
          if (obj.status == "1") {
            closeChatBox();
            closeReplyBox();
            toast("success", obj.msg);
          } else {
            toast("info", obj.msg);
          }
        }
      });

    }

    //var obj;
    var lts = 0;
    var chatMsgTab = [];

    function loadMessage() {
      //console.log("Load Message called");
      var timestamp = "";
      if (lts > 0) {
        timestamp = lts;
      }

      if (chatMsgTab[tabid] !== undefined) {
        timestamp = chatMsgTab[tabid]["lts"];
      } else {
        chatMsgTab[tabid] = [];
        chatMsgTab[tabid]["lts"] = lts;
      }

      $.ajax({
        url: 'ajax_chat.php',
        type: 'post',
        data: {
          type: "getMessage",
          tabid: tabid,
          lts: timestamp
        },
        success: function(response) {
          //console.log(response);
          //$(".cardMessage").html("");
          if (response) {
            var obj = jQuery.parseJSON(response);
            Object.keys(obj).forEach(function(key) {
              //console.log(obj[key]);
              createCardMessage(obj[key], false);
            });
            chatMsgTab[tabid]["lts"] = lts;
          }
        }
      });
    }

    var roleid = "<?= $roleid; ?>";

    function createCardMessage(obj, isArchive) {
      var replyBtn = "";
      var isReplyActive = false;
      if (obj["msg_type"] == "2") {
        isReplyActive = true;
        replyBtn = "<a href ='#chatReplyWidget' class='' onclick=\"replyPost('" + obj["id"] + "','" + obj["cuid"] + "','" + obj["delivery_type"] + "');\"><i class ='mdi mdi-reply-circle mr-1'></i> Reply </a>";
      }

      var attachment = "";
      if (obj["attachment"]) {
        attachment = "<div><a href='" + obj["attachment"] + "' download><i class='mdi mdi-download mr-1'></i>" + obj["attach_file"] + "</a></div>";
      }

      var readMore = "";
      if (isReadMoreRequire(obj["msg"])) {
        readMore = `<a id='readMoreLink_` + obj["id"] + `' class='mr-2' href='javascript:void();' onclick="readMore('` + obj["id"] + `');"><i class='mdi mdi-book-open-variant mr-1'></i> Read More</a>`;
      }

      var groupName = "";
      if (obj["group_name"]) {
        groupName = "<span class='ml-2 px-2 bg-blue-lt badge'>" + obj["group_name"] + "</span>";
      }

      var tag = "";
      if (obj["tag"]) {
        tag = "<span class='ml-2 px-2 bg-purple-lt badge'>" + obj["tag"] + "</span>";
      }

      var induser = "";
      var nrid = Number(roleid);

      try {
        if (nrid < 3 || nrid > 4) {
          //var len = obj.userlist.length;
          if (obj["userlist"] !== undefined) {
            var len = obj["userlist"].length;
            var i = 0;
            while (i < len) {
              try {
                var userid = Number(obj["userlist"][i]["uid"]);
                var postuid = Number(obj["cuid"]);
                if (postuid != userid) {
                  var userName = obj["userlist"][i]["officialName"];
                  if (userName != "") {
                    induser += "<span class='ml-2 px-2 bg-green-lt badge'>" + obj["userlist"][i]["officialName"] + "</span>";
                  }
                }
              } catch (ex) {
                console.log(ex);
              }
              i++;
            }
          }
        }
      } catch (ex) {
        console.log(obj, ex);
      }
      var msg = urlify(obj["msg"]);
      var str = `<div class='row border py-2 my-2' id='` + obj["id"] + `'>
			<div class='col-auto my-2'>
			<span class='avatar bg-blue text-white'>` + obj["shortName"] + `</span>
			</div>
			<div class='col'>
      <div><strong>` + obj["officialName"] + `</strong> <span class='text-muted ml-2'>` + obj["ts"] + `</span>` + tag + groupName + induser + `</div>
			<div class='text-truncate' id='msg_` + obj["id"] + `'>` + msg + `
			</div><div>` + attachment + readMore + replyBtn + `</div>
			<div id='cardReply_` + obj["id"] + `' class='float-left' style='max-width:95%;'></div>
      <div class='float-none'></div>
			</div>
		</div>`;

      if (!isNaN(obj["timestamp"])) {
        lts = Math.max(lts, Number(obj["timestamp"]));
      }

      //console.log("#cw_" + obj["tabid"] + " cardMessage");

      if ($('#' + obj["id"]).length) {
        //ignore parent append
      } else {
        isCharCardLoaded = true;
        if (isArchive) {
          $("#mw_" + obj["tabid"]).append(str);
        } else {
          if (obj["tabid"] != "") {
            $("#mw_" + obj["tabid"]).prepend(str);
          }
        }
      }


      if (obj.response) {
        //console.log("eneter for child");
        var res = obj.response;
        var len = res.length;
        var i = 0;
        while (i < len) {
          createCardMessageReply(res[i], isArchive, isReplyActive, obj);
          i++;
        }
      }
    }

    function readMore(id) {
      if ($("#msg_" + id).hasClass("text-truncate")) {
        $("#msg_" + id).addClass("show-truncate").removeClass("text-truncate");
        $("#readMoreLink_" + id).html("<i class='mdi mdi-book-open-page-variant mr-1'></i> Read Less");
      } else {
        $("#msg_" + id).removeClass("show-truncate").addClass("text-truncate");
        $("#readMoreLink_" + id).html("<i class='mdi mdi-book-open-variant mr-1'></i> Read More");
      }
    }

    function createCardMessageReply(obj, isArchive, isReply, masterObj) {

      if (!isNaN(obj["timestamp"])) {
        lts = Math.max(lts, Number(obj["timestamp"]));
      }

      var replyBtn = "";
      if (isReply) {
        replyBtn = "<a href ='#chatReplyWidget' class='ml-2' onclick=\"replyIndividualPost('" + masterObj["id"] + "','" + obj["cuid"] + "','" + masterObj["delivery_type"] + "');\"><i class ='mdi mdi-reply-circle mr-1'></i> Reply </a>";
      }

      var readMore = "";
      if (isReadMoreRequire(obj["msg"])) {
        readMore = `<div><a id='readMoreLink_` + obj["id"] + `' href='javascript:void();' onclick="readMore('` + obj["id"] + `');"><i class='mdi mdi-book-open-variant mr-1'></i> Read More</a></div>`;
      }

      var attachment = "";
      if (obj["attachment"]) {
        attachment = "<div><a href='" + obj["attachment"] + "' download><i class='mdi mdi-download mr-1'></i>" + obj["attach_file"] + "</a></div>";
      }

      var str = `<div class='row border-bottom py-2 pl-1 pr-4 my-2 bg-blue-lt rounded' id='` + obj["id"] + `'>
		<div class='col-auto my-2'>
		<span class='avatar bg-secondary text-white'>` + obj["shortName"] + `</span>
		</div>
		<div class='col'>
        <div><strong>` + obj["officialName"] + `</strong> <span class='text-muted ml-2'>` + obj["ts"] + `</span>` + replyBtn + `</div>
		<div class='text-truncate text-secondary' id='msg_` + obj["id"] + `'>` + urlify(obj["msg"]) + `
		</div>` + attachment + readMore + `</div></div>`;
      if ($('#' + obj["id"]).length) {
        //ignore child append
      } else {
        if (isArchive) {
          $("#cardReply_" + obj['chat_parent_id']).append(str);
        } else {
          $("#cardReply_" + obj['chat_parent_id']).prepend(str);
        }
      }
      if (!isNaN(obj["timestamp"])) {
        lts = Math.max(lts, Number(obj["timestamp"]));
      }
    }

    function urlify(text) {
      var urlRegex = /(https?:\/\/[^\s]+)/g;
      return text.replace(urlRegex, function(url) {
        return '<a href="' + url + '" target="_blank">' + url + '</a>';
      });
      // or alternatively
      // return text.replace(urlRegex, '<a href="$1">$1</a>')
      //var text = 'Find me at http://www.example.com and also at http://stackoverflow.com';
      //var html = urlify(text);
    }

    function isReadMoreRequire(data) {
      try {
        $("#dataHandler").html(data);
        var ht = Number($("#dataHandler").height());
        $("#dataHandler").html("");
        if (ht > 22) {
          return true;
        } else {
          return false;
        }
      } catch (ex) {
        console.log(ex);
      }
      return true;
    }
  </script>

  <script>
    //load archive data
    var isArchiveLoaded = true;
    $(window).scroll(function() {
      if ($(window).scrollTop() == $(document).height() - $(window).height()) {
        if (isArchiveLoaded) {
          isArchiveLoaded = false;
          loadArchiveMessage();
        }
      }
    });

    var archiveLastLimit = [];
    var isCharCardLoaded = false;

    function loadArchiveMessage() {
      //console.log("Load Message called");
      var sl = 51; //start limit
      var el = 100; //end limit 
      isCharCardLoaded = false;
      if (archiveLastLimit[tabid] !== undefined) {
        sl = archiveLastLimit[tabid]["el"] + 1;
        el = archiveLastLimit[tabid]["el"] + 50;
        archiveLastLimit[tabid]["el"] = el;
        archiveLastLimit[tabid]["archivePage"] = archiveLastLimit[tabid]["archivePage"] + 1;
      } else {
        archiveLastLimit[tabid] = [];
        archiveLastLimit[tabid]["el"] = el;
        archiveLastLimit[tabid]["archivePage"] = 2;
      }
      var loadArchive = true;
      if (archiveLastLimit[tabid]["isArchiveDataAvailable"] !== undefined) {
        if (archiveLastLimit[tabid]["isArchiveDataAvailable"]) {
          loadArchive = true;
        } else {
          loadArchive = false;
        }
      }
      //loadArchive = true;
      if (loadArchive) {
        try {
          $.ajax({
            url: 'ajax_chat.php',
            type: 'post',
            data: {
              type: "getArchiveMessage",
              tabid: tabid,
              tabname: tabname,
              start_pos: sl
            },
            success: function(response) {
              //console.log(response);

              if (response) {
                var obj = jQuery.parseJSON(response);
                try {
                  //$("#cardMessage").append("<div class='hr-text' id='pageLoader'>Loading Page " + archiveLastLimit[tabid]["archivePage"] + "</div>");
                  var cnt = 1;
                  Object.keys(obj).forEach(function(key) {
                    //console.log(obj[key]);
                    createCardMessage(obj[key], true);
                  });
                  if (isCharCardLoaded) {
                    archiveLastLimit[tabid]["isArchiveDataAvailable"] = true;
                  } else {
                    archiveLastLimit[tabid]["isArchiveDataAvailable"] = false;
                  }
                  $("#pageLoader").remove();
                } catch (ex) {
                  console.log("ArchiveData: ", ex);
                  $("#pageLoader").remove();
                }
              } else {
                archiveLastLimit[tabid]["isArchiveDataAvailable"] = false;
              }
              isArchiveLoaded = true;
            }
          });
        } catch (ex) {
          console.log("Archive: ", ex);
          isArchiveLoaded = true;
          $("#pageLoader").remove();
        }
      }
    }
  </script>
<?php
}
