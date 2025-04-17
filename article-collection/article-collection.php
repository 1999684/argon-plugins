<?php
/**
 * Plugin Name: 文章收藏
 * Description: 允许用户添加和分类文章收藏
 * Version: 1.0
 * Author: ZTGD
 * Text Domain: article-collection
 */

// 如果直接访问此文件，则退出
if (!defined('ABSPATH')) {
    exit;
}

// 注册激活钩子
register_activation_hook(__FILE__, 'article_collection_activate');

function article_collection_activate() {
    // 创建默认分类和文章
    $default_data = array(
        'categories' => array(
            array(
                'name' => '技术',
                'articles' => array(
                    array(
                        'title' => 'JavaScript基础教程',
                        'url' => 'https://example.com/js-basics',
                        'date' => '2023-05-15'
                    )
                )
            ),
            array(
                'name' => '文学',
                'articles' => array(
                    array(
                        'title' => '现代诗歌赏析',
                        'url' => 'https://example.com/modern-poetry',
                        'date' => '2023-01-30'
                    )
                )
            )
        )
    );
    
    update_option('article_collection_data', $default_data);
}

// 注册停用钩子
register_deactivation_hook(__FILE__, 'article_collection_deactivate');

function article_collection_deactivate() {
    // 停用时不删除数据
}

// 注册卸载钩子
register_uninstall_hook(__FILE__, 'article_collection_uninstall');

function article_collection_uninstall() {
    // 卸载时删除数据
    delete_option('article_collection_data');
}

// 添加管理菜单
add_action('admin_menu', 'article_collection_menu');

function article_collection_menu() {
    add_menu_page(
        '文章收藏', 
        '文章收藏', 
        'manage_options', 
        'article-collection', 
        'article_collection_page',
        'dashicons-book-alt',
        30
    );
    
    add_submenu_page(
        'article-collection',
        '所有文章',
        '所有文章',
        'manage_options',
        'article-collection',
        'article_collection_page'
    );
    
    add_submenu_page(
        'article-collection',
        '添加新文章',
        '添加新文章',
        'manage_options',
        'article-collection-add',
        'article_collection_add_page'
    );
    
    // 添加使用说明子菜单
    add_submenu_page(
        'article-collection',
        '使用说明',
        '使用说明',
        'manage_options',
        'article-collection-help',
        'article_collection_help_page'
    );
}

// 主页面 - 显示所有文章
function article_collection_page() {
    $data = get_option('article_collection_data', array('categories' => array()));
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">文章收藏</h1>
        <a href="<?php echo admin_url('admin.php?page=article-collection-add'); ?>" class="page-title-action">添加新文章</a>
        
        <hr class="wp-header-end">
        
        <?php if (empty($data['categories'])): ?>
            <p>暂无文章收藏，请添加新的文章。</p>
        <?php else: ?>
            <?php foreach ($data['categories'] as $category): ?>
                <h2>
                    <?php echo esc_html($category['name']); ?>
                    <a href="<?php echo admin_url('admin.php?page=article-collection&action=delete_category&category=' . urlencode($category['name'])); ?>" 
                       class="page-title-action" style="font-size: 12px;"
                       onclick="return confirm('确定要删除此分类及其所有文章吗？此操作不可恢复！');">
                        删除分类
                    </a>
                </h2>
                
                <?php if (empty($category['articles'])): ?>
                    <p>此分类下暂无文章。</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>标题</th>
                                <th>链接</th>
                                <th>日期</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category['articles'] as $index => $article): ?>
                                <tr>
                                    <td><?php echo esc_html($article['title']); ?></td>
                                    <td><a href="<?php echo esc_url($article['url']); ?>" target="_blank"><?php echo esc_url($article['url']); ?></a></td>
                                    <td><?php echo esc_html($article['date']); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=article-collection-add&edit=' . urlencode($category['name']) . '&article=' . $index); ?>">编辑</a> | 
                                        <a href="<?php echo admin_url('admin.php?page=article-collection&action=delete&category=' . urlencode($category['name']) . '&article=' . $index); ?>" 
                                           onclick="return confirm('确定要删除此文章吗？');">删除</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <br>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
    
    // 处理删除分类操作
    if (isset($_GET['action']) && $_GET['action'] == 'delete_category' && isset($_GET['category'])) {
        $category_name = urldecode($_GET['category']);
        
        $data = get_option('article_collection_data', array('categories' => array()));
        
        foreach ($data['categories'] as $cat_index => $category) {
            if ($category['name'] === $category_name) {
                // 删除分类
                array_splice($data['categories'], $cat_index, 1);
                
                // 保存更新后的数据
                update_option('article_collection_data', $data);
                
                // 自动更新HTML文件
                article_collection_generate_html();
                
                // 重定向以避免刷新时重复删除
                wp_redirect(admin_url('admin.php?page=article-collection&category_deleted=1'));
                exit;
            }
        }
    }
    
    // 处理删除文章操作
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['category']) && isset($_GET['article'])) {
        $category_name = urldecode($_GET['category']);
        $article_index = intval($_GET['article']);
        
        $data = get_option('article_collection_data', array('categories' => array()));
        
        foreach ($data['categories'] as $cat_index => $category) {
            if ($category['name'] === $category_name && isset($category['articles'][$article_index])) {
                // 删除文章
                array_splice($data['categories'][$cat_index]['articles'], $article_index, 1);
                
                // 保存更新后的数据
                update_option('article_collection_data', $data);
                
                // 自动更新HTML文件
                article_collection_generate_html();
                
                // 重定向以避免刷新时重复删除
                wp_redirect(admin_url('admin.php?page=article-collection&deleted=1'));
                exit;
            }
        }
    }
    
    // 显示删除成功消息
    if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
        echo '<div class="notice notice-success is-dismissible"><p>文章已成功删除！</p></div>';
    }
    
    // 显示删除分类成功消息
    if (isset($_GET['category_deleted']) && $_GET['category_deleted'] == 1) {
        echo '<div class="notice notice-success is-dismissible"><p>分类及其所有文章已成功删除！</p></div>';
    }
}

