<?php

include ("includes/templates/header.php");

if(!$cookieValid) {
	header('Location: /');
	exit;
}
//Execute the following based on what $_POST["act"] is set to
$returnError = "";
$goodMessage = "";
if (isset($_POST["act"])) {
	$act = $_POST["act"];

	if($act == "addWorker"){
		//Add worker
		$prefixUsername = $userInfo->username;
		$email = $userInfo->email;
		$inputUser = $prefixUsername.".".db_real_escape_string($_POST["username"]);
		$inputPass = db_real_escape_string($_POST["pass"]);
		$notify_down = db_real_escape_string($_POST["notify_down"]);
		if($notify_down == "send"){ $notify_down = "true"; $email = $userInfo->email; }else{ $notify_down = "false" ; }
		
		//Check if username already exists
		$usernameExistsQ = db_query("SELECT id FROM pool_worker WHERE associateduserid = ".$userId." AND username = '".$inputUser."'");
		$usernameExists = db_num_rows($usernameExistsQ);

		
		if($usernameExists == 0){
			$addWorkerQ = db_query("INSERT INTO pool_worker (associateduserid, username, password, notify_down,email ) VALUES(".$userId.", '".$inputUser."', '".$inputPass."', ".$notify_down.", '".$email."')");
			if($addWorkerQ){
				$goodMessage = "Worker successfully added!";
			}else if(!$addWorkerQ){
				$returnError = "Database Error - Worker was not added :(";
			}
		}else if($usernameExists == 1){
			$returnError = "Worker Name already Exists. Try a different Worker Name.";
		}
	}

	if($act == "Update Worker"){

		//Mysql Injection Protection
		$workerId = db_real_escape_string($_POST["workerId"]);
		$workernum = db_real_escape_string($_POST["workernum"]);
		$password = db_real_escape_string($_POST["password"]);
		$notify_down = db_real_escape_string($_POST["notify_down"]);
		
		
		$prefixUsername = $userInfo->username;
		
		$inputUser = $prefixUsername.".".db_real_escape_string($_POST["workernum"]);

		if($notify_down == "send"){ 
			$notify_down = "true";
			$email = $userInfo->email;
		}else{ 
			$notify_down = "false";
			$email = "";
		}
			
		//Check if username already exists
		$usernameExistsQ = db_query("SELECT id FROM pool_worker WHERE associateduserid = ".$userId." AND username = '".$inputUser."'");
		$usernameExists = db_num_rows($usernameExistsQ);

		if($usernameExists >= 1) {
			// Username already exists - Only allow password update
			db_query("UPDATE pool_worker SET password = '$password', notify_down = $notify_down , email = '$email' WHERE id = $workerId AND associateduserid = '$userId'");
			$goodMessage = "Worker updated.";

		} else {
			//update both
			db_query("UPDATE pool_worker SET username = '$inputUser', password = '$password', notify_down = $notify_down , email = '$email' WHERE id = $workerId AND associateduserid = $userId");
			$goodMessage = "Worker updated.";
		}
	}


	if($act == "Delete Worker"){

		//Mysql Injection Protection
		$workerId = db_real_escape_string($_POST["workerId"]);

		//Delete worker OH NOES!
		db_query("DELETE FROM pool_worker WHERE id = $workerId AND associateduserid = $userId");
		$goodMessage = "Worker successfully deleted.";
	}
}

?>

<div class="block withsidebar">

<?include("includes/templates/headerbar.php");?>

        <div class="block_content">

                <div class="sidebar">
                        <?php include ("includes/leftsidebar.php"); ?>
                </div>          <!-- .sidebar ends -->


                <div class="sidebar_content">

<?php
//Display Error and Good Messages(If Any)
if ($goodMessage) { echo "<div class=\"message success\"><p>".antiXss($goodMessage)."</p></div>"; }
if ($returnError) { echo "<div class=\"message errormsg\"><p>".antiXss($returnError)."</p></div>"; }
?>

                <div class="block" style="clear:none;">
                 <div class="block_head">
                  <div class="bheadl"></div>
                  <div class="bheadr"></div>
                  <h2>Worker Accounts</h2>
                 </div>

                 <div class="block_content" style="padding:10px;">


<ul>
<li><div><h3>Stratum - [need stratum address ]</h3></div></li>
<li><div><h3>Getwork - [need getwork address ]</h3></div></li>
<li></li>
<li><font color="red">
CAUTION! </font>Deletion of a worker could cause all associated shares for that worker to be lost.
Do not delete Workers unless you are certain all of their shares have been counted or that you have never used that worker account.
</li>
</ul>
</font>

<center>
<table border="0" cellpadding="3" cellspacing="3">
<tr><td>Worker Name</td><td>Password</td><td>Active</td><td>Khash/s</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<?php
//Get list of workers from the associatedUserId
$getWorkers = db_query("SELECT id, username, password, active, hashrate, notify_down, email FROM pool_worker WHERE associatedUserId = ".$userId);
while($worker = db_fetch_array($getWorkers)){
?>
<form action="/accountworkers" method="post">
<input type="hidden" name="workerId" value="<?php echo antiXss($worker["id"]); ?>">
<?php
//Display worker information and the forms to edit or update them
$splitUsername = explode(".", $worker["username"]);
$realUsername = $splitUsername[1];
?>
<tr>

<td <?php if ($worker["active"] == "f") { ?>style="color: orange"<? } ?>><? echo antiXss($userInfo->username); ?>.<input type="text" name="workernum" value="<?php echo antiXss($realUsername); ?>" size="10"></td>
<td><input type="text" name="password" value="<? echo antiXss($worker["password"]);?>" size="10"></td>
<td><?php if ($worker["active"] == "t") echo "Y"; else echo "N"; ?>
<td><?php echo antiXss($worker["hashrate"])?></td>
<td><input type="submit" name="act" value="Update Worker" style="padding:5px;"><br/><input type="submit" name="act" value="Delete Worker" style="padding:5px;"></td>
<td><input type="checkbox" name="notify_down" value="send" <? if($worker["notify_down"] == "t"){ echo("checked");}?>> Notify if down </td>
</tr>
</form>
<?php
}
?>
</table>
</center>

<!-- Add new Worker -->
<p><center><h2>Add a New Worker</h2>
<form action="/accountworkers" method="post"><input type="hidden"
	name="act" value="addWorker"><!--  AuthPin:<input type="password"
	name="authPin" size="4" maxlength="4"><br /> -->
<?php echo antiXss($userInfo->username);?>.<input type="text" name="username"
	value="user" size="10" maxlength="20"> &middot; <input type="text"
	name="pass" value="pass" size="10" maxlength="20"> &nbsp <input type="checkbox"
	name="notify_down" value="send">&nbsp Notify if down &nbsp <input type="submit"
	value="Add New Worker" style="padding:5px;"></form>
</p></center>
                </div>          <!-- nested block ends -->
                <div class="bendl"></div>
                <div class="bendr"></div>
                </div>

                </div>          <!-- .sidebar_content ends -->


        </div>          <!-- .block_content ends -->




        <div class="bendl"></div>
        <div class="bendr"></div>

</div>          <!-- .block ends -->

<?php include ("includes/templates/footer.php");?>
