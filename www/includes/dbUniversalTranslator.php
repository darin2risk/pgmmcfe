<?php

/*
 This is another part of my aborted attempt at a run-time 
 configurable db platform. I have not given up on the idea,
 which is why the skeleton is here.
 
 THIS VERSION ONLY WORKS WITH POSTGRES!!!
 
 THIS VERSION DOES _NOT_ WORK WITH MYSQL!!!
 
*/

require_once("requiredFunctions.php");

if($dbType=="mysql"){
  require_once('mysql.inc.php');
}else if($dbType=="postgresql"){
  require_once('postgres.inc.php');
}


?>