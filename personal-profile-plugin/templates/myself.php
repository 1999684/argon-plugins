<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>关于我自己</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Microsoft YaHei', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
        }
        
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('<?php echo isset($data['settings']['background_image']) ? $data['settings']['background_image'] : plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>');
            background-size: cover;
            background-position: center;
            filter: blur(8px);
            opacity: 0.6;
            z-index: -2;
        }
        
        body::after {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(70, 70, 70, 0.4);
            z-index: -1;
        }
        
        .card-container {
            width: 90%;
            max-width: 1200px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            flex-direction: row;
            height: 90%;
            min-height: 600px;
            position: relative;
            z-index: 1;
        }
        
        .left-section {
            flex: 1;
            padding: 40px;
            background-color: #f9f9f9;
            border-right: 1px solid #eee;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        .left-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('<?php echo isset($data['settings']['left_bg_image']) ? $data['settings']['left_bg_image'] : plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>');
            background-size: cover;
            background-position: center;
            opacity: 0.5;
            z-index: -1;
        }
        
        .right-section {
            flex: 1.2;
            position: relative;
            padding: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* 下拉菜单样式 */
        .dropdown-container {
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        /* 个人头像样式 */
        .profile-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
            cursor: pointer;
        }
        
        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        
        .profile-image:hover img {
            transform: rotate(360deg);
        }
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-btn {
            background-color: transparent;
            color: #333;
            padding: 10px 0;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: border-color 0.3s;
            display: flex;
            align-items: center;
            min-width: 120px;
        }
        
        .dropdown-btn:hover, .dropdown-btn:focus {
            border-bottom-color: #3498db;
            outline: none;
        }
        
        .dropdown-btn::after {
            content: '';
            display: inline-block;
            margin-left: 8px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #333;
            transition: transform 0.3s;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 10;
            border-radius: 8px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .dropdown-content.show {
            display: block;
        }
        
        .dropdown-btn.active::after {
            transform: rotate(180deg);
        }
        
        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
        }
        
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        
        h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 28px;
        }
        
        .description {
            line-height: 1.6;
            color: #555;
            font-size: 16px;
        }
        
        .image-container {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
        }
        
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .image-container img:hover {
            transform: scale(1.05);
        }
        
        .article {
            line-height: 1.8;
            color: white;
            font-size: 16px;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 40px;
            background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.4), transparent);
            z-index: 2;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }
        
        @media (max-width: 768px) {
            .card-container {
                flex-direction: column;
            }
            
            .left-section, .right-section {
                flex: auto;
            }
            
            .dropdown-container {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="card-container">
        <div class="left-section" style="position: relative;">
            <div style="
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: url('<?php echo isset($data['settings']['left_bg_image']) ? $data['settings']['left_bg_image'] : plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>');
                background-size: cover;
                background-position: center;
                opacity: 0.5;
                z-index: -1;
            "></div>
            
            <div class="dropdown-container">
                <!-- 添加个人头像图片 -->
                <div class="profile-image">
                    <a href="<?php echo isset($data['settings']['personal_website']) ? $data['settings']['personal_website'] : 'https://example.com'; ?>" target="_blank" title="访问我的个人网站">
                        <img src="<?php echo isset($data['settings']['profile_image']) ? $data['settings']['profile_image'] : plugin_dir_url(dirname(__FILE__)) . 'assets/images/avatar.jpg'; ?>" alt="个人头像">
                    </a>
                </div>
                
                <div class="dropdown">
                    <button class="dropdown-btn" id="identity-btn">我的身份</button>
                    <div class="dropdown-content" id="identity-menu">
                        <!-- 身份菜单项将通过JavaScript动态生成 -->
                    </div>
                </div>
                
                <div class="dropdown">
                    <button class="dropdown-btn" id="project-btn">我的项目</button>
                    <div class="dropdown-content" id="project-menu">
                        <!-- 项目菜单项将通过JavaScript动态生成 -->
                    </div>
                </div>
            </div>
            
            <h2 id="content-title">欢迎了解我</h2>
            <div class="description" id="content-description">
                请从左侧下拉菜单选择一个选项，查看相关内容。
            </div>
        </div>
        
        <div class="right-section">
            <div class="image-container">
                <img id="content-image" src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>" alt="默认图片">
            </div>
            <div class="article" id="content-article">
                选择一个选项以查看详细内容。
            </div>
        </div>
    </div>

    <script>
        // 初始数据
        let contentData = <?php echo json_encode($data); ?>;

        // 确保默认内容存在
        if (!contentData.identity.default) {
            contentData.identity.default = {
                title: "我的身份",
                description: "我拥有多重身份，包括开发者、设计师和学生。每个身份代表了我不同的技能和经历。",
                image: "<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>",
                article: "请从上方选择一个具体的身份，了解我在该领域的专长和经历。"
            };
        }
        
        if (!contentData.project.default) {
            contentData.project.default = {
                title: "我的项目",
                description: "我参与和开发了多个不同类型的项目，包括网站开发、移动应用和数据分析。",
                image: "<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/default.jpg'; ?>",
                article: "请从上方选择一个具体的项目，了解我的项目经历和成果。"
            };
        }
        
        // 页面加载完成后执行
        window.onload = function() {
            const titleElement = document.getElementById('content-title');
            const descriptionElement = document.getElementById('content-description');
            const imageElement = document.getElementById('content-image');
            const articleElement = document.getElementById('content-article');
            
            // 使用默认身份内容初始化页面
            const defaultData = contentData.identity.default;
            titleElement.textContent = defaultData.title;
            descriptionElement.textContent = defaultData.description;
            imageElement.src = defaultData.image;
            imageElement.alt = defaultData.title;
            articleElement.textContent = defaultData.article;
            
            // 更新下拉菜单按钮文本
            document.getElementById('identity-btn').textContent = '我的身份';
            document.getElementById('project-btn').textContent = '我的项目';
            
            // 动态生成身份和项目菜单项
            generateMenuItems();
            
            // 添加下拉菜单点击事件
            const identityBtn = document.getElementById('identity-btn');
            const projectBtn = document.getElementById('project-btn');
            const identityMenu = document.getElementById('identity-menu');
            const projectMenu = document.getElementById('project-menu');
            
            identityBtn.addEventListener('click', function(e) {
                e.preventDefault();
                identityMenu.classList.toggle('show');
                projectMenu.classList.remove('show');
                this.classList.toggle('active');
                projectBtn.classList.remove('active');
            });
            
            projectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                projectMenu.classList.toggle('show');
                identityMenu.classList.remove('show');
                this.classList.toggle('active');
                identityBtn.classList.remove('active');
            });
            
            // 点击页面其他地方关闭下拉菜单
            document.addEventListener('click', function(e) {
                if (!e.target.matches('.dropdown-btn') && !e.target.matches('.dropdown-content') && !e.target.matches('.dropdown-content a')) {
                    identityMenu.classList.remove('show');
                    projectMenu.classList.remove('show');
                    identityBtn.classList.remove('active');
                    projectBtn.classList.remove('active');
                }
            });
        };
        
        // 动态生成菜单项
        function generateMenuItems() {
            // 生成身份菜单项
            const identityMenu = document.getElementById('identity-menu');
            identityMenu.innerHTML = '';
            
            // 添加默认选项
            const defaultIdentityItem = document.createElement('a');
            defaultIdentityItem.href = '#';
            defaultIdentityItem.setAttribute('data-value', 'default');
            defaultIdentityItem.textContent = contentData.identity.default.title;
            identityMenu.appendChild(defaultIdentityItem);
            
            for (const key in contentData.identity) {
                if (key !== 'default') {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.setAttribute('data-value', key);
                    item.textContent = contentData.identity[key].title;
                    identityMenu.appendChild(item);
                }
            }
            
            // 生成项目菜单项
            const projectMenu = document.getElementById('project-menu');
            projectMenu.innerHTML = '';
            
            // 添加默认选项
            const defaultProjectItem = document.createElement('a');
            defaultProjectItem.href = '#';
            defaultProjectItem.setAttribute('data-value', 'default');
            defaultProjectItem.textContent = contentData.project.default.title;
            projectMenu.appendChild(defaultProjectItem);
            
            for (const key in contentData.project) {
                if (key !== 'default') {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.setAttribute('data-value', key);
                    item.textContent = contentData.project[key].title;
                    projectMenu.appendChild(item);
                }
            }
            
            // 为身份菜单项添加点击事件
            const identityLinks = document.querySelectorAll('#identity-menu a');
            identityLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const value = this.getAttribute('data-value');
                    updateContent('identity', value);
                    // 关闭下拉菜单
                    document.getElementById('identity-menu').classList.remove('show');
                    document.getElementById('identity-btn').classList.remove('active');
                });
            });
            
            // 为项目菜单项添加点击事件
            const projectLinks = document.querySelectorAll('#project-menu a');
            projectLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const value = this.getAttribute('data-value');
                    updateContent('project', value);
                    // 关闭下拉菜单
                    document.getElementById('project-menu').classList.remove('show');
                    document.getElementById('project-btn').classList.remove('active');
                });
            });
        }
        
        // 更新内容函数
        function updateContent(type, value) {
            const titleElement = document.getElementById('content-title');
            const descriptionElement = document.getElementById('content-description');
            const imageElement = document.getElementById('content-image');
            const articleElement = document.getElementById('content-article');
            const identityBtn = document.getElementById('identity-btn');
            const projectBtn = document.getElementById('project-btn');
            
            const data = contentData[type][value];
            
            titleElement.textContent = data.title;
            descriptionElement.textContent = data.description;
            imageElement.src = data.image;
            imageElement.alt = data.title;
            articleElement.textContent = data.article;
            
            // 更新按钮文本
            if (type === 'identity') {
                identityBtn.textContent = data.title;
            } else if (type === 'project') {
                projectBtn.textContent = data.title;
            }
        }
    </script>
</body>
</html>