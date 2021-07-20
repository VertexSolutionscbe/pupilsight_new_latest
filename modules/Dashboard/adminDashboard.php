<?php 
session_start();
error_reporting(0);


 ?>
<!DOCTYPE html>


<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" href="icon1.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
        integrity="sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw=="
        crossorigin="anonymous" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"> </script>
    	<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
	<link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
	<link href='https://fonts.googleapis.com/css?family=DM Sans' rel='stylesheet'>
    <style type="text/css">
    
    
        .discoverimageContent {
        font-family: Poppins;
        font-style: normal;
        font-weight: 400;
        font-size: 12px;
        line-height: 12px;
        color: #171725;
        text-align: center;

    }

        .smbox {
            height: 60px;
            width: 100%;
            background-color: white;
            box-shadow: 10px 10px 8px rgb(168, 167, 167);
            padding: 1px;
            float: right 20px;
        }

        .darkfont {
            font-family: Roboto;
            font-style: normal;
            font-weight: 900;
            font-size: 30px;
            line-height: 35px;
            letter-spacing: 0.1px;

            color: #000000;
        }

        .date {
            font-family: Roboto;
            font-style: normal;
            font-weight: normal;
            font-size: 24px;
            line-height: 28px;
            letter-spacing: 0.1px;

            color: #000000;

        }




        body {
            font-family: 'Lato', sans-serif;
            background-color: #FFFFFF;
        }

        .navbar {
            background-color: rgb(251, 251, 251);
            box-shadow: 0px 15px 15px 0px rgba(240, 240, 240, 0.9);
        }

        .dropdown-menu {
            background-color: rgb(251, 251, 251);
            border-radius: 0px;
        }

        .dropdown-item {
            color: black;
            background-color: rgb(251, 251, 251);
            transition: 0.1s;
            padding-top: 12px;
            padding-bottom: 12px;
        }

        .dropdown-item:hover {
            color: white;
            background-color: rgb(0, 90, 192);
            transform: scale(1.1);
        }

        .follow-hover {
            color: white;
            transition: 0.3s;
        }

        .follow-hover:hover {
            color: rgb(173, 173, 173);
        }

        .date {
            font-family: Roboto;
            font-style: normal;
            font-weight: 500;
            font-size: 16px;
            line-height: 26px;
            /* or 162% */

            letter-spacing: 0.1px;

            color: #44444F;

        }
		
		.modelbody{
    height: 550px;
    overflow: hidden;
    float: left;
}
@media only screen and (max-width: 600px) {
    .aside{
        width: 50%;
        display: none;
    }

}

.modelbody:hover{overflow-y:auto;}
		
@media only screen and (max-width: 600px) {
    .aside1{
     padding:70px;
    }

}
		
.overall{
font-family: DM Sans;
font-style: normal;
font-weight: bold;
font-size: 16px;
line-height: 22px;
letter-spacing: -0.4px;
color: #171717;
}
.cardContent1{
font-family: DM Sans;
font-style: normal;
font-weight: bold;
font-size: 30px;
line-height: 36px;
text-align: center;
letter-spacing: -0.4px;
}
.cardContent2{
font-family: DM Sans;
font-style: normal;
font-weight: bold;
font-size: 16px;
line-height: 18px;
text-align: center;
color: #8F92A1;
}
.row2CardHead{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 16px;
line-height: 24px;
letter-spacing: 0.1px;
color: #171725;
}
.row2CardSubHead{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 14px;
line-height: 16px;
letter-spacing: 0.1px;
color: #696974;
}
.row2CardimageSubHead{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 14px;
line-height: 16px;
letter-spacing: 0.1px;
color: #696974;

}
.leftcard1date{
font-family: Poppins;
font-style: normal;
font-weight: bold;
font-size: 20px;
line-height: 30px;
text-align: center;
color: #0062FF;

}
.leftcard1title{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 14px;
line-height: 21px;
letter-spacing: 0.1px;
color: #171725;
}
.buttonFont{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 14px;
line-height: 21px;
}
.leftcard3title{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 16px;
line-height: 24px;
letter-spacing: 0.1px;
color: #171725;
}
.leftcard3Tabletitle{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 11px;
line-height: 16px;
letter-spacing: 0.8px;
text-transform: uppercase;
color: #44444F;
}
.leftcard3Tablecontent1{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 14px;
line-height: 21px;
letter-spacing: 0.1px;
color: #171725;
}
.leftcard3Tablecontent2{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 14px;
line-height: 24px;
letter-spacing: 0.1px;
color: #92929D;
}
.leftcard3Tablecontent3{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 14px;
line-height: 16px;
letter-spacing: 0.1px;
color: #0062FF;
}
.leftcard3Tablecontent4{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 14px;
line-height: 24px;
letter-spacing: 0.1px;
color: #44444F;
}

