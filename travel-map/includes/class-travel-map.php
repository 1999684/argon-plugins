<?php
/**
 * 插件的主类
 */
class Travel_Map {

    /**
     * 插件加载器
     */
    protected $loader;

    /**
     * 插件名称
     */
    protected $plugin_name;

    /**
     * 插件版本
     */
    protected $version;

    /**
     * 初始化插件
     */
    public function __construct() {
        $this->version = TRAVEL_MAP_VERSION;
        $this->plugin_name = 'travel-map';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * 加载依赖文件
     */
    private function load_dependencies() {
        // 加载管理界面类
        require_once TRAVEL_MAP_PATH . 'includes/class-travel-map-admin.php';
        
        // 加载前端类
        require_once TRAVEL_MAP_PATH . 'includes/class-travel-map-public.php';
    }

    /**
     * 注册管理界面钩子
     */
    private function define_admin_hooks() {
        $plugin_admin = new Travel_Map_Admin($this->get_plugin_name(), $this->get_version());
        
        // 添加管理菜单
        add_action('admin_menu', array($plugin_admin, 'add_menu_page'));
        
        // 注册脚本和样式
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
        
        // 注册AJAX处理函数
        add_action('wp_ajax_travel_map_save_marker', array($plugin_admin, 'ajax_save_marker'));
        add_action('wp_ajax_travel_map_delete_marker', array($plugin_admin, 'ajax_delete_marker'));
        add_action('wp_ajax_travel_map_add_link', array($plugin_admin, 'ajax_add_link'));
        add_action('wp_ajax_travel_map_delete_link', array($plugin_admin, 'ajax_delete_link'));
    }

    /**
     * 注册前端钩子
     */
    private function define_public_hooks() {
        $plugin_public = new Travel_Map_Public($this->get_plugin_name(), $this->get_version());
        
        // 注册脚本和样式
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));
        
        // 注册短代码
        add_shortcode('travel_map', array($plugin_public, 'display_map'));
    }

    /**
     * 获取插件名称
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * 获取插件版本
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * 运行插件
     */
    public function run() {
        // 注册激活和停用钩子
        register_activation_hook(TRAVEL_MAP_PATH . 'travel-map.php', array($this, 'activate'));
        register_deactivation_hook(TRAVEL_MAP_PATH . 'travel-map.php', array($this, 'deactivate'));
    }

    /**
     * 插件激活时执行
     */
    public function activate() {
        // 创建数据库表
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // 标记点表
        $table_markers = $wpdb->prefix . 'travel_map_markers';
        $sql_markers = "CREATE TABLE $table_markers (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            lat decimal(10,6) NOT NULL,
            lng decimal(10,6) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // 链接表
        $table_links = $wpdb->prefix . 'travel_map_links';
        $sql_links = "CREATE TABLE $table_links (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            marker_id mediumint(9) NOT NULL,
            title varchar(255) NOT NULL,
            url varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY marker_id (marker_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_markers);
        dbDelta($sql_links);
    }

    /**
     * 插件停用时执行
     */
    public function deactivate() {
        // 停用时的操作
    }
}