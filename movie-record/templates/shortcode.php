<?php
global $wpdb;
$table_name = $wpdb->prefix . 'movie_records';

// 获取参数
$category = isset($atts['category']) ? $atts['category'] : '';
$columns = isset($atts['columns']) ? intval($atts['columns']) : 4; // 默认每行显示4张海报
$where_clause = $category ? $wpdb->prepare("WHERE category = %s", $category) : '';

// 获取记录
$records = $wpdb->get_results("SELECT * FROM {$table_name} {$where_clause} ORDER BY created_at DESC");

// 按分类组织数据
$categorized_records = array();
foreach ($records as $record) {
    $categorized_records[$record->category][] = $record;
}

// 分类显示名称映射
$category_names = array(
    'movie' => '电影',
    'anime' => '动漫',
    'variety' => '综艺',
    'drama' => '电视剧'
);
?>

<style>
.records-grid {
    display: grid;
    grid-template-columns: repeat(<?php echo $columns; ?>, 1fr);
    gap: 20px;
    margin: 20px 0;
}

.record-card {
    width: 100%;
}

.poster-wrapper {
    aspect-ratio: 2/3;
    overflow: hidden;
    border-radius: 8px;
}

.poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>

<div class="movie-record-showcase">
    <?php if (empty($records)): ?>
    <p class="no-records">暂无记录</p>
    <?php else: ?>
        <?php foreach ($categorized_records as $cat => $cat_records): ?>
        <div class="category-section">
            <h2 class="category-title"><?php echo esc_html($category_names[$cat]); ?></h2>
            <div class="records-grid">
                <?php foreach ($cat_records as $record): ?>
                <div class="record-card">
                    <a href="<?php echo esc_url($record->link_url); ?>" target="_blank" class="record-link">
                        <div class="poster-wrapper">
                            <img src="<?php echo esc_url($record->poster_url); ?>" alt="<?php echo esc_attr($record->title); ?>" class="poster">
                        </div>
                        <h3 class="title" style="color:#8B0000;"><?php echo esc_html($record->title); ?></h3>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>