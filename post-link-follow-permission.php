<?php
/**
 * Post Link Follow Permission
 *
 * @package       POSTLINKFO
 * @author        Jahidul islam Sabuz
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Post Link Follow Permission
 * Plugin URI:    https://imjol.com
 * Description:   This is some demo short description...
 * Version:       1.0.0
 * Author:        Jahidul islam Sabuz
 * Author URI:    https://grocoder.com
 * Text Domain:   post-link-follow-permission
 * Domain Path:   /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Register custom meta field for follow/nofollow
function register_follow_nofollow_meta() {
    register_post_meta('post', '_follow_nofollow', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => 'follow',
    ));
}
add_action('init', 'register_follow_nofollow_meta');

// Add custom column to post list
function nq_add_nofollow_column($columns) {
    $columns['follow'] = 'Follow/Nofollow';
    return $columns;
}
add_filter('manage_posts_columns', 'nq_add_nofollow_column');

// Show custom column value
function nq_show_nofollow_column($column_name, $post_id) {
    if ($column_name === 'follow') {
        $follow_nofollow = get_post_meta($post_id, '_follow_nofollow', true);
        echo $follow_nofollow === 'nofollow' ? 'Nofollow' : 'Follow';
    }
}
add_action('manage_posts_custom_column', 'nq_show_nofollow_column', 10, 2);

// Add the custom field to the Quick Edit interface
function add_quick_edit_follow_nofollow($column_name, $post_type) {
    if ($column_name == 'follow') {
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label class="inline-edit-group">
                    <span class="title">Follow/Nofollow</span>
                    <select name="follow_nofollow">
                        <option value="follow">Follow</option>
                        <option value="nofollow">Nofollow</option>
                    </select>
                </label>
            </div>
        </fieldset>
        <?php
    }
}
add_action('quick_edit_custom_box', 'add_quick_edit_follow_nofollow', 10, 2);

// Enqueue JavaScript for Quick Edit
function enqueue_quick_edit_follow_nofollow_script($hook) {
    if ($hook === 'edit.php') {
        wp_enqueue_script('quick-edit-follow-nofollow', plugin_dir_url(__FILE__) . 'js/quick-edit-follow-nofollow.js', array('jquery', 'inline-edit-post'), '', true);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_quick_edit_follow_nofollow_script');

// Save the custom field when Quick Edit is used
function save_quick_edit_follow_nofollow($post_id) {
    if (isset($_POST['follow_nofollow'])) {
        update_post_meta($post_id, '_follow_nofollow', sanitize_text_field($_POST['follow_nofollow']));
    }
}
add_action('save_post', 'save_quick_edit_follow_nofollow');

// Modify the Post Content Based on Follow/Nofollow Status
function add_nofollow_based_on_meta($content) {
    global $post;
    $follow_nofollow = get_post_meta($post->ID, '_follow_nofollow', true);

    if ($follow_nofollow === 'nofollow') {
        $content = preg_replace_callback(
            '/<a[^>]+/',
            function($matches) {
                $a = $matches[0];
                if (strpos($a, 'rel') === false) {
                    $a = preg_replace('/(href=\S(https?:\/\/(?!yourwebsite.com)[^>]+))/i', 'rel="nofollow" $1', $a);
                } elseif (preg_match('/href=\S(https?:\/\/(?!yourwebsite.com)[^>]+)/i', $a)) {
                    $a = preg_replace('/rel=\S(?!nofollow)[^\s>]+/i', 'rel="nofollow"', $a);
                }
                return $a;
            },
            $content
        );
    }

    return $content;
}
add_filter('the_content', 'add_nofollow_based_on_meta');