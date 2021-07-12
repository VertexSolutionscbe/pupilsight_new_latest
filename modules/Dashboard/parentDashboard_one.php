<?php 
session_start();
error_reporting(0);
include("dbinfo.php");
require_once '../../pupilsight.php';
$_SESSION[$guid]['pupilsightRoleIDPrimary'];
$cuid=trim($_SESSION[$guid]['pupilsightPersonID']);


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
            background-color: #FAFAFB;
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
    </style>
    <link rel="stylesheet" href="app.css">
	
	

<script type="text/javascript">

function UpdateChild(){ 
      var inputValue1 = document.getElementById("myDropDown");
	  var inputValue =inputValue1.value;
      var taskname="SetSession";	  
$.ajax({
      type:'post',
        url:'parentDashboardAjax.php',// put your real file name 
		data: {inputValue: inputValue,taskname:taskname},
        success:function(msg){
		//	alert(msg);
		$("#populate").html(msg);
       }
 });
}


function UpdateChildonLoad(){ 
      //var inputValue1 = 
	  var inputValue =<?php echo $_SESSION['ChildId'] ?>;
      var taskname="SetSession";	  
$.ajax({
      type:'post',
        url:'parentDashboardAjax.php',// put your real file name 
		data: {inputValue: inputValue,taskname:taskname},
        success:function(msg){
		//	alert(msg);
		$("#populate").html(msg);
       }
 });
}
</script>
</head>

<body >
    
    <div class="navbar navbar-expand-lg navbar-light ">

        <ul class="nav">
            <li class="nav-item">
                <button class="navbar-toggler float-sm-left mr-2" type="button" data-toggle="collapse"
                    data-target="#collapsibleNavbar">
                    <span class="navbar-toggler-icon"></span>

                </button>
            </li>
            <li class="nav-item">
                <img src="parent.jpg" class="rounded-circle  navbar-toggler float-sm-left mr-2" data-toggle="collapse"
                    alt="Cinque Terre" width="40" height="40">
            </li>
						<?php 
