<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<!--[if IE 8]><html class="no-js lt-ie9" lang="en" ><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="en" xmlns="http://www.w3.org/1999/html"><!--<![endif]-->

<head>
    <title>Album App</title>
<!--    Foundation-->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width" />
    <link rel="stylesheet" href="lib/css/foundation.css" />
    <script src="lib/js/vendor/custom.modernizr.js"></script>
    <link rel="stylesheet" href="lib/css/app.css" />
    <link rel="stylesheet" href="lib/css/jquery.fancybox.css" />

    <style>
    .ajax_loader {
        background: url("lib/images/spinner_squares_circle.gif") no-repeat center center transparent;
        width:100%;
        height:100%;
    }
    </style>
</head>

<!--Script-->
<script src="lib/js/jquery-1.7.1.min.js"></script>
<script src="lib/js/swipe.js"></script>
<script src="lib/js/AlbumApp.js"></script>
<script src="lib/js/foundation.min.js"></script>
<script src="lib/js/vendor/zepto.js"></script>
<script src="lib/js/ajaxloader.js"></script>
<script src="lib/js/jquery.fancybox.js"></script>

<script type="text/javascript">

    $(document).ready(function() {
        $('#dumpLink').hide();
        $('.progress').hide();
    });
    $(document).foundation();

</script>

