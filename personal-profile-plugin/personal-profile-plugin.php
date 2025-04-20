<?php
/**
 * Plugin Name: Personal Profile Plugin
 * Description: 一个展示个人信息的WordPress插件，可以通过域名/medias/myself.html访问
 * Version: 1.0
 * Author: ZTGD
 * Text Domain: personal-profile
 */

// 如果直接访问此文件，则退出
if (!defined('ABSPATH')) {
    exit;
}

class Personal_Profile_Plugin {
    
    // 插件实例
    private static $instance;
    
    // 数据存储选项名
    private $option_name = 'personal_profile_data';
    
    /**
     * 获取插件实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        // 添加激活和停用钩子
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // 添加重写规则
        add_action('init', array($this, 'add_rewrite_rules'));
        
        // 处理自定义URL请求
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_custom_url'));
        
        // 添加管理菜单
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // 注册AJAX处理函数
        add_action('wp_ajax_save_personal_profile', array($this, 'save_personal_profile_data'));
        
        // 添加资源文件
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * 插件激活时执行
     */
    public function activate() {
        // 添加重写规则
        $this->add_rewrite_rules();
        
        // 刷新重写规则
        flush_rewrite_rules();
        
        // 初始化默认数据
        $this->initialize_default_data();
    }
    
    /**
     * 插件停用时执行
     */
    public function deactivate() {
        // 刷新重写规则
        flush_rewrite_rules();
    }
    
    /**
     * 添加重写规则
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^medias/myself\.html$',
            'index.php?personal_profile=1',
            'top'
        );
        
        // 刷新重写规则（仅在需要时）
        flush_rewrite_rules();
    }
    
    /**
     * 添加查询变量
     */
    public function add_query_vars($vars) {
        $vars[] = 'personal_profile';
        return $vars;
    }
    
    /**
     * 处理自定义URL请求
     */
    public function handle_custom_url() {
        global $wp_query;
        
        if (isset($wp_query->query_vars['personal_profile']) && $wp_query->query_vars['personal_profile'] == '1') {
            $this->render_profile_page();
            exit;
        }
    }
    
    /**
     * 渲染个人资料页面
     */
    public function render_profile_page() {
        // 获取存储的数据
        $data = get_option($this->option_name, array());
        
        // 确保有默认数据
        $this->ensure_default_data($data);
        
        // 输出HTML
        include(plugin_dir_path(__FILE__) . 'templates/myself.php');
        exit;
    }
    
    /**
     * 添加管理菜单
     */
    public function add_admin_menu() {
        add_menu_page(
            '个人信息管理',
            '个人信息',
            'manage_options',
            'personal-profile',
            array($this, 'render_admin_page'),
            'dashicons-id',
            30
        );
    }
    
    /**
     * 渲染管理页面
     */
    public function render_admin_page() {
        // 获取存储的数据
        $data = get_option($this->option_name, array());
        
        // 确保有默认数据
        $this->ensure_default_data($data);
        
        // 输出管理界面HTML
        include(plugin_dir_path(__FILE__) . 'templates/admin.php');
    }
    
    /**
     * 注册和加载管理界面脚本和样式
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_personal-profile' !== $hook) {
            return;
        }
        
        // 加载媒体上传器
        wp_enqueue_media();
        
        // 注册和加载CSS
        wp_enqueue_style(
            'personal-profile-admin-css',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            array(),
            '1.0.0'
        );
        
        // 注册和加载JS
        wp_enqueue_script(
            'personal-profile-admin-js',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
    
    /**
     * 保存个人资料数据（AJAX处理）
     */
    public function save_personal_profile_data() {
        // 验证nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'personal_profile_nonce')) {
            wp_send_json_error('安全验证失败');
        }
        
        // 验证用户权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        // 获取并清理数据
        $data = isset($_POST['data']) ? $_POST['data'] : array();
        
        // 调试信息
        error_log('接收到的数据: ' . print_r($data, true));
        
        // 保存数据
        update_option($this->option_name, $data);
        
