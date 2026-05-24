<?php
if (!defined('ABSPATH')) exit;

function qltx_get_or_create_current_customer() {
    global $wpdb;

    if (!is_user_logged_in()) {
        return null;
    }

    $user = wp_get_current_user();
    $kh = $wpdb->get_row($wpdb->prepare("SELECT * FROM tbl_khachhang WHERE user_id = %d", $user->ID));

    if ($kh) {
        return $kh;
    }

    $wpdb->insert(
        'tbl_khachhang',
        array(
            'ho_ten' => $user->display_name ? $user->display_name : $user->user_login,
            'email' => $user->user_email,
            'so_dien_thoai' => '',
            'user_id' => $user->ID
        )
    );

    return $wpdb->get_row($wpdb->prepare("SELECT * FROM tbl_khachhang WHERE user_id = %d", $user->ID));
}

function qltx_giao_dien_danh_sach_xe_frontend() {
    global $wpdb;

    wp_enqueue_style('dashicons');

    if (!is_user_logged_in()) {
        return '<div class="qltx-rental-notice">Vui lòng đăng nhập để xem và đặt xe.</div>';
    }

    $message = '';
    $today = current_time('Y-m-d');
    $customer = qltx_get_or_create_current_customer();
    $current_user = wp_get_current_user();
    $search_name = isset($_GET['ten_xe']) ? sanitize_text_field($_GET['ten_xe']) : '';
    $filter_brand = isset($_GET['hang_xe']) ? sanitize_text_field($_GET['hang_xe']) : '';
    $filter_segment = isset($_GET['phan_khuc']) ? sanitize_text_field($_GET['phan_khuc']) : '';
    $filter_parking = isset($_GET['vi_tri_do']) ? sanitize_text_field($_GET['vi_tri_do']) : '';
    $segment_options = array('Hạng A', 'Hạng B', 'Hạng C', 'Hạng D');
    $brand_options = $wpdb->get_col("SELECT DISTINCT hang_xe FROM tbl_xe WHERE hang_xe IS NOT NULL AND hang_xe != '' ORDER BY hang_xe ASC");
    $parking_options = $wpdb->get_col("SELECT DISTINCT vi_tri_do FROM tbl_xe WHERE vi_tri_do IS NOT NULL AND vi_tri_do != '' ORDER BY vi_tri_do ASC");

    if (isset($_POST['qltx_confirm_booking'])) {
        if (!isset($_POST['qltx_booking_nonce']) || !wp_verify_nonce($_POST['qltx_booking_nonce'], 'qltx_confirm_booking')) {
            $message = '<div class="qltx-rental-alert qltx-rental-alert-error">Phiên đặt xe không hợp lệ, vui lòng thử lại.</div>';
        } elseif (!$customer) {
            $message = '<div class="qltx-rental-alert qltx-rental-alert-error">Không tìm thấy thông tin khách hàng.</div>';
        } else {
            $id_xe = isset($_POST['id_xe']) ? intval($_POST['id_xe']) : 0;
            $ngay_thue = isset($_POST['ngay_thue']) ? sanitize_text_field($_POST['ngay_thue']) : '';
            $so_ngay = isset($_POST['so_ngay']) ? intval($_POST['so_ngay']) : 0;
            $xe = $wpdb->get_row($wpdb->prepare("SELECT * FROM tbl_xe WHERE id_xe = %d AND trang_thai = 'Trong'", $id_xe));

            if (!$xe) {
                $message = '<div class="qltx-rental-alert qltx-rental-alert-error">Xe này hiện không còn trống.</div>';
            } elseif ($ngay_thue < $today) {
                $message = '<div class="qltx-rental-alert qltx-rental-alert-error">Ngày thuê phải từ hôm nay trở đi.</div>';
            } elseif ($so_ngay < 1 || $so_ngay > 7) {
                $message = '<div class="qltx-rental-alert qltx-rental-alert-error">Thời gian thuê tối đa là 7 ngày.</div>';
            } else {
                $ngay_tra = date('Y-m-d', strtotime($ngay_thue . ' +' . $so_ngay . ' days'));
                $tong_tien = floatval($xe->gia_thue) * $so_ngay;

                $inserted = $wpdb->insert(
                    'tbl_hopdong',
                    array(
                        'id_xe' => $id_xe,
                        'id_kh' => intval($customer->id_kh),
                        'ngay_thue' => $ngay_thue,
                        'ngay_tra_du_kien' => $ngay_tra,
                        'tong_tien' => $tong_tien,
                        'trang_thai_thanh_toan' => 'Chua thanh toan',
                        'tinh_trang_hd' => 'Cho xac nhan'
                    )
                );

                if ($inserted) {
                    $wpdb->update('tbl_xe', array('trang_thai' => 'Cho xac nhan'), array('id_xe' => $id_xe));
                    $message = '<div class="qltx-rental-alert qltx-rental-alert-success">Đặt xe thành công. Xe đang chờ quản trị viên xác nhận.</div>';
                } else {
                    $message = '<div class="qltx-rental-alert qltx-rental-alert-error">Không thể tạo hợp đồng. Lỗi database: ' . esc_html($wpdb->last_error) . '</div>';
                }
            }
        }
    }

    $where = "WHERE trang_thai = 'Trong'";
    if ($search_name !== '') {
        $where .= $wpdb->prepare(" AND ten_xe LIKE %s", '%' . $wpdb->esc_like($search_name) . '%');
    }
    if ($filter_brand !== '') {
        $where .= $wpdb->prepare(" AND hang_xe = %s", $filter_brand);
    }
    if ($filter_segment !== '') {
        $where .= $wpdb->prepare(" AND loai_xe = %s", $filter_segment);
    }
    if ($filter_parking !== '') {
        $where .= $wpdb->prepare(" AND vi_tri_do = %s", $filter_parking);
    }

    $list_xe = $wpdb->get_results("SELECT * FROM tbl_xe $where ORDER BY id_xe DESC");

    ob_start();
    ?>
    <style>
        body.autodrive-rental-page {
            margin: 0 !important;
            background: #f8fbff !important;
        }
        body.autodrive-rental-page .wp-site-blocks > header,
        body.autodrive-rental-page .wp-site-blocks > footer,
        body.autodrive-rental-page header.wp-block-template-part,
        body.autodrive-rental-page footer.wp-block-template-part,
        body.autodrive-rental-page .site-header,
        body.autodrive-rental-page .site-footer,
        body.autodrive-rental-page .wp-block-post-title,
        body.autodrive-rental-page .entry-title {
            display: none !important;
        }
        body.autodrive-rental-page .wp-site-blocks,
        body.autodrive-rental-page main,
        body.autodrive-rental-page .entry-content,
        body.autodrive-rental-page .wp-block-group,
        body.autodrive-rental-page .wp-block-post-content {
            max-width: none !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        body.autodrive-rental-page .entry-content > *,
        body.autodrive-rental-page .wp-block-post-content > * {
            max-width: none !important;
            margin-block-start: 0 !important;
            margin-block-end: 0 !important;
        }
        .qltx-rental-page {
            width: 100vw;
            max-width: none;
            min-height: 100vh;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
            padding: 0 0 56px;
            box-sizing: border-box;
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 42%);
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .qltx-rental-nav {
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 max(22px, calc((100vw - 1180px) / 2));
            border-bottom: 1px solid #e8edf7;
            background: rgba(255,255,255,.96);
        }
        .qltx-rental-logo {
            color: #063b8e;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0;
        }
        .qltx-rental-menu {
            display: flex;
            align-items: center;
            gap: 36px;
            font-size: 13px;
            font-weight: 700;
            color: #5b6680;
        }
        .qltx-rental-menu a {
            color: #5b6680;
            text-decoration: none;
            position: relative;
        }
        .qltx-rental-menu a.is-active {
            color: #063b8e;
        }
        .qltx-rental-menu a.is-active::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -10px;
            height: 3px;
            border-radius: 999px;
            background: #063b8e;
        }
        .qltx-rental-user {
            color: #063b8e;
            font-size: 13px;
            font-weight: 700;
        }
        .qltx-rental-content {
            padding: 42px max(22px, calc((100vw - 1180px) / 2)) 0;
        }
        .qltx-rental-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 18px;
            max-width: 1180px;
            margin: 42px auto 22px;
            padding: 0 22px;
            box-sizing: border-box;
        }
        .qltx-rental-head h1 {
            margin: 0 0 8px;
            color: #063b8e;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 0;
        }
        .qltx-rental-head p {
            margin: 0;
            color: #64748b;
            font-size: 15px;
        }
        .qltx-rental-count {
            padding: 10px 14px;
            border-radius: 999px;
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 800;
            white-space: nowrap;
        }
        .qltx-rental-filters {
            display: grid;
            grid-template-columns: minmax(220px, 1.3fr) minmax(160px, 1fr) minmax(160px, 1fr) minmax(170px, 1fr) 130px 110px;
            gap: 12px;
            align-items: end;
            max-width: 1136px;
            margin: 0 auto 24px;
            padding: 16px;
            border: 1px solid #dbeafe;
            border-radius: 12px;
            background: rgba(255,255,255,.94);
            box-shadow: 0 12px 30px rgba(15, 23, 42, .07);
        }
        .qltx-filter-field {
            min-width: 0;
        }
        .qltx-filter-field label {
            display: block;
            margin-bottom: 6px;
            color: #475569;
            font-size: 12px;
            font-weight: 900;
        }
        .qltx-filter-field input,
        .qltx-filter-field select {
            width: 100%;
            min-height: 42px;
            box-sizing: border-box;
            padding: 9px 11px;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #ffffff;
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
        }
        .qltx-filter-submit,
        .qltx-filter-reset {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }
        .qltx-filter-submit {
            border: 0;
            background: #063b8e;
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(6, 59, 142, .2);
        }
        .qltx-filter-reset {
            border: 1px solid #bfdbfe;
            background: #ffffff;
            color: #075985;
        }
        .qltx-rental-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 22px;
            box-sizing: border-box;
        }
        .qltx-rental-card {
            overflow: hidden;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 14px 36px rgba(15, 23, 42, .08);
        }
        .qltx-rental-card img {
            width: 100%;
            aspect-ratio: 16 / 10;
            object-fit: cover;
            background: #e2e8f0;
        }
        .qltx-rental-body {
            padding: 18px;
        }
        .qltx-rental-body h3 {
            margin: 0 0 10px;
            color: #0f172a;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0;
        }
        .qltx-rental-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }
        .qltx-rental-meta span {
            padding: 5px 9px;
            border-radius: 999px;
            background: #eff6ff;
            color: #075985;
            font-size: 12px;
            font-weight: 800;
        }
        .qltx-rental-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .qltx-rental-price {
            color: #063b8e;
            font-size: 18px;
            font-weight: 900;
        }
        .qltx-rental-price small {
            display: block;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }
        .qltx-rental-book {
            border: 0;
            border-radius: 10px;
            padding: 11px 16px;
            background: #063b8e;
            color: #ffffff;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(6, 59, 142, .22);
        }
        .qltx-rental-book:hover {
            background: #0750bd;
        }
        .qltx-rental-alert,
        .qltx-rental-notice {
            max-width: 1180px;
            margin: 18px auto;
            padding: 14px 16px;
            border-radius: 12px;
            font-weight: 800;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .qltx-rental-alert-success {
            border: 1px solid #bbf7d0;
            background: #dcfce7;
            color: #166534;
        }
        .qltx-rental-alert-error,
        .qltx-rental-notice {
            border: 1px solid #fecaca;
            background: #fee2e2;
            color: #991b1b;
        }
        .qltx-rental-empty {
            max-width: 1136px;
            margin: 0 auto;
            padding: 30px;
            border: 1px dashed #bfdbfe;
            border-radius: 14px;
            background: #f8fbff;
            color: #475569;
            font-weight: 800;
        }
        .qltx-booking-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15, 23, 42, .62);
            backdrop-filter: blur(4px);
        }
        .qltx-booking-dialog {
            width: 100%;
            max-width: 520px;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 26px 70px rgba(15, 23, 42, .3);
            overflow: hidden;
        }
        .qltx-booking-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 22px;
            background: #eff6ff;
            border-bottom: 1px solid #dbeafe;
        }
        .qltx-booking-head h2 {
            margin: 0;
            color: #063b8e;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0;
        }
        .qltx-booking-close {
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 26px;
            line-height: 1;
            cursor: pointer;
        }
        .qltx-booking-form {
            padding: 22px;
        }
        .qltx-booking-summary {
            margin-bottom: 18px;
            padding: 14px;
            border-radius: 12px;
            background: #f8fbff;
            border: 1px solid #dbeafe;
        }
        .qltx-booking-summary strong {
            display: block;
            color: #0f172a;
            font-size: 18px;
            margin-bottom: 4px;
        }
        .qltx-booking-summary span {
            color: #64748b;
            font-weight: 700;
        }
        .qltx-booking-field {
            margin-bottom: 14px;
        }
        .qltx-booking-field label {
            display: block;
            margin-bottom: 7px;
            color: #334155;
            font-size: 13px;
            font-weight: 900;
        }
        .qltx-booking-field input,
        .qltx-booking-field select {
            width: 100%;
            min-height: 44px;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            background: #ffffff;
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
        }
        .qltx-booking-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 18px 0;
            padding: 16px;
            border-radius: 12px;
            background: #063b8e;
            color: #ffffff;
            font-weight: 900;
        }
        .qltx-booking-total span:last-child {
            font-size: 20px;
        }
        .qltx-booking-confirm {
            width: 100%;
            min-height: 46px;
            border: 0;
            border-radius: 12px;
            background: #0ea5e9;
            color: #ffffff;
            font-size: 15px;
            font-weight: 900;
            cursor: pointer;
        }
        @media (max-width: 980px) {
            .qltx-rental-menu { display: none; }
            .qltx-rental-filters { grid-template-columns: 1fr 1fr; }
            .qltx-filter-submit,
            .qltx-filter-reset { width: 100%; }
            .qltx-rental-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .qltx-rental-nav { padding: 0 14px; }
            .qltx-rental-user { max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: right; }
            .qltx-rental-content { padding: 28px 14px 0; }
            .qltx-rental-head { display: block; }
            .qltx-rental-count { display: inline-block; margin-top: 12px; }
            .qltx-rental-filters { grid-template-columns: 1fr; }
            .qltx-rental-grid { grid-template-columns: 1fr; }
            .qltx-rental-footer { align-items: flex-start; flex-direction: column; }
            .qltx-rental-book { width: 100%; }
        }
    </style>

    <?php echo $message; ?>

    <section class="qltx-rental-page">
        <nav class="qltx-rental-nav">
            <div class="qltx-rental-logo">AutoDrive</div>
            <div class="qltx-rental-menu">
                <a href="<?php echo esc_url(remove_query_arg(array('autodrive_page', 'autodrive_search', 'ten_xe', 'ngay_nhan', 'ngay_tra', 'phan_khuc', 'hang_xe', 'vi_tri_do'))); ?>">Trang chủ</a>
                <a class="is-active" href="<?php echo esc_url(add_query_arg('autodrive_page', 'cars', remove_query_arg(array('autodrive_search', 'ten_xe', 'ngay_nhan', 'ngay_tra', 'phan_khuc', 'hang_xe', 'vi_tri_do')))); ?>">Thuê xe</a>
                <a href="<?php echo esc_url(add_query_arg('autodrive_page', 'history', remove_query_arg(array('autodrive_search', 'ten_xe', 'ngay_nhan', 'ngay_tra', 'phan_khuc', 'hang_xe', 'vi_tri_do')))); ?>">Lịch sử</a>
            </div>
            <div class="qltx-rental-user">Xin Chào, <?php echo esc_html($current_user->display_name ? $current_user->display_name : 'User'); ?></div>
        </nav>

        <div class="qltx-rental-head">
            <div>
                <h1>Danh sách xe cho thuê</h1>
                <p>Chọn xe đang trống và đặt lịch thuê trực tiếp trên AutoDrive.</p>
            </div>
            <div class="qltx-rental-count"><?php echo count($list_xe); ?> xe đang trống</div>
        </div>

        <form class="qltx-rental-filters" method="get" action="">
            <input type="hidden" name="autodrive_page" value="cars">

            <div class="qltx-filter-field">
                <label for="qltx_ten_xe">Tìm theo tên xe</label>
                <input type="search" id="qltx_ten_xe" name="ten_xe" placeholder="Nhập tên xe..." value="<?php echo esc_attr($search_name); ?>">
            </div>

            <div class="qltx-filter-field">
                <label for="qltx_hang_xe">Hãng xe</label>
                <select id="qltx_hang_xe" name="hang_xe">
                    <option value="">Tất cả hãng xe</option>
                    <?php foreach ($brand_options as $brand_option) : ?>
                        <option value="<?php echo esc_attr($brand_option); ?>" <?php selected($filter_brand, $brand_option); ?>><?php echo esc_html($brand_option); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="qltx-filter-field">
                <label for="qltx_phan_khuc">Phân khúc</label>
                <select id="qltx_phan_khuc" name="phan_khuc">
                    <option value="">Tất cả phân khúc</option>
                    <?php foreach ($segment_options as $segment_option) : ?>
                        <option value="<?php echo esc_attr($segment_option); ?>" <?php selected($filter_segment, $segment_option); ?>><?php echo esc_html($segment_option); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="qltx-filter-field">
                <label for="qltx_vi_tri_do">Vị trí đỗ</label>
                <select id="qltx_vi_tri_do" name="vi_tri_do">
                    <option value="">Tất cả vị trí đỗ</option>
                    <?php foreach ($parking_options as $parking_option) : ?>
                        <option value="<?php echo esc_attr($parking_option); ?>" <?php selected($filter_parking, $parking_option); ?>><?php echo esc_html($parking_option); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="qltx-filter-submit">Tìm kiếm</button>
            <a class="qltx-filter-reset" href="<?php echo esc_url(add_query_arg('autodrive_page', 'cars', remove_query_arg(array('autodrive_search', 'ten_xe', 'ngay_nhan', 'ngay_tra', 'phan_khuc', 'hang_xe', 'vi_tri_do')))); ?>">Làm mới</a>
        </form>

        <?php if ($list_xe) : ?>
            <div class="qltx-rental-grid">
                <?php foreach ($list_xe as $xe) :
                    $thumb_url = !empty($xe->hinh_anh) ? $xe->hinh_anh : 'https://placehold.co/640x400?text=AutoDrive';
                    ?>
                    <article class="qltx-rental-card">
                        <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($xe->ten_xe); ?>">
                        <div class="qltx-rental-body">
                            <h3><?php echo esc_html($xe->ten_xe); ?></h3>
                            <div class="qltx-rental-meta">
                                <span><?php echo esc_html($xe->hang_xe ? $xe->hang_xe : 'Chưa rõ hãng'); ?></span>
                                <span><?php echo esc_html($xe->loai_xe ? $xe->loai_xe : 'Chưa phân khúc'); ?></span>
                                <span><?php echo esc_html($xe->vi_tri_do ? $xe->vi_tri_do : 'Chưa có vị trí đỗ'); ?></span>
                                <span><?php echo esc_html($xe->bien_so); ?></span>
                            </div>
                            <div class="qltx-rental-footer">
                                <div class="qltx-rental-price">
                                    <?php echo number_format(floatval($xe->gia_thue), 0, ',', '.'); ?> đ
                                    <small>Giá thuê/ngày</small>
                                </div>
                                <button type="button"
                                        class="qltx-rental-book"
                                        data-id="<?php echo intval($xe->id_xe); ?>"
                                        data-name="<?php echo esc_attr($xe->ten_xe); ?>"
                                        data-brand="<?php echo esc_attr($xe->hang_xe); ?>"
                                        data-segment="<?php echo esc_attr($xe->loai_xe); ?>"
                                        data-price="<?php echo esc_attr(floatval($xe->gia_thue)); ?>">
                                    Đặt xe
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="qltx-rental-empty">Hiện chưa có xe trống để cho thuê.</div>
        <?php endif; ?>
    </section>

    <div class="qltx-booking-modal" id="qltxBookingModal">
        <div class="qltx-booking-dialog">
            <div class="qltx-booking-head">
                <h2>Đặt xe AutoDrive</h2>
                <button type="button" class="qltx-booking-close" onclick="qltxCloseBookingModal()">&times;</button>
            </div>
            <form method="post" class="qltx-booking-form">
                <?php wp_nonce_field('qltx_confirm_booking', 'qltx_booking_nonce'); ?>
                <input type="hidden" name="qltx_confirm_booking" value="1">
                <input type="hidden" name="id_xe" id="bookingCarId">

                <div class="qltx-booking-summary">
                    <strong id="bookingCarName">Tên xe</strong>
                    <span id="bookingCarMeta">Hãng xe - Phân khúc</span>
                </div>

                <div class="qltx-booking-field">
                    <label for="bookingStartDate">Chọn ngày thuê</label>
                    <input type="date" id="bookingStartDate" name="ngay_thue" min="<?php echo esc_attr($today); ?>" required>
                </div>

                <div class="qltx-booking-field">
                    <label for="bookingDays">Thời gian thuê</label>
                    <select id="bookingDays" name="so_ngay" required>
                        <?php for ($day = 1; $day <= 7; $day++) : ?>
                            <option value="<?php echo esc_attr($day); ?>"><?php echo esc_html($day); ?> ngày</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="qltx-booking-field">
                    <label for="bookingEndDate">Ngày trả dự kiến</label>
                    <input type="date" id="bookingEndDate" readonly>
                </div>

                <div class="qltx-booking-total">
                    <span>Tổng tiền</span>
                    <span id="bookingTotal">0 đ</span>
                </div>

                <button type="submit" class="qltx-booking-confirm">Xác nhận thuê</button>
            </form>
        </div>
    </div>

    <script>
    (function() {
        document.body.classList.add('autodrive-rental-page');

        const modal = document.getElementById('qltxBookingModal');
        const carId = document.getElementById('bookingCarId');
        const carName = document.getElementById('bookingCarName');
        const carMeta = document.getElementById('bookingCarMeta');
        const startDate = document.getElementById('bookingStartDate');
        const days = document.getElementById('bookingDays');
        const endDate = document.getElementById('bookingEndDate');
        const total = document.getElementById('bookingTotal');
        let currentPrice = 0;

        function formatMoney(value) {
            return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
        }

        function addDays(dateValue, numberOfDays) {
            const date = new Date(dateValue + 'T00:00:00');
            date.setDate(date.getDate() + numberOfDays);
            return date.toISOString().slice(0, 10);
        }

        function updateBookingPreview() {
            if (!startDate.value) {
                endDate.value = '';
                total.textContent = formatMoney(0);
                return;
            }

            const rentDays = parseInt(days.value, 10) || 1;
            endDate.value = addDays(startDate.value, rentDays);
            total.textContent = formatMoney(currentPrice * rentDays);
        }

        window.qltxCloseBookingModal = function() {
            modal.style.display = 'none';
        };

        document.querySelectorAll('.qltx-rental-book').forEach(function(button) {
            button.addEventListener('click', function() {
                currentPrice = parseFloat(button.dataset.price || '0');
                carId.value = button.dataset.id;
                carName.textContent = button.dataset.name;
                carMeta.textContent = (button.dataset.brand || 'AutoDrive') + ' - ' + (button.dataset.segment || 'Chưa phân khúc');
                startDate.value = startDate.min;
                days.value = '1';
                updateBookingPreview();
                modal.style.display = 'flex';
            });
        });

        if (startDate) startDate.addEventListener('change', updateBookingPreview);
        if (days) days.addEventListener('change', updateBookingPreview);
        if (modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    qltxCloseBookingModal();
                }
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('qltx_danh_sach_xe', 'qltx_giao_dien_danh_sach_xe_frontend');
