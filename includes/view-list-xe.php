<?php
if (!defined('ABSPATH')) exit; // Bảo mật

function qltx_giao_dien_list_xe() {
    global $wpdb;
    $table_xe = 'tbl_xe';
    $message = '';
    $phan_khuc_options = array('Hạng A', 'Hạng B', 'Hạng C', 'Hạng D');

    // ==========================================
    // 1. XỬ LÝ LOGIC NGHIỆP VỤ (THÊM / SỬA / XÓA)
    // ==========================================

    // THAO TÁC XÓA XE
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id_xe = intval($_GET['id']);
        $deleted = $wpdb->delete($table_xe, array('id_xe' => $id_xe), array('%d'));
        
        if ($deleted) {
            $message = '<div class="notice notice-success" style="padding:12px; margin-bottom:20px; background:#d4edda; color:#155724; border-left:4px solid #28a745; border-radius:4px; font-weight:500;">✅ Đã xóa phương tiện ra khỏi hệ thống thành công!</div>';
        } else {
            $message = '<div class="notice notice-error" style="padding:12px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-left:4px solid #dc3545; border-radius:4px; font-weight:500;">❌ Lỗi hệ thống, không thể xóa xe vào lúc này.</div>';
        }
    }

    if (isset($_GET['action']) && $_GET['action'] === 'confirm_rental' && isset($_GET['id'])) {
        $id_xe = intval($_GET['id']);
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field($_GET['_wpnonce']) : '';

        if (!wp_verify_nonce($nonce, 'qltx_confirm_rental_' . $id_xe)) {
            $message = '<div class="notice notice-error" style="padding:12px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-left:4px solid #dc3545; border-radius:4px; font-weight:500;">Phiên xác nhận không hợp lệ, vui lòng thử lại.</div>';
        } else {
            $updated = $wpdb->update($table_xe, array('trang_thai' => 'Dang thue'), array('id_xe' => $id_xe, 'trang_thai' => 'Cho xac nhan'));
            $wpdb->query($wpdb->prepare("UPDATE tbl_hopdong SET tinh_trang_hd = 'Dang thuc hien', trang_thai_thanh_toan = 'Da thanh toan' WHERE id_xe = %d AND tinh_trang_hd = 'Cho xac nhan'", $id_xe));

            if ($updated !== false) {
                $message = '<div class="notice notice-success" style="padding:12px; margin-bottom:20px; background:#d4edda; color:#155724; border-left:4px solid #28a745; border-radius:4px; font-weight:500;">Đã xác nhận khách hàng đã thanh toán. Xe đã chuyển sang trạng thái đang thuê.</div>';
            }
        }
    }

    // THAO TÁC LƯU DỮ LIỆU
    if (isset($_POST['qltx_save_xe'])) {
        $id_xe      = isset($_POST['xe_id']) ? intval($_POST['xe_id']) : 0;
        $ten_xe     = sanitize_text_field($_POST['ten_xe']);
        $bien_so    = sanitize_text_field($_POST['bien_so']);
        $hang_xe    = sanitize_text_field($_POST['hang_xe']);
        $loai_xe    = sanitize_text_field($_POST['loai_xe']);
        $vi_tri_do  = sanitize_text_field($_POST['vi_tri_do']);
        if (!in_array($loai_xe, $phan_khuc_options, true)) {
            $loai_xe = 'Hạng A';
        }
        $gia_thue   = floatval($_POST['gia_thue']);
        $trang_thai = sanitize_text_field($_POST['trang_thai']);
        $hinh_anh   = esc_url_raw($_POST['hinh_anh']);

        $data = array(
            'ten_xe'     => $ten_xe,
            'bien_so'    => $bien_so,
            'hang_xe'    => $hang_xe,
            'loai_xe'    => $loai_xe,
            'vi_tri_do'  => $vi_tri_do,
            'gia_thue'   => $gia_thue,
            'trang_thai' => $trang_thai,
            'hinh_anh'   => $hinh_anh
        );

        if ($id_xe > 0) {
            $updated = $wpdb->update($table_xe, $data, array('id_xe' => $id_xe));
            if ($updated !== false) {
                $message = '<div class="notice notice-success" style="padding:12px; margin-bottom:20px; background:#d4edda; color:#155724; border-left:4px solid #28a745; border-radius:4px; font-weight:500;">✅ Cập nhật thông tin phương tiện thành công!</div>';
            }
        } else {
            $inserted = $wpdb->insert($table_xe, $data);
            if ($inserted) {
                $message = '<div class="notice notice-success" style="padding:12px; margin-bottom:20px; background:#d4edda; color:#155724; border-left:4px solid #28a745; border-radius:4px; font-weight:500;">✅ Đã thêm phương tiện mới vào đội xe thành công!</div>';
            } else {
                $error_db = $wpdb->last_error;
                $message = '<div class="notice notice-error" style="padding:12px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-left:4px solid #dc3545; border-radius:4px; font-weight:500;">❌ Lỗi hệ thống database: ' . esc_html($error_db) . '</div>';
            }
        }
    }

    // TRUY VẤN BỘ LỌC TÌM KIẾM & PHÂN TRANG
    $s_search    = isset($_GET['s_search']) ? sanitize_text_field($_GET['s_search']) : '';
    $f_loai_xe   = isset($_GET['f_loai_xe']) ? sanitize_text_field($_GET['f_loai_xe']) : '';
    $f_vi_tri_do = isset($_GET['f_vi_tri_do']) ? sanitize_text_field($_GET['f_vi_tri_do']) : '';
    $f_trang_thai= isset($_GET['f_trang_thai']) ? sanitize_text_field($_GET['f_trang_thai']) : '';
    $vi_tri_do_options = $wpdb->get_col("SELECT DISTINCT vi_tri_do FROM $table_xe WHERE vi_tri_do IS NOT NULL AND vi_tri_do != '' ORDER BY vi_tri_do ASC");

    $where = " WHERE 1=1";
    if (!empty($s_search)) {
        $where .= $wpdb->prepare(" AND (ten_xe LIKE %s OR bien_so LIKE %s OR hang_xe LIKE %s OR vi_tri_do LIKE %s)", '%' . $wpdb->esc_like($s_search) . '%', '%' . $wpdb->esc_like($s_search) . '%', '%' . $wpdb->esc_like($s_search) . '%', '%' . $wpdb->esc_like($s_search) . '%');
    }
    if (!empty($f_loai_xe)) {
        $where .= $wpdb->prepare(" AND loai_xe = %s", $f_loai_xe);
    }
    if (!empty($f_vi_tri_do)) {
        $where .= $wpdb->prepare(" AND vi_tri_do = %s", $f_vi_tri_do);
    }
    if (!empty($f_trang_thai)) {
        $where .= $wpdb->prepare(" AND trang_thai = %s", $f_trang_thai);
    }

    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_xe $where");
    $per_page    = 5; 
    $total_pages = ceil($total_items / $per_page);
    $paged       = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset      = ($paged - 1) * $per_page;

    $list_xe = $wpdb->get_results("SELECT * FROM $table_xe $where ORDER BY id_xe DESC LIMIT $per_page OFFSET $offset");
    ?>

    <div class="autodrive-container" style="margin: 20px 20px 0 0; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color: #1e293b;">
        
        <?php echo $message; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <div>
                <h1 style="font-size: 26px; font-weight: 700; color: #0f172a; margin: 0 0 5px 0;">Danh sách đội xe AutoDrive</h1>
                <p style="margin: 0; color: #64748b; font-size: 14px;">Quản lý toàn bộ <?php echo $total_items; ?> xe tự lái đang có trong hệ thống.</p>
            </div>
            <button onclick="moFormXe(0)" style="background: #0f172a; color: #ffffff; border: none; padding: 12px 20px; font-size: 14px; font-weight: 600; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <span class="dashicons dashicons-plus-alt2" style="font-size: 18px; width: 18px; height: 18px; margin-top:2px;"></span> Thêm xe mới
            </button>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <form method="get" action="" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="page" value="qltx-list-xe">
                <input type="text" name="s_search" value="<?php echo esc_attr($s_search); ?>" placeholder="Tìm kiếm biển số, tên xe, hãng..." style="flex: 1; min-width: 250px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none;">
                
                <select name="f_loai_xe" style="padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #fff; min-width: 150px;">
                    <option value="">Tất cả phân khúc</option>
                    <?php foreach($phan_khuc_options as $lxe): ?>
                        <option value="<?php echo esc_attr($lxe); ?>" <?php selected($f_loai_xe, $lxe); ?>><?php echo esc_html($lxe); ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="f_vi_tri_do" style="padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #fff; min-width: 170px;">
                    <option value="">Tất cả vị trí đỗ</option>
                    <?php foreach($vi_tri_do_options as $parking_option): ?>
                        <option value="<?php echo esc_attr($parking_option); ?>" <?php selected($f_vi_tri_do, $parking_option); ?>><?php echo esc_html($parking_option); ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="f_trang_thai" style="padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #fff; min-width: 150px;">
                    <option value="" <?php selected($f_trang_thai, ''); ?>>Tất cả trạng thái</option>
                    <option value="Cho xac nhan" <?php selected($f_trang_thai, 'Cho xac nhan'); ?>>Chờ xác nhận</option>
                    <option value="Trong" <?php selected($f_trang_thai, 'Trong'); ?>>🟢 Trống (Sẵn sàng)</option>
                    <option value="Dang thue" <?php selected($f_trang_thai, 'Dang thue'); ?>>🟡 Đang thuê</option>
                    <option value="Bao tri" <?php selected($f_trang_thai, 'Bao tri'); ?>>🔴 Bảo trì</option>
                </select>

                <button type="submit" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 10px 18px; font-size: 14px; font-weight: 600; border-radius: 8px; cursor: pointer;">Lọc kết quả</button>
            </form>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600;">
                        <th style="padding: 16px 20px; width: 35%;">Hình ảnh & Tên Xe</th>
                        <th style="padding: 16px 20px;">Biển số</th>
                        <th style="padding: 16px 20px;">Hãng xe</th>
                        <th style="padding: 16px 20px;">Phân khúc</th>
                        <th style="padding: 16px 20px;">Vị trí đỗ</th>
                        <th style="padding: 16px 20px;">Giá thuê / Ngày</th>
                        <th style="padding: 16px 20px;">Trạng thái</th>
                        <th style="padding: 16px 20px; text-align: center; width: 15%;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list_xe)) : ?>
                        <tr>
                            <td colspan="8" style="padding: 40px; text-align: center; color: #94a3b8; font-style: italic;">Không tìm thấy phương tiện nào phù hợp.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($list_xe as $row) : 
                            $bg_status = '#e2e8f0'; $color_status = '#475569'; $text_status = $row->trang_thai;
                            if($row->trang_thai === 'Trong') { $bg_status = '#e0f2fe'; $color_status = '#0369a1'; $text_status = 'Trống (Sẵn sàng)'; }
                            elseif($row->trang_thai === 'Cho xac nhan') { $bg_status = '#fef9c3'; $color_status = '#a16207'; $text_status = 'Chờ xác nhận'; }
                            elseif($row->trang_thai === 'Dang thue') { $bg_status = '#fef3c7'; $color_status = '#b45309'; $text_status = 'Đang thuê'; }
                            elseif($row->trang_thai === 'Bao tri') { $bg_status = '#fee2e2'; $color_status = '#b91c1c'; $text_status = 'Bảo trì'; }

                            $thumb_url = !empty($row->hinh_anh) ? $row->hinh_anh : 'https://placehold.co/120x80?text=No+Image';
                        ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 16px 20px; display: flex; align-items: center; gap: 15px;">
                                    <div style="width: 90px; height: 55px; border-radius: 6px; overflow: hidden; background: #f1f5f9; border: 1px solid #e2e8f0;">
                                        <img src="<?php echo esc_url($thumb_url); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #0f172a; font-size: 15px; margin-bottom: 3px;"><?php echo esc_html($row->ten_xe); ?></div>
                                        <div style="font-size: 12px; color: #0284c7; background: #f0f9ff; padding: 2px 6px; border-radius: 4px; display: inline-block; font-weight: 500;">AutoDrive</div>
                                    </div>
                                </td>
                                <td style="padding: 16px 20px; font-weight: 600; color: #334155;"><?php echo esc_html($row->bien_so); ?></td>
                                <td style="padding: 16px 20px;"><span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-weight: 500;"><?php echo esc_html($row->hang_xe ? $row->hang_xe : 'Chưa rõ'); ?></span></td>
                                <td style="padding: 16px 20px;"><span style="background: #e0f2fe; color: #0369a1; padding: 5px 10px; border-radius: 999px; font-size: 12px; font-weight: 700;"><?php echo esc_html($row->loai_xe ? $row->loai_xe : 'Chưa phân loại'); ?></span></td>
                                <td style="padding: 16px 20px;"><?php echo esc_html($row->vi_tri_do ? $row->vi_tri_do : 'Chưa có'); ?></td>
                                <td style="padding: 16px 20px; font-weight: 700; color: #0f172a;"><?php echo number_format($row->gia_thue, 0, ',', '.'); ?> ₫</td>
                                <td style="padding: 16px 20px;">
                                    <span style="background: <?php echo $bg_status; ?>; color: <?php echo $color_status; ?>; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                                        ● <?php echo esc_html($text_status); ?>
                                    </span>
                                </td>
                                <td style="padding: 16px 20px; text-align: center;">
                                    <div style="display: flex; gap: 8px; justify-content: center;">
                                        <button onclick='moFormXe(<?php echo json_encode($row); ?>)' style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 13px;">✏️ Sửa</button>
                                        <?php if ($row->trang_thai === 'Cho xac nhan') : ?>
                                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=qltx-list-xe&action=confirm_rental&id=' . intval($row->id_xe)), 'qltx_confirm_rental_' . intval($row->id_xe))); ?>" style="background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 6px 10px; border-radius: 6px; font-size: 13px; text-decoration: none;">Xác nhận thanh toán</a>
                                        <?php endif; ?>
                                        <a href="?page=qltx-list-xe&action=delete&id=<?php echo $row->id_xe; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa không?');" style="background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 6px 10px; border-radius: 6px; font-size: 13px; text-decoration: none;">🗑️ Xóa</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalFormXe" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(15,23,42,0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
        <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 550px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; overflow: hidden;">
            <div style="background: #f8fafc; padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modalTitle" style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;">Thêm phương tiện mới</h3>
                <span onclick="dongFormXe()" style="cursor: pointer; font-size: 22px; color: #94a3b8; font-weight: bold;">&times;</span>
            </div>

            <form method="post" action="" style="padding: 24px; margin: 0;">
                <input type="hidden" id="xe_id" name="xe_id" value="0">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #334155;">Tên xe / Model *</label>
                        <input type="text" id="ten_xe" name="ten_xe" required style="width:100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #334155;">Biển số kiểm soát *</label>
                        <input type="text" id="bien_so" name="bien_so" required style="width:100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #334155;">Hãng sản xuất</label>
                        <input type="text" id="hang_xe" name="hang_xe" placeholder="Ví dụ: Toyota, VinFast" style="width:100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #334155;">Phân khúc xe</label>
                        <select id="loai_xe" name="loai_xe" style="width:100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background:#fff;">
                            <?php foreach ($phan_khuc_options as $phan_khuc): ?>
                                <option value="<?php echo esc_attr($phan_khuc); ?>"><?php echo esc_html($phan_khuc); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #334155;">Giá thuê một ngày *</label>
                        <input type="number" id="gia_thue" name="gia_thue" required min="0" style="width:100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #334155;">Vị trí đỗ xe</label>
                        <input type="text" id="vi_tri_do" name="vi_tri_do" placeholder="Ví dụ: Bãi A - Cột 03" style="width:100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #334155;">Trạng thái vận hành *</label>
                        <select id="trang_thai" name="trang_thai" style="width:100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background:#fff;">
                            <option value="Trong">🟢 Trống (Sẵn sàng)</option>
                            <option value="Cho xac nhan">Chờ xác nhận</option>
                            <option value="Dang thue">🟡 Đang thuê</option>
                            <option value="Bao tri">🔴 Bảo trì</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #334155;">Hình ảnh xe *</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="hinh_anh" name="hinh_anh" placeholder="Chọn ảnh hoặc dán URL..." style="flex: 1; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size:14px;">
                        <button type="button" id="btn_tai_hinh_anh" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px;">🖼️ Chọn ảnh</button>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; background: #f8fafc; padding: 16px 24px; margin: 0 -24px -24px -24px; border-top: 1px solid #e2e8f0;">
                    <button type="button" onclick="dongFormXe()" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 10px 18px; border-radius: 6px; font-weight: 600; cursor: pointer;">Hủy bỏ</button>
                    <button type="submit" name="qltx_save_xe" style="background: #0f172a; color: #ffffff; border: none; padding: 10px 22px; border-radius: 6px; font-weight: 600; cursor: pointer;">Lưu phương tiện</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function moFormXe(dataXe) {
        const modal = document.getElementById('modalFormXe');
        const title = document.getElementById('modalTitle');
        const idField = document.getElementById('xe_id');
        const tenField = document.getElementById('ten_xe');
        const bienField = document.getElementById('bien_so');
        const hangField = document.getElementById('hang_xe');
        const loaiField = document.getElementById('loai_xe');
        const parkingField = document.getElementById('vi_tri_do');
        const giaField = document.getElementById('gia_thue');
        const statusField = document.getElementById('trang_thai');
        const anhField = document.getElementById('hinh_anh');

        if (dataXe === 0) {
            title.innerText = "Thêm phương tiện mới";
            idField.value = 0; tenField.value = ""; bienField.value = ""; hangField.value = ""; loaiField.value = "Hạng A"; parkingField.value = ""; giaField.value = ""; statusField.value = "Trong"; anhField.value = "";
        } else {
            title.innerText = "Chỉnh sửa thông tin xe: " + dataXe.ten_xe;
            idField.value = dataXe.id_xe;
            tenField.value = dataXe.ten_xe;
            bienField.value = dataXe.bien_so;
            hangField.value = dataXe.hang_xe ? dataXe.hang_xe : "";
            loaiField.value = dataXe.loai_xe ? dataXe.loai_xe : "Hạng A";
            parkingField.value = dataXe.vi_tri_do ? dataXe.vi_tri_do : "";
            giaField.value = dataXe.gia_thue;
            statusField.value = dataXe.trang_thai;
            anhField.value = dataXe.hinh_anh ? dataXe.hinh_anh : "";
        }
        modal.style.display = 'flex';
    }
    function dongFormXe() { document.getElementById('modalFormXe').style.display = 'none'; }
    window.onclick = function(e) { if (e.target == document.getElementById('modalFormXe')) { dongFormXe(); } }

    document.addEventListener('DOMContentLoaded', function() {
        let khung_media;
        const nut_chon_anh = document.getElementById('btn_tai_hinh_anh');
        const o_nhap_url = document.getElementById('hinh_anh');
        if (nut_chon_anh) {
            nut_chon_anh.addEventListener('click', function(e) {
                e.preventDefault();
                if (khung_media) { khung_media.open(); return; }
                khung_media = wp.media({
                    title: 'Chọn hoặc Tải ảnh xe lên hệ thống',
                    button: { text: 'Sử dụng ảnh này' },
                    multiple: false
                });
                khung_media.on('select', function() {
                    const attachment = khung_media.state().get('selection').first().toJSON();
                    o_nhap_url.value = attachment.url;
                });
                khung_media.open();
            });
        }
    });
    </script>
    <?php
}
