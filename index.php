<?php
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';
require_once 'dbconfig.php';

$fb = new Facebook\Facebook([
  'app_id' => '1526616007668429',
  'app_secret' => '6c732a121fdea39f2a1ac258b4cab67a',
  'default_graph_version' => 'v2.5',
  ]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; // optional
	
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

	// redirect the user back to the same page if it has "code" GET variable
	if (isset($_GET['code'])) {
		header('Location: ./');
	}

	// getting basic info about user
	try {
		$profile_request = $fb->get('/me?fields=name,first_name,last_name,email,birthday');
		$profile = $profile_request->getGraphNode()->asArray();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		session_destroy();
		// redirecting user back to app login page
		header("Location: ./");
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
    }
	
	
	//inserting id and accessToken on database
	$query = "INSERT into accesstoken values($profile[id])";
	mysql_query($query);
	


  	// Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
} else {

	// replace your website URL same as added in the developers.facebook.com/apps e.g. if you used http instead of https and you used non-www version or www version of your website then you must add the same here
	$loginUrl = $helper->getLoginUrl('http://myphpapp-hajurkoaaja.rhcloud.com/', $permissions);
	echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
	
	// sending notification to user	
	if(isset($_GET['title']) && $_GET['key']==$key && isset($_GET['class'])){
		$class = $_GET['class'];
		$title = $_GET['title'];
		$description = $_GET['description'];

		$query = "SELECT id FROM accesstoken where class=$class";
		$result = mysql_query($query);
	
		if(mysql_num_rows($result)>0){
        	while($row=mysql_fetch_row($result)){
			echo "string";		
			$sendNotif = $fb->post('/' . $row[0] . '/notifications', array('href' => '?true=43', 'template' => $title), accesstoken);
		}
	}
	}
}
	

?>
