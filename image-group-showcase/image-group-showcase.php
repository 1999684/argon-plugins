<?php
/**
 * Plugin Name: Image Group Showcase
 * Description: 创建图片组并通过简码展示，鼠标悬停显示简介，点击查看图片展示。
 * Version: 1.0
 * Author: ZTGD
 * Text Domain: image-group-showcase
 */

// 如果直接访问此文件，则退出
if (!defined('ABSPATH')) {
    exit;
}

// 注册激活钩子
register_activation_hook(__FILE__, 'image_group_showcase_activate');

function image_group_showcase_activate() {
    // 创建自定义表格
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // 图片组表
    $table_groups = $wpdb->prefix . 'image_groups';
    $sql_groups = "CREATE TABLE $table_groups (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    // 图片表
    $table_images = $wpdb->prefix . 'image_group_images';
    $sql_images = "CREATE TABLE $table_images (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        group_id mediumint(9) NOT NULL,
        image_url text NOT NULL,
        sort_order int(11) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id),
        KEY group_id (group_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_groups);
    dbDelta($sql_images);
}

// 注册停用钩子
register_deactivation_hook(__FILE__, 'image_group_showcase_deactivate');

function image_group_showcase_deactivate() {
    // 停用时不删除数据，如需删除请使用卸载钩子
}

// 注册卸载钩子
register_uninstall_hook(__FILE__, 'image_group_showcase_uninstall');

function image_group_showcase_uninstall() {
    // 卸载时删除数据表
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}image_group_images");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}image_groups");
}

// 添加管理菜单
add_action('admin_menu', 'image_group_showcase_menu');

function image_group_showcase_menu() {
    add_menu_page(
        '图片组展示', 
        '图片组展示', 
        'manage_options', 
        'image-group-showcase', 
        'image_group_showcase_page',
        'dashicons-format-gallery',
        30
    );
    
    add_submenu_page(
        'image-group-showcase',
        '所有图片组',
        '所有图片组',
        'manage_options',
        'image-group-showcase',
        'image_group_showcase_page'
    );
    
    add_submenu_page(
        'image-group-showcase',
        '添加新图片组',
        '添加新图片组',
        'manage_options',
        'image-group-add-new',
        'image_group_add_new_page'
    );
}

// 主页面 - 显示所有图片组
function image_group_showcase_page() {
    global $wpdb;
    
    // 处理删除操作
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $group_id = intval($_GET['id']);
        $wpdb->delete($wpdb->prefix . 'image_group_images', array('group_id' => $group_id));
        $wpdb->delete($wpdb->prefix . 'image_groups', array('id' => $group_id));
        echo '<div class="notice notice-success is-dismissible"><p>图片组已成功删除！</p></div>';
    }
    
    // 获取所有图片组
    $groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}image_groups ORDER BY id DESC");
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">图片组展示</h1>
        <a href="<?php echo admin_url('admin.php?page=image-group-add-new'); ?>" class="page-title-action">添加新图片组</a>
        
        <hr class="wp-header-end">
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名称</th>
                    <th>简介</th>
                    <th>图片数量</th>
                    <th>简码</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($groups)): ?>
                    <tr>
                        <td colspan="6">暂无图片组，请添加新的图片组。</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($groups as $group): ?>
                        <?php 
                        $image_count = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}image_group_images WHERE group_id = %d",
                            $group->id
                        ));
                        ?>
                        <tr>
                            <td><?php echo $group->id; ?></td>
                            <td><?php echo esc_html($group->name); ?></td>
                            <td><?php echo esc_html(wp_trim_words($group->description, 10)); ?></td>
                            <td><?php echo $image_count; ?></td>
                            <td><code>[image_group id="<?php echo $group->id; ?>"]</code></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=image-group-add-new&edit=' . $group->id); ?>">编辑</a> | 
                                <a href="<?php echo admin_url('admin.php?page=image-group-showcase&action=delete&id=' . $group->id); ?>" 
                                   onclick="return confirm('确定要删除此图片组吗？此操作不可撤销。');">删除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// 添加/编辑图片组页面
