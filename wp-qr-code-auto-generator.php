<?php
/**
 * @package WP QR Code Auto Generator
 * @version 1.1.0
 */
/*
  Plugin Name: WP QR Code Auto Generator
  Plugin URI: http://www.arkaroy.net
  Description: Automatically generate QR Code for pages, posts and custom post types with permalink or shortlink. You can embed QR Code with shortcode.
  Author: Arka Roy <hello@arkaroy.net>
  Version: 1.1.0
  Author URI: http://www.arkaroy.net
 */
defined('ABSPATH') or die('No script kiddies please!');

require_once 'phpqrcode/qrlib.php';
require_once 'includes/constants.php';

add_action('admin_menu', 'wpqr_settings_menu');

function wpqr_settings_menu() {
  add_options_page(WPQR_NAME, WPQR_SHORT_NAME, 'manage_options', WPQR_SLUG, 'wpqr_settings_page');
}

function wpqr_settings_page() {
  $updated = false;
  if (isset($_POST['wpqr_submit'])) {
    $data = array(
        WPQR_KEY_ECLEVEL => (int) $_POST[WPQR_KEY_ECLEVEL],
        WPQR_KEY_MATRIX => (int) $_POST[WPQR_KEY_MATRIX],
        WPQR_KEY_FRAME => (int) $_POST[WPQR_KEY_FRAME],
        WPQR_KEY_EMBED => $_POST[WPQR_KEY_EMBED],
        WPQR_KEY_POSTS => $_POST[WPQR_KEY_POSTS]
    );
    update_option(WPQR_KEY_OPTION, $data);
    wpqr_flush();
    $updated = true;
  } else if (isset($_POST['wpqr_generate'])) {
    $url = filter_input(INPUT_POST, "generate_url", FILTER_VALIDATE_URL);
    if (!$url) {
      $generate_error = "Please provide a valid URL to generate QR Code.";
    } else {
      $eclevel = (int) $_POST[WPQR_KEY_ECLEVEL];
      $matrix = (int) $_POST[WPQR_KEY_MATRIX];
      $frame = (int) $_POST[WPQR_KEY_FRAME];
      $filename = 'wpqr_' . md5($url . $eclevel . $matrix . $frame . time()) . '.png';
      $filepath = WPQR_TEMP_DIR . $filename;
      QRcode::png($url, $filepath, $eclevel, $matrix, $frame);
      $fileurl = WPQR_TEMP_URL . $filename;
    }
  }
  $options = wpqr_get_option();
  $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
  ?>
  <div class="wrap">
    <h1><?php echo WPQR_NAME; ?></h1>
    <h2 class="nav-tab-wrapper">
      <a href="?page=wp-qr-code-auto-generator&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
      <a href="?page=wp-qr-code-auto-generator&tab=generate" class="nav-tab <?php echo $active_tab == 'generate' ? 'nav-tab-active' : ''; ?>">Generate</a>
    </h2>
    <?php if ($updated): ?>
      <div class="updated"><p>Settings updated.</p></div>
    <?php endif; ?>
    <?php if ($active_tab == 'settings'): ?>
      <form method="post" action="">
        <table class="form-table">
          <tbody>
            <tr>
              <th>Error Correction Level</th>
              <td>
                <select name="<?php echo WPQR_KEY_ECLEVEL; ?>">
                  <option value="0"<?php echo $options[WPQR_KEY_ECLEVEL] == 0 ? ' selected="selected"' : ''; ?>>L - Upto 7% damage</option>
                  <option value="1"<?php echo $options[WPQR_KEY_ECLEVEL] == 1 ? ' selected="selected"' : ''; ?>>M - Upto 15% damage</option>
                  <option value="2"<?php echo $options[WPQR_KEY_ECLEVEL] == 2 ? ' selected="selected"' : ''; ?>>Q - Upto 25% damage</option>
                  <option value="3"<?php echo $options[WPQR_KEY_ECLEVEL] == 3 ? ' selected="selected"' : ''; ?>>H - Upto 30% damage</option>
                </select>
              </td>
            </tr>
            <tr>
              <th>Matrix Size</th>
              <td>
                <select name="<?php echo WPQR_KEY_MATRIX; ?>">
                  <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?php echo $i; ?>"<?php echo $options[WPQR_KEY_MATRIX] == $i ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
                  <?php endfor; ?>
                </select>
              </td>
            </tr>
            <tr>
              <th>Frame Size</th>
              <td>
                <select name="<?php echo WPQR_KEY_FRAME; ?>">
                  <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?php echo $i; ?>"<?php echo $options[WPQR_KEY_FRAME] == $i ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
                  <?php endfor; ?>
                </select>
              </td>
            </tr>
            <tr>
              <th>URL to embed in QR</th>
              <td>
                <label style="margin-right: 20px;"><input type="radio" name="<?php echo WPQR_KEY_EMBED; ?>" value="shortlink"<?php echo $options[WPQR_KEY_EMBED] == 'shortlink' ? ' checked="checked"' : '' ?>> Shortlink</label>
                <label><input type="radio" name="<?php echo WPQR_KEY_EMBED; ?>" value="permalink"<?php echo $options[WPQR_KEY_EMBED] == 'permalink' ? ' checked="checked"' : '' ?>> Permalink</label>
              </td>
            </tr>
            <tr>
              <th>Enable QR for</th>
              <td>
                <?php $post_types = get_post_types(array('public' => true), 'object'); ?>
                <?php foreach ($post_types as $p => $post_type): ?>
                  <label style="margin-right: 20px;"><input type="checkbox" name="<?php echo WPQR_KEY_POSTS; ?>[]" value="<?php echo $p; ?>"<?php echo in_array($p, $options[WPQR_KEY_POSTS]) ? ' checked="checked"' : ''; ?>> <?php echo $post_type->labels->singular_name; ?></label>
                <?php endforeach; ?>
              </td>
            </tr>
          </tbody>
        </table>
        <p class="submit">
          <input type="submit" value="Save Changes" name="wpqr_submit" class="button button-primary">
        </p>
      </form>
    <?php elseif ($active_tab == 'generate'): ?>
      <?php if (isset($generate_error) && $generate_error): ?>
        <div class="notice notice-error">
          <p><?php echo $generate_error; ?></p>
        </div>
      <?php endif; ?>
      <?php if(isset($fileurl) && $fileurl): ?>
      <p>QR Code for <?php echo $url; ?></p>
      <img src="<?php echo $fileurl; ?>">
      <?php endif; ?>
      <form method="post" action="">
        <table class="form-table">
          <tbody>
            <tr>
              <th>URL to Generate QR Code</th>
              <td>
                <input type="text" name="generate_url">
              </td>
            </tr>
            <tr>
              <th>Error Correction Level</th>
              <td>
                <select name="<?php echo WPQR_KEY_ECLEVEL; ?>">
                  <option value="0"<?php echo $options[WPQR_KEY_ECLEVEL] == 0 ? ' selected="selected"' : ''; ?>>L - Upto 7% damage</option>
                  <option value="1"<?php echo $options[WPQR_KEY_ECLEVEL] == 1 ? ' selected="selected"' : ''; ?>>M - Upto 15% damage</option>
                  <option value="2"<?php echo $options[WPQR_KEY_ECLEVEL] == 2 ? ' selected="selected"' : ''; ?>>Q - Upto 25% damage</option>
                  <option value="3"<?php echo $options[WPQR_KEY_ECLEVEL] == 3 ? ' selected="selected"' : ''; ?>>H - Upto 30% damage</option>
                </select>
              </td>
            </tr>
            <tr>
              <th>Matrix Size</th>
              <td>
                <select name="<?php echo WPQR_KEY_MATRIX; ?>">
                  <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?php echo $i; ?>"<?php echo $options[WPQR_KEY_MATRIX] == $i ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
                  <?php endfor; ?>
                </select>
              </td>
            </tr>
            <tr>
              <th>Frame Size</th>
              <td>
                <select name="<?php echo WPQR_KEY_FRAME; ?>">
                  <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?php echo $i; ?>"<?php echo $options[WPQR_KEY_FRAME] == $i ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
                  <?php endfor; ?>
                </select>
              </td>
            </tr>            
          </tbody>
        </table>
        <p class="submit">
          <input type="submit" value="Generate" name="wpqr_generate" class="button button-primary">
        </p>
      </form>
    <?php endif; ?>
  </div>
  <?php
}

