<?php 
	session_start();
	set_time_limit(0);
   	error_reporting(E_ALL);
	
	$val = str_replace('','',$_SERVER['REDIRECT_URL']);
	$valArray = explode('/',$val);
	
	$apiArray = array();
	$apiArray['home'] = 'Controller/home.php';
       $apiArray['picasa'] = 'Controller/picasa.php';
	$apiArray['album'] = 'Controller/album.php';

	$className = isset($apiArray[$valArray[1]]) && strlen($valArray[1]) > 0 ? $valArray[1]   : "none";
	$methodName = isset($valArray[2]) ? $valArray[2]   : "display";

	if($className=="none")
	{
       	 header('Location:http://50.112.48.91/home');
        	 exit;
       }
	if (!isset($_SESSION['user_id']) && isset($valArray[2]) or !isset($_SESSION['user_id']) && $valArray[1]=="picasa") {
		header('Location:http://50.112.48.91/home');
		exit;
	}


	require_once('config.php');
	require_once 'Db/Db.php';

	require_once($apiArray[$className]);
	$objClass = new $className;
	if((int)method_exists($objClass, $methodName)){
			$objClass->$methodName();
	}
       else{
		echo "<h1>Default method not found!";
	}
	
?>
