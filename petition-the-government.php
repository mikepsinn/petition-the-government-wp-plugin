<?php
/**
 * Plugin Name: Petition the Government
 * Plugin URI:  https://fdai.earth/petition-the-government
 * Description: Allows users to sign a petition, creates a new subscriber user, and stores address information.
 * Version:     1.0
 * Author:      Mike Sinn
 * Author URI:  https://fdai.earth
 */

function petition_the_government_register_block() {
    wp_register_script(
        'petition-the-government-editor',
        plugins_url('build/index.js', __FILE__),
        ['wp-blocks', 'wp-element', 'wp-editor'],
        filemtime(plugin_dir_path(__FILE__) . 'build/index.js')
    );

    register_block_type('petition-the-government/petition-form', [
        'editor_script' => 'petition-the-government-editor',
        'render_callback' => 'petition_the_government_render_block',
    ]);
}

function petition_the_government_render_block() {

    $countryOptions = require plugin_dir_path(__FILE__) . 'countries.php';
    $countryOptionsHtml = '';
    foreach ($countryOptions as $code => $name) {
		$countryOptionsHtml .= "<option value=\"$code\">$name</option>";
	}

$form_html = <<<HTML
<form id="petition-form" 
class="petition-form"
action="index.php?rest_route=/petition-the-government/v1/submit"
 method="POST">
    <div>
        <label for="petition-name">Name:</label>
        <input type="text" id="petition-name" name="name" required>
    </div>
    
    <div>
        <label for="petition-email">Email:</label>
        <input type="email" id="petition-email" name="email" required>
    </div>
    
    <div>
        <label for="petition-postal">Postal Code:</label>
        <input type="text" id="petition-postal" name="postal">
    </div>
    
    <div>
        <label for="petition-street">Street Address (Optional):</label>
        <input type="text" id="petition-street" name="street">
    </div>
    
    <div>
        <label for="petition-organization">Organization (Optional):</label>
        <input type="text" id="petition-organization" name="organization">
    </div>
    
    <div>
        <label for="petition-phone">Phone Number (Optional):</label>
        <input type="tel" id="petition-phone" name="phone">
    </div>
    
    <div>
        <label for="petition-country">Country:</label>
        <select id="petition-country" name="country" onchange="toggleStateField()">
$countryOptionsHtml
        </select>
    </div>
    
    <div id="state-field" style="display: none;">
        <label for="petition-state">State:</label>
        <select id="petition-state" name="state">
            <option value="AL">Alabama</option>
            <option value="AK">Alaska</option>
            <!-- Add more states as needed -->
        </select>
    </div>
    
    <button type="submit">
        Sign Petition
    </button>
</form>
<script>
    function toggleStateField() {
        var country = document.getElementById('petition-country').value;
        var stateField = document.getElementById('state-field');
        stateField.style.display = country === 'USA' ? 'block' : 'none';
    }
    // Call toggleStateField on page load in case USA is preselected
    document.addEventListener('DOMContentLoaded', toggleStateField);
</script>
HTML;

    return $form_html;
}


add_action('init', 'petition_the_government_register_block');

function petition_the_government_enqueue_assets() {
    wp_enqueue_style('petition-the-government-style', plugins_url('/petition-style.css', __FILE__));
}

// Enqueue CSS for both frontend and backend.
add_action('enqueue_block_assets', 'petition_the_government_enqueue_assets');


function petition_the_government_handle_submit($request)
{
    $name = sanitize_text_field($request['name']);
    $email = sanitize_email($request['email']);
    $street = sanitize_text_field($request['street']); // Optional street address
    $organization = sanitize_text_field($request['organization']);
    $phone = sanitize_text_field($request['phone']);
    $country = sanitize_text_field($request['country']);
    $state = sanitize_text_field($request['state']);

    $user_id = wp_insert_user([
        'user_login' => $email,
        'user_email' => $email,
        'display_name' => $name,
        'user_pass' => wp_generate_password(),
        'role' => 'subscriber'
    ]);

    if (is_wp_error($user_id)) {
	    error_log($user_id->get_error_message());
	    wp_redirect(home_url('/petition-thank-you/'));
	    exit;
        //return new WP_Error('user_create_failed', 'Failed to create user.', ['status' => 500]);
    }

    // Store the additional information in wp_usermeta
    if (!empty($street)) {
        add_user_meta($user_id, 'street', $street, true);
    }
    if (!empty($organization)) {
        add_user_meta($user_id, 'organization', $organization, true);
    }
    if (!empty($phone)) {
        add_user_meta($user_id, 'phone', $phone, true);
    }
	if(!empty($state)) {
		add_user_meta($user_id, 'state', $state, true);
	}
	if(!empty($country)) {
		add_user_meta($user_id, 'country', $country, true);
	}


	$user = get_user_by('email', $email);
	if ($user) {
		wp_set_current_user($user->ID, $user->user_login);
		wp_set_auth_cookie($user->ID);
		do_action('wp_login', $user->user_login, $user);
		// Redirect to the thank you page
		wp_redirect(home_url('/petition-thank-you/'));
		exit;
	}


	return new WP_REST_Response('Petition signed successfully. User created and data stored.', 200);
}

function petition_the_government_register_rest_route()
{
    register_rest_route('petition-the-government/v1', '/submit', [
        'methods' => 'POST',
        'callback' => 'petition_the_government_handle_submit',
        'permission_callback' => '__return_true',
    ]);
}

add_action('rest_api_init', 'petition_the_government_register_rest_route');

function petition_the_government_create_thank_you_page() {
	$the_page_title = 'Thank You for Signing the Petition!';
	$the_page_content = 'Thank you for signing the petition. Share it with your friends on social media.';
	$the_page = get_page_by_title($the_page_title);

	if (!$the_page) {
		// Create post object
		$_p = array();
		$_p['post_title'] = $the_page_title;
		$_p['post_content'] = $the_page_content;
		$_p['post_status'] = 'publish';
		$_p['post_type'] = 'page';
		$_p['comment_status'] = 'closed';
		$_p['ping_status'] = 'closed';
		$_p['post_category'] = array(1); // the default 'Uncategorized'
		$_p['post_name'] = 'petition-thank-you';

		// Insert the post into the database
		$the_page_id = wp_insert_post($_p);
	}
}
register_activation_hook(__FILE__, 'petition_the_government_create_thank_you_page');
