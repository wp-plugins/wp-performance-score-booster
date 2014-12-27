<?php
/*
Plugin Name: WP Performance Score Booster
Plugin URI: https://github.com/dipakcg/wp-performance-score-booster
Description: Speed-up page load times and improve website scores in services like PageSpeed, YSlow, Pingdom and GTmetrix.
Version: 1.2.2
Author: Dipak C. Gajjar
Author URI: http://dipakgajjar.com
Text Domain: wp-performance-score-booster
*/

// Define plugin version for future releases
if (!defined('WPPSB_PLUGIN_VERSION')) {
    define('WPPSB_PLUGIN_VERSION', 'wppsb_plugin_version');
}
if (!defined('WPPSB_PLUGIN_VERSION_NUM')) {
    define('WPPSB_PLUGIN_VERSION_NUM', '1.2.2');
}
update_option(WPPSB_PLUGIN_VERSION, WPPSB_PLUGIN_VERSION_NUM);

// Load plugin textdomain for language trnaslation
// load_plugin_textdomain( 'wp-performance-score-booster', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
function wppsb_load_plugin_textdomain() {

	$domain = 'wp-performance-score-booster';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	// wp-content/languages/plugin-name/plugin-name-de_DE.mo
	load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
	// wp-content/plugins/plugin-name/languages/plugin-name-de_DE.mo
	load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );

}
add_action( 'init', 'wppsb_load_plugin_textdomain' );

// Register with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
add_action( 'admin_init', 'wppsb_add_stylesheet' );
function wppsb_add_stylesheet() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style( 'wppsb-stylesheet', plugins_url('assets/css/style.css', __FILE__) );
    wp_enqueue_style( 'wppsb-stylesheet' );
}

// Remove query strings from static content
function wppsb_remove_query_strings_q( $src ) {
	$str_parts = explode( '?ver', $src );
	return $str_parts[0];
}
function wppsb_remove_query_strings_emp( $src ) {
	$str_parts = explode( '&ver', $src );
	return $str_parts[0];
}

/* function wppsb_enable_gzip_compression ( $host ) {
	// Get htaccess file path
	$htaccess_file = ABSPATH.'.htaccess';

	// $host_rule = '# BEGIN : Enable GZIP Compression by WP Performance Score Booster' . PHP_EOL;
	$host_rule = '<IfModule mod_deflate.c>' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE text/plain' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE text/html' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE text/xml' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE text/css' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE application/xml' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE application/xhtml+xml' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE application/rss+xml' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE application/javascript' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE application/x-javascript' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE application/x-httpd-php' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE application/x-httpd-fastphp' . PHP_EOL;
	$host_rule .= 'AddOutputFilterByType DEFLATE image/svg+xml' . PHP_EOL;
	$host_rule .= 'SetOutputFilter DEFLATE' . PHP_EOL;
	$host_rule .= '</IfModule>' . PHP_EOL;
	$host_rule .= '# END : Enable GZIP Compression by WP Performance Score Booster'. PHP_EOL . PHP_EOL;

	// Get the file permission to make sure we can write to the file
	$perms = substr( sprintf( '%o', @fileperms( $htaccess_file ) ), - 4 );

	@chmod( $htaccess_file, 0664 );

	// Get the contents of the htaccess
	$htaccess_contents = @file_get_contents( $htaccess_file );

	// We couldn't get the file contents
	if ( $htaccess_contents === false ) {
		return false;
	}

	$htaccess_contents = preg_replace( "/(\\r\\n|\\n|\\r)/", PHP_EOL, $htaccess_contents );

	$htaccess_contents = $host_rule . $htaccess_contents;

	if ( strpos( $htaccess_contents, '# BEGIN : Enable GZIP Compression by WP Performance Score Booster' ) !== false ) {
		$htaccess_contents = str_replace( $host_rule, $host_rule, $htaccess_contents );
	} else {
		$htaccess_contents = '# BEGIN : Enable GZIP Compression by WP Performance Score Booster' . PHP_EOL . $host_rule . $htaccess_contents;
	}

	@file_put_contents( $htaccess_file, $htaccess_contents, LOCK_EX );

	return true;
} */

