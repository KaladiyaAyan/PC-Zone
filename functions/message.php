<?php

// fubctions for create message function 
function message($type, $icon, $title)
{
  echo "MEessage";
  $_SESSION['message'] = [
    "type" => $type,
    "icon" =>  $icon,
    "title" => $title
  ];
}
