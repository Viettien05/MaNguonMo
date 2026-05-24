<?php
function qltx_giao_dien_add_hd() {
    global $wpdb;
    $message = '';

    // 1. Xử lý khi nhấn nút Lưu hợp đồng
    if (isset($_POST['btn_save_hd'])) {
        $id_xe = intval($_POST['sel_xe']);
        $id_kh = intval($_POST['sel_kh']);
        $ngay_thue = sanitize_text_field($_POST['txt_ngay_thue']);
        $ngay_tra  = sanitize_text_field($_POST['txt_ngay_tra']);
        $tong_tien = floatval($_POST['txt_tong_tien']);

        $inserted = $wpdb->insert(
            'tbl_hopdong',
            array(
                'id_xe' => $id_xe,
                'id_kh' => $id_kh,
                'ngay_thue' => $ngay_thue,
                'ngay_tra_du_kien' => $ngay_tra,
                'tong_tien' => $tong_tien,
                'trang_thai_thanh_toan' => 'Da thanh toan',
                'tinh_trang_hd' => 'Dang thuc hien'
            )
        );

        if ($inserted) {
            // CẬP NHẬT TRẠNG THÁI XE SANG 'Da thue'
            $wpdb->update('tbl_xe', array('trang_thai' => 'Dang thue'), array('id_xe' => $id_xe));
            
            $message = '<div class="notice notice-success"><p>✅ Lập hợp đồng thành công! Xe đã được chuyển trạng thái "Đã thuê".</p></div>';
            echo "<script>setTimeout(function(){ window.location.href = 'admin.php?page=qltx-list-hd'; }, 2000);</script>";
        }
    }

    // 2. Lấy dữ liệu cho các ô chọn (Dropdown)
    $list_kh = $wpdb->get_results("SELECT id_kh, ho_ten FROM tbl_khachhang");
    $list_xe = $wpdb->get_results("SELECT id_xe, ten_xe, gia_thue FROM tbl_xe WHERE trang_thai = 'Trong'");
    ?>

    <div class="wrap">
        <h1>📜 Lập Hợp đồng Thuê xe mới</h1>
        <?php echo $message; ?>

        <form method="post" id="form-add-hd" style="background:#fff; padding:25px; border:1px solid #ccd0d4; max-width:700px; margin-top:20px; border-radius:8px;">
            <table class="form-table">
                <tr>
                    <th>Chọn Khách hàng</th>
                    <td>
                        <select name="sel_kh" class="regular-text" required>
                            <option value="">-- Chọn khách hàng --</option>
                            <?php foreach($list_kh as $kh): ?>
                                <option value="<?php echo $kh->id_kh; ?>"><?php echo esc_html($kh->ho_ten); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Chọn Xe thuê</th>
                    <td>
                        <select name="sel_xe" id="sel_xe" class="regular-text" required onchange="tinhTongTien()">
                            <option value="" data-gia="0">-- Chọn xe đang trống --</option>
                            <?php foreach($list_xe as $xe): ?>
                                <option value="<?php echo $xe->id_xe; ?>" data-gia="<?php echo $xe->gia_thue; ?>">
                                    <?php echo esc_html($xe->ten_xe); ?> (<?php echo number_format($xe->gia_thue); ?>đ/ngày)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Ngày thuê</th>
                    <td><input type="date" name="txt_ngay_thue" id="ngay_thue" class="regular-text" required onchange="tinhTongTien()"></td>
                </tr>
                <tr>
                    <th>Ngày trả dự kiến</th>
                    <td><input type="date" name="txt_ngay_tra" id="ngay_tra" class="regular-text" required onchange="tinhTongTien()"></td>
                </tr>
                <tr>
                    <th>Tổng cộng (VNĐ)</th>
                    <td>
                        <input type="number" name="txt_tong_tien" id="tong_tien" class="regular-text" readonly style="background:#f0f0f1; font-weight:bold; color:#d63638;">
                        <p class="description">Hệ thống tự tính dựa trên đơn giá xe và số ngày thuê.</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="btn_save_hd" class="button button-primary button-large">💾 Lưu hợp đồng & Xuất xe</button>
                <a href="admin.php?page=qltx-list-hd" class="button button-large">Quay lại</a>
            </p>
        </form>
    </div>

    <script>
    function tinhTongTien() {
        const selXe = document.getElementById('sel_xe');
        const giaThue = parseFloat(selXe.options[selXe.selectedIndex].getAttribute('data-gia'));
        const ngayThue = new Date(document.getElementById('ngay_thue').value);
        const ngayTra = new Date(document.getElementById('ngay_tra').value);
        const inputTongTien = document.getElementById('tong_tien');

        if (ngayThue && ngayTra && ngayTra > ngayThue && giaThue > 0) {
            const diffTime = Math.abs(ngayTra - ngayThue);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
            inputTongTien.value = diffDays * giaThue;
        } else {
            inputTongTien.value = 0;
        }
    }
    </script>
    <?php
}
