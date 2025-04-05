<?php
/*
Plugin Name: Argon Steam Library
Description: 展示 Steam 游戏库，支持卡片式布局和加载更多功能
Version: 1.0.0
Author: ZTGD
*/

if (!defined('ABSPATH')) {
    exit;
}

function asl_add_settings_page() {
    add_options_page(
        'Steam Library 设置',
        'Steam Library',
        'manage_options',
        'asl-settings',
        'asl_render_settings_page'
    );
}
add_action('admin_menu', 'asl_add_settings_page');

function asl_register_settings() {
    register_setting('asl_settings_group', 'asl_steam_api_key');
    register_setting('asl_settings_group', 'asl_steam_id');
    register_setting('asl_settings_group', 'asl_games_per_page', array('default' => 12));
    register_setting('asl_settings_group', 'asl_cache_time', array('default' => 3600));
    register_setting('asl_settings_group', 'asl_cards_per_row', array('default' => 4));
}
add_action('admin_init', 'asl_register_settings');

function asl_get_steam_games($page = 1) {
    $steam_api_key = get_option('asl_steam_api_key');
    $steam_id = get_option('asl_steam_id');
    $games_per_page = get_option('asl_games_per_page', 12);
    $cache_time = get_option('asl_cache_time', 3600);
    
    $cache_key = 'asl_steam_games';
    $games = get_transient($cache_key);
    
    if ($games === false) {
        $api_url = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key={$steam_api_key}&steamid={$steam_id}&format=json&include_appinfo=1&include_played_free_games=1";
        
        $response = wp_remote_get($api_url);
        
        if (is_wp_error($response)) {
            return array();
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['response']['games'])) {
            return array();
        }
        
        $games = $data['response']['games'];
        
        usort($games, function($a, $b) {
            return $b['playtime_forever'] - $a['playtime_forever'];
        });
        
        set_transient($cache_key, $games, $cache_time);
    }
    
    $offset = ($page - 1) * $games_per_page;
    $total_games = count($games);
    $games_slice = array_slice($games, $offset, $games_per_page);
    
    return array(
        'games' => $games_slice,
        'total' => $total_games,
        'has_more' => ($offset + $games_per_page) < $total_games
    );
}

function asl_display_steam_library($atts) {
    wp_enqueue_style('asl-style');
    wp_enqueue_script('asl-script');
    
    $steam_api_key = get_option('asl_steam_api_key');
    $steam_id = get_option('asl_steam_id');
    
    if (empty($steam_api_key) || empty($steam_id)) {
        return '<div class="asl-error">请在 WordPress 后台设置 Steam API Key 和 Steam ID。</div>';
    }
    
    $inline_css = "
        .asl-game-card {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 0;
            padding-bottom: 46.74%;
        }
        .asl-game-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .asl-game-playtime {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            z-index: 2;
        }
    ";
    
    $output = '<style>' . $inline_css . '</style>';
    $output .= '<div class="asl-container" data-page="1">';
    $output .= '<div class="asl-games-grid">';
    
    $games_data = asl_get_steam_games(1);
    foreach ($games_data['games'] as $game) {
        $playtime = floor($game['playtime_forever'] / 60);
        $image_url = "https://cdn.akamai.steamstatic.com/steam/apps/{$game['appid']}/header.jpg";
        
        $output .= '<div class="asl-game-card">';
        $output .= '<div class="asl-game-image"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($game['name']) . '"></div>';
        $output .= '<div class="asl-game-playtime">' . esc_html($playtime) . ' 小时</div>';
        $output .= '<div class="asl-game-info">';
        $output .= '<h3 class="asl-game-name">' . esc_html($game['name']) . '</h3>';
        $output .= '</div>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    $output .= '<p style="text-align: center;font-size:15px;font-color: #96CDCD">该插件由<a href="https://github.com/1999684/argon-plugins">ZTGD</a>制作</p>';
    if ($games_data['has_more']) {
        $output .= '<div class="asl-load-more"><button class="asl-load-more-btn">加载更多</button></div>';
    }
    
    $output .= '</div>';
    
    return $output;
}
add_shortcode('steam_library', 'asl_display_steam_library');

function asl_load_more_games() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'asl_nonce')) {
        wp_send_json_error('安全验证失败');
        exit;
    }
    
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $games_data = asl_get_steam_games($page);
    
    wp_send_json($games_data);
}
add_action('wp_ajax_asl_load_more', 'asl_load_more_games');
add_action('wp_ajax_nopriv_asl_load_more', 'asl_load_more_games');

