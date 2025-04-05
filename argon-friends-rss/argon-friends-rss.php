<?php
/*
Plugin Name: Argon 友链 RSS 展示
Description: 为 Argon 主题添加友链 RSS 文章聚合功能
Version: 1.3.0
Author: ZTGD
*/

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 获取友链 RSS 源
function afr_get_friend_rss_feeds() {
    // 获取名为"友链RSS"的链接分类
    $category = get_term_by('name', '友链RSS', 'link_category');
    
    // 如果找不到该分类，返回空数组
    if (!$category) {
        return array();
    }
    
    // 只获取"友链RSS"分类下的链接
    $links = get_bookmarks(array(
        'category' => $category->term_id,
        'orderby' => 'name', 
        'order' => 'ASC'
    ));
    
    $rss_feeds = array();
    foreach ($links as $link) {
        if (!empty($link->link_rss)) {
            $rss_feeds[] = array(
                'name' => $link->link_name,
                'url'  => $link->link_url,
                'rss'  => $link->link_rss
            );
        }
    }
    return $rss_feeds;
}

// 解析并缓存 RSS 数据
function afr_fetch_rss_items($feed_url, $cache_time = 3600) {
    $cache_key = 'afr_rss_' . md5($feed_url);
    $cached = get_transient($cache_key);

    if ($cached) {
        return $cached;
    }

    include_once(ABSPATH . WPINC . '/feed.php');
    $rss = fetch_feed($feed_url);

    if (is_wp_error($rss)) {
        return array();
    }

    $items_per_feed = get_option('afr_items_per_feed', 5);
    $items = $rss->get_items(0, $rss->get_item_quantity($items_per_feed));
    $rss_data = array();

    foreach ($items as $item) {
        $rss_data[] = array(
            'title'       => $item->get_title(),
            'link'        => $item->get_permalink(),
            'description' => $item->get_description(),
            'date'        => $item->get_date('Y-m-d H:i:s')
        );
    }

    set_transient($cache_key, $rss_data, $cache_time);
    return $rss_data;
}

