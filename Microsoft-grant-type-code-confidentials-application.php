<?php
	//Application data registered with Azure AD Developer
	$client_id = "XXXXXXXXXXXXXXXXXXXXXX";
	$client_secret = "XXXXXXXXXXXXXXXXXXXXXX"; 
	$tenant_id = "XXXXXXXXXXXXXXXXXXXXXX";

	//Endpoints
	$redirect_uri = "XXXXXXXXXXXXXXXXXXXXXX";
	$endpopoint_token = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";
	
	session_start ();
	$_SESSION['state'] = session_id();

	//Check if application gate token 
	if(isset($_SESSION['token_app']))
	{
		header('Location: index.php'); //Redericted if all ok
	}
	else
	{	
		//Params to build access by client credentials
		$params = array(
			'grant_type' => 'client_credentials', 
			'client_id' => $client_id,                    
			'client_secret' => $client_secret,
			'scope' => 'https://graph.microsoft.com/.default',
		);
		
		//Calling to get token via application
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpopoint_token);                
		curl_setopt($ch, CURLOPT_POST, 1);  
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$res = json_decode(curl_exec($ch), 1);
		if(!array_key_exists('error', $res))
		{
			$_SESSION['token_app'] = $res['access_token'];
		}
		curl_close($ch);	
		header("Location: $redirect_uri"); //Redericted after login
	}