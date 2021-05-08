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

if (
    isActionAccessible(
        $guid,
        $connection2,
        "/modules/Messenger/messenger_post.php"
    ) == false
) {
    //Acess denied
    print "<div class='alert alert-danger'>";
    print __("You do not have access to this action.");
    print "</div>";
} else {
     ?>
	 	<!---Chat Post Widget---->
		<div class="row" id='chatPostWidget'>
			<div class="col-12 my-3">
			<textarea class="form-control" id="chat_message" name="chat_message" rows="6" placeholder="Write Message Here" ></textarea>
			<input type='hidden' id='chat_parent_id' value=''>
			</div>
			<div class="col-12 my-3" >
			<div class="mb-3">
				<div class="form-label">Message Type</div>
				<div>
					<label class="form-check form-check-inline">
					<input class="form-check-input" id="msg_type2" name="msg_type" type="radio" checked value="2">
					<span class="form-check-label">Two Way</span>
					</label>

					<label class="form-check form-check-inline">
					<input class="form-check-input" id="msg_type1" name='msg_type' type="radio" value="1">
					<span class="form-check-label">One Way</span>
					</label>
				</div>
				</div>
			</div>
			<div class="col-12">
				<button type="button" class="btn btn-primary" onclick="postMessage();">Post Message</button>
			</div>
		</div>
		<!--Chat Area Details--->
		<div class="card my-4">

			<div class='card-header'>
				<div class='container'>
				<div class='row'>
					<div class='col-auto'><h2>Chat Message</h2></div>
					<div class='col-auto ml-auto'>
						<button class='btn btn-primary' onclick="openChatBox();">Post New Message</button>
					</div>
				</div>
				</div>
			</div>

			<div class="card-body" id='cardMessage'>
				<!--
				<div class='row border py-2' id=''>
					<div class='col-auto my-2'>
					<span class='avatar'>HS</span>
					</div>
					<div class='col'>
					<div class='text-truncate'>
					<strong>Rakesh Kumar</strong> As you are aware that we have gone live with parents login for St. Josephs Indian High School yesterday and parents are logging into the portal access their respective wards information. 
					The school has asked the parents to make the fee payment online and the payment gateway is configured to accept the payments.
					Parents have been making the fee payment online from today, however parents reached out to use saying they logged into pupilpod 
					and made the payment of Rs. 15,000/- but once the payment is made they get no response and the page just freezes there with no details of the 
					payment or invoice. When they login to the portal the invoice to which the payment was made is still showing as Not Paid where the amount has been 
					deducted from there respective Bank account and they also got SMS saying the transaction is successful.
					</div>
					<div class='text-muted'> 
						<span>12.25pm</span>
						<button class='btn btn-link'><i class='mdi mdi-book-open-variant mr-1'></i> Read more</button>
						<button class='btn btn-link'><i class='mdi mdi-message-reply mr-1'></i> Reply</button>
					</div>
					</div>
					<div id='cardReply'></div>
				</div>
				-->
				
			</div>
		</div>
	<?php
}
?>

<script>
	loadMessage();
	var interval;
	$(function() {
		interval = setInterval(() => {
			loadMessage();
		}, 10000);
		$("#chatPostWidget").hide();
	});

	var transcation = 400;
	function openChatBox(){
		$("#chatPostWidget").show(transcation);
		$("#chat_message").focus("");
	}

	function closeChatBox(){
		$("#chatPostWidget").hide(transcation);
		$("#chat_message").val("");
		$("#chat_parent_id").val("");
	}

	function replyPost(chat_parent_id){
		$("#chat_parent_id").val(chat_parent_id);
		$("#chat_message").val("");
		openChatBox();
	}

function postMessage(){
	var msg = $("#chat_message").val();
	var msg_type = $('input[name="msg_type"]:checked').val();
	var chat_parent_id = $("#chat_parent_id").val();
	if(msg){
	$.ajax({
		url: 'ajax_chat.php',
		type: 'post',
		data: {
			type: "postMessage",
			msg_type: msg_type,
			chat_parent_id: chat_parent_id,
			msg: msg
		},
		success: function(response) {
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			loadMessage();
			if(obj.status=="1"){
				closeChatBox();
			}
			alert(obj.msg);
		}
	});
	}else{
		alert("Message is empty.");
	}
}

//var obj;
var lts = 0;
function loadMessage(){
	console.log("Load Message called");
	var timestamp ="";
	if(lts>0){
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
			if(response){
				var obj = jQuery.parseJSON(response);
				
				Object.keys(obj).forEach(function (key){
					//console.log(obj[key]);
					createCardMessage(obj[key]);
				});
			}
		}
	});
}



function createCardMessage(obj){
	console.log("test card message: ",obj);
	var replyBtn = "";
	if(obj["msg_type"]=="2"){
		replyBtn = "<button class='btn btn-link' onclick=\"replyPost('"+obj["id"]+"');\"><i class='mdi mdi-message-reply mr-1'></i> Reply</button>";
	}

	var str =
		`<div class='row border py-2 my-2' id='`+obj["id"]+`'>
			<div class='col-auto my-2'>
			<span class='avatar'>`+obj["shortName"]+`</span>
			</div>
			<div class='col'>
			<div class='text-truncate' id='msg_`+obj["id"]+`'>
			<strong>`+obj["officialName"]+`</strong> `+obj["msg"]+`
			</div>
			<div class='text-muted'> 
				<span>`+obj["ts"]+`</span>
				<button class='btn btn-link ml-2' onclick="readMore('`+obj["id"]+`');"><i class='mdi mdi-book-open-variant mr-1'></i> Read more</button>
				`+replyBtn+`
			</div>
			<div id='cardReply_`+obj["id"]+`'></div>
			</div>
		</div>`;
		lts = Math.max(lts, Number(obj["timestamp"]));
	//console.log(str);
	if($('#'+obj["id"]).length){
		//ignore parent append
	}else{
		$("#cardMessage").prepend(str);	
	}
	

	if(obj.response){
		//console.log("eneter for child");
		var res = obj.response;
		var len = res.length;
		var i = 0;
		while(i<len){
			createCardMessageReply(res[i]);
			i++;
		}
	}
}

function readMore(id){
	$("#msg_"+id).removeClass("text-truncate");
}

function createCardMessageReply(obj){
 	var str =
	 `<div class='row border-bottom bg-gray-lt py-2' id='`+obj["id"]+`'>
		<div class='col-auto my-2'>
		<span class='avatar'>`+obj["shortName"]+`</span>
		</div>
		<div class='col'>
		<div class='text-truncate' id='msg_`+obj["id"]+`'>
		<strong>`+obj["officialName"]+`</strong> `+obj["msg"]+`
		</div>
		<div class='text-muted'> 
			<span>`+obj["ts"]+`</span>
			<button class='btn btn-link ml-2' onclick="readMore('`+obj["id"]+`');"><i class='mdi mdi-book-open-variant mr-1'></i> Read more</button>
		</div>
		</div>
	</div>`;
	if($('#'+obj["id"]).length){
		//ignore child append
	}else{
		$("#cardReply_"+obj['chat_parent_id']).prepend(str);
	}
	lts = Math.max(lts, Number(obj["timestamp"]));
}


</script>