<?php

if($_POST['action']=="send_otp")
{
	 $mobile= $_POST['mobile_number'];
	 $otp=rand(1000,9999);
	 
	 /*$url = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
					$url .="&send_to=".$mobile;
					$url .="&msg=".rawurlencode($otp);
					$url .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
					
				 
					$res = file_get_contents($url); */
	 $res = "success";				
		if($res=="success")
		{
			echo $res."|".$otp;
		}	
		else
		{
			echo "fail"."|"."";
		}	
					

}

 else if($_POST['action']=="verify_otp")
{
	//echo "val".$_POST['user_otp'].$_POST['mobile_otp'];
	if($_POST['user_otp']==$_POST['mobile_otp'])
	{
		echo "verified";
	}
	else
	{
		echo "not";
	}
}
else
{
	echo "";
}
?>