<body>

    <div class="row" style="max-width: 100%;background-color: #2BA6CB">
        <div class="large-4 columns" style="margin-left: 50px;"><h2 style="color:#f5f5f5">Album App</h2>        </div>

        <div class="button-bar large-7 columns" style="margin-top: 15px">
            <ul class="button-group" style="float: right">

            <?php
                if(isset($_SESSION['sessionToken']))
                {
                    if($_SESSION['sessionToken']=="")
                    {
            ?>
                        <li><a href='<?php echo picasa_login ?>'  class="button secondary">Connect to Picasa</a></li>
               <?php }

                 }else if(isset($_SESSION['user_name'])){?>

                    <li><a href='<?php echo picasa_login ?>'  class="button secondary">Connect to Picasa</a></li>
           <?php }

            if(isset($_SESSION['user_name'])){?>
                <li><a  href='<?php echo $logoutUrl ?>'  class="button secondary">Logout</a></li>
                <?php }
            ?>

            </ul>
       </div>
    </div>

    <?php if(!$user){ ?>

            <div class="row" style="border-style: solid;border-width: 1px;border-color: #d9d9d9;margin-bottom: 1.25em;padding: 1.25em;">

                <div class="large columns">

                    <!-- Grid Example -->
                    <div class="row">
                        <div class="large-8 columns">
                            <div style="margin-top: 130px">
                                <p>Album App is a free service that allows you to move your facebook profile's Album to Picasa.It also allows you to  download all your photos into a single Zip archive!
                                <br><br>Try it! It's free.</p>
                                    <a href="<?php echo $loginUrl; ?>"><img src="lib/images/login2.png"></a>
                            </div>
                        </div>
                        <div class="large-4 columns">
                            <p><img style="width: 100%;" src="lib/images/box.jpg" data-interchange="[lib/images/box.jpg, (default)], [lib/images/box.jpg, (screen and (max-width: 568px))], [lib/images/box.jpg, (small)], [lib/images/box.jpg, (medium)], [lib/images/box.jpg, (large)]" data-uuid="ecb8eefd-9a44-a755-6fb1-b71f655a2c7a"></p>
                        </div>
                    </div>
                    <br><br>
                </div>


    <?php } else {?>

            <div class="row" style="max-width: 100%;background-color: #ffffff;">

                    <div id="dumpLink" class="panel columns" style="z-index: 8050;width: 100%;display:none;padding: 0px;">
                        <p style="font-size: 12px"><a id="zip_link" name="zip_link" onclick="options('Download');">Download Zip</a><br>
                        or <br>
                            Notify me, when archive will be ready for download.<input type=text id=fbEmail placeholder="Enter your Email" ><input type=button value='Notify' onclick="options('Notify');"><span id='emailmsg' style='color: red;display: none'></span>
                        </p>
                    </div>
                    <input type=hidden id=move />
                    <input type=hidden id=albumlist />
		      <input type=hidden id=photoId />

                    <ul class="button-group" style="float: right">

                        <li><input id='da' type="button" class="small button" value="Download All" onclick="downloadalbum(this.id);"></li>
                        <li><input id="ds" type="button" class="small button" value="Download Selected" onclick="downloadalbum(this.id);"></li>
                        <?php
                        if(isset($_SESSION['sessionToken']))
                        {
                            if($_SESSION['sessionToken']!="")
                            {
                        ?>
                        <li><input id="ma" type="button" class="small button" value="Move All" onclick="downloadalbum(this.id);"></li>
                        <li><input id="ms" type="button" class="small button" value="Move Selected" onclick="downloadalbum(this.id);"></li>
                        <?php
                            }
                        }
                        ?>
                    </ul>

            </div>

            <div class="row">
                <div class="row" style="width: 95%;margin-left: 9px;">
                    <div id='alertbox' class="alert-box" style="display:none;">
                        <div id="msg"></div>
                        <a onclick="alertclose();" class="close">&times;</a>
                    </div>

                        <div class="progress success round" style="display: none">
                            <span class="meter" style="width:0%"></span>
                            <br>
                            <lable  style="margin-left: 5px;"><span class="pr_album">Preparing&nbsp;&nbsp;</span><img src='lib/images/ajax-loader.gif' style="margin-left: 10px"></lable>
                        <lable class="pr_photos" style="float: right;margin-right: 5px;"></lable>

                    </div>
                    <br>
                </div>
                <?php
                    if ($user) {
                ?>
                        <script>showAjaxLoader();</script>
                <?php
                //          Proceed knowing you have a logged in user who's authenticated.
                            $albums = $facebook->api('/me?fields=albums.fields(photos.limit(1).fields(source),name)&access_token=' .$facebook->getAccessToken());

			if(isset($albums['albums']['data']))
			{	
                        for($i=0;$i<count($albums['albums']['data']);$i++)
                            {
                                if(isset($albums['albums']['data'][$i]['photos'])){
                                ?>
                                    <ul class="stack">
                                        <input type="checkbox" class="album_check" name="album_checkbox[]" value='<?php echo $albums['albums']['data'][$i]['id'];?>' style="z-index:2;position: absolute;left:15px;top:14px">
						<input type="button"  style="background-image:url(lib/images/icon1.png);height:18px;width:20px;z-index:4;position: absolute;top:14px;right:40px;cursor: pointer" id="<?php echo $albums['albums']['data'][$i]['id'];?>_d" onclick="single(this.id)">
                                        <?php
                                            if(isset($_SESSION['sessionToken']))
                                            {
                                                if($_SESSION['sessionToken']!="")
                                            {   ?>
                                        <input type="button" style="background-image:url(lib/images/icon2.png);height:18px;width:20px;z-index:3;position: absolute;top:14px;right:63px;cursor: pointer" id="<?php echo $albums['albums']['data'][$i]['id'];?>_p" onclick="single(this.id)">
                                        <?php
                                            }
                                        }
                                            set_time_limit(0);
                                       ?>
                                        
                                        <a onclick="viewalbum('<?php echo $albums['albums']['data'][$i]['id'] ?>');">
                                            <li><img src="<?php  echo $albums['albums']['data'][$i]['photos']['data'][0]['source']?>" style="height: 200px;width: 200px;z-index:1" title="<?php echo $albums['albums']['data'][$i]['name']?> " /></li>
                                       </a>

                                   </ul>
                        <?php }
                        }  
			}else
			{
				echo 'There Is no Album....';	
			} ?>
                <script>hideAjaxLoader();</script>

         <?php } ?>
    </div><br>

    <?php }   ?>
    <div id="gallery"> </div>
  </body>
</html>