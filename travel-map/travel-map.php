<?php
/**
 * Plugin Name: Travel Map
 * Description: 一个用于在WordPress网站上显示旅行地图和相关链接的插件
 * Version: 1.0.3
 * Author: ZTGD
 * Text Domain: travel-map
 */

// 如果直接访问此文件，则中止
if (!defined('WPINC')) {
    die;
}

// 定义插件版本
define('TRAVEL_MAP_VERSION', '1.0.3');

// 定义插件路径常量
define('TRAVEL_MAP_PATH', plugin_dir_path(__FILE__));
define('TRAVEL_MAP_URL', plugin_dir_url(__FILE__));

// 包含主类文件
require_once TRAVEL_MAP_PATH . 'includes/class-travel-map.php';

/**
 * 开始执行插件
 */
function run_travel_map() {
    $plugin = new Travel_Map();
    $plugin->run();
}

// 运行插件
run_travel_map();