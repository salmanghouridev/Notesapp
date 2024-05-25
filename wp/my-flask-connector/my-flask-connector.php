<?php
/*
Plugin Name: AI WP Product Recommendation
Description: Connects WordPress to a Flask ML model and recommends products.
Version: 1.2
Author: hashplugin
*/

// Function to check connection to Flask app
function check_flask_connection() {
    $url = 'http://127.0.0.1:5000/api/status';
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return isset($data['status']) && $data['status'] === 'connected';
}

$user_ip = get_real_ip();
// Function to display connection status
function display_connection_status() {
    if (check_flask_connection()) {
        echo '<div style="background: #28a745; color: #fff; padding: 10px; text-align: center;">AI Recommendation system Active</div>';
    } else {
        echo '<div style="background: #dc3545; color: #fff; padding: 10px; text-align: center;">AI Recommendation system Deactive</div>';
    }
}

// Function to add menu item in the WordPress dashboard
function ai_recommendation_menu() {
    add_menu_page(
        'AI Recom System',
        'AI Recom System',
        'manage_options',
        'ai-recommendation-settings',
        'ai_recommendation_settings_page'
    );
}
// Hook into WordPress to display user IP address at the top of the page
add_action('wp_body_open', function() use ($user_ip) {
    if (!empty($user_ip)) {
        echo '<div style="background: #000; color: #fff; padding: 10px; text-align: center;">Your IP Address: ' . $user_ip . '</div>';
    }
});

// Callback function to display settings page
function ai_recommendation_settings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_ips';
    $user_ips = $wpdb->get_results("SELECT * FROM $table_name");

    ?>
    <div style="background: #000; color: #fff; padding: 10px; text-align: center;">
    Your IP Address: <?php echo $user_ip; ?>
</div>
    <div class="wrap">
        <h2>AI Recommendation System Settings</h2>
        <p>Configure settings for the AI recommendation system.</p>
        <h3>User Activity Data</h3>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>IP Address</th>
                    <th>Time Saved</th>
                    <th>Pages Visited</th>
                    <th>User Agents</th>
                    <th>Referrers</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_ips as $user_ip) : ?>
                    <tr>
                        <td><?php echo $user_ip->id; ?></td>
                        <td><?php echo $user_ip->ip_address; ?></td>
                        <td><?php echo $user_ip->time_saved; ?></td>
                        <td><?php echo isset($user_ip->pages_visited) ? implode(', ', json_decode($user_ip->pages_visited)) : ''; ?></td>
                        <td><?php echo isset($user_ip->user_agents) ? implode(', ', json_decode($user_ip->user_agents)) : ''; ?></td>
                        <td><?php echo isset($user_ip->referrers) ? implode(', ', json_decode($user_ip->referrers)) : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Function to create database table on plugin activation
function ai_recommendation_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_ips';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ip_address varchar(100) NOT NULL,
        time_saved datetime NOT NULL,
        pages_visited text NOT NULL,
        user_agents text NOT NULL,
        referrers text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Function to save user IP address and activity data
function ai_recommendation_save_user_ip() {
    if (!is_woocommerce_page()) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'user_ips';
    $ip_address = get_real_ip(); // Get real IP address
    error_log("Captured IP: " . $ip_address); // Log the captured IP address for debugging
    $time_saved = current_time('mysql');
    $page_visited = esc_url_raw($_SERVER['REQUEST_URI']);
    $user_agent = esc_html($_SERVER['HTTP_USER_AGENT']);
    $referrer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '';

    // Check if the IP address already exists in the database
    $existing_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ip_address = %s", $ip_address));
    error_log("Existing record: " . print_r($existing_record, true)); // Log the existing record for debugging

    if ($existing_record) {
        // If the record exists, update it
        $pages_visited = isset($existing_record->pages_visited) ? json_decode($existing_record->pages_visited) : [];
        $user_agents = isset($existing_record->user_agents) ? json_decode($existing_record->user_agents) : [];
        $referrers = isset($existing_record->referrers) ? json_decode($existing_record->referrers) : [];

        // Append new data to arrays
        $pages_visited[] = $page_visited;
        $user_agents[] = $user_agent;
        if ($referrer) {
            $referrers[] = $referrer;
        }

        // Update the database record
        $wpdb->update(
            $table_name,
            array(
                'time_saved' => $time_saved,
                'pages_visited' => json_encode(array_unique($pages_visited)),
                'user_agents' => json_encode(array_unique($user_agents)),
                'referrers' => json_encode(array_unique($referrers))
            ),
            array('ip_address' => $ip_address)
        );
    } else {
        // If the record does not exist, insert a new one
        $wpdb->insert(
            $table_name,
            array(
                'ip_address' => $ip_address,
                'time_saved' => $time_saved,
                'pages_visited' => json_encode(array($page_visited)),
                'user_agents' => json_encode(array($user_agent)),
                'referrers' => json_encode(array($referrer))
            )
        );
    }
}

// Function to check if the current page is a WooCommerce page
function is_woocommerce_page() {
    if (function_exists('is_woocommerce') && (is_product() || is_product_category() || is_shop())) {
        return true;
    }
    return false;
}

// Function to get real IP address
function get_real_ip() {
       // If user is behind a proxy, get the forwarded IP address
       if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
// Function to save user IP address and activity data upon cookie acceptance
function ai_recommendation_save_user_ip_on_accept() {
    // Check if the consent cookie is set
    if (isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'accepted') {
        // Proceed to capture user IP and related data
        ai_recommendation_save_user_ip();
    }
}

// Hook into WordPress to save user IP address and activity data upon cookie acceptance
add_action('wp', 'ai_recommendation_save_user_ip_on_accept');

// Function to add cookie consent banner
function add_cookie_consent_banner() {
    ?>
    <div id="cookie-consent-banner" style="position: fixed; bottom: 0; left: 0; width: 100%; background: #fff; padding: 10px; text-align: center; box-shadow: 0 -2px 5px rgba(0,0,0,0.1);">
        <p>This website uses cookies to ensure you get the best experience. <a href="#" onclick="acceptCookies()">Accept</a></p>
    </div>
    <script>
        function acceptCookies() {
            document.getElementById('cookie-consent-banner').style.display = 'none';
            document.cookie = "cookie_consent=accepted; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=/";
        }
    </script>
    <?php
}

// Hook into WordPress to add cookie consent banner
add_action('wp_footer', 'add_cookie_consent_banner');

// Function to drop the database table on plugin deactivation
function ai_recommendation_drop_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_ips';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

// Hook into WordPress to create menu item
add_action('admin_menu', 'ai_recommendation_menu');

// Hook into plugin activation to create database table
register_activation_hook(__FILE__, 'ai_recommendation_create_table');

// Hook into plugin deactivation to drop database table
register_deactivation_hook(__FILE__, 'ai_recommendation_drop_table');

// Hook into WordPress to save user IP address and activity data
add_action('wp', 'ai_recommendation_save_user_ip');

// Hook into WordPress to display status on home page
add_action('wp_head', 'display_connection_status');
?>
