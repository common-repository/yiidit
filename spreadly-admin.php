<?php
include_once('spreadly-api.php');

/**
 * adds the Spreadly-items to the admin-menu
 */
function spreadly_admin_menu_item() {
  add_options_page('Spreadly', 'Spreadly', 'manage_options', 'spreadly', 'spreadly_admin_show_settings');
}
add_action('admin_menu', 'spreadly_admin_menu_item');

/**
 * add a settings link next to deactive / edit
 *
 * @author spreadly team
 * @param array $links
 * @param string $file
 * @return array
 */
function spreadly_admin_settings_link( $links, $file ) {
  if( preg_match("/yiidit/i", $file) && function_exists( "admin_url" ) ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=spreadly' ) . '">' . __('Settings') . '</a>';
    $signup_link = '<a href="http://spreadly.com/user/new" target="_blank" title="Create a Spreadly-account to view the stats of your page">' . __('Create Account') . '</a>';
    array_unshift( $links, $settings_link, $signup_link );
  }
  return $links;
}
add_filter('plugin_action_links', 'spreadly_admin_settings_link', 10, 2);

/**
 * displays the yiid.it settings page
 */
function spreadly_admin_show_settings() {
  if (isset($_GET['spreadly_action']) && $_GET['spreadly_action'] == "create_account") {
    $response = spreadly_api_post();

    $message = json_decode($response, true);

    if (array_key_exists("error", $message)) {
      echo '<div id="message" class="error"><p><strong>'.$message['error']['message'].'</strong></p></div>';
    } else {
      echo '<div id="message" class="updated fade"><p>'.$message['success']['message'].'</p></div>';
    }
  }

  $spreadly_button_visibility = get_option('spreadly_button_visibility');
  $spreadly_service_icons = get_option('spreadly_service_icons');
?>

  <div class="wrap">
    <img src="<?php echo WP_PLUGIN_URL ?>/yiidit/logo_32x32.png" alt="Spreadly" class="icon32" />

    <h2><?php _e('Spreadly Settings', 'spreadly') ?></h2>

    <p>Check out the social media reach of your blog at <a href="http://spreadly.com" target="_blank">spreadly.com</a></p>

    <h3><?php echo __('Support us'); ?></h3>

    <p><?php echo _e("If you enjoy our service don't hestiate to spread the word"); ?></p>
    <p><iframe src="http://button.spread.ly/?url=http%3A%2F%2Fwordpress.org%2Fextend%2Fplugins%2Fyiidit%2F&title=yiidit+plugin+for+wordpress" style="overflow:hidden;
      width: 350px; height: 30px;" frameborder="0" scrolling="no" marginheight="0" allowTransparency="true"></iframe></p>

    <div style="float: right; width: 300px; border-left: 1px solid #ccc; padding-left: 20px">
      <h3>Spreadly.com Account</h3>
      <p>Why should you claim your Domain?</p>

      <p>Only if we know who is the owner of this blog we can do payouts of the revenue share. So
         if you like to earn money with your shared and recommend content please setup your account and
         claim your Domain with a few simple clicks.</p>

      <p>With a spreadly.com Account and a claimend Domain you have access to the free Social Analytics about sharing on your blog.</p>

      <p>If you have any further question, please contact us via Twitter (<a href="http://twitter.com/spreadly_helps" target="_blank">@spreadly_helps</a>),
      Facebook (<a href="http://www.facebook.com/spreadly" target="_blank">www.fb.com/spreadly</a>) oder
      <a href="mailto:support@spreadly.com" target="_blank">support@spreadly.com</a></p>

      <p>Thanks a lot</p>

      <form action="<?php echo WP_ADMIN_URL; ?>/options-general.php" method="get">
        <input type="hidden" name="page" value="spreadly" />
        <input type="hidden" name="spreadly_action" value="create_account" />
        <input type="submit" value="Create a Spreadly Acccount" />
      </form>

      <p><small>(This will use informations (including your e-mail address) to create a Spreadly account)</small></p>

      <h4>Test our Widget<sup>Beta</sup></h4>

      <p>All users with a Spreadly.com account are able to test our new spreadly-widget.</p>

      <p>Shortcode: <code>[spreadly-widget]</code></p>
    </div>

    <div style="float: left;">

    <form method="post" action="options.php">
      <?php //wp_nonce_field('update-options'); ?>
      <!-- starting -->
      <?php settings_fields('spreadly_settings_group'); ?>
      <?php do_settings_sections('spreadly_settings_section'); ?>
      <!-- ending -->

      <h3><?php echo __('General'); ?></h3>

      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row"><label for="spreadly_button_label">Button-Label</label></th>
            <td>
              <input type="text" id="spreadly_button_label" name="spreadly_button_label" value="<?php form_option("spreadly_button_label"); ?>" />
              <p class="description">For example "sharing is caring" or "please share". It will be displayed above the button.</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="spreadly_button_counter">Show counter?</label></th>
            <td>
              <input type="checkbox" name="spreadly_button_counter" id="spreadly_button_counter" value="true" <?php checked(get_option("spreadly_button_counter"), "true"); ?> />
              <p class="description">Check this box to enable the click counter</p>
            </td>
          </tr>
        </tbody>
      </table>

      <h3><?php echo __('Ad Settings'); ?></h3>

      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row"><label for="spreadly_ad_position">Ad-position</label></th>
            <td>
              <select id="spreadly_ad_position" name="spreadly_ad_position">
                <option value="top" <?php selected(get_option("spreadly_ad_position"), "top"); ?>>above button</option>
                <option value="bottom" <?php selected(get_option("spreadly_ad_position"), "bottom"); ?>>below button</option>
              </select>
            </td>

          </tr>
        </tbody>
      </table>

      <h3><?php echo __('Where to display'); ?></h3>
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row"><label for="spreadly_button_position">Show</label></th>
            <td>
              <select id="spreadly_button_position" name="spreadly_button_position">
                <option value="bottom" <?php selected(get_option("spreadly_button_position"), "bottom"); ?>>after post/page</option>
                <option value="top" <?php selected(get_option("spreadly_button_position"), "top"); ?>>before page/post</option>
              </select>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="spreadly_button_visibility_pages">Show on pages:</label></th>
            <td>
              <input type="checkbox" name="spreadly_button_visibility[pages]" id="spreadly_button_visibility_pages" value="show" <?php if (isset($spreadly_button_visibility['pages'])) { echo 'checked="checked"'; } ?> />
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="spreadly_button_visibility_posts">Show on single posts:</label></th>
            <td>
              <input type="checkbox" name="spreadly_button_visibility[posts]" id="spreadly_button_visibility_posts" value="show" <?php if (isset($spreadly_button_visibility['posts'])) { echo 'checked="checked"'; } ?> />
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="spreadly_button_visibility_overviews">Show on overview pages (home, archives, author, ...):</label></th>
            <td>
              <input type="checkbox" name="spreadly_button_visibility[overviews]" id="spreadly_button_visibility_overviews" value="show" <?php if (isset($spreadly_button_visibility['overviews'])) { echo 'checked="checked"'; } ?> />
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="spreadly_static_feed_button">Use static buttons in feeds:</label></th>
              <td>
                <input type="checkbox" name="spreadly_static_feed_button" id="spreadly_static_feed_button" value="show" <?php checked(get_option('spreadly_static_feed_button'), "show"); ?> />
              </td>
            </tr>
          </tbody>
        </table>

        <h3 id="button-style"><?php echo __('Button Style'); ?></h3>

        <table class="form-table">
          <tbody>
            <tr>
              <th scope="row"><label for="spreadly_button_style">Button template</label></th>
              <td>
                <select id="spreadly_button_style" name="spreadly_button_style">
                  <option value="classic" <?php selected(get_option("spreadly_button_style"), "classic"); ?>>Classic design</option>
                  <option value="flat" <?php selected(get_option("spreadly_button_style"), "flat"); ?>>Flat design</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>

        <h3 id="service-icons"><?php echo __('Service Icons'); ?></h3>

        <table class="form-table">
          <tbody>
            <tr>
              <th scope="row"><label for="spreadly_button_position">Customize the icons of the button</label></th>
              <td>
                <input type="checkbox" name="spreadly_service_icons[twitter]" id="spreadly_service_icons_twitter" value="twitter" <?php if (isset($spreadly_service_icons["twitter"])) { echo 'checked="checked"'; } ?> /> <label for="spreadly_service_icons_twitter">Twitter</label>
              </td>
              <td>
                <input type="checkbox" name="spreadly_service_icons[facebook]" id="spreadly_service_icons_facebook" value="facebook" <?php if (isset($spreadly_service_icons["facebook"])) { echo 'checked="checked"'; } ?> /> <label for="spreadly_service_icons_facebook">Facebook</label>
              </td>
              <td>
                <input type="checkbox" name="spreadly_service_icons[linkedin]" id="spreadly_service_icons_linkedin" value="linkedin" <?php if (isset($spreadly_service_icons["linkedin"])) { echo 'checked="checked"'; } ?> /> <label for="spreadly_service_icons_linkedin">LinkedIn</label>
              </td>
              <td>
                <input type="checkbox" name="spreadly_service_icons[xing]" id="spreadly_service_icons_xing" value="xing" <?php if (isset($spreadly_service_icons["xing"])) { echo 'checked="checked"'; } ?> /> <label for="spreadly_service_icons_xing">Xing</label>
              </td>
              <td>
                <input type="checkbox" name="spreadly_service_icons[tumblr]" id="spreadly_service_icons_tumblr" value="tumblr" <?php if (isset($spreadly_service_icons["tumblr"])) { echo 'checked="checked"'; } ?> /> <label for="spreadly_service_icons_tumblr">Tumblr</label>
              </td>
              <td>
                <input type="checkbox" name="spreadly_service_icons[flattr]" id="spreadly_service_icons_flattr" value="flattr" <?php if (isset($spreadly_service_icons["flattr"])) { echo 'checked="checked"'; } ?> /> <label for="spreadly_service_icons_flattr">Flattr</label>
              </td>
            </tr>
          </tbody>
        </table>

        <h3><?php echo __('Claim your Blog'); ?></h3>

        <p>To claim your blog for the Spread.ly-Stats, add the mail addresses you used to signup to Spread.ly (one per line).</p>
        <textarea rows="5" cols="50" name="spreadly_email_addresses"><?php echo get_option('spreadly_email_addresses'); ?></textarea>
        <p>More informations about Spread.ly, visit: <a href="http://spreadly.com" target="_blank">spreadly.com</a></p>

        <input type="submit" name="submit" value="save changes" />
      </form>

      <h3>Button Shortcode</h3>

      <p>To be more flexible we also created a shortcode <code>[spreadly-button]</code></p>

      <p>Possible attributes are:</p>

      <ul>
        <li><code>url</code> - if empty the plugin tries to use the <code>permalink</code>.</li>
        <li><code>title</code> -  if empty the plugin tries to use the <code>title</code> of the page.</li>
        <li><code>style</code> -  the style of the button. You can choose between <code>classic</code> and <code>flat</code>.</li>
        <li>
          <code>services</code> - comma separated list of services. If empty it shows the icons defined under <em>"Service Icons"</em>.<br />
          Possible services are: <code>twitter</code>, <code>facebook</code>, <code>linkedin</code>, <code>xing</code>, <code>tumblr</code>, <code>flattr</code>.</li>
        <li><code>label</code> - will be displayed above the button.</li>
      </ul>

      <p>Full examle: <code>[spreadly-button url="http://spreadly.com" title="Spreadly" services="twitter,facebook,xing" style="flat"]</code></p>
    </div>
    </div>
<?php
}
// keep WPMU happy
function spreadly_admin_register_settings() {
  register_setting('spreadly_settings_group','spreadly_static_feed_button');
  register_setting('spreadly_settings_group','spreadly_button_position');
  register_setting('spreadly_settings_group','spreadly_button_visibility');
  register_setting('spreadly_settings_group','spreadly_button_style');
  register_setting('spreadly_settings_group','spreadly_email_addresses');
  register_setting('spreadly_settings_group','spreadly_service_icons');
  register_setting('spreadly_settings_group','spreadly_button_label');
  register_setting('spreadly_settings_group','spreadly_button_counter');
  register_setting('spreadly_settings_group','spreadly_ad_position');
}
add_action('admin_init', 'spreadly_admin_register_settings');
?>