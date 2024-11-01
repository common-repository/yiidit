<?php
function spreadly_api_prepare_json() {
  $json = array();

  $user = wp_get_current_user();

  if (!$user) {
    return false;
  }

  $json["displayName"] = $user->display_name;
  $json["nickname"] = $user->user_login;
  $json["name"] = array("familyName" => $user->user_lastname ? $user->user_lastname : $user->display_name, "givenName" => $user->user_firstname ? $user->user_firstname : $user->display_name);
  $json["emails"] = array(array("value" => $user->user_email, "type" => "org"));
  $json["urls"] = array(array("value" => get_bloginfo("siteurl"),
                              "type" => "blog",
                              "title" => get_bloginfo("name"),
                              "description" => get_bloginfo("description"),
                              "tags" => spreadly_api_get_tags()
                             ));

  return json_encode($json);
}

function spreadly_api_post() {
  $ch = curl_init("http://api.spreadly.ly/account/create");
  curl_setopt($ch, CURLOPT_POST,           1);
  curl_setopt($ch, CURLOPT_POSTFIELDS,     spreadly_api_prepare_json());
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_HEADER,         0);  // DO NOT RETURN HTTP HEADERS
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // RETURN THE CONTENTS OF THE CALL
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  curl_setopt($ch, CURLOPT_VERBOSE,        0);

  $response = curl_exec($ch);

  return $response;
}

function spreadly_api_get_tags() {
  $args = array("orderby" => "count", "order" => "DESC", "number" => 10);
  $tags = get_tags($args);

  $return = array();
  foreach ($tags as $tag) {
    $return[] = $tag->name;
  }

  return $return;
}