<?php

use Pupilsight\Comms\NotificationEvent;

include "../../pupilsight.php";

setCurrentSchoolYear($guid, $connection2);

//The current/actual school year info, just in case we are working in a different year
$_SESSION[$guid]["pupilsightSchoolYearIDCurrent"] = $_SESSION[$guid]["pupilsightSchoolYearID"];
$_SESSION[$guid]["pupilsightSchoolYearNameCurrent"] = $_SESSION[$guid]["pupilsightSchoolYearName"];
$_SESSION[$guid]["pupilsightSchoolYearSequenceNumberCurrent"] = $_SESSION[$guid]["pupilsightSchoolYearSequenceNumber"];

$_SESSION[$guid]["pageLoads"] = NULL;

$URL = "index.php";

$redirect_uri = getSettingByScope($connection2, "System", "googleRedirectUri" );

/************************************************
  Make an API request on behalf of a user. In
  this case we need to have a valid OAuth 2.0
  token for the user, so we need to send them
  through a login flow. To do this we need some
  information from our API console project.
 ************************************************/
$client = $container->get('Google_Client');

/************************************************
  When we create the service here, we pass the
  client to it. The client then queries the service
  for the required scopes, and uses that when
  generating the authentication URL later.
 ************************************************/
$service = new Google_Service_Oauth2($client);

/************************************************
  If we have a code back from the OAuth 2.0 flow,
  we need to exchange that with the authenticate()
  function. We store the resultant access token
  bundle in the session, and redirect to ourself.
*/

if (isset($_GET['error'])) {
    header('Location: '.getSettingByScope($connection2, 'System', 'absoluteURL').'?loginReturn=fail7');
    exit;
}

if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION[$guid]['googleAPIAccessToken']  = $client->getAccessToken();

  if (isset($_GET['state'])) {
    $redirect_uri .= '?state='.$_GET['state'];
  }

  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
  exit;
}

/************************************************
  If we have an access token, we can make
  requests, else we generate an authentication URL.
 ************************************************/
$refreshToken = isset($_SESSION[$guid]['googleAPIAccessToken']['refresh_token'])? $_SESSION[$guid]['googleAPIAccessToken']['refresh_token'] : '';

if (isset($_SESSION[$guid]['googleAPIAccessToken'] ) && $_SESSION[$guid]['googleAPIAccessToken'] ) {
  $client->setAccessToken($_SESSION[$guid]['googleAPIAccessToken'] );
} else {
  $authUrl = $client->createAuthUrl();
}


//Display user info or display login url as per the info we have.