/* function wppsb_disable_gzip_compression ( $src ) {
		$rule_open  = array( '# BEGIN : Enable GZIP Compression by WP Performance Score Booster', '# BEGIN : Enable GZIP Compression by WP Performance Score Booster' );
		$rule_close = array( '# END : Enable GZIP Compression by WP Performance Score Booster', '# END : Enable GZIP Compression by WP Performance Score Booster' );

		// Get htaccess file path
		$htaccess_file = ABSPATH.'.htaccess';

		// Get the contents of the htaccess or nginx file
		$perms = substr( sprintf( '%o', @fileperms( $htaccess_file ) ), - 4 );

		if ( $perms == '0444' ) {
			@chmod( $htaccess_file, 0664 );
		}

		// Make sure the file exists and create it if it doesn't
		if ( ! file_exists( $htaccess_file ) ) {
			@touch( $htaccess_file );
		}

		// Get the contents of the htaccess
		$htaccess_contents = @file_get_contents( $htaccess_file );

		// We couldn't get the file contents
		if ( $htaccess_contents === false ) {
			return false;
		}
		else {
			// Create an array to make this easier
			$lines = explode( PHP_EOL, $htaccess_contents );
			$state = false;

			// For each line in the file
			foreach ( $lines as $line_number => $line ) {

				// If we're at the beginning of the section
				if ( in_array( trim( $line ), $rule_open ) !== false ) {
					$state = true;
				}

				// As long as we're not in the section keep writing
				if ( $state == true ) {
					unset( $lines[$line_number] );
				}

				// See if we're at the end of the section
				if ( in_array( trim( $line ), $rule_close ) !== false ) {
					$state = false;
				}
			}

			$htaccess_contents = trim( implode( PHP_EOL, $lines ) );

			if ( strlen( $htaccess_contents ) < 1 ) {
				$htaccess_contents = PHP_EOL;
			}

			if ( ! @file_put_contents( $htaccess_file, $htaccess_contents, LOCK_EX ) ) {
				return false;
			}
		}

		//reset file permissions if we changed them
		if ( $perms == '0444' ) {
			@chmod( $htaccess_file, 0444 );
		}

		return true;
} */

// Enable GZIP Compression
function wppsb_enable_gzip_filter( $rules ) {
$gzip_htaccess_content = <<<EOD
\n## Added by WP Performance Score Booster ##
## BEGIN : Enable GZIP Compression (compress text, html, javascript, css, xml and so on) ##
<IfModule mod_deflate.c>
AddOutputFilterByType DEFLATE text/plain
AddOutputFilterByType DEFLATE text/html
AddOutputFilterByType DEFLATE text/xml
AddOutputFilterByType DEFLATE text/css
AddOutputFilterByType DEFLATE application/xml
AddOutputFilterByType DEFLATE application/xhtml+xml
AddOutputFilterByType DEFLATE application/rss+xml
AddOutputFilterByType DEFLATE application/javascript
AddOutputFilterByType DEFLATE application/x-javascript
AddOutputFilterByType DEFLATE application/x-httpd-php
AddOutputFilterByType DEFLATE application/x-httpd-fastphp
AddOutputFilterByType DEFLATE image/svg+xml
SetOutputFilter DEFLATE
</IfModule>
## END : Enable GZIP Compression ##\n\n
EOD;
    return $gzip_htaccess_content . $rules;
}

// Enable expire caching
function wppsb_expire_caching_filter( $rules ) {
$expire_cache_htaccess_content = <<<EOD
\n## Added by WP Performance Score Booster ##
## BEGIN : Expires Caching (Leverage Browser Caching) ##
<IfModule mod_expires.c>
ExpiresActive On
ExpiresByType image/jpg "access 2 week"
ExpiresByType image/jpeg "access 2 week"
ExpiresByType image/gif "access 2 week"
ExpiresByType image/png "access 2 week"
ExpiresByType text/css "access 2 week"
ExpiresByType application/pdf "access 2 week"
ExpiresByType text/x-javascript "access 2 week"
ExpiresByType application/x-shockwave-flash "access 2 week"
ExpiresByType image/x-icon "access 2 week"
ExpiresDefault "access 2 week"
</IfModule>
## END : Expires Caching (Leverage Browser Caching) ##\n\n
EOD;
    return $expire_cache_htaccess_content . $rules;
}

