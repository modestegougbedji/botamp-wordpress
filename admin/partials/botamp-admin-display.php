<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       support@botamp.com
 * @since      1.1.0
 *
 * @package    Botamp
 * @subpackage Botamp/admin/partials
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<?php
	if ( isset( $_POST['action'] ) && 'import_all_posts' === $_POST['action'] ) :
		$this->import_all_posts();
		else :
	?>
	<h2> <?php echo esc_html( get_admin_page_title() ); ?> </h2>
	<form action="options.php" method="post">
	<?php
		$woocommerce_admin = new Botamp_Woocommerce_Admin( $plugin_name, $version );
		settings_fields( $this->plugin_name );
		do_settings_sections( $this->plugin_name );
		?>
		<h2>General</h2>
		<?php $this->general_cb(); ?>
		<table class="form-table">
	        <tr valign="top">
	        	<th scope="row"> <label for="<?php echo $this->option( 'api_key' ) ?>">API key</label> </th>
	        	<td><?php $this->api_key_cb(); ?></td>
	        </tr>
	        <tr valign="top" class="botamp-content-post-type">
	        	<th scope="row"> <label for="<?php echo $this->option( 'post_type' ); ?>">Post type</label> </th>
	        	<td><?php $this->post_type_cb(); ?></td>
	        </tr>
	        <tr valign="top">
	        	<th scope="row"> <label for="<?php echo $this->option( 'order_notifications' ); ?>">Order notifications</label> </th>
	        	<td><?php $woocommerce_admin->order_notifications_cb(); ?></td>
	        </tr>
	    </table>
	    <h2>Content Mapping</h2>
		<?php
			$this->entity_cb();
			$this->entity_fields();
		?>
	    <?php submit_button(); ?>
	</form>
	<form action="" method="post">
		<input type="hidden" name="action" value="import_all_posts">
		<?php submit_button( __( 'Import all posts' ) ); ?>
	</form>
	<?php endif; ?>
</div>
