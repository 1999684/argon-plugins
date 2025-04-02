<?php
/*
Plugin Name: Argon 友链展示
Description: 为 Argon 主题添加友链卡片展示功能，支持单向友链、双向友链和博客圈分类
Version: 1.0.0
Author: ZTGD
*/

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 获取友链数据
function afl_get_friend_links() {
    // 检查是否有缓存
    $cache_key = 'afl_friend_links_data';
    $cache_time = get_option('afl_cache_time', 3600); // 默认缓存1小时
    
    // 如果缓存存在且未过期，直接返回缓存数据
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false && !isset($_GET['afl_clear_cache'])) {
        return $cached_data;
    }
    
    $categories = array(
        'blogroll' => '友链RSS',
        'bilateral' => '友链RSS',
        'unilateral' => '友链RSS'
    );
    
    $result = array();
    
    // 获取友链RSS分类
    $category = get_term_by('name', '友链RSS', 'link_category');
    
    if (!$category) {
        // 如果找不到分类，返回空结果
        foreach ($categories as $slug => $name) {
            $result[$slug] = array();
        }
        return $result;
    }
    
    // 获取该分类下的所有友链
    $links = get_bookmarks(array(
        'category' => $category->term_id,
        'orderby' => 'name', 
        'order' => 'ASC'
    ));
    
    // 根据链接备注区分不同类型
    foreach ($links as $link) {
        $notes = strtolower($link->link_notes);
        
        if (strpos($notes, '博客圈') !== false) {
            $result['blogroll'][] = $link;
        } else if (strpos($notes, '双向') !== false) {
            $result['bilateral'][] = $link;
        } else if (strpos($notes, '单向') !== false) {
            $result['unilateral'][] = $link;
        }
    }
    
    // 缓存结果
    if ($cache_time > 0) {
        set_transient($cache_key, $result, $cache_time);
    }
    
    return $result;
}

// 前端展示逻辑
function afl_display_friend_links($atts) {
    // 解析短代码属性
    $atts = shortcode_atts(array(
        'type' => 'all', // all, blogroll, bilateral, unilateral
    ), $atts);
    
    // 确保样式已加载
    wp_enqueue_style('afl-style');
    
    $cards_per_row = get_option('afl_cards_per_row', 3);
    
    $output = '<div class="afl-container">';
    $friend_links = afl_get_friend_links();
    
    // 确定要显示的友链类型
    $types_to_show = ($atts['type'] == 'all') ? 
        array('blogroll', 'bilateral', 'unilateral') : 
        array($atts['type']);
    
    foreach ($types_to_show as $type) {
        if (!isset($friend_links[$type]) || empty($friend_links[$type])) {
            continue;
        }
        
        // 获取分类名称
        $category_names = array(
            'blogroll' => '博客圈',
            'bilateral' => '双向友链',
            'unilateral' => '单向友链'
        );
        
        $output .= '<div class="afl-section">';
        $output .= '<h3 class="afl-section-title">' . $category_names[$type] . '</h3>';
        $output .= '<div class="afl-links-grid" data-cards-per-row="' . esc_attr($cards_per_row) . '">';
        
        foreach ($friend_links[$type] as $link) {
            $image = !empty($link->link_image) ? $link->link_image : plugins_url('assets/default-avatar.png', __FILE__);
            $description = !empty($link->link_description) ? $link->link_description : '这个站点没有描述';
            
            $output .= '<div class="afl-card">';
            $output .= '<a href="' . esc_url($link->link_url) . '" target="_blank" rel="noopener">';
            $output .= '<div class="afl-card-avatar"><img src="' . esc_url($image) . '" alt="' . esc_attr($link->link_name) . '"></div>';
            $output .= '<div class="afl-card-info">';
            $output .= '<div class="afl-card-name">' . esc_html($link->link_name) . '</div>';
            $output .= '<div class="afl-card-description">' . esc_html($description) . '</div>';
            $output .= '</div>';
            $output .= '</a>';
            $output .= '</div>';
        }
        
        $output .= '</div>'; // .afl-links-grid
        $output .= '</div>'; // .afl-section
    }
    $output .= '<p style="text-align: center;font-size:15px;font-color: #96CDCD">该插件由<a href="https://github.com/1999684/argon-friends-rss">ZTGD</a>制作</p>';
    $output .= '</div>'; // .afl-container
    
    // 添加PJAX支持的脚本
    $output .= '<script>
    (function(){
        function initFriendLinksAnimation() {
            // 这里可以添加任何需要在页面加载时执行的友链相关JS
            // 例如：添加动画效果、事件监听等
            console.log("友链卡片已加载");
        }
        
        // 页面首次加载时执行
        initFriendLinksAnimation();
        
        // 为PJAX添加支持
        if (typeof window.pjaxLoaded !== "function") {
            window.pjaxLoaded = initFriendLinksAnimation;
        } else {
            var originalPjaxLoaded = window.pjaxLoaded;
            window.pjaxLoaded = function() {
                originalPjaxLoaded();
                initFriendLinksAnimation();
            };
        }
    })();
    </script>';
    
    return $output;
}
add_shortcode('argon_friend_links', 'afl_display_friend_links'); // 注册短代码

