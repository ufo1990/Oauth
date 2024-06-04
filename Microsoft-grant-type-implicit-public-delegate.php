<?php
	//Application data registered with Azure AD Developer
	$client_id = "XXXXXXXXXXXXXXXXXXXXXX";

	//Endpoints
	$redirect_uri = "XXXXXXXXXXXXXXXXXXXXXX";
	$endpopoint_authorize ="https://login.microsoftonline.com/common/oauth2/v2.0/authorize";
	
	session_start ();
	$_SESSION['state'] = session_id();

	//Check if the user has successfully logged in
	if(isset($_SESSION['logged']))
	{
		header('Location: index.php'); //Redericted if all ok
	}
	else
	{	
		//Calling the Microsoft 365 account login function
		$params = array('client_id' => $client_id,
		'redirect_uri' => $redirect_uri,
		'response_type' => 'token',
		'response_mode' =>'form_post',
		'prompt' => 'select_account', 
		'scope' =>'https://graph.microsoft.com/User.Read',
		'state' => $_SESSION['state']);
		header('Location: '.$endpopoint_authorize.'?'.http_build_query ($params));
	}

	//Check that url has token
	if(array_key_exists('access_token', $_POST))
	{
		$_SESSION['token'] = $_POST['access_token'];
		
		//Log via Microsoft 365 account 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Authorization: Bearer '.$_SESSION['token'],'Conent-type: application/json'));
		curl_setopt($ch, CURLOPT_URL, "https://graph.microsoft.com/v1.0/me/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = json_decode(curl_exec($ch), true);
		if(!array_key_exists('error', $res))
		{  
			$_SESSION['logged'] = true; //Verify success login
			$_SESSION['social_login_email'] = $res['mail']; //E-mail user
		}
		curl_close($ch);
		header("Location: $redirect_uri"); //Redericted after login
	}