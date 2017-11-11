<?php
require_once __DIR__ . '/../lib/UserstreamPhirehose.php';
require_once __DIR__ . "/../../twitter-php/src/twitter.class.php";
//require_once __DIR__ . "/klib/google.php";

$config_json_data = file_get_contents(__DIR__ . "/config.json");
$config_json_decoded = json_decode($config_json_data, true);
$account_name = "DefSoul_KW";
$account = $config_json_decoded["twitter_accounts"][$account_name];
$consumerKey = $account["auth"]["consumerKey"];
$consumerSecret = $account["auth"]["consumerSecret"];
$accessToken = $account["auth"]["accessToken"];
$accessTokenSecret = $account["auth"]["accessTokenSecret"];

// The OAuth credentials you received when registering your app at Twitter

define("TWITTER_CONSUMER_KEY", $consumerKey);
define("TWITTER_CONSUMER_SECRET", $consumerSecret);

// The OAuth data for the twitter account

define("OAUTH_TOKEN", $accessToken);
define("OAUTH_SECRET", $accessTokenSecret);
/**
 * Barebones example of using UserstreamPhirehose.
 */
class MyUserConsumer extends UserstreamPhirehose
{
	/**
	 * First response looks like this:
	 *    $data=array('friends'=>array(123,2334,9876));
	 *
	 * Each tweet of your friends looks like:
	 *   [id] => 1011234124121
	 *   [text] =>  (the tweet)
	 *   [user] => array( the user who tweeted )
	 *   [entities] => array ( urls, etc. )
	 *
	 * Every 30 seconds we get the keep-alive message, where $status is empty.
	 *
	 * When the user adds a friend we get one of these:
	 *    [event] => follow
	 *    [source] => Array(   my user   )
	 *    [created_at] => Tue May 24 13:02:25 +0000 2011
	 *    [target] => Array  (the user now being followed)
	 *
	 * @param string $status
	 */
	public
	
	function enqueueStatus($status)
	{
		/*
		* In this simple example, we will just display to STDOUT rather than enqueue.
		* NOTE: You should NOT be processing tweets at this point in a real application, instead they
		*  should be being enqueued and processed asyncronously from the collection process.
		*/
		$data = json_decode($status, true);

		//  echo date("Y-m-d H:i:s (").strlen($status)."):".print_r($data,true)."\n";
		// print_r($data);
		
		
		// check if its a tweet
		
		if (!empty($data['direct_message'])) {
			// its a direct message
			incomingDirectMessage($data['user']['screen_name'], $data['id'], $data);
		}
		
		if (!empty($data['id'])) {
			// must be an incoming tweet -  lets continue 
			// incomingTweet($sendTo ,$id, $hashtagsObj, $urlsObj);
			incomingTweet($data['user']['screen_name'] , $data['id'], $data);
		}
		
	}
}

