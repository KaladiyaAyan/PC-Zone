<?php

// fubctions for create message function 
function message($type, $icon, $title)
{
  $_SESSION['message'] = [
    "type" => $type,
    "icon" =>  $icon,
    "title" => $title
  ];
}