function wpqr_register_meta_boxes() {
  $options = wpqr_get_option();
  add_meta_box('wpqr-code', WPQR_SHORT_NAME, 'wpqr_meta_box_callback', $options[WPQR_KEY_POSTS], 'side', 'high');
}

add_action('add_meta_boxes', 'wpqr_register_meta_boxes');

function wpqr_meta_box_callback($post) {
  if ($post->post_status != 'publish') {
    echo '<p>Please publish to generate WP QR Code.</p>';
  } else {
    $post_id = $post->ID;
    wpqr_code($post_id);
  }
}

function wpqr_get_option() {
  $defaults = array(
      WPQR_KEY_ECLEVEL => 1,
      WPQR_KEY_MATRIX => 4,
      WPQR_KEY_FRAME => 4,
      WPQR_KEY_EMBED => 'shortlink',
      WPQR_KEY_POSTS => array('page', 'post'),
  );
  $options = get_option(WPQR_KEY_OPTION, null);
  foreach ($defaults as $k => $v) {
    if (is_array($options) && isset($options[$k])) {
      $defaults[$k] = $options[$k];
    }
  }
  return $defaults;
}

function get_wpqr_url($post_id = 0) {
  if (!$post_id) {
    $post = get_post();
    if (!$post) {
      return null;
    }
    $post_id = $post->ID;
  }
  $wpqr_url = get_post_meta($post_id, WPQR_KEY_URL, true);
  if ($wpqr_url) {
    return $wpqr_url;
  }
  $options = wpqr_get_option();
  $embed_url = $options[WPQR_KEY_EMBED] == 'shortlink' ? wp_get_shortlink($post_id) : get_permalink($post_id);
  $filename = 'wpqr_' . md5($embed_url . $options[WPQR_KEY_ECLEVEL] . $options[WPQR_KEY_MATRIX] . $options[WPQR_KEY_FRAME]) . '.png';
  $filepath = WPQR_DIR . $filename;
  QRcode::png($embed_url, $filepath, $options[WPQR_KEY_ECLEVEL], $options[WPQR_KEY_MATRIX], $options[WPQR_KEY_FRAME]);
  $wpqr_url = WPQR_URL . $filename;
  update_post_meta($post_id, WPQR_KEY_URL, $wpqr_url);
  return $wpqr_url;
}

