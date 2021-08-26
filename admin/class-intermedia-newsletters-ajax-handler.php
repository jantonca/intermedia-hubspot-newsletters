<?php
/**
 * The Ajax hadler
 *
 * @link       https://www.intermedia.com.au/
 * @since      1.0.0
 *
 * @package    Intermedia_Hubspot_Newsletters
 * @subpackage Intermedia_Hubspot_Newsletters/admin
 */

/**
 * Class for managing Ajax
 *
 */
class AjaxHandler {

    /**
     * Action hook used by the AJAX class.
     *
     * @var string
     */
    const ACTION = 'intermedia_newsletters_ajax';

    /**
     * Action argument used by the nonce validating the AJAX request.
     *
     * @var string
     */
    const NONCE = 'intermedia-newsletter-ajax';

    /**
     * Register the AJAX handler class with all the appropriate WordPress hooks.
     */
    public static function register()
    {
        $handler = new self();

        add_action('wp_ajax_' . self::ACTION, array($handler, 'handle'));
        add_action('wp_ajax_nopriv_' . self::ACTION, array($handler, 'handle'));
        add_action('wp_loaded', array($handler, 'register_script'));
    }

    /**
     * Handles the AJAX request for my plugin.
     */
    public function handle()
    {
        // Make sure we are getting a valid AJAX request
        check_ajax_referer(self::NONCE);

        // Stand back! I'm about to try... SCIENCE!

        die();
    }

    /**
     * Register our AJAX JavaScript.
     */
    public function register_script()
    {
        wp_register_script('wp_ajax', plugins_url('/admin/js/ajax.js', __FILE__));
        wp_localize_script('wp_ajax', 'wp_ajax_data', $this->get_ajax_data());
        wp_enqueue_script('wp_ajax');
    }

    /**
     * Get the AJAX data that WordPress needs to output.
     *
     * @return array
     */
    private function get_ajax_data()
    {
        return array(
            'action' => self::ACTION,
            'nonce' => wp_create_nonce(AjaxHandler::NONCE)
        );
    }

    /**
     * Get the comment text sent by the AJAX request.
     *
     * @return string
     */
    private function get_comment()
    {
        $comment = '';

        if (isset($_POST['comment'])) {
            $comment = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);
        }

        return $comment;
    }

    /**
     * Get the post ID sent by the AJAX request.
     *
     * @return int
     */
    private function get_post_id()
    {
        $post_id = 0;

        if (isset($_POST['post_id'])) {
            $post_id = absint(filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT));
        }

        return $post_id;
    }

    /**
     * Sends a JSON response with the details of the given error.
     *
     * @param WP_Error $error
     */
    private function send_error(WP_Error $error)
    {
        wp_send_json(array(
            'code' => $error->get_error_code(),
            'message' => $error->get_error_message()
        ));
    }

    private function delete_all_entities_metadata( $current_user ) {

		global $current_user;
		wp_get_current_user();
		$current_user->user_login;
		global $wpdb; // this is how you get access to the database
		$result = $_POST['result'];

        $entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');
        $cpt_included = $entities_options['cpt_included'];

		$args = array(
			'posts_per_page' => -1, // this gets all posts, you may only want to get a few at a time
			'post_type' => $cpt_included,
			'meta_key' => 'entities_select_positions',
			'fields' => 'ids'
		);
		$post_with_positions = get_posts( $args );
		if ( count( $post_with_positions ) > 0 ) {
			$s = (count( $post_with_positions ) > 1 ? 's' : '');
			$message = count( $post_with_positions ).' post'.$s.' deleted position'.$s.': ';
			foreach ($post_with_positions as $post_id) {
				delete_post_meta($post_id, 'entities_select_positions');
				$message .= ' '.$post_id.',';
			}
		} else {
			$message = 'Hi '.$current_user->user_login.', there are not positions asigned to any post..';
		}
		$result = $message;
		echo $result;
		exit(); // this is required to return a proper result & exit is faster than die();
        
	}

}