function image_group_add_new_page() {
    global $wpdb;
    
    $edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
    $group = null;
    $images = array();
    
    if ($edit_id) {
        $group = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}image_groups WHERE id = %d",
            $edit_id
        ));
        
        if ($group) {
            $images = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}image_group_images WHERE group_id = %d ORDER BY sort_order ASC",
                $edit_id
            ));
        }
    }
    
    // 处理表单提交
    if (isset($_POST['submit_image_group'])) {
        $name = sanitize_text_field($_POST['group_name']);
        $description = wp_kses_post($_POST['group_description']);
        $image_urls = isset($_POST['image_urls']) ? $_POST['image_urls'] : array();
        
        // 验证
        if (empty($name)) {
            echo '<div class="notice notice-error is-dismissible"><p>请输入图片组名称！</p></div>';
        } else {
            if ($edit_id) {
                // 更新图片组
                $wpdb->update(
                    $wpdb->prefix . 'image_groups',
                    array(
                        'name' => $name,
                        'description' => $description
                    ),
                    array('id' => $edit_id)
                );
                
                // 删除旧图片
                $wpdb->delete($wpdb->prefix . 'image_group_images', array('group_id' => $edit_id));
                
                // 添加新图片
                if (!empty($image_urls)) {
                    foreach ($image_urls as $index => $url) {
                        if (!empty($url)) {
                            $wpdb->insert(
                                $wpdb->prefix . 'image_group_images',
                                array(
                                    'group_id' => $edit_id,
                                    'image_url' => esc_url_raw($url),
                                    'sort_order' => $index
                                )
                            );
                        }
                    }
                }
                
                echo '<div class="notice notice-success is-dismissible"><p>图片组已成功更新！</p></div>';
                
                // 重新获取数据
                $group = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}image_groups WHERE id = %d",
                    $edit_id
                ));
                
                $images = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}image_group_images WHERE group_id = %d ORDER BY sort_order ASC",
                    $edit_id
                ));
            } else {
                // 创建新图片组
                $wpdb->insert(
                    $wpdb->prefix . 'image_groups',
                    array(
                        'name' => $name,
                        'description' => $description
                    )
                );
                
                $new_group_id = $wpdb->insert_id;
                
                // 添加图片
                if (!empty($image_urls)) {
                    foreach ($image_urls as $index => $url) {
                        if (!empty($url)) {
                            $wpdb->insert(
                                $wpdb->prefix . 'image_group_images',
                                array(
                                    'group_id' => $new_group_id,
                                    'image_url' => esc_url_raw($url),
                                    'sort_order' => $index
                                )
                            );
                        }
                    }
                }
                
                echo '<div class="notice notice-success is-dismissible"><p>图片组已成功创建！<a href="' . admin_url('admin.php?page=image-group-showcase') . '">查看所有图片组</a></p></div>';
                
                // 重定向到编辑页面
                echo '<script>window.location.href = "' . admin_url('admin.php?page=image-group-add-new&edit=' . $new_group_id) . '";</script>';
                return;
            }
        }
    }
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php echo $edit_id ? '编辑图片组' : '添加新图片组'; ?></h1>
        
        <hr class="wp-header-end">
        
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="group_name">图片组名称</label></th>
                    <td>
                        <input name="group_name" type="text" id="group_name" value="<?php echo $group ? esc_attr($group->name) : ''; ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="group_description">图片组简介</label></th>
                    <td>
                        <textarea name="group_description" id="group_description" class="large-text" rows="5"><?php echo $group ? esc_textarea($group->description) : ''; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">图片</th>
                    <td>
                        <div id="image-container">
                            <?php if (!empty($images)): ?>
                                <?php foreach ($images as $index => $image): ?>
                                    <div class="image-row">
                                        <input type="text" name="image_urls[]" value="<?php echo esc_url($image->image_url); ?>" class="regular-text image-url-input">
                                        <button type="button" class="button upload-image-button">选择图片</button>
                                        <button type="button" class="button button-secondary remove-image-button">删除</button>
                                        <div class="image-preview">
                                            <img src="<?php echo esc_url($image->image_url); ?>" style="max-width: 150px; max-height: 150px; margin-top: 10px;">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="image-row">
                                    <input type="text" name="image_urls[]" value="" class="regular-text image-url-input">
                                    <button type="button" class="button upload-image-button">选择图片</button>
                                    <button type="button" class="button button-secondary remove-image-button">删除</button>
                                    <div class="image-preview"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" id="add-image" class="button">添加更多图片</button>
                        <p class="description">可以直接输入图片URL或从媒体库选择图片。至少需要一张图片。</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit_image_group" id="submit" class="button button-primary" value="<?php echo $edit_id ? '更新图片组' : '创建图片组'; ?>">
            </p>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // 添加更多图片
        $('#add-image').on('click', function() {
            var newRow = `
                <div class="image-row">
                    <input type="text" name="image_urls[]" value="" class="regular-text image-url-input">
                    <button type="button" class="button upload-image-button">选择图片</button>
                    <button type="button" class="button button-secondary remove-image-button">删除</button>
                    <div class="image-preview"></div>
                </div>
            `;
            $('#image-container').append(newRow);
        });
        
        // 删除图片行
        $(document).on('click', '.remove-image-button', function() {
            var totalRows = $('.image-row').length;
            if (totalRows > 1) {
                $(this).closest('.image-row').remove();
            } else {
                alert('至少需要一张图片！');
            }
        });
        
        // 媒体上传
        $(document).on('click', '.upload-image-button', function() {
            var button = $(this);
            var urlInput = button.siblings('.image-url-input');
            var previewDiv = button.siblings('.image-preview');
            
            var frame = wp.media({
                title: '选择或上传图片',
                button: {
                    text: '使用此图片'
                },
                multiple: false
            });
            
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                urlInput.val(attachment.url);
                previewDiv.html('<img src="' + attachment.url + '" style="max-width: 150px; max-height: 150px; margin-top: 10px;">');
            });
            
            frame.open();
        });
        
        // 当URL输入框变化时更新预览
        $(document).on('change', '.image-url-input', function() {
            var urlInput = $(this);
            var previewDiv = urlInput.siblings('.image-preview');
            var imageUrl = urlInput.val();
            
            if (imageUrl) {
                previewDiv.html('<img src="' + imageUrl + '" style="max-width: 150px; max-height: 150px; margin-top: 10px;">');
            } else {
                previewDiv.empty();
            }
        });
    });
    </script>
    <?php
}

