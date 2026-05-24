<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_list_kh() {
    global $wpdb;
    $table_name = 'tbl_khachhang';

    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id_delete = intval($_GET['id']);
        $wpdb->delete($table_name, array('id_kh' => $id_delete));
        echo "<div class='updated'><p>Đã xoá khách hàng thành công!</p></div>";
    }

    $search = isset($_POST['s']) ? sanitize_text_field($_POST['s']) : '';
    if (!empty($search)) {
        $keyword = '%' . $wpdb->esc_like($search) . '%';
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE ho_ten LIKE %s OR email LIKE %s OR so_dien_thoai LIKE %s",
            $keyword,
            $keyword,
            $keyword
        ));
    } else {
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id_kh DESC");
    }
    ?>

    <style>
        .qltx-container { margin: 20px 20px 0 0; }
        .qltx-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 15px 20px;
            border: 1px solid #ccd0d4;
            border-radius: 8px 8px 0 0;
            border-bottom: none;
        }
        .qltx-search-box {
            background: #fdfdfd;
            padding: 15px 20px;
            border: 1px solid #ccd0d4;
            border-bottom: none;
        }
        .qltx-table-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 0 0 8px 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .badge-contact {
            background: #f0f6fb;
            color: #2271b1;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .row-actions-visible { color: #a7aaad; font-size: 13px; }
        .btn-edit { color: #2271b1; text-decoration: none; font-weight: 500; }
        .btn-delete { color: #d63638; text-decoration: none; font-weight: 500; }
        .wp-list-table th { font-weight: 700 !important; color: #1d2327; }
    </style>

    <div class="qltx-container">
        <div class="qltx-header-flex">
            <h1 style="margin:0; font-size: 23px;">Quản lý Khách hàng</h1>
            <a href="admin.php?page=qltx-add-kh" class="button button-primary button-large">
                <span class="dashicons dashicons-plus-alt2" style="margin-top:4px;"></span> Thêm khách hàng mới
            </a>
        </div>

        <div class="qltx-search-box">
            <form method="post" style="display: flex; gap: 10px; width: 100%;">
                <input type="text" name="s" value="<?php echo esc_attr($search); ?>"
                       placeholder="Tìm theo tên, email hoặc số điện thoại..."
                       style="width: 350px; height: 35px; border-radius: 4px;">
                <button type="submit" class="button button-secondary">
                    <span class="dashicons dashicons-search" style="margin-top:3px;"></span> Tìm kiếm
                </button>
                <?php if (!empty($search)) : ?>
                    <a href="admin.php?page=qltx-list-kh" class="button">Xóa bộ lọc</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="qltx-table-card">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th width="25%">Họ tên khách hàng</th>
                        <th width="25%">Email</th>
                        <th width="20%">Số điện thoại</th>
                        <th width="15%" style="text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($results) : foreach ($results as $row) : ?>
                        <tr>
                            <td><strong style="font-size: 14px; color: #2271b1;"><?php echo esc_html($row->ho_ten); ?></strong></td>
                            <td><?php echo esc_html($row->email); ?></td>
                            <td>
                                <span class="badge-contact">
                                    <span class="dashicons dashicons-phone" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                    <?php echo esc_html($row->so_dien_thoai); ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="row-actions-visible">
                                    <a href="admin.php?page=qltx-edit-kh&id=<?php echo intval($row->id_kh); ?>" class="btn-edit">Sửa</a> |
                                    <a href="admin.php?page=qltx-list-kh&action=delete&id=<?php echo intval($row->id_kh); ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Bạn có chắc chắn muốn xoá khách hàng này?')">Xoá</a>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; else : ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px; color: #646970;">
                                <span class="dashicons dashicons-warning" style="font-size: 20px;"></span><br>
                                Không tìm thấy dữ liệu khách hàng nào phù hợp.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
