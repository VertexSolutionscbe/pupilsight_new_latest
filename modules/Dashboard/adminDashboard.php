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

    <div class="container mt-4 " style="background-color: #F9FAFA;height:1170px; border-radius: 20px" >
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
	
	<div style="width:625px;height:218px;background-color:#FFFFFF;border-radius:20px;margin-top:20px;padding-left:20px">
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
	
	<div style="width:625px;height:218px;background-color:#FFFFFF;border-radius:20px;margin-top:20px;padding-left:20px">
	<div class="row2CardHead" style="padding-top:20px">Student Performance trends</div>	
	<div class="row2CardSubHead "style="padding-top:10px">2017-2018</div>
	<div class="row">
	<div class="col-2"><img style="margin-left:15px;margin-top:20px" src="./images/Challenge Medal.png"></img></div> 
	<div class="col-2 col-md2" style="margin-top:30px">
	<span class="row2CardHead aside1" >Habitual Performar</span> <br> 
	</div>
		
	<div class="col-2"><img style="margin-left:15px;margin-top:20px" src="./images/Top Student.png"></img></div>      
	<div class="col-2 col-md2" style="margin-top:30px">
	<span class="row2CardHead aside1" >Rising Stars</span>
	</div>
	
	<div class="col-2"><img style="margin-left:15px;margin-top:20px" src="./images/r3c1img3.png"></img></div>                   
	<div class="col-2 col-md2" style="margin-top:30px">
	<span class="row2CardHead aside1" >Needs attention</span>
	</div>
	</div>
	</div>
	

	</div>
	</div><!--Populate End -->

           
            
<script type="text/javascript" src="app.js"></script>     
</body>
 

</html>