// 添加插件设置页面
function afl_add_settings_page() {
    add_options_page(
        '友链展示设置',
        '友链展示',
        'manage_options',
        'afl-settings',
        'afl_render_settings_page'
    );
}
add_action('admin_menu', 'afl_add_settings_page');

// 使用说明部分修正
function afl_render_settings_page() {
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
    ?>
    <div class="wrap">
        <h1>友链展示设置</h1>
        
        <h2 class="nav-tab-wrapper">
            <a href="?page=afl-settings&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">基本设置</a>
            <a href="?page=afl-settings&tab=instructions" class="nav-tab <?php echo $active_tab == 'instructions' ? 'nav-tab-active' : ''; ?>">使用说明</a>
        </h2>
        
        <?php if ($active_tab == 'settings') { ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('afl_settings_group');
                do_settings_sections('afl-settings');
                submit_button();
                ?>
            </form>
        <?php } else if ($active_tab == 'instructions') { ?>
            <div class="afl-instructions">
                <h3>插件使用说明</h3>
                
                <h4>1. 添加友链</h4>
                <p>本插件会根据友链备注自动分组展示。请按照以下步骤操作：</p>
                <ol>
                    <li>在 WordPress 后台，进入"链接"->"链接分类"，创建一个名为"友链RSS"的分类。</li>
                    <p>此处为了配合RSS插件使用同一个分类</p>
                    <li>在"链接"->"添加链接"中，添加友情链接，并确保：
                        <ul>
                            <li>填写网站名称</li>
                            <li>填写网站 URL</li>
                            <li>填写网站描述（可选）</li>
                            <li>上传或填写网站图片 URL（可选）</li>
                            <li>在备注中标明类型：
                                <ul>
                                    <li>添加"博客圈"标记为博客圈友链</li>
                                    <li>添加"双向"标记为双向友链</li>
                                    <li>添加"单向"标记为单向友链</li>
                                </ul>
                            </li>
                            <li>选择"友链RSS"分类</li>
                        </ul>
                    </li>
                </ol>
                
                <h4>2. 在页面中显示友链</h4>
                <p>使用短代码 <code>[argon_friend_links]</code> 在任何页面或文章中显示所有友链。</p>
                <p>您也可以使用以下参数指定显示特定类型的友链：</p>
                <ul>
                    <li><code>[argon_friend_links type="blogroll"]</code> - 只显示博客圈</li>
                    <li><code>[argon_friend_links type="bilateral"]</code> - 只显示双向友链</li>
                    <li><code>[argon_friend_links type="unilateral"]</code> - 只显示单向友链</li>
                </ul>
                
                <h4>3. 自定义样式</h4>
                <p>插件自带美观的卡片样式，您也可以通过自定义 CSS 来修改友链卡片的外观。</p>

                <h4>4. 缓存设置</h4>
                <p>为了提高性能，插件会缓存友链数据。您可以在设置页面中：</p>
                <ul>
                    <li>设置缓存时间（默认为1小时）</li>
                    <li>点击"清理友链缓存"按钮手动清理缓存</li>
                </ul>
                <p>当您添加、编辑或删除友链时，缓存会自动清理。但如果您发现更改没有立即生效，可以手动清理缓存。</p>
            </div>
        <?php } ?>
    </div>
    
    <style>
        .afl-instructions {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.04);
            margin-top: 20px;
        }
        .afl-instructions h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .afl-instructions h4 {
            margin-top: 20px;
            color: #23282d;
        }
        .afl-instructions ul, .afl-instructions ol {
            margin-left: 20px;
        }
        .afl-instructions code {
            background: #f5f5f5;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
    <?php
}

function afl_register_settings() {
    register_setting('afl_settings_group', 'afl_cards_per_row', array('default' => 3));
    register_setting('afl_settings_group', 'afl_cache_time', array('default' => 3600));

    add_settings_section('afl_main_section', '基础设置', null, 'afl-settings');

    add_settings_field(
        'afl_cards_per_row',
        '每行显示卡片数',
        'afl_cards_per_row_callback',
        'afl-settings',
        'afl_main_section'
    );
    
    add_settings_field(
        'afl_cache_time',
        '缓存时间（秒）',
        'afl_cache_time_callback',
        'afl-settings',
        'afl_main_section'
    );
}
add_action('admin_init', 'afl_register_settings');

function afl_cards_per_row_callback() {
    $value = get_option('afl_cards_per_row', 3);
    echo '<input type="number" min="1" max="6" name="afl_cards_per_row" value="' . esc_attr($value) . '" />';
    echo '<p class="description">设置每行显示的友链卡片数量（1-6之间）</p>';
}

// 添加样式
function afl_enqueue_styles() {
    // 使用版本号避免缓存问题
    $version = filemtime(plugin_dir_path(__FILE__) . 'style.css');
    if (!$version) $version = '1.0.0';
    
    wp_register_style(
        'afl-style', 
        plugins_url('style.css', __FILE__), 
        array(), 
        $version
    );
    
    // 直接加载样式，不再依赖短代码检测
    wp_enqueue_style('afl-style');
}
add_action('wp_enqueue_scripts', 'afl_enqueue_styles');