// OUTSIDE OF CLASS
function incomingDirectMessage($sendTo, $id, $inObj){
	// validate
	if (empty($id)) {
		$e = "\n Problem.. No ID exist. Need ID to continue\n";
		echo $e;
		return $e;
	}

	
	
		//     if (!empty($data['direct_message'])){
		//       $hashtags = [];
		//       if (!empty($data['direct_message']['entities']['hashtags'])){
		//         foreach ($data['direct_message']['entities']['hashtags'] as $item) {
		//           array_push($hashtags, $item['text']);
		//         }
		//         if ($hashtags[0] == "ssh"){
		// 					try {
		// 						$cmd = $data['direct_message']['text'];
		// 						foreach ($hashtags as $hash){
		// 							$cmd = str_replace("#$hash ", "", $cmd);
		// 							$cmdParse = escapeshellarg($cmd);
		// 						}
		// 						//exec($cmdParse, $output);
		// 						//$output = implode(" ",$output);
		// // 						echo "exec($cmdParse, $output);" . "\n";
		// // 						if (empty($output)){
		// // 							$output = "Problsem....";
		// // 						}
		// 						$cmdV = $cmdParse;
		// 						$descriptorspec = array(
		// 							 0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
		// 							 1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
		// 							 2 => array("pipe", "w")    // stderr is a pipe that the child will write to
		// 						);
		// 						flush();
		// 						$process = proc_open($cmdV, $descriptorspec, $pipes, realpath('./'), array());
		// 						//echo "<pre>";
		// 						if (is_resource($process)) {
		// 								while ($s = fgets($pipes[1])) {
		// 										print $s;
		// 										try {
		// 											$twitter = new Twitter($GLOBALS["consumerKey"], $GLOBALS["consumerSecret"], $GLOBALS["accessToken"], $GLOBALS["accessTokenSecret"]);
		// 											$twitDir = $twitter->sendDirectMessage("DefSoul_KW", "$s");
		// 										} catch (Exception $e) {
		// 												//echo 'Caught exception: ' .Â  $e->getMessage() . "\n";
		// 										}
		// 										flush();
		// 								}
		// 						}
		// 						//echo "</pre>";
		// 					} catch (Exception $e) {
		// 						//echo 'Caught exception: ' .Â  $e->getMessage() . "\n";
		// 					}
		// // 					try {
		// // 						$twitter = new Twitter($GLOBALS["consumerKey"], $GLOBALS["consumerSecret"], $GLOBALS["accessToken"], $GLOBALS["accessTokenSecret"]);
		// // 						$twitDir = $twitter->sendDirectMessage("DefSoul_KW", "$output");
		// // 					} catch (Exception $e) {
		// // 							//echo 'Caught exception: ' .Â  $e->getMessage() . "\n";
		// // 					}
		//         }
		//       }
		//     }
}

function incomingTweet($sendTo ,$id, $data){
	// debug info
	echo "\n\n";
	echo "------------------------------------------------START------------------------------------------------------\n";
	
	// define some variables
	$expanded_urls = [];
	$hashtags_texts = [];

	echo "#-- Start of debug of post --#\n";
	if (strpos($data['source'], 'appkieron') !== false || strpos($data['source'], 'DefSoul_B_App') !== false) {
		// post is response from bot - dont process
		$e = "\n!-- Post is response from bot - don't process --!\n";
		echo $e;
		
		//print_r($data);
		
		return $e;
	}

	// validate if we have hashtags - if not return
	if (empty($data['entities']['hashtags'])){
		$e = "\n!-- Not a request - AKA normal tweet.. No hashtags exist. Need hashtag to continue\n" . $data['text'] . " --!\n";
		echo $e;

		//print_r($data);
		
		return $e;
	}
	
	print_r($data);
	echo "#-- End of debug of post --#\n";

	// push all hashtags into an array
	echo "#-- Pushing hashtags into array --#\n";
	foreach($data['entities']['hashtags'] as $item) {
		array_push($hashtags_texts, $item['text']);
	}



	// hashtag detection for logic
	if ($hashtags_texts[0] == "search"){
		echo "#-- #search detected --#\n";
		googleSearch($sendTo, $data, $hashtags_texts);
	}

	// Hash = #url - parsing url with optional timer param
	if ($hashtags_texts[0] == "url") {
		echo "#-- #url detected --#\n";
		urlParse($sendTo, $data, $hashtags_texts);
	}
	
	// Hash = #reddit - parsing reddit
	if ($hashtags_texts[0] == "reddit") {
		echo "#-- #reddit detected --#\n";
		reddit($sendTo, $data, $hashtags_texts);
	}
	
	// Hash = #searchget - parsing google search select array item
	if ($hashtags_texts[0] == "searchGet") {
		echo "#-- #searchget detected --#\n";
		searchGet($sendTo, $data, $hashtags_texts);
	}

	// Hash = #searchget - parsing google search select array item
	if ($hashtags_texts[0] == "searchGetI") {
		echo "#-- #searchget detected --#\n";
		searchGetI($sendTo, $data, $hashtags_texts);
	}
	
		
	echo "\n-------------------------------------------------END-------------------------------------------------------\n\n\n\n";
}










