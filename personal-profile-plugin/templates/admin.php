<div class="wrap personal-profile-admin">
    <h1>个人信息管理</h1>
    
    <div class="tabs">
        <div class="tab active" data-tab="identity">身份管理</div>
        <div class="tab" data-tab="project">项目管理</div>
        <div class="tab" data-tab="settings">页面设置</div>
    </div>
    
    <div class="tab-content active" id="identity-content">
        <!-- 现有的身份管理内容 -->
        <h2>我的身份</h2>
        <div class="item-list" id="identity-list">
            <!-- 身份列表将通过JavaScript动态生成 -->
        </div>
        <button class="button button-primary" id="add-identity-btn">添加新身份</button>
    </div>
    
    <div class="tab-content" id="project-content">
        <!-- 现有的项目管理内容 -->
        <h2>我的项目</h2>
        <div class="item-list" id="project-list">
            <!-- 项目列表将通过JavaScript动态生成 -->
        </div>
        <button class="button button-primary" id="add-project-btn">添加新项目</button>
    </div>
    
    <!-- 新增的页面设置内容 -->
    <div class="tab-content" id="settings-content">
        <h2>页面设置</h2>
        <form id="settings-form">
            <div class="form-group">
                <label for="background-image">背景图片</label>
                <div class="image-field">
                    <input type="text" id="background-image" class="regular-text" value="<?php echo isset($data['settings']['background_image']) ? $data['settings']['background_image'] : plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>" required>
                    <button type="button" class="button upload-image-btn" data-target="background-image">选择图片</button>
                </div>
                <p class="description">设置页面的背景图片</p>
            </div>
            
            <div class="form-group">
                <label for="left-bg-image">左侧卡片背景图片</label>
                <div class="image-field">
                    <input type="text" id="left-bg-image" class="regular-text" value="<?php echo isset($data['settings']['left_bg_image']) ? $data['settings']['left_bg_image'] : plugin_dir_url(dirname(__FILE__)) . 'assets/images/left-bg.jpg'; ?>" required>
                    <button type="button" class="button upload-image-btn" data-target="left-bg-image">选择图片</button>
                </div>
                <p class="description">设置左侧卡片的背景图片（将以半透明方式显示）</p>
            </div>
            
            <div class="form-group">
                <label for="profile-image">个人头像</label>
                <div class="image-field">
                    <input type="text" id="profile-image" class="regular-text" value="<?php echo isset($data['settings']['profile_image']) ? $data['settings']['profile_image'] : plugin_dir_url(dirname(__FILE__)) . 'assets/images/avatar.jpg'; ?>" required>
                    <button type="button" class="button upload-image-btn" data-target="profile-image">选择图片</button>
                </div>
                <p class="description">设置显示在左侧的个人头像</p>
            </div>
            
            <div class="form-group">
                <label for="personal-website">个人网站</label>
                <input type="url" id="personal-website" class="regular-text" value="<?php echo isset($data['settings']['personal_website']) ? $data['settings']['personal_website'] : 'https://example.com'; ?>" required>
                <p class="description">设置点击头像后跳转的个人网站地址</p>
            </div>
            <h3>默认身份内容</h3>
            <div class="form-group">
                <label for="default-identity-title">标题</label>
                <input type="text" id="default-identity-title" class="regular-text" value="<?php echo isset($data['identity']['default']['title']) ? $data['identity']['default']['title'] : '我的身份'; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="default-identity-description">简短描述</label>
                <textarea id="default-identity-description" class="large-text" rows="3" required><?php echo isset($data['identity']['default']['description']) ? $data['identity']['default']['description'] : '我拥有多重身份，包括开发者、设计师和学生。每个身份代表了我不同的技能和经历。'; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="default-identity-image">图片路径</label>
                <div class="image-field">
                    <input type="text" id="default-identity-image" class="regular-text" value="<?php echo isset($data['identity']['default']['image']) ? $data['identity']['default']['image'] : plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>" required>
                    <button type="button" class="button upload-image-btn" data-target="default-identity-image">选择图片</button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="default-identity-article">详细内容</label>
                <textarea id="default-identity-article" class="large-text" rows="4" required><?php echo isset($data['identity']['default']['article']) ? $data['identity']['default']['article'] : '请从上方选择一个具体的身份，了解我在该领域的专长和经历。'; ?></textarea>
            </div>
            
            <h3>默认项目内容</h3>
            <div class="form-group">
                <label for="default-project-title">标题</label>
                <input type="text" id="default-project-title" class="regular-text" value="<?php echo isset($data['project']['default']['title']) ? $data['project']['default']['title'] : '我的项目'; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="default-project-description">简短描述</label>
                <textarea id="default-project-description" class="large-text" rows="3" required><?php echo isset($data['project']['default']['description']) ? $data['project']['default']['description'] : '我参与和开发了多个不同类型的项目，包括网站开发、移动应用和数据分析。'; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="default-project-image">图片路径</label>
                <div class="image-field">
                    <input type="text" id="default-project-image" class="regular-text" value="<?php echo isset($data['project']['default']['image']) ? $data['project']['default']['image'] : plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>" required>
                    <button type="button" class="button upload-image-btn" data-target="default-project-image">选择图片</button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="default-project-article">详细内容</label>
                <textarea id="default-project-article" class="large-text" rows="4" required><?php echo isset($data['project']['default']['article']) ? $data['project']['default']['article'] : '请从上方选择一个具体的项目，了解我的项目经历和成果。'; ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="button button-primary" id="save-settings-btn">保存设置</button>
            </div>
        </form>
    </div>
    
    <div class="preview-link">
        <a href="<?php echo home_url('medias/myself.html'); ?>" target="_blank">预览个人页面</a>
    </div>
