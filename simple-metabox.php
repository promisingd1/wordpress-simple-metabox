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
			add_action( 'admin_menu', array( $this, 'simplemeta_add_metabox' ) );
			add_action( 'save_post', array( $this, 'simplemeta_save_postmeta' ) );
		}

		public function simplemeta_load_textdomain() {
			load_plugin_textdomain( 'simple-metabox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		function simplemeta_add_metabox() {
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
			$is_favorite     = get_post_meta( $post->ID, 'simplemeta_is_favorite', true );
			print_r($is_favorite);
			$checked = ( $is_favorite == 1 ) ? 'checked' : '';
			print_r($checked);
			$country_label        = __( 'Country', 'simple-metabox' );
			$city_label        = __( 'City', 'simple-metabox' );
			$is_favorite_label = __( 'Is Favorite?', 'simple-metabox' );
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
<p>
<label for="simplemeta_is_favorite">{$is_favorite_label}</label>
<input type="checkbox" name="simplemeta_is_favorite" id="simplemeta_is_favorite" value="1" {$checked}>
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
			$is_favorite = isset( $_POST['simplemeta_is_favorite'] ) ? $_POST['simplemeta_is_favorite'] : 0 ;

			// Sanitizing Input Values
			$country = sanitize_text_field($country);
			$city   = sanitize_text_field($city);

			// Checking if meta data exists
			update_post_meta( $post_id, 'simplemeta_country', $country );
			update_post_meta( $post_id, 'simplemeta_city', $city );
			update_post_meta( $post_id, 'simplemeta_is_favorite', $is_favorite );
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