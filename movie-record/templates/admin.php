<div class="wrap movie-record-admin">
    <h1>影视记录管理</h1>
    
    <!-- 使用说明 -->
    <div class="card instruction">
        <h2>使用说明</h2>
        <div class="instruction-content">
            <h3>基本使用</h3>
            <ol>
                <li>在此页面添加您想要记录的影视作品信息，包括名称、分类、海报和链接。</li>
                <li>添加完成后，可以在前台页面通过shortcode显示影视记录列表。</li>
                <li>支持按分类筛选显示不同类型的影视作品。</li>
            </ol>
            
            <h3>Shortcode使用方法</h3>
            <p>在任意页面或文章中使用以下shortcode来显示影视记录：</p>
            <code>[movie_records]</code>
            
            <h4>可选参数：</h4>
            <ul>
                <li><strong>category</strong>：指定显示特定分类的记录
                    <br>示例：<code>[movie_records category="movie"]</code> - 仅显示电影分类
                </li>
                <li><strong>limit</strong>：限制显示记录的数量
                    <br>示例：<code>[movie_records limit="6"]</code> - 显示最新6条记录
                </li>
                <li><strong>order</strong>：自定义列数
                    <br>示例： <code>[movie_records columns="3"]</code>
                    <br>可以与分类筛选组合： <code>[movie_records category="movie" columns="5"]</code>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- 添加新记录表单 -->
    <div class="card add-new-record">
        <h2>添加新记录</h2>
        <form id="add-movie-form" method="post">
            <div class="form-group">
                <label for="title">名称</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="category">分类</label>
                <select id="category" name="category" required>
                    <option value="">请选择分类</option>
                    <option value="movie">电影</option>
                    <option value="anime">动漫</option>
                    <option value="variety">综艺</option>
                    <option value="drama">电视剧</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="poster">海报</label>
                <div class="poster-upload">
                    <input type="hidden" id="poster_url" name="poster_url" required>
                    <div id="poster-preview"></div>
                    <button type="button" class="button button-secondary" id="upload-poster-btn">选择图片</button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="link_url">链接</label>
                <input type="url" id="link_url" name="link_url" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="button button-primary">保存记录</button>
            </div>
        </form>
    </div>
    
    <!-- 记录列表 -->
    <div class="card record-list">
        <h2>所有记录</h2>
        <div class="tablenav top">
            <div class="alignleft actions">
                <select id="filter-category">
                    <option value="">所有分类</option>
                    <option value="movie">电影</option>
                    <option value="anime">动漫</option>
                    <option value="variety">综艺</option>
                    <option value="drama">电视剧</option>
                </select>
                <button type="button" class="button" id="filter-records">筛选</button>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>海报</th>
                    <th>名称</th>
                    <th>分类</th>
                    <th>链接</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="records-list">
                <!-- 记录将通过JavaScript动态加载 -->
            </tbody>
        </table>
    </div>
</div>

<!-- 编辑记录模态框 -->
<div id="edit-record-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>编辑记录</h2>
        <form id="edit-movie-form" method="post">
            <input type="hidden" id="edit_id" name="edit_id">
            <div class="form-group">
                <label for="edit_title">名称</label>
                <input type="text" id="edit_title" name="edit_title" required>
            </div>
            
            <div class="form-group">
                <label for="edit_category">分类</label>
                <select id="edit_category" name="edit_category" required>
                    <option value="movie">电影</option>
                    <option value="anime">动漫</option>
                    <option value="variety">综艺</option>
                    <option value="drama">电视剧</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_poster">海报</label>
                <div class="poster-upload">
                    <input type="hidden" id="edit_poster_url" name="edit_poster_url" required>
                    <div id="edit-poster-preview"></div>
                    <button type="button" class="button button-secondary" id="edit-upload-poster-btn">选择图片</button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="edit_link_url">链接</label>
                <input type="url" id="edit_link_url" name="edit_link_url" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="button button-primary">更新记录</button>
            </div>
        </form>
    </div>
</div>