if (isset($authUrl)){
	//show login url
    echo '<div>';
        $themeName = isset($_SESSION[$guid]['pupilsightThemeName'])? $_SESSION[$guid]['pupilsightThemeName'] : 'Default';
        echo '<a target=\'_top\' class="login" href="' . $authUrl . '" onclick="addGoogleLoginParams(this)">';
            echo '<button class="w-full bg-white rounded shadow border border-gray flex items-center px-2 py-1 mb-2 text-gray hover:shadow-md hover:border-blue-600 hover:text-blue-600">';
                echo '<img class="w-10 h-10" src="themes/'.$themeName.'/img/google-login.svg">';
                echo '<span class="flex-grow text-lg">'.__('Sign in with Google').'</span>';
            echo '</button>';
        echo '</a>';

        $form = \Pupilsight\Forms\Form::create('loginFormGoogle', '#');
        $form->setFactory(\Pupilsight\Forms\DatabaseFormFactory::create($pdo));
        $form->setClass('blank fullWidth loginTableGoogle');

        $loginIcon = '<img src="'.$_SESSION[$guid]['absoluteURL'].'/themes/'.$themeName.'/img/%1$s.png" style="width:20px;height:20px;margin:-2px 0 0 2px;" title="%2$s">';

        $row = $form->addRow()->setClass('loginOptionsGoogle');
            $row->addContent(sprintf($loginIcon, 'planner', __('School Year')));
            $row->addSelectSchoolYear('pupilsightSchoolYearIDGoogle')
                ->setClass('fullWidth')
                ->placeholder(null)
                ->selected($_SESSION[$guid]['pupilsightSchoolYearID']);

        $row = $form->addRow()->setClass('loginOptionsGoogle');
            $row->addContent(sprintf($loginIcon, 'language', __('Language')));
            $row->addSelectI18n('pupilsighti18nIDGoogle')
                ->setClass('fullWidth')
                ->placeholder(null)
                ->selected($_SESSION[$guid]['i18n']['pupilsighti18nID']);

        $row = $form->addRow();
            $row->addContent('<a class="showGoogleOptions" onclick="false" href="#">'.__('Options').'</a>')
                ->wrap('<span class="small">', '</span>')
                ->setClass('right');

        echo $form->getOutput();
        ?>

        <script>
        $(".loginOptionsGoogle").hide();
        $(".showGoogleOptions").click(function(){
            if ($('.loginOptionsGoogle').is(':hidden')) $(".loginTableGoogle").removeClass('blank').addClass('noIntBorder');
            $(".loginOptionsGoogle").fadeToggle(1000, function() {
                if ($('.loginOptionsGoogle').is(':hidden')) $(".loginTableGoogle").removeClass('noIntBorder').addClass('blank');
            });
        });

        function addGoogleLoginParams(element)
        {
            $(element).attr('href', function() {
                if ($('#pupilsightSchoolYearIDGoogle').is(':visible')) {
                    var googleSchoolYear = $('#pupilsightSchoolYearIDGoogle').val();
                    var googleLanguage = $('#pupilsighti18nIDGoogle').val();
                    return this.href.replace('&state&', '&state='+googleSchoolYear+':'+googleLanguage+'&');
                }
            });
        }
        </script>
        <?php
	echo '</div>';
} else {
	$user = $service->userinfo->get(); //get user info
	$email = $user->email;
	$_SESSION[$guid]['gplusuer'] = $user;

	try {
		$data = array("email"=>$email);
		$sql = "SELECT * FROM pupilsightPerson WHERE email=:email";
		$result = $connection2->prepare($sql);
		$result->execute($data);
	}
	catch(PDOException $e) {}

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $pupilsighti18nID = $_SESSION[$guid]['i18n']['pupilsighti18nID'];

    // If available, load school year and language from state passed back from OAuth redirect
    if (isset($_GET['state']) && stripos($_GET['state'], ':') !== false) {
        list($pupilsightSchoolYearID, $pupilsighti18nID) = explode(':', $_GET['state']);
    }

	//Test to see if email exists in logintable
	if ($result->rowCount() != 1) {
        setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, null, 'Google Login - Failed', array('username' => $email, 'reason' => 'No matching email found', 'email' => $email), $_SERVER['REMOTE_ADDR']);
        unset($_SESSION[$guid]['googleAPIAccessToken'] );
		unset($_SESSION[$guid]['gplusuer']);
 		session_destroy();
		$_SESSION[$guid] = NULL;
		$URL = "../../index.php?loginReturn=fail8";
		header("Location: {$URL}");
		exit;
	}
	//Start to collect User Info and test
	try {
		$data = array("email"=>$email);
		$sql = "SELECT pupilsightPerson.*, futureYearsLogin, pastYearsLogin FROM pupilsightPerson LEFT JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE email=:email AND status='Full'";
		$result = $connection2->prepare($sql);
		$result->execute($data);
	}
	catch(PDOException $e) { }

	//Test to see if gmail matches email in pupilsight
	if ($result->rowCount() != 1) {
		unset($_SESSION[$guid]['googleAPIAccessToken'] );
		unset($_SESSION[$guid]['gplusuer']);
		@session_destroy();
		$_SESSION[$guid] = NULL;
        $URL = "../../index.php?loginReturn=fail8";
        header("Location: {$URL}");
        exit;
	}
	else {
        $row = $result->fetch();
        
        // Get primary role info
        $data = array('pupilsightRoleIDPrimary' => $row['pupilsightRoleIDPrimary']);
        $sql = "SELECT * FROM pupilsightRole WHERE pupilsightRoleID=:pupilsightRoleIDPrimary";
        $role = $pdo->selectOne($sql, $data);

        // Insufficient privileges to login
        if ($row['canLogin'] != 'Y' || (!empty($role['canLoginRole']) && $role['canLoginRole'] != 'Y')) {
            unset($_SESSION[$guid]['googleAPIAccessToken'] );
            unset($_SESSION[$guid]['gplusuer']);
            @session_destroy();
            $URL = "../../index.php?loginReturn=fail2";
            header("Location: {$URL}");
            exit;
        }

		$username = $row['username'];
		if ($row["failCount"] >= 3) {
			try {
				$data = array("lastFailIPAddress"=> $_SERVER["REMOTE_ADDR"], "lastFailTimestamp"=> date("Y-m-d H:i:s"), "failCount"=>($row["failCount"]+1), "username"=>$username);
				$sqlSecure = "UPDATE pupilsightPerson SET lastFailIPAddress=:lastFailIPAddress, lastFailTimestamp=:lastFailTimestamp, failCount=:failCount WHERE username=:username";
				$resultSecure = $connection2->prepare($sqlSecure);
				$resultSecure->execute($data);
			}
			catch(PDOException $e) { }

			if ($row["failCount"] == 3) {
                // Raise a new notification event
                $event = new NotificationEvent('User Admin', 'Login - Failed');

                $event->addRecipient($_SESSION[$guid]['organisationAdministrator']);
                $event->setNotificationText(sprintf(__('Someone failed to login to account "%1$s" 3 times in a row.'), $username));
                $event->setActionLink('/index.php?q=/modules/User Admin/user_manage.php&search='.$username);

                $event->sendNotifications($pdo, $pupilsight->session);
			}

            setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Google Login - Failed', array('username' => $username, 'reason' => 'Too many failed logins'), $_SERVER['REMOTE_ADDR']);
            unset($_SESSION[$guid]['googleAPIAccessToken'] );
            unset($_SESSION[$guid]['gplusuer']);
            @session_destroy();
            $URL = "../../index.php?loginReturn=fail6";
			header("Location: {$URL}");
			exit;
		}

		if ($row["passwordForceReset"] == "Y") {
            // Sends the user to the password reset page after login
            $_SESSION[$guid]['passwordForceReset'] = 'Y';
		}


		if ($row["pupilsightRoleIDPrimary"] == "" OR count(getRoleList($row["pupilsightRoleIDAll"], $connection2)) == 0) {
			//FAILED TO SET ROLES
            setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Google Login - Failed', array('username' => $username, 'reason' => 'Failed to set role(s)'), $_SERVER['REMOTE_ADDR']);
            unset($_SESSION[$guid]['googleAPIAccessToken'] );
            unset($_SESSION[$guid]['gplusuer']);
            @session_destroy();
            $URL = "../../index.php?loginReturn=fail2";
			header("Location: {$URL}");
			exit;
		} else {
            //Allow for non-current school years to be specified
            if ($pupilsightSchoolYearID != $_SESSION[$guid]['pupilsightSchoolYearID']) {
                if ($row['futureYearsLogin'] != 'Y' and $row['pastYearsLogin'] != 'Y') { //NOT ALLOWED DUE TO CONTROLS ON ROLE, KICK OUT!
                    setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Login - Failed', array('username' => $username, 'reason' => 'Not permitted to access non-current school year'), $_SERVER['REMOTE_ADDR']);
                    unset($_SESSION[$guid]['googleAPIAccessToken'] );
                    unset($_SESSION[$guid]['gplusuer']);
                    session_destroy();
                    $_SESSION[$guid] = NULL;
                    $URL = "../../index.php?loginReturn=fail9";
                    header("Location: {$URL}");
                    exit;
                } else {
                    //Get details on requested school year
                    try {
                        $dataYear = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                        $sqlYear = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
                        $resultYear = $connection2->prepare($sqlYear);
                        $resultYear->execute($dataYear);
                    } catch (PDOException $e) {
                    }

                    //Check number of rows returned.
                    //If it is not 1, show error
                    if (!($resultYear->rowCount() == 1)) {
                        die(__('Configuration Error: there is a problem accessing the current Academic Year from the database.'));
                    }
                    //Else get year details
                    else {
                        $rowYear = $resultYear->fetch();
                        if ($row['futureYearsLogin'] != 'Y' and $_SESSION[$guid]['pupilsightSchoolYearSequenceNumber'] < $rowYear['sequenceNumber']) { //POSSIBLY NOT ALLOWED DUE TO CONTROLS ON ROLE, CHECK YEAR
                            setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Login - Failed', array('username' => $username, 'reason' => 'Not permitted to access non-current school year'), $_SERVER['REMOTE_ADDR']);
                            unset($_SESSION[$guid]['googleAPIAccessToken'] );
                            unset($_SESSION[$guid]['gplusuer']);
                            session_destroy();
                            $_SESSION[$guid] = NULL;
                            $URL = "../../index.php?loginReturn=fail9";
                            header("Location: {$URL}");
                            exit;
                        } elseif ($row['pastYearsLogin'] != 'Y' and $_SESSION[$guid]['pupilsightSchoolYearSequenceNumber'] > $rowYear['sequenceNumber']) { //POSSIBLY NOT ALLOWED DUE TO CONTROLS ON ROLE, CHECK YEAR
                            setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Login - Failed', array('username' => $username, 'reason' => 'Not permitted to access non-current school year'), $_SERVER['REMOTE_ADDR']);
                            unset($_SESSION[$guid]['googleAPIAccessToken'] );
                            unset($_SESSION[$guid]['gplusuer']);
                            session_destroy();
                            $_SESSION[$guid] = NULL;
                            $URL = "../../index.php?loginReturn=fail9";
                            header("Location: {$URL}");
                            exit;
                        } else { //ALLOWED
                            $_SESSION[$guid]['pupilsightSchoolYearID'] = $rowYear['pupilsightSchoolYearID'];
                            $_SESSION[$guid]['pupilsightSchoolYearName'] = $rowYear['name'];
                            $_SESSION[$guid]['pupilsightSchoolYearSequenceNumber'] = $rowYear['sequenceNumber'];
                        }
                    }
                }
            }
        }

		//USER EXISTS, SET SESSION VARIABLES
		$pupilsight->session->createUserSession($username, $row);

        // If user has personal language set, load it
        if (!empty($_SESSION[$guid]['pupilsighti18nIDPersonal']) && $pupilsighti18nID == $_SESSION[$guid]['i18n']['pupilsighti18nID']) {
            $pupilsighti18nID = $_SESSION[$guid]['pupilsighti18nIDPersonal'];
        }

        // Allow for non-system default language to be specified (from login form or personal)
        if (!empty($pupilsighti18nID)) {
            try {
                $dataLanguage = array('pupilsighti18nID' => $pupilsighti18nID);
                $sqlLanguage = 'SELECT * FROM pupilsighti18n WHERE pupilsighti18nID=:pupilsighti18nID';
                $resultLanguage = $connection2->prepare($sqlLanguage);
                $resultLanguage->execute($dataLanguage);
            } catch (PDOException $e) {
            }
            if ($resultLanguage->rowCount() == 1) {
                $rowLanguage = $resultLanguage->fetch();
                setLanguageSession($guid, $rowLanguage, false);
            }
        }

		try {
			$data = array( "lastIPAddress"=> $_SERVER["REMOTE_ADDR"], "lastTimestamp"=> date("Y-m-d H:i:s"), "failCount"=>0, "username"=> $username );
			$sql = "UPDATE pupilsightPerson SET lastIPAddress=:lastIPAddress, lastTimestamp=:lastTimestamp, failCount=:failCount WHERE username=:username";
			$result = $connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { }

		//Set Goolge API refresh token where appropriate, and update user
		if (!empty($refreshToken)) {
			$_SESSION[$guid]["googleAPIRefreshToken"] = $refreshToken;
			try {
				$data = array( "googleAPIRefreshToken"=> $_SESSION[$guid]["googleAPIRefreshToken"], "username"=> $username );
				$sql = "UPDATE pupilsightPerson SET googleAPIRefreshToken=:googleAPIRefreshToken WHERE username=:username";
				$result = $connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { }
		} else {
            // No refresh token and none saved in pupilsightPerson: force a re-authorization of this account
            if (empty($row['googleAPIRefreshToken'])) {
                $client->setApprovalPrompt('force');
                $authUrl = $client->createAuthUrl();
                header('Location: ' . $authUrl);
                exit;
            }
        }

        //The final reckoning...does email match?
		if (isset($_SESSION[$guid]["username"])) { //YES!
            setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Google Login - Success', array('username' => $username), $_SERVER['REMOTE_ADDR']);
            $URL = "../../index.php";
    		header("Location: {$URL}");
    		exit;
		}
		else { //NO
            setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, null, 'Google Login - Failed', array('username' => $username, 'reason' => 'No matching email found', 'email' => $email), $_SERVER['REMOTE_ADDR']);
            unset($_SESSION[$guid]['googleAPIAccessToken'] );
			unset($_SESSION[$guid]['gplusuer']);
			session_destroy();
			$_SESSION[$guid] = NULL;
            $URL = "../../index.php?loginReturn=fail8";
    		header("Location: {$URL}");
    		exit;
		}
	}


    if (isset($_GET['logout'])) {
      unset($_SESSION[$guid]['googleAPIAccessToken'] );
      unset($_SESSION[$guid]['gplusuer']);

      session_destroy();
      header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']); // it will simply destroy the current seesion which you started before
      exit;
    }
}
?>
