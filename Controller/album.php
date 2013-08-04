<?php
require 'lib/facebook-php-sdk-master/src/facebook.php';
require 'picasa.php';
require 'PHPMailer/class.phpmailer.php';


    class album{

        //Get Album Detail
        function albumdetail()
        {
            //if dump folder for particuler user is available then delete old one and make new directory
           $timestamp=time();
           $path=basePath.userId.$timestamp;
           mkdir($path);

           $facebook = new Facebook(array(
                'appId'  => appId,
                'secret' => appSecret,
           ));

            $albumId=$_REQUEST['selected_checkbox'];
            $_SESSION['CacheAlbum']=$_REQUEST['selected_checkbox'];
            $_SESSION['Timestamp']=$timestamp;
            $albums=explode(',',$albumId);
            $total="";
            $result=array();
            for($i=0;$i<count($albums);$i++)
            {
                set_time_limit(0);
                $aid=trim($albums[$i]);
                $photos = $facebook->api("/$aid/?fields=name,photos.fields(images)&access_token=".$facebook->getAccessToken());
                $temp = str_replace('.', '-', $photos['name']);
                $aname=preg_replace('/[^A-Za-z0-9\-]/', '', $temp);

                $result[$i]['id']=$aid;
                $result[$i]['name']=$aname;
                $result[$i]['total']=count($photos['photos']['data']);
                $total=$total+count($photos['photos']['data']);
            }
            $result[0]['all']=$total;
            $result[0]['timestamp']=$timestamp;
            echo json_encode($result);
        }

	   //Check If Album Request in Cache
        function CheckCache()
        {
            if($_REQUEST['move']!=1)
            {
                if(isset($_SESSION['CacheAlbum']))
                {
                    if($_SESSION['CacheAlbum']==$_REQUEST['selected_checkbox'])
                    {
                        echo $_SESSION['Timestamp'];
                    }
                }else{
                    echo 0;
                }
            }else{
                echo 0;
            }
        }

        //Create Album to picasa.
        function createAlbum()
        {
            $name=$_REQUEST['name'];
            $move=$_REQUEST['move'];
            $timestamp=$_REQUEST['timestamp'];

            $AlubumId="";
            $path=basePath.userId.$timestamp;
            $result=array();
            $facebook = new Facebook(array(
                'appId'  => appId,
                'secret' => appSecret,
            ));

            if (!is_dir($path.'/'.$name))
            {
                mkdir($path.'/'.$name);
            }
            if($move==1)
            {
                $client = picasa::getAuthSubHttpClient();
                $AlubumId=picasa::addAlbum($client,$name);
            }
            $aid=trim($_REQUEST['id']);
            $photos = $facebook->api("/$aid/photos?access_token=".$facebook->getAccessToken());
                for($j=0;$j<count($photos['data']);$j++)
                {
                    set_time_limit(0);
                    $result[$j]['source']=$photos['data'][$j]['images'][0]['source'];
                    if($move==1)
                    {
                        $result[$j]['picasaId']=$AlubumId;
                    }
                }
            echo json_encode($result);
        }

        //Save photos
        function saveAlbum()
        {
            $source=$_REQUEST['source'];
            $move=$_REQUEST['move'];
            $name=$_REQUEST['name'];
            $timestamp=$_REQUEST['timestamp'];
            $path=basePath.userId.$timestamp;

            set_time_limit(0);
            $ext=pathinfo($source);
            copy($source,$path.'/'.$name.'/'.$ext['filename'].'.'.$ext['extension']);

            if($move==1)
            {
                $client = picasa::getAuthSubHttpClient();
                picasa::addPhoto($client,$_REQUEST['picasaId'],$path.'/'.$name.'/'.$ext['filename'].'.'.$ext['extension'],$ext['extension'],$ext['filename'],'Normal');
            }
        }

      

         //Create Zip
        function createZip()
        {
            $move=$_REQUEST['move'];
            album::zip($move,'',$_REQUEST['timestamp']);
        }

        function zip($move,$type,$timestamp)
        {
            $path=basePath.userId.$timestamp;
            if($move==0)
            {
                ini_set("max_execution_time", 300);
                // create object
                $zip = new ZipArchive();
                // open archive
                $filename="";
                if($type=='background')
                {
                    $filename=$_SESSION['fileName'];
                    $_SESSION['fileName']=$path.'.zip';
                    $_SESSION['backFile']=$_SESSION['fileName'];
                }else{
                    $_SESSION['fileName']=$path.'.zip';
                }

                if ($zip->open($path.'.zip', ZIPARCHIVE::OVERWRITE) !== TRUE) {
                    die("Could not open archive");
                }
                // initialize an iterator
                // pass it the directory to be processed
                //echo basePath.userId.'/Main/';
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path.'/'));
                // iterate over the directory
                // add each file found to the archive
                foreach ($iterator as $key=>$value) {

                    $key = str_replace("\\", "/", $key);
                    $key = str_replace("\\", "/", $key);
                    $key = str_replace("\\", "/", $key);
                    $key = str_replace("\\", "/", $key);
                    $key = str_replace("\\", "/", $key);

                    $value = str_replace("\\", "/", $value);
                    $value = str_replace("\\", "/", $value);
                    $value = str_replace("\\", "/", $value);
                    $value = str_replace("\\", "/", $value);
                    $value = str_replace("\\", "/", $value);

                    $newKey = str_replace($path."/", "", $key);
                    if($type=='single')
		      {
	  			if($newKey!="" and $newKey!=".." and $newKey!=".")
                    		{
                        		$zip->addFile(realpath($key), $newKey) or die ("ERROR: Could not add file: $key");
                    		}
		      }else{

                    $temp=explode('/',$newKey);
                    
                    if($newKey!="" and $newKey!="." and $newKey!=".." )
                    {
                       if(isset($temp[1]))
			  {
                          if($temp[1]!=".." and $temp[1]!=".")
			     {    
                       
				//echo $newKey." - ".$key."</br>" ;
			      $zip->addFile(realpath($key), $newKey) or die ("ERROR: Could not add file: $key - $newKey");
				}
			   }
                    }
		    }

                }

                $zip->close();
            }
                if($type=='background')
                {
                    $_SESSION['fileName']=$filename;
                }
            

            //delete folder
            if(is_dir($path))
            {
                album::recursive_remove_directory($path,$empty=TRUE);
                rmdir($path);

            }
        }

         //For single Photo
        function downloadalbum()
        {
            error_reporting(E_ERROR);
            $move=$_REQUEST['move'];
            //if dump folder for particuler user is available then delete old one and make new directory
            $timestamp=time();
            $path=basePath.userId.$timestamp;
            mkdir($path);

            $facebook = new Facebook(array(
                'appId'  => appId,
                'secret' => appSecret,
            ));

            set_time_limit(0);
            $photoId=trim($_REQUEST['photo_id']);
            $SinglePhoto= $facebook->api("/$photoId?access_token=".$facebook->getAccessToken());
            if(isset($SinglePhoto['images']))
            {
                $ext=pathinfo($SinglePhoto['images'][0]['source']);
                copy($SinglePhoto['images'][0]['source'],$path.'/'.$ext['filename'].'.'.$ext['extension']);
                if($move==1)
                {
                    $client = picasa::getAuthSubHttpClient();
                    picasa::addPhoto($client,'',$path.'/'.$ext['filename'].'.'.$ext['extension'],$ext['extension'],$ext['filename'],'single');
			
		      if(is_dir($path))
            	      {
                      album::recursive_remove_directory($path,$empty=TRUE);
                      rmdir($path);
                    }
                }
            }else{
                echo 'error';
            }
            album::zip($move,'single',$timestamp);
            echo $timestamp;
        }

        //Download Zip file
        function getzip()
        {
            if(isset($_SESSION['user_id']))
            {
                header("Content-Description: File Transfer");
                header("Content-Disposition:attachment;filename=".userId.'.zip');
                header('Content-Type: application/zip');
                header("Content-Transfer-Encoding: binary");
                readfile(basePath.$_SESSION['user_id'].$_REQUEST['timestamp'].'.zip');

            }else{
                echo "error";
            }
        }

        function downloadBack()
        {
            error_reporting(E_ERROR);
            $albumId=$_REQUEST['selected_checkbox'];
            $move=$_REQUEST['move'];
            $timestamp=time();
            $path=basePath.userId.$timestamp;
            $facebook = new Facebook(array(
                'appId'  => appId,
                'secret' => appSecret,
            ));


            mkdir($path);

            $albums=explode(',',$albumId);
            for($i=0;$i<count($albums);$i++)
            {
                $aid=trim($albums[$i]);
                $photos = $facebook->api("/$aid/photos?access_token=".$facebook->getAccessToken());
                $AlbumName=$facebook->api("/$aid?access_token=".$facebook->getAccessToken());
                $temp = str_replace('.', '-', $AlbumName['name']);
                $aname=preg_replace('/[^A-Za-z0-9\-]/', '', $temp);
                if (!is_dir($path.'/'.$aname))
                {
                    mkdir($path.'/'.$aname);
                }
                //Create Album if Move
                for($j=0;$j<count($photos['data']);$j++)
                {
                    set_time_limit(0);
                    $ext=pathinfo($photos['data'][$j]['source']);
                    copy($photos['data'][$j]['source'],$path.'/'.$aname.'/ '.$ext['filename'].'.'.$ext['extension']);
                }
            }
            album::zip($move,'background',$timestamp);
            album::email($path.'.zip',$_REQUEST['email']);
        }

        //Recursive Remove directory
        function recursive_remove_directory($directory, $empty=FALSE)
        {
            // if the path has a slash at the end we remove it here
            if(substr($directory,-1) == '/')
            {
                $directory = substr($directory,0,-1);
            }

            // if the path is not valid or is not a directory ...
            if(!file_exists($directory) || !is_dir($directory))
            {
                // ... we return false and exit the function
                return FALSE;

                // ... if the path is not readable
            }elseif(!is_readable($directory))
            {
                // ... we return false and exit the function
                return FALSE;

                // ... else if the path is readable
            }else{

                // we open the directory
                $handle = opendir($directory);

                // and scan through the items inside
                while (FALSE !== ($item = readdir($handle)))
                {
                    // if the filepointer is not the current directory
                    // or the parent directory
                    if($item != '.' && $item != '..')
                    {
                        // we build the new path to delete
                        $path = $directory.'/'.$item;

                        // if the new path is a directory
                        if(is_dir($path))
                        {
                            // we call this function with the new path
                            album::recursive_remove_directory($path);

                            // if the new path is a file
                        }else{
                            // we remove the file
                            unlink($path);
                        }
                    }
                }
                // close the directory
                closedir($handle);

                // if the option to empty is not set to true
                if($empty == FALSE)
                {
                    // try to delete the now empty directory
                    if(!rmdir($directory))
                    {
                        // return false if not possible
                        return FALSE;
                    }
                }
                // return success
                return TRUE;
            }
        }

	 // Sent Email With Zip link

        function email($filename,$email)
        {
            error_reporting(E_ERROR);

            $message = '<html>

                            <body>
                                <div style="border:1px solid #ccc;">
                                    <div style="padding:20px;text-size:16px;color:#fff;background:#2BA6CB;border-bottom:1px solid #ccc;">Album App</div>
                                    <div style="padding:20px; color:#666666;">
                                        <p><b>Hello '.$_SESSION['user_name'].',</b></p><br />
                                        <p>Thank you for using our service.</p>
					      <p>To Download Your zipped album,Click on <a href=http://50.112.48.91/'.$filename.'>http://50.112.48.91/'.$filename.'</a></p><br/>
                                        <p>Album App <br>Director </p>
                                    <div>
                                </div>
                            </body>
                            </html>';

              $subject='Album Zip Link';
              $to =$email;

		$mail = new PHPMailer;

		$mail->IsSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.gmail.com';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $email;                            // your SMTP username
		$mail->Password = $password;                           // Your SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
		
		$mail->From = 'Notification@AlbumApp.com';
		$mail->FromName = 'AlbumApp Notification';
		$mail->AddAddress($email, $_SESSION['user_name']);  // Add a recipient
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		$mail->IsHTML(true);                                  // Set email format to HTML
		$mail->Subject = 'Zip Link';
		$mail->Body    = $message;
		

		if(!$mail->Send()) {
		    echo 'Message could not be sent.';
		    echo 'Mailer Error: ' . $mail->ErrorInfo;
		    exit;
		} 
           
            
            echo $email;
        }
    }
