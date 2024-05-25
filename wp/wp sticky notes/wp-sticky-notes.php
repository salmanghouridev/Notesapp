<?php
/*
Plugin Name: WP Sticky Notes
Description: A simple sticky notes plugin for WordPress.
Version: 1.3
Author: hashplugin
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Activation hook to create the database table.
register_activation_hook(__FILE__, 'wp_sticky_notes_create_table');
function wp_sticky_notes_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sticky_notes';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        heading text NOT NULL,
        description longtext NOT NULL,
        color varchar(7) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Enqueue scripts and styles.
add_action('admin_enqueue_scripts', 'wp_sticky_notes_enqueue_scripts');
function wp_sticky_notes_enqueue_scripts($hook_suffix) {
    if ($hook_suffix != 'toplevel_page_wp-sticky-notes') {
        return;
    }
    wp_enqueue_script('wp-sticky-notes', plugins_url('sticky-notes.js', __FILE__), array('jquery', 'wp-color-picker'), '1.3', true);
    wp_localize_script('wp-sticky-notes', 'wpStickyNotes', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style('wp-sticky-notes', plugins_url('sticky-notes.css', __FILE__));
}

// Admin menu.
add_action('admin_menu', 'wp_sticky_notes_menu');
function wp_sticky_notes_menu() {
    add_menu_page('Sticky Notes', 'Sticky Notes', 'manage_options', 'wp-sticky-notes', 'wp_sticky_notes_page');
    add_submenu_page('wp-sticky-notes', 'Notes Link', 'Notes Link', 'manage_options', 'wp-sticky-notes-links', 'wp_sticky_notes_links_page');
}

// Create a new function for the "Notes Link" page
function wp_sticky_notes_links_page() {
    ?>
    <div class="wrap">
        <h1>Notes Link</h1>
        <div id="notes-links-container"></div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $.post(wpStickyNotes.ajax_url, { action: 'get_notes' }, function(response) {
            if (response.success) {
                $('#notes-links-container').empty();
                response.data.forEach(function(note) {
                    var noteHeading = $('<div>').text(note.heading).html();
                    var shareUrl = window.location.origin + '/?note_id=' + note.id + '&note_heading=' + encodeURIComponent(note.heading.toLowerCase().replace(/ /g, '-'));
                    $('#notes-links-container').append('<p><strong>' + noteHeading + ':</strong> <a href="' + shareUrl + '" target="_blank">' + shareUrl + '</a></p>');
                });
            }
        });
    });
    </script>
    <?php
}
// Admin page content.
function wp_sticky_notes_page() {
    ?>
    <div class="wrap">
        <h1>Sticky Notes</h1>
        <div id="notes-container"></div>
        <h2>Add New Note</h2>
        <input type="text" id="new-note-heading" placeholder="Heading" /><br />
        <textarea id="new-note-description" placeholder="Description" style="width: 100%; height: 200px;"></textarea><br />
        <input type="text" id="new-note-color" class="color-picker" value="#000000" /><br />
        <button id="add-note">Add Note</button>
    </div>

    <div id="edit-note-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Note</h2>
            <input type="text" id="edit-note-heading" placeholder="Heading" /><br />
            <textarea id="edit-note-description" placeholder="Description" style="width: 100%; height: 200px;"></textarea><br />
            <input type="text" id="edit-note-color" class="color-picker" /><br />
            <input type="hidden" id="edit-note-id" />
            <button id="save-note">Save Note</button>
        </div>
    </div>
    <?php
}

// AJAX handler for adding a note.
add_action('wp_ajax_add_note', 'wp_sticky_notes_add_note');
function wp_sticky_notes_add_note() {
    global $wpdb;
    $heading = sanitize_text_field($_POST['heading']);
    $description = wp_kses_post($_POST['description']);
    $color = sanitize_hex_color($_POST['color']);
    $wpdb->insert($wpdb->prefix . 'sticky_notes', array('heading' => $heading, 'description' => $description, 'color' => $color));
    wp_send_json_success(array('id' => $wpdb->insert_id, 'heading' => $heading, 'description' => $description, 'color' => $color));
}

// AJAX handler for deleting a note.
add_action('wp_ajax_delete_note', 'wp_sticky_notes_delete_note');
function wp_sticky_notes_delete_note() {
    global $wpdb;
    $id = intval($_POST['id']);
    $wpdb->delete($wpdb->prefix . 'sticky_notes', array('id' => $id));
    wp_send_json_success();
}

// AJAX handler for fetching notes.
add_action('wp_ajax_get_notes', 'wp_sticky_notes_get_notes');
function wp_sticky_notes_get_notes() {
    global $wpdb;
    $notes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sticky_notes ORDER BY created_at DESC");
    wp_send_json_success($notes);
}

// AJAX handler for editing a note.
add_action('wp_ajax_edit_note', 'wp_sticky_notes_edit_note');
function wp_sticky_notes_edit_note() {
    global $wpdb;
    $id = intval($_POST['id']);
    $heading = sanitize_text_field($_POST['heading']);
    $description = wp_kses_post($_POST['description']);
    $color = sanitize_hex_color($_POST['color']);
    $wpdb->update($wpdb->prefix . 'sticky_notes', array('heading' => $heading, 'description' => $description, 'color' => $color), array('id' => $id));
    wp_send_json_success(array('id' => $id, 'heading' => $heading, 'description' => $description, 'color' => $color));
}

// Shortcode to display a note by ID.
function wp_sticky_notes_display_note_shortcode($atts) {
    global $wpdb;
    $atts = shortcode_atts(array('id' => 0), $atts);
    $note_id = intval($atts['id']);
    $note = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sticky_notes WHERE id = %d", $note_id));

    if ($note) {
        return '<div class="note" style="background-color:' . esc_attr($note->color) . '">' .
               '<h3 class="note-heading">' . esc_html($note->heading) . '</h3>' .
               '<div class="note-description">' . wp_kses_post($note->description) . '</div>' .
               '</div>';
    } else {
        return '<p>Note not found.</p>';
    }
}
add_shortcode('display_note', 'wp_sticky_notes_display_note_shortcode');

// Handle note display on frontend.
function wp_sticky_notes_template_redirect() {
    if (isset($_GET['note_id'])) {
        $note_id = intval($_GET['note_id']);
        $note_heading = sanitize_title_for_query($_GET['note_heading']);
        
        // Get the full path to the template file
        $template_path = plugin_dir_path(__FILE__) . 'template-note.php';
        
        if (file_exists($template_path)) {
            include($template_path);
        } else {
            // Handle the error if the template file doesn't exist
            echo 'Template file not found.';
        }
        exit;
    }
}
add_action('template_redirect', 'wp_sticky_notes_template_redirect');
add_action('rest_api_init', function () {
    register_rest_route('wp-sticky-notes/v1', '/notes', array(
        'methods' => 'GET',
        'callback' => 'wp_sticky_notes_get_notes_api',
    ));
    register_rest_route('wp-sticky-notes/v1', '/notes', array(
        'methods' => 'POST',
        'callback' => 'wp_sticky_notes_add_note_api',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ));
    register_rest_route('wp-sticky-notes/v1', '/notes/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'wp_sticky_notes_delete_note_api',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ));
    register_rest_route('wp-sticky-notes/v1', '/notes/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'wp_sticky_notes_edit_note_api',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ));
});
// Get notes callback function for API
function wp_sticky_notes_get_notes_api() {
    global $wpdb;
    $notes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sticky_notes ORDER BY created_at DESC");
    return new WP_REST_Response($notes, 200);
}

// Add note callback function for API
function wp_sticky_notes_add_note_api(WP_REST_Request $request) {
    global $wpdb;
    $params = $request->get_json_params();
    $heading = sanitize_text_field($params['heading']);
    $description = wp_kses_post($params['description']);
    $color = sanitize_hex_color($params['color']);
    
    $wpdb->insert($wpdb->prefix . 'sticky_notes', array('heading' => $heading, 'description' => $description, 'color' => $color));
    return new WP_REST_Response(array('id' => $wpdb->insert_id, 'heading' => $heading, 'description' => $description, 'color' => $color), 201);
}

// Delete note callback function for API
function wp_sticky_notes_delete_note_api($data) {
    global $wpdb;
    $id = intval($data['id']);
    $wpdb->delete($wpdb->prefix . 'sticky_notes', array('id' => $id));
    return new WP_REST_Response(null, 204);
}

// Edit note callback function for API
function wp_sticky_notes_edit_note_api(WP_REST_Request $request) {
    global $wpdb;
    $id = intval($request['id']);
    $params = $request->get_json_params();
    $heading = sanitize_text_field($params['heading']);
    $description = wp_kses_post($params['description']);
    $color = sanitize_hex_color($params['color']);
    
    $wpdb->update($wpdb->prefix . 'sticky_notes', array('heading' => $heading, 'description' => $description, 'color' => $color), array('id' => $id));
    return new WP_REST_Response(array('id' => $id, 'heading' => $heading, 'description' => $description, 'color' => $color), 200);
}


?>
