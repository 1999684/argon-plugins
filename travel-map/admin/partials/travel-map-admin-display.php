<?php
// 获取标记点数据
$admin = new Travel_Map_Admin($this->plugin_name, $this->version);
$markers = $admin->get_markers();
?>
<div class="wrap travel-map-admin">
    <h1>旅行地图管理</h1>
    
    <div class="notice notice-info is-dismissible">
        <p>请在地图上<strong>点击</strong>选择位置来添加新的标记点。如需设置高德地图 API Key，请前往 <a href="<?php echo admin_url('admin.php?page=travel-map-settings'); ?>">设置页面</a>。</p>
        <p>注意：对于使用Argon主题的用户，需要添加以下代码片段到页面内</p>
        <pre><code><?php echo htmlspecialchars('<script>
// 处理后退/前进时的缓存刷新
window.addEventListener(\'pageshow\', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});

// 首次加载时通过修改URL触发刷新
if (!window.location.search.includes(\'reloaded\')) {
    window.location.search = \'reloaded=1\';
}
</script>'); ?></code></pre>
    </div>
    
    <div class="travel-map-container">
        <div class="travel-map-sidebar">
            <div class="travel-map-section">
                <h2>添加新标记点</h2>
                <div class="form-group">
                    <label for="marker-title">标记点名称</label>
                    <input type="text" id="marker-title" class="regular-text" placeholder="输入标记点名称">
                </div>
                <div class="form-group">
                    <label>位置</label>
                    <p id="selected-position">请在地图上点击选择位置</p>
                </div>
                <button id="add-marker" class="button button-primary" disabled>添加标记点</button>
                <button id="clear-selection" class="button">清除选择</button>
            </div>
            
            <div class="travel-map-section">
                <h2>已添加的标记点</h2>
                <div id="marker-list">
                    <?php if (empty($markers)): ?>
                        <p>暂无标记点，请在地图上点击添加</p>
                    <?php else: ?>
                        <?php foreach ($markers as $marker): ?>
                            <div class="marker-item" data-id="<?php echo esc_attr($marker['id']); ?>">
                                <div class="marker-title"><?php echo esc_html($marker['title']); ?></div>
                                <div class="marker-position">
                                    位置: <?php echo esc_html(number_format($marker['lng'], 6)); ?>, <?php echo esc_html(number_format($marker['lat'], 6)); ?>
                                </div>
                                <div class="marker-actions">
                                    <button class="button add-link-btn">添加链接</button>
                                    <button class="button button-link-delete delete-marker-btn">删除标记点</button>
                                </div>
                                
                                <?php if (!empty($marker['links'])): ?>
                                    <div class="link-list">
                                        <div class="link-list-title">关联链接:</div>
                                        <?php foreach ($marker['links'] as $link): ?>
                                            <div class="link-item" data-id="<?php echo esc_attr($link['id']); ?>">
                                                <div class="link-content">
                                                    <strong><?php echo esc_html($link['title']); ?></strong>: 
                                                    <a href="<?php echo esc_url($link['url']); ?>" target="_blank"><?php echo esc_url($link['url']); ?></a>
                                                </div>
                                                <button class="button button-link-delete delete-link-btn">删除</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="travel-map-section">
                <h2>使用方法</h2>
                <p>在任意页面或文章中使用以下短代码显示地图:</p>
                <code>[travel_map]</code>
                <p>可选参数:</p>
                <ul>
                    <li><code>height</code> - 地图高度，默认 600px</li>
                    <li><code>zoom</code> - 地图缩放级别，默认 4</li>
                    <li><code>center</code> - 地图中心点坐标，格式为"经度,纬度"，默认北京</li>
                </ul>
                <p>示例:</p>
                <code>[travel_map height="500px" zoom="5" center="120.15,30.28"]</code>
            </div>
        </div>
        
        <div class="travel-map-map" id="admin-map"></div>
    </div>
    
    <!-- 链接表单对话框 -->
    <div id="link-form-dialog" class="travel-map-dialog" style="display:none;">
        <div class="travel-map-dialog-content">
            <h3>添加链接到 <span id="current-marker-title"></span></h3>
            <div class="form-group">
                <label for="link-title">链接标题</label>
                <input type="text" id="link-title" class="regular-text" placeholder="输入链接标题">
            </div>
            <div class="form-group">
                <label for="link-url">链接地址</label>
                <input type="text" id="link-url" class="regular-text" placeholder="输入链接地址">
            </div>
            <div class="dialog-buttons">
                <button id="add-link" class="button button-primary">添加链接</button>
                <button id="cancel-add-link" class="button">取消</button>
            </div>
        </div>
    </div>
</div>