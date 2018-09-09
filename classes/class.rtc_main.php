<?php

if (!class_exists("RTC_Main")) {

    class RTC_Main {

        function __construct() {
            add_action('add_meta_boxes', array($this, "create_meta_box"));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_style_and_scripts'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_style_and_scripts'));
            add_action('save_post', array($this, 'save_metabox'), 10, 1);
            add_filter('the_content', array($this, 'display_contributors'));
        }

        function create_meta_box() {
            $user = wp_get_current_user();
            if (count(array_intersect(array('author', 'administrator', 'editor'), (array) $user->roles)) > 0) {
                add_meta_box('rtc_metabox', __(RTC_NAME, "rtc"), array($this, "display_meta_box"), array('post','page'), 'side', 'high');
            }
        }

        function enqueue_style_and_scripts() {
            wp_enqueue_style('rtc_style', RTC_URL . 'assets/rtc_style.css');
        }

        function display_meta_box() {
            global $post;
            $args = array(
                'blog_id' => $GLOBALS['blog_id'],
                'role' => 'author'
            );
            $authors = get_users($args);
            $selected_contributors = get_post_meta($post->ID, 'rt_contributors', true);
            include( RTC_TEMPLATE . 'rtc_view.php' );
        }

        function save_metabox($id) {
            $post_type = get_post_type($id);
            if ("post" != $post_type && "page" != $post_type) {
                return;
            }
            if (isset($_POST['rt_authors']) && !empty($_POST['rt_authors'])) {
                update_post_meta($id, 'rt_contributors', $_POST['rt_authors']);
            }
        }

        function display_contributors($content) {
            global $post;
            $id = $post->ID;
            $contributors = get_post_meta($id, 'rt_contributors', true);
            if (!empty($contributors) && (is_singular() || is_single())) {
                $content .= sprintf("<div id='rt_contributors'><p>%s</p><ul>", __("Contributors", "rtc"));
                foreach ($contributors as $value) {
                    $meta = get_userdata($value);
                    $display_name = $meta->display_name;
                    $avatar = get_avatar_url($value, array('size' => '35'));
                    $url = get_author_posts_url($value);
                    $content .= sprintf("<li><img src='%s'><a href='%s'>%s</a></li>", $avatar, $url, $display_name);
                }
                $content .= sprintf("</ul></div>", __("Contributors", "rtc"));
            }
            return $content;
        }

    }

    new RTC_Main();
}