<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_lich_su_thue_frontend() {
    global $wpdb;

    wp_enqueue_style('dashicons');

    if (!is_user_logged_in()) {
        return '<div class="qltx-history-notice">Vui lòng đăng nhập để xem lịch sử thuê xe.</div>';
    }

    $user = wp_get_current_user();
    $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM tbl_khachhang WHERE user_id = %d", $user->ID));

    $contracts = array();
    if ($customer) {
        $contracts = $wpdb->get_results($wpdb->prepare("
            SELECT hd.*, xe.ten_xe, xe.bien_so, xe.hang_xe, xe.loai_xe, xe.vi_tri_do, xe.hinh_anh
            FROM tbl_hopdong hd
            INNER JOIN tbl_xe xe ON hd.id_xe = xe.id_xe
            WHERE hd.id_kh = %d
            ORDER BY hd.id_hopdong DESC
        ", intval($customer->id_kh)));
    }

    ob_start();
    ?>
    <style>
        body.autodrive-history-page {
            margin: 0 !important;
            background: #f8fbff !important;
        }
        body.autodrive-history-page .wp-site-blocks > header,
        body.autodrive-history-page .wp-site-blocks > footer,
        body.autodrive-history-page header.wp-block-template-part,
        body.autodrive-history-page footer.wp-block-template-part,
        body.autodrive-history-page .site-header,
        body.autodrive-history-page .site-footer,
        body.autodrive-history-page .wp-block-post-title,
        body.autodrive-history-page .entry-title {
            display: none !important;
        }
        body.autodrive-history-page .wp-site-blocks,
        body.autodrive-history-page main,
        body.autodrive-history-page .entry-content,
        body.autodrive-history-page .wp-block-group,
        body.autodrive-history-page .wp-block-post-content {
            max-width: none !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        body.autodrive-history-page .entry-content > *,
        body.autodrive-history-page .wp-block-post-content > * {
            max-width: none !important;
            margin-block-start: 0 !important;
            margin-block-end: 0 !important;
        }
        .ad-history {
            width: 100vw;
            min-height: 100vh;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
            padding: 0 0 60px;
            box-sizing: border-box;
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 44%);
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .ad-history-nav {
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 max(22px, calc((100vw - 1180px) / 2));
            border-bottom: 1px solid #e8edf7;
            background: rgba(255,255,255,.96);
        }
        .ad-history-logo {
            color: #063b8e;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0;
        }
        .ad-history-menu {
            display: flex;
            align-items: center;
            gap: 36px;
            font-size: 13px;
            font-weight: 700;
            color: #5b6680;
        }
        .ad-history-menu a {
            color: #5b6680;
            text-decoration: none;
            position: relative;
        }
        .ad-history-menu a.is-active {
            color: #063b8e;
        }
        .ad-history-menu a.is-active::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -10px;
            height: 3px;
            border-radius: 999px;
            background: #063b8e;
        }
        .ad-history-user {
            color: #063b8e;
            font-size: 13px;
            font-weight: 700;
        }
        .ad-history-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 18px;
            max-width: 1180px;
            margin: 40px auto 24px;
            padding: 0 22px;
            box-sizing: border-box;
        }
        .ad-history-top h1 {
            margin: 0 0 8px;
            color: #063b8e;
            font-size: 34px;
            font-weight: 900;
            letter-spacing: 0;
        }
        .ad-history-top p {
            margin: 0;
            color: #64748b;
            font-size: 15px;
        }
        .ad-history-actions {
            display: flex;
            gap: 10px;
        }
        .ad-history-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 16px;
            border-radius: 10px;
            background: #063b8e;
            color: #fff;
            text-decoration: none;
            font-weight: 800;
        }
        .ad-history-table-wrap {
            overflow: hidden;
            max-width: 1180px;
            margin: 0 auto;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
        }
        .ad-history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .ad-history-table th {
            padding: 16px;
            background: #f8fbff;
            color: #475569;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }
        .ad-history-table td {
            padding: 16px;
            border-top: 1px solid #eef2ff;
            vertical-align: middle;
        }
        .ad-history-car {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .ad-history-car img {
            width: 82px;
            height: 54px;
            border-radius: 8px;
            object-fit: cover;
            background: #e2e8f0;
        }
        .ad-history-car strong {
            display: block;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .ad-history-car span {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }
        .ad-history-money {
            color: #063b8e;
            font-weight: 900;
        }
        .ad-history-status {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
        }
        .ad-history-status-active {
            background: #dcfce7;
            color: #166534;
        }
        .ad-history-status-pending {
            background: #fef9c3;
            color: #a16207;
        }
        .ad-history-status-done {
            background: #e2e8f0;
            color: #334155;
        }
        .ad-history-empty,
        .qltx-history-notice {
            max-width: 1130px;
            margin: 0 auto;
            padding: 24px;
            border: 1px dashed #bfdbfe;
            border-radius: 14px;
            background: #f8fbff;
            color: #475569;
            font-weight: 800;
        }
        @media (max-width: 980px) {
            .ad-history-menu {
                display: none;
            }
        }
        @media (max-width: 820px) {
            .ad-history-nav {
                padding: 0 14px;
            }
            .ad-history-user {
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                text-align: right;
            }
            .ad-history-top {
                display: block;
                margin-top: 28px;
                padding: 0 14px;
            }
            .ad-history-actions {
                margin-top: 14px;
            }
            .ad-history-table,
            .ad-history-table tbody,
            .ad-history-table tr,
            .ad-history-table td {
                display: block;
                width: 100%;
            }
            .ad-history-table thead {
                display: none;
            }
            .ad-history-table tr {
                border-top: 1px solid #eef2ff;
                padding: 12px;
            }
            .ad-history-table td {
                border: 0;
                padding: 8px 4px;
            }
        }
    </style>

    <section class="ad-history">
        <nav class="ad-history-nav">
            <div class="ad-history-logo">AutoDrive</div>
            <div class="ad-history-menu">
                <a href="<?php echo esc_url(remove_query_arg(array('autodrive_page', 'autodrive_search', 'ten_xe', 'ngay_nhan', 'ngay_tra', 'phan_khuc', 'hang_xe', 'vi_tri_do'))); ?>">Trang chủ</a>
                <a href="<?php echo esc_url(add_query_arg('autodrive_page', 'cars', remove_query_arg(array('autodrive_search', 'ten_xe', 'ngay_nhan', 'ngay_tra', 'phan_khuc', 'hang_xe', 'vi_tri_do')))); ?>">Thuê xe</a>
                <a class="is-active" href="<?php echo esc_url(add_query_arg('autodrive_page', 'history', remove_query_arg(array('autodrive_search', 'ten_xe', 'ngay_nhan', 'ngay_tra', 'phan_khuc', 'hang_xe', 'vi_tri_do')))); ?>">Lịch sử</a>
            </div>
            <div class="ad-history-user">Xin Chào, <?php echo esc_html($user->display_name ? $user->display_name : 'User'); ?></div>
        </nav>

        <div class="ad-history-top">
            <div>
                <h1>Lịch sử thuê xe</h1>
                <p>Theo dõi các hợp đồng thuê xe của riêng bạn tại AutoDrive.</p>
            </div>
            <div class="ad-history-actions">
                <a href="<?php echo esc_url(add_query_arg('autodrive_page', 'cars', remove_query_arg('autodrive_page'))); ?>">Thuê xe tiếp</a>
            </div>
        </div>

        <?php if ($contracts) : ?>
            <div class="ad-history-table-wrap">
                <table class="ad-history-table">
                    <thead>
                        <tr>
                            <th>Xe đã thuê</th>
                            <th>Ngày thuê</th>
                            <th>Ngày trả</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contracts as $contract) :
                            $is_active = ($contract->tinh_trang_hd === 'Dang thuc hien');
                            $is_pending = ($contract->tinh_trang_hd === 'Cho xac nhan');
                            $status_class = $is_pending ? 'ad-history-status-pending' : ($is_active ? 'ad-history-status-active' : 'ad-history-status-done');
                            $status_text = $is_pending ? 'Chờ xác nhận thanh toán' : ($is_active ? 'Đã thanh toán - Đang thuê' : 'Đã trả');
                            $thumb_url = !empty($contract->hinh_anh) ? $contract->hinh_anh : 'https://placehold.co/300x200?text=AutoDrive';
                            ?>
                            <tr>
                                <td>
                                    <div class="ad-history-car">
                                        <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($contract->ten_xe); ?>">
                                        <div>
                                            <strong><?php echo esc_html($contract->ten_xe); ?></strong>
                                            <span><?php echo esc_html($contract->hang_xe); ?> - <?php echo esc_html($contract->loai_xe); ?> - <?php echo esc_html($contract->vi_tri_do ? $contract->vi_tri_do : 'Chưa có vị trí đỗ'); ?> - <?php echo esc_html($contract->bien_so); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo esc_html(date('d/m/Y', strtotime($contract->ngay_thue))); ?></td>
                                <td><?php echo esc_html(date('d/m/Y', strtotime($contract->ngay_tra_du_kien))); ?></td>
                                <td class="ad-history-money"><?php echo number_format(floatval($contract->tong_tien), 0, ',', '.'); ?> đ</td>
                                <td>
                                    <span class="ad-history-status <?php echo esc_attr($status_class); ?>">
                                        <?php echo esc_html($status_text); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="ad-history-empty">Bạn chưa có hợp đồng thuê xe nào.</div>
        <?php endif; ?>
    </section>

    <script>
    document.body.classList.add('autodrive-history-page');
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('qltx_lich_su_thue', 'qltx_giao_dien_lich_su_thue_frontend');
