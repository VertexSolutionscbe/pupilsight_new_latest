<?php 
include("dbinfo.php");
$studid='0000004709';
$q1="SELECT fn_fee_invoice.fn_fees_head_id, fn_fee_invoice_student_assign.invoice_no  FROM fn_fee_invoice_student_assign LEFT JOIN fn_fee_invoice 
ON fn_fee_invoice_student_assign.fn_fee_invoice_id = fn_fee_invoice.id WHERE fn_fee_invoice_student_assign.pupilsightPersonID = '$studid' 
AND fn_fee_invoice_student_assign.invoice_status != 'Fully Paid' AND fn_fee_invoice_student_assign.status = '1' GROUP BY fn_fee_invoice.fn_fees_head_id";

$Eq1=mysqli_query($conn,$q1);
while($FEq1=mysqli_fetch_array($Eq1))
{
	$q2="SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, g.fine_type, g.name AS fine_name, 
g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype, pupilsightPerson.officialName , 
pupilsightPerson.email, pupilsightPerson.phone1, pupilsightStudentEnrolment.pupilsightYearGroupID as classid, pupilsightStudentEnrolment.pupilsightRollGroupID as 
sectionid, pupilsightStudentEnrolment.pupilsightProgramID FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON 
fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON 
pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON 
pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id 
LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON 
pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE fn_fee_invoice_student_assign.invoice_status != 'Fully Paid' 
AND fn_fee_invoice_student_assign.status = '1' AND pupilsightPerson.pupilsightPersonID = '$studid' AND 
fn_fee_invoice.fn_fees_head_id = ' ".$FEq1['fn_fees_head_id']."' GROUP BY fn_fee_invoice.id ORDER BY fn_fee_invoice.due_date ASC";
$Eq2=mysqli_query($conn,$q2);
while($FEq2=mysqli_fetch_array($Eq2))
{
	$q3="SELECT SUM(fn_fee_invoice_item.total_amount) as totalamount, group_concat(fn_fee_invoice_item.id) as fn_fee_invoice_item_id FROM fn_fee_invoice_item 
WHERE fn_fee_invoice_id = '".$FEq2['invoiceid']."'";
$Eq3=mysqli_query($conn,$q3);
   while($FEq3=mysqli_fetch_array($Eq3))
   {
	  echo  $FEq3['totalamount'];
	  echo  " / " .$FEq3['fn_fee_invoice_item_id'];
	  echo  " / " .$FEq2['stu_invoice_no'];
	  echo  " / " .$FEq2['due_date'];
	   echo  " / " .$FEq2['title'];
	   echo  " / " .$FEq2['officialName'];
	  echo "</br></br>";
   }

}

}

?>