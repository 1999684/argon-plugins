var TravelMapPlugin = (function($) {
    'use strict';

    let map;
    let markers = [];
    let isInitialized = false;
    const mapContainerId = 'travel-map'; // 地图容器ID

    // 核心地图初始化逻辑
    function initializeMap() {
        // 防止重复初始化 (如果地图实例仍然存在)
        if (isInitialized && map) {
            console.log('Travel Map: 地图实例已存在，跳过重复初始化。');
            return;
        }

        const mapContainer = document.getElementById(mapContainerId);
        if (!mapContainer) {
            console.log('Travel Map: 地图容器 #' + mapContainerId + ' 在当前页面未找到。');
            isInitialized = false; // 重置状态，因为容器不在了
            if (map && typeof map.destroy === 'function') {
                map.destroy(); // 销毁旧地图实例
                map = null;
            }
            markers = []; // 清空标记
            return;
        }

        // 检查 AMap API 是否加载
        if (typeof AMap === 'undefined' || typeof AMap.Map === 'undefined') {
            console.error('Travel Map: 高德地图 API (AMap) 未加载。');
            // 可以在这里添加重试逻辑，或者依赖于脚本加载顺序
            return;
        }
        
        console.log('Travel Map: 开始初始化...');

        // --- 初始化地图 ---
        try {
            // 销毁可能存在的旧实例 (以防万一)
            if (map && typeof map.destroy === 'function') {
                 console.log('Travel Map: 销毁之前的地图实例。');
                 map.destroy();
                 map = null;
                 markers = []; // 清空标记
            }

            // 检查配置是否存在
            if (typeof window.travelMapConfig === 'undefined') {
                console.warn('Travel Map: 地图配置不存在，使用默认值。');
                map = new AMap.Map(mapContainerId, {
                    zoom: 4,
                    center: [116.397428, 39.90923] // 默认北京
                });
            } else {
                const config = window.travelMapConfig;
                const zoom = config.zoom || 4;
                let center = [116.397428, 39.90923]; // 默认北京
                
                if (config.center) {
                    if (Array.isArray(config.center)) {
                        center = config.center;
                    } else if (typeof config.center === 'string') {
                        const centerParts = config.center.split(',');
                        if (centerParts.length === 2) {
                            const lng = parseFloat(centerParts[0]);
                            const lat = parseFloat(centerParts[1]);
                            if (!isNaN(lng) && !isNaN(lat)) {
                                center = [lng, lat];
                            }
                        }
                    }
                }
                map = new AMap.Map(mapContainerId, {
                    zoom: zoom,
                    center: center
                });
            }
            console.log('Travel Map: 地图对象已创建。');
        } catch (e) {
            console.error('Travel Map: 初始化地图时出错:', e);
            isInitialized = false; // 标记初始化失败
            return; // 初始化失败则不继续
        }

        // --- 加载标记点 ---
        loadMarkers();

        isInitialized = true;
        console.log('Travel Map: 初始化完成。');
    }

    // 加载标记点逻辑 (基本保持不变)
    function loadMarkers() {
        // 清空旧标记 (如果地图重建了)
        markers = []; 
        // 如果地图对象不存在，则无法添加标记
        if (!map) {
             console.error('Travel Map: 地图对象未初始化，无法加载标记点。');
             return;
        }

        if (typeof window.travel_map_data === 'undefined' || !window.travel_map_data.markers) {
            console.warn('Travel Map: 标记点数据不存在。');
            $('#travel-map-links').html('<h3>暂无标记点数据</h3>'); // 确保链接容器存在
            return;
        }

        const markersData = window.travel_map_data.markers;
        const linksContainer = $('#travel-map-links'); // 获取链接容器

        if (!markersData || markersData.length === 0) {
             if(linksContainer.length) linksContainer.html('<h3>暂无标记点数据</h3>');
            return;
        }
        
        markersData.forEach(function(markerData) {
            try {
                const marker = new AMap.Marker({
                    position: [markerData.lng, markerData.lat],
                    title: markerData.title,
                    map: map // 确保添加到当前地图实例
                });
                
                markers.push({
                    id: markerData.id,
                    data: markerData,
                    marker: marker
                });
                
                marker.on('click', function() {
                    showLinks(markerData);
                });
            } catch(e) {
                 console.error('Travel Map: 添加标记点时出错:', markerData, e);
            }
        });
        
        if (markersData.length > 0 && (!window.travelMapConfig || !window.travelMapConfig.center)) {
             if(map && typeof map.setCenter === 'function') {
                map.setCenter([markersData[0].lng, markersData[0].lat]);
             }
        }
        console.log('Travel Map: 标记点加载完成。');
    }

    // 显示链接逻辑 (保持不变)
    function showLinks(marker) {
        const $linksContainer = $('#travel-map-links');
        if(!$linksContainer.length) return; // 如果容器不存在则退出

        $linksContainer.empty();
        
        const $titleElement = $('<div>', {
            class: 'travel-map-marker-title',
            text: marker.title || '标记点信息'
        });
        $linksContainer.append($titleElement);
        
        if (marker.links && marker.links.length > 0) {
            marker.links.forEach(function(link) {
                const $linkItem = $('<div>', { class: 'travel-map-link-item' });
                const $linkElement = $('<a>', {
                    class: 'travel-map-link-title',
                    href: link.url,
                    text: link.title,
                    target: '_blank'
                });
                $linkItem.append($linkElement);
                $linksContainer.append($linkItem);
            });
        } else {
            const $noLinks = $('<div>', {
                text: '该标记点没有关联链接',
                css: { color: 'white' }
            });
            $linksContainer.append($noLinks);
        }
    }

    // 公开的初始化接口，由 pjaxLoaded 调用
    function publicInit() {
        // 每次 pjax 加载都尝试初始化
        // initializeMap 内部会检查容器是否存在以及是否已初始化
        initializeMap();
    }
    // 返回公共接口
    return {
        init: publicInit
    };

})(jQuery);

// --- Argon 主题 PJAX 集成 ---

// 定义 pjaxLoaded 函数
window.pjaxLoaded = function(){
    console.log('Travel Map: pjaxLoaded triggered.');
    // 检查 TravelMapPlugin 是否已定义
    if (typeof TravelMapPlugin !== 'undefined' && typeof TravelMapPlugin.init === 'function') {
        TravelMapPlugin.init();
    } else {
        console.error('Travel Map: TravelMapPlugin 未定义或 init 方法不存在。');
    }
};
							
// 手动执行一次以处理首次页面加载
// 确保在 TravelMapPlugin 定义之后执行
if (typeof window.pjaxLoaded === 'function') {
    console.log('Travel Map: Initial page load, calling pjaxLoaded manually.');
    // 可以稍微延迟执行，确保其他脚本（如高德地图API）已加载
    setTimeout(window.pjaxLoaded, 100); 
} else {
     console.error('Travel Map: window.pjaxLoaded 未定义。');
}