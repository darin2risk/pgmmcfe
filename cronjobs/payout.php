<?php

$includeDirectory = "[Path to your includes]/includes/";

include($includeDirectory."requiredFunctions.php");

$litecoinController = new LitecoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

// Pay users who have exceeded their threshold setting

//$resultQ = pg_query("SELECT userid, balance, COALESCE(paid, 0) as paid, COALESCE(sendAddress,'') as sendaddress FROM accountbalance WHERE threshold >= 0.10 AND balance > threshold");
$resultQ = pg_query("SELECT userid, balance, COALESCE(paid, 0) as paid, COALESCE(sendAddress,'') as sendaddress
                        FROM accountbalance WHERE balance >= ((SELECT value from settings where setting = 'share_window')::numeric)
                                                OR balance > threshold");
while ($resultR = pg_fetch_object($resultQ)) {
	$currentBalance = $resultR->balance;
	$paid = $resultR->paid;
	$paymentAddress = $resultR->sendaddress;
	$userId = $resultR->userid;

	if ($paymentAddress != '')
	{
		$isValidAddress = $litecoinController->validateaddress($paymentAddress);
		if($isValidAddress){
			// Subtract TX fee & calculate total amount the pool will pay
			$currentBalance = $currentBalance - $settings->getsetting('transactionfee'); //0.0005;
			$tot_paid = $resultR->paid + $currentBalance;

			// Send the coins!
			// debug
			echo "sending: ". $currentBalance . " to ". $paymentAddress;

			if($litecoinController->sendtoaddress($paymentAddress, $currentBalance)) {
				// Reduce balance amount to zero, update total paid amount, and make a ledger entry
				db_query("UPDATE accountbalance SET balance = 0, paid = ".$tot_paid." WHERE userid = ".$userId);

                db_query("INSERT INTO ledger (userid, transtype, amount, sendaddress) ".
                                            " VALUES ".
                                            "($userId, 'Debit_ATP', $currentBalance, '$paymentAddress')");

			}
		}
	}
}
