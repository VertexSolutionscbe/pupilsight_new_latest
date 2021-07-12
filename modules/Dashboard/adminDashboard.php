<?php 
session_start();
error_reporting(0);
include("dbinfo.php");
require_once '../../pupilsight.php';
$_SESSION[$guid]['pupilsightRoleIDPrimary'];
$cuid=trim($_SESSION[$guid]['pupilsightPersonID']);
$yearid=$_SESSION[$guid]['pupilsightSchoolYearID'];


$sql = "SELECT pupilsightPerson.*, futureYearsLogin, pastYearsLogin FROM pupilsightPerson LEFT JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE (pupilsightPersonID='".$cuid."')";
$Esi=mysqli_query($conn,$sql);
$Fsi=mysqli_fetch_array($Esi);

$sqlf = 'SELECT pupilsightFamilyID FROM pupilsightFamilyAdult WHERE pupilsightPersonID= ' . $cuid . ' ';
$Esqlf=mysqli_query($conn,$sqlf);
$Fsqlf=mysqli_fetch_array($Esqlf);
$pupilsightFamilyID=$Fsqlf['pupilsightFamilyID'];

$childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b 
ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" ';
$Echilds=mysqli_query($conn,$childs);

$EchildsDetails=mysqli_query($conn,$childs);
$Fchilds=mysqli_fetch_array($EchildsDetails);
$_SESSION['ChildName']=$Fchilds['officialName'];
$_SESSION['ChildId']=$Fchilds['pupilsightPersonID'];


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
		

		
.parentArea{
font-family: Poppins;
font-style: normal;
font-weight: bold;
font-size: 16px;
line-height: 28px;
/* or 175% */
color: #43484B;
}
.rahulare{
font-family: Roboto;
font-style: normal;
font-weight: 900;
font-size: 30px;
line-height: 35px;
letter-spacing: 0.1px;
color: #000000;
}
.selectChildarea{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 18px;
line-height: 21px;
letter-spacing: 0.1px;
}
.childNameArea{
font-family: Poppins;
font-style: normal;
font-weight: bold;
font-size: 16px;
line-height: 28px;
}
.todayArea{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 24px;
line-height: 28px;
letter-spacing: 0.1px;
color: #000000;
}

.subjectArea{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 16px;
line-height: 19px;
letter-spacing: 0.1px;
color: #44444F;
}
.subjectContentArea{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 14px;
line-height: 24px;
letter-spacing: 0.1px;
color: #92929D;

}
.discover{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 24px;
line-height: 28px;
letter-spacing: 0.1px;
color: #000000;
/*width: 408px;
left: 126px;
top: 1206px;*/

}
.discoverimageContent{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 12px;
line-height: 18px;
color: #171725;
text-align:center;

}
.eventInvitation{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 12px;
line-height: 14px;
color: #0062FF;
}
.someHeadingContent{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 14px;
line-height: 22px;
letter-spacing: 0.1px;
color: #171725;
}
.someHeadingdown{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 14px;
line-height: 22px;
letter-spacing: 0.1px;
color: grey;
}
.someHeadingdown1{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 14px;
line-height: 22px;
letter-spacing: 0.1px;
color: #44444F;
}
.someHeadingdown2{
font-family: Roboto;
font-style: normal;
font-weight: bold;
font-size: 12px;
line-height: 14px;
color: #92929D;
}
.someHeadingdown3{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 12px;
line-height: 14px;
color: #FC5A5A;
}
.timePeriodArea{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 12px;
line-height: 18px;
letter-spacing: 0.1px;
color: #171725;
}
.dayArea{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 12px;
line-height: 18px;
text-align: center;
letter-spacing: 0.857143px;
text-transform: uppercase;
color: #92929D;
}
.monthArea{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 18px;
line-height: 27px;
letter-spacing: 0.1px;
color: #171725;
}
.dateArea{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 16px;
line-height: 52px;
text-align: center;
}
.chatHeadArea1{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 12px;
line-height: 14px;
color: #0062FF;
}
.chatHeadArea2{
font-family: Poppins;
font-style: normal;
font-weight: 600;
font-size: 14px;
line-height: 22px;
letter-spacing: 0.1px;
color: #171725;
}
.chatDaysArea{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 14px;
line-height: 16px;
letter-spacing: 0.1px;
color: #92929D;
}
.chatSubjectArea{
font-family: Roboto;
font-style: normal;
font-weight: normal;
font-size: 14px;
line-height: 22px;
letter-spacing: 0.1px;
color: #44444F;

}
.overall{
font-family: DM Sans;
font-style: normal;
font-weight: bold;
font-size: 16px;
line-height: 22px;
/* identical to box height, or 137% */

letter-spacing: -0.4px;

/* Black/B100 */

color: #171717;

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

	
    <div class="container mt-4 " style="background-color: #E5E5E5;height:1170px; border-radius: 20px;" >
	<p class="overall">OVERALL - HOME</p>
        <div class="row" style="width:100%">

            <div class="col-sm-6 " id="myScrollspy">
               <img src="./images/Row 1.png"></img>
            </div>
         
        </div>
    </div>

			</div> <!--Populate End -->

           
            
<script type="text/javascript" src="app.js"></script>     
</body>
 

</html>