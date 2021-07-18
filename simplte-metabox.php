<?php
	/**
	 * Plugin Name:       Simple Metabox
	 * Version:           1.0.1
	 * License:           GPL v2 or later
	 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
	 * Text Domain:       simple-metabox
	 * Domain Path:       /languages
	 */

	class SimpleMetabox {
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'simplemeta_load_textdomain' ) );
			add_action( 'admin_menu', array( $this, 'simplemeta_add_meta_box' ) );
			add_action( 'save_post', array( $this, 'simplemeta_save_postmeta' ) );
		}

		public function simplemeta_load_textdomain() {
			load_plugin_textdomain( 'simple-metabox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		function simplemeta_add_meta_box() {
			add_meta_box(
				'sm_post_location',                 // Unique ID
				__( 'Location Info', 'simple-metabox' ),      // Box title
				array( $this, 'simplemeta_display_post_location' ),  // Content callback, must be of type callable
				'post'                            // Post type
			);
		}
		function simplemeta_display_post_location( $post ) {
			$country     = get_post_meta( $post->ID, 'simplemeta_country', true );
			$city     = get_post_meta( $post->ID, 'simplemeta_city', true );
			$country_label        = __( 'Country', 'simple_metabox' );
			$city_label        = __( 'City', 'simple_metabox' );
			wp_nonce_field( 'simplemeta_location_info', 'simplemeta_location_field');
			$metabox_html = <<<EOD
<p>
	<label for="simplemeta_country">{$country_label}</label>
	<input type="text" name="simplemeta_country" id="simplemeta_country" value={$country}>
	<br>
	<br>
	<label for="simplemeta_city">{$city_label}</label>
	<input type="text" name="simplemeta_city" id="simplemeta_city" value={$city}>
</p>
EOD;
			echo $metabox_html;

		}


		function simplemeta_save_postmeta( $post_id ) {
			if ( ! $this->is_secured_postmeta( 'simplemeta_location_field', 'simplemeta_location_info', $post_id ) ) {
				return $post_id;
			}
			$country =  isset( $_POST['simplemeta_country'] ) ? $_POST['simplemeta_country'] : '' ;
			$city =  isset( $_POST['simplemeta_city'] ) ? $_POST['simplemeta_city'] : '' ;

			// Sanitizing Input Values
			$country = sanitize_text_field($country);
			$city   = sanitize_text_field($city);

			// Checking if meta data exists
			$this->checkPostMeta('simplemeta_country', $country , $post_id);
			$this->checkPostMeta( 'simplemeta_city', $city , $post_id);
		}

		function checkPostMeta($meta_key, $meta_field_val, $post_id) {
			if ( array_key_exists( $meta_key, $_POST ) ) {
				update_post_meta(
					$post_id,
					$meta_key,
					$meta_field_val
				);
			} else {
				$post_id;
			}
		}

		/*Securing Post Meta*/
		private function is_secured_postmeta($nonce_field, $action, $post_id) {
			$nonce = isset( $_POST[$nonce_field] ) ? $_POST[$nonce_field] : '' ;
			if ( $nonce == ''
			     || ! wp_verify_nonce( $nonce, $action )
			     || ! current_user_can( 'edit_post', $post_id )
			     || wp_is_post_autosave( $post_id )
			     || wp_is_post_revision( $post_id ) ) {
				return false;
			} else {
				return true;
			}
		}
	}

	new SimpleMetabox();