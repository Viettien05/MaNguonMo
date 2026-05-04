<?php
function qltx_giao_dien_edit_kh() {
    global $wpdb;
    $table_name = 'tbl_khachhang';
    $id = intval($_GET['id']);
    $message = '';

    // Xử lý khi nhấn nút Cập nhật
    if (isset($_POST['btn_update_kh'])) {
        $wpdb->update(
            $table_name,
            array(
                'ho_ten' => sanitize_text_field($_POST['txt_hoten']),
                'cccd'   => sanitize_text_field($_POST['txt_cccd']),
                'so_dien_thoai' => sanitize_text_field($_POST['txt_sdt']),
                'dia_chi' => sanitize_textarea_field($_POST['txt_diachi'])
            ),
            array('id_kh' => $id)
        );
        $message = '<div class="updated"><p>Cập nhật thông tin thành công! Đang quay lại danh sách...</p></div>';
        echo "<script>setTimeout(function(){ window.location.href = 'admin.php?page=qltx-list-kh'; }, 2000);</script>";
    }

    // Lấy dữ liệu cũ để đổ vào Form
    $kh = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id_kh = %d", $id));

    if (!$kh) {
        echo "<div class='error'><p>Không tìm thấy khách hàng!</p></div>";
        return;
    }
    ?>
    <div class="wrap">
        <h1>📝 Chỉnh sửa thông tin khách hàng</h1>
        <?php echo $message; ?>
        <form method="post" style="background:#fff; padding:20px; border:1px solid #ccd0d4; margin-top:20px; max-width:600px;">
            <table class="form-table">
                <tr>
                    <th>Họ và tên</th>
                    <td><input type="text" name="txt_hoten" value="<?php echo esc_attr($kh->ho_ten); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th>Số CCCD</th>
                    <td><input type="text" name="txt_cccd" value="<?php echo esc_attr($kh->cccd); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th>Số điện thoại</th>
                    <td><input type="text" name="txt_sdt" value="<?php echo esc_attr($kh->so_dien_thoai); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th>Địa chỉ</th>
                    <td><textarea name="txt_diachi" rows="3" class="regular-text"><?php echo esc_textarea($kh->dia_chi); ?></textarea></td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="btn_update_kh" class="button button-primary">Cập nhật thay đổi</button>
                <a href="admin.php?page=qltx-list-kh" class="button">Quay lại danh sách</a>
            </p>
        </form>
    </div>
    <?php
}