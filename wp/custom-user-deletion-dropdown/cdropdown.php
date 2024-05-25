

<?php
/*
Plugin Name: Custom User Deletion Dropdown
Description: Customizes the user dropdown when deleting a user, adding search functionality.
Version: 1.1
Author: Your Name
*/

// Enqueue jQuery and custom JavaScript for the search functionality
function custom_enqueue_scripts($hook) {
    if ('users.php' != $hook) {
        return;
    }
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-user-deletion-dropdown', plugin_dir_url(__FILE__) . 'custom-user-deletion-dropdown.js', array('jquery'), null, true);
    wp_enqueue_style('custom-user-deletion-dropdown', plugin_dir_url(__FILE__) . 'custom-user-deletion-dropdown.css');
}
add_action('admin_enqueue_scripts', 'custom_enqueue_scripts');

// Enqueue Select2 CSS and JS
function custom_enqueue_select2() {
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'custom_enqueue_select2');

// Add AJAX action to fetch users
function custom_fetch_users() {
    $search = sanitize_text_field($_GET['q']);
    $args = array(
        'search' => '*' . esc_attr($search) . '*',
        'search_columns' => array('user_login', 'user_nicename', 'display_name'),
        'fields' => array('ID', 'display_name'),
        'number' => 20, // Adjust the number of results returned
        'exclude' => array(1) // Exclude admin user
    );
    $user_query = new WP_User_Query($args);
    $results = array();
    if (!empty($user_query->get_results())) {
        foreach ($user_query->get_results() as $user) {
            $results[] = array('id' => $user->ID, 'text' => $user->display_name);
        }
    }
    wp_send_json($results);
}
add_action('wp_ajax_custom_fetch_users', 'custom_fetch_users');
add_action('wp_ajax_nopriv_custom_fetch_users', 'custom_fetch_users');
?>

