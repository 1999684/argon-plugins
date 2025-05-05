jQuery(document).ready(function($) {
    // 上传海报图片
    function initMediaUploader(buttonId, previewId, inputId) {
        $(buttonId).click(function(e) {
            e.preventDefault();
            
            var mediaUploader = wp.media({
                title: '选择海报图片',
                button: {
                    text: '使用此图片'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $(previewId).html('<img src="' + attachment.url + '" alt="海报预览">');
                $(inputId).val(attachment.url);
            });
            
            mediaUploader.open();
        });
    }
    
    // 初始化媒体上传器
    initMediaUploader('#upload-poster-btn', '#poster-preview', '#poster_url');
    initMediaUploader('#edit-upload-poster-btn', '#edit-poster-preview', '#edit_poster_url');
    
    // 加载记录列表
    function loadRecords(category = '') {
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'get_movie_records',
                nonce: movieRecordAdmin.nonce,
                category: category
            },
            success: function(response) {
                if (response.success) {
                    var records = response.data;
                    var html = '';
                    
                    records.forEach(function(record) {
                        html += '<tr>';
                        html += '<td><img src="' + record.poster_url + '" alt="海报"></td>';
                        html += '<td>' + record.title + '</td>';
                        html += '<td>' + getCategoryName(record.category) + '</td>';
                        html += '<td><a href="' + record.link_url + '" target="_blank">' + record.link_url + '</a></td>';
                        html += '<td class="action-buttons">';
                        html += '<button type="button" class="button edit-record" data-id="' + record.id + '">编辑</button>';
                        html += '<button type="button" class="button button-link-delete delete-record" data-id="' + record.id + '">删除</button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    $('#records-list').html(html);
                }
            }
        });
    }
    
    // 获取分类显示名称
    function getCategoryName(category) {
        var categories = {
            'movie': '电影',
            'anime': '动漫',
            'variety': '综艺',
            'drama': '电视剧'
        };
        return categories[category] || category;
    }
    
    // 初始加载记录
    loadRecords();
    
    // 筛选记录
    $('#filter-records').click(function() {
        var category = $('#filter-category').val();
        loadRecords(category);
    });
    
    // 添加新记录
    $('#add-movie-form').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'add_movie_record',
                nonce: movieRecordAdmin.nonce,
                title: $('#title').val(),
                category: $('#category').val(),
                poster_url: $('#poster_url').val(),
                link_url: $('#link_url').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('记录添加成功');
                    $('#add-movie-form')[0].reset();
                    $('#poster-preview').empty();
                    loadRecords();
                } else {
                    alert('添加失败：' + response.data);
                }
            }
        });
    });
    
    // 编辑记录
    $(document).on('click', '.edit-record', function() {
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        
        $('#edit_id').val(id);
        $('#edit_title').val(row.find('td:eq(1)').text());
        $('#edit_category').val(row.find('td:eq(2)').data('category'));
        $('#edit_poster_url').val(row.find('img').attr('src'));
        $('#edit-poster-preview').html(row.find('td:eq(0)').html());
        $('#edit_link_url').val(row.find('td:eq(3) a').attr('href'));
        
        $('#edit-record-modal').show();
    });
    
    // 关闭模态框
    $('.close').click(function() {
        $('#edit-record-modal').hide();
    });
    
    // 更新记录
    $('#edit-movie-form').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_movie_record',
                nonce: movieRecordAdmin.nonce,
                id: $('#edit_id').val(),
                title: $('#edit_title').val(),
                category: $('#edit_category').val(),
                poster_url: $('#edit_poster_url').val(),
                link_url: $('#edit_link_url').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('记录更新成功');
                    $('#edit-record-modal').hide();
                    loadRecords();
                } else {
                    alert('更新失败：' + response.data);
                }
            }
        });
    });
    
    // 删除记录
    $(document).on('click', '.delete-record', function() {
        if (!confirm('确定要删除这条记录吗？')) {
            return;
        }
        
        var id = $(this).data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_movie_record',
                nonce: movieRecordAdmin.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    alert('记录删除成功');
                    loadRecords();
                } else {
                    alert('删除失败：' + response.data);
                }
            }
        });
    });
});