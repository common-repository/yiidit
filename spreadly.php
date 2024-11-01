<?php
/*
Plugin Name: spread.ly for WordPress
Plugin URI: http://spreadly.com
Description: Adds support for the spread.ly like/deal button
Version: 2.0.8
Author: spreadly
Author URI: http://spreadly.com
*/

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
  define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
  define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
  define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
  define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
if ( ! defined( 'WP_ADMIN_URL' ) )
  define( 'WP_ADMIN_URL', get_option('siteurl') . '/wp-admin' );

include_once('spreadly-admin.php');

/**
 * adds the code to the content
 *
 * @param string $content
 * @return string
 */
function spreadly_add_button($content) {
  // display static-button in feed
  if (is_feed()) {
    if (get_option('spreadly_static_feed_button') == "show") {
      return $content . spreadly_generate_static_button();
    } else {
      return $content;
    }
  }

  if (get_option('spreadly_button_position') == "top") {
    $content_with_button = spreadly_generate_button().$content;
  } else {
    $content_with_button = $content.spreadly_generate_button();
  }



  // where to show
  $spreadly_button_visibility = get_option('spreadly_button_visibility');
  if ((is_archive() || is_home() || is_author()) == true && isset($spreadly_button_visibility['overviews']) && $spreadly_button_visibility['overviews'] == "show")
    return $content_with_button;
  elseif (is_single() == true && isset($spreadly_button_visibility['posts']) && $spreadly_button_visibility['posts'] == "show")
    return $content_with_button;
  elseif (is_page() == true && isset($spreadly_button_visibility['pages']) && $spreadly_button_visibility['pages'] == "show")
    return $content_with_button;
  else
    return $content;
}
add_action('the_content', 'spreadly_add_button', 21);

/**
 * adds microids to header
 */
function spreadly_add_html_header() {
  global $wpdb;

  $emails = get_option('yiid_email_addresses');
  $emails = explode("\n",$emails);

  $users = get_users('role=administrator');

  foreach ($users as $user) {
    $emails[] = $user->user_email;
  }

  $emails = array_unique($emails);

  $blogurl = get_bloginfo('wpurl');
  $urlArray = parse_url($blogurl);
  $domain = $urlArray['scheme']."://".$urlArray['host'];

  $hash = spreadly_generate_microid("mailto:".get_bloginfo('admin_email '), $domain);
  echo '<meta name="microid" content="mailto+http:sha1:' . $hash . '" />'."\n";

  foreach ($emails as $email) {
    if ($email = trim($email)) {
      $hash = spreadly_generate_microid("mailto:".$email, $domain);
      echo '<meta name="microid" content="mailto+http:sha1:' . $hash . '" />'."\n";
    }
  }
}
add_action('wp_head', 'spreadly_add_html_header', 21);

function spreadly_add_scripts() {
  wp_enqueue_script( 'spreadly-share', '//button.spread.ly/js/v1/loader.js' );
}
add_action('wp_enqueue_scripts', 'spreadly_add_scripts');

/**
 * generates the like button
 *
 * @return string
 */
function spreadly_generate_button($url = null, $title = null, $services = null, $label = null, $counter = null, $ad_position = null, $style = null) {
  if (!$url) {
    $url = get_permalink();
  }
  if (!$title) {
    $title = get_the_title();
  }
  if (!$services && $spreadly_service_icons = get_option('spreadly_service_icons')) {
    $services = implode(",", $spreadly_service_icons);
  }

  $data_services = "";
  if ($services) {
    $data_services = "data-services='$services'";
  }

  $data_counter = "";
  if ($counter) {
    $data_counter = "data-counter='$counter'";
  } elseif (get_option('spreadly_button_counter', false)) {
    $data_counter = "data-counter='".get_option('spreadly_button_counter')."'";
  }

  $data_ad_postition = "";
  if ($ad_position) {
    $data_ad_postition = "data-adlayer-position='$ad_position'";
  } elseif (get_option('spreadly_ad_position', false)) {
    $data_ad_postition = "data-adlayer-position='".get_option('spreadly_ad_position')."'";
  }

  $data_style = "";
  if ($style) {
    $data_style = "data-style='$style'";
  } elseif (get_option('spreadly_button_style', false)) {
    $data_style = "data-style='".get_option('spreadly_button_style')."'";
  }

  if (!$label && $spreadly_button_label = get_option("spreadly_button_label")) {
    $label = "<div class='spreadly-button-label'>$spreadly_button_label</div>";
  }

  $button = "<p>$label<a href='$url' title='$title' class='spreadly-button' $data_ad_postition $data_services $data_counter $data_style rel='share like'></a></p>";

  return $button;
}

/**
 * a button shortcode
 *
 * @param array $atts the shortcode attributes
 * @return string the button html code
 */
function spreadly_button_shortcode($atts) {
	extract( shortcode_atts( array(
		'url' => get_permalink(),
    'title' => get_the_title(),
    'services' => '',
    'label' => '',
    'counter' => '',
    'ad_position' => '',
    'style' => 'classic',
	), $atts ) );

	return spreadly_generate_button($url, $title, $services, $label, $counter, $ad_position, $style);
}
add_shortcode("spreadly-button", "spreadly_button_shortcode");

/**
 * a button shortcode
 *
 * @param array $atts the shortcode attributes
 * @return string the button html code
 */
function spreadly_widget_shortcode($atts) {

	return "<div class='spreadly-widget'></div>";
}
add_shortcode("spreadly-widget", "spreadly_widget_shortcode");

/**
 * generates the like button
 *
 * @return string
 */
function spreadly_generate_static_button() {
  $widgetCode = '<p style="clear: both;"><a href="http://spread.ly/?url='.urlencode(get_permalink()).'&title='.urlencode(strip_tags(html_entity_decode(get_the_title()))).'&tags='.spreadly_get_tags().'" rel="like"><img src="http://spread.ly/img/like-button.jpg" alt="Like" /></a></p>';

  return $widgetCode;
}

/**
 * generate a tag string
 *
 * @return array
 */
function spreadly_get_tags() {
  $posttags = get_the_tags();
  $tags = array();
  if ($posttags) {
    foreach($posttags as $tag) {
      $tags[] = urlencode($tag->name);
    }
  }

  foreach((get_the_category()) as $category) {
    $tags[] = urlencode($category->name);
  }

  return implode(",", $tags);
}

function spreadly_generate_microid($first_uri, $second_uri) {
  $first_uri_hash = sha1($first_uri);
  $second_uri_hash = sha1($second_uri);

  return sha1($first_uri_hash . $second_uri_hash);
}

function spreadly_send_welcome_mail() {
  $body = "Spreadly.com Account

  Why should you claim your Domain?

  Only if we know who is the owner of this blog we can do payouts of the revenue share. So if you like to earn money with your shared and recommend content please setup your account and claim your Domain with a few simple clicks.

  With a spreadly.com Account and a claimend Domain you have access to the free Social Analytics about sharing on your blog.

  If you have any further question, please contact us via Twitter (http://twitter.com/spreadly_helps), Facebook (http://www.fb.com/spreadly) oder support@spreadly.com

  Thanks a lot";
  wp_mail(get_bloginfo('admin_email'), "Thanks for using \"Spreadly for WordPress\"", $body);
}
register_activation_hook(__FILE__, 'spreadly_send_welcome_mail');