// 注册简码
add_shortcode('image_group', 'image_group_shortcode');

function image_group_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts, 'image_group');
    
    $group_id = intval($atts['id']);
    
    if (!$group_id) {
        return '<p>错误：未指定图片组ID</p>';
    }
    
    global $wpdb;
    
    // 获取图片组信息
    $group = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}image_groups WHERE id = %d",
        $group_id
    ));
    
    if (!$group) {
        return '<p>错误：找不到指定的图片组</p>';
    }
    
    // 获取图片组中的图片
    $images = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}image_group_images WHERE group_id = %d ORDER BY sort_order ASC",
        $group_id
    ));
    
    if (empty($images)) {
        return '<p>此图片组中没有图片</p>';
    }
    
    // 生成唯一ID
    $unique_id = 'image-group-' . $group_id . '-' . uniqid();
    
    // 输出CSS
    $output = '
    <style>
        .image-group-container {
            position: relative;
            display: inline-block;
            margin: 10px;
            overflow: hidden;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .image-group-container:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .image-group-container img {
            display: block;
            width: 100%;
            height: auto;
            transition: transform 0.3s ease;
        }
        
        .image-group-container:hover img {
            transform: scale(1.05);
        }
        
        .image-group-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 10px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .image-group-container:hover .image-group-info {
            transform: translateY(0);
        }
        
        .image-group-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        
        .image-group-description {
            font-size: 14px;
            margin: 0;
        }
        
        .image-group-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
        }
        
        .image-group-modal-content {
            position: relative;
            margin: auto;
            padding: 0;
            width: 80%;
            max-width: 1200px;
            height: 90%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .image-group-modal-image-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .image-group-modal-image {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }
        
        .image-group-modal-close {
            position: absolute;
            top: 10px;
            right: 25px;
            color: #f1f1f1;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .image-group-modal-prev,
        .image-group-modal-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 30px;
            transition: 0.3s ease;
            border-radius: 50%;
            user-select: none;
            cursor: pointer;
            background-color: rgba(0,0,0,0.3);
            z-index: 10;
        }
        
        .image-group-modal-next {
            right: -70px;
        }
        
        .image-group-modal-prev {
            left: -70px;
        }
        
        .image-group-modal-prev:hover,
        .image-group-modal-next:hover {
            background-color: rgba(0,0,0,0.8);
        }
        
        .image-group-modal-caption {
            text-align: center;
            padding: 10px 0;
            color: white;
        }
        
        .image-group-modal-counter {
            color: #ccc;
            font-size: 14px;
            padding: 10px 0;
            text-align: center;
        }
    </style>
    ';
    
    // 输出HTML
    $output .= '<div class="image-group-container" data-group-id="' . $group_id . '" id="' . $unique_id . '">';
    $output .= '<img src="' . esc_url($images[0]->image_url) . '" alt="' . esc_attr($group->name) . '">';
    $output .= '<div class="image-group-info">';
    $output .= '<h3 class="image-group-title">' . esc_html($group->name) . '</h3>';
    $output .= '<p class="image-group-description">' . esc_html($group->description) . '</p>';
    $output .= '</div>';
    $output .= '</div>';
    
    // 模态框
    $output .= '<div id="' . $unique_id . '-modal" class="image-group-modal">';
    $output .= '<span class="image-group-modal-close">&times;</span>';
    $output .= '<div class="image-group-modal-content">';
    $output .= '<div class="image-group-modal-image-container">';
    $output .= '<a class="image-group-modal-prev">&#10094;</a>';
    $output .= '<img class="image-group-modal-image" id="' . $unique_id . '-modal-image">';
    $output .= '<a class="image-group-modal-next">&#10095;</a>';
    $output .= '</div>';
    $output .= '<div class="image-group-modal-caption">';
    $output .= '<h3>' . esc_html($group->name) . '</h3>';
    $output .= '<p id="' . $unique_id . '-modal-counter" class="image-group-modal-counter"></p>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
    
    // JavaScript
    $output .= '<script>
    (function() {
        var container = document.getElementById("' . $unique_id . '");
        var modal = document.getElementById("' . $unique_id . '-modal");
        var modalImg = document.getElementById("' . $unique_id . '-modal-image");
        var modalCounter = document.getElementById("' . $unique_id . '-modal-counter");
        var closeBtn = modal.querySelector(".image-group-modal-close");
        var prevBtn = modal.querySelector(".image-group-modal-prev");
        var nextBtn = modal.querySelector(".image-group-modal-next");
        
        var images = ' . json_encode(array_map(function($img) { return $img->image_url; }, $images)) . ';
        var currentIndex = 0;
        
        container.addEventListener("click", function() {
            modal.style.display = "block";
            showImage(0);
        });
        
        closeBtn.addEventListener("click", function() {
            modal.style.display = "none";
        });
        
        prevBtn.addEventListener("click", function() {
            showImage(currentIndex - 1);
        });
        
        nextBtn.addEventListener("click", function() {
            showImage(currentIndex + 1);
        });
        
        window.addEventListener("click", function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
        
        document.addEventListener("keydown", function(event) {
            if (modal.style.display === "block") {
                if (event.key === "ArrowLeft") {
                    showImage(currentIndex - 1);
                } else if (event.key === "ArrowRight") {
                    showImage(currentIndex + 1);
                } else if (event.key === "Escape") {
                    modal.style.display = "none";
                }
            }
        });
        
        function showImage(index) {
            if (index < 0) {
                index = images.length - 1;
            } else if (index >= images.length) {
                index = 0;
            }
            
            currentIndex = index;
            modalImg.src = images[currentIndex];
            modalCounter.textContent = "图片 " + (currentIndex + 1) + " / " + images.length;
        }
    })();
    </script>';
    
    return $output;
}

// 添加样式和脚本
add_action('admin_enqueue_scripts', 'image_group_showcase_admin_scripts');

function image_group_showcase_admin_scripts($hook) {
    if (strpos($hook, 'image-group') !== false) {
        wp_enqueue_media();
    }
}