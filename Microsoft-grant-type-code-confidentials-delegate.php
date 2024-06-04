<?php
	//Application data registered with Azure AD Developer
	$client_id = "XXXXXXXXXXXXXXXXXXXXXX";
	$client_secret = "XXXXXXXXXXXXXXXXXXXXXX";

	//Endpoints
	$redirect_uri = "XXXXXXXXXXXXXXXXXXXXXX";
	$endpopoint_authorize ="https://login.microsoftonline.com/common/oauth2/v2.0/authorize";
	$endpoint_token  = "https://login.microsoftonline.com/common/oauth2/v2.0/token";
	
	session_start();
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
		'response_type' => 'code',
		'prompt' => 'select_account',
		'nonce'=> $_SESSION['state'],
		'scope' =>'https://graph.microsoft.com/User.Read openid',
		'state' => $_SESSION['state']);
		header('Location: '.$endpopoint_authorize.'?'.http_build_query($params));
	}
	
	//Check that url has code
	if(isset($_GET['code']))
	{
		$code = $_GET['code'];  
		
		//Log via Microsoft 365 account 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint_token);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
			'code'          => $code,
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'redirect_uri'  => $redirect_uri,
			'grant_type'    => 'authorization_code',
		]));
		$res = json_decode(curl_exec($ch), true);
		if(!array_key_exists('error', $res))
		{
			$_SESSION['token_app'] = $res['access_token'];
		}
		curl_close($ch);		
	}
	
	//Check if exist access token
	if(isset($_SESSION['token_app']))
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Authorization: Bearer '.$_SESSION['token_app']));
		curl_setopt($ch, CURLOPT_URL, "https://graph.microsoft.com/v1.0/me");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = json_decode(curl_exec($ch), true);
		if(!array_key_exists('error', $res))
		{
			$_SESSION['logged'] = true; //Verify success login 
			$_SESSION['social_login_email'] =  $res['mail']; //E-mail user
		}
		curl_close($ch);
		header("Location: $redirect_uri"); //Redericted after login
	}
