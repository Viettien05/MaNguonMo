<?php
function qltx_init_database() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // 1. Tạo bảng Xe (Đã bổ sung cột hang_xe và hinh_anh chuẩn Git)
    $sql_xe = "CREATE TABLE IF NOT EXISTS tbl_xe (
        id_xe INT NOT NULL AUTO_INCREMENT,
        ten_xe VARCHAR(100) NOT NULL,
        bien_so VARCHAR(20) NOT NULL,
        hang_xe VARCHAR(50) DEFAULT '',
        loai_xe VARCHAR(50),
        vi_tri_do VARCHAR(100) DEFAULT '',
        gia_thue DECIMAL(10,2) NOT NULL,
        hinh_anh TEXT DEFAULT '',
        trang_thai VARCHAR(20) DEFAULT 'Trong',
        PRIMARY KEY (id_xe)
    ) $charset_collate;";

    // 2. Tạo bảng Khách hàng
    $sql_khach = "CREATE TABLE IF NOT EXISTS tbl_khachhang (
        id_kh INT NOT NULL AUTO_INCREMENT,
        ho_ten VARCHAR(100) NOT NULL,
        so_dien_thoai VARCHAR(15),
        email VARCHAR(100),
        user_id BIGINT(20) DEFAULT 0,
        PRIMARY KEY (id_kh)
    ) $charset_collate;";

    // 3. Tạo bảng Hợp đồng
    $sql_hd = "CREATE TABLE IF NOT EXISTS tbl_hopdong (
        id_hopdong INT NOT NULL AUTO_INCREMENT,
        id_xe INT NOT NULL,
        id_kh INT NOT NULL,
        ngay_thue DATE NOT NULL,
        ngay_tra_du_kien DATE NOT NULL,
        tong_tien DECIMAL(15,2),
        trang_thai_thanh_toan VARCHAR(30) DEFAULT 'Chua thanh toan',
        tinh_trang_hd VARCHAR(30) DEFAULT 'Dang thuc hien',
        PRIMARY KEY (id_hopdong)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_xe);
    dbDelta($sql_khach);
    dbDelta($sql_hd);
    qltx_migrate_xe_columns();
    qltx_migrate_hopdong_columns();
    qltx_migrate_khachhang_columns();
}

function qltx_remove_cccd_column() {
    qltx_migrate_xe_columns();
    qltx_migrate_hopdong_columns();
    qltx_migrate_khachhang_columns();
}

function qltx_migrate_xe_columns() {
    global $wpdb;

    $vi_tri_do_exists = $wpdb->get_var("SHOW COLUMNS FROM tbl_xe LIKE 'vi_tri_do'");
    if (!$vi_tri_do_exists) {
        $wpdb->query("ALTER TABLE tbl_xe ADD COLUMN vi_tri_do VARCHAR(100) DEFAULT '' AFTER loai_xe");
    }
}

function qltx_migrate_hopdong_columns() {
    global $wpdb;

    $payment_exists = $wpdb->get_var("SHOW COLUMNS FROM tbl_hopdong LIKE 'trang_thai_thanh_toan'");
    if (!$payment_exists) {
        $wpdb->query("ALTER TABLE tbl_hopdong ADD COLUMN trang_thai_thanh_toan VARCHAR(30) DEFAULT 'Chua thanh toan' AFTER tong_tien");
        $wpdb->query("UPDATE tbl_hopdong SET trang_thai_thanh_toan = 'Da thanh toan' WHERE tinh_trang_hd IN ('Dang thuc hien', 'Da hoan thanh')");
    }
}

function qltx_migrate_khachhang_columns() {
    global $wpdb;

    $cccd_exists = $wpdb->get_var("SHOW COLUMNS FROM tbl_khachhang LIKE 'cccd'");
    if ($cccd_exists) {
        $wpdb->query("ALTER TABLE tbl_khachhang DROP COLUMN cccd");
    }

    $email_exists = $wpdb->get_var("SHOW COLUMNS FROM tbl_khachhang LIKE 'email'");
    if (!$email_exists) {
        $wpdb->query("ALTER TABLE tbl_khachhang ADD COLUMN email VARCHAR(100) AFTER so_dien_thoai");
    }

    $dia_chi_exists = $wpdb->get_var("SHOW COLUMNS FROM tbl_khachhang LIKE 'dia_chi'");
    if ($dia_chi_exists) {
        $wpdb->query("ALTER TABLE tbl_khachhang DROP COLUMN dia_chi");
    }

}
