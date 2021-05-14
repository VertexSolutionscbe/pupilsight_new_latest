<style>
.text-truncate {
    height: 26px;
}
</style>
<?php

/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Contracts\Comms\SMS;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Helper\HelperGateway;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Messenger\GroupGateway;
use Pupilsight\Tables\DataTable;
?>

<?php
require_once __DIR__ . "/moduleFunctions.php";

$page->breadcrumbs->add(__("Chat Message"));
$accessFlag = true;
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
    
    $helperGateway = $container->get(HelperGateway::class);
    $roleid = $_SESSION[$guid]["pupilsightRoleIDPrimary"];
    $isPostAllow = true;
    $isStParent = false; // student and parent post
    if ($roleid == "003") {
        $isPostAllow = false;
        $isStParent = true;
    } elseif ($roleid == "004") {
        $isPostAllow = false;
        $isStParent = true;
    }
    
    if($isStParent){
        $pupilsightSchoolYearID  =$_SESSION[$guid]["pupilsightSchoolYearID"];
        $pupilsightPersonID  =$_SESSION[$guid]["pupilsightPersonID"];
        $stSubList = $helperGateway->getClassTeacher($connection2, $pupilsightSchoolYearID, $pupilsightPersonID);
        $groupList = $helperGateway->getGroupList($connection2, $pupilsightSchoolYearID);
    }
    
    
    if ($isPostAllow) { ?>
<!---Chat Post Widget---->
<div class="card" id='chatPostWidget'>
    <div class="card-body">
        <div class="row">
            <div class='col-md-2 col-sm-12'>
                <div class="form-label">Bulk or Individual Type</div>
                <select id='delivery_type' name='delivery_type' class='form-control' onchange="changeDeliveryType();">
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
                        <div class="form-label">Select User Type</div>
                        <select id='userType' name='userType' class='form-control' onchange="changeUserType();">
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
            <div class="col-12 my-3">
                <textarea class="form-control" id="chat_message" name="chat_message" rows="6"
                    placeholder="Write Message Here"></textarea>
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
                <input class="form-check-input" id="msg_type2" name="msg_type" type="radio" checked value="2">
                <span class="form-check-label">Two Way</span>
            </label>

            <label class="form-check form-check-inline">
                <input class="form-check-input" id="msg_type1" name='msg_type' type="radio" value="1">
                <span class="form-check-label">One Way</span>
            </label>
        </div>



        <div class="col-12 mt-4">
            <button type="button" class="btn btn-primary" id='postBtn' onclick="postMessage();">Post Message</button>
            <button type="button" class="btn btn-secondary ml-2" onclick="closeChatBox();">Cancel</button>
        </div>
    </div>
</div>
</div>
<?php }else if($isStParent){
    ?>
<div class="card" id='chatStPostWidget'>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 col-sm-12">
                <select id='stGroup' onchange="stGroupChange()">
                    <option value="">Select Type</option>
                    <?php
                        if($stSubList["pupilsightPersonID"]){
                            echo "<option value='".$stSubList["pupilsightPersonID"]."' groupid='' groupname='Class Teacher'>Class Teacher</option>";
                        }
                        echo "<option value='subject_teacher' groupid='' groupname='Subject Teacher'>Subject Teacher(s)</option>";
                        
                        if($groupList){
                            $len = count($groupList);
                            $i = 0;
                            while($i<$len){
                                echo "<option value='".$groupList[$i]["uid"]."' groupid='".$groupList[$i]["groupid"]."' groupname='".$groupList[$i]["name"]."'>".$groupList[$i]["name"]."</option>";
                                $i++;
                            }
                        }
                    ?>
                </select>
            </div>

            <div class="col-md-4 col-sm-12">
                <select id='stSubject'>
                    <?php
                        $sublist = $stSubList["sublist"];
                        $len = count($sublist);
                        $i = 0;
                        while($i<$len){
                            echo "<option value='".$sublist[$i]["pupilsightPersonID"]."'>".$sublist[$i]["subject_display_name"]."</option>";
                            $i++;
                        }
                    ?>
                </select>
            </div>

            <div class="col-12 my-3">
                <textarea class="form-control" id="st_chat_message" name="chat_message" rows="6"
                    placeholder="Write Message Here"></textarea>
            </div>

            <div class="col-12 my-1">
                <div class="form-label">Attachment</div>
                <form enctype="multipart/form-data" id="st_post_form">
                    <input type="file" id='st_post_attachment' name="attachment" class='form-control'>
                </form>
            </div>

            <div class="col-12 mt-4">
                <button type="button" class="btn btn-primary" id='postStBtn' onclick="postStMessage();">Post
                    Message</button>
                <button type="button" class="btn btn-secondary ml-2" onclick="closeStChatBox();">Cancel</button>
            </div>
        </div>

    </div>
</div>
<?php
}
?>
<div class="card" id='chatReplyWidget'>
    <div class="card-body">
        <div class="row">
            <div class="col-12 my-3">
                <textarea class="form-control" id="reply_message" name="chat_message" rows="6"
                    placeholder="Write Message Here"></textarea>
                <input type='hidden' id='chat_parent_id' value="">
                <input type='hidden' id='reply_delivery_type' value="">
            </div>
            <div class="col-12 mb-3">
                <div class="form-label">Attachment</div>
                <form enctype="multipart/form-data" id="reply_form">
                    <input type="file" id='reply_attachment' name="attachment" class='form-control'>
                </form>
            </div>
        </div>
        <div class="col-12">
            <button type="button" class="btn btn-primary" id='replyBtn' onclick="replyMessage();">Reply
                Message</button>
            <button type="button" class="btn btn-secondary ml-2" onclick="closeReplyBox();">Cancel</button>
        </div>
    </div>
