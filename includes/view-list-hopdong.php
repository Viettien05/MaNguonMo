<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_list_hd() {
    global $wpdb;
    $table_hd = 'tbl_hopdong';
    $table_xe = 'tbl_xe';
    $table_kh = 'tbl_khachhang';
    $message = '';

    // --- 1. XỬ LÝ LOGIC XOÁ & TRẢ XE ---
    if (isset($_GET['action'])) {
        $id_hd = intval($_GET['id_hd']);
        if ($_GET['action'] == 'tra_xe' && isset($_GET['id_xe'])) {
            $id_xe = intval($_GET['id_xe']);
            $wpdb->update($table_hd, array('tinh_trang_hd' => 'Da hoan thanh'), array('id_hopdong' => $id_hd));
            $wpdb->update($table_xe, array('trang_thai' => 'Trong'), array('id_xe' => $id_xe));
            $message = '<div class="notice notice-success is-dismissible qltx-notice"><p>✅ Đã trả xe và giải phóng trạng thái trống thành công!</p></div>';
        }
        if ($_GET['action'] == 'delete_hd') {
            $wpdb->delete($table_hd, array('id_hopdong' => $id_hd));
            $message = '<div class="notice notice-warning is-dismissible qltx-notice"><p>🗑️ Đã xóa bản ghi hợp đồng thành công.</p></div>';
        }
    }

    // --- 2. TẢI SẴN DỮ LIỆU ---
    $sql_init = "SELECT hd.*, kh.ho_ten, xe.ten_xe, xe.bien_so 
                 FROM $table_hd hd 
                 JOIN $table_kh kh ON hd.id_kh = kh.id_kh 
                 JOIN $table_xe xe ON hd.id_xe = xe.id_xe 
                 ORDER BY hd.id_hopdong DESC";
    $results_init = $wpdb->get_results($sql_init);
    ?>

    <div class="wrap qltx-container">
        <div class="qltx-header-flex">
            <h1 style="margin:0; font-size: 24px; font-weight: 700;">📜 Quản lý Hợp đồng</h1>
            <a href="admin.php?page=qltx-add-hd" class="button button-primary button-large" style="display: inline-flex; align-items: center; gap: 5px;">
                <span class="dashicons dashicons-plus-alt2" style="font-size: 16px; width: 16px; height: 16px; margin: 0;"></span> Lập hợp đồng mới
            </a>
        </div>

        <div class="qltx-search-box" style="background: #fff; padding: 15px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); border: 1px solid #dcdcde; display: flex; gap: 12px; align-items: center;">
            <input type="text" id="qltx-ajax-search" placeholder="Tìm theo tên khách hàng, xe, biển số..." 
                    style="width: 320px; height: 36px; border-radius: 6px; border: 1px solid #8c8f94; padding: 0 12px; margin: 0;">
            
            <select id="qltx-ajax-status" style="height: 36px; border-radius: 6px; border: 1px solid #8c8f94; min-width: 160px; margin: 0;">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="Dang thuc hien">● Đang thuê</option>
                <option value="Da hoan thanh">✓ Đã hoàn thành</option>
            </select>
        </div>

        <?php echo $message; ?>

        <div class="qltx-table-card" style="margin-top: 20px;">
            <table class="wp-list-table widefat fixed striped qltx-table">
                <thead>
                    <tr>
                        <th width="6%" style="text-align: center;">ID</th>
                        <th width="18%">Khách hàng</th>
                        <th width="20%">Thông tin xe</th>
                        <th width="12%">Ngày thuê</th>
                        <th width="14%">Ngày trả dự kiến</th>
                        <th width="13%">Tổng tiền</th>
                        <th width="12%">Trạng thái</th>
                        <th width="15%" style="text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="qltx-table-body">
                    <?php if ($results_init): foreach ($results_init as $row): 
                        $is_active = ($row->tinh_trang_hd == 'Dang thuc hien'); 
                        $search_data = mb_strtolower($row->ho_ten . ' ' . $row->ten_xe . ' ' . $row->bien_so, 'UTF-8');
                    ?>
                    <tr class="qltx-hd-row" 
                        data-search="<?php echo esc_attr($search_data); ?>" 
                        data-status="<?php echo esc_attr($row->tinh_trang_hd); ?>">
                        
                        <td style="text-align: center;"><span style="color:#8c8f94; font-weight: 600;">#<?php echo $row->id_hopdong; ?></span></td>
                        <td><strong><?php echo esc_html($row->ho_ten); ?></strong></td>
                        <td>
                            <span class="car-info"><?php echo esc_html($row->ten_xe); ?></span>
                            <br><span class="license-plate"><?php echo esc_html($row->bien_so); ?></span>
                        </td>
                        <td><small style="color: #646970;">📅</small> <?php echo date('d/m/Y', strtotime($row->ngay_thue)); ?></td>
                        <td><small style="color: #646970;">🏁</small> <?php echo date('d/m/Y', strtotime($row->ngay_tra_du_kien)); ?></td>
                        <td><span class="price-text"><?php echo number_format($row->tong_tien); ?>đ</span></td>
                        <td>
                            <span class="status-badge <?php echo $is_active ? 'status-active' : 'status-done'; ?>">
                                <?php echo $is_active ? '● ĐANG THUÊ' : '✓ ĐÃ TRẢ'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="row-actions-visible">
                                <?php if ($is_active): ?>
                                    <a href="admin.php?page=qltx-list-hd&action=tra_xe&id_hd=<?php echo $row->id_hopdong; ?>&id_xe=<?php echo $row->id_xe; ?>" 
                                       class="btn-tra-xe" onclick="return confirm('Xác nhận khách đã trả xe?')">Trả xe</a>
                                <?php endif; ?>
                                <a href="admin.php?page=qltx-list-hd&action=delete_hd&id_hd=<?php echo $row->id_hopdong; ?>" 
                                   class="btn-delete" onclick="return confirm('Xóa vĩnh viễn hợp đồng này?')">Xóa</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <tr id="qltx-no-results" style="display: none;">
                        <td colspan="8" style="text-align: center; padding: 40px; color: #646970;">
                            <span class="dashicons dashicons-warning" style="font-size: 28px; width: 28px; height: 28px; margin-bottom: 8px;"></span><br>
                            Không tìm thấy dữ liệu hợp đồng nào khớp với bộ lọc.
                        </td>
                    </tr>
                    
                    <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #646970;">Chưa có dữ liệu.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputSearch = document.getElementById('qltx-ajax-search');
        const selectStatus = document.getElementById('qltx-ajax-status');
        const rows = document.querySelectorAll('.qltx-hd-row');
        const noResultsRow = document.getElementById('qltx-no-results');

        function xoaDauTiengViet(str) {
            str = str.toLowerCase();
            str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
            str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
            str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
            str = str.replace(/ò|ó|ọ|ỏ|ã|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
            str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
            str = str.replace(/ỳ|á|ỵ|ỷ|ỹ/g, "y");
            str = str.replace(/đ/g, "d");
            return str;
        }

        function phoiHopBoLoc() {
            const keyword = xoaDauTiengViet(inputSearch.value.trim());
            const targetStatus = selectStatus.value;
            let coKetQua = false;
            rows.forEach(row => {
                const rowDataText = xoaDauTiengViet(row.getAttribute('data-search'));
                const rowStatus = row.getAttribute('data-status');
                const matchSearch = rowDataText.includes(keyword);
                const matchStatus = (targetStatus === "") || (rowStatus === targetStatus);
                if (matchSearch && matchStatus) {
                    row.style.display = ''; 
                    coKetQua = true;
                } else {
                    row.style.display = 'none';
                }
            });
            if (noResultsRow) noResultsRow.style.display = coKetQua ? 'none' : 'table-row';
        }
        inputSearch.addEventListener('input', phoiHopBoLoc);
        selectStatus.addEventListener('change', phoiHopBoLoc);
    });
    </script>
    <?php
}