function asl_enqueue_assets() {
    $version = filemtime(plugin_dir_path(__FILE__) . 'css/style.css');
    if (!$version) $version = time();
    
    wp_enqueue_style('asl-style', plugins_url('css/style.css', __FILE__), array(), $version);
    
    wp_register_script('asl-script', plugins_url('js/script.js', __FILE__), array('jquery'), $version, true);
    
    wp_localize_script('asl-script', 'aslAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('asl_nonce')
    ));
    
    $cards_per_row = get_option('asl_cards_per_row', 4);
    
    $inline_css = "
        .asl-games-grid {
            display: grid;
            grid-template-columns: repeat({$cards_per_row}, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        @media (max-width: 992px) {
            .asl-games-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 768px) {
            .asl-games-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 480px) {
            .asl-games-grid {
                grid-template-columns: 1fr;
            }
        }
    ";
    wp_add_inline_style('asl-style', $inline_css);
}
add_action('wp_enqueue_scripts', 'asl_enqueue_assets');

function asl_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Steam 游戏库设置</h1>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('asl_settings_group');
            do_settings_sections('asl-settings');
            ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Steam API Key</th>
                    <td>
                        <input type="text" name="asl_steam_api_key" value="<?php echo esc_attr(get_option('asl_steam_api_key')); ?>" class="regular-text" />
                        <p class="description">在 <a href="https://steamcommunity.com/dev/apikey" target="_blank">Steam 开发者网站</a> 获取 API Key</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Steam ID</th>
                    <td>
                        <input type="text" name="asl_steam_id" value="<?php echo esc_attr(get_option('asl_steam_id')); ?>" class="regular-text" />
                        <p class="description">您的 Steam ID（17位数字）</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">每页游戏数</th>
                    <td>
                        <input type="number" name="asl_games_per_page" value="<?php echo esc_attr(get_option('asl_games_per_page', 12)); ?>" min="1" max="100" />
                        <p class="description">每次加载显示的游戏数量</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">每行卡片数</th>
                    <td>
                        <input type="number" name="asl_cards_per_row" value="<?php echo esc_attr(get_option('asl_cards_per_row', 4)); ?>" min="1" max="6" />
                        <p class="description">每行显示的游戏卡片数量（1-6之间）</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">缓存时间（秒）</th>
                    <td>
                        <input type="number" name="asl_cache_time" value="<?php echo esc_attr(get_option('asl_cache_time', 3600)); ?>" min="0" />
                        <p class="description">游戏数据缓存时间，0 表示不缓存</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        
        <div class="asl-instructions">
            <h2>使用说明</h2>
            <p>在任何页面或文章中使用短代码 <code>[steam_library]</code> 来显示您的 Steam 游戏库。</p>
            <p>确保您已经正确设置了 Steam API Key 和 Steam ID。</p>
            
            <h3>如何获取 Steam ID？</h3>
            <ol>
                <li>访问您的 Steam 个人资料页面</li>
                <li>在浏览器地址栏中，您会看到类似 <code>https://steamcommunity.com/id/YOUR_USERNAME/</code> 或 <code>https://steamcommunity.com/profiles/YOUR_STEAM_ID/</code> 的 URL</li>
                <li>如果是第一种格式，您需要使用 <a href="https://steamid.io/" target="_blank">SteamID.io</a> 等工具转换为 17 位数字 ID</li>
            </ol>
        </div>
    </div>
    <style>
        .asl-instructions {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.04);
        }
        .asl-instructions h2 {
            margin-top: 0;
        }
    </style>
    <?php
    
    ?>
    <div class="asl-cache-control" style="margin-top: 20px;">
        <a href="<?php echo admin_url('options-general.php?page=asl-settings&asl_clear_cache=1'); ?>" class="button button-secondary">
            清理游戏库缓存
        </a>
        <p class="description">如果您的游戏库有更新，点击此按钮可以立即刷新数据。</p>
    </div>
    <?php
}

function asl_maybe_clear_cache() {
    if (isset($_GET['page']) && $_GET['page'] == 'asl-settings' && isset($_GET['asl_clear_cache'])) {
        delete_transient('asl_steam_games');
        add_action('admin_notices', 'asl_cache_cleared_notice');
    }
}
add_action('admin_init', 'asl_maybe_clear_cache');

function asl_cache_cleared_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>Steam 游戏库缓存已成功清理！</p>
    </div>
    <?php
}