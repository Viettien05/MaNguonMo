<?php
function qltx_giao_dien_add_kh() {
    global $wpdb;
    $table_name = 'tbl_khachhang';
    $message = '';

    if (isset($_POST['btn_save_kh'])) {
        $cccd = sanitize_text_field($_POST['txt_cccd']);

        // KIỂM TRA TRÙNG
        $check_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE cccd = %s", $cccd));

        if ($check_exists > 0) {
            $message = '<div class="notice notice-error is-dismissible"><p>❌ Lỗi: Số CCCD <strong>'.$cccd.'</strong> đã tồn tại trong hệ thống!</p></div>';
        } else {
            $data = array(
                'ho_ten'        => sanitize_text_field($_POST['txt_hoten']),
                'cccd'          => $cccd,
                'so_dien_thoai' => sanitize_text_field($_POST['txt_sdt']),
                'dia_chi'       => sanitize_textarea_field($_POST['txt_diachi'])
            );

            $inserted = $wpdb->insert($table_name, $data);
            if ($inserted) {
                $message = '<div class="notice notice-success is-dismissible"><p>✅ Thêm khách hàng thành công! Đang quay lại danh sách...</p></div>';
                echo "<script>setTimeout(function(){ window.location.href = 'admin.php?page=qltx-list-kh'; }, 2000);</script>";
            }
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
        .qltx-form-group input, .qltx-form-group textarea { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #8c8f94; 
            border-radius: 4px; 
            font-size: 14px; 
        }
        .qltx-form-group input:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; outline: none; }
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
                    <input type="text" id="txt_hoten" name="txt_hoten" placeholder="Ví dụ: Nguyen Van A" required>
                </div>

                <div class="qltx-form-group">
                    <label for="txt_cccd">Số CCCD / Định danh</label>
                    <input type="text" id="txt_cccd" name="txt_cccd" placeholder="Nhập 12 số CCCD" required>
                </div>

                <div class="qltx-form-group">
                    <label for="txt_sdt">Số điện thoại</label>
                    <input type="text" id="txt_sdt" name="txt_sdt" placeholder="Ví dụ: 0945..." required>
                </div>

                <div class="qltx-form-group">
                    <label for="txt_diachi">Địa chỉ thường trú</label>
                    <textarea id="txt_diachi" name="txt_diachi" rows="3" placeholder="Số nhà, tên đường, quận/huyện..."></textarea>
                </div>

                <div class="qltx-footer-btns">
                    <button type="submit" name="btn_save_kh" class="button button-primary button-large">
                        💾 Lưu khách hàng
                    </button>
                    <a href="admin.php?page=qltx-list-kh" class="button button-large">
                        ❌ Hủy bỏ
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php
}