<!DOCTYPE html>
<html >
  <head>
    <meta charset="UTF-8">
    <title>Custom Login Form</title>
    
    
    
    
        <link rel="stylesheet" href="css/style3.css">
<script>
function clear(something) {
	document.getElementById("result").innerHTML = "";
    document.getElementById("result").innerHTML = "<img src='"+something+"'/>";
}
</script>
    
    
    
  </head>
<?php
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';

$fb = new Facebook\Facebook([
  'app_id' => '967468303366130',
  'app_secret' => '4af91a64320f0d8e5646ec19b155a882',
  'default_graph_version' => 'v2.6',
  ]);

$helper = $fb->getRedirectLoginHelper();

$permissions = array(
    'email',
    'user_location',
    'user_birthday',
    'user_friends'
); // optional
	
try {
	if (isset($_SESSION['facebook_access_token'])) {
		$accessToken = $_SESSION['facebook_access_token'];
	} else {
  		$accessToken = $helper->getAccessToken();
	}
} catch(Facebook\Exceptions\FacebookResponseException $e) {
 	// When Graph returns an error
 	echo 'Graph returned an error: ' . $e->getMessage();

  	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
 	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
  	exit;
 }

if (isset($accessToken)) {
	if (isset($_SESSION['facebook_access_token'])) {
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	} else {
		// getting short-lived access token
		$_SESSION['facebook_access_token'] = (string) $accessToken;

	  	// OAuth 2.0 client handler
		$oAuth2Client = $fb->getOAuth2Client();

		// Exchanges a short-lived access token for a long-lived one
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);

		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;

		// setting default access token to be used in script
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}
        // validating the access token
	try {
		$request = $fb->get('/me');
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		if ($e->getCode() == 190) {
			// replace your website URL same as added in the developers.facebook.com/apps e.g. if you used http instead of https and you used non-www version or www version of your website then you must add the same here
	$loginUrl = $helper->getLoginUrl('http://localhost/sample/index1.php', $permissions);
  echo '<body><div id="wrapper"><div class="join">Welcome,</div><div class="lock"></div><div class="clr">Click here to login trough facebook.</div><a class="facebook" href="' . $loginUrl . '">Facebook</a></div></body>';
		}
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	// redirect the user back to the same page if it has "code" GET variable
	if (isset($_GET['code'])) {
		header('Location: ./index1.php');
	}

	// getting basic info about user
	try {
		$requestPicture = $fb->get('/me/picture?redirect=false&height=200'); //getting user picture
		$requestProfile = $fb->get('/me?fields=name,id,email,location,birthday,gender'); // getting basic info
		$friends = $fb->get('/me/taggable_friends?fields=name,picture.width(100).height(100)&limit=100');
		$friends = $friends->getGraphEdge();
		$picture = $requestPicture->getGraphUser();
		$profile = $requestProfile->getGraphUser();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		session_destroy();
		// redirecting user back to app login page
		header("Location: ./index1.php");
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	echo '<body><div id="wrapper1">';
	// showing picture on the screen
	echo "<img src='".$picture['url']."'/>";
	echo '<div class="clr"></div>';
	/*echo 'Name: ' . $profile['name'];
	echo '<div class="clr"></div>';
	echo 'Email: ' . $profile['email'];
	echo '<div class="clr"></div>';
	echo 'Gender: ' . $profile['gender'];
	echo '<div class="clr"></div>';
	echo 'Birthday: ' . $profile['birthday']->format('d-m-Y');
	echo '<div class="clr"></div>';
	echo 'Location: ' . $profile['location']['name'];*/
	//geting friends profile pic
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST["name"];
}
	echo '<form method="post" action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'"> Name: <input type="text" name="name" value="'.$name.'"><input type="submit" name="submit" value="Submit"></form>';
	echo '<div id="result">';
	if ($fb->next($friends)) {
		$allFriends = array();
		$friendsArray = $friends->asArray();
		$allFriends = array_merge($friendsArray, $allFriends);
		while ($friends = $fb->next($friends)) {
			$friendsArray = $friends->asArray();
			$allFriends = array_merge($friendsArray, $allFriends);
		}
	} else {
		$allFriends = $friends->asArray();
		$totalFriends = count($allFriends);
	}
	
		foreach ($allFriends as $key) {
			if( strpos(strtolower($key['name']), $name) !== false ) echo "<a href='javascript:clear(\"".$key['picture']['url']."\");'><img src='".$key['picture']['url']."'/>".$key['name']."</a><br>";
		}
	echo '</div>';
	/*$x = 1; 
	while($x <= 5) {
	echo "<img src='".$friends[$x]['picture']['url']."'/>";
	echo "      ";
	$x++;}*/
        echo '</div></body>';
	// saving picture
	$img = __DIR__.'/'.$profile['id'].'.jpg';
	file_put_contents($img, file_get_contents($picture['url']));

  	// Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
} else {
	// replace your website URL same as added in the developers.facebook.com/apps e.g. if you used http instead of https and you used non-www version or www version of your website then you must add the same here
	$loginUrl = $helper->getLoginUrl('http://localhost/sample/index1.php', $permissions);
  echo '<body><div id="wrapper"><div class="join">Welcome,</div><div class="lock"></div><div class="clr">Click here to login trough facebook.</div><a class="facebook" href="' . $loginUrl . '">Facebook</a></div></body>';
}
?>
</html>