</div>
</div>

<!--Chat Area Details--->
<div class="card my-4">

    <div class='card-header'>
        <div class='container'>
            <div class='row'>
                <div class='col-auto'>
                    <h2>Chat Message</h2>
                </div>
                <div class='col-auto ml-auto'>
                    <?php if ($isPostAllow) { 
                        echo "<button class='btn btn-primary' onclick='openChatBox();'>Post New Message</button>";
                     } else if($isStParent){
                        echo "<button class='btn btn-primary' onclick='openStChatBox();'>Post New Message</button>";
                    } ?>
                </div>
            </div>
        </div>
    </div>

    <!--Card Message Check-->
    <div class="card-body" id='cardMessage'></div>

</div>

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
        alert("Please enter your message");
        return;
    }

    var stGroup = $("#stGroup").val();
    if (stGroup == "") {
        alert("Please select group type or class teacher");
        return;
    }

    var people = stGroup;
    if (stGroup == "subject_teacher") {
        people = $("#stSubject").val();
    }

    var delivery_type = "individual";

    var groupid = $("#stGroup").find(':selected').attr('groupid');
    var groupName = $("#stGroup").find(':selected').attr('groupname');


    var data = new FormData(document.getElementById("st_post_form"));
    data.append("type", "postMessage");
    data.append("msg_type", "2");
    data.append("people", people);
    data.append("group_id", groupid);
    data.append("group_name", groupName);
    data.append("delivery_type", delivery_type);
    data.append("msg", msg);
    //console.log(data);


    if (msg) {
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
                }
                alert(obj.msg);
            }
        });
    } else {
        alert("Message is empty.");
    }
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
                console.log(len);
                var i = 0;
                var str = "";
                while (i < len) {
                    str += "<option value='" + obj[i]['pupilsightPersonID'] + "'>" + obj[i][
                        'officialName'
                    ] + "</option>";
                    i++;
                }
                if (str) {
                    $('#studentList').html("");
                    //$('#studentList').selectize()[0].selectize.destroy();
                    $('#studentList').html(str);
                    $('#studentList').selectize({
                        plugins: ['remove_button'],
                    });
                }
                console.log(obj);
            }
        });
    } catch (ex) {
        console.log(ex);
    }
}
</script>
<script>
var interval;
$(function() {
    loadPeople('all');
    loadMessage();
    interval = setInterval(() => {
        loadMessage();
    }, 10000);
    $("#chatPostWidget, #chatReplyWidget, #stSubject, #chatStPostWidget").hide();
});

var transcation = 400;

function openReplyBox() {
    closeChatBox();
    $("#chatReplyWidget").show(transcation);
    $("#reply_message").focus("");
    $("#reply_message").val("");
}

function closeReplyBox() {
    $("#chatReplyWidget").hide(transcation);
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
    $("#post_attachment").val("");
    //var $select = $('#studentList').selectize();
    //var control = $select[0].selectize;
    //control.clear();
}

function replyPost(chat_parent_id, deliveryType) {
    openReplyBox();
    $("#chat_parent_id").val(chat_parent_id);
    $("#reply_delivery_type").val(deliveryType);
    $("#reply_attachment").val("");
    document.getElementById("chatReplyWidget").focus();
}

function postMessage() {
    var msg = $("#chat_message").val();
    var msg_type = $('input[name="msg_type"]:checked').val();
    var people = $("#studentList").val();
    var delivery_type = $("#delivery_type").val();

    if (delivery_type == "individual") {
        if (people == "") {
            alert("You have not selected any user");
            return;
        }
    }

    var data = new FormData(document.getElementById("post_form"));
    data.append("type", "postMessage");
    data.append("msg_type", msg_type);
    data.append("people", people);
    data.append("delivery_type", delivery_type);
    data.append("msg", msg);
    //console.log(data);


    if (msg) {
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
                }
                alert(obj.msg);
            }
        });
    } else {
        alert("Message is empty.");
    }
}