// 添加/编辑文章页面
function article_collection_add_page() {
    $data = get_option('article_collection_data', array('categories' => array()));
    
    $edit_category = isset($_GET['edit']) ? urldecode($_GET['edit']) : '';
    $edit_article_index = isset($_GET['article']) ? intval($_GET['article']) : -1;
    $edit_article = null;
    
    // 查找要编辑的文章
    if ($edit_category && $edit_article_index >= 0) {
        foreach ($data['categories'] as $category) {
            if ($category['name'] === $edit_category && isset($category['articles'][$edit_article_index])) {
                $edit_article = $category['articles'][$edit_article_index];
                break;
            }
        }
    }
    
    // 处理表单提交
    if (isset($_POST['submit_article'])) {
        $category = sanitize_text_field($_POST['category']);
        $new_category = isset($_POST['new_category']) ? sanitize_text_field($_POST['new_category']) : '';
        $title = sanitize_text_field($_POST['title']);
        $url = esc_url_raw($_POST['url']);
        $date = sanitize_text_field($_POST['date']);
        
        // 使用新分类（如果有）
        if (!empty($new_category)) {
            $category = $new_category;
        }
        
        // 创建新文章对象
        $new_article = array(
            'title' => $title,
            'url' => $url,
            'date' => $date
        );
        
        // 查找分类
        $category_found = false;
        
        foreach ($data['categories'] as $cat_index => $cat) {
            if ($cat['name'] === $category) {
                $category_found = true;
                
                if ($edit_category && $edit_article_index >= 0 && $edit_category === $category) {
                    // 更新现有文章
                    $data['categories'][$cat_index]['articles'][$edit_article_index] = $new_article;
                } else {
                    // 添加新文章
                    $data['categories'][$cat_index]['articles'][] = $new_article;
                }
                
                break;
            }
        }
        
        // 如果分类不存在，创建新分类
        if (!$category_found) {
            $data['categories'][] = array(
                'name' => $category,
                'articles' => array($new_article)
            );
        }
        
        // 保存更新后的数据
        update_option('article_collection_data', $data);
        
        // 自动更新HTML文件
        $html_result = article_collection_generate_html();
        $html_message = '';
        if ($html_result === true) {
            $html_message = ' HTML文件已自动更新。';
        } else {
            $html_message = ' 但HTML文件更新失败：' . $html_result;
        }
        
        // 显示成功消息
        echo '<div class="notice notice-success is-dismissible"><p>' . 
            ($edit_article ? '文章已成功更新！' : '文章已成功添加！') . $html_message . 
            ' <a href="' . admin_url('admin.php?page=article-collection') . '">查看所有文章</a></p></div>';
        
        // 重置编辑状态
        $edit_category = '';
        $edit_article_index = -1;
        $edit_article = null;
    }
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php echo $edit_article ? '编辑文章' : '添加新文章'; ?></h1>
        
        <hr class="wp-header-end">
        
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="category">分类</label></th>
                    <td>
                        <select name="category" id="category">
                            <option value="">-- 选择分类 --</option>
                            <?php foreach ($data['categories'] as $category): ?>
                                <option value="<?php echo esc_attr($category['name']); ?>" <?php selected($edit_category, $category['name']); ?>>
                                    <?php echo esc_html($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="add-category-btn" class="button">添加新分类</button>
                    </td>
                </tr>
                <tr id="new-category-row" style="display: none;">
                    <th scope="row"><label for="new-category">新分类名称</label></th>
                    <td>
                        <input name="new_category" type="text" id="new-category" value="" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="title">文章标题</label></th>
                    <td>
                        <input name="title" type="text" id="title" value="<?php echo $edit_article ? esc_attr($edit_article['title']) : ''; ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="url">文章链接</label></th>
                    <td>
                        <input name="url" type="url" id="url" value="<?php echo $edit_article ? esc_url($edit_article['url']) : ''; ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="date">发布日期</label></th>
                    <td>
                        <input name="date" type="date" id="date" value="<?php echo $edit_article ? esc_attr($edit_article['date']) : date('Y-m-d'); ?>" class="regular-text" required>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit_article" id="submit" class="button button-primary" value="<?php echo $edit_article ? '更新文章' : '添加文章'; ?>">
            </p>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // 添加新分类按钮点击事件
        $('#add-category-btn').on('click', function() {
            var newCategoryRow = $('#new-category-row');
            
            if (newCategoryRow.is(':visible')) {
                newCategoryRow.hide();
                $('#new-category').val('');
                $(this).text('添加新分类');
            } else {
                newCategoryRow.show();
                $('#new-category').focus();
                $(this).text('取消添加');
            }
        });
    });
    </script>
    <?php
}

