<?php

/*
	Support class Cli.gs and Tweet WordPress Plugin
	by Marcel Bokhorst
*/

// Include xml parser
if (!class_exists('XMLParser'))
	require_once('parser_php4.php');

// Define constants
define('c_cltw_text_domain', 'cltw');

define('c_cltw_meta_exclude', 'cltw_exclude');
define('c_cltw_meta_cligs_url', 'cltw_cligs_url');
define('c_cltw_meta_tweet_id', 'cltw_tweet_id');
define('c_cltw_meta_twitter_user', 'cltw_twitter_user');
define('c_cltw_meta_twitter_pwd', 'cltw_twitter_password');
define('c_cltw_meta_twitter_hash', 'cltw_twitter_hashtag');
define('c_cltw_meta_twitter_msg', 'cltw_twitter_message');
define('c_cltw_meta_last_error', 'cltw_last_error');

define('c_cltw_option_version', 'cltw_version');
define('c_cltw_option_post_url', 'cltw_post_url');
define('c_cltw_option_page_url', 'cltw_page_url');
define('c_cltw_option_post_tweet', 'cltw_post_tweet');
define('c_cltw_option_page_tweet', 'cltw_page_tweet');
define('c_cltw_option_auto_discovery', 'cltw_auto_discovery');
define('c_cltw_option_front_page', 'cltw_front_page');
define('c_cltw_option_post_before', 'cltw_post_before');
define('c_cltw_option_post_after', 'cltw_post_after');
define('c_cltw_option_text_icon', 'cltw_text_icon');
define('c_cltw_option_text_before', 'cltw_text_before');
define('c_cltw_option_text_after', 'cltw_text_after');
define('c_cltw_option_http_timeout', 'cltw_http_timeout');
define('c_cltw_option_clean_options', 'cltw_clean_options');
define('c_cltw_option_clean_data', 'cltw_clean_data');
define('c_cltw_option_donated', 'cltw_donated');

