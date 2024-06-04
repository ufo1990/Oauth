<?php
	//Application data registered with Google Cloud Platform
	$client_id = "XXXXXXXXXXXXXXXXXXXXXX.apps.googleusercontent.com";
	$client_secret = "XXXXXXXXXXXXXXXXXXXXXX";
	
	//Endpoints
	$redirect_uri = "XXXXXXXXXXXXXXXXXXXXXX";
	$endpopoint_authorize ="https://accounts.google.com/o/oauth2/v2/auth";
	$endpoint_token = "https://accounts.google.com/o/oauth2/token";
	
	session_start();
	$_SESSION['state'] = session_id();
	
	//Check if the user has successfully logged in
	if(isset($_SESSION['logged']))
	{
		header('Location: index.php'); //Redericted if all ok
	}
	else
	{	
		//Calling the Google account login function
		$params = array('client_id' => $client_id,
		'redirect_uri' => $redirect_uri,
		'response_type' => 'code',
		'scope' =>'https://www.googleapis.com/auth/userinfo.email',
		'state' => $_SESSION['state']);
		header('Location: '.$endpopoint_authorize.'?'.http_build_query($params));
	}

	//Check that url has token
	if(isset($_GET['code'])){
	
		$code = $_GET['code'];   
		
		//Log via Google account 
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
			$_SESSION['token_app'] =  $res['access_token'];
		}	
		curl_close($ch);
	}
	
	//Check if exist access token
	if(isset($_SESSION['token_app']))
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Authorization: Bearer '.$_SESSION['token_app']));
		curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v2/userinfo?fields=email");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = json_decode(curl_exec($ch), true);
		if(!array_key_exists('error', $res))
		{
			$_SESSION['logged'] = true; //Verify success login 
			$_SESSION['social_login_email'] = $res['email']; //E-mail user
		}
		curl_close($ch);
		header("Location: $redirect_uri"); //Redericted after login
	}