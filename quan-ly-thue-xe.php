<?php
/*
Plugin Name: Hệ thống Quản lý Thuê xe Tự lái
Author: Đồng Việt Tiến - Đàm Tiến Trình
Description: Đồ án cuối môn: Xây dựng hệ thống quản lý thuê xe bằng WordPress & PHP.
Version: 1.0
*/

if (!defined('ABSPATH')) exit;

// 1. ĐỊNH NGHĨA ĐƯỜNG DẪN
define('QLTX_PATH', plugin_dir_path(__FILE__));

// 2. NẠP CÁC FILE HỆ THỐNG
include_once QLTX_PATH . 'includes/db-handler.php';      // Xử lý Database
include_once QLTX_PATH . 'includes/view-list-xe.php';    // Giao diện danh sách xe
include_once QLTX_PATH . 'includes/view-list-kh.php';    // Giao diện danh sách khách hàng
include_once QLTX_PATH . 'includes/view-add-kh.php';
include_once QLTX_PATH . 'includes/view-edit-kh.php';

include_once QLTX_PATH . 'includes/view-add-hopdong.php';
include_once QLTX_PATH . 'includes/view-list-hopdong.php';
include_once QLTX_PATH . 'includes/view-detail-hopdong.php';
include_once QLTX_PATH . 'includes/view-report-doanhthu.php';

include_once QLTX_PATH . 'includes/view-home.php';
include_once QLTX_PATH . 'includes/view-frontend-cars.php';
include_once QLTX_PATH . 'includes/view-rental-history.php';
include_once QLTX_PATH . 'includes/view-auth.php';

// 3. KÍCH HOẠT TẠO BẢNG KHI ACTIVE PLUGIN
register_activation_hook(__FILE__, 'qltx_init_database');
add_action('admin_init', 'qltx_remove_cccd_column');
add_action('init', 'qltx_migrate_xe_columns');
add_action('init', 'qltx_migrate_hopdong_columns');

// 4. ĐĂNG KÝ MENU TRONG ADMIN
add_action('admin_menu', 'qltx_register_menu');

function qltx_register_menu() {
    // Menu chính: Quản Lý Thuê Xe
    add_menu_page(
        'Quản Lý Thuê Xe', 
        'Quản Lý Thuê Xe', 
        'manage_options', 
        'qltx-main', 
        'qltx_dashboard_page', 
        'dashicons-car', 
        6
    );

    // Menu con: Danh sách xe
    add_submenu_page(
        'qltx-main', 
        'Danh sách xe', 
        '🚗 Quản lý xe', 
        'manage_options', 
        'qltx-list-xe', 
        'qltx_giao_dien_list_xe'
    );

    // Menu con: Quản lý khách hàng
    add_submenu_page(
        'qltx-main', 
        'Quản lý khách', 
        '👥 Quản lý khách hàng', 
        'manage_options', 
        'qltx-list-kh', 
        'qltx_giao_dien_list_kh'
    );

    add_submenu_page(null, 'Thêm khách hàng', '', 'manage_options', 'qltx-add-kh', 'qltx_giao_dien_add_kh');
    add_submenu_page(null, 'Sửa khách hàng', '', 'manage_options', 'qltx-edit-kh', 'qltx_giao_dien_edit_kh');

    add_submenu_page('qltx-main', 'Danh sách hợp đồng', '📜 Hợp đồng', 'manage_options', 'qltx-list-hd', 'qltx_giao_dien_list_hd');
    add_submenu_page('qltx-main', 'Thống kê doanh thu', '📊 Thống kê doanh thu', 'manage_options', 'qltx-report-doanhthu', 'qltx_giao_dien_report_doanhthu');
    
    // Trang thêm hợp đồng mới
    add_submenu_page(null, 'Lập hợp đồng mới', '➕ Lập hợp đồng', 'manage_options', 'qltx-add-hd', 'qltx_giao_dien_add_hd');
    add_submenu_page(null, 'Chi tiết hợp đồng', '', 'manage_options', 'qltx-detail-hd', 'qltx_giao_dien_detail_hd');
}

// =========================================================================
// CHỖ THÊM MỚI: NẠP THƯ VIỆN MEDIA WP ĐỂ ĐIỀU KHIỂN KHUNG CHỌN ẢNH TỪ FILE JS
// =========================================================================
add_action('admin_enqueue_scripts', 'qltx_load_media_library_for_admin');
function qltx_load_media_library_for_admin($hook) {
    // Chỉ ép nạp thư viện khi đang ở đúng trang Quản lý xe để tránh làm nặng các trang khác
    if (isset($_GET['page']) && $_GET['page'] === 'qltx-list-xe') {
        wp_enqueue_media();
    }
}

// 5. GIAO DIỆN TRANG CHỦ (DASHBOARD)
function qltx_dashboard_page() {
    ?>
    <div class="wrap">
        <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-top: 20px;">
            <h1>Hệ thống Quản lý Thuê xe Tự lái</h1>
            <p>Chào mừng bạn đến với hệ thống quản lý đồ án của <strong>Tiến và Trình</strong>.</p>
            <hr>
            <div style="display: flex; gap: 20px;">
                <div style="flex: 1; border: 1px solid #eee; padding: 15px;">
                    <h3>📂 Quản lý Đội xe</h3>
                    <p>Theo dõi tình trạng xe, biển số và giá thuê.</p>
                    <a href="admin.php?page=qltx-list-xe" class="button button-primary">Xem danh sách xe</a>
                </div>
                <div style="flex: 1; border: 1px solid #eee; padding: 15px;">
                    <h3>👥 Quản lý Khách hàng</h3>
                    <p>Lưu trữ thông tin CCCD và số điện thoại khách thuê.</p>
                    <a href="admin.php?page=qltx-list-kh" class="button button-primary">Xem khách hàng</a>
                </div>
            </div>
        </div>
    </div>
    <?php
}
