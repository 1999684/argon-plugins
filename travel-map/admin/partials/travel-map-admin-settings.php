<div class="wrap">
    <h1>旅行地图设置</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('travel_map_save_settings', 'travel_map_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="amap_api_key">高德地图 API Key</label>
                </th>
                <td>
                    <input type="text" id="amap_api_key" name="amap_api_key" value="<?php echo esc_attr($amap_api_key); ?>" class="regular-text">
                    <p class="description">
                        请输入您的高德地图 JavaScript API Key。
                        <a href="https://lbs.amap.com/api/javascript-api/guide/abc/prepare" target="_blank">如何获取 API Key?</a>
                    </p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="保存设置">
        </p>
    </form>
</div>