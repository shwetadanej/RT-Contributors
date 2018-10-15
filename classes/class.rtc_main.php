<?php

if (!class_exists("RTC_Main")) {

    class RTC_Main {

        public $contributors;
        function __construct() {
            add_action('add_meta_boxes', array($this, "create_meta_box"));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_style_and_scripts'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_style_and_scripts'));
            add_action('save_post', array($this, 'save_metabox'), 10, 1);
            add_filter('the_content', array($this, 'display_contributors'),99);
            add_filter( 'posts_where', array($this,'modifiy_author_archive_query' ));
            add_action( 'wp', array($this,'change_author_data' ));
        }

        function create_meta_box() {
            $user = wp_get_current_user();
            if (count(array_intersect(array('author', 'administrator', 'editor'), (array) $user->roles)) > 0) {
                add_meta_box('rtc_metabox', __(RTC_NAME, "rtc"), array($this, "display_meta_box"), array('post'), 'side', 'high');
            }
        }

        function enqueue_style_and_scripts() {
            wp_enqueue_style('rtc_style', RTC_URL . 'assets/rtc_style.css');
        }

        function display_meta_box() {
            global $post;
            $contributors = array();
            $all_users = get_users();
            foreach($all_users as $user){
                if($user->has_cap('publish_posts')){
                    $contributors[] = $user;
                }
            }
            $selected_contributors = get_post_meta($post->ID, 'rt_contributors', true);
            $author_id = $post->post_author;
            include( RTC_TEMPLATE . 'rtc_view.php' );
        }

        function save_metabox($id) {
            $post_type = get_post_type($id);
            if ("post" != $post_type && "page" != $post_type) {
                return;
            }
            $prev_users = get_post_meta($id, 'rt_contributors', true);
            if (isset($_POST['rt_authors']) && !empty($_POST['rt_authors'])) {
                update_post_meta($id, 'rt_contributors', $_POST['rt_authors']);
            }
            $deleted_users = array_diff($prev_users,  $_POST['rt_authors']);
            foreach ($deleted_users as $k => $v){
                $postlist = get_user_meta($v,'rt_contributor_post',true);
                $key = array_search($id, $postlist);
                unset($postlist[$key]);
                update_user_meta($v,'rt_contributor_post' , $postlist);
            }
            foreach ($_POST['rt_authors'] as $key => $value) {
                $postlist = get_user_meta($value,'rt_contributor_post',true);
                if(!is_array($postlist)){
                    $postlist = array();
                }
                if(!in_array($id, $postlist)){
                    $postlist[] = $id;
                }
                update_user_meta($value,'rt_contributor_post' , $postlist);
            }
        }

        function display_contributors($content) {
            global $post;
            $id = $post->ID;
            $contributors = get_post_meta($id, 'rt_contributors', true);
            if (!empty($contributors)) {
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
        function modifiy_author_archive_query($where){
            if(!is_admin() && is_author()){
                $author = get_user_by( 'slug', get_query_var( 'author_name' ) );
                $authorid = $author->ID;
                $postids = get_user_meta($authorid,'rt_contributor_post',true);
                if($postids){
                    $postids = implode(",", $postids);
                    $where .= "OR ID IN ($postids)";
                }
            }
            return $where;
        }
        function change_author_data(){
            if(!is_admin() && is_author()){
                global $authordata;
                $author = get_user_by( 'slug', get_query_var( 'author_name' ) );
                $authordata = $author;
            }
        }
    }

    new RTC_Main();
}