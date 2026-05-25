<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_edit_kh() {
    global $wpdb;
    $table_name = 'tbl_khachhang'; 
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $message = '';

    // Xử lý khi nhấn nút Cập nhật
    if (isset($_POST['btn_update_kh'])) {
        // Kiểm tra Nonce để bảo mật
        if (!isset($_POST['qltx_nonce_kh']) || !wp_verify_nonce($_POST['qltx_nonce_kh'], 'qltx_edit_kh_action')) {
            $message = '<div class="notice notice-error"><p>❌ Lỗi bảo mật!</p></div>';
        } else {
            $updated = $wpdb->update(
                $table_name,
                array(
                    'ho_ten'        => sanitize_text_field($_POST['txt_hoten']),
                    'so_dien_thoai' => sanitize_text_field($_POST['txt_sdt']),
                    'email'         => sanitize_email($_POST['txt_email']),
                    'user_id'       => intval($_POST['txt_user_id'])
                ),
                array('id_kh' => $id)
            );
            
            $message = '<div class="notice notice-success is-dismissible"><p>✅ Cập nhật thành công!</p></div>';
            echo "<script>setTimeout(function(){ window.location.href = 'admin.php?page=qltx-list-kh'; }, 2000);</script>";
        }
    }

    // Lấy dữ liệu cũ
    $kh = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id_kh = %d", $id));

    if (!$kh) {
        echo "<div class='notice notice-error'><p>❌ Không tìm thấy khách hàng!</p></div>";
        return;
    }
    ?>
    <div class="wrap qltx-wrap">
        <div class="qltx-card">
            <div class="qltx-card-title">
                <span class="dashicons dashicons-edit"></span> Chỉnh sửa thông tin Khách hàng
            </div>

            <?php echo $message; ?>
            
            <form method="post">
                <?php wp_nonce_field('qltx_edit_kh_action', 'qltx_nonce_kh'); ?>

                <div class="qltx-form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="txt_hoten" value="<?php echo esc_attr($kh->ho_ten); ?>" required>
                </div>

                <div class="qltx-form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="txt_sdt" value="<?php echo esc_attr($kh->so_dien_thoai); ?>" required>
                </div>

                <div class="qltx-form-group">
                    <label>Email</label>
                    <input type="email" name="txt_email" value="<?php echo esc_attr($kh->email); ?>">
                </div>

                <div class="qltx-form-group">
                    <label>Liên kết tài khoản (User ID)</label>
                    <select name="txt_user_id">
                        <option value="0">Khách vãng lai (Không liên kết)</option>
                        <?php
                        $users = get_users(['fields' => ['ID', 'display_name']]);
                        foreach ($users as $user) {
                            $selected = ($kh->user_id == $user->ID) ? 'selected' : '';
                            echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="qltx-footer-btns">
                    <button type="submit" name="btn_update_kh" class="button button-primary button-large">💾 Cập nhật thay đổi</button>
                    <a href="admin.php?page=qltx-list-kh" class="button button-large">❌ Hủy bỏ</a>
                </div>
            </form>
        </div>
    </div>
    <?php
}