// Set Vary: Accept-Encoding Header
function wppsb_vary_accept_encoding_filter( $rules ) {
$vary_accept_encoding_header = <<<EOD
\n## Added by WP Performance Score Booster ##
## BEGIN : Vary: Accept-Encoding Header ##
<IfModule mod_headers.c>
<FilesMatch "\.(js|css|xml|gz)$">
Header append Vary: Accept-Encoding
</FilesMatch>
</IfModule>
## END : Vary: Accept-Encoding Header ##\n\n
EOD;
    return $vary_accept_encoding_header . $rules;
}

// Defer parsing of java-script (to load at last)
/* function defer_parsing_of_js ( $src ) {
	if ( FALSE === strpos( $src, '.js' ) )
		return $src;
	if ( strpos( $src, 'jquery.js' ) )
		return $src;
	return "$src' defer='defer";
}
add_filter( 'clean_url', 'defer_parsing_of_js', 11, 1 ); */

// Enqueue scripts in the footer to speed-up page load
/* function footer_enqueue_scripts() {
	remove_action('wp_head', 'wp_print_scripts');
	// remove_action('wp_head', 'wp_print_head_scripts', 9);
	remove_action('wp_head', 'wp_enqueue_scripts', 1);
	add_action('wp_footer', 'wp_print_scripts', 5);
	// add_action('wp_footer', 'wp_print_head_scripts', 5);
    add_action('wp_footer', 'wp_enqueue_scripts', 5);
}
add_action('after_setup_theme', 'footer_enqueue_scripts'); */

// If 'Remove query strings" checkbox ticked, add filter otherwise remove filter
if (get_option('wppsb_remove_query_strings') == 'on') {
	add_filter( 'script_loader_src', 'wppsb_remove_query_strings_q', 15, 1 );
	add_filter( 'style_loader_src', 'wppsb_remove_query_strings_q', 15, 1 );
	add_filter( 'script_loader_src', 'wppsb_remove_query_strings_emp', 15, 1 );
	add_filter( 'style_loader_src', 'wppsb_remove_query_strings_emp', 15, 1 );
}
else {
	remove_filter( 'script_loader_src', 'wppsb_remove_query_strings_q');
	remove_filter( 'style_loader_src', 'wppsb_remove_query_strings_q');
	remove_filter( 'script_loader_src', 'wppsb_remove_query_strings_emp');
	remove_filter( 'style_loader_src', 'wppsb_remove_query_strings_emp');
}

