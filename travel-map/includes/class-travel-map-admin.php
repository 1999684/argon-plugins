<?php
/**
 * 插件的管理界面类
 */
class Travel_Map_Admin {

    /**
     * 插件名称
     */
    private $plugin_name;

    /**
     * 插件版本
     */
    private $version;

    /**
     * 初始化类
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * 注册管理界面样式
     */
    public function enqueue_styles($hook) {
        // 只在插件页面加载
        if (strpos($hook, 'travel-map') === false) {
            return;
        }
        
        wp_enqueue_style($this->plugin_name, TRAVEL_MAP_URL . 'admin/css/travel-map-admin.css', array(), $this->version, 'all');
    }

    /**
     * 注册管理界面脚本
     */
    public function enqueue_scripts($hook) {
        // 只在插件页面加载
        if (strpos($hook, 'travel-map') === false) {
            return;
        }
        
        // 获取高德地图 API Key
        $amap_api_key = $this->get_amap_api_key();
        
        // 加载高德地图API
        wp_enqueue_script('amap', 'https://webapi.amap.com/maps?v=2.0&key=' . $amap_api_key, array(), null, false);
        
        // 加载插件JS
        wp_enqueue_script($this->plugin_name, TRAVEL_MAP_URL . 'admin/js/travel-map-admin.js', array('jquery'), $this->version, false);
        
        // 传递AJAX URL和安全nonce到JS
        wp_localize_script($this->plugin_name, 'travel_map_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('travel_map_nonce'),
        ));
    }

    /**
     * 添加管理菜单
     */
    public function add_menu_page() {
        add_menu_page(
            '旅行地图', 
            '旅行地图', 
            'manage_options', 
            'travel-map', 
            array($this, 'display_admin_page'), 
            'dashicons-location-alt', 
            30
        );
        
        // 添加设置子菜单
        add_submenu_page(
            'travel-map',
            '旅行地图设置',
            '设置',
            'manage_options',
            'travel-map-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * 显示管理界面
     */
    public function display_admin_page() {
        include TRAVEL_MAP_PATH . 'admin/partials/travel-map-admin-display.php';
    }

    /**
     * AJAX: 保存标记点
     */
    public function ajax_save_marker() {
        // 检查安全nonce
        check_ajax_referer('travel_map_nonce', 'nonce');
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        // 获取POST数据
        $title = sanitize_text_field($_POST['title']);
        $lat = floatval($_POST['lat']);
        $lng = floatval($_POST['lng']);
        $marker_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        global $wpdb;
        $table_markers = $wpdb->prefix . 'travel_map_markers';
        
        // 更新或插入标记点
        if ($marker_id > 0) {
            // 更新现有标记点
            $wpdb->update(
                $table_markers,
                array(
                    'title' => $title,
                    'lat' => $lat,
                    'lng' => $lng
                ),
                array('id' => $marker_id)
            );
        } else {
            // 插入新标记点
            $wpdb->insert(
                $table_markers,
                array(
                    'title' => $title,
                    'lat' => $lat,
                    'lng' => $lng
                )
            );
            $marker_id = $wpdb->insert_id;
        }
        
        wp_send_json_success(array(
            'id' => $marker_id,
            'message' => '标记点已保存'
        ));
    }

    /**
     * AJAX: 删除标记点
     */
    public function ajax_delete_marker() {
        // 检查安全nonce
        check_ajax_referer('travel_map_nonce', 'nonce');
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        // 获取POST数据
        $marker_id = intval($_POST['id']);
        
        global $wpdb;
        $table_markers = $wpdb->prefix . 'travel_map_markers';
        $table_links = $wpdb->prefix . 'travel_map_links';
        
        // 首先删除关联的链接
        $wpdb->delete($table_links, array('marker_id' => $marker_id));
        
        // 然后删除标记点
        $wpdb->delete($table_markers, array('id' => $marker_id));
        
        wp_send_json_success(array(
            'message' => '标记点已删除'
        ));
    }

    /**
     * AJAX: 添加链接
     */
    public function ajax_add_link() {
        // 检查安全nonce
        check_ajax_referer('travel_map_nonce', 'nonce');
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        // 获取POST数据
        $marker_id = intval($_POST['marker_id']);
        $title = sanitize_text_field($_POST['title']);
        $url = esc_url_raw($_POST['url']);
        
        // 如果URL没有协议前缀，添加http://
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        
        global $wpdb;
        $table_links = $wpdb->prefix . 'travel_map_links';
        
        // 插入链接
        $wpdb->insert(
            $table_links,
            array(
                'marker_id' => $marker_id,
                'title' => $title,
                'url' => $url
            )
        );
        
        $link_id = $wpdb->insert_id;
        
        wp_send_json_success(array(
            'id' => $link_id,
            'message' => '链接已添加'
        ));
    }

    /**
     * AJAX: 删除链接
     */
    public function ajax_delete_link() {
        // 检查安全nonce
        check_ajax_referer('travel_map_nonce', 'nonce');
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        // 获取POST数据
        $link_id = intval($_POST['id']);
        
        global $wpdb;
        $table_links = $wpdb->prefix . 'travel_map_links';
        
        // 删除链接
        $wpdb->delete($table_links, array('id' => $link_id));
        
        wp_send_json_success(array(
            'message' => '链接已删除'
        ));
    }

    /**
     * 显示设置页面
     */
    public function display_settings_page() {
        // 保存设置
        if (isset($_POST['travel_map_settings_nonce']) && wp_verify_nonce($_POST['travel_map_settings_nonce'], 'travel_map_save_settings')) {
            if (isset($_POST['amap_api_key'])) {
                update_option('travel_map_amap_api_key', sanitize_text_field($_POST['amap_api_key']));
                echo '<div class="notice notice-success is-dismissible"><p>设置已保存。</p></div>';
            }
        }
        
        // 获取当前设置
        $amap_api_key = get_option('travel_map_amap_api_key', '11111111');
        
        // 显示设置表单
        include TRAVEL_MAP_PATH . 'admin/partials/travel-map-admin-settings.php';
    }

    /**
     * 获取所有标记点数据
     */
    public function get_markers() {
        global $wpdb;
        $table_markers = $wpdb->prefix . 'travel_map_markers';
        $table_links = $wpdb->prefix . 'travel_map_links';
        
        // 获取所有标记点
        $markers = $wpdb->get_results("SELECT * FROM $table_markers ORDER BY id DESC", ARRAY_A);
        
        // 为每个标记点获取关联的链接
        foreach ($markers as &$marker) {
            $links = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_links WHERE marker_id = %d ORDER BY id ASC",
                    $marker['id']
                ),
                ARRAY_A
            );
            $marker['links'] = $links;
        }
        
        return $markers;
    }

    /**
     * 获取高德地图 API Key
     */
    public function get_amap_api_key() {
        return get_option('travel_map_amap_api_key', '1111111');
    }
}