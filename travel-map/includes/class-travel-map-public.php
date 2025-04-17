<?php
/**
 * 插件的前端显示类
 */
class Travel_Map_Public {

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
     * 注册前端样式
     */
    public function enqueue_styles() {
        // 只在有短代码的页面加载
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'travel_map')) {
            wp_enqueue_style($this->plugin_name, TRAVEL_MAP_URL . 'public/css/travel-map-public.css', array(), $this->version, 'all');
        }
    }

    /**
     * 注册前端脚本
     */
    public function enqueue_scripts() {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'travel_map')) {
            $admin = new Travel_Map_Admin($this->plugin_name, $this->version);
            $amap_api_key = $admin->get_amap_api_key();
            
            // 确保 AMap API 在页脚加载，或者至少在插件脚本之前加载
            wp_enqueue_script('amap', 'https://webapi.amap.com/maps?v=2.0&key=' . $amap_api_key, array(), null, true); 
            
            // 确保插件脚本在页脚加载，并依赖 jQuery 和 amap
            wp_enqueue_script($this->plugin_name, TRAVEL_MAP_URL . 'public/js/travel-map-public.js', array('jquery', 'amap'), $this->version, true); 
            
            $markers = $this->get_markers_data();
            
            // 确保 localize 在 enqueue 之后调用，且针对正确的脚本句柄
            wp_localize_script($this->plugin_name, 'travel_map_data', array(
                'markers' => $markers
            ));
        }
    }

    /**
     * 获取标记点数据
     */
    private function get_markers_data() {
        global $wpdb;
        $table_markers = $wpdb->prefix . 'travel_map_markers';
        $table_links = $wpdb->prefix . 'travel_map_links';
        
        // 获取所有标记点
        $markers = $wpdb->get_results("SELECT * FROM $table_markers ORDER BY id ASC", ARRAY_A);
        
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
     * 显示地图短代码
     */
    public function display_map($atts) {
        // 短代码属性
        $atts = shortcode_atts(array(
            'height' => '600px',
            'zoom' => '4',
            'center' => '116.397428,39.90923', // 默认北京
        ), $atts, 'travel_map');
        
        // 解析中心点坐标
        $center = explode(',', $atts['center']);
        $center_lng = isset($center[0]) ? trim($center[0]) : '116.397428';
        $center_lat = isset($center[1]) ? trim($center[1]) : '39.90923';
        
        // 开始输出缓冲
        ob_start();
        
        // 包含模板文件
        include TRAVEL_MAP_PATH . 'public/partials/travel-map-public-display.php';
        
        // 返回输出内容
        return ob_get_clean();
    }
}