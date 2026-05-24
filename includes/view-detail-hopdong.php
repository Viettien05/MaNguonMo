<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_detail_hd() {
    global $wpdb;

    $id_hd = isset($_GET['id_hd']) ? intval($_GET['id_hd']) : 0;
    if ($id_hd <= 0) {
        echo '<div class="wrap"><div class="notice notice-error"><p>Không tìm thấy mã hợp đồng.</p></div></div>';
        return;
    }

    $sql = $wpdb->prepare("
        SELECT hd.*,
               kh.ho_ten, kh.email, kh.so_dien_thoai,
               xe.ten_xe, xe.bien_so, xe.hang_xe, xe.loai_xe, xe.gia_thue, xe.hinh_anh
        FROM tbl_hopdong hd
        INNER JOIN tbl_khachhang kh ON hd.id_kh = kh.id_kh
        INNER JOIN tbl_xe xe ON hd.id_xe = xe.id_xe
        WHERE hd.id_hopdong = %d
    ", $id_hd);

    $hd = $wpdb->get_row($sql);
    if (!$hd) {
        echo '<div class="wrap"><div class="notice notice-error"><p>Hợp đồng không tồn tại hoặc đã bị xóa.</p></div></div>';
        return;
    }

    $ngay_thue = date('d/m/Y', strtotime($hd->ngay_thue));
    $ngay_tra = date('d/m/Y', strtotime($hd->ngay_tra_du_kien));
    $so_ngay = max(1, ceil((strtotime($hd->ngay_tra_du_kien) - strtotime($hd->ngay_thue)) / DAY_IN_SECONDS));
    $is_active = ($hd->tinh_trang_hd === 'Dang thuc hien');
    $is_paid = ($hd->trang_thai_thanh_toan === 'Da thanh toan');
    $thumb_url = !empty($hd->hinh_anh) ? $hd->hinh_anh : 'https://placehold.co/480x300?text=No+Image';
    ?>

    <style>
        .qltx-detail-wrap { margin:20px 20px 0 0; color:#1d2327; }
        .qltx-detail-actions { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
        .qltx-contract { background:#fff; border:1px solid #dcdcde; border-radius:8px; padding:28px; max-width:980px; box-shadow:0 1px 3px rgba(0,0,0,.05); }
        .qltx-contract-head { display:flex; justify-content:space-between; gap:20px; border-bottom:2px solid #1d2327; padding-bottom:18px; margin-bottom:22px; }
        .qltx-contract-title { margin:0; font-size:26px; text-transform:uppercase; letter-spacing:0; }
        .qltx-contract-code { text-align:right; color:#50575e; }
        .qltx-contract-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:20px; }
        .qltx-info-box { border:1px solid #dcdcde; border-radius:8px; padding:16px; background:#fbfbfc; }
        .qltx-info-box h2 { margin:0 0 12px; font-size:17px; }
        .qltx-info-row { display:flex; justify-content:space-between; gap:16px; padding:8px 0; border-bottom:1px solid #eee; }
        .qltx-info-row:last-child { border-bottom:0; }
        .qltx-info-label { color:#646970; }
        .qltx-info-value { font-weight:600; text-align:right; }
        .qltx-car-photo { width:100%; height:220px; object-fit:cover; border-radius:8px; border:1px solid #dcdcde; margin-bottom:14px; background:#f6f7f7; }
        .qltx-total-box { background:#f6f7f7; border:1px solid #dcdcde; border-radius:8px; padding:18px; display:flex; justify-content:space-between; align-items:center; margin-top:18px; }
        .qltx-total-money { font-size:26px; color:#b32d2e; font-weight:800; }
        .qltx-status { display:inline-block; padding:6px 12px; border-radius:999px; font-weight:700; font-size:12px; }
        .qltx-status-active { background:#d1e7dd; color:#0f5132; }
        .qltx-status-done { background:#e2e3e5; color:#41464b; }
        .qltx-signature { display:grid; grid-template-columns:1fr 1fr; gap:80px; margin-top:34px; text-align:center; }
        .qltx-signature strong { display:block; margin-bottom:70px; }
        @media print {
            #adminmenumain, #wpadminbar, #wpfooter, .notice, .qltx-detail-actions { display:none !important; }
            #wpcontent { margin-left:0 !important; padding-left:0 !important; }
            .qltx-detail-wrap { margin:0; }
            .qltx-contract { max-width:none; border:0; box-shadow:none; padding:0; }
            .qltx-car-photo { max-height:180px; }
        }
        @media (max-width: 900px) {
            .qltx-contract-grid, .qltx-contract-head, .qltx-signature { grid-template-columns:1fr; display:block; }
            .qltx-contract-code { text-align:left; margin-top:12px; }
        }
    </style>

    <div class="wrap qltx-detail-wrap">
        <div class="qltx-detail-actions">
            <div>
                <h1 class="wp-heading-inline">Chi tiết hợp đồng</h1>
                <a href="admin.php?page=qltx-list-hd" class="page-title-action">Quay lại danh sách</a>
            </div>
            <button type="button" class="button button-primary button-large" onclick="window.print()">In hợp đồng</button>
        </div>

        <div class="qltx-contract">
            <div class="qltx-contract-head">
                <div>
                    <h2 class="qltx-contract-title">Hợp đồng thuê xe tự lái</h2>
                    <p>Hệ thống quản lý thuê xe xây dựng bằng WordPress và PHP.</p>
                </div>
                <div class="qltx-contract-code">
                    <strong>Mã hợp đồng: #<?php echo esc_html($hd->id_hopdong); ?></strong><br>
                    Ngày lập: <?php echo esc_html($ngay_thue); ?><br>
                    <span class="qltx-status <?php echo $is_active ? 'qltx-status-active' : 'qltx-status-done'; ?>">
                        <?php echo $is_active ? 'Đang thực hiện' : 'Đã hoàn thành'; ?>
                    </span>
                </div>
            </div>

            <div class="qltx-contract-grid">
                <div class="qltx-info-box">
                    <h2>Thông tin khách hàng</h2>
                    <div class="qltx-info-row"><span class="qltx-info-label">Họ tên</span><span class="qltx-info-value"><?php echo esc_html($hd->ho_ten); ?></span></div>
                    <div class="qltx-info-row"><span class="qltx-info-label">Email</span><span class="qltx-info-value"><?php echo esc_html($hd->email); ?></span></div>
                    <div class="qltx-info-row"><span class="qltx-info-label">Số điện thoại</span><span class="qltx-info-value"><?php echo esc_html($hd->so_dien_thoai); ?></span></div>
                </div>

                <div class="qltx-info-box">
                    <h2>Thông tin xe thuê</h2>
                    <img class="qltx-car-photo" src="<?php echo esc_url($thumb_url); ?>" alt="Ảnh xe">
                    <div class="qltx-info-row"><span class="qltx-info-label">Tên xe</span><span class="qltx-info-value"><?php echo esc_html($hd->ten_xe); ?></span></div>
                    <div class="qltx-info-row"><span class="qltx-info-label">Biển số</span><span class="qltx-info-value"><?php echo esc_html($hd->bien_so); ?></span></div>
                    <div class="qltx-info-row"><span class="qltx-info-label">Hãng xe</span><span class="qltx-info-value"><?php echo esc_html($hd->hang_xe); ?></span></div>
                    <div class="qltx-info-row"><span class="qltx-info-label">Loại xe</span><span class="qltx-info-value"><?php echo esc_html($hd->loai_xe); ?></span></div>
                </div>
            </div>

            <div class="qltx-info-box">
                <h2>Thông tin thuê xe</h2>
                <div class="qltx-info-row"><span class="qltx-info-label">Ngày thuê</span><span class="qltx-info-value"><?php echo esc_html($ngay_thue); ?></span></div>
                <div class="qltx-info-row"><span class="qltx-info-label">Ngày trả dự kiến</span><span class="qltx-info-value"><?php echo esc_html($ngay_tra); ?></span></div>
                <div class="qltx-info-row"><span class="qltx-info-label">Số ngày thuê</span><span class="qltx-info-value"><?php echo esc_html($so_ngay); ?> ngày</span></div>
                <div class="qltx-info-row"><span class="qltx-info-label">Đơn giá</span><span class="qltx-info-value"><?php echo number_format(floatval($hd->gia_thue), 0, ',', '.'); ?> đ/ngày</span></div>
            </div>

            <div class="qltx-total-box">
                <strong>Tổng tiền thanh toán <span class="qltx-status <?php echo $is_paid ? 'qltx-status-active' : 'qltx-status-done'; ?>" style="margin-left:10px;"><?php echo $is_paid ? 'Đã thanh toán' : 'Chưa thanh toán'; ?></span></strong>
                <span class="qltx-total-money"><?php echo number_format(floatval($hd->tong_tien), 0, ',', '.'); ?> đ</span>
            </div>

            <div class="qltx-signature">
                <div>
                    <strong>Bên cho thuê</strong>
                    <span>Quản trị viên</span>
                </div>
                <div>
                    <strong>Bên thuê xe</strong>
                    <span><?php echo esc_html($hd->ho_ten); ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php
}