function wppsb_admin_options() {
	?>
	<div class="wrap">
	<table width="100%" border="0">
	<tr>
	<td width="75%">
	<h2><?php echo '<img src="' . plugins_url( 'assets/images/wppsb-icon-24x24.png' , __FILE__ ) . '" > ';  ?> <?php _e('WP Performance Score Booster Settings', 'wp-performance-score-booster'); ?></h2>
	<hr />
	<?php
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	// Variables for the field and option names
	$hidden_field_name = 'wppsb_submit_hidden';
    $remove_query_strings = 'wppsb_remove_query_strings';
    $enable_gzip = 'wppsb_enable_gzip';
    $expire_caching = 'wppsb_expire_caching';

    // Read in existing option value from database
    $remove_query_strings_val = get_option($remove_query_strings);
    $enable_gzip_val = get_option($enable_gzip);
    $expire_caching_val = get_option($expire_caching);

	// See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[$hidden_field_name]) && $_POST[$hidden_field_name] == 'Y' ) {
        // Read their posted value
        $remove_query_strings_val = (isset($_POST[$remove_query_strings]) ? $_POST[$remove_query_strings] : "");
        $enable_gzip_val = (isset($_POST[$enable_gzip]) ? $_POST[$enable_gzip] : "");
        $expire_caching_val = (isset($_POST[$expire_caching]) ? $_POST[$expire_caching] : "");

        // Save the posted value in the database
        update_option( $remove_query_strings, $remove_query_strings_val );
        update_option( $enable_gzip, $enable_gzip_val );
        update_option( $expire_caching, $expire_caching_val );

		// If 'Enable GZIP" checkbox ticked, add filter otherwise remove filter
		if ($enable_gzip_val == 'on') {
			add_filter('mod_rewrite_rules', 'wppsb_enable_gzip_filter');
			add_filter('mod_rewrite_rules', 'wppsb_vary_accept_encoding_filter');
			/* add_filter( 'init', 'wppsb_enable_gzip_compression' ); */
		}
		else {
			remove_filter('mod_rewrite_rules', 'wppsb_enable_gzip_filter');
			remove_filter('mod_rewrite_rules', 'wppsb_vary_accept_encoding_filter');
			/* add_filter( 'init', 'wppsb_disable_gzip_compression' ); */
		}

		// If 'Expire caching" checkbox ticked, add filter otherwise remove filter
		if ($expire_caching_val == 'on') {
			add_filter('mod_rewrite_rules', 'wppsb_expire_caching_filter');
		}
		else {
			remove_filter('mod_rewrite_rules', 'wppsb_expire_caching_filter');
		}

	    flush_rewrite_rules();

        // Put the settings updated message on the screen
   	?>
   	<div class="updated"><p><strong><?php _e('Settings Saved.', 'wp-performance-score-booster'); ?></strong></p></div>
	<?php
	}
	?>
	<form method="post" name="options_form">
	<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
	<p>
	<input type="checkbox" name="<?php echo $remove_query_strings; ?>" <?php checked( $remove_query_strings_val == 'on',true); ?> /> &nbsp; <span class="wppsb_settings"> <?php _e('Remove query strings from static content', 'wp-performance-score-booster'); ?> </span>
	</p>
	<p>
	<?php if (function_exists('ob_gzhandler') || ini_get('zlib.output_compression')) { ?>
    	<input type="checkbox" name="<?php echo $enable_gzip; ?>" <?php checked( $enable_gzip_val == 'on',true); ?>  /> &nbsp; <span class="wppsb_settings"> <?php _e('Enable GZIP compression (compress text, html, javascript, css, xml and so on)', 'wp-performance-score-booster'); ?> </span>
    <?php }
    else { ?>
    	<input type="checkbox" name="<?php echo $enable_gzip; ?>" disabled="true" <?php checked( $enable_gzip_val == 'on',true); ?> /> &nbsp; <span class="wppsb_settings"> <?php _e('Enable GZIP compression (compress text, html, javascript, css, xml and so on)', 'wp-performance-score-booster'); ?> </span> <br /> <span class="wppsb_settings" style="margin-left:30px; color:RED;"> <?php _e('Your web server does not support GZIP compression. Contact your hosting provider to enable it.', 'wp-performance-score-booster'); ?> </span>
    <?php } ?>
    </p>
    <p>
    <input type="checkbox" name="<?php echo $expire_caching; ?>" <?php checked( $expire_caching_val == 'on',true); ?> /> &nbsp; <span class="wppsb_settings"> <?php _e('Set expire caching (Leverage Browser Caching)', 'wp-performance-score-booster'); ?> </span>
    </p>
    <p><input type="submit" value="<?php esc_attr_e('Save Changes', 'wp-performance-score-booster'); ?>" class="button button-primary" name="submit" /></p>
    </form>
	</td>
	<td style="text-align: left;">
	<div class="wppsb_admin_dev_sidebar_div">
	<img src="http://www.gravatar.com/avatar/38b380cf488d8f8c4007cf2015dc16ac.jpg" width="100px" height="100px" /> <br />
	<span class="wppsb_admin_dev_sidebar"> <?php echo '<img src="' . plugins_url( 'assets/images/wppsb-support-this-16x16.png' , __FILE__ ) . '" > ';  ?> <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3S8BRPLWLNQ38" target="_blank"> <?php _e('Support this plugin and donate', 'wp-performance-score-booster'); ?> </a> </span>
	<span class="wppsb_admin_dev_sidebar"> <?php echo '<img src="' . plugins_url( 'assets/images/wppsb-rate-this-16x16.png' , __FILE__ ) . '" > ';  ?> <a href="http://wordpress.org/support/view/plugin-reviews/wp-performance-score-booster" target="_blank"> <?php _e('Rate this plugin on WordPress.org', 'wp-performance-score-booster'); ?> </a> </span>
	<span class="wppsb_admin_dev_sidebar"> <?php echo '<img src="' . plugins_url( 'assets/images/wppsb-wordpress-16x16.png' , __FILE__ ) . '" > ';  ?> <a href="http://wordpress.org/support/plugin/wp-performance-score-booster" target="_blank"> <?php _e('Get support on on WordPress.org', 'wp-performance-score-booster'); ?> </a> </span>
	<span class="wppsb_admin_dev_sidebar"> <?php echo '<img src="' . plugins_url( 'assets/images/wppsb-github-16x16.png' , __FILE__ ) . '" > ';  ?> <a href="https://github.com/dipakcg/wp-performance-score-booster" target="_blank"> <?php _e('Contribute development on GitHub', 'wp-performance-score-booster'); ?> </a> </span>
	<span class="wppsb_admin_dev_sidebar"> <?php echo '<img src="' . plugins_url( 'assets/images/wppsb-other-plugins-16x16.png' , __FILE__ ) . '" > ';  ?> <a href="http://profiles.wordpress.org/dipakcg#content-plugins" target="_blank"> <?php _e('Get my other plugins', 'wp-performance-score-booster'); ?> </a> </span>
	<span class="wppsb_admin_dev_sidebar"> <?php echo '<img src="' . plugins_url( 'assets/images/wppsb-twitter-16x16.png' , __FILE__ ) . '" > ';  ?>Follow me on Twitter: <a href="https://twitter.com/dipakcgajjar" target="_blank">@dipakcgajjar</a> </span>
	<br />
	<span class="wppsb_admin_dev_sidebar" style="float: right;"> <?php _e('Version:', 'wp-performance-score-booster'); ?> <strong> <?php echo get_option('wppsb_plugin_version'); ?> </strong> </span>
	</div>
	</td>
	</tr>
	</table>
	</div>
	<?php
}

