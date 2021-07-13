<?php 
session_start();
include("dbinfo.php");
error_reporting(0);
if( isset($_POST['inputValue']) ) {
//if(trim($_POST['inputValue'])=='SetSession'){
$pupilsightPersonID=trim($_POST['inputValue'])	;
$yearid=trim($_POST['YearId'])	;
$childs = 'SELECT a.pupilsightFamilyID,b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b 
ON a.pupilsightPersonID = b.pupilsightPersonID WHERE  a.pupilsightPersonID ='. $pupilsightPersonID .'';
$Echilds=mysqli_query($conn,$childs);
$Fchilds=mysqli_fetch_array($Echilds);






$childs1 = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b 
ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $Fchilds['pupilsightFamilyID'] . ' AND a.pupilsightPersonID != "" ';


$EchildsDetails=mysqli_query($conn,$childs1);

$_SESSION['ChildName']='';
 $_SESSION['ChildId']='';
 $_SESSION['random']=rand(100,9000);

 $_SESSION['ChildName']=$Fchilds['officialName'];
 $_SESSION['ChildId']=$Fchilds['pupilsightPersonID'];
 
 
 $cuid=$_SESSION['ChildId'];
 
 ?>

 <!--   <link rel="stylesheet" href="app.css">
	<script src="app.js"></script>-->
 <div id="populate" name="populate">

<div class="container-lg">
        <div class="row">
            <div class="col-sm-6" id="childarea">
                <div class="rahulare mt-5" >Hello <?php echo $_SESSION['ChildName']; ?></div>
            </div>
            <div class="col-sm-6">
                <div class="float-right mt-4 selectChildarea" >Select Child
                    <form action="/" class="ml-2 mt-lg-3">
						<input type="hidden" id="yearid" name="yearid" value="<?php echo $yearid; ?>">

                        <select name="myDropDown" id="myDropDown" style="border: none; background-color: #FAFAFB; overflow: hidden; " onchange="UpdateChild(this.value)">
						<?php 
						while($Fsi=mysqli_fetch_array($EchildsDetails))
						{
						?>
                            <option value="<?php echo $Fsi['pupilsightPersonID']; ?>" <?php if($Fchilds['pupilsightPersonID']==$Fsi['pupilsightPersonID']){ ?>selected <?php } ?>><?php echo $Fsi['officialName']; ?></option>
                            
						<?php } ?>
                        </select>
                    </form>             
                </div>
                <img src="parent.jpg" class="rounded-circle float-right mt-lg-5 " alt="Cinque Terre" width="40" height="40">
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
                <div class="light googul" style="display: flex; background-color: white; ">

                    <div class="calendar" id="calendar" style="background-color: white;margin-top:-200px" data-spy="scroll" data-target="#myScrollspy"
                        data-offset="1">
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
                            
                        </div>
                        <div class="month-list"></div>
                    </div>
					 <script type="text/javascript" src="app.js"></script>
                    

                </div>
            </div>
			
            <div class="col-sm-6" >
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
               <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Students/student_view_details.php&pupilsightPersonID=<?php echo $_SESSION['ChildId']; ?>"><img style="height: 61px; width: 62px;" src="./images/timtable.png"></img></a> <br><span class="discoverimageContent" style="text-align:center">Timetable</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Academics/result.php"> <img style="height: 61px; width: 62px;" src="./images/Chart 1.png"></img></a><br><span class="discoverimageContent">Progress Report</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Messenger/messageWall_view.php"> <img style="height: 61px; width: 62px;" src="./images/circular.png"></img></a><br><span class="discoverimageContent">Circular</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Messenger/chat_message.php"> <img style="height: 61px; width: 62px;" src="./images/Message 1.png"></img></a><br><span class="discoverimageContent" >Chat</span></a></div> 
                <div class="col" style="text-align:center"> <img style="height: 61px; width: 62px;" src="./images/event.png"></img></a><br><span class="discoverimageContent">Event</span></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Activities/activities_view.php&pupilsightPersonID=<?php echo $_SESSION['ChildId']; ?>"> <img style="height: 61px; width: 62px;" src="./images/Brainstorming 1.png"></img></a><br><span class="discoverimageContent">Activities</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Finance/invoice_child_view.php"> <img style="height: 61px; width: 62px;" src="./images/Frame (1).png"></img></a><br><span class="discoverimageContent">Fees invoice</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Finance/invoice_child_view.php"> <img style="height: 61px; width: 62px;" src="./images/Fees Recipet.png"></img></a><br><span class="discoverimageContent">Fees Receipt</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Students/student_view_details.php"> <img style="height: 61px; width: 62px;" src="./images/library.png"></img></a><br><span class="discoverimageContent">Library</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Attendance/report_studentHistory.php"> <img style="height: 61px; width: 62px;" src="./images/User 1.png"></img></a><br><span class="discoverimageContent">Attendance</span></a></div> 
                <div class="col" style="text-align:center"><a href="<?php echo $location; ?>/modules/Transport/bus_manage.php"> <img style="height: 60px; width: 62px;" src="./images/Frame.png"></img></a><br><span class="discoverimageContent">Transport</span></a></div> 
                <div class="col" style="text-align:center"> <img style="height: 61px; width: 62px;" src="./images/Learning.png"></img></a><br><span class="discoverimageContent">Learning</span></div> 
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
                    <span class="eventInvitation aside1" >Event Invitation</span> <br> 
                    <span class="someHeadingContent aside1" >Some Heading Goes Here</span>
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
					$pupilsightPersonID=$_SESSION['ChildId'];
					// $pupilsightPersonID="0000000001";
					$sms="SELECT * FROM pupilsightmessenger where sms='Y' AND pupilsightPersonID='".$pupilsightPersonID."' order by timestamp desc";
					$Qsms=mysqli_query($conn,$sms);
					$i=0;
					while($Fsms=mysqli_fetch_array($Qsms))
					{
						$i++;
						if($i<=3)
						{
							$date1=strtotime($Fsms['messageWall_date1']);
							$date2=strtotime(date('Y-m-d'));
						    $diff = ($date2 - $date1)/60/60/24; 							
					?>
					<div class="row mt-4" style="width:100%">
                    <div class="col-11" ><span class="chatHeadArea1">SMS</span></div>
                    <div  style="float:right"><span  class="chatDaysArea "> <?php echo $diff; ?> days ago </span></div>
                    </div>
					<div class="row mt-1">
                   
                    </div>
					 <hr style="width:100%;text-align:left;margin-left:0">
                    <div class="row mt-4">
					 <div class="col-6 col-md6 "><span class="chatHeadArea2"><?php echo $Fsms['subject']; ?></span></div>
                    <div class="col-12 col-md12 ">
					<span class="chatSubjectArea"> 
					<?php echo $Fsms['body']; ?>
					</span>
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
					
				
					<?php }}

					if($i !="")
						{
					?>
					
						<div class="col-12 col-md6 " >
					<div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 					   
					   <button type="button" class="btn btn-warning btn-sm" style="color:white" data-toggle="collapse" data-target="#smsenlarge" >View Details</button>
                    </div> 
					</div>
						<?php } ?> 
					
                    </div>
                    </div>
					
					
					                    
                    <div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    
			
                    <div class="col">
                    
                   <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Chat</span></div>
                    </div>

					 <hr style="width:100%;text-align:left;margin-left:0">
					<div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea2">Class Teacher</span></div>
                    <div style="float:right"><span class="chatDaysArea daysAgo"> 7days ago </span></div>
                    </div>	
					<div style="float:right">
                    <div><button class="btn btn-bprimary rounded-circle" data-toggle="collapse" data-target="#demo1" style="background-color:#2F80ED;color:#FFFFFF;height:50px">15</button></div>
                    </div>
                    
					<div class="row mt-4" >					 
                    <a ><div class="col-12 col-md6 " ><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div></a>
					</div>  

					<div id="demo1" class="collapse row mt-4">
					 <div class="col-12 col-md12 "><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div>
					</div>					
					 <hr style="width:100%;text-align:left;margin-left:0"> 
					<div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea2">Maths Teacher</span></div>
                    <div style="float:right"><span class="chatDaysArea daysAgo"> 7days ago </span></div>
                    </div>
						<div style="float:right">
                    <div><button class="btn btn-bprimary rounded-circle" data-toggle="collapse" data-target="#demo2" style="background-color:#2F80ED;color:#FFFFFF;height:50px">15</button></div>
                    </div>
                   
				   <div class="row mt-4" >					 
                    <a ><div class="col-12 col-md6 " ><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div></a>
					</div>  

					<div id="demo2" class="collapse row mt-4">
					 <div class="col-12 col-md6 "><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div>
					</div>	            
					
					<hr style="width:100%;text-align:left;margin-left:0"> 
					<div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea2">Science Teacher</span></div>
                    <div style="float:right"><span class="chatDaysArea daysAgo"> 7days ago </span></div>
                    </div>
						<div style="float:right">
                    <div><button class="btn btn-bprimary rounded-circle" data-toggle="collapse" data-target="#demo3" style="background-color:#2F80ED;color:#FFFFFF;height:50px">15</button></div>
                    </div>
                    <div class="row mt-4" >					 
                    <a ><div class="col-12 col-md6 " ><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div></a>
					</div>  

					<div id="demo3" class="collapse row mt-4">
					 <div class="col-12 col-md6 "><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div>
					</div>	                                   
			      
				 
				  
				  <div id="buttonOpen" class="collapse">
				  <div>
                    
					
					<hr style="width:100%;text-align:left;margin-left:0"> 
					<div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea2">English Teacher</span></div>
                    <div style="float:right"><span class="chatDaysArea daysAgo"> 7days ago </span></div>
                    </div>
						<div style="float:right">
                    <div><button class="btn btn-bprimary rounded-circle" data-toggle="collapse" data-target="#demo4" style="background-color:#2F80ED;color:#FFFFFF;height:50px">15</button></div>
                    </div>
                    <div class="row mt-4" >					 
                    <a ><div class="col-12 col-md6 " ><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div></a>
					</div>  

					<div id="demo4" class="collapse row mt-4">
					 <div class="col-12 col-md6 "><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div>
					</div>	                                   
					</div>	              

					<div>
                   
					
					<hr style="width:100%;text-align:left;margin-left:0"> 
					<div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea2">Tamil Teacher</span></div>
                    <div style="float:right"><span class="chatDaysArea daysAgo"> 7days ago </span></div>
                    </div>
						<div style="float:right">
                    <div><button class="btn btn-bprimary rounded-circle" data-toggle="collapse" data-target="#demo5" style="background-color:#2F80ED;color:#FFFFFF;height:50px">15</button></div>
                    </div>
                    <div class="row mt-4" >					 
                    <a ><div class="col-12 col-md6 " ><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div></a>
					</div>  

					<div id="demo5" class="collapse row mt-4">
					 <div class="col-12 col-md6 "><span class="chatSubjectArea"> Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet. Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.   </span></div>
					</div>	                                   
					</div>	              					
					</div>	
			
					<div class="col-12 col-md6 ">
					<div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 					   
					<button type="button" class="btn btn-warning" style="color:white" data-toggle="collapse" data-target="#buttonOpen" >View</button>
                    </div> 
					</div>					
			      
             
                    </div>
					</div>
					
										                    
              					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">        
                    <div class="col">              					
				         <?php 
					$pupilsightPersonID=$_SESSION['ChildId'];
					// $pupilsightPersonID="0000000001";
					$sms="SELECT * FROM pupilsightmessenger where messageWall='Y' AND pupilsightPersonID='".$pupilsightPersonID."' order by timestamp desc";
					$Qsms=mysqli_query($conn,$sms);
					$i=0;
					while($Fsms=mysqli_fetch_array($Qsms))
					{
						$i++;
						if($i<=3)
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
                   
                    </div>
					 <hr style="width:100%;text-align:left;margin-left:0">
                    <div class="row mt-4">
					 <div class="col-6 col-md6 "><span class="chatHeadArea2"><?php echo $Fsms['subject']; ?></span></div>
                    <div class="col-12 col-md12 ">
					<span class="chatSubjectArea"> 
					<?php echo $Fsms['body']; ?>
					</span>
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
					
				
					<?php }} 
					if($i !="")
						{
					?>
						<div class="col-12 col-md6 " >
					<div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 					   
					   <button type="button" class="btn btn-warning btn-sm" style="color:white" data-toggle="collapse" data-target="#smsenlarge" >View Details</button>
                    </div> 
					</div>
						<?php } ?>
					
					</div>
                    </div>
					
					
					
					
						<?php  
					
			// $feehsHead = 'SELECT fn_fee_invoice.fn_fees_head_id, fn_fee_invoice_student_assign.invoice_no  FROM fn_fee_invoice_student_assign LEFT JOIN fn_fee_invoice ON fn_fee_invoice_student_assign.fn_fee_invoice_id = fn_fee_invoice.id WHERE fn_fee_invoice_student_assign.pupilsightPersonID = ' . $_SESSION['ChildId'] . ' AND fn_fee_invoice_student_assign.invoice_status != "Fully Paid" AND fn_fee_invoice_student_assign.status = "1" GROUP BY fn_fee_invoice.fn_fees_head_id';
			// $feesHead1=mysqli_query($conn,$feehsHead);
			// $feehsHead2=mysqli_fetch_array($feesHead1);

            // $fees = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, g.fine_type, g.name AS fine_name, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype, pupilsightPerson.officialName , pupilsightPerson.email, pupilsightPerson.phone1, pupilsightStudentEnrolment.pupilsightYearGroupID as classid, pupilsightStudentEnrolment.pupilsightRollGroupID as sectionid, pupilsightStudentEnrolment.pupilsightProgramID FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE fn_fee_invoice_student_assign.invoice_status != "Fully Paid" AND fn_fee_invoice_student_assign.status = "1" AND pupilsightPerson.pupilsightPersonID = "' . $_SESSION['ChildId'] . '" AND fn_fee_invoice.fn_fees_head_id = ' . $feehsHead2['fn_fees_head_id'] . ' GROUP BY fn_fee_invoice.id ORDER BY fn_fee_invoice.due_date ASC';
			// $fees1=mysqli_query($conn,$fees);
			// $fees2=mysqli_fetch_array($fees1);
			
			// $feesAmount = 'SELECT * from fn_fee_invoice_item where fn_fee_invoice_id='.$fees2['id'].'';
			// $feesAmount1=mysqli_query($conn,$feesAmount);
			// $feesAmount2=mysqli_fetch_array($feesAmount1);
			
			// $upcomingfeehsHead = 'SELECT fn_fee_invoice.fn_fees_head_id, fn_fee_invoice_student_assign.invoice_no  FROM fn_fee_invoice_student_assign LEFT JOIN fn_fee_invoice ON fn_fee_invoice_student_assign.fn_fee_invoice_id = fn_fee_invoice.id WHERE fn_fee_invoice_student_assign.pupilsightPersonID = ' . $_SESSION['ChildId'] . ' AND fn_fee_invoice_student_assign.invoice_status != "Fully Paid" AND fn_fee_invoice_student_assign.status = "1" GROUP BY fn_fee_invoice.fn_fees_head_id';
			// $upcomingfeesHead1=mysqli_query($conn,$upcomingfeehsHead);
			// $upcomingfeehsHead2=mysqli_fetch_array($upcomingfeesHead1);
			
			
			// $upcomingfees = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, g.fine_type, g.name AS fine_name, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype, pupilsightPerson.officialName , pupilsightPerson.email, pupilsightPerson.phone1, pupilsightStudentEnrolment.pupilsightYearGroupID as classid, pupilsightStudentEnrolment.pupilsightRollGroupID as sectionid, pupilsightStudentEnrolment.pupilsightProgramID FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE fn_fee_invoice_student_assign.invoice_status != "Fully Paid" AND fn_fee_invoice_student_assign.status = "1" AND pupilsightPerson.pupilsightPersonID = "' . $_SESSION['ChildId'] . '" AND fn_fee_invoice.fn_fees_head_id = ' . $upcomingfeehsHead2['fn_fees_head_id'] . ' GROUP BY fn_fee_invoice.id ORDER BY fn_fee_invoice.due_date ASC';
			// $upcomingfees1=mysqli_query($conn,$upcomingfees);
			// $upcomingfees2=mysqli_fetch_array($upcomingfees1);
			
			
			// $upcomingfeesAmount = 'SELECT * from fn_fee_invoice_item where fn_fee_invoice_id='.$upcomingfees2['id'].'';
			// $upcomingfeesAmount1=mysqli_query($conn,$upcomingfeesAmount);
			// $upcomingfeesAmount2=mysqli_fetch_array($upcomingfeesAmount1);
			
			$t1 = 'SELECT * from fn_fee_invoice_student_assign  where pupilsightPersonID='.$_SESSION['ChildId'].' and invoice_status="Not Paid"';
			$t11=mysqli_query($conn,$t1);
			$t111=mysqli_fetch_array($t11);
						
						
			
			$t2 = 'SELECT sum(total_amount) as amount from fn_fee_invoice_item where fn_fee_invoice_id='.$t111['fn_fee_invoice_id'].' ';
			$t22=mysqli_query($conn,$t2);
			$t222=mysqli_fetch_array($t22);
			
			
			$t3 = 'SELECT * from fn_fee_invoice where fn_fee_structure_id='.$t111['fn_fee_structure_id'].' ';
			$t33=mysqli_query($conn,$t3);
			$t333=mysqli_fetch_array($t33);
			

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
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">
					
					Over Due Invoice</br></br>

					Dear Parent, you have a overdue fee to be paid. Please ignore if paid already.</br></br>

					Invoice number: <?php echo $t111['invoice_no'];?> Invoice amount : ₹<?php echo $t222['amount'];?> Due Date: <?php echo date("j  F Y ",strtotime($t333['due_date']))?></br></br>
					<hr style="width:100%;text-align:left;margin-left:0">
				

					Upcoming Invoice</br></br>

					Dear Parent, You have upcoming fee to be paid. Please ignore if paid already.</br></br>

					Invoice number: <?php echo $t111['invoice_no'];?> Invoice amount: ₹<?php echo $t222['amount'];?> Due Date: <?php echo date("j  F Y ",strtotime($t333['due_date']))?></br></br>
					<hr style="width:100%;text-align:left;margin-left:0">

					No invoices or All Invoices are paid</br>

					Dear Parent, You have paid all the fees for the academic year. Thank you for making the payment</br>
					</div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="<?php echo $location; ?>/modules/Finance/invoice_child_view.php"><img src="./images/pay.png"></img></a> 
                    </div>                    
                    </div>
                    </div>
					
					
					
					
					<?php					
				//	$sql = 'SELECT b.id as test_id, b.name as test_name FROM examinationTestAssignClass AS a LEFT JOIN examinationTest AS b ON a.test_id = b.id WHERE a.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND a.pupilsightProgramID = '.$students['pupilsightProgramID'].' AND a.pupilsightYearGroupID = '.$students['pupilsightYearGroupID'].' AND a.pupilsightRollGroupID = '.$students['pupilsightRollGroupID'].' AND b.enable_html = "1" ';
					$stuId = $_SESSION[$guid]['pupilsightPersonID'];
				 	$chkchilds = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1, c.* FROM pupilsightPerson AS b LEFT JOIN pupilsightStudentEnrolment AS c ON b.pupilsightPersonID = c.pupilsightPersonID WHERE b.pupilsightPersonID = '.$_SESSION['ChildId'].' AND c.pupilsightSchoolYearID = '.$yearid.' ';
					$resultachk = mysqli_query($conn,$chkchilds);
					$students = mysqli_fetch_array($resultachk);
					
					$sql = 'SELECT b.id as test_id, b.name as test_name FROM examinationTestAssignClass AS a LEFT JOIN examinationTest AS b ON a.test_id = b.id WHERE a.pupilsightSchoolYearID = '.$yearid.' AND a.pupilsightProgramID = '.$students['pupilsightProgramID'].' AND a.pupilsightYearGroupID = '.$students['pupilsightYearGroupID'].' AND a.pupilsightRollGroupID = '.$students['pupilsightRollGroupID'].' AND b.enable_html = "1" ';
					$result = mysqli_query($conn,$sql);
					$testData=mysqli_fetch_array($result);
					?>		
					<?php if($testData['test_name']!=""){?>
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
                    <div class="col-8 col-md4 "> 
					<span class="someHeadingdown1">
					<?php echo $testData['test_name']; ?>
					</span> </div>
                    </div> 
					<?php 
					$cid=$_SESSION['ChildId'];
					?>
                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="<?php echo $location; ?>/modules/Academics/result_details.php&tid=<?php echo $testData['test_id']; ?>&cid=<?php echo $cid; ?>"><img src="./images/view_details.png"></img></a> 
                    </div>           
                    </div>
                    </div>
					<?php } ?>
					
		 <?php
			
	    	$marks = 'SELECT * FROM history_of_students_marks where pupilsightPersonID='.$_SESSION['ChildId'].' order by id desc';
			$marks1=mysqli_query($conn,$marks);
			$marks2=mysqli_fetch_array($marks1);

   
		   ?>
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px" hidden>
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Academic - IRC</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Academic - IRC</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					
         
					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">No Data <?php echo $marks2['marks_obtained']; ?></span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="<?php echo $location; ?>/modules/Students/student_view_details.php&pupilsightPersonID=<?php echo $_SESSION['ChildId']; ?>&subpage=Academic"><img src="./images/view_details.png"></img></a> 
                    </div>                    

                    </div>
                    </div>
					

					 
					
		<?php
			$today=date('Y-m-d');
	    	$attendance = 'SELECT * FROM pupilsightattendancelogperson where pupilsightPersonID='.$_SESSION['ChildId'].' and date=$today';
			$attendance1=mysqli_query($conn,$attendance);
			$attendance2=mysqli_fetch_array($attendance1);
			
			$psid='0000000175';
			
			$sqltop="select count(distinct(date)) as countSessionOne from pupilsightattendancelogperson where pupilsightPersonID='".$psid."' AND session_no='1'";
			$Esqltop=mysqli_query($conn,$sqltop);
			$Fsqltop=mysqli_fetch_array($Esqltop);
			
			$sqlttp="select count(distinct(date)) as countSessionTwo from pupilsightattendancelogperson where pupilsightPersonID='".$psid."' AND session_no='2'";
			$Esqlttp=mysqli_query($conn,$sqlttp);
			$Fsqlttp=mysqli_fetch_array($Esqlttp);			
			
			
            $sqlop="select count(distinct(date)) as countSessionOnePresent from pupilsightattendancelogperson where pupilsightPersonID='".$psid."' AND session_no='1' AND type='Present'";
			$Esqlop=mysqli_query($conn,$sqlop);
			$Fsqlop=mysqli_fetch_array($Esqlop);
			
			$sqloa="select count(distinct(date)) as countSessionOneAbsent from pupilsightattendancelogperson where pupilsightPersonID='".$psid."' AND session_no='1' AND type='Absent'";
			$Esqloa=mysqli_query($conn,$sqloa);
			$Fsqloa=mysqli_fetch_array($Esqloa);
			
			$sqltp="select count(distinct(date)) as countSessionOnePresent from pupilsightattendancelogperson where pupilsightPersonID='".$psid."' AND session_no='2' AND type='Present'";
			$Esqltp=mysqli_query($conn,$sqltp);
			$Fsqltp=mysqli_fetch_array($Esqltp);
			
			$sqlta="select count(distinct(date)) as countSessionOneAbsent from pupilsightattendancelogperson where pupilsightPersonID='".$psid."' AND session_no='2' AND type='Absent'";
			$Esqlta=mysqli_query($conn,$sqlta);
			$Fsqlta=mysqli_fetch_array($Esqlta);
			
			$countSessionOne=$Fsqltop['countSessionOne'];
			$countSessionTwo=$Fsqlttp['countSessionTwo'];
			$countSessionOnePresent=$Fsqlop['countSessionOnePresent'];
			$countSessionOneAbsent=$Fsqloa['countSessionOneAbsent'];
			$countSessionTwoPresent=$Fsqltp['countSessionOnePresent'];
			$countSessionTwoAbsent=$Fsqlta['countSessionOneAbsent'];
			
			
			$totalSession=$countSessionOne+$countSessionTwo;			
			$totalAbsent=$countSessionOneAbsent+$countSessionTwoAbsent;			
			$totalPresent= $countSessionOnePresent+$countSessionTwoPresent;
			
         
		   ?>
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Attendance</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Attendance</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					
         
       
					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Total Sessions : <?php echo $totalSession; ?></span> </div>
					<div class="col-8 col-md4 "> <span class="someHeadingdown1">Total Present : <?php echo $totalPresent; ?></span> </div>
					<div class="col-8 col-md4 "> <span class="someHeadingdown1">Total Absent : <?php echo $totalAbsent; ?></span> </div>
					<div class="col-8 col-md4 "> <span class="someHeadingdown1">Attendance % : <?php 
					$tt=($totalPresent/$totalSession)*100;
					echo $tt; ?></span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="<?php echo $location; ?>/modules/Attendance/report_studentHistory.php"><img src="./images/view_details.png"></img></a> 
                    </div>                    

                    </div>
                    </div>
		 	
					
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px" hidden>
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Homework</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Homework</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					

                    
					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">No Data</span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="<?php echo $location; ?>/modules/Students/student_view_details.php&pupilsightPersonID=<?php echo $_SESSION['ChildId']; ?>&subpage=Homework"><img src="./images/view_details.png"></img></a> 
                    </div>                    

                    </div>
                    </div>	
					
					
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px" hidden>
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Activities</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Activities</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					
         

					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">No Data</span> </div>
                    </div> 

                    <div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 
					  <a href="<?php echo $location; ?>/modules/Students/student_view_details.php&pupilsightPersonID=<?php echo  $_SESSION['ChildId']; ?>&subpage=Activities"><img src="./images/view_details.png"></img></a> 
                    </div>                    

                    </div>
                    </div>
					
					
					<?php
					$library11 = 'SELECT * FROM pupilsightlibraryitem where pupilsightPersonIDOwnership='.$_SESSION['ChildId'].'';
					$library22=mysqli_query($conn,$library11);
					$library33=mysqli_fetch_array($library22);
					
					if($library33['name']!="")
					{
						?>
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 15px;border-radius:20px">
                    <div class="col">
                    
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Library</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Library</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					<?php 
					$i=0;
					$library1 = 'SELECT * FROM pupilsightlibraryitem where pupilsightPersonIDOwnership='.$_SESSION['ChildId'].'';
					$library2=mysqli_query($conn,$library1);
					while($library3=mysqli_fetch_array($library2)){
					$i++;
					if($i<=3)
						{
					?> 

					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Book Name : <?php echo $library3['name']; ?></span> </div>
                    </div>
					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Return Expected : <?php echo $library3['returnExpected']; ?></span> </div>
                    </div> 
					<hr style="width:100%;text-align:left;margin-left:0">
					<?php 
					}
					else
					{
					?>
					<div id="librarylarge" class="collapse">	
					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Book Name : <?php echo $library3['name']; ?></span> </div>
                    </div>
					<div class="row mt-2">
                    <div class="col-8 col-md4 "> <span class="someHeadingdown1">Return Expected : <?php echo $library3['returnExpected']; ?></span> </div>
                    </div>

					<div class="row mt-2 ">
					<div class="col-11 col-md12 ">                    
					<a  href="<?php echo $location; ?>/modules/Students/student_view_details.php&pupilsightPersonID=<?php echo $_SESSION['ChildId']; ?>&subpage=Library Borrowing"><span class="chatHeadArea1">View More</span></a>
                    </div>
                    </div>
					
                    </div> 
					<?php 
					}
					}	

					if($i!="")
					{
					?>

                   	<div class="col-12 col-md6 " >
					<div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 					   
					<button type="button" class="btn btn-warning btn-sm" style="color:white" data-toggle="collapse" data-target="#librarylarge" >View Details</button>
                    </div> 
					</div>  
					<?php } ?>					

                    </div>
                    </div>	
					<?php } ?>

					
					
			<?php 

			$profile = "SELECT * FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE pupilsightSchoolYearID=".$yearid." AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date('Y-m-d') . "') AND (dateEnd IS NULL  OR dateEnd>='" . date('Y-m-d') . "') AND pupilsightPerson.pupilsightPersonID=".$_SESSION['ChildId']."";
			$profile1=mysqli_query($conn,$profile);
			$profile2=mysqli_fetch_array($profile1);
			
			$profile12 = "SELECT * FROM pupilsightYearGroup WHERE pupilsightYearGroupID=".$profile2['pupilsightYearGroupID']."";
			$profile13=mysqli_query($conn,$profile12);
			$profile14=mysqli_fetch_array($profile13);
			
			$profile15 = "SELECT * FROM pupilsightRollGroup WHERE pupilsightRollGroupID=".$profile2['pupilsightRollGroupID']."";
			$profile16=mysqli_query($conn,$profile15);
			$profile17=mysqli_fetch_array($profile16);
					?>
					
					
					<div class="row" style="background-color:#FFFFFF;margin-top:20px;margin-bottom: 100px;border-radius:20px">
                    <div class="col">
                
					
                    <div class="row mt-4">
                    <div class="col-11 col-md12 "><span class="chatHeadArea1">Profile</span></div>
                    </div>
					
					<div class="row mt-1">
                    <div class="col-6 col-md6 "><span class="someHeadingContent">Profile</span></div>
                    </div>
					
					<hr style="width:100%;text-align:left;margin-left:0">
					
         
			<!--	<?php  //if($profile2['image_240']!=""){?>                   
					<div class="row mt-2">
                    <div class="col-8 col-md4 "><img src="<?php echo $profile2['image_240']; ?>"></img> </div>
                    </div> 
				<?php // }else{ ?>
					<div class="row mt-2">
                    <div class="col-8 col-md4 "><img src="./images/anonymous_75.png"></img> </div>
                    </div> 
				<?php // } ?> -->
				
					<div class="row mt-2" hidden>
                    <div class="col-8 col-md4 "><img src="./images/anonymous_75.png"></img>
					<span class="someHeadingdown1"><?php echo $profile2['officialName']; ?></span> </br>
					 <span class="someHeadingdown1"><?php echo $profile14['name']; ?> - <?php echo $profile17['name']; ?></span>
					</div>
                    </div> 
					
					
					 <div class="row">
                    <div class="col-1"><img style="width:64px;height:64px;margin-left:15px;margin-top:20px" src="./images/anonymous_75.png"></img></div>
                    
                    <div class="col-10" style="margin-top:30px">
                    <span class="someHeadingdown1 aside1" ><?php echo $profile2['officialName']; ?></span> <br> 
                    <span class="someHeadingdown1 aside1" ><?php echo $profile14['name']; ?> - <?php echo $profile17['name']; ?></span>
                    </div>
                    
                    </div>
			
			
     
					<div class="col-12 col-md6 " >
					<div  style="float:right;margin-bottom:10px;margin-top:20px;margin-right:10px"> 					   
					  <a href="<?php echo $location; ?>/modules/Students/student_view.php"><button type="button" class="btn btn-warning btn-sm" style="color:white" >View Details</button></a>
                    </div> 
					</div>  					

                    </div>
                    </div>
					
					
					
					
            </div>
			</section>
			</div>
<?php } ?>