<?
class twit(){
	__INIT__($sendTo, $id, $data){
		$sendTo = $this->sendTo;
		$id = $this->id;
		$data = $this->$data;
	}



// The OAuth credentials you received when registering your app at Twitter

define("TWITTER_CONSUMER_KEY", $consumerKey);
define("TWITTER_CONSUMER_SECRET", $consumerSecret);

// The OAuth data for the twitter account

define("OAUTH_TOKEN", $accessToken);
define("OAUTH_SECRET", $accessTokenSecret);	


public function incomingDirectMessage($sendTo, $id, $data){

}

// example twit->rebootG()
public function rebootG($sendTo, $id, $data){
	exec("sudo reboot");
}


public function urlG($sendTo, $id, $data){
	// this function will parse the sendTo , $id, $data (array)
	// data array contains all twitter metadata and content

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


	// Hash = #url - prepare and execute html to image - outputted file is $tempImgPath
	htmlToImage($expanded_url, $timeParam, $tempImgPath);

	// Hash = #url - send tweet to account name (current account), 
	twitSendImage($sendTo, "$expanded_url - ID: $id", $tempImgPath);

}


public function htmltoimage($sendTo, $id, $data){
	echo "#-- htmlToImage() with $url started temp is $tempImgPath --#\n";
	$height = "768";
	$width = "100";
	
	$url_escaped = escapeshellarg($url);
	$termOut = exec("/usr/local/bin/wkhtmltoimage --load-error-handling ignore --height 1027 --width 600 --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);
	//$termOut = exec("/usr/local/bin/wkhtmltoimage --width $width --height $height --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);
	//$termOut = exec("/usr/local/bin/wkhtmltoimage --load-error-handling ignore --height 1027 --width 768 --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);

	return $cmdOutput;
}


public function twitSendImage($sendTo, $id, $data){

}

public function getReddit($sendTo, $id, $data){

}

public function removeHashes($sendTo, $id, $data){

}

public function htmlToImageDirect($sendTo, $id, $data){
	echo "#-- htmlToImage() with $url started temp is $tempImgPath --#\n";
	$height = "768";
	$width = "100";
	
	$url_escaped = escapeshellarg($url);
	$termOut = exec("/usr/local/bin/wkhtmltoimage --load-error-handling ignore --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);
	//$termOut = exec("/usr/local/bin/wkhtmltoimage --width $width --height $height --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);
	//$termOut = exec("/usr/local/bin/wkhtmltoimage --load-error-handling ignore --height 1027 --width 768 --javascript-delay $timeParam $url_escaped $tempImgPath", $cmdOutput);

	return $cmdOutput;

}

public function searchGetI($sendTo, $id, $data){
//getGoogleSearch($key, $cx, $query)
	$id = $data['id'];
	$randomInt = rand(500000, 1000000);
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

public function searchGet($sendTo, $id, $data){

}

public function reddit($sendTo, $id, $data){

}


}



?>