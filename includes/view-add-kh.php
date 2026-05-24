<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_add_kh() {
    global $wpdb;
    $table_name = 'tbl_khachhang';
    $message = '';

    if (isset($_POST['btn_save_kh'])) {
        $data = array(
            'ho_ten'        => sanitize_text_field($_POST['txt_hoten']),
            'email'         => sanitize_email($_POST['txt_email']),
            'so_dien_thoai' => sanitize_text_field($_POST['txt_sdt'])
        );

        $inserted = $wpdb->insert($table_name, $data);
        if ($inserted) {
            $message = '<div class="notice notice-success is-dismissible"><p>Thêm khách hàng thành công! Đang quay lại danh sách...</p></div>';
            echo "<script>setTimeout(function(){ window.location.href = 'admin.php?page=qltx-list-kh'; }, 1200);</script>";
        } else {
            $message = '<div class="notice notice-error is-dismissible"><p>Không thể thêm khách hàng. Lỗi database: ' . esc_html($wpdb->last_error) . '</p></div>';
        }
    }
    ?>

    <style>
        .qltx-wrap { margin-top: 20px; max-width: 700px; }
        .qltx-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .qltx-card-title {
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #23282d;
        }
        .qltx-form-group { margin-bottom: 20px; }
        .qltx-form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #50575e;
        }
        .qltx-form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 14px;
        }
        .qltx-footer-btns {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>

    <div class="wrap qltx-wrap">
        <div class="qltx-card">
            <div class="qltx-card-title">
                <span class="dashicons dashicons-id-alt" style="font-size: 30px; width: 30px; height: 30px;"></span>
                Đăng ký Khách hàng mới
            </div>

            <?php echo $message; ?>

            <form method="post">
                <div class="qltx-form-group">
                    <label for="txt_hoten">Họ và tên</label>
                    <input type="text" id="txt_hoten" name="txt_hoten" placeholder="Ví dụ: Nguyễn Văn A" required>
                </div>

                <div class="qltx-form-group">
                    <label for="txt_email">Email</label>
                    <input type="email" id="txt_email" name="txt_email" placeholder="vidu@email.com" required>
                </div>

                <div class="qltx-form-group">
                    <label for="txt_sdt">Số điện thoại</label>
                    <input type="text" id="txt_sdt" name="txt_sdt" placeholder="Ví dụ: 0945..." required>
                </div>

                <div class="qltx-footer-btns">
                    <button type="submit" name="btn_save_kh" class="button button-primary button-large">Lưu khách hàng</button>
                    <a href="admin.php?page=qltx-list-kh" class="button button-large">Hủy bỏ</a>
                </div>
            </form>
        </div>
    </div>
    <?php
}
