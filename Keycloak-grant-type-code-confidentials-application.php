<?php
//Application data registered with Keycloak Platform
$client_id = 'XXXXXX';
$client_secret = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

//Endpoints
$redirect_uri = 'https://XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
$endpopoint_authorize = 'https://xxxxxxxxxxx/realms/xxxxxxxxxxx/protocol/openid-connect/auth';
$endpoint_token = 'https://xxxxxxxxxxx/realms/xxxxxxxxxxx/protocol/openid-connect/token';
$userinfo_endpoint = 'https://xxxxxxxxxxx/realms/xxxxxxxxxxx/protocol/openid-connect/userinfo';

session_start();
$_SESSION['state'] = session_id();

//Check if the user has successfully logged_social in
if(isset($_SESSION['logged']))
{
	header('Location: index.php'); //Redericted if all ok
}
elseif(!isset($_GET['code']))
{	
	//Calling the Keycloak account login function
	$params = array('client_id' => $client_id,
	'redirect_uri' => $redirect_uri,
	'response_type' => 'code',
	'scope' => 'openid',
	'kc_idp_hint' => 'google', //This parametr set default provider
	'state' => $_SESSION['state']);
	header ('Location: '.$endpopoint_authorize.'?'.http_build_query($params));
	exit();
}

//Check that url has token
if(isset($_GET['code']))
{
	$code = $_GET['code'];   
	
	//Log via Keycloak account 
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
		$access_token =  $res['access_token'];	
	}

	//Close the cURL session
	curl_close($ch);
}

//Check if exist access token
if(isset($access_token))
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Authorization: Bearer '.$access_token));
	curl_setopt($ch, CURLOPT_URL, $userinfo_endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$res = json_decode(curl_exec($ch), true);
	
	if(!array_key_exists('error', $res))
	{
		$_SESSION['logged'] = true; //Verify success login 
		$_SESSION['social_login_email'] = $res['email'];
	}

	//Close the cURL session
	curl_close($ch);

	//Redericted after login
	header("Location: $redirect_uri"); 
}