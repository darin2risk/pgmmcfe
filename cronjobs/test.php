#!/usr/bin/php
<?php

for($u=0;$u<12;$u++){
  $transactions[$u]["category"] = "confirmed";
}


for($i=0;$i<12;$i++){
  if (($transactions[$i]["category"] = "immature") || ($transactions[$i]["category"] = "immature")){
    echo("yes.\n");
  }else{
    echo("no.\n");
  }
}