// 注册短代码
add_shortcode('article_collection', 'article_collection_shortcode');

function article_collection_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => '', // 可选，指定显示特定分类
    ), $atts, 'article_collection');
    
    $data = get_option('article_collection_data', array('categories' => array()));
    
    // 开始输出缓冲
    ob_start();
    
    // 输出CSS
    ?>
    <style>
        .article-collection-container {
            display: flex;
            flex-wrap: wrap;
        }
        
        .article-collection-main {
            flex: 3;
            min-width: 300px;
        }
        
        .article-collection-sidebar {
            flex: 1;
            min-width: 200px;
            margin-left: 20px;
            padding: 15px;
            background: #f7f7f7;
            border-radius: 5px;
        }
        
        .article-collection-category {
            margin-bottom: 30px;
        }
        
        .article-collection-category h2 {
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        
        .article-collection-article {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .article-collection-article h3 {
            margin: 0 0 5px 0;
        }
        
        .article-collection-article-date {
            color: #666;
            font-size: 0.9em;
        }
        
        .article-collection-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .article-collection-sidebar li {
            margin-bottom: 10px;
        }
        
        .article-collection-sidebar a {
            text-decoration: none;
        }
        
        .article-collection-sidebar a:hover {
            text-decoration: underline;
        }
    </style>
    
    <div class="article-collection-container">
        <main class="article-collection-main">
            <?php
            $categories_to_show = $data['categories'];
            
            // 如果指定了分类，只显示该分类
            if (!empty($atts['category'])) {
                $filtered_categories = array();
                foreach ($categories_to_show as $category) {
                    if ($category['name'] === $atts['category']) {
                        $filtered_categories[] = $category;
                        break;
                    }
                }
                $categories_to_show = $filtered_categories;
            }
            
            if (empty($categories_to_show)) {
                echo '<p>暂无文章收藏。</p>';
            } else {
                foreach ($categories_to_show as $category) {
                    ?>
                    <div class="article-collection-category">
                        <h2><?php echo esc_html($category['name']); ?></h2>
                        
                        <?php if (empty($category['articles'])): ?>
                            <p>此分类下暂无文章。</p>
                        <?php else: ?>
                            <?php foreach ($category['articles'] as $article): ?>
                                <div class="article-collection-article">
                                    <h3><a href="<?php echo esc_url($article['url']); ?>" target="_blank"><?php echo esc_html($article['title']); ?></a></h3>
                                    <div class="article-collection-article-date"><?php echo esc_html($article['date']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php
                }
            }
            ?>
        </main>
        
        <aside class="article-collection-sidebar">
            <h2>分类</h2>
            <ul>
                <?php foreach ($data['categories'] as $category): ?>
                    <li>
                        <a href="#" class="article-collection-category-link" data-category="<?php echo esc_attr($category['name']); ?>">
                            <?php echo esc_html($category['name']); ?> (<?php echo count($category['articles']); ?>)
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 分类筛选功能
        var categoryLinks = document.querySelectorAll('.article-collection-category-link');
        var categories = document.querySelectorAll('.article-collection-category');
        
        categoryLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                var selectedCategory = this.getAttribute('data-category');
                
                // 显示/隐藏相应分类
                categories.forEach(function(category) {
                    var categoryName = category.querySelector('h2').textContent;
                    
                    if (selectedCategory === 'all' || categoryName === selectedCategory) {
                        category.style.display = 'block';
                    } else {
                        category.style.display = 'none';
                    }
                });
                
                // 更新活动状态
                categoryLinks.forEach(function(catLink) {
                    catLink.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    });
    </script>
    <?php
    
    // 返回输出缓冲内容
    return ob_get_clean();
}

// 添加使用说明页面
function article_collection_help_page() {
    ?>
    <div class="wrap">
        <h1>文章收藏插件使用说明</h1>
        
        <div class="card">
            <h2>插件简介</h2>
            <p>文章收藏插件允许您收集和分类各种文章链接，并通过简码或独立HTML页面展示给访问者。</p>
        </div>
        
        <div class="card">
            <h2>基本使用</h2>
            <ol>
                <li><strong>添加文章</strong> - 在"添加新文章"页面，填写文章标题、链接和日期，选择或创建分类。</li>
                <li><strong>管理文章</strong> - 在"所有文章"页面，您可以查看、编辑或删除已添加的文章。</li>
                <li><strong>前台展示</strong> - 使用简码 <code>[article_collection]</code> 在任何页面或文章中展示您的文章收藏。</li>
                <li><strong>独立HTML页面</strong> - 点击下方的"生成HTML文件"按钮，创建可独立访问的HTML页面。</li>
            </ol>
        </div>
        
        <div class="card">
            <h2>独立HTML页面</h2>
            <p>您可以生成独立的HTML文件，并将其放置在WordPress根目录下的other文件夹中。</p>
            <p>生成后，可以通过 <code>您的域名/other/articlecollection.html</code> 访问。</p>
            <p><strong>注意：</strong> 当您添加、编辑或删除文章时，HTML文件会自动更新。您也可以使用下面的按钮手动更新。</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('generate_html_action', 'generate_html_nonce'); ?>
                <input type="submit" name="generate_html" class="button button-primary" value="手动更新HTML文件">
            </form>
            
            <?php
            // 处理生成HTML文件的请求
            if (isset($_POST['generate_html']) && check_admin_referer('generate_html_action', 'generate_html_nonce')) {
                $result = article_collection_generate_html();
                if ($result === true) {
                    echo '<div class="notice notice-success is-dismissible"><p>HTML文件已成功生成！路径：' . get_home_url() . '/other/articlecollection.html</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>生成HTML文件失败：' . $result . '</p></div>';
                }
            }
            ?>
        </div>
        
        <div class="card">
            <h2>简码使用</h2>
            <p>插件提供以下简码用于前台展示：</p>
            <ul>
                <li><code>[article_collection]</code> - 显示所有分类和文章</li>
                <li><code>[article_collection category="技术"]</code> - 只显示指定分类的文章</li>
            </ul>
        </div>
        
        <div class="card">
            <h2>常见问题</h2>
            <div class="accordion">
                <h3>如何添加新分类？</h3>
                <div>
                    <p>在添加或编辑文章时，点击"添加新分类"按钮，然后输入新分类名称。</p>
                </div>
                
                <h3>如何修改文章顺序？</h3>
                <div>
                    <p>目前文章按添加顺序排列。未来版本将添加自定义排序功能。</p>
                </div>
                
                <h3>是否支持导入/导出数据？</h3>
                <div>
                    <p>目前不支持。未来版本将考虑添加此功能。</p>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin-bottom: 20px;
            padding: 20px;
        }
        
        .card h2 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .accordion h3 {
            background: #f5f5f5;
            padding: 10px 15px;
            margin: 0 0 1px 0;
            cursor: pointer;
            position: relative;
        }
        
        .accordion h3:after {
            content: "+";
            position: absolute;
            right: 15px;
            top: 10px;
        }
        
        .accordion h3.active:after {
            content: "-";
        }
        
        .accordion div {
            padding: 15px;
            background: #fafafa;
            display: none;
            margin-bottom: 10px;
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // 手风琴效果
        $('.accordion h3').click(function() {
            $(this).toggleClass('active');
            $(this).next('div').slideToggle();
        });
    });
    </script>
    <?php
}

