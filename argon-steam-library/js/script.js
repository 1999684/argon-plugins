jQuery(document).ready(function($) {
    $('.asl-load-more-btn').on('click', function() {
        var $button = $(this);
        var $container = $button.closest('.asl-container');
        var $grid = $container.find('.asl-games-grid');
        var currentPage = parseInt($container.data('page'));
        
        if ($button.hasClass('loading')) {
            return;
        }
        
        $button.addClass('loading').text('加载中...');
        
        $.ajax({
            url: aslAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'asl_load_more',
                page: currentPage + 1,
                nonce: aslAjax.nonce
            },
            success: function(response) {
                if (response.games && response.games.length > 0) {
                    response.games.forEach(function(game) {
                        var playtime = Math.floor(game.playtime_forever / 60);
                        // 修改为使用 Steam 商店的游戏封面图片
                        var imageUrl = 'https://cdn.akamai.steamstatic.com/steam/apps/' + 
                                     game.appid + '/header.jpg';
                        
                        var gameCard = `
                            <div class="asl-game-card">
                                <div class="asl-game-image">
                                    <img src="${imageUrl}" alt="${game.name}">
                                </div>
                                <div class="asl-game-playtime">${playtime} 小时</div>
                                <div class="asl-game-info">
                                    <h3 class="asl-game-name">${game.name}</h3>
                                </div>
                            </div>
                        `;
                        
                        $grid.append(gameCard);
                    });
                    
                    $container.data('page', currentPage + 1);
                    
                    if (!response.has_more) {
                        $button.parent().remove();
                    }
                }
                
                $button.removeClass('loading').text('加载更多');
            },
            error: function() {
                $button.removeClass('loading').text('加载失败，请重试');
            }
        });
    });
});