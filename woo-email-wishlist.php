<?php
/*
Plugin Name: Woocommerce E-mail Wishlist
Description: Add button on WooCommerce products to send it as a wishlist to e-mail
Version: 1.0.0
Plugin URI: http://github.com/viankakrisna/
Author: Ade Viankakrisna Fadlil
Author URI: http://viankakrisna.com
*/
class Woo_Email_Wishlist {

	function __construct(){
		add_action( 'wp_footer' , array($this, 'print_modal') );
		add_action( 'wp_enqueue_scripts' , array($this, 'enqueue_scripts') );
		add_action( 'woocommerce_after_add_to_cart_button', array($this, 'print_button') );
		add_action( 'wp_ajax_wew_send_wishlist', array($this, 'ajax_callback'));
		add_action( 'wp_ajax_nopriv_wew_send_wishlist', array($this, 'ajax_callback'));
		add_action( 'admin_menu', array( $this, 'wew_create_menu'));
		add_action( 'init', array($this, 'post_type'));
	}

	function post_type(){
		register_post_type( 'wew-emails', array(
			'labels' => array(
				'name' => 'Woocommerce E-mail Wishlists',
			),
			'public' => true
		));
	}

	function print_button(){
		?>
		<div id="wew">
			<button id="wew-open-modal">
			  <?php echo $this->button_text(); ?>
			</button>
		</div>
		<?php
	}

	function wew_create_menu() {
		add_menu_page('WEW Settings', 'WEW Settings', 'administrator', __FILE__, array($this, 'wew_settings_page') );
		add_action( 'admin_init', array( $this, 'register_wew_settings' ) );
	}

	function settings(){
		return array(
            'email_subject',
            'button_text',
            'modal_title',
            'before_user_label',
            'user_label',
            'before_target_label',
            'target_label',
            'message_label',
            'thank_you_message',
            'email_template'
		);
	}

	function register_wew_settings() {
		foreach ($this->settings() as $setting){
			register_setting( 'wew-settings-group', 'wew_'.$setting );
		}
	}

	function wew_settings_page() {
		?>
		<div class="wrap">
			<h2>Woocommerce E-mail Wishlist</h2>

			<form method="post" action="options.php">
			    <?php settings_fields( 'wew-settings-group' ); ?>
			    <?php do_settings_sections( 'wew-settings-group' ); ?>
			    <hr>
			    <h2>
			    	<?php _e('Form Settings'); ?>
			    </h2>
			    <hr>
			    <table class="form-table">

			        <tr valign="top">
			        <th scope="row">Button Text</th>
			        <td><input type="text" name="wew_button_text" value="<?php echo esc_attr( $this->button_text() ); ?>" /></td>
			        </tr>

			        <tr valign="top">
			        <th scope="row">Modal Title</th>
			        <td><input type="text" name="wew_modal_title" value="<?php echo esc_attr( $this->modal_title() ); ?>" /></td>
			        </tr>

			        <tr valign="top">
			        <th scope="row">Before User Label</th>
			        <td><input type="text" name="wew_before_user_label" value="<?php echo esc_attr( $this->before_user_label() ); ?>" /></td>
			        </tr>

			        <tr valign="top">
			        <th scope="row">User Label</th>
			        <td><input type="text" name="wew_user_label" value="<?php echo esc_attr( $this->user_label() ); ?>" /></td>
			        </tr>

			        <tr valign="top">
			        <th scope="row">Before Target Label</th>
			        <td><input type="text" name="wew_before_target_label" value="<?php echo esc_attr( $this->before_target_label() ); ?>" /></td>
			        </tr>

			        <tr valign="top">
			        <th scope="row">Target Label</th>
			        <td><input type="text" name="wew_target_label" value="<?php echo esc_attr( $this->target_label() ); ?>" /></td>
			        </tr>

			        <tr valign="top">
			        <th scope="row">Message Label</th>
			        <td><input type="text" name="wew_message_label" value="<?php echo esc_attr( $this->message_label() ); ?>" /></td>
			        </tr>

			        <tr valign="top">
			        <th scope="row">Thank You Message</th>
			        <td><?php wp_editor( $this->thank_you_message() , 'wew_thank_you_message', array('tinymce'=>false)); ?></td>
			        </tr>
			    </table>
			    <hr>
			    <h2>
			    	<?php _e('E-mail Settings'); ?>
			    </h2>
			    <hr>
			    <table class="form-table">

		        	<tr valign="top">
			        <th scope="row">E-mail Subject</th>
			        <td><input type="text" name="wew_email_subject" value="<?php echo esc_attr( $this->subject() ); ?>" /></td>
			        </tr>

			        <tr valign="top">
			        <th scope="row">E-mail Template</th>
			        <td>
			        	<?php wp_editor( $this->email_template() , 'wew_email_template', array('tinymce'=>false)); ?>
			        	<p>
			        		<?php _e('Available parameters:') ?>
			        	</p>
			        	<p>
			        		[user_email]<br>[target_email]<br>[product_image_link]<br>[proceed_link]<br>[message]<br>[email_link]
			        	</p>
			        </td>
			        </tr>
			    </table>

			    <?php submit_button(); ?>

			</form>
		</div>
		<?php
	}