// Define class
if (!class_exists('WPCligsAndTweet')) {
	class WPCligsAndTweet {
		// Class variables
		var $main_file = null;

		// Constructor
		function WPCligsAndTweet() {
			$bt = debug_backtrace();
			$this->main_file = $bt[0]['file'];

			// Register deactivation hook
			register_activation_hook($this->main_file, array(&$this, 'cltw_activate'));
			register_deactivation_hook($this->main_file, array(&$this, 'cltw_deactivate'));

			// Register actions/filters
			add_action('init', array(&$this, 'cltw_init'));
			if (is_admin()) {
				add_action('post_submitbox_start', array(&$this, 'cltw_post_submitbox'));
				add_action('save_post', array(&$this, 'cltw_save_post'), 10, 2);
				add_action('admin_menu', array(&$this, 'cltw_admin_menu'));
				add_action('admin_notices', array(&$this, 'cltw_admin_notices'));
			}
			else {
				add_action('wp_head', array(&$this, 'cltw_wp_head'));
				add_filter('the_content', array(&$this, 'cltw_content'));
			}
		}

		// Handle plugin activation
		function cltw_activate() {
			// Set default options
			if (!get_option(c_cltw_option_version)) {
				update_option(c_cltw_option_version, 1);
				update_option(c_cltw_option_post_url, true);
				update_option(c_cltw_option_page_url, true);
				update_option(c_cltw_option_post_tweet, true);
				update_option(c_cltw_option_page_tweet, true);
				update_option(c_cltw_option_auto_discovery, true);
				update_option(c_cltw_option_front_page, true);
				update_option(c_cltw_option_post_before, true);
				update_option(c_cltw_option_post_after, false);
				update_option(c_cltw_option_text_icon, true);
				update_option(c_cltw_option_text_before, '');
				update_option(c_cltw_option_text_after, 'Cli.gs');
				update_option(c_cltw_option_http_timeout, 5);
				update_option(c_cltw_option_clean_options, false);
				update_option(c_cltw_option_clean_data, false);

				global $wpdb;
				$rows = $wpdb->get_results("SELECT ID FROM " . $wpdb->users);
				foreach ($rows as $row)
					update_usermeta($row->ID, c_cltw_meta_twitter_msg, '[hash] New: [post] on [blog] [url]');
			}
		}

		// Handle plugin deactivation
		function cltw_deactivate() {
			// Cleanup data
			if (get_option(c_cltw_option_clean_data)) {
				global $wpdb;
				$sql = "DELETE FROM " . $wpdb->postmeta;
				$sql .= " WHERE meta_key='" . c_cltw_meta_cligs_url . "'";
				$sql .= " OR meta_key='" . c_cltw_meta_tweet_id . "'";
				if ($wpdb->query($sql) === false)
					$wpdb->print_error();
			}

			// Cleanup settings
			if (get_option(c_cltw_option_clean_options)) {
				global $wpdb;
				$sql = "DELETE FROM " . $wpdb->usermeta;
				$sql .= " WHERE meta_key='" . c_cltw_meta_twitter_user . "'";
				$sql .= " OR meta_key='" . c_cltw_meta_twitter_pwd . "'";
				$sql .= " OR meta_key='" . c_cltw_meta_twitter_hash . "'";
				$sql .= " OR meta_key='" . c_cltw_meta_twitter_msg . "'";
				$sql .= " OR meta_key='" . c_cltw_meta_last_error . "'";
				if ($wpdb->query($sql) === false)
					$wpdb->print_error();

				delete_option(c_cltw_option_version);
				delete_option(c_cltw_option_post_url);
				delete_option(c_cltw_option_page_url);
				delete_option(c_cltw_option_post_tweet);
				delete_option(c_cltw_option_page_tweet);
				delete_option(c_cltw_option_auto_discovery);
				delete_option(c_cltw_option_front_page);
				delete_option(c_cltw_option_post_before);
				delete_option(c_cltw_option_post_after);
				delete_option(c_cltw_option_text_icon);
				delete_option(c_cltw_option_text_before);
				delete_option(c_cltw_option_text_after);
				delete_option(c_cltw_option_http_timeout);
				delete_option(c_cltw_option_clean_options);
				delete_option(c_cltw_option_clean_data);
			}
		}

		// Handle initialize
		function cltw_init() {
			// I18n
			if (is_admin())
				load_plugin_textdomain(c_cltw_text_domain, false, basename(dirname($this->main_file)));

			// Load style sheets
			$css_name = $this->change_extension(basename($this->main_file), '.css');
			$plugin_url = WP_PLUGIN_URL . '/' . basename(dirname($this->main_file));
			if (file_exists(TEMPLATEPATH . '/' . $css_name))
				$css_url = get_bloginfo('template_directory') . '/' . $css_name;
			else
				$css_url = $plugin_url . '/' . $css_name;
			wp_register_style('cltw_style', $css_url);
			wp_enqueue_style('cltw_style');

			// Load scripts
			if (!is_admin())
				wp_enqueue_script('jquery');
		}

		// Modify blog head
		function cltw_wp_head() {
			// Output short url auto-discovery code
			if (get_option(c_cltw_option_auto_discovery))
				if (is_single() || is_page()) {
					$cligs_url = $this->cltw_get_cligs_url();
					if ($cligs_url)
						echo '<link rel="shorturl" href="' . $cligs_url . '" />';
				}
?>
			<script type="text/javascript">
			//* <![CDATA[ */
				jQuery(document).ready(function($) {
					/* Hide cli.gs url by default */
					$('.cltw_link').hide();

					/* Show/select cli.gs url on click */
					$('.cltw_toggle').click(function() {
						var lnk = $(this).parent().contents('.cltw_link');
						lnk.toggle();
						lnk.children(':first').select();
						return false;
					});
				});
			/* ]]> */
			</script>
<?php
		}

		// Modify post contents
		function cltw_content($content = '') {
			// Prepend/append cli.gs link
			if (get_option(c_cltw_option_front_page) ? true : !is_front_page()) {
				$cligs_url = $this->cltw_get_cligs_url();
				if ($cligs_url) {
					$cligs_content = '<div class="cltw_container"><a class="cltw_toggle" href="#">';
					$cligs_content .= htmlspecialchars(trim(get_option(c_cltw_option_text_before)));

					if (get_option(c_cltw_option_text_icon)) {
						$cligs_icon_url = WP_PLUGIN_URL . '/' . basename(dirname($this->main_file)) . '/cligs-icon.png';
						$cligs_content .= '<img src="' . $cligs_icon_url . '" />';
					}

					$cligs_content .= htmlspecialchars(trim(get_option(c_cltw_option_text_after)));
					$cligs_content .= '</a><form class="cltw_link" action="#" style="display: none;"><input type="text" value="' . $cligs_url . '" /></form></div>';

					if (get_option(c_cltw_option_post_before))
						$content = $cligs_content . $content;
					if (get_option(c_cltw_option_post_after))
						$content .= $cligs_content;
				}
			}
			return $content;
		}

		// Handle post submit box
		function cltw_post_submitbox() {
			$postid = $this->cltw_get_post_id();
			$exclude = get_post_meta($postid, c_cltw_meta_exclude, true);
			$chk_exclude = $exclude ? 'checked' : 'unchecked';
?>
			<div id="cltw_post_submit">
			<input type="checkbox" name="cltw_exclude" <?php echo $chk_exclude; ?> />
			<span><?php _e('Do not create short URL', c_cltw_text_domain); ?></span>
			</div>
<?php
		}

		// Handle save post
		function cltw_save_post($postid, $post) {
			// Get post id (no revisions)
			$postid = $this->cltw_get_post_id($postid);

			// Process exclude flag
			$exclude = $_POST['cltw_exclude'];
			update_post_meta($postid, c_cltw_meta_exclude, $exclude);

			// Check post status
			if (!$exclude && $post->post_status == 'publish') {
				// Get current user
				global $user_ID;
				get_currentuserinfo();

				// Get long url and title
				$permalink = get_permalink($postid);
				$title = trim($post->post_title);

				// Check if permalink and title
				if ($permalink != '' && $title != '') {
					// Cli.gs
					if (($post->post_type == 'post' && get_option(c_cltw_option_post_url)) ||
						($post->post_type == 'page' && get_option(c_cltw_option_page_url))) {
						// Get/check cli.gs url
						$cligs_url = $this->cltw_get_cligs_url($postid);
						if (!$cligs_url) {
							// Create/store cli.gs url
							$cligs_url = $this->cltw_cligs_create_url($permalink, $title);
							if ($cligs_url)
								add_post_meta($postid, c_cltw_meta_cligs_url, $cligs_url, true);
							else
								update_usermeta($user_ID, c_cltw_meta_last_error, error_get_last());
						}
					}

					// Tweet
					if (($post->post_type == 'post' && get_option(c_cltw_option_post_tweet)) ||
						($post->post_type == 'page' && get_option(c_cltw_option_page_tweet))) {
						// Get existing tweet id
						$tweet_id = get_post_meta($postid, c_cltw_meta_tweet_id, true);
						$tweet_id = ($tweet_id == '' ? false : $tweet_id);
						if ($cligs_url && !$tweet_id) {
							// Get twitter authentication
							$twit_user = get_usermeta($user_ID, c_cltw_meta_twitter_user);
							$twit_pwd = get_usermeta($user_ID, c_cltw_meta_twitter_pwd);

							// Get hashtag
							$twit_hash = get_usermeta($user_ID, c_cltw_meta_twitter_hash);
							if ($twit_hash != '' && strpos($twit_hash, '#') !== 0)
								$twit_hash = '#' . $twit_hash;

							// Format twitter message
							$twit_msg = get_usermeta($user_ID, c_cltw_meta_twitter_msg);
							$twit_msg = str_replace('[hash]', $twit_hash, $twit_msg);
							$twit_msg = str_replace('[post]', $title, $twit_msg);
							$twit_msg = str_replace('[blog]', get_bloginfo(), $twit_msg);
							$twit_msg = str_replace('[url]', $cligs_url, $twit_msg);

							// Check arguments
							if ($twit_user != '' && $twit_pwd != '' && $twit_msg != '') {
								// Send twitter message
								$tweet_xml = $this->cltw_twit($twit_user, $twit_pwd, $twit_msg);
								if ($tweet_xml) {
									// Parse result to get tweet id
									$tweet_id = false;
									$parser = new XMLParser($tweet_xml);
									$parser->Parse();
									foreach ($parser->document->tagChildren as $child)
										if ($child->tagName == 'id')
											$tweet_id = $child->tagData;

									// Store tweet id
									if ($tweet_id)
										update_post_meta($postid, c_cltw_meta_tweet_id, $tweet_id);
									else
										update_usermeta($user_ID, c_cltw_meta_last_error, 'Tweet id not found');
								}
								else
									update_usermeta($user_ID, c_cltw_meta_last_error, error_get_last());
							}
						}
					}
				}
			}
		}

		// Handle admin notices
		function cltw_admin_notices() {
			// Get current user
			global $user_ID;
			get_currentuserinfo();

			// Check/display errors
			$error = get_usermeta($user_ID, c_cltw_meta_last_error);
			if ($error) {
				echo '<div class="error fade cltw_notice"><p>' . htmlspecialchars($error['message']) . '</p></div>';
				delete_usermeta($user_ID, c_cltw_meta_last_error);
			}
		}

		// Add management & options page
		function cltw_admin_menu() {
			// Add tools page
			if (function_exists('add_management_page')) {
				add_management_page(
					__('Cli.gs and Tweet', c_cltw_text_domain),
					__('Cli.gs and Tweet', c_cltw_text_domain),
					'publish_posts',
					'cltw-management',
					array(&$this, 'cltw_user_settings'));
			}

			// Add settings page
			if (function_exists('add_options_page'))
				add_options_page(
					__('Cli.gs and Tweet', c_cltw_text_domain),
					__('Cli.gs and Tweet', c_cltw_text_domain),
					'manage_options',
					'cltw-options',
					array(&$this, 'cltw_admin_settings'));
		}

		// Handle tools menu
		function cltw_user_settings() {
			// Get current user
			global $user_ID;
			get_currentuserinfo();

			// Check for update
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'update') {
				// Security check
				check_admin_referer('cltw-update');

				// Update values
				update_usermeta($user_ID, c_cltw_meta_twitter_user, trim($_POST[c_cltw_meta_twitter_user]));
				update_usermeta($user_ID, c_cltw_meta_twitter_pwd, trim($_POST[c_cltw_meta_twitter_pwd]));
				update_usermeta($user_ID, c_cltw_meta_twitter_msg, trim($_POST[c_cltw_meta_twitter_msg]));
				update_usermeta($user_ID, c_cltw_meta_twitter_hash, trim($_POST[c_cltw_meta_twitter_hash]));

				// Display update
				echo '<div class="updated fade cltw_message"><p>' . __('Settings Saved') . '</p></div>';
			}

			echo '<div class="wrap">';

			// Render Info panel
			$this->cltw_render_info_panel();
?>
			<div id="cltw_admin_panel">
			<h2><?php _e('Cli.gs and Tweet Settings', c_cltw_text_domain) ?></h2>

			<form method="post" action="<?php echo add_query_arg('action', 'update'); ?>">
			<?php wp_nonce_field('cltw-update'); ?>

			<table class="form-table">

			<tr><th scope="row"><?php _e('Twitter user name:', c_cltw_text_domain) ?></th>
			<td><input type="text" name="<?php echo c_cltw_meta_twitter_user; ?>" value="<?php echo get_usermeta($user_ID, c_cltw_meta_twitter_user); ?>" /></td></tr>

			<tr><th scope="row"><?php _e('Twitter password:', c_cltw_text_domain) ?></th>
			<td><input type="password" name="<?php echo c_cltw_meta_twitter_pwd; ?>" value="<?php echo get_usermeta($user_ID, c_cltw_meta_twitter_pwd); ?>" />
			<span class="cltw_admin_hint"><?php _e('Will be stored and sent in clear text', c_cltw_text_domain) ?></span></td></tr>

			<tr><th scope="row"><?php _e('Twitter hashtag:', c_cltw_text_domain) ?></th>
			<td><input type="text" name="<?php echo c_cltw_meta_twitter_hash; ?>" value="<?php echo get_usermeta($user_ID, c_cltw_meta_twitter_hash); ?>" />
			<span class="cltw_admin_hint"><a href="http://en.wikipedia.org/wiki/Tag_(metadata)#Hash_tags" target="_blank"><?php _e('Details', c_cltw_text_domain) ?></a></td></tr>

			<tr><th scope="row"><?php _e('Twitter message:', c_cltw_text_domain) ?></th>
			<td><input type="text" name="<?php echo c_cltw_meta_twitter_msg; ?>" value="<?php echo get_usermeta($user_ID, c_cltw_meta_twitter_msg);?>" size="50" /></td></tr>

			<tr><th scope="row"></th>
			<td><table id="cltw_legend">
			<tr><td>[hash]</td><td><?php _e('The set Twitter hashtag', c_cltw_text_domain) ?></td></tr>
			<tr><td>[post]</td><td><?php _e('The post title', c_cltw_text_domain) ?></td></tr>
			<tr><td>[blog]</td><td><?php _e('The blog title', c_cltw_text_domain) ?></td></tr>
			<tr><td>[url]</td><td><?php _e('The Clig.s short url', c_cltw_text_domain) ?></td></tr>
			</table></td></tr>

			</table>
			<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save', c_cltw_text_domain) ?>" /></p>
			</form>

			<h2><?php _e('Create URLs for existing posts/pages', c_cltw_text_domain) ?></h2>
<?php
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'create') {
				// Security check
				check_admin_referer('cltw-create');
				// Create Cli.gs URLs for existing posts for current user
				$this->cltw_create_urls($user_ID);
			}
?>
			<form method="post" action="<?php echo add_query_arg('action', 'create'); ?>">
			<?php wp_nonce_field('cltw-create'); ?>
			<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Create', c_cltw_text_domain) ?>" /></p>
			</form>
			</div>
			</div>
<?php
		}

		// Helper create URLs for existing post
		function cltw_create_urls($user_ID) {
			$count = 0;
			$errors = 0;
			global $wpdb;
			$rows = $wpdb->get_results("SELECT ID, post_status, post_type, post_title FROM " . $wpdb->posts . " WHERE post_author=" . $user_ID);
			echo '<div id="cltw_create"><table>';
			foreach ($rows as $row) {
				$exclude = get_post_meta($row->ID, c_cltw_meta_exclude, true);
				// Check if post published
				if (!$exclude && $row->post_status == 'publish') {
					// Get post data
					$postid = $this->cltw_get_post_id($row->ID);
					$permalink = get_permalink($postid);
					$title = trim($row->post_title);

					// Check if permalink and title
					if ($permalink != '' && $title != '') {
						// Cli.gs
						if (($row->post_type == 'post' && get_option(c_cltw_option_post_url)) ||
							($row->post_type == 'page' && get_option(c_cltw_option_page_url))) {
							// Get/check cli.gs url
							$cligs_url = $this->cltw_get_cligs_url($postid);
							if (!$cligs_url) {
								// Create/store cli.gs url
								$cligs_url = $this->cltw_cligs_create_url($permalink, $title);
								if ($cligs_url) {
									$count++;
									add_post_meta($row->ID, c_cltw_meta_cligs_url, $cligs_url, true);
									echo '<tr><td>' . $row->post_type . '</td><td>' . $row->post_title . '</td><td>' . $cligs_url . '</td></tr>';
								}
								else {
									$errors++;
									$error = error_get_last();
									echo '<tr><td>' . $row->post_type . '</td><td>' . $row->post_title . '</td><td>' . $error['message'] . '</td></tr>';
								}
							}
						}
					}
				}
			}
			echo '</table>';
			echo '<span>' . $count . ' ' . __('URL(s) created') . ', ' . $errors . ' ' . __('error(s)') . '</span>';
			echo '</div>';
		}

		// Handle settings page
		function cltw_admin_settings() {
			// Build option name array
			$options = array();
			$options[] = c_cltw_option_post_url;
			$options[] = c_cltw_option_page_url;
			$options[] = c_cltw_option_post_tweet;
			$options[] = c_cltw_option_page_tweet;
			$options[] = c_cltw_option_auto_discovery;
			$options[] = c_cltw_option_front_page;
			$options[] = c_cltw_option_post_before;
			$options[] = c_cltw_option_post_after;
			$options[] = c_cltw_option_text_icon;
			$options[] = c_cltw_option_text_before;
			$options[] = c_cltw_option_text_after;
			$options[] = c_cltw_option_http_timeout;
			$options[] = c_cltw_option_clean_options;
			$options[] = c_cltw_option_clean_data;
			$options[] = c_cltw_option_donated;

			// Get boolean states
			$chk_post_url = get_option(c_cltw_option_post_url) ? 'checked' : 'unchecked';
			$chk_page_url = get_option(c_cltw_option_page_url) ? 'checked' : 'unchecked';
			$chk_post_tweet = get_option(c_cltw_option_post_tweet) ? 'checked' : 'unchecked';
			$chk_page_tweet = get_option(c_cltw_option_page_tweet) ? 'checked' : 'unchecked';
			$chk_disco = get_option(c_cltw_option_auto_discovery) ? 'checked' : 'unchecked';
			$chk_front_page = get_option(c_cltw_option_front_page) ? 'checked' : 'unchecked';
			$chk_post_before = get_option(c_cltw_option_post_before) ? 'checked' : 'unchecked';
			$chk_post_after = get_option(c_cltw_option_post_after) ? 'checked' : 'unchecked';
			$chk_text_icon = get_option(c_cltw_option_text_icon) ? 'checked' : 'unchecked';
			$chk_cleanup_options = get_option(c_cltw_option_clean_options) ? 'checked' : 'unchecked';
			$chk_cleanup_data = get_option(c_cltw_option_clean_data) ? 'checked' : 'unchecked';
			$chk_donated = get_option(c_cltw_option_donated) ? 'checked' : 'unchecked';

			$this->cltw_render_pluginsponsor();

			echo '<div class="wrap">';

			// Render Info panel
			$this->cltw_render_info_panel();

			// Render settings form
?>
			<div id="cltw_admin_panel">
			<h2><?php _e('Cli.gs and Tweet Settings', c_cltw_text_domain) ?></h2>

			<form method="post" action="<?php echo admin_url('options.php'); ?>">
			<?php wp_nonce_field('update-options'); ?>

			<table class="form-table">

			<tr><th scope="row"><?php _e('Create short URLs for posts:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_post_url; ?>" <?php echo $chk_post_url; ?> /></td></tr>

			<tr><th scope="row"><?php _e('Create short URLs for pages:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_page_url; ?>" <?php echo $chk_page_url; ?> /></td></tr>

			<tr><th scope="row"><?php _e('Send tweet for posts:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_post_tweet; ?>" <?php echo $chk_post_tweet; ?> /></td></tr>

			<tr><th scope="row"><?php _e('Send tweet for pages:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_page_tweet; ?>" <?php echo $chk_page_tweet; ?> /></td></tr>

			<tr><th scope="row"><?php _e('Short URL auto-discovery:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_auto_discovery; ?>" <?php echo $chk_disco; ?> />
			<span class="cltw_admin_hint"><a href="http://wiki.snaplog.com/short_url" target="_blank"><?php _e('Details', c_cltw_text_domain) ?></a></td></tr>

			<tr><th scope="row"><?php _e('Display short URLs on frontpage:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_front_page; ?>" <?php echo $chk_front_page; ?> /></td></tr>

			<tr><th scope="row"><?php _e('Add short URL before post:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_post_before; ?>" <?php echo $chk_post_before; ?> /></td></tr>

			<tr><th scope="row"><?php _e('Add short URL after post:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_post_after; ?>" <?php echo $chk_post_after; ?> /></td></tr>

			<tr><th scope="row"><?php _e('Show icon:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_text_icon; ?>" <?php echo $chk_text_icon; ?> /></td></tr>

			<tr><th scope="row"><?php _e('Text before icon:', c_cltw_text_domain) ?></th>
			<td><input type="text" name="<?php echo c_cltw_option_text_before; ?>" value="<?php echo get_option(c_cltw_option_text_before); ?>" /></td></tr>

			<tr><th scope="row"><?php _e('Text after icon:', c_cltw_text_domain) ?></th>
			<td><input type="text" name="<?php echo c_cltw_option_text_after; ?>" value="<?php echo get_option(c_cltw_option_text_after); ?>" /></td></tr>

			<tr><th scope="row"><?php _e('Request time-out:', c_cltw_text_domain) ?></th>
			<td><input type="text" name="<?php echo c_cltw_option_http_timeout; ?>" value="<?php echo get_option(c_cltw_option_http_timeout); ?>" />
			<span class="cltw_admin_hint"><?php _e('Seconds', c_cltw_text_domain) ?></span></td></tr>

			<tr><th scope="row"><?php _e('Delete options on deactivate:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_clean_options; ?>" <?php echo $chk_cleanup_options; ?> />
			<span class="cltw_admin_hint"><?php _e('These and the tool settings', c_cltw_text_domain) ?></span></td></tr>

			<tr><th scope="row"><?php _e('Delete data on deactivate:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_clean_data; ?>" <?php echo $chk_cleanup_data; ?> />
			<span class="cltw_admin_hint"><?php _e('Custom fields related to this plugin', c_cltw_text_domain) ?></span></td></tr>

			<tr><th scope="row"><?php _e('I have donated to this plugin:', c_cltw_text_domain) ?></th>
			<td><input type="checkbox" name="<?php echo c_cltw_option_donated; ?>" <?php echo $chk_donated; ?> /></td></tr>

			</table>

			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="<?php echo implode(',', $options); ?>" />

			<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save', c_cltw_text_domain) ?>" /></p>
			</form>
			</div>
			</div>
<?php
		}

		// Helper display resources panel
		function cltw_render_info_panel() {
?>
			<div id="cltw_resources_panel">
			<h3><?php _e('Resources', c_cltw_text_domain); ?></h3>
			<ul>
			<li><a href="http://cli.gs/" target="_blank">Cli.gs</a></li>
			<li><a href="http://twitter.com/" target="_blank">Twitter</a></li>
			<li><a href="http://wiki.snaplog.com/short_url" target="_blank"><?php _e('Short URL Auto-Discovery', c_cltw_text_domain); ?></a></li>
			<li><a href="http://wordpress.org/extend/plugins/cligs-and-tweet/" target="_blank"><?php _e('Usage instructions', c_cltw_text_domain); ?></a></li>
			<li><a href="http://wordpress.org/extend/plugins/cligs-and-tweet/faq/" target="_blank"><?php _e('Frequently asked questions', c_cltw_text_domain); ?></a></li>
			<li><a href="http://blog.bokhorst.biz/2354/computers-en-internet/wordpress-plugin-cli-gs-and-tweet/" target="_blank"><?php _e('Support page', c_cltw_text_domain); ?></a></li>
			<li><a href="http://blog.bokhorst.biz/about/" target="_blank"><?php _e('About the author', c_cltw_text_domain); ?></a></li>
			</ul>
<?php		if (!get_option(c_cltw_option_donated)) { ?>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHXwYJKoZIhvcNAQcEoIIHUDCCB0wCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYBVOe8AYcsad6iCloBLCg/ivkURYdeKvrIK74Co2gIt6LBpy4nKZtfKM0y8f93vftZlFVG98GM8OZcdMZqrACB/tJECpAZTguIE0sExFXaekGW6ap3E9eOyWyXCGtP7jaAm8rmqiDmlfvadG5tWJ3wSBLPbG5ZLPKM3ZpTALUDtVDELMAkGBSsOAwIaBQAwgdwGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIQmoXyEwG4IqAgbgvYtvI+HL/a5km6GOPGIR1HAWsvSdZ9mus/4M+NNXhwwsDZXhuemcdI2SVgnIj9kgpRMu6jRJl/euBaHBCHzmU+daDl3LUvy8MJ3O2/1P3wEvDScDg2zOUMpTyy/cKqhXAgAtmFFwWYICpYxjoyRvvaGGOfB5528jV9qlqC+r4OPW3ryWp0lQ9l+H3SnR6r+tBNwNUpmGsk9AzVxB6W0DfUMtjR1eK7bCWWpTberWaY6DeAsMc5NYioIIDhzCCA4MwggLsoAMCAQICAQAwDQYJKoZIhvcNAQEFBQAwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMB4XDTA0MDIxMzEwMTMxNVoXDTM1MDIxMzEwMTMxNVowgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBR07d/ETMS1ycjtkpkvjXZe9k+6CieLuLsPumsJ7QC1odNz3sJiCbs2wC0nLE0uLGaEtXynIgRqIddYCHx88pb5HTXv4SZeuv0Rqq4+axW9PLAAATU8w04qqjaSXgbGLP3NmohqM6bV9kZZwZLR/klDaQGo1u9uDb9lr4Yn+rBQIDAQABo4HuMIHrMB0GA1UdDgQWBBSWn3y7xm8XvVk/UtcKG+wQ1mSUazCBuwYDVR0jBIGzMIGwgBSWn3y7xm8XvVk/UtcKG+wQ1mSUa6GBlKSBkTCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb22CAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCBXzpWmoBa5e9fo6ujionW1hUhPkOBakTr3YCDjbYfvJEiv/2P+IobhOGJr85+XHhN0v4gUkEDI8r2/rNk1m0GA8HKddvTjyGw/XqXa+LSTlDYkqI8OwR8GEYj4efEtcRpRYBxV8KxAW93YDWzFGvruKnnLbDAF6VR5w/cCMn5hzGCAZowggGWAgEBMIGUMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbQIBADAJBgUrDgMCGgUAoF0wGAYJKoZIhvcNAQkDMQsGCSqGSIb3DQEHATAcBgkqhkiG9w0BCQUxDxcNMDkwODA5MTYxNTI0WjAjBgkqhkiG9w0BCQQxFgQUjfxnWb1mMwu65R8ho8tlSQKqqcswDQYJKoZIhvcNAQEBBQAEgYBnXMTHM/xaEPwPapCUMSIc8fef8pfKVk/IMZZm3bnyA5v1jfI1VTYvSTPBMqHnDLexdX6KE9bdSQ5HJLrgnD40stILw3+AE/CksJyJ0Jc+cuq66G/DTJxlbCQrnfWIV9A5RD9YGN4Si/fiIFi3WNd4YPy7Oe0brzcpW5+jyYG5zw==-----END PKCS7-----
			">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			</form>
<?php		} ?>
			</div>
<?php
		}

		// Helper get (current) post id
		function cltw_get_post_id($postid = false) {
			global $post;
			$postid = ($postid ? $postid : $post->ID);
			$revision = wp_is_post_revision($postid);
			return ($revision ? $revision : $postid);
		}

		// Helper get existing cli.gs url
		function cltw_get_cligs_url($postid = false) {
			$postid = $this->cltw_get_post_id($postid);
			$value = get_post_meta($postid, c_cltw_meta_cligs_url, true);
			return ($value == '' ? false : $value);
		}

		// Helper method to create cli.gs url
		function cltw_cligs_create_url($longurl, $title) {
			// Build cli-gs query
			$query = http_build_query(array(
				'url' => $longurl,
				'title' => $title
			));

			// Get time out
			$timeout = intval(get_option(c_cltw_option_http_timeout));
			if (!$timeout || $timeout <= 0) $timeout = 5;

			// Build cli.gs query context
			$context = stream_context_create(array(
				'http' => array(
					'method'  => 'GET',
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'timeout' => $timeout
				),
			));

			// Run cli.gs query
			return @file_get_contents('http://cli.gs/api/v1/cligs/create?' . $query, false, $context);
		}

		// Helper method to send twitter message
		function cltw_twit($username, $password, $message)
		{
			// Get time out
			$timeout = intval(get_option(c_cltw_option_http_timeout));
			if (!$timeout || $timeout <= 0) $timeout = 5;

			// Build twitter query context
			$context = stream_context_create(array(
				'http' => array(
					'method'  => 'POST',
					'header'  =>
						"Authorization: Basic " . base64_encode($username . ':' . $password) . "\r\n" .
						"Content-type: application/x-www-form-urlencoded\r\n",
					'content' => http_build_query(array('status' => $message)),
					'timeout' => $timeout
				),
			));

			// Send twitter status update
			return @file_get_contents('http://twitter.com/statuses/update.xml', false, $context);
		}

		// Helper check environment
		function cltw_check_prerequisites() {
			// Check PHP version
			if (version_compare(PHP_VERSION, '4.3.0', '<'))
				die('Cli.gs requires at least PHP 4.3.0');
			// file_get_contents requires 4.3.0

			// Check WordPress version
			global $wp_version;
			if (version_compare($wp_version, '2.7') < 0)
				die('Cli.gs requires at least WordPress 2.7');

			// Check basic prerequisities
			WPCligsAndTweet::cltw_check_function('register_activation_hook');
			WPCligsAndTweet::cltw_check_function('register_deactivation_hook');
			WPCligsAndTweet::cltw_check_function('add_action');
			WPCligsAndTweet::cltw_check_function('add_filter');
			WPCligsAndTweet::cltw_check_function('wp_register_style');
			WPCligsAndTweet::cltw_check_function('wp_enqueue_style');
			WPCligsAndTweet::cltw_check_function('wp_enqueue_script');
		}

		function cltw_check_function($name) {
			if (!function_exists($name))
				die('Required WordPress function "' . $name . '" does not exist');
		}

		// Helper change file name extension
		function change_extension($filename, $new_extension) {
			return preg_replace('/\..+$/', $new_extension, $filename);
		}
	}
}

?>
