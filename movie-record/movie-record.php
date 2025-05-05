<?php
/**
 * Plugin Name: Movie Record
 * Plugin URI: #
 * Description: 一个用于记录影视作品的WordPress插件，支持电影、动漫、综艺、电视剧等分类。
 * Version: 1.0.0
 * Author: ZTGD
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('MOVIE_RECORD_VERSION', '1.0.0');
define('MOVIE_RECORD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MOVIE_RECORD_PLUGIN_URL', plugin_dir_url(__FILE__));

// 注册激活钩子
register_activation_hook(__FILE__, 'movie_record_activate');

// 激活插件时创建数据表
function movie_record_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $table_name = $wpdb->prefix . 'movie_records';
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        poster_url text NOT NULL,
        link_url text NOT NULL,
        category varchar(50) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// 添加管理菜单
add_action('admin_menu', 'movie_record_admin_menu');

function movie_record_admin_menu() {
    add_menu_page(
        '影视记录', // 页面标题
        '影视记录', // 菜单标题
        'manage_options', // 权限
        'movie-record', // 菜单标识
        'movie_record_admin_page', // 回调函数
        'dashicons-video-alt2', // 图标
        30 // 位置
    );
}

// 加载管理页面
function movie_record_admin_page() {
    require_once MOVIE_RECORD_PLUGIN_DIR . 'templates/admin.php';
}

// 注册后台脚本和样式
add_action('admin_enqueue_scripts', 'movie_record_admin_scripts');

function movie_record_admin_scripts($hook) {
    if (strpos($hook, 'movie-record') !== false) {
        wp_enqueue_media();
        wp_enqueue_style('movie-record-admin-style', MOVIE_RECORD_PLUGIN_URL . 'assets/css/admin.css', array(), MOVIE_RECORD_VERSION);
        wp_enqueue_script('movie-record-admin-script', MOVIE_RECORD_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), MOVIE_RECORD_VERSION, true);
        
        wp_localize_script('movie-record-admin-script', 'movieRecordAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('movie_record_nonce')
        ));
    }
}

// 注册前台脚本和样式
add_action('wp_enqueue_scripts', 'movie_record_frontend_scripts');

function movie_record_frontend_scripts() {
    wp_enqueue_style('movie-record-style', MOVIE_RECORD_PLUGIN_URL . 'assets/css/style.css', array(), MOVIE_RECORD_VERSION);
    wp_enqueue_script('movie-record-script', MOVIE_RECORD_PLUGIN_URL . 'assets/js/script.js', array('jquery'), MOVIE_RECORD_VERSION, true);
}

// 注册短代码
add_shortcode('movie_records', 'movie_record_shortcode');

function movie_record_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => ''
    ), $atts);
    
    ob_start();
    require MOVIE_RECORD_PLUGIN_DIR . 'templates/shortcode.php';
    return ob_get_clean();
}

// AJAX处理函数
require_once MOVIE_RECORD_PLUGIN_DIR . 'includes/ajax-handlers.php';