</div>

<!-- 现有的模态框代码 -->
<div class="modal" id="identity-modal">
    <div class="modal-content">
        <div class="modal-title" id="identity-modal-title">添加新身份</div>
        <span class="close-btn" id="close-identity-modal">&times;</span>
        
        <form id="identity-form">
            <input type="hidden" id="identity-key" value="">
            
            <div class="form-group">
                <label for="identity-title">标题</label>
                <input type="text" id="identity-title" class="regular-text" required>
            </div>
            
            <div class="form-group">
                <label for="identity-description">简短描述</label>
                <textarea id="identity-description" class="large-text" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="identity-image">图片路径</label>
                <div class="image-field">
                    <input type="text" id="identity-image" class="regular-text" placeholder="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/example.jpg'; ?>" required>
                    <button type="button" class="button upload-image-btn" data-target="identity-image">选择图片</button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="identity-article">详细内容</label>
                <textarea id="identity-article" class="large-text" rows="6" required></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="button" id="cancel-identity-btn">取消</button>
                <button type="submit" class="button button-primary" id="save-identity-btn">保存</button>
            </div>
        </form>
    </div>
</div>

<!-- 编辑/添加项目的模态框 -->
<div class="modal" id="project-modal">
    <div class="modal-content">
        <div class="modal-title" id="project-modal-title">添加新项目</div>
        <span class="close-btn" id="close-project-modal">&times;</span>
        
        <form id="project-form">
            <input type="hidden" id="project-key" value="">
            
            <div class="form-group">
                <label for="project-title">标题</label>
                <input type="text" id="project-title" class="regular-text" required>
            </div>
            
            <div class="form-group">
                <label for="project-description">简短描述</label>
                <textarea id="project-description" class="large-text" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="project-image">图片路径</label>
                <div class="image-field">
                    <input type="text" id="project-image" class="regular-text" placeholder="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/example.jpg'; ?>" required>
                    <button type="button" class="button upload-image-btn" data-target="project-image">选择图片</button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="project-article">详细内容</label>
                <textarea id="project-article" class="large-text" rows="6" required></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="button" id="cancel-project-btn">取消</button>
                <button type="submit" class="button button-primary" id="save-project-btn">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // 初始数据
        let contentData = <?php echo json_encode($data); ?>;
        
        // 确保设置对象存在
        if (!contentData.settings) {
            contentData.settings = {
                background_image: '<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>',
                left_bg_image: '<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/left-bg.jpg'; ?>',
                profile_image: '<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/avatar.jpg'; ?>',
                personal_website: 'https://example.com'
            };
        } else {
            // 确保各项设置都存在
            if (!contentData.settings.background_image) {
                contentData.settings.background_image = '<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>';
            }
            if (!contentData.settings.left_bg_image) {
                contentData.settings.left_bg_image = '<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/left-bg.jpg'; ?>';
            }
            if (!contentData.settings.profile_image) {
                contentData.settings.profile_image = '<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/avatar.jpg'; ?>';
            }
            if (!contentData.settings.personal_website) {
                contentData.settings.personal_website = 'https://example.com';
            }
        }
        if (!contentData.settings.left_bg_image) {
            contentData.settings.left_bg_image = '<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/left-bg.jpg'; ?>';
        }
        
        // 确保默认身份对象存在
        if (!contentData.identity.default) {
            contentData.identity.default = {
                title: '我的身份',
                description: '我拥有多重身份，包括开发者、设计师和学生。每个身份代表了我不同的技能和经历。',
                image: '<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>',
                article: '请从上方选择一个具体的身份，了解我在该领域的专长和经历。'
            };
        }
        
        // 确保默认项目对象存在
        if (!contentData.project.default) {
            contentData.project.default = {
                title: '我的项目',
                description: '我参与和开发了多个不同类型的项目，包括网站开发、移动应用和数据分析。',
                image: '<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>',
                article: '请从上方选择一个具体的项目，了解我的项目经历和成果。'
            };
        }
        
        // 初始化页面
        function init() {
            renderIdentityList();
            renderProjectList();
            
            // 标签切换事件
            $('.tab').on('click', function() {
                const tabId = $(this).data('tab');
                
                // 更新标签状态
                $('.tab').removeClass('active');
                $(this).addClass('active');
                
                // 更新内容状态
                $('.tab-content').removeClass('active');
                $('#' + tabId + '-content').addClass('active');
            });
            
            // 添加身份按钮事件
            $('#add-identity-btn').on('click', function() {
                $('#identity-modal-title').text('添加新身份');
                $('#identity-key').val('');
                $('#identity-title').val('');
                $('#identity-description').val('');
                $('#identity-image').val('<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>');
                $('#identity-article').val('');
                $('#identity-modal').addClass('active');
            });
            
            // 添加项目按钮事件
            $('#add-project-btn').on('click', function() {
                $('#project-modal-title').text('添加新项目');
                $('#project-key').val('');
                $('#project-title').val('');
                $('#project-description').val('');
                $('#project-image').val('<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>');
                $('#project-article').val('');
                $('#project-modal').addClass('active');
            });
            
            // 关闭模态框事件
            $('#close-identity-modal, #cancel-identity-btn').on('click', function() {
                $('#identity-modal').removeClass('active');
            });
            
            $('#close-project-modal, #cancel-project-btn').on('click', function() {
                $('#project-modal').removeClass('active');
            });
            
            // 保存身份表单事件
            $('#identity-form').on('submit', function(e) {
                e.preventDefault();
                
                const key = $('#identity-key').val() || generateKey();
                const title = $('#identity-title').val();
                const description = $('#identity-description').val();
                const image = $('#identity-image').val();
                const article = $('#identity-article').val();
                
                contentData.identity[key] = {
                    title,
                    description,
                    image,
                    article
                };
                
                saveData();
                $('#identity-modal').removeClass('active');
            });
            
            // 保存项目表单事件
            $('#project-form').on('submit', function(e) {
                e.preventDefault();
                
                const key = $('#project-key').val() || generateKey();
                const title = $('#project-title').val();
                const description = $('#project-description').val();
                const image = $('#project-image').val();
                const article = $('#project-article').val();
                
                contentData.project[key] = {
                    title,
                    description,
                    image,
                    article
                };
                
                saveData();
                $('#project-modal').removeClass('active');
            });
            
            // 保存设置表单事件
            $('#settings-form').on('submit', function(e) {
                e.preventDefault();
                
                const backgroundImage = $('#background-image').val();
                const leftBgImage = $('#left-bg-image').val();
                
                // 获取默认身份内容
                const defaultIdentityTitle = $('#default-identity-title').val();
                const defaultIdentityDescription = $('#default-identity-description').val();
                const defaultIdentityImage = $('#default-identity-image').val();
                const defaultIdentityArticle = $('#default-identity-article').val();
                
                // 获取默认项目内容
                const defaultProjectTitle = $('#default-project-title').val();
                const defaultProjectDescription = $('#default-project-description').val();
                const defaultProjectImage = $('#default-project-image').val();
                const defaultProjectArticle = $('#default-project-article').val();
                
                // 更新设置
                contentData.settings = {
                    background_image: backgroundImage,
                    left_bg_image: leftBgImage,
                    profile_image: $('#profile-image').val(),
                    personal_website: $('#personal-website').val()
                };
                
                // 更新默认身份内容
                contentData.identity.default = {
                    title: defaultIdentityTitle,
                    description: defaultIdentityDescription,
                    image: defaultIdentityImage,
                    article: defaultIdentityArticle
                };
                
                // 更新默认项目内容
                contentData.project.default = {
                    title: defaultProjectTitle,
                    description: defaultProjectDescription,
                    image: defaultProjectImage,
                    article: defaultProjectArticle
                };
                
                saveData();
                alert('设置已保存！');
            });
            
            // 添加媒体上传按钮事件
            $('.upload-image-btn').on('click', function() {
                const targetId = $(this).data('target');
                
                // 创建媒体框架
                const frame = wp.media({
                    title: '选择或上传图片',
                    button: {
                        text: '使用此图片'
                    },
                    multiple: false
                });
                
                // 当选择了媒体项目时
                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    $('#' + targetId).val(attachment.url);
                });
                
                // 打开媒体上传器
                frame.open();
            });
        }
        
        // 渲染身份列表
        function renderIdentityList() {
            const $list = $('#identity-list');
            $list.empty();
            
            for (const key in contentData.identity) {
                if (key !== 'default') {
                    const item = contentData.identity[key];
                    const $item = $('<div class="item"></div>');
                    
                    $item.html(`
                        <div class="item-title">${item.title}</div>
                        <div class="item-actions">
                            <button class="button edit-identity" data-key="${key}">编辑</button>
                            <button class="button button-link-delete delete-identity" data-key="${key}">删除</button>
                        </div>
                    `);
                    
                    $list.append($item);
                }
            }
            
            // 添加编辑事件
            $('.edit-identity').on('click', function() {
                const key = $(this).data('key');
                const item = contentData.identity[key];
                
                $('#identity-modal-title').text('编辑身份');
                $('#identity-key').val(key);
                $('#identity-title').val(item.title);
                $('#identity-description').val(item.description);
                $('#identity-image').val(item.image);
                $('#identity-article').val(item.article);
                
                $('#identity-modal').addClass('active');
            });
            
            // 添加删除事件
            $('.delete-identity').on('click', function() {
                const key = $(this).data('key');
                
                if (confirm(`确定要删除"${contentData.identity[key].title}"吗？`)) {
                    delete contentData.identity[key];
                    saveData();
                    renderIdentityList();
                }
            });
        }
        
        // 渲染项目列表
        function renderProjectList() {
            const $list = $('#project-list');
            $list.empty();
            
            for (const key in contentData.project) {
                if (key !== 'default') {
                    const item = contentData.project[key];
                    const $item = $('<div class="item"></div>');
                    
                    $item.html(`
                        <div class="item-title">${item.title}</div>
                        <div class="item-actions">
                            <button class="button edit-project" data-key="${key}">编辑</button>
                            <button class="button button-link-delete delete-project" data-key="${key}">删除</button>
                        </div>
                    `);
                    
                    $list.append($item);
                }
            }
            
            // 添加编辑事件
            $('.edit-project').on('click', function() {
                const key = $(this).data('key');
                const item = contentData.project[key];
                
                $('#project-modal-title').text('编辑项目');
                $('#project-key').val(key);
                $('#project-title').val(item.title);
                $('#project-description').val(item.description);
                $('#project-image').val(item.image);
                $('#project-article').val(item.article);
                
                $('#project-modal').addClass('active');
            });
            
            // 添加删除事件
            $('.delete-project').on('click', function() {
                const key = $(this).data('key');
                
                if (confirm(`确定要删除"${contentData.project[key].title}"吗？`)) {
                    delete contentData.project[key];
                    saveData();
                    renderProjectList();
                }
            });
        }
        
        // 生成唯一键
        function generateKey() {
            return Math.random().toString(36).substring(2, 10);
        }
        
        // 保存数据
        function saveData() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'save_personal_profile',
                    nonce: '<?php echo wp_create_nonce('personal_profile_nonce'); ?>',
                    data: contentData
                },
                success: function(response) {
                    if (response.success) {
                        alert('保存成功！');
                        renderIdentityList();
                        renderProjectList();
                    } else {
                        alert('保存失败：' + response.data);
                    }
                },
                error: function() {
                    alert('保存失败，请稍后再试。');
                }
            });
        }
        
        // 初始化页面
        init();
    });
</script>