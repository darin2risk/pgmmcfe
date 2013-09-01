<?php
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
Website Reference:http://www.gnu.org/licenses/gpl-2.0.html
*/
require_once("/usr/share/pear/Mail.php");
require_once("/usr/share/pear/Mail/smtp.php");
// RPC litecoind Credentials
//

/** production 
$rpcType = "http"; 				// http or https
$rpcUsername = ""; 				// username as specified in your bitcoin.conf configuration file
$rpcPassword = ""; 				// password
$rpcHost = "";					// host:port
/**/

/** testcoin **/
$rpcType = "http"; 				// http or https
$rpcUsername = "";		// username as specified in your bitcoin.conf configuration file
$rpcPassword = ""; 				// password
$rpcHost = "";
/**/

//
// DB Credentials
// dbType NEEDS TO BE POSTGRES!!!
// This version will NOT work with MySQL
// 
$dbType = "postgresql";
$dbHost = "";
$dbUsername = "";
$dbPassword = "";
$dbPort = "5432";
$dbDatabasename = "pool_ltc";

// Cookie settings (more info @http://us.php.net/manual/en/function.setcookie.php)
//
$cookieName = ""; 			//Set this to what ever you want (text string)
$cookiePath = "/";							//Choose your path!
$cookieDomain = "";	//Set this to your domain

// TimeZone init
date_default_timezone_set("America/Los_Angeles");

//include("bitcoinController/bitcoin.inc.php");	// Dont touch.
include("litecoinController/litecoin.inc.php"); // Dont touch.

// Salt & Pretzels
$salt = "LTHDH[]EHFuhgu7%&¤Hg783tr7gf¤%¤fyegfredfoGHYFGYe(%/(&%6"; 	// random series of numbers and letters; set it to anything or any length you want.
$cookieValid = false; 				// leave as: false

require_once('dbUniversalTranslator.php');
connectToDb();
include('settings.php');

$settings = new Settings();

//						//
//--------- End Configuration Section ----------//
//						//



function connectToDb(){
	//Set variables to global retireve outside of the scope
	global $dbType, $dbHost, $dbPort, $dbUsername, $dbPassword, $dbDatabasename,$dbConn;

	//Connect to database
	if($dbType=="mysql"){
	    mysql_connect($dbHost, $dbUsername, $dbPassword)or die(pg_last_error());
	    mysql_select_db($dbDatabasename);
    }else if($dbType=="postgresql"){
        $connstring="host=$dbHost port=$dbPort dbname=$dbDatabasename user=$dbUsername password=$dbPassword";
        $dbConn=pg_connect($connstring)or die("Couldn't connect to postgres. '$connstring'\n");
    }else{
        die("Unknown database type specified - $dbType.\n");
    }
    
}

class checkLogin
{
	function checkCookie($input, $ipaddress){
		global $salt;
		connectToDb();
		/*$input comes in the following format userId-passwordhash

		/*Validate that the cookie hash meets the following criteria:
			Cookie Ip: matches $ipaddres;
			Cookie Timeout: Is still greater then the current time();
			Cookie Secret: matches the mysql database secret;
		*/

		//Split cookie into 2 mmmmm!
		$cookieInfo = explode("-", $input);

		//Get "secret" from database
		$getSecretQ	= db_query("SELECT secret, pass, sessiontimeoutstamp FROM webusers WHERE id = ".db_real_escape_string($cookieInfo[0])." LIMIT 1");
		$getSecret	= db_fetch_object($getSecretQ);
		if (isset($getSecret->pass)) { $password = $getSecret->pass; }
		if (isset($getSecret->secret)) { $secret = $getSecret->secret; }
		if (isset($getSecret->sessiontimeoutstamp)) { $timeoutStamp = $getSecret->sessiontimeoutstamp;

			//Create a variable to test the cookie hash against
			$hashTest = hash("sha256", $secret.$password.$ipaddress.$timeoutStamp.$salt);

			//Test if $hashTest = $cookieInfo[1] hash value; return results
			$validCookie = false;
			if($hashTest == $cookieInfo[1]){
				$validCookie = true;
			}
			return $validCookie;
		}
	}

	function returnUserId($input){
		//Just split the cookie to get the userId
		$cookieInfo = explode("-", $input);

		return $cookieInfo[0];
	}
}



function outputPageTitle(){
	if (!isset($settings))
	{
		connectToDb();
		$settings = new Settings();
	}
	//Get page title
	return $settings->getsetting("pagetitle");;
}

function outputHeaderTitle(){
	if (!isset($settings))
	{
		connectToDb();
		$settings = new Settings();
	}
	return $settings->getsetting("websitename");
}