	function print_modal(){
		?>
		<script type="text/template" id="wew-modal">
			<h1>
				<?php echo $this->modal_title(); ?>
			</h1>
			<form id="wew-form">
				<p>
					<?php echo $this->before_user_label(); ?>
				</p>
				<label>
					<?php echo $this->user_label(); ?>
				</label>
					<input id="wew-user" type="email" required>
				<p>
					<?php echo $this->before_target_label(); ?>
				</p>
				<label>
					<?php echo $this->target_label(); ?>
				</label>
					<input id="wew-target" type="email" required>
				<label>
					<?php echo $this->message_label(); ?>
				</label>
				<textarea id="wew-message" rows="5"></textarea>
				<input id="wew-id" type="hidden" value="<?php echo get_the_id(); ?>">
				<input id="wew-nonce" type="hidden" value="<?php echo wp_create_nonce('wew-nonce'); ?>">
				<input id="wew-submit" type="submit">
			</form>
		</script>
		<?php
	}


	function enqueue_scripts(){
		wp_enqueue_script('jquery-validation-plugin', '//cdn.jsdelivr.net/jquery.validation/1.14.0/jquery.validate.min.js', array('jquery'));
		wp_enqueue_script('wew-modal', plugins_url('/assets/js/jsmodal.js' , __FILE__), array('jquery'));
		wp_enqueue_script('wew-js', plugins_url('/assets/js/wew.js' , __FILE__), array('jquery'));
		wp_enqueue_style('wew-modal-css', plugins_url('/assets/css/jsmodal-dark.css' , __FILE__));
		wp_enqueue_style('wew-whirl-css', plugins_url('/assets/css/whirl.min.css' , __FILE__));
		wp_enqueue_style('wew-css', plugins_url('/assets/css/wew.css' , __FILE__));
		wp_localize_script( 'wew-js', 'wew_ajax', array(
			'url' => admin_url('admin-ajax.php')
		));
	}

	function ajax_callback(){
		check_ajax_referer('wew-nonce', 'nonce');
		$user = sanitize_text_field( $_POST['user'] );
		$target = sanitize_text_field( $_POST['target'] );
		$message = sanitize_text_field( $_POST['message'] );
		$id = absint($_POST['id']);

		if (is_email($user) && is_email($target)) {
			$response = array(
				'message' => $this->thank_you_message()
			);
			$post_id = wp_insert_post( array(
				'post_type'  => 'wew-emails',
				'post_title' => $user .' to '. $target . ' ' . time(),
				'post_content' => 'none',
				'post_status' => 'publish'
			) );
			$parameter = array(
				'id' => $id,
				'user_email' => $user,
				'target_email' => $target,
				'message' => $message,
				'post_id' => $post_id
			);
			$subject = $this->generate_template($parameter, $this->subject());
			$email_template = $this->generate_template($parameter, $this->email_template());
			wp_update_post(array(
				'ID' => $post_id,
				'post_content' => $email_template
			));
			update_post_meta( $post_id, 'wew_parameter', $parameter );
			$headers = array('Content-Type: text/html; charset=UTF-8');
			do_action('wew_before_email_sent', $email_template);
			$mail = wp_mail( $target, $subject, $email_template, $headers );
			do_action('wew_after_email_sent', $mail);
		}

		wp_send_json( $response );

	}

	function generate_template($parameter, $content){
        $replace_tag = array( 'user_email', 'target_email', 'product_image_link', 'proceed_link', 'message', 'email_link' );

        foreach ($replace_tag as $tag) {
            $func           = 'get_' . $tag;
            $values         = $this->$func($parameter);
            $tag            = '['.$tag.']';
            $content = str_replace($tag, $values, $content );
        }
        return $content;
	}

	function get_email_link($parameter){
		$post_id = $parameter['post_id'];
		return get_permalink( $post_id );
	}

	function get_proceed_link($parameter){
		$id = $parameter['id'];
		$wc = new WC_Cart;
		return add_query_arg(array('add-to-cart' => $id), $wc->get_checkout_url());
	}

	function get_product_image_link($parameter){
		$id = $parameter['id'];
		return wp_get_attachment_url(get_post_thumbnail_id( $id ));
	}

	function get_user_email($parameter){
		return $parameter['user_email'];
	}

	function get_target_email($parameter){
		return $parameter['target_email'];
	}

	function get_message($parameter){
		return $parameter['message'];
	}

	function email_template(){
		return get_option('wew_email_template', '[user_email]<br>[target_email]<br>[product_image_link]<br>[proceed_link]<br>[message]<br>[email_link]');
	}

	function button_text(){
		return get_option('wew_button_text', 'Summer Dreams');
	}

	function modal_title(){
		return get_option('wew_modal_title', 'Summer Dreams');
	}

	function before_user_label(){
		return get_option('wew_before_user_label', 'Summer Dreamer');
	}

	function user_label(){
		return get_option('wew_user_label', 'Your E-mail*');
	}

	function before_target_label(){
		return get_option('wew_before_target_label', 'Special ones');
	}

	function target_label(){
		return get_option('wew_target_label', 'His/her E-mail*');
	}

	function message_label(){
		return get_option('wew_message_label', 'Message');
	}

	function thank_you_message(){
		return get_option('wew_thank_you_message', 'Thank You!');
	}

	function subject(){
		return get_option('wew_email_subject', 'New Wishlist!');
	}

}
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	new Woo_Email_Wishlist();
} else {
	add_action('admin_notices', function(){
		$class = "error";
		$message = "WooCommerce E-mail Wishlist needs WooCommerce to work!";
		echo"<div class=\"$class\"> <p>$message</p></div>";
	});
}