function replyMessage() {
    var msg = $("#reply_message").val();
    if (msg == "") {
        alert("Message required");
        return;
    }

    var chat_parent_id = $("#chat_parent_id").val();
    var delivery_type = $("#reply_delivery_type").val();

    var data = new FormData(document.getElementById("reply_form"));
    data.append("type", "replyMessage");
    data.append("chat_parent_id", chat_parent_id);
    data.append("delivery_type", delivery_type);
    data.append("msg", msg);


    if (msg) {
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
                }
                alert(obj.msg);
            }
        });
    } else {
        alert("Message is empty.");
    }
}

//var obj;
var lts = 0;

function loadMessage() {
    //console.log("Load Message called");
    var timestamp = "";
    if (lts > 0) {
        timestamp = lts;
    }
    $.ajax({
        url: 'ajax_chat.php',
        type: 'post',
        data: {
            type: "getMessage",
            lts: timestamp
        },
        success: function(response) {
            if (response) {
                var obj = jQuery.parseJSON(response);

                Object.keys(obj).forEach(function(key) {
                    //console.log(obj[key]);
                    createCardMessage(obj[key]);
                });
            }
        }
    });
}

function createCardMessage(obj) {
    //console.log("test card message: ",obj);
    var replyBtn = "";
    if (obj["msg_type"] == "2") {
        replyBtn = "<a href ='#chatReplyWidget' class='ml-2' onclick=\"replyPost('" + obj["id"] + "','" + obj[
                "delivery_type"] +
            "');\"><i class ='mdi mdi-message-reply-text mr-1'></i> Reply </a>";
    }
    var attachment = "";
    if (obj["attachment"]) {
        attachment = "<div><a href='" + obj["attachment"] + "' download><i class='mdi mdi-download mr-1'></i>" +
            obj["attach_file"] + "</a></div>";
    }

    var groupName = "";
    if (obj["group_name"]) {
        groupName = "<span class='ml-2 px-2 bg-blue-lt'>" + obj["group_name"] + "</span>";
    }
    var str =
        `<div class='row border py-2 my-2' id='` + obj["id"] + `'>
			<div class='col-auto my-2'>
			<span class='avatar'>` + obj["shortName"] + `</span>
			</div>
			<div class='col'>
            <div><strong>` + obj["officialName"] + `</strong> <span class='text-muted ml-2'>` + obj["ts"] + `</span>` +
        groupName + `</div>
			<div class='text-truncate' id='msg_` + obj["id"] + `'>` + obj["msg"] + `
			</div>` + attachment + `
			<div><a href='javascript:void();' onclick="readMore('` + obj["id"] + `');"><i class='mdi mdi-book-open-variant mr-1'></i> Read more</a>
				` + replyBtn + `
			</div>
			<div id='cardReply_` + obj["id"] + `'></div>
			</div>
		</div>`;
    if (!isNaN(obj["timestamp"])) {
        lts = Math.max(lts, Number(obj["timestamp"]));
    }
    //console.log(str);
    if ($('#' + obj["id"]).length) {
        //ignore parent append
    } else {
        $("#cardMessage").prepend(str);
    }


    if (obj.response) {
        //console.log("eneter for child");
        var res = obj.response;
        var len = res.length;
        var i = 0;
        while (i < len) {
            createCardMessageReply(res[i]);
            i++;
        }
    }
}

function readMore(id) {
    $("#msg_" + id).removeClass("text-truncate");
}

function createCardMessageReply(obj) {

    var attachment = "";
    if (obj["attachment"]) {
        attachment = "<div><a href='" + obj["attachment"] + "' download><i class='mdi mdi-download mr-1'></i>" +
            obj[
                "attach_file"] + "</a></div>";
    }

    var str =
        `<div class='row border-bottom bg-gray-lt py-2' id='` + obj["id"] + `'>
		<div class='col-auto my-2'>
		<span class='avatar'>` + obj["shortName"] + `</span>
		</div>
		<div class='col'>
        <div><strong>` + obj["officialName"] + `</strong> <span class='text-muted ml-2'>` + obj["ts"] + `</span></div>
		<div class='text-truncate' id='msg_` + obj["id"] + `'>
		 ` + obj["msg"] + `
		</div>` + attachment + `
		<div>
        <a href='javascript:void();' onclick="readMore('` + obj["id"] + `');"><i class='mdi mdi-book-open-variant mr-1'></i> Read more</a>
		</div>
		</div>
	</div>`;
    if ($('#' + obj["id"]).length) {
        //ignore child append
    } else {
        $("#cardReply_" + obj['chat_parent_id']).prepend(str);
    }
    if (!isNaN(obj["timestamp"])) {
        lts = Math.max(lts, Number(obj["timestamp"]));
    }
}
</script>
<?php
}