function outputSlogan(){
	if (!isset($settings))
	{
		connectToDb();
		$settings = new Settings();
	}
	return $settings->getsetting("slogan");
}

//Helpfull functions
function genRandomString($length=10) {
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $string = "";

    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string;
}


function antiXss($input) {
	//strip HTML tags from input data
	return htmlentities(strip_tags($input), ENT_QUOTES);
}

function account_type($userid) {
        // return account type from DB
        //
        // 0 = normal account no special treatment
        // 9 = early adopter account 0% fees for life

        $q = db_fetch_object(db_query("SELECT account_type FROM webusers WHERE id='" .$userid. "'"));

        return $q->account_type;
}

function getTotalPaidToUser($uid){
	if($uid=="" || $uid==NULL)
		return(0);
	$ret=db_fetch_object(db_query("SELECT paid from accountbalance where userid = $uid"));
	return($ret->paid);
}

function get_tz_selector($var="tz",$selected){
	
	$tzl=timezone_abbreviations_list();
	$sublist=array();
	$toplist=array();
	//var_dump($tzlist);
	foreach($tzl as $div=>$m){
		foreach($m as $top){
			if( $top['timezone_id'] != "" && !is_array($top['timezone_id']) ){
				if(preg_match("/^[A-Z][A-Z][A-Z].*/",$top['timezone_id'])==1){
					if(!in_array($top['timezone_id'],$sublist))
						$sublist[]=$top['timezone_id'];
				}else{
					if(!in_array($top['timezone_id'],$toplist))
						$toplist[]=$top['timezone_id'];
				}
			}	
		}
	}
	sort($toplist);
	sort($sublist);
	$tzlist=array_merge($sublist,$toplist);
	
	$selector="<select name=\"".$var."\">\n"; 
	foreach($tzlist as $entry){
		if($entry != $selected)
			$selector.="  <option value=\"$entry\">$entry</option>\n";
		else
			$selector.="  <option value=\"$entry\" selected>$entry</option>\n";
		
	}
	$selector.="</select>\n";
	
	return $selector;
	
}

function convertUTCtoUserTimeZone($time,$userInfo){
	if($userInfo->tz == null || $userInfo->tz == "" )
		echo("userInfo is invalid");
	$timestamp = new DateTime("now", new DateTimeZone('UTC'));
	$timestamp->setTimestamp($time);
	$timestamp->setTimezone(new DateTimeZone($userInfo->tz));
	return $date;
}

function convertUserTimeZonetoUTC($time){
	if($userInfo->tz == null || $userInfo->tz == "" )
		echo("userInfo is invalid");
	$timestamp = new DateTime(DateTime::setTimestamp($time), new DateTimeZone($userInfo->tz));
	$timestamp->setTimezone(new DateTimeZone('UTC'));
	return $timestamp;
}

function convertTimeZone($time,$from,$to){
	if($from == null || $to == null || $from == "" || $to == "")
		echo("invalid input to convertTimeZone");
	$timestamp=new DateTime("now", new DateTimeZone($from));
	$timestamp->setTimestamp($time);
	$timestamp->setTimezone(new DateTimeZone($to));
	return $timestamp;
}

// TODO: Store these in the database

function notify_down($email,$worker){
	if(!isset($email) || !isset($worker)){
		echo("Null value, cannot build down worker notification\n");
	}
	$to      = $email;
	$subject = 'Mining Pool Downed Worker Notification';
	$headers = 'From: Pool Operator <do_not_reply@pooldomain.com>' . "\r\n" .
			   'Reply-To: do_not_reply@pooldomain.com' . "\r\n"; //.
			   //'X-Mailer: PHP/' . phpversion();

	$date=date("Y:m:d H:i:s");
	$message = 	"Dear Miner,\n".
			"\n".
			"$date\n".
			"$worker stopped submitting shares.\n".
			"\n".
			"\n".
			"The Pool WatchDog";

	//mail($to, $subject, $message, $headers);
	// OR
	$from="Pool Operator <do_not_reply@lavajumper.com>";
	my_mail($to,$subject,$message,$from);
}

function my_mail($to, $subject, $message, $from){
	$mailinfo['host']='';
	$mailinfo['username']="";
	$mailinfo['password']="";
	$mailinfo['port']="";
	$mailinfo['localhost']="";
	$mailinfo['auth']=true;
	
	$header['Subject']=$subject;
	$header['From']="$from";
	$header['To']=$to;
	$header['Date']=date("r");
		
	$mailer=new Mail_smtp($mailinfo);
	if(!$mailer->send($to,$header,$message)){
		echo("Doh!");
	};
}
?>