// 添加内联样式以应用每行卡片数设置
function afl_add_inline_styles() {
    $cards_per_row = get_option('afl_cards_per_row', 3);
    $custom_css = "
        .afl-links-grid {
            grid-template-columns: repeat({$cards_per_row}, 1fr) !important;
        }
        
        @media (max-width: 768px) {
            .afl-links-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        
        @media (max-width: 480px) {
            .afl-links-grid {
                grid-template-columns: 1fr !important;
            }
        }
    ";
    
    wp_add_inline_style('afl-style', $custom_css);
}
add_action('wp_enqueue_scripts', 'afl_add_inline_styles', 20); // 提高优先级，确保在主样式后加载

// 添加PJAX支持的全局脚本
function afl_add_pjax_support() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 如果页面包含友链卡片，初始化它们
        if (document.querySelector('.afl-container')) {
            if (typeof window.pjaxLoaded === 'function') {
                window.pjaxLoaded();
            }
        }
    });
    
    // 为PJAX加载添加全局事件监听
    document.addEventListener('pjax:complete', function() {
        // PJAX加载完成后，检查是否有友链容器
        if (document.querySelector('.afl-container')) {
            if (typeof window.pjaxLoaded === 'function') {
                window.pjaxLoaded();
            }
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'afl_add_pjax_support');

// 创建默认头像文件
function afl_create_default_avatar() {
    $avatar_path = plugin_dir_path(__FILE__) . 'assets/default-avatar.png';
    
    // 如果默认头像不存在，创建一个简单的默认头像
    if (!file_exists($avatar_path)) {
        // 确保assets目录存在
        $assets_dir = plugin_dir_path(__FILE__) . 'assets';
        if (!file_exists($assets_dir)) {
            mkdir($assets_dir, 0755, true);
        }
        
        // 尝试复制WordPress默认头像或创建一个简单的头像
        $wp_avatar = ABSPATH . 'wp-includes/images/blank.png';
        if (file_exists($wp_avatar)) {
            copy($wp_avatar, $avatar_path);
        } else {
            // 创建一个简单的默认头像
            $img = imagecreatetruecolor(60, 60);
            $bg_color = imagecolorallocate($img, 240, 240, 240);
            $text_color = imagecolorallocate($img, 180, 180, 180);
            imagefilledrectangle($img, 0, 0, 60, 60, $bg_color);
            imagestring($img, 5, 10, 20, 'Avatar', $text_color);
            imagepng($img, $avatar_path);
            imagedestroy($img);
        }
    }
}
register_activation_hook(__FILE__, 'afl_create_default_avatar');

function afl_cache_time_callback() {
    $value = get_option('afl_cache_time', 3600);
    echo '<input type="number" min="0" name="afl_cache_time" value="' . esc_attr($value) . '" />';
    echo '<p class="description">设置友链数据缓存的时间（秒），设置为0则禁用缓存。默认3600秒（1小时）</p>';
}

// 添加清理缓存按钮
function afl_add_clear_cache_button() {
    if (isset($_GET['page']) && $_GET['page'] == 'afl-settings') {
        ?>
        <div class="wrap" style="margin-top: 20px;">
            <a href="<?php echo admin_url('options-general.php?page=afl-settings&afl_clear_cache=1'); ?>" class="button button-secondary">
                清理友链缓存
            </a>
            <p class="description">点击此按钮可以立即清理友链数据缓存，使更改立即生效。</p>
        </div>
        <?php
    }
    
    // 处理缓存清理请求
    if (isset($_GET['afl_clear_cache'])) {
        delete_transient('afl_friend_links_data');
        add_action('admin_notices', 'afl_cache_cleared_notice');
    }
}
add_action('admin_footer', 'afl_add_clear_cache_button');

// 显示缓存已清理的通知
function afl_cache_cleared_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>友链缓存已成功清理！</p>
    </div>
    <?php
}

// 当友链或设置更改时自动清理缓存
function afl_clear_cache_on_update($data, $postarr) {
    if ($data['post_type'] == 'link') {
        delete_transient('afl_friend_links_data');
    }
    return $data;
}
add_filter('wp_insert_post_data', 'afl_clear_cache_on_update', 10, 2);

// 当设置更改时清理缓存
function afl_clear_cache_on_settings_change($old_value, $new_value) {
    delete_transient('afl_friend_links_data');
}
add_action('update_option_afl_cache_time', 'afl_clear_cache_on_settings_change', 10, 2);
add_action('update_option_afl_cards_per_row', 'afl_clear_cache_on_settings_change', 10, 2);

// 添加链接更新时的钩子
function afl_clear_cache_on_link_update() {
    delete_transient('afl_friend_links_data');
}
add_action('add_link', 'afl_clear_cache_on_link_update');
add_action('edit_link', 'afl_clear_cache_on_link_update');
add_action('delete_link', 'afl_clear_cache_on_link_update');