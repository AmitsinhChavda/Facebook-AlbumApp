<?php
	define('DB_SERVER',"localhost");
	define('DB_USER',"root");
	define('DB_PASS',"admin");
	define('DB_DATABASE',"album_app");
    
	define('basePath',"Dump/");
    	if(isset($_SESSION['user_id']))
    	{
       	 define('userId',$_SESSION['user_id']);
    	}

    //Facebook Credential
    define('appId','478199228917139');
    define('appSecret','5aa373398322b6ccdf9f8ff3ad58731e');
    define('next','http://50.112.48.91/home');
    define('next_logout','http://50.112.48.91/home/logout');
    define('picasa_login','https://www.google.com/accounts/AuthSubRequest?next=http%3A%2F%2F50.112.48.91%2Fpicasa&scope=http://picasaweb.google.com/data&secure=&session=1');



?>
