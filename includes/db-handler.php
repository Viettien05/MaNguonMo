<?php
function qltx_init_database() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // 1. Tạo bảng Xe
    $sql_xe = "CREATE TABLE IF NOT EXISTS tbl_xe (
        id_xe INT NOT NULL AUTO_INCREMENT,
        ten_xe VARCHAR(100) NOT NULL,
        bien_so VARCHAR(20) NOT NULL,
        loai_xe VARCHAR(50),
        gia_thue DECIMAL(10,2) NOT NULL,
        trang_thai VARCHAR(20) DEFAULT 'Trong',
        PRIMARY KEY (id_xe)
    ) $charset_collate;";

    // 2. Tạo bảng Khách hàng
    $sql_khach = "CREATE TABLE IF NOT EXISTS tbl_khachhang (
        id_kh INT NOT NULL AUTO_INCREMENT,
        ho_ten VARCHAR(100) NOT NULL,
        cccd VARCHAR(20) NOT NULL,
        so_dien_thoai VARCHAR(15),
        dia_chi TEXT,
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
        tinh_trang_hd VARCHAR(30) DEFAULT 'Dang thuc hien',
        PRIMARY KEY (id_hopdong)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_xe);
    dbDelta($sql_khach);
    dbDelta($sql_hd);
}