function reddit($sendTo, $data, $hashtags_texts){
	$id = $data['id'];
	$tempImgPath = __DIR__ . "/temp/$id.jpg";
	$filter = "hot";
	$limit = "10";
	$sub = $hashtags_texts[1];
	
	try {
		foreach ($hashtags_texts as $hash){
			if (strpos($hash, 'filter') !== false ){
				$hash = str_replace("filter", "", $hash);
				$filter = $hash;
				
				echo "#-- filter detected $hash --#\n";
			}
			
			if (strpos($hash, 'limit') !== false ){
				$hash = str_replace("limit", "", $hash);
				$limit = $hash;
				
				echo "#-- limit detected $hash --#\n";
			}
		}
	} catch (Exception $e) {
			echo 'Caught exception: ' . $e->getMessage() . "\n";
	}
	
	
	$articles = getReddit($sub, $filter, $limit);
	//echo "\n\n\n\n ITEMS ---- " . print_r($articles) . "\n\n";
	
	foreach ($articles as $item){
		$imgExts = array(
			"gif",
			"jpg",
			"jpeg",
			"png",
			"tiff",
			"tif",
		);
		
		$urlExt = pathinfo($item['url'], PATHINFO_EXTENSION);
		if (in_array($urlExt, $imgExts)) {
			echo "#-- Image detected --#\n";
			
			$htmlPath = __DIR__ . "/image.html";
			$url = injectHtmlFile($item['url'], $htmlPath);

		}else{
			// just get its url and htmltoimage
			$url = $item['url'];
		}
		
		$title = $item["title"];
		$message = "Title: ".$title."\n"."ID: ".substr($id, 0, -15);
		if (strlen($message) >= 120){
			$cut = round(strlen($title) / 2);
			$title = substr($title, 0, -$cut);
			$title = substr($title, 0, -20);
		} 
		$message = "Sub: " . $item["subreddit"] . "\nTitle: ".$title."\n"."ID: ".substr($id, 0, -15);
		
		
		usleep(rand(1000000, 3000000));
		htmlToImage($url, $timeParam = "2000", $tempImgPath);
		twitSendImage($sendTo, $message, $tempImgPath);	
	}

}








function injectHtmlFile($injection, $htmlPath){
	//$htmlPath = __DIR__ . "/image.html";
	
	$html = "<html>
	<body>
	<image style='max-width: 728px;display: block;margin: 0 auto;' id='imageA' src='" . $injection . "'></image>
	</body>
	</html>";

	echo "#-- Pushing html to image.html --#\n";
	
	$htmlFile = fopen($htmlPath, "w") or die("Unable to open file!");
	fwrite($htmlFile, $html);
	fclose($htmlFile);

	return $htmlPath;
}







function searchGet($sendTo, $data, $hashtags_texts){
	//getGoogleSearch($key, $cx, $query)
	$id = $data['id'];
	$tempImgPath = __DIR__ . "/temp/$id.jpg";
	$key = "AIzaSyC2a8AYvUPlScKtPXf12lGXNwQSqFxKCi4";
	$cx = "016964280318240832222:fvcr2r3ezkg";
	$get = "0";
	$items_array = [];
	$hash = $hashtags_texts[1];
	$searchType ="get";
	
	echo "#-- get detected $hash --#\n";

	$get = (int)trim(str_replace("get", "", $hash));

	$message = trim(removeHashes($data['text'], $hashtags_texts));

	echo "#-- googleSearchSelect() using $message";
	$json_res = json_decode(getGoogleSearch($key, $cx, $message));

	foreach ($json_res->items as $item){
		array_push($items_array, $item->link);
	}

	//print_r($json_res);

	echo "\n#-- Getting result of array index given from hash\n\n";
	echo $items_array[$get];
		

	// sleep delay - randomized
	usleep(rand(1000000, 5000000));

	// Hash = #search - prepare and execute html to image - outputted file is tempImgPath
	htmlToImage($items_array[$get], $timeParam = "2000", $tempImgPath);
	
	// Hash = #search - send tweet to account name (current account) - twitSendImage(sendTo, message, image);
	twitSendImage($sendTo, "Type: $searchType - $message - ID: $id", $tempImgPath);	
}








function rebootG(){
	exec("sudo reboot");
}



function 