function get_wpqr_existing_url($post_id = 0) {
  if (!$post_id) {
    $post = get_post();
    if (!$post) {
      return null;
    }
    $post_id = $post->ID;
  }
  $wpqr_url = get_post_meta($post_id, WPQR_KEY_URL, true);
  return $wpqr_url;
}

function get_wpqr_code($post_id = 0) {
  $wpqr_url = get_wpqr_url($post_id);
  if ($wpqr_url) {
    return '<img src="' . $wpqr_url . '" class="wpqr-code">';
  } else {
    return '<p>Failed to generate WP QR Code.</p>';
  }
}

function wpqr_code($post_id = 0) {
  echo get_wpqr_code($post_id);
}

function shortcode_wpqr_code($atts) {
  $a = shortcode_atts(array('id' => 0), $atts);
  return get_wpqr_code($a['id']);
}

add_shortcode('wpqr-code', 'shortcode_wpqr_code');

function wpqr_flush() {
  $options = wpqr_get_option();
  $args = array(
      'post_type' => $options[WPQR_KEY_POSTS],
      'posts_per_page' => -1
  );
  $posts = new WP_Query($args);
  if ($posts->have_posts()) {
    while ($posts->have_posts()) {
      $posts->the_post();
      wpqr_flush_post(get_the_ID());
    }
  } else {
    
  }
  wp_reset_postdata();
}

function wpqr_flush_post($post_id) {
  $wpqr_url = get_wpqr_existing_url($post_id);
  if ($wpqr_url) {
    $filename = basename($wpqr_url);
    $filepath = WPQR_DIR . $filename;
    if (file_exists($filepath)) {
      unlink($filepath);
    }
  }
  delete_post_meta($post_id, WPQR_KEY_URL);
}
