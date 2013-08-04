<?php

require '../src/facebook.php';

$facebook = new Facebook(array(
  'appId'  => '570695096297476',
  'secret' => '92fc9347b5f88a550f26da934e85afca',
));

// See if there is a user from a cookie
$user = $facebook->getUser();

if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
      $albums = $facebook->api('/me?fields=albums.fields(cover_photo,name)&access_token=' .$facebook->getAccessToken());
     // $user_profile = $facebook->api('/me/albums?access_token=' .$facebook->getAccessToken());

  } catch (FacebookApiException $e) {
    echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
    $user = null;
  }
}

?>
<!DOCTYPE html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <body>
    <?php if ($user) { ?>
      Your user profile is
<!--        --><?php //print htmlspecialchars(print_r($albums, true)) ?>
          <?php
            for($i=0;$i<count($albums['albums']['data']);$i++)
            {
                $photos = $facebook->api("/{$albums['albums']['data'][$i]['id']}/photos");
                foreach($photos['data'] as $photo)
                {
                    echo "<img src={$photo['source']} />", "<br />";
                }
            }
          ?>

    <?php } else { ?>
      <fb:login-button></fb:login-button>
    <?php } ?>
    <div id="fb-root"></div>
    <script>
      window.fbAsyncInit = function() {
        FB.init({
          appId: '<?php echo $facebook->getAppID() ?>',
          cookie: true,
          xfbml: true,
          oauth: true
        });

        FB.Event.subscribe('auth.login', function(response) {
          window.location.reload();
        });
        FB.Event.subscribe('auth.logout', function(response) {
          window.location.reload();
        });
      };
      (function() {
        var e = document.createElement('script'); e.async = true;
        e.src = document.location.protocol +
          '//connect.facebook.net/en_US/all.js';
        document.getElementById('fb-root').appendChild(e);
      }());
    </script>
  </body>
</html>
