<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_add_hd() {
    global $wpdb;
    $message = '';
    $hom_nay = date('Y-m-d'); 
    $table_hd = 'tbl_hopdong';
    $table_xe = 'tbl_xe';
    $table_kh = 'tbl_khachhang';

    // 1. Xử lý khi nhấn nút Lưu hợp đồng
    if (isset($_POST['btn_save_hd'])) {
        if (!isset($_POST['qltx_nonce_hd']) || !wp_verify_nonce($_POST['qltx_nonce_hd'], 'qltx_add_hd_action')) {
            $message = '<div class="notice notice-error"><p>❌ Lỗi bảo mật!</p></div>';
        } else {
            $id_xe = intval($_POST['sel_xe']);
            $id_kh = intval($_POST['sel_kh']);
            $ngay_thue = sanitize_text_field($_POST['txt_ngay_thue']);
            $ngay_tra  = sanitize_text_field($_POST['txt_ngay_tra']);
            $tong_tien = floatval($_POST['txt_tong_tien']);

            // Validate logic ngày tháng
            if (strtotime($ngay_tra) <= strtotime($ngay_thue)) {
                $message = '<div class="notice notice-error is-dismissible qltx-notice"><p>❌ Lỗi: Ngày trả phải sau ngày thuê!</p></div>';
            } else {
                $inserted = $wpdb->insert(
                    $table_hd,
                    array(
                        'id_xe' => $id_xe,
                        'id_kh' => $id_kh,
                        'ngay_thue' => $ngay_thue,
                        'ngay_tra_du_kien' => $ngay_tra,
                        'tong_tien' => $tong_tien,
                        'tinh_trang_hd' => 'Dang thuc hien'
                    )
                );

                if ($inserted) {
                    $wpdb->update($table_xe, array('trang_thai' => 'Da thue'), array('id_xe' => $id_xe));
                    $message = '<div class="notice notice-success is-dismissible qltx-notice"><p>✅ Lập hợp đồng thành công!</p></div>';
                    echo "<script>setTimeout(function(){ window.location.href = 'admin.php?page=qltx-list-hd'; }, 2000);</script>";
                }
            }
        }
    }

    $list_kh = $wpdb->get_results("SELECT id_kh, ho_ten FROM $table_kh");
    $list_xe = $wpdb->get_results("SELECT id_xe, ten_xe, gia_thue FROM $table_xe WHERE trang_thai = 'Trong'");
    ?>

    <div class="wrap qltx-wrap">
        <div class="qltx-card">
            <div class="qltx-card-title">
                <span class="dashicons dashicons-media-text"></span> Lập Hợp đồng Thuê xe mới
            </div>

            <?php echo $message; ?>

            <form method="post" id="form-add-hd">
                <?php wp_nonce_field('qltx_add_hd_action', 'qltx_nonce_hd'); ?>
                
                <div class="qltx-form-group">
                    <label>Chọn Khách hàng</label>
                    <select name="sel_kh" required>
                        <option value="">-- Chọn khách hàng --</option>
                        <?php foreach($list_kh as $kh): ?>
                            <option value="<?php echo $kh->id_kh; ?>"><?php echo esc_html($kh->ho_ten); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="qltx-form-group">
                    <label>Chọn Xe thuê</label>
                    <select name="sel_xe" id="sel_xe" required onchange="tinhTongTien()">
                        <option value="" data-gia="0">-- Chọn xe đang trống --</option>
                        <?php foreach($list_xe as $xe): ?>
                            <option value="<?php echo $xe->id_xe; ?>" data-gia="<?php echo $xe->gia_thue; ?>">
                                <?php echo esc_html($xe->ten_xe); ?> (<?php echo number_format($xe->gia_thue); ?>đ/ngày)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="qltx-form-group">
                    <label>Ngày thuê</label>
                    <input type="date" name="txt_ngay_thue" id="ngay_thue" value="<?php echo $hom_nay; ?>" min="<?php echo $hom_nay; ?>" required onchange="updateNgayTraMin(); tinhTongTien();">
                </div>

                <div class="qltx-form-group">
                    <label>Ngày trả dự kiến</label>
                    <input type="date" name="txt_ngay_tra" id="ngay_tra" required onchange="tinhTongTien()">
                </div>

                <div class="qltx-form-group">
                    <label>Tổng cộng (VNĐ)</label>
                    <input type="number" name="txt_tong_tien" id="tong_tien" readonly style="background:#f0f0f1; font-weight:700;">
                </div>

                <div class="qltx-footer-btns">
                    <button type="submit" name="btn_save_hd" class="button button-primary button-large">💾 Lưu hợp đồng</button>
                    <a href="admin.php?page=qltx-list-hd" class="button button-large">❌ Hủy bỏ</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Logic chặn ngày trả trước ngày thuê giữ nguyên UI
    function updateNgayTraMin() {
        const ngayThue = document.getElementById('ngay_thue').value;
        const inputNgayTra = document.getElementById('ngay_tra');
        if (ngayThue) {
            const date = new Date(ngayThue);
            date.setDate(date.getDate() + 1);
            inputNgayTra.min = date.toISOString().split('T')[0];
            // Reset nếu ngày trả hiện tại nhỏ hơn ngày thuê
            if (inputNgayTra.value && inputNgayTra.value < inputNgayTra.min) {
                inputNgayTra.value = '';
            }
        }
    }

    function tinhTongTien() {
        const selXe = document.getElementById('sel_xe');
        const giaThue = parseFloat(selXe.options[selXe.selectedIndex].getAttribute('data-gia') || 0);
        const t1 = new Date(document.getElementById('ngay_thue').value);
        const t2 = new Date(document.getElementById('ngay_tra').value);
        
        if (t2 > t1 && giaThue > 0) {
            const diffDays = Math.ceil((t2 - t1) / (1000 * 60 * 60 * 24));
            document.getElementById('tong_tien').value = diffDays * giaThue;
        } else {
            document.getElementById('tong_tien').value = 0;
        }
    }

    // Set giá trị min ban đầu khi load trang
    window.onload = updateNgayTraMin;
    </script>
    <?php
}