.rightCard1Cont1{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 14px;
line-height: 16px;
letter-spacing: 0.1px;
color: #FFFFFF;
}

.rightCard1Cont2{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 14px;
line-height: 21px;
letter-spacing: 0.1px;
color: #FFFFFF;
}



    </style>
    <link rel="stylesheet" href="app.css">
	
	

<script type="text/javascript">

function UpdateChild(){ 
      var inputValue1 = document.getElementById("myDropDown");
	  var YearId=document.getElementById("yearid").value;
	  var inputValue =inputValue1.value;
      var taskname="SetSession";	  
$.ajax({
      type:'post',
        url:'parentDashboardAjax.php',// put your real file name 
		data: {inputValue: inputValue,YearId:YearId,taskname:taskname},
        success:function(msg){
		//	alert(msg);
		$("#populate").html(msg);
       }
 });
}


function UpdateChildonLoad(){ 
      //var inputValue1 = 
	  var inputValue =<?php echo $_SESSION['ChildId'] ?>;
	  var YearId=<?php echo $_SESSION[$guid]['pupilsightSchoolYearID']; ?>;
      var taskname="SetSession";	  
$.ajax({
      type:'post',
        url:'parentDashboardAjax.php',// put your real file name 
		data: {inputValue: inputValue,YearId:YearId,taskname:taskname},
        success:function(msg){
		//	alert(msg);
		$("#populate").html(msg);
       }
 });
}
</script>
</head>

<body >
    


	<div id="populate" name="populate">

    <div class="container mt-4 " style="background-color: #F9FAFA;height:auto; border-radius: 20px;padding-background-bottom:20px" >
	<p class="overall" style="padding-top:20px">OVERALL - HOME</p>
	
	<div class="row" style="padding-left:15px">
	
	<div style="height:218px;width:262px;background-color:#FFFFFF;border-radius:20px">
	<div style="text-align:center;padding-top:10%"><img  src="./images/adminCard1-icon.png"></img></div>
	<div class="cardContent1" style="margin-top:20px">44</div>
	<div class="cardContent2" style="margin-top:10px">MY SCHOOL</div>
	</div>
	
	<div style="height:218px;width:262px;background-color:#FFFFFF;border-radius:20px;margin-left:20px">
	<div style="height:218px;width:262px;background-color:#FFFFFF;border-radius:20px">
	<div style="text-align:left;padding-top:10%;padding-left:10%"><img  src="./images/adminCard1-icon2.png"></img></div>
	<div class="cardContent1" style="margin-top:20px">44</div>
	<div class="cardContent2" style="margin-top:10px">ADMISSION</div>
	</div>
	</div>	
	
	<div style="height:218px;width:262px;background-color:#FFFFFF;border-radius:20px;margin-left:20px">
	<div style="height:218px;width:262px;background-color:#FFFFFF;border-radius:20px">
	<div style="text-align:left;padding-top:10%;padding-left:10%"><img  src="./images/adminCard1-icon3.png"></img></div>
	<div class="cardContent1" style="margin-top:20px">44</div>
	<div class="cardContent2" style="margin-top:10px">FEE COLLECTION</div>
	</div>
	</div>
	
	<div style="height:218px;width:262px;background-color:#FFFFFF;border-radius:20px;margin-left:20px">
	<div style="height:218px;width:262px;background-color:#FFFFFF;border-radius:20px">
	<div style="text-align:left;padding-top:10%;padding-left:10%"><img  src="./images/adminCard1-icon4.png"></img></div>
	<div class="cardContent1" style="margin-top:20px">44</div>
	<div class="cardContent2" style="margin-top:10px">STAFF INFORMATION</div>
	</div>
	</div>
	
    </div>

