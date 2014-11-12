TumblrOAuth
======================

The PHP Library to support OAuth for Tumblr's REST API.


#Example code

* install-app.php

```PHP
require_once ('config.php');
require_once ('OAuth.php');
require_once ('TumblrOAuth.php');
$_SESSION['oauth_token'] = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
$url = $connection->getAuthorizeURL($request_token['oauth_token']);
header("Location: " . $url );
```

* callback-app.php

```PHP
require_once ('config.php');
require_once ('OAuth.php');
require_once ('TumblrOAuth.php');
if(isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
$_SESSION['oauth_status'] = 'oldtoken';
	header('Location: ./install-app.php');
}

$connection = new TumblrOAuth(
	CONSUMER_KEY,
	CONSUMER_SECRET,
	$_SESSION['oauth_token'],
	$_SESSION['oauth_token_secret']
	);
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
$_SESSION['access_token'] = $access_token;
if(200 == $connection->http_code) {
	header('Location: ./post-app.php');
}
```

* post-app.php

```PHP
require_once ('config.php');
require_once ('OAuth.php');
require_once ('TumblrOAuth.php');
$access_token = $_SESSION['access_token'];
if ( strlen($item_title) >= 10)
{
	$connection = new TumblrOAuth(
		CONSUMER_KEY, 
		CONSUMER_SECRET, 
		$access_token['oauth_token'],    
		$access_token['oauth_token_secret']
	);
	$post = $connection->post('blog/YOUR-BLOG.tumblr.com/post/', array(
		'type' => 'text',
		'format' => 'html',
		'title' => $item_title,
		'tags' => $item_tags,
		'body' => $item_content));
}
