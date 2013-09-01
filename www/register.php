<?php
include ("includes/templates/header.php");
require_once("includes/dbUniversalTranslator.php");
//Test registration information
$returnError = "";
$goodMessage = "";
if (isset($_POST["act"]))
	{
	$act = $_POST["act"];
	if($act == "attemptRegister"){
		//Valid date all fields
		$username	= db_real_escape_string($_POST["user"]);
		$pass		= db_real_escape_string($_POST["pass"]);
		$rPass		= db_real_escape_string($_POST["pass2"]);
		//$token		= db_real_escape_string($_POST["token"]);
		$email		= db_real_escape_string($_POST["email"]);
		$email2		= db_real_escape_string($_POST["email2"]);
		$authPin	= db_real_escape_string($_POST["authPin"]);

		$validRegister = 1;
			
			//Validate token
			// $sql="select token_from, date_redeemed from invite_tokens where token = '$token'";
			// $tok=db_fetch_array(db_query($sql));
			// if($tok['token_from']=="" || $tok['date_redeemed'] != ""){
				// $validRegister = 0;
				// echo(print_r($tok,true));
				// $returnError .= "Invalid Registration Token";
			// }
			
			//Validate username
			if (!preg_match('/^[a-z\d_]{4,20}$/i', $username)) {
				$validRegister = 0;
			   	$returnError .= " | Wrong username format or username too short";
			}

			//Validate passwords
			if($pass != $rPass){
				if(strlen($pass) < 5){
					$validRegister = 0;
					$returnError .= " | Password is too short";
				}else{
					$validRegister = 0;
					$returnError .= " | Passwords do not match";
				}
			}

			//Email Validation
			if ($email !== "") {
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$validRegister = 0;
				    	$returnError .= " | Wrong email address format.";
				}else{
					//Validate that emails match
					if($email != $email2){
						$validRegister = 0;
						$returnError .= " | Emails didn't match!";
					}
				}
			}

			//validate authpin
			if(strlen($authPin) >= 4){
				if(!is_numeric($authPin)){
					$validRegister = 0;
					$returnError .= " | Not a valid authpin";
				}
			}else{
				$validRegister = 0;
				$returnError .= " | Authorization pin number is not valid";
			}

		if($validRegister){
			//Add user to webUsers
			$emailAuthPin = genRandomString(10);
			$secret = genRandomString(10);
			$apikey = hash("sha256",$username.$salt);
			//Check to see if user exists already
			$testUserQ = db_query("SELECT id FROM webusers WHERE username = '".$username."' LIMIT 1");
			//If not, create new user
			//if (!$testUserQ) {
			if (($testUserQ == false) || (db_num_rows($testUserQ) == 0)) {
				db_query("BEGIN");
				$result = db_query("INSERT INTO webusers (admin, username, pass, email, emailauthpin, secret, loggedip, sessiontimeoutstamp, accountlocked, accountfailedattempts, pin, share_count, stale_share_count, shares_this_round, api_key)
				VALUES (0, '".$username."', '".hash("sha256", $pass.$salt)."', '".$email."', '".$emailAuthPin."', '".$secret."', '0', '0', '0', '0', '".hash("sha256", $authPin.$salt)."', '0', '0', '0', '".$apikey."') RETURNING id");
				$tmp=pg_fetch_row($result);
				$returnId = $tmp[0];//db_insert_id();
				db_query("INSERT INTO accountbalance (userid, balance) VALUES (".$returnId.",0)");
				db_query("INSERT INTO pool_worker (associateduserid, username, password) VALUES (".$returnId.",'".$username.".1','x')");
				//db_query("UPDATE invite_tokens SET token_to = '".$username."', date_redeemed = now() WHERE token = '$token'");
				db_query("COMMIT");
				$goodMessage = "Your account has been successfully created. Please login to continue.";
			} else {
				db_query("ROLLBACK");
				$returnError = "Account already exists. Please choose a different username.";
			}
		}
	}
}

include("includes/templates/register.php");
include("includes/templates/footer.php");

?>