// Register admin menu
add_action( 'admin_menu', 'wppsb_add_admin_menu' );
function wppsb_add_admin_menu() {
	add_menu_page( __('WP Performance Score Booster Settings', 'wp-performance-score-booster'), __('WP Performance Score Booster', 'wp-performance-score-booster'), 'manage_options', 'wp-performance-score-booster', 'wppsb_admin_options', plugins_url('assets/images/wppsb-icon-24x24.png', __FILE__) );
	// add_options_page( __('WP Performance Score Booster Settings', 'wp-performance-score-booster'), __('WP Performance Score Booster', 'wp-performance-score-booster'), 'manage_options', 'wp-performance-score-booster', 'wppsb_admin_options' );
}

// Add header
function wppsb_add_header() {
	// Get the plugin version from options (in the database)
	$wppsb_plugin_version = get_option('wppsb_plugin_version');
	$head_comment = <<<EOD
<!-- Performance scores of this site is tuned by WP Performance Score Booster plugin v$wppsb_plugin_version - http://wordpress.org/plugins/wp-performance-score-booster -->
EOD;
	$head_comment = $head_comment . PHP_EOL;
	print ($head_comment);
}
add_action('wp_head', 'wppsb_add_header', 1);

// Calling this function will make flush_rules to be called at the end of the PHP execution
function wppsb_activate_plugin() {

    // Save default options value in the database
    update_option( 'wppsb_remove_query_strings', 'on' );
    add_filter( 'script_loader_src', 'wppsb_remove_query_strings_q', 15, 1 );
	add_filter( 'style_loader_src', 'wppsb_remove_query_strings_q', 15, 1 );
	add_filter( 'script_loader_src', 'wppsb_remove_query_strings_emp', 15, 1 );
	add_filter( 'style_loader_src', 'wppsb_remove_query_strings_emp', 15, 1 );

	if (function_exists('ob_gzhandler') || ini_get('zlib.output_compression')) {
		update_option( 'wppsb_enable_gzip', 'on' );
		add_filter('mod_rewrite_rules', 'wppsb_enable_gzip_filter');
		add_filter('mod_rewrite_rules', 'wppsb_vary_accept_encoding_filter');
	}
	else {
		update_option( 'wppsb_enable_gzip', '' );
	}

	update_option( 'wppsb_expire_caching', 'on' );
	add_filter('mod_rewrite_rules', 'wppsb_expire_caching_filter');

    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wppsb_activate_plugin' );

// Remove filters/functions on plugin deactivation
function wppsb_deactivate_plugin() {
	delete_option( 'wppsb_plugin_version' );

    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wppsb_deactivate_plugin' );
?>
