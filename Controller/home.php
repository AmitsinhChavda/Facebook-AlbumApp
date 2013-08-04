<?php
    require 'lib/facebook-php-sdk-master/src/facebook.php';
    require 'picasa.php';
    
	class home{

        function display()
        {
            $facebook = new Facebook(array(
                'appId'  => appId,
                'secret' => appSecret,
            ));

            $user = $facebook->getUser();

            if ($user)
            {
                //Check User session is set
                // if not than fetch user info and check user exists. then save user info into session
                //otherwise insert new record and set session
                if(!isset($_SESSION['user_name']))
                {
			$userinfo = $facebook->api('/me?fields=email,username,name&access_token=' .$facebook->getAccessToken());
			
                    $db = new Database();
                    $db->connect();
                    // check user exists if yes then set use_name & picasa_token into session

                    $checkUser = $db->fetch_all_array("SELECT
										*
								FROM
										user_master
								WHERE
										facebook_id = ".$userinfo['id']);

                    if(!$checkUser)
                    {
                        $data3 = array();
                        $data3['facebook_id']=$userinfo['id'];
                        $data3['username']=$userinfo['username'];
                        $data3['name']=$userinfo['name'];
                        $id=$db->query_insert("user_master",$data3);
                        $_SESSION['user_name']=$userinfo['name'];
                        $_SESSION['user_id']=$id;

                    }else{
                        $_SESSION['user_name']=$checkUser[0]['name'];
                        $_SESSION['sessionToken']=$checkUser[0]['picasa_token'];
                        $_SESSION['user_id']=$checkUser[0]['id'];

                    }
                }

                $params = array( 'next' => next_logout);
                 $logoutUrl = $facebook->getLogoutUrl($params);
            }
            else
            {
                $params = array( 'scope' => 'user_photos,user_status,email');
                $loginUrl = $facebook->getLoginUrl($params);
            }
            include 'Templete/index.php';
        }

        //Load Album after user logged in successfully.
        function viewalbum()
        {
            $facebook = new Facebook(array(
                'appId'  => appId,
                'secret' => appSecret,
            ));
            $photos = $facebook->api('/'.$_REQUEST['id'].'?fields=photos.fields(source)&access_token='.$facebook->getAccessToken());
            for($i=0;$i<count($photos['photos']['data']);$i++)
            {
                echo "<a href='".$photos['photos']['data'][$i]['source']."'class='fancybox' data-fancybox-group='gallery' title='".$photos['photos']['data'][$i]['id']."'></a>";
            }
        }

        //Logout from Facebook & Album App
        function logout()
        {
            $facebook = new Facebook(array(
                'appId'  => appId,
                'secret' => appSecret,
            ));
            $facebook->destroySession();
            session_destroy();
            header("Location:/home");

        }

}