        wp_send_json_success('数据已保存');
    }
    
    /**
     * 清理数据
     */
    private function sanitize_data($data) {
        if (!is_array($data)) {
            return array();
        }
        
        $clean_data = array();
        
        // 清理身份数据
        if (isset($data['identity']) && is_array($data['identity'])) {
            foreach ($data['identity'] as $key => $identity) {
                $clean_data['identity'][$key] = array(
                    'title' => sanitize_text_field($identity['title']),
                    'description' => wp_kses_post($identity['description']),
                    'image' => esc_url_raw($identity['image']),
                    'article' => wp_kses_post($identity['article'])
                );
            }
        }
        
        // 清理项目数据
        if (isset($data['project']) && is_array($data['project'])) {
            foreach ($data['project'] as $key => $project) {
                $clean_data['project'][$key] = array(
                    'title' => sanitize_text_field($project['title']),
                    'description' => wp_kses_post($project['description']),
                    'image' => esc_url_raw($project['image']),
                    'article' => wp_kses_post($project['article']),
                    'github_username' => sanitize_text_field($project['github_username']),
                    'github_repo' => sanitize_text_field($project['github_repo'])
                );
            }
        }
        
        // 清理设置数据
        if (isset($data['settings']) && is_array($data['settings'])) {
            $clean_data['settings'] = array(
                'background_image' => esc_url_raw($data['settings']['background_image']),
                'left_bg_image' => esc_url_raw($data['settings']['left_bg_image']),
                'profile_image' => esc_url_raw($data['settings']['profile_image']),
                'personal_website' => esc_url_raw($data['settings']['personal_website']),
                'email' => sanitize_email($data['settings']['email']),
                'qq' => sanitize_text_field($data['settings']['qq']),
                'wechat' => sanitize_text_field($data['settings']['wechat']),
            );
        }
        
        return $clean_data;
    }
    
    /**
     * 初始化默认数据
     */
    private function initialize_default_data() {
        // 检查是否已有数据
        $existing_data = get_option($this->option_name, false);
        
        if ($existing_data) {
            return;
        }
        
        // 默认数据
        $default_data = array(
            'identity' => array(
                'default' => array(
                    'title' => '我的身份',
                    'description' => '我拥有多重身份，包括开发者、设计师和学生。每个身份代表了我不同的技能和经历。',
                    'image' => plugin_dir_url(__FILE__) . 'assets/images/default.jpg',
                    'article' => '请从上方选择一个具体的身份，了解我在该领域的专长和经历。'
                ),
                'developer' => array(
                    'title' => '我是一名开发者',
                    'description' => '作为一名全栈开发者，我擅长前端和后端技术，热爱解决复杂问题并创造用户友好的应用程序。',
                    'image' => plugin_dir_url(__FILE__) . 'assets/images/developer.jpg',
                    'article' => '我的开发之旅始于大学时期，从那时起我就对编程充满热情。我精通HTML、CSS、JavaScript、React等前端技术，以及Node.js、Python等后端技术。我相信技术的力量可以改变世界，并致力于创造有意义的数字体验。'
                )
            ),
            'project' => array(
                'default' => array(
                    'title' => '我的项目',
                    'description' => '我参与和开发了多个不同类型的项目，包括网站开发、移动应用和数据分析。',
                    'image' => plugin_dir_url(__FILE__) . 'assets/images/default.jpg',
                    'article' => '请从上方选择一个具体的项目，了解我的项目经历和成果。'
                ),
                'project1' => array(
                    'title' => '个人网站项目',
                    'description' => '这是我开发的个人展示网站，展示我的作品集和技能。',
                    'image' => plugin_dir_url(__FILE__) . 'assets/images/project1.jpg',
                    'article' => '这个个人网站项目使用了现代前端技术栈，包括HTML5、CSS3和JavaScript。网站采用响应式设计，确保在各种设备上都有良好的显示效果。此外，我还实现了一些交互效果，提升用户体验。'
                )
            )
        );
        
        // 保存默认数据
        update_option($this->option_name, $default_data);
    }
    
    /**
     * 确保数据包含默认值
     */
    private function ensure_default_data(&$data) {
        if (!isset($data['identity'])) {
            $data['identity'] = array();
        }
        
        if (!isset($data['project'])) {
            $data['project'] = array();
        }
        
        // 确保有默认身份
        if (!isset($data['identity']['default'])) {
            $data['identity']['default'] = array(
                'title' => '我的身份',
                'description' => '我拥有多重身份，包括开发者、设计师和学生。每个身份代表了我不同的技能和经历。',
                'image' => plugin_dir_url(__FILE__) . 'assets/images/default.jpg',
                'article' => '请从上方选择一个具体的身份，了解我在该领域的专长和经历。'
            );
        }
        
        // 确保有默认项目
        if (!isset($data['project']['default'])) {
            $data['project']['default'] = array(
                'title' => '我的项目',
                'description' => '我参与和开发了多个不同类型的项目，包括网站开发、移动应用和数据分析。',
                'image' => plugin_dir_url(__FILE__) . 'assets/images/default.jpg',
                'article' => '请从上方选择一个具体的项目，了解我的项目经历和成果。'
            );
        }
    }
}

// 初始化插件
function personal_profile_plugin_init() {
    Personal_Profile_Plugin::get_instance();
}
add_action('plugins_loaded', 'personal_profile_plugin_init');