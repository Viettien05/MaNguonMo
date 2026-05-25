<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_add_kh() {
    global $wpdb;
    $table_name = 'tbl_khachhang'; 
    $message = '';

    if (isset($_POST['btn_save_kh'])) {
        // Kiểm tra Nonce để bảo mật form 
        if (!isset($_POST['qltx_nonce_kh']) || !wp_verify_nonce($_POST['qltx_nonce_kh'], 'qltx_add_kh_action')) {
            $message = '<div class="notice notice-error"><p>❌ Lỗi bảo mật!</p></div>';
        } else {
            // Lấy và làm sạch dữ liệu
            $data = array(
                'ho_ten'        => sanitize_text_field($_POST['txt_hoten']),
                'so_dien_thoai' => sanitize_text_field($_POST['txt_sdt']),
                'email'         => sanitize_email($_POST['txt_email']),
                'user_id'       => intval($_POST['txt_user_id'])
            );

            $inserted = $wpdb->insert($table_name, $data);

            if ($inserted) {
                $message = '<div class="notice notice-success is-dismissible"><p>✅ Thêm khách hàng thành công!</p></div>';
                echo "<script>setTimeout(function(){ window.location.href = 'admin.php?page=qltx-list-kh'; }, 2000);</script>";
            } else {
                $message = '<div class="notice notice-error is-dismissible"><p>❌ Có lỗi xảy ra khi lưu vào database.</p></div>';
            }
        }
    }
    ?>
    
    <div class="wrap qltx-wrap">
        <div class="qltx-card">
            <div class="qltx-card-title">
                <span class="dashicons dashicons-id-alt" style="font-size: 30px; width: 30px; height: 30px;"></span> 
                Đăng ký Khách hàng mới
            </div>

            <?php echo $message; ?>
            
            <form method="post">
                <?php wp_nonce_field('qltx_add_kh_action', 'qltx_nonce_kh'); ?>
                
                <div class="qltx-form-group">
                    <label for="txt_hoten">Họ và tên</label>
                    <input type="text" id="txt_hoten" name="txt_hoten" required>
                </div>

                <div class="qltx-form-group">
                    <label for="txt_sdt">Số điện thoại</label>
                    <input type="text" id="txt_sdt" name="txt_sdt" required>
                </div>

                <div class="qltx-form-group">
                    <label for="txt_email">Email</label>
                    <input type="email" id="txt_email" name="txt_email">
                </div>

                <div class="qltx-form-group">
                    <label for="txt_user_id">Liên kết tài khoản (nếu có)</label>
                    <select name="txt_user_id" id="txt_user_id">
                        <option value="0">Khách vãng lai (Không liên kết)</option>
                        <?php
                        $users = get_users(['fields' => ['ID', 'display_name']]);
                        foreach ($users as $user) {
                            echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="qltx-footer-btns">
                    <button type="submit" name="btn_save_kh" class="button button-primary button-large">
                        💾 Lưu khách hàng
                    </button>
                    <a href="admin.php?page=qltx-list-kh" class="button button-large">❌ Hủy bỏ</a>
                </div>
            </form>
        </div>
    </div>
    <?php
}