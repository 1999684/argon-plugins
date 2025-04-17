(function($) {
    'use strict';

    // 全局变量
    let map;
    let selectedPosition = null;
    let tempMarker = null;
    let currentMarkerId = null;
    let mapMarkers = [];

    // 初始化函数
    function init() {
        // 初始化地图
        initMap();
        
        // 绑定事件
        bindEvents();
        
        // 加载标记点
        loadMarkers();
    }

    // 初始化地图
    function initMap() {
        map = new AMap.Map('admin-map', {
            zoom: 4,
            center: [116.397428, 39.90923] // 默认中心点，北京
        });
        
        // 地图点击事件
        map.on('click', function(e) {
            selectedPosition = e.lnglat;
            $('#selected-position').text(`经度: ${selectedPosition.lng.toFixed(6)}, 纬度: ${selectedPosition.lat.toFixed(6)}`);
            
            // 启用添加按钮
            $('#add-marker').prop('disabled', false);
            
            // 添加临时标记
            if (tempMarker) {
                tempMarker.setMap(null);
            }
            
            tempMarker = new AMap.Marker({
                position: [selectedPosition.lng, selectedPosition.lat],
                map: map,
                animation: 'AMAP_ANIMATION_DROP'
            });
        });
    }

    // 绑定事件
    function bindEvents() {
        // 添加标记点按钮
        $('#add-marker').on('click', function() {
            const title = $('#marker-title').val().trim();
            
            if (!title) {
                alert('请输入标记点名称');
                return;
            }
            
            if (!selectedPosition) {
                alert('请在地图上选择位置');
                return;
            }
            
            // 保存标记点
            saveMarker(title, selectedPosition.lat, selectedPosition.lng);
        });
        
        // 清除选择按钮
        $('#clear-selection').on('click', function() {
            clearSelection();
        });
        
        // 添加链接按钮（使用事件委托）
        $(document).on('click', '.add-link-btn', function() {
            const $markerItem = $(this).closest('.marker-item');
            const markerId = $markerItem.data('id');
            const markerTitle = $markerItem.find('.marker-title').text();
            
            showLinkForm(markerId, markerTitle);
        });
        
        // 删除标记点按钮（使用事件委托）
        $(document).on('click', '.delete-marker-btn', function() {
            if (confirm('确定要删除这个标记点吗？这将同时删除所有关联的链接。')) {
                const $markerItem = $(this).closest('.marker-item');
                const markerId = $markerItem.data('id');
                
                deleteMarker(markerId);
            }
        });
        
        // 删除链接按钮（使用事件委托）
        $(document).on('click', '.delete-link-btn', function() {
            if (confirm('确定要删除这个链接吗？')) {
                const $linkItem = $(this).closest('.link-item');
                const linkId = $linkItem.data('id');
                
                deleteLink(linkId);
            }
        });
        
        // 添加链接表单提交
        $('#add-link').on('click', function() {
            const linkTitle = $('#link-title').val().trim();
            const linkUrl = $('#link-url').val().trim();
            
            if (!linkTitle || !linkUrl) {
                alert('请输入链接标题和地址');
                return;
            }
            
            addLink(currentMarkerId, linkTitle, linkUrl);
        });
        
        // 取消添加链接
        $('#cancel-add-link').on('click', function() {
            hideLinkForm();
        });
    }

    // 加载标记点
    function loadMarkers() {
        // 清除地图上的标记点
        clearMapMarkers();
        
        // 遍历DOM中的标记点
        $('.marker-item').each(function() {
            const $item = $(this);
            const id = $item.data('id');
            const title = $item.find('.marker-title').text();
            const positionText = $item.find('.marker-position').text();
            
            // 解析位置文本
            const positionMatch = positionText.match(/位置: ([\d\.]+), ([\d\.]+)/);
            if (positionMatch) {
                const lng = parseFloat(positionMatch[1]);
                const lat = parseFloat(positionMatch[2]);
                
                // 添加到地图
                addMarkerToMap(id, title, lat, lng);
            }
        });
    }

    // 添加标记点到地图
    function addMarkerToMap(id, title, lat, lng) {
        const marker = new AMap.Marker({
            position: [lng, lat],
            title: title,
            map: map
        });
        
        // 存储标记点引用
        mapMarkers.push({
            id: id,
            marker: marker
        });
        
        // 点击标记点时，滚动到对应的列表项
        marker.on('click', function() {
            const $item = $(`.marker-item[data-id="${id}"]`);
            if ($item.length) {
                $('.travel-map-sidebar').animate({
                    scrollTop: $item.offset().top - $('.travel-map-sidebar').offset().top + $('.travel-map-sidebar').scrollTop()
                }, 500);
                
                // 高亮显示
                $item.addClass('highlight');
                setTimeout(function() {
                    $item.removeClass('highlight');
                }, 2000);
            }
        });
    }

    // 清除地图上的标记点
    function clearMapMarkers() {
        mapMarkers.forEach(function(item) {
            item.marker.setMap(null);
        });
        mapMarkers = [];
    }

    // 清除选择
    function clearSelection() {
        if (tempMarker) {
            tempMarker.setMap(null);
            tempMarker = null;
        }
        
        selectedPosition = null;
        $('#selected-position').text('请在地图上点击选择位置');
        $('#add-marker').prop('disabled', true);
        $('#marker-title').val('');
    }

    // 显示链接表单
    function showLinkForm(markerId, markerTitle) {
        currentMarkerId = markerId;
        $('#current-marker-title').text(markerTitle);
        $('#link-title').val('');
        $('#link-url').val('');
        $('#link-form-dialog').show();
    }

    // 隐藏链接表单
    function hideLinkForm() {
        $('#link-form-dialog').hide();
        currentMarkerId = null;
    }

    // 保存标记点
    function saveMarker(title, lat, lng) {
        $.ajax({
            url: travel_map_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'travel_map_save_marker',
                nonce: travel_map_admin.nonce,
                title: title,
                lat: lat,
                lng: lng
            },
            success: function(response) {
                if (response.success) {
                    // 刷新页面以显示新标记点
                    location.reload();
                } else {
                    alert('保存标记点失败: ' + response.data);
                }
            },
            error: function() {
                alert('保存标记点时发生错误');
            }
        });
    }

    // 删除标记点
    function deleteMarker(markerId) {
        $.ajax({
            url: travel_map_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'travel_map_delete_marker',
                nonce: travel_map_admin.nonce,
                id: markerId
            },
            success: function(response) {
                if (response.success) {
                    // 从DOM中移除标记点
                    $(`.marker-item[data-id="${markerId}"]`).remove();
                    
                    // 从地图中移除标记点
                    const markerIndex = mapMarkers.findIndex(item => item.id == markerId);
                    if (markerIndex !== -1) {
                        mapMarkers[markerIndex].marker.setMap(null);
                        mapMarkers.splice(markerIndex, 1);
                    }
                    
                    // 如果没有标记点了，显示提示
                    if ($('.marker-item').length === 0) {
                        $('#marker-list').html('<p>暂无标记点，请在地图上点击添加</p>');
                    }
                } else {
                    alert('删除标记点失败: ' + response.data);
                }
            },
            error: function() {
                alert('删除标记点时发生错误');
            }
        });
    }

    // 添加链接
    function addLink(markerId, title, url) {
        $.ajax({
            url: travel_map_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'travel_map_add_link',
                nonce: travel_map_admin.nonce,
                marker_id: markerId,
                title: title,
                url: url
            },
            success: function(response) {
                if (response.success) {
                    // 刷新页面以显示新链接
                    location.reload();
                } else {
                    alert('添加链接失败: ' + response.data);
                }
            },
            error: function() {
                alert('添加链接时发生错误');
            }
        });
    }

    // 删除链接
    function deleteLink(linkId) {
        $.ajax({
            url: travel_map_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'travel_map_delete_link',
                nonce: travel_map_admin.nonce,
                id: linkId
            },
            success: function(response) {
                if (response.success) {
                    // 从DOM中移除链接
                    $(`.link-item[data-id="${linkId}"]`).remove();
                } else {
                    alert('删除链接失败: ' + response.data);
                }
            },
            error: function() {
                alert('删除链接时发生错误');
            }
        });
    }

    // 页面加载完成后初始化
    $(document).ready(init);

})(jQuery);