<style>
@media only screen and (max-width: 600px) {
    .row1card1{
width:625px;height:218px;background-color:#FFFFFF;border-radius:20px;margin-top:20px;padding-left:20px
    }

}
</style>
	<div class="container h-100 row1card1">
	<div class="row align-items-center h-100">
	<div class="col-6 mx-auto">
	<div  style="width:625px;height:218px;background-color:#FFFFFF;border-radius:20px;margin-top:20px;padding-left:20px">
	<div class="row2CardHead" style="padding-top:20px">Academic Performance</div>	
	<div class="row2CardSubHead "style="padding-top:10px">2017-2018</div>
	<div class="row">
	<div class="col-2"><img style="margin-left:15px;margin-top:20px" src="./images/r2c1img1.png"></img></div> 
	<div class="col-2 col-md2" style="margin-top:30px">
	<span class="row2CardHead aside1" >68</span> <br> 
	<span class="row2CardimageSubHead aside1" >Avg Score</span>
	</div>
		
	<div class="col-2"><img style="margin-left:15px;margin-top:20px" src="./images/r2c1img2.png"></img></div>      
	<div class="col-2 col-md2" style="margin-top:30px">
	<span class="row2CardHead aside1" >87</span> <br> 
	<span class="row2CardimageSubHead aside1" >Max Score</span>
	</div>
	
	<div class="col-2"><img style="margin-left:15px;margin-top:20px" src="./images/r2c1img3.png"></img></div>                   
	<div class="col-2 col-md2" style="margin-top:30px">
	<span class="row2CardHead aside1" >08</span> <br> 
	<span class="row2CardimageSubHead aside1" >Min Score</span>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	
		<div class="container h-100 row1card1">
	<div class="row align-items-center h-100">
	<div class="col-6 mx-auto">
	<div style="width:625px;height:218px;background-color:#FFFFFF;border-radius:20px;margin-top:20px;padding-left:20px">
	<div class="row2CardHead" style="padding-top:20px">Student Performance trends</div>	
	<div class="row2CardSubHead "style="padding-top:10px">2017-2018</div>
	<div class="row mt-1">
	<div class="col-1.5"><img style="margin-top:20px" src="./images/Challenge Medal.png"></img></div> 
	<div class="col-2 col-md2" style="margin-top:30px">
	<span class="row2CardHead aside1" >Habitual Performar</span> <br> 
	</div>
		
	<div class="col-1.5"><img style="margin-top:20px" src="./images/Top Student.png"></img></div>      
	<div class="col-2 col-md2" style="margin-top:30px">
	<span class="row2CardHead aside1" >Rising Stars</span>
	</div>
	
	<div class="col-1.5"><img style="margin-top:20px" src="./images/r3c1img3.png"></img></div>                   
	<div class="col-2 col-md2" style="margin-top:30px">
	<span class="row2CardHead aside1" >Needs attention</span>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	
	<div class="row">
	<div class="col-6" > 
	<div class="col-12">
			<div  style="width:522px;height:auto;background-color:#FFFFFF;border-radius:20px;margin-top:20px;padding-left:30px;padding-bottom:20px;">
		<div style="padding-top:10px">Upcoming Events</div>
		
		<div class="row mt-2" style="width:479px;height:90px;background-color:#FAFAFB;border-radius:10px;padding-top:30px">
		<span class="col-3 leftcard1date">23 Sep </span>
		<span class=" col-6 leftcard1title">Parent Teacher Meeting</span>
		<button  style="background-color:#E1EBFB;color:#0062FF;border-radius:5px;width:103px;height:30px;border-color:#E1EBFB">See Detail</button>
		</div>
		
		<div class="row mt-2" style="width:479px;height:90px;background-color:#FAFAFB;border-radius:10px;padding-top:30px">
		<span class="col-3 leftcard1date">23 Sep </span>
		<span class=" col-6 leftcard1title">Parent Teacher Meeting</span>
		<button  style="background-color:#E1EBFB;color:#0062FF;border-radius:5px;width:103px;height:30px;border-color:#E1EBFB">See Detail</button>
		</div>
		
		<div class="row mt-2" style="width:479px;height:90px;background-color:#FAFAFB;border-radius:10px;padding-top:30px">
		<span class="col-3 leftcard1date">23 Sep </span>
		<span class=" col-6 leftcard1title">Parent Teacher Meeting</span>
		<button  style="background-color:#E1EBFB;color:#0062FF;border-radius:5px;width:103px;height:30px;border-color:#E1EBFB">See Detail</button>
		</div>

	</div>
	
	<div  style="width:522px;height:auto;background-color:#FFFFFF;border-radius:20px;margin-top:20px;padding-left:30px;padding-bottom:20px;">
		<div style="padding-top:10px">Upcoming Birthdays</div>
		
		<div class="row mt-2" style="width:479px;height:90px;background-color:#FAFAFB;border-radius:10px;padding-top:30px">
		<span class="col-3 leftcard1date">23 Sep </span>
		<span class=" col-6 leftcard1title">Ramkrishna</span>
		<button  style="background-color:#E1EBFB;color:#0062FF;border-radius:5px;width:103px;height:30px;border-color:#E1EBFB">See Detail</button>
		</div>
		
		<div class="row mt-2" style="width:479px;height:90px;background-color:#FAFAFB;border-radius:10px;padding-top:30px">
		<span class="col-3 leftcard1date">23 Sep </span>
		<span class=" col-6 leftcard1title">Ramkrishna</span>
		<button  style="background-color:#E1EBFB;color:#0062FF;border-radius:5px;width:103px;height:30px;border-color:#E1EBFB">See Detail</button>
		</div>
		
		<div class="row mt-2" style="width:479px;height:90px;background-color:#FAFAFB;border-radius:10px;padding-top:30px">
		<span class="col-3 leftcard1date">23 Sep </span>
		<span class=" col-6 leftcard1title">Ramkrishna</span>
		<button  style="background-color:#E1EBFB;color:#0062FF;border-radius:5px;width:103px;height:30px;border-color:#E1EBFB">See Detail</button>
		</div>

	</div>
	
		<div  style="width:522px;height:auto;background-color:#FFFFFF;border-radius:20px;margin-top:20px;padding-left:30px;padding-bottom:20px;">
		<div class="row" style="padding-top:10px" >
		<div class="col-7 leftcard3title">
		To do list
		</div>
		<div>
		<button class="buttonFont" style="background-color:#0062FF;border-color:#0062FF;color:white;border-radius:5px;width:160px;height:30px">Create New Task</button>
		</div>
		</div>
		
		<div class="row" style="margin-top:20px;background-color:#F9FAFA" >
		<div class="col-1 leftcard3Tabletitle">
	
		</div>
		<div class="col-3 leftcard3Tabletitle">
		To do Item
		</div>
		<div class="col-3 leftcard3Tabletitle">
		Assigned By
		</div>
		<div class="col-2 leftcard3Tabletitle">
		Due Date
		</div>
		<div class="col-2 leftcard3Tabletitle">
		Status
		</div>
		</div>

