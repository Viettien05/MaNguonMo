<?php
/*
Plugin Name: Hệ thống Quản lý Thuê xe Tự lái mới
Author: Đồng Việt Tiến - Đàm Tiến Trình
Description: Đồ án cuối môn: Xây dựng hệ thống quản lý thuê xe bằng WordPress & PHP.
Version: 1.0
*/

if (!defined('ABSPATH')) exit;

// 1. ĐỊNH NGHĨA ĐƯỜNG DẪN
define('QLTX_PATH', plugin_dir_path(__FILE__));

// 2. NẠP CÁC FILE HỆ THỐNG
include_once QLTX_PATH . 'includes/db-handler.php';      
include_once QLTX_PATH . 'includes/view-list-xe.php';    
include_once QLTX_PATH . 'includes/view-list-kh.php';    
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

// NẠP THƯ VIỆN MEDIA WP ĐỂ ĐIỀU KHIỂN KHUNG CHỌN ẢNH TỪ FILE JS
add_action('admin_enqueue_scripts', 'qltx_load_media_library_for_admin');
function qltx_load_media_library_for_admin($hook) {
    if (isset($_GET['page']) && $_GET['page'] === 'qltx-list-xe') {
        wp_enqueue_media();
    }
}

// Nạp file CSS dùng chung vào trang quản trị
function qltx_enqueue_admin_styles() {
    wp_enqueue_style(
        'qltx-admin-style', 
        plugin_dir_url(__FILE__) . 'assets/style.css', 
        array(), 
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'qltx_enqueue_admin_styles');


// 5. GIAO DIỆN TRANG CHỦ (DASHBOARD)
function qltx_dashboard_page() {
    global $wpdb;

    // Định nghĩa tên bảng chuẩn WP
    $table_kh = 'tbl_khachhang';
    $table_xe = 'tbl_xe';
    $table_hd = 'tbl_hopdong';

    // XỬ LÝ TRUY VẤN (Thêm LIKE % để bẫy mọi trường hợp chuỗi chữ hoa, chữ thường trong DB tránh lỗi 0 dữ liệu)
    $count_kh = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table_kh");
    $xe_dang_thue = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table_xe WHERE trang_thai LIKE '%thue%' OR trang_thai LIKE '%Da thue%'");
    $xe_trong = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table_xe WHERE trang_thai LIKE '%trong%' OR trang_thai LIKE '%Trong%'");
    
    // Tổng doanh thu từ hợp đồng đã hoàn thành / đã thanh toán
    $revenue = $wpdb->get_var("SELECT SUM(tong_tien) FROM $table_hd WHERE tinh_trang_hd LIKE '%hoan thanh%' OR trang_thai_thanh_toan LIKE '%Da thanh toan%'");

    // Nhúng thư viện Chart.js
    echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
    ?>

    <div class="wrap qltx-dash-wrap" style="margin-top: 20px;">
        <div class="qltx-dash-header" style="margin-bottom: 25px; background: #fff; padding: 20px; border: 1px solid #dcdcde; border-radius: 8px;">
            <h1 style="font-size: 26px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-chart-bar" style="font-size: 28px; width: 28px; height: 28px; color: #3b82f6;"></span>
                Hệ thống Quản lý Thuê xe Tự lái
            </h1>
            <p style="margin: 6px 0 0 38px; color: #64748b; font-size: 14px;">
                Chào mừng bạn đến với hệ thống.
            </p>
        </div>

        <div class="qltx-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px;">
            
            <div class="qltx-card card-revenue" style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #dcdcde; box-shadow: 0 1px 2px rgba(0,0,0,.04);">
                <div class="card-body-flex" style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h3 style="margin: 0 0 8px 0; font-size: 14px; color: #64748b; font-weight: 500;">Tổng doanh thu</h3>
                        <div class="val" style="font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 4px;"><?php echo number_format($revenue ? $revenue : 0, 0, ',', '.'); ?>đ</div>
                        <span class="card-sub-text" style="font-size: 12px; color: #10b981; font-weight: 500;">✓ Từ hợp đồng hoàn thành</span>
                    </div>
                    <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 12px; border-radius: 10px; display: flex; align-items: center;">
                        <span class="dashicons dashicons-money-alt"></span>
                    </div>
                </div>
            </div>

            <div class="qltx-card card-renting" style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #dcdcde; box-shadow: 0 1px 2px rgba(0,0,0,.04);">
                <div class="card-body-flex" style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h3 style="margin: 0 0 8px 0; font-size: 14px; color: #64748b; font-weight: 500;">Xe đang thuê</h3>
                        <div class="val" style="font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 4px;"><?php echo $xe_dang_thue; ?> <span style="font-size: 14px; color: #64748b;">xe</span></div>
                        <span class="card-sub-text" style="font-size: 12px; color: #3b82f6; font-weight: 500;">● Khách đang vận hành</span>
                    </div>
                    <div style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 12px; border-radius: 10px; display: flex; align-items: center;">
                        <span class="dashicons dashicons-businessman"></span>
                    </div>
                </div>
            </div>

            <div class="qltx-card card-warning" style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #dcdcde; box-shadow: 0 1px 2px rgba(0,0,0,.04);">
                <div class="card-body-flex" style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h3 style="margin: 0 0 8px 0; font-size: 14px; color: #64748b; font-weight: 500;">Xe đang trống</h3>
                        <div class="val" style="font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 4px;"><?php echo $xe_trong; ?> <span style="font-size: 14px; color: #64748b;">xe</span></div>
                        <span class="card-sub-text" style="font-size: 12px; color: #f59e0b; font-weight: 500;">○ Sẵn sàng đón khách</span>
                    </div>
                    <div style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 12px; border-radius: 10px; display: flex; align-items: center;">
                        <span class="dashicons dashicons-car"></span>
                    </div>
                </div>
            </div>

            <div class="qltx-card card-danger" style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #dcdcde; box-shadow: 0 1px 2px rgba(0,0,0,.04);">
                <div class="card-body-flex" style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h3 style="margin: 0 0 8px 0; font-size: 14px; color: #64748b; font-weight: 500;">Khách hàng</h3>
                        <div class="val" style="font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 4px;"><?php echo $count_kh; ?> <span style="font-size: 14px; color: #64748b;">người</span></div>
                        <span class="card-sub-text" style="font-size: 12px; color: #ef4444; font-weight: 500;">👥 Tổng số thành viên</span>
                    </div>
                    <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 10px; display: flex; align-items: center;">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-container" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 25px;">
            <div class="chart-box" style="flex: 1; min-width: 300px; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #dcdcde; box-shadow: 0 1px 2px rgba(0,0,0,.04);">
                <h3 style="margin-top: 0; font-size: 16px; font-weight: 600; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Tỷ lệ trạng thái xe</h3>
                <div style="height:280px; display: flex; justify-content: center; align-items: center; margin-top: 15px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="chart-box" style="flex: 1; min-width: 300px; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #dcdcde; box-shadow: 0 1px 2px rgba(0,0,0,.04);">
                <h3 style="margin-top: 0; font-size: 16px; font-weight: 600; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Thống kê dữ liệu tổng quan</h3>
                <div style="height:280px; margin-top: 15px;">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px;">
            <div style="flex: 1; border: 1px solid #eee; padding: 18px; background: #fff; border-radius: 8px;">
                <h4 style="margin-top: 0; font-size: 15px;">🚗 Phân hệ bãi xe</h4>
                <p style="color:#64748b; font-size:13px;">Quản lý thông tin phương tiện, cập nhật trạng thái trống hoặc đang thuê.</p>
                <a href="admin.php?page=qltx-list-xe" class="button button-secondary">Đi tới quản lý xe</a>
            </div>
            <div style="flex: 1; border: 1px solid #eee; padding: 18px; background: #fff; border-radius: 8px;">
                <h4 style="margin-top: 0; font-size: 15px;">📜 Quản lý vận hành</h4>
                <p style="color:#64748b; font-size:13px;">Kiểm tra tiến độ thực hiện hợp đồng, tạo mới biểu mẫu bàn giao xe tự lái.</p>
                <a href="admin.php?page=qltx-list-hd" class="button button-secondary">Xem danh sách hợp đồng</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Biểu đồ Doughnut trạng thái xe
            var ctxStatus = document.getElementById('statusChart').getContext('2d');
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Đang thuê', 'Xe trống'],
                    datasets: [{
                        data: [<?php echo $xe_dang_thue; ?>, <?php echo $xe_trong; ?>],
                        backgroundColor: ['#3b82f6', '#f59e0b'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // 2. Biểu đồ Bar cột đứng
            var ctxBar = document.getElementById('barChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['Khách hàng', 'Tổng số xe'],
                    datasets: [{
                        label: 'Số lượng',
                        data: [<?php echo $count_kh; ?>, <?php echo ($xe_dang_thue + $xe_trong); ?>],
                        backgroundColor: ['#ef4444', '#10b981'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        });
    </script>
    <?php
}