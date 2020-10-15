<?php

/**
 *
 * @Author: Kuldip.D
 * @Creation-Date: 13-04-2015
 * @Copyright: Mindgate Solution Pvt. Ltd.
 * @Description: This class is used to connect QuickPay service.
 * @Program-Specs-Referred: Functional Specification and TAD documents
 * @Revision:
 * ----------------------------------------------------------------------------------------------------------------
 * @Version    | @Last Revision Date | @Name                  | @Function/Module affected    | @Modifications Done
 * ----------------------------------------------------------------------------------------------------------------	
 * 0.1			23/11/2015			  Kuldip Deshmukh		   Initial Class and functionalities created
 *
 *
 *
 */
class HTTPPost
{
	


/**
 * This method is used to post data to server end.
 *
 * Step 1 : Open the URL connection and set the request property
 * Step 2 : Send the request
 * Step 3 : Read the response
 *
 * @param String : URL
 * @param String : Parameters
 * @return String
 */
function excutePost($qpURL,$urlParameters)
{
	$url='';
	$connection='';
	$resStr=null;
	$result="";
	try
	{
		$url=$qpURL;
		
		// Step 1 : Open the URL connection and set the request property
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$urlParameters);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CAINFO, getcwd() . "\EntrustRootCertificationAuthority-G2.crt");
		// Step 2 : Send the request
		$result = curl_exec ($ch);
		$error = curl_error($ch);
		//echo $error;
		// Step 3 : Read the response
		$reponseInfo = curl_getinfo($ch);
		curl_close ($ch);
		if (preg_match('/OK/',$result))
		{
		echo "ok";
			
		return $result;
		}
		else
		{
			return $result;
		}
	}

	catch(Exception $e)
	{
		
		echo 'Message: ' . $e->getTraceAsString();
	}

	return $result;
}

}
