<?php
  
  
  // SET THIS FILE'S URL AS YOUR BOT'S CALLBACK
  
  
  require 'groupMeApi.php'; // functions for the bot to use
  require 'responseArrays.php'; // arrays of responses that the bot might use. In another file to keep this one shorter.
  
  /* **************************************************
    SETTINGS
	************************************************** */
  $br = "\r\n"; // Useful shorthand when you need to have a line break in a bot message
  $bMakeGeneralResponses = true; // Determine whether to respond at any mention of the bot, or just when specific requests are found
  
  /* **************************************************
    USER VARIABLES
	************************************************** */
  $botId   = "_BOT_ID_HERE_";
  $groupMe = new groupMeApi();
  
  
  /* **************************************************
    Get post data from the callback
	************************************************** */
  $postdata = file_get_contents("php://input"); // Payload for bot callback will be viewable in php://input
  $p = json_decode($postdata,true); // using TRUE makes it an associative array
  
  if (!($p)) {
    // No callback data, so we shouldn't do anything (either API callback error, or the page was loaded in a browser rather than used as callback URL
    exit();
	}
  
  /* **************************************************
    
    Format of callback data:
    
    //  {
    //    "id": "123456789012345678",
    //    "source_guid": "1234abcd5678efab9012cdef3456abcd",
    //    "created_at": 1420070400,
    //    "user_id": "12345678"
    //    "group_id": "1234567",
    //    "name": "NameString Here",
    //    "avatar_url": "https://i.groupme.com/1280x1280.jpeg.12345",
    //    "text": "hi",
    //    "system": false,
    //    "attachments": 
    //    [{
    //      "type":"image",
    //      "url":"https://i.groupme.com/460x574.jpeg.12345",
    //   }]
    // }
    
	************************************************** */
  
  // If it's blank (or only an attachment) no need to keep working to see if the bot needs to respond
  if (strlen($p['text']) < 1) {
    exit();
	}
  
  // Store some of the variables for quick use
  $userIdNumber = $p['user_id'];
  $groupId = $p['group_id'];
  $text = $p['text'];
  
  /* **************************************************
    
    Checks for string(s) in a bit of text
    
    $hay       [string]     text to search
    $arrNeedles [array]      array of values to look for
    
    returns     [true/false]   true if any of the needles match
    
	************************************************** */
  function iStr($hay = false,$arrNeedles = false) {
    if ($hay && $arrNeedles) {
      if (!is_array($arrNeedles)) { $arrNeedles = array($arrNeedles); }
      foreach ($arrNeedles as $n) {  
        if (preg_match("/(".preg_quote($n,'/').")/i", $hay) === 1) {
          return true;
				}
			}
		}
    return false;
	}
  
  /* **************************************************
    Select a random element from an array, good for 
    a bot's conversational abilities
	************************************************** */
  function aRand($arr) {
    shuffle($arr);
    return $arr[rand(0,count($arr) - 1)];
	}
  
  /* **************************************************
    Random array of responses for use by the bot
    (so the user knows their chat was received and is being processed)
	************************************************** */
  function makeHangOnString($strRequestType) {
    $aH = array(
    "Sure thing, lemme build that $strRequestType for you. Hang on a sec...",
    "Let me get that $strRequestType for you, hang on a sec...",
    "Let me get that $strRequestType for you, hang tight...",
    "One $strRequestType coming right up, just a sec...",
    "Gotta put that $strRequestType together, hold on...",
    "Let me look that up and make a $strRequestType for you, hold on...",
    "Got it, lemme get the info for a $strRequestType. Hold on..."
    );
    return aRand($aH);
	}
  
  
  if (iStr($p['name'],array('botname')) === false) { // This post wasn't made by the bot, so we'll continue (prevents infinite loop)
    
    // Did the user mention the bot by name? Check multiple variations of the bot's name
    if (iStr($text,array("botname","bot name","bit name"))) {
      
      /* **************************************************
        Check to see what they said, then respond accordingly
			************************************************** */
      
      if (iStr($text,array("thank you","thanks","thankyou"))) {
        //Thanks
        $strResponse = aRand($arrPhrasesWelcome);
			}
      elseif (iStr($text,array("love you","marry me","love","you're awesome"))){
        // Love or praise
        $strResponse = aRand($arrPhrasesPositiveFeedback);
			}
      elseif (iStr($text,array("sorry","I apologize"))){
        // Apology
        $strResponse = aRand($arrPhrasesAfterSorry);
			}
      else { 
        // general response to a mention of the bot
        // Only used if $bMakeGeneralResponses is set to true at the beginning of this file
        if ($bMakeGeneralResponses) {
          $strResponse = aRand($arrPhrasesBasicResponses);
				}
			}
      
      if (!empty($strResponse)) {
        // Post the message
        $groupMe->botPost($groupId,$botId,$strResponse); 
			}
      
		}
	}
  
?>  