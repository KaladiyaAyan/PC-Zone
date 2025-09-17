<?php

function slugify($string)
{
  // Convert to lowercase, remove special characters and replace spaces with hyphens
  $slug = strtolower(trim($string));
  $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // remove non-alphanum
  $slug = preg_replace('/[\s-]+/', '-', $slug);      // replace spaces with -
  return trim($slug, '-');                           // trim hyphens
}

// function message($type, $icon, $title)
// {
//   $_SESSION['message'] = [
//     "type" => $type,
//     "icon" =>  $icon,
//     "title" => $title
//   ];
// }
