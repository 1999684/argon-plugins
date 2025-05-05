<?php
// 添加新记录
add_action('wp_ajax_add_movie_record', 'handle_add_movie_record');
function handle_add_movie_record() {
    check_ajax_referer('movie_record_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    $title = sanitize_text_field($_POST['title']);
    $category = sanitize_text_field($_POST['category']);
    $poster_url = esc_url_raw($_POST['poster_url']);
    $link_url = esc_url_raw($_POST['link_url']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'movie_records';
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'title' => $title,
            'category' => $category,
            'poster_url' => $poster_url,
            'link_url' => $link_url
        ),
        array('%s', '%s', '%s', '%s')
    );
    
    if ($result === false) {
        wp_send_json_error('添加记录失败');
    } else {
        wp_send_json_success(array(
            'message' => '记录添加成功',
            'record_id' => $wpdb->insert_id
        ));
    }
}

// 获取记录列表
add_action('wp_ajax_get_movie_records', 'handle_get_movie_records');
function handle_get_movie_records() {
    check_ajax_referer('movie_record_nonce', 'nonce');
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'movie_records';
    
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
    $where_clause = $category ? $wpdb->prepare("WHERE category = %s", $category) : '';
    
    $records = $wpdb->get_results("SELECT * FROM {$table_name} {$where_clause} ORDER BY created_at DESC");
    
    wp_send_json_success($records);
}

// 更新记录
add_action('wp_ajax_update_movie_record', 'handle_update_movie_record');
function handle_update_movie_record() {
    check_ajax_referer('movie_record_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    $id = intval($_POST['id']);
    $title = sanitize_text_field($_POST['title']);
    $category = sanitize_text_field($_POST['category']);
    $poster_url = esc_url_raw($_POST['poster_url']);
    $link_url = esc_url_raw($_POST['link_url']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'movie_records';
    
    $result = $wpdb->update(
        $table_name,
        array(
            'title' => $title,
            'category' => $category,
            'poster_url' => $poster_url,
            'link_url' => $link_url
        ),
        array('id' => $id),
        array('%s', '%s', '%s', '%s'),
        array('%d')
    );
    
    if ($result === false) {
        wp_send_json_error('更新记录失败');
    } else {
        wp_send_json_success('记录更新成功');
    }
}

// 删除记录
add_action('wp_ajax_delete_movie_record', 'handle_delete_movie_record');
function handle_delete_movie_record() {
    check_ajax_referer('movie_record_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    $id = intval($_POST['id']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'movie_records';
    
    $result = $wpdb->delete(
        $table_name,
        array('id' => $id),
        array('%d')
    );
    
    if ($result === false) {
        wp_send_json_error('删除记录失败');
    } else {
        wp_send_json_success('记录删除成功');
    }
}