// 添加生成HTML文件的函数
function article_collection_generate_html() {
    $data = get_option('article_collection_data', array('categories' => array()));
    
    // 创建other目录（如果不存在）
    $other_dir = ABSPATH . 'other';
    if (!file_exists($other_dir)) {
        if (!mkdir($other_dir, 0755, true)) {
            return '无法创建other目录';
        }
    }
    
    // HTML文件路径
    $html_file = $other_dir . '/articlecollection.html';
    
    // 开始生成HTML内容
    $html_content = '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章收藏</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: \'Microsoft YaHei\', sans-serif;
            line-height: 1.6;
            color: #e0e0e0;
            background-color: #121212;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        body.sidebar-open {
            overflow: hidden;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #e0e0e0;
            font-size: 24px;
        }

        .menu-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        #sidebar-toggle {
            background: #1e1e1e;
            border: none;
            border-radius: 4px;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        
        #sidebar-toggle span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: #e0e0e0;
            margin: 2px 0;
            transition: all 0.3s;
        }
        
        .article-collection-container {
            display: flex;
            gap: 30px;
        }
        
        .article-collection-main {
            flex: 1;
        }
        
        .article-collection-sidebar {
            width: 250px;
            background-color: #1e1e1e;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            position: fixed;
            top: 0;
            right: -280px;
            height: 100%;
            overflow-y: auto;
            transition: right 0.3s ease;
            z-index: 999;
        }

        .article-collection-sidebar.active {
            right: 0;
        }
        
        .article-collection-category {
            margin-bottom: 20px;
        }
        
        .article-collection-category h2 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #b0b0b0;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
        }
        
        .article-collection-article {
            padding: 8px 0;
            border-bottom: 1px solid #333;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .article-collection-article h3 {
            margin: 0;
            flex: 1;
        }
        
        .article-collection-article h3 a {
            color: #64b5f6;
            text-decoration: none;
        }
        
        .article-collection-article h3 a:hover {
            text-decoration: underline;
            color: #90caf9;
        }
        
        .article-collection-article-date {
            color: #888;
            font-size: 12px;
            text-align: right;
            margin-left: 10px;
        }
        
        .article-collection-sidebar h2 {
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #333;
            color: #e0e0e0;
        }
        
        .article-collection-sidebar ul {
            list-style: none;
        }
        
        .article-collection-sidebar li {
            margin-bottom: 8px;
        }
        
        .article-collection-sidebar a {
            color: #b0b0b0;
            text-decoration: none;
            display: block;
            padding: 5px;
            border-radius: 3px;
        }
        
        .article-collection-sidebar a:hover,
        .article-collection-sidebar a.active {
            background-color: #2a2a2a;
            color: #4CAF50;
        }
        
        .article-collection-footer {
            text-align: center;
            margin-top: 30px;
            color: #888;
            font-size: 12px;
        }
        
        .article-list {
            list-style: none;
            background-color: #1e1e1e;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            padding: 10px 15px;
            margin-bottom: 15px;
        }

        .back-to-site {
            margin-bottom: 20px;
        }
        
        .back-to-site a {
            display: inline-flex;
            align-items: center;
            color: #64b5f6;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 12px;
            background-color: #1e1e1e;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .back-to-site a:hover {
            background-color: #2a2a2a;
            color: #90caf9;
            transform: translateX(-5px);
        }
        
        .back-to-site svg {
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .article-collection-container {
                flex-direction: column;
            }
            
            article-collection-sidebar {
                width: 80%;
            }
        }
    </style>
