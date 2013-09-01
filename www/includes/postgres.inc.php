<?php

function db_query($query){
  return(pg_query($query));
}

function db_fetch_object($res){
  return(pg_fetch_object($res));
}

function db_fetch_row($res){
  return(pg_fetch_row($res));
}

function db_fetch_array($res){
  return(pg_fetch_array($res));
}

function db_execute($stmt,$params){
  return(pg_execute($stmt,$params));
}

function db_real_escape_string($str){
  return(pg_escape_string($str));
}

function db_num_rows($res){
  return(pg_num_rows($res));
}

/**TODO: Need to handle this **/
function db_insert_id(){
  return(pg_insert_id());
}
?>