<hr style="width:106%;margin-left:-30px"> 		
		
		<div class="row">
		<div>
		<button  class="btn btn-bprimary rounded-circle btn-sm"  style="background-color:#3DD598;color:#FFFFFF;height:30px;width:30px"><img src="./images/check.png" /></button>
		</div>
		<div class="col-3 leftcard3Tablecontent1">	To do list Item	</br><span class="leftcard3Tablecontent2">Hey Cak, Could you free now?</span></div>
		<div class="col-3 leftcard3Tablecontent3">	Ramakrishna</div>
		<div class="col-2 leftcard3Tablecontent4">	Mar 21, 2019</div>
		<div class="col-3 "><button  class="btn btn-bprimary"  style="background-color:#E6F6F0;color:#3DD598;border-radius:5px">Completed</button></div>
		
				<hr style="width:100%;margin-left:-13px"> 	

		</div>
		
		
		<div class="row">
		<div>
		<button  class="btn btn-bprimary rounded-circle btn-sm"  style="background-color:#3DD598;color:#FFFFFF;height:30px;width:30px"><img src="./images/check.png" /></button>
		</div>
		<div class="col-3 leftcard3Tablecontent1">	To do list Item	</br><span class="leftcard3Tablecontent2">Hey Cak, Could you free now?</span></div>
		<div class="col-3 leftcard3Tablecontent3">	Ramakrishna</div>
		<div class="col-2 leftcard3Tablecontent4">	Mar 21, 2019</div>
		<div class="col-3 "><button  class="btn btn-bprimary"  style="background-color:#E6F6F0;color:#3DD598;border-radius:5px">Completed</button></div>
		
				<hr style="width:100%;margin-left:-13px"> 	

		</div>
		</div>
	</div>
	</div>
		
		
<div class="col-6" >
<div class="col-12">
	
	<div  style="width:522px;height:auto;background-color:#FFFFFF;border-radius:20px;margin-top:20px;padding-left:30px;padding-bottom:20px;">
		<div style="padding-top:10px"><button type= "button" class="btn" style="background-color:#1E75FF;color:#FFFFFF;border-radius:5px">CREATE NEW MESSAGE</button></div>
		
			<div class="row mt-2" style="width:100%;height:132px;background-color:#1E75FF;border-radius:10px;padding-top:10px">
			<div class="row" style="margin-left:10px">
			<div ><button  class="btn btn-warning rounded-circle "  style="color:#FFFFFF">M</button></div>			
			<div class="rightCard1Cont1" style="margin-left:10px">Any Carrol</br><span class="rightCard1Cont2">Mobile Apps Red</span></div>			
			</div>
		</div>
	
	</div>


</div>	
</div>


</div>
	
	


	</div>
	</div><!--Populate End -->

           
            
<script type="text/javascript" src="app.js"></script>     
</body>
 

</html>