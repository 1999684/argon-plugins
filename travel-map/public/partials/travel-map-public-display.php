<div class="travel-map-container">
    <div class="travel-map-wrapper" id="travel-map" style="height: <?php echo esc_attr($atts['height']); ?>"></div>
    <div class="travel-map-links" id="travel-map-links">
        <h3>点击地图上的标记点查看相关链接</h3>
    </div>
</div>

<script>
    // 定义地图配置变量（使用window确保在全局作用域）
    window.travelMapConfig = {
        zoom: <?php echo esc_js($atts['zoom']); ?>,
        center: "<?php echo esc_js($center_lng); ?>,<?php echo esc_js($center_lat); ?>"
    };
</script>