<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_list_kh() {
    global $wpdb;
    $table_name = 'tbl_khachhang'; 

    // LOGIC XOÁ KHÁCH HÀNG
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id_delete = intval($_GET['id']);
        $wpdb->delete($table_name, array('id_kh' => $id_delete));
        echo "<div class='notice notice-success is-dismissible qltx-notice'><p>✅ Đã xoá khách hàng thành công!</p></div>";
    }

    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id_kh DESC");
    ?>
    
    <div class="qltx-container">
        <div class="qltx-header-flex">
            <h1 style="margin:0; font-size: 24px; font-weight: 700;">👥 Quản lý Khách hàng</h1>
            <a href="admin.php?page=qltx-add-kh" class="button button-primary button-large" style="display: inline-flex; align-items: center; gap: 5px;">
                <span class="dashicons dashicons-plus-alt2" style="font-size: 16px; width: 16px; height: 16px; margin: 0;"></span> Thêm khách hàng mới
            </a>
        </div>

        <div class="qltx-search-box" style="background: #fff; padding: 15px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); border: 1px solid #dcdcde;">
            <input type="text" id="qltx-kh-search" placeholder="Tìm theo tên, email hoặc số điện thoại..." 
                   style="width: 380px; height: 36px; border-radius: 6px; border: 1px solid #8c8f94; padding: 0 12px; margin: 0;">
        </div>

        <div class="qltx-table-card" style="margin-top: 20px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th width="25%">Họ tên khách hàng</th>
                        <th width="20%">Số điện thoại</th>
                        <th width="25%">Email</th>
                        <th width="15%">User ID</th>
                        <th width="15%" style="text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="qltx-kh-table-body">
                    <?php if ($results): foreach ($results as $row): 
                        // Cập nhật dữ liệu tìm kiếm cho phù hợp với DB mới
                        $search_data = mb_strtolower($row->ho_ten . ' ' . $row->email . ' ' . $row->so_dien_thoai, 'UTF-8');
                    ?>
                    <tr class="qltx-kh-row" data-search="<?php echo esc_attr($search_data); ?>" style="transition: opacity 0.15s ease-in-out;">
                        <td>
                            <strong style="font-size: 14px; color: #1d2327;"><?php echo esc_html($row->ho_ten); ?></strong>
                        </td>
                        <td>
                            <span class="badge-contact">
                                <span class="dashicons dashicons-phone" style="font-size: 13px; width: 13px; height: 13px; margin: 0;"></span>
                                <?php echo esc_html($row->so_dien_thoai); ?>
                            </span>
                        </td>
                        <td style="color: #2c3338; font-size: 13px;"><?php echo esc_html($row->email); ?></td>
                        <td><?php echo $row->user_id > 0 ? esc_html($row->user_id) : '<em>Không</em>'; ?></td>
                        <td>
                            <div class="row-actions-visible">
                                <a href="admin.php?page=qltx-edit-kh&id=<?php echo $row->id_kh; ?>" class="btn-edit" title="Sửa thông tin">Sửa</a> | 
                                <a href="admin.php?page=qltx-list-kh&action=delete&id=<?php echo $row->id_kh; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xoá khách hàng này?')" title="Xóa khách hàng">Xoá</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <tr id="qltx-no-results" style="display: none;">
                        <td colspan="5" style="text-align: center; padding: 40px; color: #646970;">
                            <span class="dashicons dashicons-warning" style="font-size: 28px; width: 28px; height: 28px; margin-bottom: 8px;"></span><br>
                            Không tìm thấy dữ liệu khách hàng nào phù hợp.
                        </td>
                    </tr>
                    
                    <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #646970;">Chưa có dữ liệu khách hàng nào.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputSearch = document.getElementById('qltx-kh-search');
        const rows = document.querySelectorAll('.qltx-kh-row');
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

        inputSearch.addEventListener('input', function() {
            const keyword = xoaDauTiengViet(inputSearch.value.trim());
            let hasResults = false;

            rows.forEach(row => {
                const rowData = xoaDauTiengViet(row.getAttribute('data-search'));
                if (rowData.includes(keyword)) {
                    row.style.display = ''; 
                    hasResults = true;
                } else {
                    row.style.display = 'none'; 
                }
            });

            if (noResultsRow) {
                noResultsRow.style.display = hasResults ? 'none' : 'table-row';
            }
        });
    });
    </script>
    <?php
}