</head>
<body>
    <div class="back-to-site">
        <a href="https://ztgdblog.icu/">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            返回网站
        </a>
    </div>
    <br>
    <div class="article-collection-container">
        <main class="article-collection-main">';
    
    // 添加文章内容
    if (empty($data['categories'])) {
        $html_content .= '<p class="no-articles">暂无文章收藏。</p>';
    } else {
        // 创建一个包含所有文章的数组
        $all_articles = array();
        
        foreach ($data['categories'] as $category) {
            if (!empty($category['articles'])) {
                foreach ($category['articles'] as $article) {
                    // 添加分类信息到文章
                    $article['category'] = $category['name'];
                    $article['category_id'] = sanitize_title($category['name']);
                    $all_articles[] = $article;
                }
            }
        }
        
        // 显示所有文章的列表（默认视图）
        $html_content .= '<div class="article-collection-category" id="category-all">
            <div class="article-list">';
        
        if (empty($all_articles)) {
            $html_content .= '<p class="no-articles">暂无文章。</p>';
        } else {
            foreach ($all_articles as $article) {
                $html_content .= '<div class="article-collection-article" data-category="' . esc_attr($article['category_id']) . '">
                    <h3><a href="' . esc_url($article['url']) . '" target="_blank">' . esc_html($article['title']) . '</a></h3>
                    <div class="article-collection-article-date">' . esc_html($article['date']) . '</div>
                </div>';
            }
        }
        
        $html_content .= '</div></div>';
    }
    
    $html_content .= '</main>

        <div class="menu-toggle">
            <button id="sidebar-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        
        <aside class="article-collection-sidebar">
            <h2>有趣的文章</h2>
            <ul>
                <li><a href="#" class="article-collection-category-link" data-category="all">全部分类</a></li>';
    
    foreach ($data['categories'] as $category) {
        $html_content .= '<li><a href="#category-' . esc_attr(sanitize_title($category['name'])) . '" class="article-collection-category-link" data-category="' . esc_attr(sanitize_title($category['name'])) . '">' . esc_html($category['name']) . ' (' . count($category['articles']) . ')</a></li>';
    }
    
    $html_content .= '</ul>
        </aside>
    </div>
    
    <div class="article-collection-footer">
        <p style="font-color: #96CDCD">该插件由<a href="https://github.com/1999684/argon-plugins">ZTGD</a>制作</p>
    </div>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // 侧边栏切换功能
        var sidebarToggle = document.getElementById("sidebar-toggle");
        var sidebar = document.querySelector(".article-collection-sidebar");
        var body = document.body;

        sidebarToggle.addEventListener("click", function() {
            sidebar.classList.toggle("active");
            body.classList.toggle("sidebar-open");
            
            // 切换汉堡按钮样式
            var spans = this.querySelectorAll("span");
            if (sidebar.classList.contains("active")) {
                spans[0].style.transform = "rotate(45deg) translate(5px, 5px)";
                spans[1].style.opacity = "0";
                spans[2].style.transform = "rotate(-45deg) translate(5px, -5px)";
            } else {
                spans[0].style.transform = "none";
                spans[1].style.opacity = "1";
                spans[2].style.transform = "none";
            }
        });
        // 分类筛选功能
        var categoryLinks = document.querySelectorAll(".article-collection-category-link");
        var articles = document.querySelectorAll(".article-collection-article");

        categoryLinks.forEach(function(link) {
            link.addEventListener("click", function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove("active");
                    body.classList.remove("sidebar-open");
                    
                    // 恢复汉堡按钮样式
                    var spans = sidebarToggle.querySelectorAll("span");
                    spans[0].style.transform = "none";
                    spans[1].style.opacity = "1";
                    spans[2].style.transform = "none";
                }
            });
        });
        
        // 默认显示全部
        document.querySelector("[data-category=\'all\']").classList.add("active");
        
        categoryLinks.forEach(function(link) {
            link.addEventListener("click", function(e) {
                var selectedCategory = this.getAttribute("data-category");
                
                if (selectedCategory === "all") {
                    articles.forEach(function(article) {
                        article.style.display = "flex";
                    });
                } else {
                    articles.forEach(function(article) {
                        if (article.getAttribute("data-category") === selectedCategory) {
                            article.style.display = "flex";
                        } else {
                            article.style.display = "none";
                        }
                    });
                }
                
                // 更新活动状态
                categoryLinks.forEach(function(catLink) {
                    catLink.classList.remove("active");
                });
                this.classList.add("active");
                
                // 阻止默认行为
                e.preventDefault();
            });
        });
    });
    </script>
</body>
</html>';

    // 写入文件
    if (file_put_contents($html_file, $html_content)) {
        return true;
    } else {
        return '无法写入HTML文件';
    }
}