function searchGetI($sendTo, $data, $hashtags_texts){
	//getGoogleSearch($key, $cx, $query)
	$id = $data['id'];
	$tempImgPath = __DIR__ . "/temp/$id.jpg";
	$key = "AIzaSyC2a8AYvUPlScKtPXf12lGXNwQSqFxKCi4";
	$cx = "016964280318240832222:fvcr2r3ezkg";
	$get = "0";
	$items_array = [];
	$hash = $hashtags_texts[1];
	$searchType ="getI";
	
	echo "#-- get detected $hash --#\n";

	$get = (int)trim(str_replace("get", "", $hash));

	$message = trim(removeHashes($data['text'], $hashtags_texts));

	echo "#-- googleSearchSelect() using $message";
	$json_res = json_decode(getGoogleSearch($key, $cx, $message));

	foreach ($json_res->items as $item){
		array_push($items_array, $item->link);
	}

	//print_r($json_res);

	echo "\n#-- Getting result of array index given from hash\n\n";
	echo $items_array[$get];


	//$url = $items_array[$get];

	htmlToImage($items_array[$get], $timeParam = "2000", $tempImgPath);
	
	$htmlPath = __DIR__ . "/image.html";
	$url = injectHtmlFile($tempImgPath, $htmlPath);

	// sleep delay - randomized
	usleep(rand(1000000, 5000000));

	// Hash = #search - prepare and execute html to image - outputted file is tempImgPath
	htmlToImage($url, $timeParam = "2000", $tempImgPath);
	
	// Hash = #search - send tweet to account name (current account) - twitSendImage(sendTo, message, image);
	twitSendImage($sendTo, "Type: $searchType - $message - ID: $id", $tempImgPath);	
}









function googleSearch($sendTo, $data, $hashtags_texts){
	// #url https://www.google.com/search?site=&tbm=isch&source=hp&biw=1366&bih=672&q=cats&oq=cats
	$id = $data['id'];
	$tempImgPath = __DIR__ . "/temp/$id.jpg";
	$queryBase = "";
	$searchType = "";
	$query = "";
	$url = "";

	
	if (in_array("images", $hashtags_texts)){
		$searchType = "images";
	}else{
		$searchType = "normal";
	}
	
	if ($searchType === "images"){
		echo "#-- Search type is images search --#\n";
		$queryBase = "https://www.google.com/search?site=&tbm=isch&source=hp&q=";
	}
	
	if ($searchType === "normal"){
		$queryBase = "https://www.google.com/search?site=&q=";
	}

	$query_raw = $data['text'];
	$query = removeHashes($query_raw, $hashtags_texts);
	$query_url = $queryBase . trim($query);
	$url = $query_url;

	// sleep delay - randomized
	usleep(rand(1000000, 5000000));

	// Hash = #search - prepare and execute html to image - outputted file is tempImgPath
	htmlToImage($url, $timeParam = "2000", $tempImgPath);
	
	// Hash = #search - send tweet to account name (current account) - twitSendImage(sendTo, message, image);
	twitSendImage($sendTo, "Type: $searchType - $query - ID: $id", $tempImgPath);	
}











function urlParse($sendTo, $data, $hashtags_texts){
	$expanded_urls = [];
	$id = $data['id'];

	// Hash = #url - use ID of tweet for image filename
	$tempImgPath = __DIR__ . "/temp/$id.jpg";

	// Hash = #url - get the full expanded_url from message
	foreach($data['entities']['urls'] as $urlItem) {
		echo "#-- Getting full expanded_urls --#\n";
		array_push($expanded_urls, $urlItem['expanded_url']);
	}


	// Hash = #url - checking if optional timer param used
	if (!empty($hashtags_texts[1])) {
		// Hash = #url - time param is used continue to parse the param
		$timeParam = str_replace("t", "", $hashtags_texts[1]);
		echo "#-- Time paramater used of value $timeParam --#\n";
	}
	else {
		// Hash = #url - timer param was not used so default is set
		echo "#-- Time paramater not used default value 2000 --#\n";
		$timeParam = "2000";
	}

	// Hash = #url - get expanded url of link - eg google.com
	$expanded_url = $expanded_urls[0];

	// sleep delay - randomized
	usleep(rand(1000000, 5000000));

	// Hash = #url - prepare and execute html to image - outputted file is $tempImgPath
	htmlToImage($expanded_url, $timeParam, $tempImgPath);

	// Hash = #url - send tweet to account name (current account), 
	twitSendImage($sendTo, "$expanded_url - ID: $id", $tempImgPath);
}











