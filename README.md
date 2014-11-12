TumblrOAuth
-------------

The PHP Library to support OAuth for Tumblr's REST API.


Example code
================

1) install-app.php
  
    require_once ('OAuth.php');
    require_once ('TumblrOAuth.php');
    $_SESSION['oauth_token'] = $request_token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
    $url = $connection->getAuthorizeURL($request_token['oauth_token']);
    header("Location: " . $url );

