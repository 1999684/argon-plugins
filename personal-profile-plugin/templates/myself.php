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

        .github-button {
            display: inline-flex;
            align-items: center;
            background-color: #24292e;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .github-button:hover {
            background-color: #2c974b;
            text-decoration: none;
            color: white;
        }
        
        .github-button svg path {
            fill: white;
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
            
            #contact-icons {
                position: static !important;
                margin-top: 20px;
            }
        }
        
        /* 联系方式图标悬停效果 */
        #contact-icons a:hover {
            transform: translateY(-3px);
            background-color: rgba(255, 255, 255, 1);
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
                    <a href="javascript:void(0);" onclick="window.location.href='<?php echo isset($data['settings']['personal_website']) ? $data['settings']['personal_website'] : 'https://example.com'; ?>'" title="访问我的个人网站">
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
            
            <!-- 添加GitHub项目链接 -->
            <div id="github-link-container" style="display: none; margin-top: 15px;">
                <a id="github-link" href="#" target="_blank" class="github-button">
                    <svg height="20" width="20" viewBox="0 0 16 16" style="vertical-align: middle; margin-right: 5px;">
                        <path fill="white" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path>
                    </svg>
                    查看GitHub项目
                </a>
            </div>

            <!-- 添加联系方式图标区域 -->
            <div id="contact-icons" style="position: absolute; bottom: 20px; left: 0; right: 0; display: flex; justify-content: center; gap: 15px;">
                <a href="mailto:<?php echo isset($data['settings']['email']) ? $data['settings']['email'] : 'example@example.com'; ?>" title="发送邮件" style="display: inline-block; width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.8); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: transform 0.3s, background-color 0.3s;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#e74c3c" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm-8-7L4 6h16l-8 5z"/>
                    </svg>
                </a>
                <a href="javascript:void(0);" onclick="copyToClipboard('<?php echo isset($data['settings']['qq']) ? $data['settings']['qq'] : '123456789'; ?>', 'QQ号已复制到剪贴板')" title="点击复制QQ号" style="display: inline-block; width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.8); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: transform 0.3s, background-color 0.3s;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#3498db" d="M12.5,1C18,1,22.1,5.1,22.1,10.5c0,5.4-4.1,9.5-9.6,9.5c-0.5,0-1.1,0-1.6-0.1c-0.5,0.2-1.6,0.7-3.8,1.7 c-0.4,0.2-0.9-0.2-0.7-0.6c0.4-1,0.7-1.8,0.8-2.6C4.8,16.9,3,14,3,10.5C3,5.1,7.1,1,12.5,1z M12.5,3C8.4,3,5,6.3,5,10.5 c0,2.8,1.5,5.2,3.8,6.4c0.3,0.2,0.5,0.5,0.4,0.9c-0.1,0.4-0.2,0.8-0.3,1.2c0.7-0.3,1.3-0.5,1.8-0.7c0.3-0.1,0.6-0.1,0.9,0 c0.3,0.1,0.6,0.1,0.9,0.1c4.1,0,7.5-3.3,7.5-7.5C20,6.3,16.6,3,12.5,3z"/>
                    </svg>
                </a>
                <a href="javascript:void(0);" onclick="copyToClipboard('<?php echo isset($data['settings']['wechat']) ? $data['settings']['wechat'] : 'my_wechat'; ?>', '微信号已复制到剪贴板')" title="点击复制微信号" style="display: inline-block; width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.8); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: transform 0.3s, background-color 0.3s;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#25D366" d="M8.5,14.1c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9c0.5,0,0.9,0.4,0.9,0.9C9.4,13.7,9,14.1,8.5,14.1z M12,14.1c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9c0.5,0,0.9,0.4,0.9,0.9C12.9,13.7,12.5,14.1,12,14.1z M16,11.4c0.5,0,0.9,0.4,0.9,0.9c0,0.5-0.4,0.9-0.9,0.9c-0.5,0-0.9-0.4-0.9-0.9C15.1,11.8,15.5,11.4,16,11.4z M19.5,11.4c0.5,0,0.9,0.4,0.9,0.9c0,0.5-0.4,0.9-0.9,0.9c-0.5,0-0.9-0.4-0.9-0.9C18.6,11.8,19,11.4,19.5,11.4z M21.7,15.4c0-3.8-3.8-6.8-8.5-6.8c-4.7,0-8.5,3-8.5,6.8c0,3.8,3.8,6.8,8.5,6.8c1,0,2-0.1,2.9-0.4l2.6,1.5c-0.7-2.1,0-2.3,0-2.3l0.1-0.1C20.3,19.3,21.7,17.5,21.7,15.4z M13.2,2c-5.4,0-9.8,3.7-9.8,8.2c0,2.4,1.2,4.6,3.1,6.1l-0.8,2.4c0,0-0.1,0.4,0.3,0.4c0.2,0,0.4-0.1,0.4-0.1l2.9-1.6c1.2,0.3,2.5,0.5,3.8,0.5c0.4,0,0.9,0,1.3-0.1c-0.5-1.1-0.8-2.4-0.8-3.7C13.7,8.5,18,4.1,23,4.1c0.4,0,0.9,0,1.3,0.1C22.8,3.7,18.3,2,13.2,2z"/>
                    </svg>
                </a>
                <a href="<?php echo isset($data['settings']['personal_website']) ? $data['settings']['personal_website'] : 'https://example.com'; ?>" title="访问个人网站" target="_blank" style="display: inline-block; width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.8); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: transform 0.3s, background-color 0.3s;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#007bb5" d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M12,4c1.38,0,2.64,0.4,3.75,1.05 c-0.24,0.31-0.44,0.67-0.53,1.08c-0.27,1.25,0.07,2.5,0.98,3.44c0.54,0.55,0.98,1.21,1.14,1.96c0.23,1.04,0.02,2.15-0.61,3.03 c-0.79,1.08-2.09,1.68-3.44,1.53c-1.13-0.13-2.13-0.76-2.69-1.76c-0.36-0.65-0.53-1.38-0.54-2.12c-0.01-1.25,0.5-2.43,1.42-3.26 c0.59-0.54,1.25-0.97,1.91-1.38c0.36-0.22,0.73-0.46,0.99-0.79c0.16-0.2,0.27-0.43,0.3-0.68c0.02-0.25-0.07-0.49-0.21-0.69 c-0.23-0.32-0.58-0.53-0.96-0.62c-0.48-0.12-0.95,0.01-1.35,0.27C12.1,4.8,12.05,4.83,12,4.86V4z M6.69,7.06 C7.38,6.5,8.13,6.05,8.95,5.75C9.24,6.06,9.65,6.27,10.1,6.36c0.74,0.14,1.48-0.02,2.08-0.44c0.09,0.12,0.17,0.25,0.24,0.38 c0.21,0.4,0.29,0.86,0.21,1.31c-0.09,0.53-0.39,0.99-0.77,1.34c-0.86,0.8-1.96,1.3-2.92,2.02c-0.6,0.44-1.12,0.97-1.49,1.59 c-0.67,1.12-0.85,2.43-0.62,3.7c0.29,1.57,1.2,2.96,2.49,3.93c-2.05-0.76-3.71-2.34-4.58-4.31C3.97,14.25,4.28,10.33,6.69,7.06z M16.55,7.33c0.17,0.21,0.33,0.44,0.47,0.68c0.35,0.59,0.6,1.24,0.72,1.92c0.18,1.02,0.09,2.08-0.27,3.06 c-0.45,1.22-1.3,2.28-2.37,3.06c-0.56,0.4-1.18,0.72-1.85,0.93c-0.74,0.24-1.52,0.34-2.3,0.3c0.83-0.4,1.56-0.98,2.11-1.71 c0.91-1.22,1.2-2.8,0.89-4.28c-0.22-1.04-0.81-1.98-1.66-2.69c-0.4-0.33-0.85-0.6-1.33-0.83c0.63-0.19,1.28-0.29,1.94-0.29 C14.08,7.48,15.37,7.79,16.55,7.33z"/>
                    </svg>
                </a>
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
    <p style="text-align: center; font-size: 15px; color:rgb(4, 19, 6); position: fixed; bottom: 10px; left: 0; right: 0; margin: 0 auto; z-index: 10;">该插件由<a href="https://github.com/1999684/argon-plugins" style="color: #6495ED;">ZTGD</a>制作</p>

    <script>
        // 初始数据
        let contentData = <?php echo json_encode($data); ?>;
        
        // 调试输出
        console.log('加载的数据:', contentData);
        
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
            const githubLinkContainer = document.getElementById('github-link-container');
            
            // 使用默认身份内容初始化页面
            const defaultData = contentData.identity.default;
            titleElement.textContent = defaultData.title;
            descriptionElement.textContent = defaultData.description;
            imageElement.src = defaultData.image;
            imageElement.alt = defaultData.title;
            
            // 清除文章元素中的文本节点
            Array.from(articleElement.childNodes).forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    articleElement.removeChild(node);
                }
            });
            
            // 添加新的文本节点作为第一个子节点
            articleElement.insertBefore(document.createTextNode(defaultData.article), articleElement.firstChild);
            
            // 初始化时隐藏GitHub链接
            githubLinkContainer.style.display = 'none';
            
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
            const githubLinkContainer = document.getElementById('github-link-container');
            const githubLink = document.getElementById('github-link');
            
            const data = contentData[type][value];
            
            // 详细调试信息
            console.log('更新内容类型:', type);
            console.log('更新内容键值:', value);
            console.log('数据对象:', data);
            console.log('GitHub用户名:', data.github_username);
            console.log('GitHub仓库名:', data.github_repo);
            
            titleElement.textContent = data.title;
            descriptionElement.textContent = data.description;
            imageElement.src = data.image;
            imageElement.alt = data.title;
            
            // 更新文章内容
            if (articleElement.childNodes[0] && articleElement.childNodes[0].nodeType === Node.TEXT_NODE) {
                articleElement.childNodes[0].nodeValue = data.article;
            } else {
                articleElement.textContent = data.article;
            }
            
            // 处理GitHub链接
            if (type === 'project' && data.github_username && data.github_repo) {
                const repoUrl = `https://github.com/${data.github_username}/${data.github_repo}`;
                githubLink.href = repoUrl;
                githubLinkContainer.style.display = 'block';
                console.log('显示GitHub链接:', repoUrl);
            } else {
                githubLinkContainer.style.display = 'none';
                console.log('隐藏GitHub链接, 原因:', 
                    type !== 'project' ? '不是项目类型' : 
                    !data.github_username ? '缺少用户名' : 
                    !data.github_repo ? '缺少仓库名' : 
                    '用户名或仓库名为空');
            }
            
            // 更新按钮文本
            if (type === 'identity') {
                identityBtn.textContent = data.title;
            } else if (type === 'project') {
                projectBtn.textContent = data.title;
            }
        }

        function copyToClipboard(text, message) {
            // 创建一个临时的textarea元素
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';  // 防止滚动到页面底部
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                // 执行复制命令
                const successful = document.execCommand('copy');
                
                // 显示提示信息
                if (successful) {
                    showToast(message);
                } else {
                    showToast('复制失败，请手动复制');
                }
            } catch (err) {
                showToast('复制失败，请手动复制');
                console.error('复制失败:', err);
            }
            
            // 移除临时元素
            document.body.removeChild(textarea);
        }
        
        function showToast(message) {
            // 检查是否已存在toast元素，如果有则移除
            const existingToast = document.getElementById('copy-toast');
            if (existingToast) {
                document.body.removeChild(existingToast);
            }
            
            // 创建toast元素
            const toast = document.createElement('div');
            toast.id = 'copy-toast';
            toast.textContent = message;
            toast.style.position = 'fixed';
            toast.style.bottom = '30px';
            toast.style.left = '50%';
            toast.style.transform = 'translateX(-50%)';
            toast.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            toast.style.color = 'white';
            toast.style.padding = '10px 20px';
            toast.style.borderRadius = '4px';
            toast.style.zIndex = '1000';
            toast.style.transition = 'opacity 0.3s';
            
            // 添加到页面
            document.body.appendChild(toast);
            
            // 3秒后自动消失
            setTimeout(function() {
                toast.style.opacity = '0';
                setTimeout(function() {
                    if (document.body.contains(toast)) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>