function htmlToImage($url, $timeParam, $tempImgPath){
	echo "#-- htmlToImage() with $url started temp is $tempImgPath --#\n";
	$height = "768";
	$width = "100";
	
	$url_escaped = escapeshellarg($url);
	$termOut = exec("/usr/local/bin/wkhtmltoimage --load-error-handling ignore --height 1027 --width 600 --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);
	//$termOut = exec("/usr/local/bin/wkhtmltoimage --width $width --height $height --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);
	//$termOut = exec("/usr/local/bin/wkhtmltoimage --load-error-handling ignore --height 1027 --width 768 --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);

	return $cmdOutput;
}









function htmlToImageInject($url, $timeParam, $tempImgPath, $injection){
	echo "#-- htmlToImage() with $url started temp is $tempImgPath --#\n";
	$height = "768";
	$width = "100";
	
	$url_escaped = escapeshellarg($url);
	$termOut = exec("/usr/local/bin/wkhtmltoimage --load-error-handling ignore --height 1027 --width 600 --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);
	//$termOut = exec("/usr/local/bin/wkhtmltoimage --width $width --height $height --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);
	//$termOut = exec("/usr/local/bin/wkhtmltoimage --load-error-handling ignore --height 1027 --width 768 --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);

	return $cmdOutput;
}











function twitSendImage($sentTo, $tweetMessage, $imagePath){
	echo "#-- twitSendImage() started using $imagePath --#\n";
	$twitter = new Twitter($GLOBALS["consumerKey"], $GLOBALS["consumerSecret"], $GLOBALS["accessToken"], $GLOBALS["accessTokenSecret"]);
	try {
		// download image
		file_get_contents($imagePath);
		
		// send tweet with image - Can be array
		$tweet = $twitter->send("@$sentTo " . "\n" . $tweetMessage, $imagePath); // you can add $imagePath or array of image paths as second argument
		unlink($imagePath);
	}

	catch(TwitterException $e) {
		echo 'Error: ' . var_dump($e->getMessage());
	}
}
















function getReddit($sub, $filter, $limit)
{

	// https://www.reddit.com/r/funny/top.json?limit=100
	// $sub = sub reddit to scrape
	// $filter = filter on reddit eg, top, new, hot
	// $limit = ammount of posts to scrape

	echo "#-- getReddit() started --#\n";
	echo "\r\n";
	$url = "https://www.reddit.com/r/$sub/$filter.json?limit=$limit";
	echo $url . "\r\n";
	$json = file_get_contents($url);
	$json_data = json_decode($json, true);
	$post_hint = "";
	$out = [];
	foreach($json_data["data"]["children"] as $item) {
		if (empty($item["post_hint"])){
			$post_hint = "link";
		}
		
		$data = array(
			"subreddit" => $sub,
			"title" => $item["data"]["title"],
			"thumbnail" => $item["data"]["thumbnail"],
			"url" => $item["data"]["url"],
			"post_hint" => $post_hint
		);
		
		array_push($out, $data);
	}

	//print_r($json_data["data"]["children"]);
	return $out;
}






function removeHashes($text, $hashtags_texts){
	try {
		foreach ($hashtags_texts as $hash){
			$text = str_replace("#$hash", "", $text);
		}
		$out = trim($text);
	} catch (Exception $e) {
			echo 'Caught exception: ' . $e->getMessage() . "\n";
	}
	
	return $out;
}






function getGoogleSearch($key, $cx, $query){
	$key = "AIzaSyC2a8AYvUPlScKtPXf12lGXNwQSqFxKCi4";
	$cx = "016964280318240832222:fvcr2r3ezkg";
	$query = urlencode($query);
	$url =  "https://www.googleapis.com/customsearch/v1?q=$query&key=$key&cx=$cx";

	//$test = "https://www.googleapis.com/customsearch/v1?q=iplwiki&key=AIzaSyC2a8AYvUPlScKtPXf12lGXNwQSqFxKCi4&cx=016964280318240832222:fvcr2r3ezkg";


	$agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_URL,$url);
	$result=curl_exec($ch);

	return $result;
}











// Start streaming

$sc = new MyUserConsumer(OAUTH_TOKEN, OAUTH_SECRET);
$sc->consume();
	
?>