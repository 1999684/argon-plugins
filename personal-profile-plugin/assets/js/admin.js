jQuery(document).ready(function($) {
    // 初始化媒体上传器
    if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
        $('.upload-image-btn').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var targetId = button.data('target');
            
            // 创建媒体框架
            var frame = wp.media({
                title: '选择或上传图片',
                button: {
                    text: '使用此图片'
                },
                multiple: false
            });
            
            // 当选择了媒体项目时
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#' + targetId).val(attachment.url);
            });
            
            // 打开媒体上传器
            frame.open();
        });
    } else {
        $('.upload-image-btn').hide();
        console.error('WordPress 媒体上传器未加载');
    }
});