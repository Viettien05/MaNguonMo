<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_report_doanhthu() {
    global $wpdb;

    $table_hd = 'tbl_hopdong';
    $table_xe = 'tbl_xe';
    $table_kh = 'tbl_khachhang';

    $selected_year = isset($_GET['nam']) ? intval($_GET['nam']) : intval(date('Y'));
    $selected_month = isset($_GET['thang']) ? intval($_GET['thang']) : 0;

    $years = $wpdb->get_col("SELECT DISTINCT YEAR(ngay_thue) FROM $table_hd ORDER BY YEAR(ngay_thue) DESC");
    if (empty($years)) {
        $years = array($selected_year);
    }

    $where_time = $wpdb->prepare("YEAR(hd.ngay_thue) = %d", $selected_year);
    if ($selected_month > 0) {
        $where_time .= $wpdb->prepare(" AND MONTH(hd.ngay_thue) = %d", $selected_month);
    }

    $tong_doanh_thu = $wpdb->get_var("
        SELECT COALESCE(SUM(hd.tong_tien), 0)
        FROM $table_hd hd
        WHERE hd.trang_thai_thanh_toan = 'Da thanh toan'
        AND $where_time
    ");

    $so_hd_da_thanh_toan = $wpdb->get_var("
        SELECT COUNT(*)
        FROM $table_hd hd
        WHERE hd.trang_thai_thanh_toan = 'Da thanh toan'
        AND $where_time
    ");

    $so_hd_dang_thue = $wpdb->get_var("
        SELECT COUNT(*)
        FROM $table_hd hd
        WHERE hd.tinh_trang_hd = 'Dang thuc hien'
        AND $where_time
    ");

    $monthly_rows = $wpdb->get_results($wpdb->prepare("
        SELECT MONTH(ngay_thue) AS thang,
               COUNT(*) AS so_hop_dong,
               COALESCE(SUM(CASE WHEN trang_thai_thanh_toan = 'Da thanh toan' THEN tong_tien ELSE 0 END), 0) AS doanh_thu
        FROM $table_hd
        WHERE YEAR(ngay_thue) = %d
        GROUP BY MONTH(ngay_thue)
        ORDER BY MONTH(ngay_thue)
    ", $selected_year));

    $monthly_map = array();
    foreach ($monthly_rows as $row) {
        $monthly_map[intval($row->thang)] = $row;
    }

    $top_xe = $wpdb->get_results("
        SELECT xe.ten_xe, xe.bien_so, COUNT(hd.id_hopdong) AS so_luot, COALESCE(SUM(hd.tong_tien), 0) AS tong_tien
        FROM $table_hd hd
        INNER JOIN $table_xe xe ON hd.id_xe = xe.id_xe
        WHERE hd.trang_thai_thanh_toan = 'Da thanh toan' AND $where_time
        GROUP BY hd.id_xe, xe.ten_xe, xe.bien_so
        ORDER BY so_luot DESC, tong_tien DESC
        LIMIT 5
    ");

    $top_kh = $wpdb->get_results("
        SELECT kh.ho_ten, kh.so_dien_thoai, COUNT(hd.id_hopdong) AS so_luot, COALESCE(SUM(hd.tong_tien), 0) AS tong_tien
        FROM $table_hd hd
        INNER JOIN $table_kh kh ON hd.id_kh = kh.id_kh
        WHERE hd.trang_thai_thanh_toan = 'Da thanh toan' AND $where_time
        GROUP BY hd.id_kh, kh.ho_ten, kh.so_dien_thoai
        ORDER BY so_luot DESC, tong_tien DESC
        LIMIT 5
    ");
    ?>

    <style>
        .qltx-report-wrap { margin: 20px 20px 0 0; color: #1d2327; }
        .qltx-report-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:20px; }
        .qltx-report-title { margin:0 0 6px; font-size:26px; font-weight:700; }
        .qltx-report-subtitle { margin:0; color:#646970; }
        .qltx-filter-card, .qltx-report-card, .qltx-report-table-card { background:#fff; border:1px solid #dcdcde; border-radius:8px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .qltx-filter-card { padding:14px; }
        .qltx-stat-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:16px; margin-bottom:18px; }
        .qltx-report-card { padding:18px; }
        .qltx-stat-label { color:#646970; font-size:13px; margin-bottom:8px; }
        .qltx-stat-value { font-size:26px; font-weight:800; color:#0f172a; }
        .qltx-stat-note { margin-top:8px; color:#646970; font-size:12px; }
        .qltx-report-layout { display:grid; grid-template-columns:1.2fr .8fr; gap:18px; }
        .qltx-report-table-card { overflow:hidden; margin-bottom:18px; }
        .qltx-report-table-card h2 { margin:0; padding:16px 18px; border-bottom:1px solid #f0f0f1; font-size:17px; }
        .qltx-report-table { width:100%; border-collapse:collapse; }
        .qltx-report-table th, .qltx-report-table td { padding:13px 16px; border-bottom:1px solid #f0f0f1; text-align:left; }
        .qltx-report-table th { background:#f6f7f7; color:#50575e; font-size:12px; text-transform:uppercase; }
        .qltx-money { color:#b32d2e; font-weight:700; }
        .qltx-rank { width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; background:#f0f6fc; color:#2271b1; font-weight:700; }
        @media (max-width: 1100px) {
            .qltx-stat-grid, .qltx-report-layout { grid-template-columns:1fr; }
            .qltx-report-header { display:block; }
            .qltx-filter-card { margin-top:14px; }
        }
    </style>

    <div class="wrap qltx-report-wrap">
        <div class="qltx-report-header">
            <div>
                <h1 class="qltx-report-title">Thống kê doanh thu</h1>
                <p class="qltx-report-subtitle">Báo cáo quản trị phục vụ demo đồ án WordPress và PHP.</p>
            </div>

            <form method="get" class="qltx-filter-card">
                <input type="hidden" name="page" value="qltx-report-doanhthu">
                <select name="thang">
                    <option value="0">Tất cả tháng</option>
                    <?php for ($month = 1; $month <= 12; $month++) : ?>
                        <option value="<?php echo esc_attr($month); ?>" <?php selected($selected_month, $month); ?>>Tháng <?php echo esc_html($month); ?></option>
                    <?php endfor; ?>
                </select>
                <select name="nam">
                    <?php foreach ($years as $year) : ?>
                        <option value="<?php echo esc_attr($year); ?>" <?php selected($selected_year, intval($year)); ?>>Năm <?php echo esc_html($year); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button button-primary">Lọc báo cáo</button>
            </form>
        </div>

        <div class="qltx-stat-grid">
            <div class="qltx-report-card">
                <div class="qltx-stat-label">Tổng doanh thu đã thanh toán</div>
                <div class="qltx-stat-value"><?php echo number_format(floatval($tong_doanh_thu), 0, ',', '.'); ?> đ</div>
                <div class="qltx-stat-note">Chỉ tính hợp đồng đã xác nhận thanh toán.</div>
            </div>
            <div class="qltx-report-card">
                <div class="qltx-stat-label">Số hợp đồng đã thanh toán</div>
                <div class="qltx-stat-value"><?php echo intval($so_hd_da_thanh_toan); ?></div>
                <div class="qltx-stat-note">Dùng để trình bày hiệu quả vận hành.</div>
            </div>
            <div class="qltx-report-card">
                <div class="qltx-stat-label">Hợp đồng đang thực hiện</div>
                <div class="qltx-stat-value"><?php echo intval($so_hd_dang_thue); ?></div>
                <div class="qltx-stat-note">Theo dõi số xe đang được khách thuê.</div>
            </div>
        </div>

        <div class="qltx-report-layout">
            <div class="qltx-report-table-card">
                <h2>Doanh thu theo tháng năm <?php echo esc_html($selected_year); ?></h2>
                <table class="qltx-report-table">
                    <thead>
                        <tr>
                            <th>Tháng</th>
                            <th>Số hợp đồng</th>
                            <th>Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($month = 1; $month <= 12; $month++) :
                            $row = isset($monthly_map[$month]) ? $monthly_map[$month] : null;
                            ?>
                            <tr>
                                <td>Tháng <?php echo esc_html($month); ?></td>
                                <td><?php echo $row ? intval($row->so_hop_dong) : 0; ?></td>
                                <td class="qltx-money"><?php echo $row ? number_format(floatval($row->doanh_thu), 0, ',', '.') : 0; ?> đ</td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div>
                <div class="qltx-report-table-card">
                    <h2>Xe cho thuê nhiều nhất</h2>
                    <table class="qltx-report-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Xe</th>
                                <th>Lượt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($top_xe) : $index = 1; foreach ($top_xe as $xe) : ?>
                                <tr>
                                    <td><span class="qltx-rank"><?php echo esc_html($index++); ?></span></td>
                                    <td>
                                        <strong><?php echo esc_html($xe->ten_xe); ?></strong><br>
                                        <span style="color:#646970;"><?php echo esc_html($xe->bien_so); ?></span>
                                    </td>
                                    <td><?php echo intval($xe->so_luot); ?></td>
                                </tr>
                            <?php endforeach; else : ?>
                                <tr><td colspan="3">Chưa có dữ liệu.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="qltx-report-table-card">
                    <h2>Khách hàng thuê nhiều nhất</h2>
                    <table class="qltx-report-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Khách hàng</th>
                                <th>Lượt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($top_kh) : $index = 1; foreach ($top_kh as $kh) : ?>
                                <tr>
                                    <td><span class="qltx-rank"><?php echo esc_html($index++); ?></span></td>
                                    <td>
                                        <strong><?php echo esc_html($kh->ho_ten); ?></strong><br>
                                        <span style="color:#646970;"><?php echo esc_html($kh->so_dien_thoai); ?></span>
                                    </td>
                                    <td><?php echo intval($kh->so_luot); ?></td>
                                </tr>
                            <?php endforeach; else : ?>
                                <tr><td colspan="3">Chưa có dữ liệu.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
}
