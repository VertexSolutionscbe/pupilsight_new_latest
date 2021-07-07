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


use Pupilsight\Domain\Messenger\ChatGateway;
?>

<?php
require_once __DIR__ . '/moduleFunctions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page->breadcrumbs->add(__('Manage Chat Tab/Module'));
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
  $chatGateway = $container->get(ChatGateway::class);
  $roleid = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
  //tab list
  $uid = $_SESSION[$guid]['pupilsightPersonID'];
  $roleidnum = (int)$roleid;
  $tabs = $chatGateway->getAdminRoleTabs($connection2);
  $roles = $chatGateway->getRoleMaster($connection2);
  $len = count($roles);
  $i = 0;
  $strole = "";
  while ($i < $len) {
    $strole .= "<option value='" . (int)$roles[$i]["id"] . "'>" . ucwords($roles[$i]["name"]) . "</option>";
    $i++;
  }
  $len = count($tabs);
  $i = 0;
?>
  <div id='actionDiv' class='d-none'>

    <form id="frmTab" class="border p-4 my-4">
      <div class='row'>
        <div class="col-md-6 col-sm-12">
          <label class="form-label required">Tab / Module Name</label>
          <input type='hidden' id='id' name='id' value="">
          <input type='text' class="form-control" id='name' name='name' value="" required>
        </div>

        <div class="col-md-6 col-sm-12">
          <label class="form-label required">Select Roles</label>
          <select id='roles' name='roles[]' multiple class='form-select' required>
            <?= $strole; ?>
          </select>
        </div>

        <div class="col-md-12 col-sm-12 mt-4">
          <button type='button' id='btnSave' class='btn btn-primary mr-2' onclick="saveTab();">Save</button>
          <button type='button' class='btn btn-secondary' onclick="cancelActionDiv();">Cancel</button>
        </div>
      </div>
    </form>

  </div>
  <script>
    $(function() {
      $("#actionDiv").removeClass("d-none").hide();
    });

    function saveTab() {
      try {
        var name = $("#name").val();
        var roles = $("#roles").val();
        if (name == "") {
          toast("error", "Enter Tab/Module Name.");
          return;
        }
        if (roles == "") {
          toast("error", "Please Select Roles.");
          return;
        }
        $("#btnSave").prop('disabled', true);
        var data = new FormData(document.getElementById("frmTab"));
        data.append("type", "saveTab");

        $.ajax({
          url: 'ajax_chat.php',
          type: 'post',
          contentType: false,
          cache: false,
          processData: false,
          async: false,
          data: data,
          success: function(response) {
            cancelActionDiv();
            $("#btnSave").prop('disabled', false);
            var obj = jQuery.parseJSON(response);
            if (obj.status == "1") {
              toast("success", obj.msg);
              setTimeout(location.reload(), 400);
            } else {
              toast("info", obj.msg);
            }
          }
        });
      } catch (ex) {
        console.log("saveTab: ", ex);
      }
    }

    function openActionDiv() {
      $("#viewDiv").hide(400);
      $("#actionDiv").show(400);
    }

    function formReset() {
      $("#id").val("");
      $("#name").val("");
      $("#roles").val("");
      $('#frmTab').trigger("reset");
    }

    function cancelActionDiv() {
      $("#viewDiv").show(400);
      $("#actionDiv").hide(400);
      formReset();
    }
  </script>
  <div id='viewDiv'>
    <button class="btn btn-primary my-2" onclick="openActionDiv();"><i class="mdi mdi-plus-thick mr-1"></i>Tab / Module</button>
    <table class="table">
      <thead>
        <tr>
          <td style='width:50px' class='text-center'>SrNo</td>
          <td>Name</td>
          <td style='width:200px;' class='text-center'>Action</td>
        </tr>
      </thead>
      <tbody>
        <?php
        $st = 1;
        $str = "";
        $tb = array();
        while ($i < $len) {
          $tb[$tabs[$i]["id"]] = $tabs[$i];
          $str .= "<tr>";
          $str .= "<td class='text-center'>" . $st . "</td>";
          $str .= "<td>" . ucwords($tabs[$i]["name"]) . "</td>";
          $str .= "<td class='text-center'><button class='btn btn-link' type='button' onclick=\"editTab('" . $tabs[$i]["id"] . "');\"><i class='mdi mdi-pencil-box-outline mdi-24px'></i></button>";
          $str .= "<button class='btn btn-link' type='button' onclick=\"deleteTab('" . $tabs[$i]["id"] . "');\"><i class='mdi mdi-delete-forever-outline mdi-24px'></i></button></td>";
          $str .= "</tr>";
          $i++;
          $st++;
        }
        echo $str;
        ?>
      </tbody>
    </table>
  </div>
  <script>
    //tab data
    var tbd = <?php echo json_encode($tb); ?>;

    function editTab(id) {
      openActionDiv();
      $("#id").val(tbd[id]["id"]);
      $("#name").val(tbd[id]["name"]);
      var roles = (tbd[id]["roleids"]).split(",");
      $("#roles").val(roles);
      console.log(roles);
    }

    function deleteTab(id) {
      try {
        $.ajax({
          url: 'ajax_chat.php',
          type: 'post',
          cache: false,
          async: false,
          data: {
            "id": id,
            "type": "deleteTab"
          },
          success: function(response) {
            var obj = jQuery.parseJSON(response);
            if (obj.status == "1") {
              toast("success", obj.msg);
              location.reload();
            } else {
              toast("info", obj.msg);
            }
          }
        });
      } catch (ex) {
        console.log("deleteTab: ", ex);
      }
    }
  </script>

<?php
}
