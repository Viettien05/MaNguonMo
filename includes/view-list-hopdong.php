<?php
function qltx_giao_dien_list_hd() {
    global $wpdb;
    $table_hd = 'tbl_hopdong';
    $table_xe = 'tbl_xe';
    $message = '';

    // --- 1. XỬ LÝ LOGIC (Giữ nguyên logic của Trình) ---
    if (isset($_GET['action'])) {
        $id_hd = intval($_GET['id_hd']);
        if ($_GET['action'] == 'tra_xe' && isset($_GET['id_xe'])) {
            $id_xe = intval($_GET['id_xe']);
            $wpdb->update($table_hd, array('tinh_trang_hd' => 'Da hoan thanh'), array('id_hopdong' => $id_hd));
            $wpdb->update($table_xe, array('trang_thai' => 'Trong'), array('id_xe' => $id_xe));
            $message = '<div class="notice notice-success is-dismissible"><p>✅ Đã trả xe và giải phóng trạng thái trống.</p></div>';
        }
        if ($_GET['action'] == 'delete_hd') {
            $wpdb->delete($table_hd, array('id_hopdong' => $id_hd));
            $message = '<div class="notice notice-warning is-dismissible"><p>🗑️ Đã xóa bản ghi hợp đồng.</p></div>';
        }
    }

    $sql = "SELECT hd.*, kh.ho_ten, xe.ten_xe, xe.bien_so 
            FROM $table_hd hd
            JOIN tbl_khachhang kh ON hd.id_kh = kh.id_kh
            JOIN tbl_xe xe ON hd.id_xe = xe.id_xe
            ORDER BY hd.id_hopdong DESC";
    $results = $wpdb->get_results($sql);
    ?>

    <style>
        /* Nâng cấp CSS cho Dashboard chuyên nghiệp */
        .qltx-wrap { margin: 20px 20px 0 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .qltx-table { 
            background: #fff; 
            border-radius: 8px; 
            overflow: hidden; 
            box-shadow: 0 2px 15px rgba(0,0,0,0.05) !important;
            border: none !important;
        }
        .qltx-table thead th { 
            background: #f8f9fa; 
            padding: 15px !important; 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 12px;
            color: #555;
        }
        .qltx-table tbody td { padding: 15px !important; vertical-align: middle !important; }
        
        /* Badge trạng thái */
        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }
        .status-active { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-done { background: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; }
        
        /* Text & Price */
        .price-text { color: #d63638; font-weight: 800; font-size: 14px; }
        .car-info { color: #2271b1; font-weight: 600; }
        .license-plate { 
            background: #333; 
            color: #fff; 
            padding: 2px 6px; 
            border-radius: 4px; 
            font-size: 10px; 
            font-family: monospace;
        }

        /* Nút bấm */
        .btn-tra-xe {
            background: #2271b1;
            color: white !important;
            padding: 5px 12px;
            border-radius: 4px;
            text-decoration: none;
            transition: 0.3s;
            font-size: 12px;
        }
        .btn-tra-xe:hover { background: #135e96; }
        .btn-delete { color: #d63638; text-decoration: none; font-size: 12px; margin-left: 10px; }
        .btn-delete:hover { text-decoration: underline; }
    </style>

    <div class="wrap qltx-wrap">
        <h1 class="wp-heading-inline">📜 Quản lý Hợp đồng</h1>
        <a href="admin.php?page=qltx-add-hd" class="page-title-action">➕ Lập hợp đồng mới</a>
        <hr class="wp-header-end">

        <?php echo $message; ?>

        <table class="wp-list-table widefat fixed striped qltx-table" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="15%">Khách hàng</th>
                    <th width="20%">Thông tin xe</th>
                    <th width="12%">Ngày thuê</th>
                    <th width="12%">Ngày trả dự kiến</th>
                    <th width="13%">Tổng tiền</th>
                    <th width="10%">Trạng thái</th>
                    <th width="13%">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results): foreach ($results as $row): ?>
                <tr>
                    <td><span style="color:#999;">#<?php echo $row->id_hopdong; ?></span></td>
                    <td><strong><?php echo esc_html($row->ho_ten); ?></strong></td>
                    <td>
                        <span class="car-info"><?php echo esc_html($row->ten_xe); ?></span>< 
                        <br><span class="license-plate"><?php echo esc_html($row->bien_so); ?></span>
                    </td>
                    <td><small>📅</small> <?php echo date('d/m/Y', strtotime($row->ngay_thue)); ?></td>
                    <td><small>🏁</small> <?php echo date('d/m/Y', strtotime($row->ngay_tra_du_kien)); ?></td>
                    <td><span class="price-text"><?php echo number_format($row->tong_tien); ?>đ</span></td>
                    <td>
                        <?php 
                            $is_active = ($row->tinh_trang_hd == 'Dang thuc hien');
                            echo '<span class="status-badge ' . ($is_active ? 'status-active' : 'status-done') . '">';
                            echo $is_active ? '● ĐANG THUÊ' : '✓ ĐÃ TRẢ';
                            echo '</span>';
                        ?>
                    </td>
                    <td>
                        <?php if ($is_active): ?>
                            <a href="admin.php?page=qltx-list-hd&action=tra_xe&id_hd=<?php echo $row->id_hopdong; ?>&id_xe=<?php echo $row->id_xe; ?>" 
                               class="btn-tra-xe" 
                               onclick="return confirm('Xác nhận khách đã trả xe?')">Trả xe</a>
                        <?php endif; ?>
                        
                        <a href="admin.php?page=qltx-list-hd&action=delete_hd&id_hd=<?php echo $row->id_hopdong; ?>" 
                           class="btn-delete" 
                           onclick="return confirm('Xóa vĩnh viễn hợp đồng này?')">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="8" style="text-align: center; padding: 30px; color: #999;">Chưa có dữ liệu hợp đồng nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}