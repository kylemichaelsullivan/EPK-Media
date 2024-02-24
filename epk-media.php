<?php
/**
 * Plugin Name: EPK Media
 * 
 * Description: This plugin handles the ordering of all media: music, photos, videos.
 * Version: 1.0.0
 * Author: Beer City Bands
 * Author URI: https://beercitybands.com/
 * 
 * @link https://developer.wordpress.org/reference/functions/add_media_page/
 * @link https://codex.wordpress.org/Plugin_API
 *
 * @since 1.0.0
 */

define( 'EPK_MEDIA_VERSION', '1.0.0' );
define( 'EPK_MEDIA_PATH', plugin_dir_path( __FILE__ ) );
define( 'EPK_MEDIA_URL', plugin_dir_url( __FILE__ ) );

class EPK_Media {
	private $menu_slug;
	private $tabs;
	private $active_tab;

	public function __construct() {
		$this->menu_slug = 'manage';
		$this->tabs = array(
			'Audio' => 'dashicons-format-audio',
			'Image' => 'dashicons-format-image',
			'Video' => 'dashicons-editor-video',
		);
		$this->active_tab = $_GET[ 'tab' ] ?? '';

		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('admin_menu', array($this, 'add_manage_page'));
	}

	public function enqueue_scripts() {
		$current_screen = get_current_screen();
		// only enqueue epk-media.css on this screen
		if($current_screen->id === 'media_page_manage') {
			wp_enqueue_style( 'epk-media-css',
				EPK_MEDIA_URL . 'epk-media.css',
				'',
				EPK_MEDIA_VERSION
			);

			wp_enqueue_script( 'epk-media-js',
				EPK_MEDIA_URL . 'epk-media.js',
				'',
				EPK_MEDIA_VERSION
			);
		}
	}

	public function add_manage_page() {
		add_media_page( 'Media Manager',     // $page_title
			'Manager',                         // $menu_title
			'manage_options',                  // $capability
			$this->menu_slug,                  // $menu_slug
			array($this, 'render_manage_page') // $callback
		);
	}

	private function render_manage_title() {
		echo '<div class="wrap" id="epk-manage-media">';
		echo '<h2>EPK Media Manager</h2>';
		echo '<div class="description">Choose what media will render on the homepage and in what order.</div>';
	}

	private function render_manage_tabs() {
		echo '<h2 class="nav-tab-wrapper">';
			foreach ($this->tabs as $Tab => $dashicon) {
				$tab = strtolower($Tab);
				$classNames = $this->active_tab == $tab ? 'nav-tab nav-tab-active' : 'nav-tab';
				$icon = sprintf('<span class="dashicons %s"></span>', $dashicon);
		
				printf('<a href="?page=%1$s&tab=%2$s" class="%3$s" title="%4$s">%5$s <span class="nav-tab-text">%4$s</span></a>', $this->menu_slug, $tab, $classNames, $Tab, $icon);
			}
		echo '</h2>';
	}

	private function render_manage_content() {
		$mimes = array(
			'audio',
			'image',
			'video'
		);

		if( $this->active_tab && in_array($this->active_tab, $mimes) ) {
			include EPK_MEDIA_PATH . "/$this->active_tab.php";
		} else {
			echo '<h2>Pick a media type.</h2>';
		}
	}

	public function render_manage_page() {
		$this->render_manage_title();
		$this->render_manage_tabs();
		$this->render_manage_content();
	}
}

if( ! function_exists('get_acf_field_key') ) {
	function get_acf_field_key($field_name, $post_id) {
		return get_field_object($field_name, $post_id)['key'];
	}
}

if( ! function_exists('update_epk_media') ) {
	function update_epk_media() {
		// Check if the form has been submitted & verify nonce
		if( isset($_POST['submit']) ) {
			check_admin_referer('update_epk_media_nonce', 'nonce_field');

			$medias = json_decode(stripslashes($_POST['epk-media']));

			foreach ($medias as $media) {
				$id = $media->id;
				$order = $media->order;
				$render = $media->render;
				$field = get_acf_field_key('render', $id);

				wp_update_post(array(
					'ID' => $id,
					'menu_order' => $order,
				));

				update_field($field, $render, $id);
			}

			$redirect_URL = "upload.php?page=manage";
			$redirect_URL .= isset( $_POST['epk_media_type'] ) ? "&tab={$_POST['epk_media_type']}" : '';

			wp_redirect($redirect_URL);
			exit;
		}
	}
	add_action('admin_post_update_epk_media', 'update_epk_media');
}

$epk_media = new EPK_Media();