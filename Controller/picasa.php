<?php
require_once 'lib/library/Zend/Loader.php';

/**
 * @see Zend_Gdata
 */
Zend_Loader::loadClass('Zend_Gdata');

/**
 * @see Zend_Gdata_AuthSub
 */
Zend_Loader::loadClass('Zend_Gdata_AuthSub');

/**
 * @see Zend_Gdata_Photos
 */
Zend_Loader::loadClass('Zend_Gdata_Photos');

/**
 * @see Zend_Gdata_Photos_UserQuery
 */
Zend_Loader::loadClass('Zend_Gdata_Photos_UserQuery');

/**
 * @see Zend_Gdata_Photos_AlbumQuery
 */
Zend_Loader::loadClass('Zend_Gdata_Photos_AlbumQuery');

/**
 * @see Zend_Gdata_Photos_PhotoQuery
 */
Zend_Loader::loadClass('Zend_Gdata_Photos_PhotoQuery');

/**
 * @see Zend_Gdata_App_Extension_Category
 */
Zend_Loader::loadClass('Zend_Gdata_App_Extension_Category');

    class picasa
    {
        function token()
        {
            $string='http://picasaweb.google.com/data/entry/api/user/110658941631401277180/albumid/5892319942233042273';
                        $AlbumId=explode('/',$string);
                    echo $AlbumId[count($AlbumId)-1];

        }
        function display()
        {
            global $_SESSION, $_GET;

            if (!isset($_SESSION['sessionToken']) && !isset($_GET['token'])) {
                $authSubUrl = picasa::getAuthSubUrl();
                echo "<a href=\"{$authSubUrl}\"></a>";
            } else {
                $client = picasa::getAuthSubHttpClient();
                header("Location:/home");

            }

        }
        function addPhoto($client, $albumId, $photo,$extension,$filename,$type)
        {

            $photos = new Zend_Gdata_Photos($client);

            $fd = $photos->newMediaFileSource($photo);
            $fd->setContentType('image/'.$extension);

            $entry = new Zend_Gdata_Photos_PhotoEntry();
            $entry->setMediaSource($fd);
            $entry->setTitle($photos->newTitle($filename));

            $albumQuery = new Zend_Gdata_Photos_AlbumQuery;
            $albumQuery->setUser("default");
            if($type=='single')
            {
                $albumQuery->setAlbumId("default");
            }else
            {
                $albumQuery->setAlbumId($albumId);
            }
            $result = $photos->insertPhotoEntry($entry, $albumQuery->getQueryUrl());

        }
        function addAlbum($client, $name)
        {
            $photos = new Zend_Gdata_Photos($client);

            $entry = new Zend_Gdata_Photos_AlbumEntry();
            $entry->setTitle($photos->newTitle($name));

            $result = $photos->insertAlbumEntry($entry);

            if ($result) {
                $AlbumId=explode('/',$result->getId());
               return $AlbumId[count($AlbumId)-1];
            } else {
                return 'error';
            }
        }
       function getAuthSubHttpClient()
        {
            global $_SESSION, $_GET;
            if (!isset($_SESSION['sessionToken']) && isset($_GET['token'])) {
                $_SESSION['sessionToken'] =
                    Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
                $db = new Database();
                $db->connect();
                $sql="update
                      user_master
                      set picasa_token='".$_SESSION['sessionToken']."' where id=".$_SESSION['user_id'];
                $db->query($sql);
            }
            $client = Zend_Gdata_AuthSub::getHttpClient($_SESSION['sessionToken']);
            return $client;
        }
        function getAuthSubUrl()
        {
            $next = picasa::getCurrentUrl();
            $scope = 'http://picasaweb.google.com/data';
            $secure = false;
            $session = true;
            return Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure,
                $session);
        }
        function getCurrentUrl()
        {
            global $_SERVER;

            /**
             * Filter php_self to avoid a security vulnerability.
             */
            $php_request_uri = htmlentities(substr($_SERVER['REQUEST_URI'], 0,
                strcspn($_SERVER['REQUEST_URI'], "\n\r")), ENT_QUOTES);

            if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
                $protocol = 'https://';
            } else {
                $protocol = 'http://';
            }
            $host = $_SERVER['HTTP_HOST'];
            if ($_SERVER['SERVER_PORT'] != '' &&
                (($protocol == 'http://' && $_SERVER['SERVER_PORT'] != '80') ||
                    ($protocol == 'https://' && $_SERVER['SERVER_PORT'] != '443'))) {
                $port = ':' . $_SERVER['SERVER_PORT'];
            } else {
                $port = '';
            }
            return $protocol . $host . $port . $php_request_uri;
        }
    }
