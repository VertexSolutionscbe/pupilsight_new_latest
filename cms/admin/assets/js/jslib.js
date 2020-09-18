var regexp =  /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
$(document).ready(function(){


	$('#login').on('submit',(function(e) {
	
alert("gsfxgsfg");
	}));





 $('#add_blog').on('submit',(function(e) {
	
    e.preventDefault();
	var formData = new FormData(this);
	
    var ename=$('.blogTitle').val();
    var event_img=$('.blog_img').val();
    var  short_desc=$('.shortDesc').val();
    // var event_des=$('.description').val();
	var event_des = $("#description").code();
	formData.append('Des1',event_des);
    var titleErr,imgErr,shortDescErr,descErr;    

    if (!ename.match(/\S/)) {
       
        titleErr=1;
			$('.blogTitle').addClass('val_err');
		} else {
			titleErr=0;
			$('.blogTitle').removeClass('val_err');
		}
		if (event_img=='') {
			imgErr=1;
			$('.fancy-file-upload').addClass('val_err');
		} else {
			imgErr=0;
			$('.fancy-file-upload').removeClass('val_err');
        }
     
        if (event_des.trim()=='') {
			descErr=1;
			$('.note-editable').addClass('val_err');
		} else {
			descErr=0;
			$('.note-editable').removeClass('val_err');
		}

		
    if(titleErr==0 && imgErr==0  && descErr==0  ){
		// alert("Everything is fine");
	$.ajax({
		url: "ajax/ajax_calls.php", // Url to which the request is send
		type: "POST",             // Type of request to be send, called as method
		data: formData, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
		contentType: false,       // The content type used when sending data to the server.
		cache: false,             // To unable request pages to be cached
		processData:false,        // To send DOMDocument or non processed data file it is set to false
		success: function(data)   // A function to be called if request succeeds
		{
		  alert(data);
		
			setTimeout(function() 
			{
				window.location.href = "viewBlogs.php";
				//Refresh page
			}, 1000);

		//   $(".MessDisplay").html(data);
		//   $('.hi_alert').delay(5000).fadeOut('slow');
		//   $('#add_events')[0].reset();
		}
	});
}
else{
	// alert("hiii");
}
}));


$('#update_blog').on('submit',(function(e) {
	
    e.preventDefault();
	var formData = new FormData(this);
	
    var ename=$('.blogTitle').val();
    // var event_img=$('.blog_img').val();

	var event_des = $("#description").code();
	formData.append('Des2',event_des);
    // var event_des=$('.description').val();
   
    var titleErr,imgErr,shortDescErr,descErr;    

    if (!ename.match(/\S/)) {
       
        titleErr=1;
			$('.blogTitle').addClass('val_err');
		} else {
			titleErr=0;
			$('.blogTitle').removeClass('val_err');
		}
	
        if (event_des.trim()=='') {
			descErr=1;
			$('.description').addClass('val_err');
		} else {
			descErr=0;
			$('.description').removeClass('val_err');
		}

		
    if(titleErr==0  && descErr==0  ){
		// alert("Everything is fine");
	$.ajax({

		url: "ajax/ajax_calls.php", // Url to which the request is send
		type: "POST",             // Type of request to be send, called as method
		data: formData, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
		contentType: false,       // The content type used when sending data to the server.
		cache: false,             // To unable request pages to be cached
		processData:false,        // To send DOMDocument or non processed data file it is set to false
		success: function(data)   // A function to be called if request succeeds
		{
			
			alert(data);
		
			setTimeout(function() 
			{
				window.location.href = "viewBlogs.php";
				//Refresh page
			}, 1000);

		//   $(".MessDisplay").html(data);
		//   $('.hi_alert').delay(5000).fadeOut('slow');
		//   $('#update_blog').reset();
		}
	});
}
else{
	// alert("hiii");
}
}));


// Video Adding Code Start Here

$('#add_video').on('submit',(function(e) {

    e.preventDefault();
	var formData = new FormData(this);
	
    var vlink=$('.videoLink').val();
    

    var vlinkErr;    

    if (!vlink.match(/\S/)) {
       
        vlinkErr=1;
			$('.videoLink').addClass('val_err');
		} else {
			vlinkErr=0;
			$('.videoLink').removeClass('val_err');
		}
	
       
     

		
    if(vlinkErr==0 ){
		// alert("Everything is fine");
	$.ajax({
		url: "ajax/ajax_calls.php", // Url to which the request is send
		type: "POST",             // Type of request to be send, called as method
		data: formData, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
		contentType: false,       // The content type used when sending data to the server.
		cache: false,             // To unable request pages to be cached
		processData:false,        // To send DOMDocument or non processed data file it is set to false
		success: function(data)   // A function to be called if request succeeds
		{
		  alert(data);
		
			setTimeout(function() 
			{
				window.location.reload();
				//Refresh page
			}, 1000);

		//   $(".MessDisplay").html(data);
		//   $('.hi_alert').delay(5000).fadeOut('slow');
		//   $('#add_events')[0].reset();
		}
	});
}
else{
	// alert("hiii");
}
}));


});





$(document).on('click','.del_Video',function(){
    var id=$(this).attr('data-id');
    var img=$(this).attr('data-img');
    var type="del_Video";
    var r = confirm("Are you sure want to delete ?");
   if (r == true) {
       $.ajax({
       type      : 'POST',
       crossDomain : true,
       url       : 'ajax/ajax_calls.php', 
       data      : {type:type,img:img,id:id},
       success   : function(data) {
       if(data==1){
          location.reload();
       }else {
           alert(data);
       }
       }
       });
   }
});


$(document).on('click','.blod_del',function(){
    var id=$(this).attr('data-id');
    var img=$(this).attr('data-img');
    var type="blod_del";
    var r = confirm("Are you sure want to delete ?");
   if (r == true) {
       $.ajax({
       type      : 'POST',
       crossDomain : true,
       url       : 'ajax/ajax_calls.php', 
       data      : {type:type,img:img,id:id},
       success   : function(data) {
       if(data==1){
          location.reload();
       }else {
           alert(data);
       }
       }
       });
   }
});
$(document).on('click','.view_video',function(){
    var id=$(this).attr('data-id');
    var type="view_video";
       $.ajax({
       type      : 'POST',
       crossDomain : true,
       url       : 'ajax/ajax_calls.php', 
       data      : {type:type,id:id},
       success   : function(data) {
         $('.replace_datanew').html(data);
       }
       });
});


$(".char-textarea").on("keyup",function(event){
  checkTextAreaMaxLength(this,event);
});

/*
Checks the MaxLength of the Textarea
-----------------------------------------------------
@prerequisite:	textBox = textarea dom element
				e = textarea event
                length = Max length of characters
*/
function checkTextAreaMaxLength(textBox, e) { 
    
    var maxLength = parseInt($(textBox).data("length"));
    
  
    if (!checkSpecialKeys(e)) { 
        if (textBox.value.length > maxLength - 1) textBox.value = textBox.value.substring(0, maxLength); 
   } 
  $(".char-count").html(textBox.value.length);
    
    return true; 
} 
/*
Checks if the keyCode pressed is inside special chars
-------------------------------------------------------
@prerequisite:	e = e.keyCode object for the key pressed
*/
function checkSpecialKeys(e) { 
    if (e.keyCode != 8 && e.keyCode != 46 && e.keyCode != 37 && e.keyCode != 38 && e.keyCode != 39 && e.keyCode != 40) 
        return false; 
    else 
        return true; 
}
