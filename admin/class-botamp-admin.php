<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'traits/option.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'traits/botamp-client.php';

class Botamp_Admin {

	use Option;
	use Botamp_Client;

	private $plugin_name;
	private $version;
	private $fields;
	private $botamp;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		global $wpdb;
		$this->fields = [
		 'post_title',
		 'post_content',
		 'post_excerpt',
		 'post_thumbnail_url',
		 'post_permalink',
		];
		$post_metas = $wpdb->get_col( "select distinct meta_key from {$wpdb->prefix}postmeta
										where meta_key not like 'botamp_%'", 0 );
		$this->fields = array_merge( $this->fields, $post_metas );

		$this->botamp = $this->get_botamp();
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/botamp-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/botamp-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function import_all_posts() {
		include_once 'partials/botamp-admin-display-import.php';
	}

	public function ajax_import_post() {
		// @codingStandardsIgnoreStart
		@error_reporting( 0 ); // Don't break the JSON result
		// @codingStandardsIgnoreEnd

		header( 'Content-type: application/json' );

		$post_id = (int) $_REQUEST['post_id'];

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-botamp-public.php';
		$plugin_public = new Botamp_Public( $this->plugin_name, $this->version );
		if ( $plugin_public->create_or_update_entity( $post_id ) === true ) {
			die( json_encode( array( 'success' => sprintf( __( 'The post <i>%s</i> was successfully imported' ), get_the_title( $post_id ) ) ) ) );
		} else {
			die( json_encode( array( 'error' => sprintf( __( 'The post <i>%s</i> failed to import' ), get_the_title( $post_id ) ) ) ) );
		}
	}

	public function display_warning_message() {
		$api_key = $this->get_option( 'api_key' );
		if ( empty( $api_key ) ) {
			$html = '<div class="notice notice-warning is-dismissible"> <p>';
			$html .= sprintf( __( 'Please complete the Botamp plugin installation on the <a href="%s">settings page</a>.', 'botamp' ), admin_url( 'options-general.php?page=botamp' ) );
			$html .= '</p> </div>';
			set_transient( 'botamp_auth_status', 'unauthorized', HOUR_IN_SECONDS );
			echo $html;
		} else {
			$auth_status = get_transient( 'botamp_auth_status' );
			if ( false === $auth_status ) {
				try {
					$this->botamp->me->get();
					set_transient( 'botamp_auth_status', 'ok', HOUR_IN_SECONDS );
				} catch (Botamp\Exceptions\Unauthorized $e) {
					set_transient( 'botamp_auth_status', 'unauthorized', HOUR_IN_SECONDS );
				}

				$this->display_warning_message();
			} elseif ( 'unauthorized' === $auth_status ) {
				$html = '<div class="notice notice-warning is-dismissible"> <p>';
				$html .= sprintf( __( 'Authentication with the provided API key is not working.<br/>
Please provide a valid API key on the <a href="%s">settings page</a>.', 'botamp' ), admin_url( 'options-general.php?page=botamp' ) );
				$html .= '</p> </div>';
				echo $html;
			}
		}

	}

	public function add_options_page() {
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Botamp Application Settings', 'botamp' ),
			__( 'Botamp', 'botamp' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_options_page' )
		);
	}

	public function display_options_page() {
		include_once 'partials/botamp-admin-display.php';
	}

	public function register_settings() {
		register_setting( $this->plugin_name, $this->option( 'api_key' ) );
		register_setting( $this->plugin_name, $this->option( 'post_type' ) );
	}

	public function general_cb() {
		echo '<p>'
			. __( 'Visit <a href="https://app.botamp.com">your bot settings page on Botamp</a> to get your API key.', 'botamp' )
			. '</p>';
	}

	public function entity_cb() {
		echo '<p>'
			. __( 'Choose the post fields your bot will use to respond to your customers.', 'botamp' )
			. '</p>';
	}

	public function api_key_cb() {
		$api_key = $this->get_option( 'api_key' );
		echo '<input type="text" name="' . $this->option( 'api_key' ) . '" value="' . $api_key . '" class="regular-text"> ';
	}

	public function post_type_cb() {
		$html = '<select class = "botamp-post-type regular-list" >';
		foreach ( get_post_types( array("public" => true), 'objects' ) as $post_type ) {
			$html .= "<option value = '{$post_type->name}'> {$post_type->label} </option>";
		}
		$html .= '</select>';
		echo $html;
	}

	public function entity_fields() {
		$html = '<div class="botamp-content-mapping">';
		foreach ( get_post_types( array("public" => true), 'objects' ) as $post_type ) {
			$option_value = $this->get_option( 'post_type' )[$post_type->name];
	        $html .= '<table class="form-table" id="botamp-form-table-'.$post_type->name.'"> <tr valign="top">
	        	<th scope="row"><label for="'.$this->option( "post_type" ).'['.$post_type->name.'][description]">Description</label> </th>
	        	<td>
	        	<select name="'.$this->option( "post_type" ).'['.$post_type->name.'][description]" class = "regular-list">';
	        		foreach ( $this->fields as $field ) {
						if ( $option_value['description'] === $field ) {
							$html .= "<option value = '$field' selected='true'>"
							. $this->field_name( $field )
							. '</option>';
						} else {
							$html .= "<option value = '$field'>"
							. $this->field_name( $field )
							. '</option>';
						}
					}
			$html .= '</select>
				</td>
	        </tr>
	        <tr valign="top">
	        	<th scope="row"> <label for="'.$this->option( "post_type" ).'['.$post_type->name.'][image_url]">Image URL</label> </th>
	        	<td>
	        	<select name="'.$this->option( "post_type" ).'['.$post_type->name.'][image_url]" class = "regular-list">';
	        		foreach ( [ '', 'post_thumbnail_url' ] as $field ) {
						if ( $option_value['image_url'] === $field ) {
							$html .= "<option value = '$field' selected='true'>"
							. $this->field_name( $field )
							. '</option>';
						} else {
							$html .= "<option value = '$field'>"
							. $this->field_name( $field )
							. '</option>';
						}
					}
			$html .= '</select>
	        	</td>
	        </tr>
	        <tr valign="top" >
	        	<th scope="row"><h3>'.$post_type->label.'</h3></th>
	        	<td></td>
	        </tr>
	        <tr valign="top">
	        	<th scope="row"> <label for="' . $this->option( 'order_notifications' ) . '">Sync this post type</label> </th>
	        	<td>
	        	<input type="checkbox" name="'.$this->option( "post_type" ).'['.$post_type->name.'][valid]" value="enabled" ' .checked( 'enabled', $option_value['valid'], false ) . '/>
	        	</td>
	        </tr>
	        <tr valign="top">
	        	<th scope="row"> <label for="'.$this->option( "post_type" ).'['.$post_type->name.'][title]">Title</label> </th>
	        	<td>
	        	<select name="'.$this->option( "post_type" ).'['.$post_type->name.'][title]" class = "regular-list">';
	        		foreach ( $this->fields as $field ) {
						if ( $option_value['title'] === $field ) {
							$html .= "<option value = '$field' selected='true'>"
							. $this->field_name( $field )
							. '</option>';
						} else {
							$html .= "<option value = '$field'>"
							. $this->field_name( $field )
							. '</option>';
						}
					}
			$html .= '</select>
	        	</td>
	        </tr>
	        <tr valign="top">
	        	<th scope="row"> <label for="'.$this->option( "post_type" ).'['.$post_type->name.'][url]">URL</label> </th>
	        	<td>
	        	<select name="'.$this->option( "post_type" ).'['.$post_type->name.'][url]" class = "regular-list">';
	        		foreach ( $this->fields as $field ) {
						if ( $option_value['url'] === $field ) {
							$html .= "<option value = '$field' selected='true'>"
							. $this->field_name( $field )
							. '</option>';
						} else {
							$html .= "<option value = '$field'>"
							. $this->field_name( $field )
							. '</option>';
						}
					}
			$html .= '</select>
	        	</td>
	        </tr></table>';

		}
		$html .= '</div>';
		echo $html;
	}

	private function field_name( $field ) {
		switch ( $field ) {
			case 'post_title':
				return __( 'Post title', 'botamp' );
			case 'post_content':
				return __( 'Post content', 'botamp' );
			case 'post_excerpt':
				return __( 'Post excerpt', 'botamp' );
			case 'post_thumbnail_url':
				return __( 'Post thumbnail URL', 'botamp' );
			case 'post_permalink':
				return __( 'Post permalink', 'botamp' );
			default:
				return $field;
		}
	}
}