// 前端展示逻辑
function afr_display_rss_feed() {
    $output = '<div class="afr-rss-container">';
    $rss_feeds = afr_get_friend_rss_feeds();
    $cache_time = get_option('afr_cache_time', 3600);
    
    // 获取时间期限设置
    $date_limit = get_option('afr_date_limit', '');
    $date_timestamp = !empty($date_limit) ? strtotime($date_limit) : 0;
    
    // 收集所有文章并按日期排序
    $all_items = array();
    
    foreach ($rss_feeds as $feed) {
        $items = afr_fetch_rss_items($feed['rss'], $cache_time);
        if (empty($items)) continue;
        
        foreach ($items as $item) {
            // 如果设置了时间期限，则只添加该日期之后的文章
            $item_timestamp = strtotime($item['date']);
            if ($date_timestamp > 0 && $item_timestamp < $date_timestamp) {
                continue; // 跳过早于时间期限的文章
            }
            
            $item['source_name'] = $feed['name'];
            $item['source_url'] = $feed['url'];
            $all_items[] = $item;
        }
    }
    
    // 按日期排序（最新的在前）
    usort($all_items, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    // 获取总数上限设置并应用
    $total_limit = get_option('afr_total_limit', 0);
    if ($total_limit > 0 && count($all_items) > $total_limit) {
        $all_items = array_slice($all_items, 0, $total_limit);
    }
    
    // 输出文章列表
    $output .= '<div class="afr-rss-list">';
    
    foreach ($all_items as $item) {
        $date_obj = date_create($item['date']);
        $formatted_date = date_format($date_obj, 'Y年m月d日');
        $output .= '<div class="afr-rss-item">
                        <span class="afr-item-title">
                        <a href="' . esc_url($item['link']) . '" target="_blank" title="' . esc_attr($item['title']) . '">' . esc_html($item['title']) . '</a>
                        </span>
                        <div class="afr-item-meta">
                        <span class="afr-item-source">' . esc_html($item['source_name']) . '</span>
                        <span class="afr-item-date">' . $formatted_date . '</span>
                        </div>
                    </div>';
    }
    
    $output .= '</div>';
    $output .= '<p style="text-align: center;font-size:15px;font-color: #96CDCD">该插件由<a href="https://github.com/1999684/argon-plugins">ZTGD</a>制作</p>';
    $output .= '</div>';
    return $output;
}
add_shortcode('argon_friends_rss', 'afr_display_rss_feed'); // 注册短代码

// 添加插件设置页面
function afr_add_settings_page() {
    add_options_page(
        '友链 RSS 设置',
        '友链 RSS',
        'manage_options',
        'afr-settings',
        'afr_render_settings_page'
    );
}
add_action('admin_menu', 'afr_add_settings_page');

function afr_render_settings_page() {
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
    ?>
    <div class="wrap">
        <h1>友链 RSS 设置</h1>
        
        <h2 class="nav-tab-wrapper">
            <a href="?page=afr-settings&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">基本设置</a>
            <a href="?page=afr-settings&tab=instructions" class="nav-tab <?php echo $active_tab == 'instructions' ? 'nav-tab-active' : ''; ?>">使用说明</a>
        </h2>
        
        <?php if ($active_tab == 'settings') { ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('afr_settings_group');
                do_settings_sections('afr-settings');
                submit_button();
                ?>
            </form>
        <?php } else if ($active_tab == 'instructions') { ?>
            <div class="afr-instructions">
                <h3>插件使用说明</h3>
                
                <h4>1. 基本设置</h4>
                <p>在"基本设置"标签页中，您可以设置以下选项：</p>
                <ul>
                    <li><strong>缓存时间</strong>：RSS 数据缓存的时间（秒），默认为 3600 秒（1小时）。</li>
                    <li><strong>每源显示文章数</strong>：每个 RSS 源最多显示的文章数量，默认为 5 篇。</li>
                    <li><strong>时间期限</strong>：只显示该日期之后的文章。如果设置为2025年1月1日，则只显示2025年1月1日之后的文章。留空则显示所有文章。</li>
                    <li><strong>显示文章总数上限</strong>：最终显示的文章总数上限。设置为0则不限制总数。</li>
                </ul>
                
                <h4>2. 添加友链 RSS</h4>
                <p>本插件会自动获取名为"友链RSS"分类下的友情链接。请按照以下步骤操作：</p>
                <ol>
                    <li>在 WordPress 后台，进入"链接"->"链接分类"，创建一个名为"友链RSS"的分类。</li>
                    <li>在"链接"->"添加链接"中，添加友情链接，并确保：
                        <ul>
                            <li>填写正确的网站名称</li>
                            <li>填写网站 URL</li>
                            <li>填写 RSS 地址</li>
                            <li>选择"友链RSS"分类</li>
                        </ul>
                    </li>
                </ol>
                
                <h4>3. 在页面中显示友链 RSS</h4>
                <p>使用短代码 <code>[argon_friends_rss]</code> 在任何页面或文章中显示友链 RSS 内容。</p>
                <p>您也可以在小工具中添加文本小工具，并在其中使用该短代码。</p>
                
                <h4>4. 显示效果</h4>
                <p>插件会自动获取友链的 RSS 源，并按时间顺序（最新的在前）显示文章标题、来源博客名称和发布日期。</p>
                
                <h4>5. 故障排除</h4>
                <p>如果没有显示任何内容，请检查：</p>
                <ul>
                    <li>是否创建了名为"友链RSS"的链接分类</li>
                    <li>是否有友情链接被添加到该分类中</li>
                    <li>友情链接是否填写了有效的 RSS 地址</li>
                </ul>
            </div>
        <?php } ?>
    </div>
    
    <style>
        .afr-instructions {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.04);
            margin-top: 20px;
        }
        .afr-instructions h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .afr-instructions h4 {
            margin-top: 20px;
            color: #23282d;
        }
        .afr-instructions ul, .afr-instructions ol {
            margin-left: 20px;
        }
        .afr-instructions code {
            background: #f5f5f5;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
    <?php
}

function afr_register_settings() {
    register_setting('afr_settings_group', 'afr_cache_time', array('default' => 3600));
    register_setting('afr_settings_group', 'afr_items_per_feed', array('default' => 5));
    register_setting('afr_settings_group', 'afr_date_limit', array('default' => ''));
    register_setting('afr_settings_group', 'afr_total_limit', array('default' => 0));

    add_settings_section('afr_main_section', '基础设置', null, 'afr-settings');

    add_settings_field(
        'afr_cache_time',
        '缓存时间（秒）',
        'afr_cache_time_callback',
        'afr-settings',
        'afr_main_section'
    );

    add_settings_field(
        'afr_items_per_feed',
        '每源显示文章数',
        'afr_items_per_feed_callback',
        'afr-settings',
        'afr_main_section'
    );
    
    add_settings_field(
        'afr_total_limit',
        '显示文章总数上限',
        'afr_total_limit_callback',
        'afr-settings',
        'afr_main_section'
    );
    
    add_settings_field(
        'afr_date_limit',
        '时间期限',
        'afr_date_limit_callback',
        'afr-settings',
        'afr_main_section'
    );
}
add_action('admin_init', 'afr_register_settings');

function afr_cache_time_callback() {
    $value = get_option('afr_cache_time', 3600);
    echo '<input type="number" name="afr_cache_time" value="' . esc_attr($value) . '" />';
    echo '<p class="description">RSS 数据缓存的时间（秒），设置较长的缓存时间可减轻服务器负担。</p>';
}

function afr_items_per_feed_callback() {
    $value = get_option('afr_items_per_feed', 5);
    echo '<input type="number" name="afr_items_per_feed" value="' . esc_attr($value) . '" />';
    echo '<p class="description">每个 RSS 源最多显示的文章数量。</p>';
}

// 在插件中添加小工具支持
add_filter('widget_text', 'do_shortcode');

// 添加样式
function afr_enqueue_styles() {
    wp_enqueue_style('afr-style', plugins_url('style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'afr_enqueue_styles');

function afr_clear_cache_on_settings_change($old_value, $new_value) {
    if ($old_value != $new_value) {
        // 获取所有友链RSS源
        $rss_feeds = afr_get_friend_rss_feeds();
        
        // 清除每个源的缓存
        foreach ($rss_feeds as $feed) {
            $cache_key = 'afr_rss_' . md5($feed['rss']);
            delete_transient($cache_key);
        }
    }
}

function afr_date_limit_callback() {
    $value = get_option('afr_date_limit', '');
    echo '<input type="date" name="afr_date_limit" value="' . esc_attr($value) . '" />';
    echo '<p class="description">只显示该日期之后的文章。留空则显示所有文章。格式：YYYY-MM-DD</p>';
}

function afr_total_limit_callback() {
    $value = get_option('afr_total_limit', 0);
    echo '<input type="number" name="afr_total_limit" value="' . esc_attr($value) . '" />';
    echo '<p class="description">最多显示的文章总数。设置为0则不限制总数。</p>';
}

// 当设置更改时清除缓存
add_action('update_option_afr_items_per_feed', 'afr_clear_cache_on_settings_change', 10, 2);
add_action('update_option_afr_cache_time', 'afr_clear_cache_on_settings_change', 10, 2);
add_action('update_option_afr_date_limit', 'afr_clear_cache_on_settings_change', 10, 2);
add_action('update_option_afr_total_limit', 'afr_clear_cache_on_settings_change', 10, 2);