$childs1 = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b 
ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" ';
$Echilds1=mysqli_query($conn,$childs1);
			?>
            <li class="nav-item border-0">
                <form action="/" class="navbar-toggler mr-2 outline-danger" data-toggle="collapse">
                    <select name="cars" style="border: none; background-color:white; overflow: hidden;">
                       <?php 
						while($Fsi1=mysqli_fetch_array($Echilds1))
						{
						?>
                            <option value="<?php echo $Fsi1['pupilsightPersonID']; ?>"><?php echo $Fsi1['officialName']; ?></option>
                            
						<?php } ?>

                    </select>

                </form>
            </li>
        </ul>





        <div class="navbar-collapse collapse" id="collapsibleNavbar">
            <ul class="navbar-nav ml-auto">


                <li class="nav-item">
                    <a href="#"> <img src="parent.jpg" class="rounded-circle" alt="Cinque Terre" width="40" height="40">
                    </a>
                </li>
                <li class="nav-item ">

                    <a class="nav-link mt-2" href="#" style="font-family: Poppins;
                    font-style: normal;
                    font-weight: bold;
                    font-size: 16px;
                    line-height: 28px;"><?php echo $Fsi['preferredName']; ?></a>
                </li>
            </ul>
        </div>
    </div>

	<div id="populate" name="populate">
    <div class="container-lg">
        <div class="row">
            <div class="col-sm-6" id="childarea">
                <div class="rahulare mt-5" ><?php echo $_SESSION['ChildName']; ?>'s Area</div>
            </div>

            <div class="col-sm-6 aside">
                <div class="float-right mt-4 selectChildarea" >Select Child
                    <form action="/" class="ml-2 mt-lg-3">
                        <select name="myDropDown" id="myDropDown" style="border: none; background-color: #FAFAFB; overflow: hidden; " onchange="UpdateChild(this.value)">
						<?php 
						while($Fsi=mysqli_fetch_array($Echilds))
						{
						?>
                            <option value="<?php echo $Fsi['pupilsightPersonID']; ?>"><?php echo $Fsi['officialName']; ?></option>
                            
						<?php } ?>
                        </select>
                    </form>             
                </div>
                <a href="http://localhost/pupilsight_new/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=<?php echo $_SESSION['ChildId'] ?>"><img src="parent.jpg" class="rounded-circle float-right mt-lg-5 " alt="Cinque Terre" width="40" height="40"></a>
            </div>

        </div>
        <hr>
        <div class="row">
            <div class="col-sm-12">
                <div class="todayArea">Today's Timetable</div>

            </div>
        </div>
    </div>
	
    <div class="container mt-5 " style="background-color: white; border-radius: 20px;" >
        <div class="row" style="width:100%">

            <div class="col-sm-6 " id="myScrollspy">
                <div class="light googul" style="display: flex; background-color: white;height:450px">
                    <div class="calendar " style="background-color: white;margin-top:20px " data-spy="scroll" data-target="#myScrollspy"
                        data-offset="1" >
                        <div class="calendar-header">
                            <span class="month-picker monthArea" id="month-picker">February </span>
                            <span id="year" class="monthArea">2021</span>
                            <hr style="width:50%;text-align:left;margin-left:0">
                            <div class="year-picker">
                                <span id="prev-year">
                                    <pre><img src="save.PNG" style="height: 38px;" alt=""></pre>
                                </span>
                                <span id="next-year">
                                    <pre><img src="save1.PNG" alt="" style="height: 38px;"></pre>
                                </span>
                            </div>
                        </div>
                        <div class="calendar-body">
                            <div class="calendar-week-day">
                                <div class="dayArea">Sun</div>
                                <div class="dayArea">Mon</div>
                                <div class="dayArea">Tue</div>
                                <div class="dayArea">Wed</div>
                                <div class="dayArea">Thu</div>
                                <div class="dayArea">Fri</div>
                                <div class="dayArea">Sat</div>
                            </div>
                            <a href="#section1" style="display: inline-block;">
                                <div class="calendar-days dateArea"></div>
                            </a>
                        </div>
                        <div class="calendar-footer">
                            <!-- <div class="toggle">
                                <span>Dark Mode</span>
                                <div class="dark-mode-switch">
                                    <div class="dark-mode-switch-ident"></div>
                                </div>
                            </div> -->
                        </div>
                        <div class="month-list"></div>
                    </div>

                    

                </div>
            </div>
            <div class="col-sm-6">
						<div class="modelbody">

                <div id="section1" style="margin-top:40px">
                    <div class="row">
                        <div class="col-sm-2">
                            <p style="width:80px"> <img src="box.PNG" style="height: 20px; width: 20px;" />1 july</p>

                        </div>
                        <div class=" timePeriodArea col-sm-3" align="right">
                            8-9AM

                        </div>
                        <div class="col-sm-7">
                            <h6 class="subjectArea">Subject 1</h6>
                            <p class="subjectContentArea">Contrary to popular belief, Lorem, Ipsum is not simply random text.</p>

                        </div>
                        <hr style="width:100%;text-align:left;margin-left:0">
                    </div>
                    <div class="row">
                        <div class="col-sm-2">


                        </div>
                        <div class="  col-sm-3">
                            <h6 class="timePeriodArea" align="right">9-10AM</h6>

                        </div>
                        <div class="col-sm-7">
                            <h6 class="subjectArea">Subject 2</h6>
                            <p class="subjectContentArea">Contrary to popular belief, Lorem, Ipsum is not simply random text.</p>

                        </div>
                        <hr style="width:100%;text-align:left;margin-left:0">
                    </div>
                    <div class="row">
                        <div class="col-sm-2">


                        </div>
                        <div class="  col-sm-3">
                            <h6 class="timePeriodArea" align="right">10-10.30AM</h6>

                        </div>
                        <div class="col-sm-7">
                            <h6 class="subjectArea">Subject 3</h6>
                            <p class="subjectContentArea">Contrary to popular belief, Lorem, Ipsum is not simply random text. </p>

                        </div>
                        <hr style="width:100%;text-align:left;margin-left:0">
                    </div>                    
					<div class="row">
                        <div class="col-sm-2">


                        </div>
                        <div class="  col-sm-3">
                            <h6 class="timePeriodArea" align="right">10.30-11AM</h6>

                        </div>
                        <div class="col-sm-7">
                            <h6 class="subjectArea">Subject 4</h6>
                            <p class="subjectContentArea">Contrary to popular belief, Lorem, Ipsum is not simply random text. </p>

                        </div>
                        <hr style="width:100%;text-align:left;margin-left:0">
                    </div>
					
					<div class="row">
                        <div class="col-sm-2">


                        </div>
                        <div class="  col-sm-3">
                            <h6 class="timePeriodArea" align="right">11-12PM</h6>

                        </div>
                        <div class="col-sm-7">
                            <h6 class="subjectArea">Subject 5</h6>
                            <p class="subjectContentArea">Contrary to popular belief, Lorem, Ipsum is not simply random text. </p>

                        </div>
                        <hr style="width:100%;text-align:left;margin-left:0">
                    </div>					
					<div class="row">
                        <div class="col-sm-2">


                        </div>
                        <div class="  col-sm-3">
                            <h6 class="timePeriodArea" align="right">12.30-2PM</h6>

                        </div>
                        <div class="col-sm-7">
                            <h6 class="subjectArea">Subject 6</h6>
                            <p class="subjectContentArea">Contrary to popular belief, Lorem, Ipsum is not simply random text. </p>

                        </div>
                        <hr style="width:100%;text-align:left;margin-left:0">
                    </div>
					
                    <div class="row">
                        <div class="col-sm-2">


                        </div>
                        <div class="  col-sm-3">
                        <h6 class="timePeriodArea"align="right">2-3 PM</h6>

                        </div>
                        <div class="col-sm-7">
                            <h6 class="subjectArea">Subject 7</h6>
                            <p class="subjectContentArea">Contrary to popular belief, Lorem, Ipsum is not simply random text.</p>

                        </div>
                        <hr style="width:100%;text-align:left;margin-left:0">
                    </div>


                </div>
            </div>

        </div>
        </div>
    </div>

    <section>
        <div class="container mt-5" id="discover">
            <div class="row">
                <div class="col-3 ">
                    <h4 class="discover">Discover</h4>
                </div>
            </div>
             <?php 
			 //$location="https://testchristacademy.pupilpod.net/index.php?q=";
			 $location="http://localhost/pupilsight_new/index.php?q=";
			 ?>
            <div class="row">
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Students/student_view_details.php&pupilsightPersonID=<?php echo $_SESSION['ChildId']; ?>"><img style="height: 61px; width: 62px;" src="./images/Calendar 1.png"></img></a> <br><span class="discoverimageContent" style="text-align:center">Timetable</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Academics/result.php"> <img style="height: 61px; width: 62px;" src="./images/Chart 1.png"></img></a><br><span class="discoverimageContent">Progress Report</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Messenger/messageWall_view.php"> <img style="height: 61px; width: 62px;" src="./images/circular.png"></img></a><br><span class="discoverimageContent">Circular</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Messenger/chat_message.php"> <img style="height: 61px; width: 62px;" src="./images/Message 1.png"></img></a><br><span class="discoverimageContent" >Chat</span></a></div> 
                <div class="col" style="text-align:center"> <img style="height: 61px; width: 62px;" src="./images/Design 1.png"></img></a><br><span class="discoverimageContent">Event</span></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Activities/activities_view.php&pupilsightPersonID=<?php echo $_SESSION['ChildId']; ?>"> <img style="height: 61px; width: 62px;" src="./images/Brainstorming 1.png"></img></a><br><span class="discoverimageContent">Activities</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Finance/invoice_child_view.php"> <img style="height: 61px; width: 62px;" src="./images/Printer 1.png"></img></a><br><span class="discoverimageContent">Fees invoice</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Finance/invoice_child_view.php"> <img style="height: 61px; width: 62px;" src="./images/Documents 1.png"></img></a><br><span class="discoverimageContent">Fees Receipt</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Students/student_view_details.php"> <img style="height: 61px; width: 62px;" src="./images/library.png"></img></a><br><span class="discoverimageContent">Library</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Attendance/report_studentHistory.php"> <img style="height: 61px; width: 62px;" src="./images/User 1.png"></img></a><br><span class="discoverimageContent">Attendance</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Transport/bus_manage.php"> <img style="height: 60px; width: 62px;" src="./images/Backpack 1.png"></img></a><br><span class="discoverimageContent">Transport</span></a></div> 
                <div class="col" style="text-align:center"> <img style="height: 61px; width: 62px;" src="./images/Graphic tablet 1.png"></img></a><br><span class="discoverimageContent">Learning</span></div> 
          </div>
          </div>
		   </section>
		   
		   <?php
			
	    	$marks = 'SELECT * FROM history_of_students_marks where pupilsightPersonID='.$_SESSION['ChildId'].'';
			$marks1=mysqli_query($conn,$marks);
			$marks2=mysqli_fetch_array($marks1);




		   ?>
		    <section>
            <div class="container  mt-5">
                <div class="row mt-3">
                    <div  class="col-12 discover"> <?php echo $_SESSION['ChildName']; ?>'s NewsFeed  </div>
                    </div>
                    
                    <div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    
                    <div class="col">
                    
                    <div class="row">
                    <div class="col-1"><img style="width:64px;height:64px;margin-left:15px;margin-top:20px" src="./Random_PP.png"></img></div>
                    
                    <div class="col-10" style="margin-top:30px">
                    <span class="eventInvitation">Event Invitation</span> <br> 
                    <span class="someHeadingContent">Some Heading Goes Here</span>
                    </div>
                    
                    <div class="col-12 col-md6"><img style="width:100%;height:221px;margin-top:14px" src="./Dashboard_img.png"></img></div>
                    </div>
                    
                    <div class="row mt-4">
                    <div class="col-12 col-md6 "><span class="someHeadingdown"> Some Headings For Invite that needsto be attended. </span></div>
                    </div>
                    <div class="row mt-2">
                    <div class="col-12 col-md12 "> <span class="someHeadingdown1">Every hero, Every story, Every moment has led us here. Marver Studios Avengers: ENDGAME is now playing in theater.</span> </div>
                    </div>  
					<div class="row mt-2">
                    <div class="col-12 col-md6 someHeadingdown2"> 32 Students Collected. <span class="someHeadingdown3">1 day left</span>  </div>
                    </div>
                    
                    
                    <div class="row mt-2" style="float:right;margin-right:10px;margin-bottom:10px">
              
                    
                    <div > 
                     <a href="#"><img  src="./images/see_calender.png"></img></a> 
					  <a href="#"><img style="margin-left:10px" src="./images/view_details.png"></img></a> 
                    </div>                    
			
                    
                     
                    </div>
                    
                    <div class="row mt-4">
                    <div class="col-12 col-md6 "></div>
                    </div>
                    
                    </div>
                    </div>
					
					
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">        
                    <div class="col">              					
					<?php 
					//$pupilsightPersonID=$_SESSION['ChildId'];
					$pupilsightPersonID="0000000001";
					$sms="SELECT * FROM pupilsightmessenger where sms='Y' AND pupilsightPersonID='".$pupilsightPersonID."' order by timestamp desc";
					$Qsms=mysqli_query($conn,$sms);
					$i=0;
					while($Fsms=mysqli_fetch_array($Qsms))
					{
						$i++;
						if($i==1)
						{
							$date1=strtotime($Fsms['messageWall_date1']);
							$date2=strtotime(date('Y-m-d'));
						    $diff = ($date2 - $date1)/60/60/24; 							
					?>
					<div class="row mt-4" style="width:100%">
                    <div class="col-11" ><span class="chatHeadArea1">SMS</span></div>
                    <div  style="float:right"><span  class="chatDaysArea"> <?php echo $diff; ?> days ago </span></div>
                    </div>
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="chatHeadArea2"><?php echo $Fsms['subject']; ?></span></div>
                    </div>
					 <hr style="width:100%;text-align:left;margin-left:0">
                    <div class="row mt-4">
                    <div class="col-12 col-md12 ">
					<span class="chatSubjectArea"> 
					<?php echo $Fsms['body']; ?>
					</span>
					</div>                    
					<div class="col-12 col-md6 ">
					<div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 					   
					   <button type="button" class="btn btn-warning btn-sm" style="color:white" data-toggle="collapse" data-target="#smsenlarge" >View Details</button>
                    </div> 
					</div>
					</div>
					<?php }
					else
					{
					?>
					<div id="smsenlarge" class="collapse">	
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="chatHeadArea2"><?php echo $Fsms['subject']; ?></span></div>
                    </div>
					 <hr style="width:100%;text-align:left;margin-left:0">
					<div class="row mt-4">
					<div class="col-12 col-md12 ">
					<span class="chatSubjectArea"> 
					<?php echo $Fsms['body']; ?>
					</span>
					</div>
					</div>
     				</div>
					<?php }} ?>
                    </div>
                    </div>
					
					
					                    
                    <div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    
			
                    <div class="col">
                    
                   <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Chat</span></div>
                    <div style="float:right"><span class="chatDaysArea"> 7days ago </span></div>
                    </div>
					 <div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="chatHeadArea2">Chat Heading</span></div>
                    </div>
					 <hr style="width:100%;text-align:left;margin-left:0">
					
                    <div class="row mt-4">
                    <div class="col-12 col-md6 "><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div>
                    <div class="col-12 col-md6 ">
					<div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 					   
					   <button type="button" class="btn btn-warning" style="color:white" data-toggle="collapse" data-target="#demo" >View</button>
                    </div> 
					</div>
					</div>                                       
			        <div id="demo" class="collapse row mt-4">
					 <div class="col-12 col-md6 "><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div>
					</div>
                    
             
                    </div>
					</div>
					
										                    
                    
					
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">        
                    <div class="col">              					
					<?php 
					// $pupilsightPersonID=$_SESSION['ChildId'];
					$pupilsightPersonID="0000000001";
					$sms="SELECT * FROM pupilsightmessenger where messageWall='Y' AND pupilsightPersonID='".$pupilsightPersonID."' order by timestamp desc";
					$Qsms=mysqli_query($conn,$sms);
					$i=0;
					while($Fsms=mysqli_fetch_array($Qsms))
					{
						$i++;
						if($i==1)
						{
							$date1=strtotime($Fsms['messageWall_date1']);
							$date2=strtotime(date('Y-m-d'));
						    $diff = ($date2 - $date1)/60/60/24; 							
					?>
					<div class="row mt-4" style="width:100%">
                    <div class="col-11" ><span class="chatHeadArea1">Message Wall</span></div>
                    <div  style="float:right"><span  class="chatDaysArea"> <?php echo $diff; ?> days ago </span></div>
                    </div>
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="chatHeadArea2"><?php echo $Fsms['subject']; ?></span></div>
                    </div>
					 <hr style="width:100%;text-align:left;margin-left:0">
                    <div class="row mt-4">
                    <div class="col-12 col-md12 ">
					<span class="chatSubjectArea"> 
					<?php echo $Fsms['body']; ?>
					</span>
					</div>                    
					<div class="col-12 col-md6 ">
					<div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 					   
					   <button type="button" class="btn btn-warning btn-sm" style="color:white" data-toggle="collapse" data-target="#Messageenlarge" >View Details</button>
                    </div> 
					</div>
					</div>
					<?php }
					else
					{
					?>
					<div id="Messageenlarge" class="collapse">	
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="chatHeadArea2"><?php echo $Fsms['subject']; ?></span></div>
                    </div>
					 <hr style="width:100%;text-align:left;margin-left:0">
					<div class="row mt-4">
					<div class="col-12 col-md12 ">
					<span class="chatSubjectArea"> 
					<?php echo $Fsms['body']; ?>
					</span>
					</div>
					</div>
     				</div>
					<?php }} ?>
                    </div>
                    </div>
					
					
					<?php  
					
			$feehsHead = 'SELECT fn_fee_invoice.fn_fees_head_id, fn_fee_invoice_student_assign.invoice_no  FROM fn_fee_invoice_student_assign LEFT JOIN fn_fee_invoice ON fn_fee_invoice_student_assign.fn_fee_invoice_id = fn_fee_invoice.id WHERE fn_fee_invoice_student_assign.pupilsightPersonID = ' . $_SESSION['ChildId'] . ' AND fn_fee_invoice_student_assign.invoice_status != "Fully Paid" AND fn_fee_invoice_student_assign.status = "1" GROUP BY fn_fee_invoice.fn_fees_head_id';
			$feesHead1=mysqli_query($conn,$feehsHead);
			$feehsHead2=mysqli_fetch_array($feesHead1);

            $fees = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, g.fine_type, g.name AS fine_name, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype, pupilsightPerson.officialName , pupilsightPerson.email, pupilsightPerson.phone1, pupilsightStudentEnrolment.pupilsightYearGroupID as classid, pupilsightStudentEnrolment.pupilsightRollGroupID as sectionid, pupilsightStudentEnrolment.pupilsightProgramID FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE fn_fee_invoice_student_assign.invoice_status != "Fully Paid" AND fn_fee_invoice_student_assign.status = "1" AND pupilsightPerson.pupilsightPersonID = "' . $_SESSION['ChildId'] . '" AND fn_fee_invoice.fn_fees_head_id = ' . $feehsHead2['fn_fees_head_id'] . ' GROUP BY fn_fee_invoice.id ORDER BY fn_fee_invoice.due_date ASC';
			$fees1=mysqli_query($conn,$fees);
			$fees2=mysqli_fetch_array($fees1);
			
			$feesAmount = 'SELECT * from fn_fee_invoice_item where fn_fee_invoice_id='.$fees2['id'].'';
			$feesAmount1=mysqli_query($conn,$feesAmount);
			$feesAmount2=mysqli_fetch_array($feesAmount1);
			
			
		   $amount=$feesAmount2['amount'];
					?>
					
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
 
                    <div class="col">
                    
                   <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Fee</span></div>
                    </div>
					 <div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent"><?php echo $fees2['title'];?></span></div>
                    </div>
					 <hr style="width:100%;text-align:left;margin-left:0">
					
        
                    <div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Please pay the pending amount of Rs. <b><?php echo $amount;?></b></span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="http://localhost/pupilsight_new/index.php?q=modules/Finance/invoice_child_view.php"><img src="./images/pay.png"></img></a> 
                    </div>                    

                    </div>
                    </div>
					
					<?php
					
					$sql = 'SELECT b.id as test_id, b.name as test_name FROM examinationTestAssignClass AS a LEFT JOIN examinationTest AS b ON a.test_id = b.id WHERE a.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND a.pupilsightProgramID = '.$students['pupilsightProgramID'].' AND a.pupilsightYearGroupID = '.$students['pupilsightYearGroupID'].' AND a.pupilsightRollGroupID = '.$students['pupilsightRollGroupID'].' AND b.enable_html = "1" ';


					?>
					
					
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Academic - Results</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Academic - Results</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					
         
					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Every hero, Every story, Every moment has led us here. Marver Studios Avengers: ENDGAME is now playing in theater.</span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="http://localhost/pupilsight_new/index.php?q=/modules/Academics/result.php"><img src="./images/view_details.png"></img></a> 
                    </div>                    

                    </div>
                    </div>
					
		 <?php
			
	    	$marks = 'SELECT * FROM history_of_students_marks where pupilsightPersonID='.$_SESSION['ChildId'].' order by id desc';
			$marks1=mysqli_query($conn,$marks);
			$marks2=mysqli_fetch_array($marks1);

   
		   ?>
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Academic - IRC</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Academic - IRC</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					
         
					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Overall Performance <?php echo $marks2['marks_obtained']; ?></span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="http://localhost/pupilsight_new/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=<?php echo $_SESSION['ChildId']; ?>&subpage=Academic"><img src="./images/view_details.png"></img></a> 
                    </div>                    

                    </div>
                    </div>
					

					 
					
		<?php
			$today=date('Y-m-d');
	    	$attendance = 'SELECT * FROM pupilsightattendancelogperson where pupilsightPersonID='.$_SESSION['ChildId'].' and date=$today';
			$attendance1=mysqli_query($conn,$attendance);
			$attendance2=mysqli_fetch_array($attendance1);

         
         if($attendance2['type']!="Present")
		 {
		   ?>
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Attendance</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Attedance</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					
         
       
					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Click the view button details to check the attendance log.</span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="http://localhost/pupilsight_new/index.php?q=/modules/Attendance/report_studentHistory.php"><img src="./images/view_details.png"></img></a> 
                    </div>                    

                    </div>
                    </div>
		 <?php } ?>		
					
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Homework</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Homework</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					

                    
					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Every hero, Every story, Every moment has led us here. Marver Studios Avengers: ENDGAME is now playing in theater.</span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="http://localhost/pupilsight_new/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=<?php echo $_SESSION['ChildId']; ?>&subpage=Homework"><img src="./images/view_details.png"></img></a> 
                    </div>                    

                    </div>
                    </div>	
					
					
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Activities</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Activities</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					
         

					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Every hero, Every story, Every moment has led us here. Marver Studios Avengers: ENDGAME is now playing in theater.</span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="http://localhost/pupilsight_new/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=<?php echo  $_SESSION['ChildId']; ?>&subpage=Activities"><img src="./images/view_details.png"></img></a> 
                    </div>                    

                    </div>
                    </div>
					
					
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Library</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Library</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					

					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">This is give the information or reminder of the borrowed book that is due for submission with view details button</span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="http://localhost/pupilsight_new/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=<?php echo $_SESSION['ChildId']; ?>&subpage=Library Borrowing"><img src="./images/view_details.png"></img></a> 
                    </div>                    

                    </div>
                    </div>					
					
					
					
					
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Profile</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Some Heading Goes Here</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					
         

                    
					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Every hero, Every story, Every moment has led us here. Marver Studios Avengers: ENDGAME is now playing in theater.</span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="http://localhost/pupilsight_new/index.php?q=/modules/Students/student_view.php"><img src="./images/view_details.png"></img></a> 
                    </div>                    

                    </div>
                    </div>
					
					
					
					
            </div>
			</section>
			</div> <!--Populate End -->

           
            
<script type="text/javascript" src="app.js"></script>     
</body>
 

</html>