<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_home($atts = array()) {
    global $wpdb;

    if (!is_user_logged_in()) {
        return function_exists('qltx_giao_dien_auth') ? qltx_giao_dien_auth() : '<p>Vui lòng đăng nhập để xem trang chủ AutoDrive.</p>';
    }

    wp_enqueue_style('dashicons');

    $today = current_time('Y-m-d');
    $phan_khuc_options = array('Hạng A', 'Hạng B', 'Hạng C', 'Hạng D');
    $hang_xe_options = $wpdb->get_col("SELECT DISTINCT hang_xe FROM tbl_xe WHERE hang_xe IS NOT NULL AND hang_xe != '' ORDER BY hang_xe ASC");
    $vi_tri_do_options = $wpdb->get_col("SELECT DISTINCT vi_tri_do FROM tbl_xe WHERE vi_tri_do IS NOT NULL AND vi_tri_do != '' ORDER BY vi_tri_do ASC");

    $ngay_nhan = isset($_GET['ngay_nhan']) ? sanitize_text_field($_GET['ngay_nhan']) : '';
    $ngay_tra = isset($_GET['ngay_tra']) ? sanitize_text_field($_GET['ngay_tra']) : '';
    $phan_khuc = isset($_GET['phan_khuc']) ? sanitize_text_field($_GET['phan_khuc']) : '';
    $hang_xe = isset($_GET['hang_xe']) ? sanitize_text_field($_GET['hang_xe']) : '';
    $vi_tri_do = isset($_GET['vi_tri_do']) ? sanitize_text_field($_GET['vi_tri_do']) : '';
    $has_search = isset($_GET['autodrive_search']);

    $where = " WHERE trang_thai = 'Trong'";
    if ($phan_khuc !== '') {
        $where .= $wpdb->prepare(" AND loai_xe = %s", $phan_khuc);
    }
    if ($hang_xe !== '') {
        $where .= $wpdb->prepare(" AND hang_xe = %s", $hang_xe);
    }
    if ($vi_tri_do !== '') {
        $where .= $wpdb->prepare(" AND vi_tri_do = %s", $vi_tri_do);
    }

    $list_xe = $has_search ? $wpdb->get_results("SELECT * FROM tbl_xe $where ORDER BY gia_thue ASC LIMIT 8") : array();
    $current_user = wp_get_current_user();
    $autodrive_page = isset($_GET['autodrive_page']) ? sanitize_key($_GET['autodrive_page']) : 'home';

    if ($autodrive_page === 'cars' && function_exists('qltx_giao_dien_danh_sach_xe_frontend')) {
        return qltx_giao_dien_danh_sach_xe_frontend();
    }

    if ($autodrive_page === 'history' && function_exists('qltx_giao_dien_lich_su_thue_frontend')) {
        return qltx_giao_dien_lich_su_thue_frontend();
    }

    ob_start();
    ?>
    <style>
        body.autodrive-home-page {
            margin: 0 !important;
            background: #ffffff !important;
        }
        body.autodrive-home-page .wp-site-blocks > header,
        body.autodrive-home-page .wp-site-blocks > footer,
        body.autodrive-home-page header.wp-block-template-part,
        body.autodrive-home-page footer.wp-block-template-part,
        body.autodrive-home-page .site-header,
        body.autodrive-home-page .site-footer,
        body.autodrive-home-page .wp-block-post-title,
        body.autodrive-home-page .entry-title {
            display: none !important;
        }
        body.autodrive-home-page .wp-site-blocks,
        body.autodrive-home-page main,
        body.autodrive-home-page .entry-content,
        body.autodrive-home-page .wp-block-group,
        body.autodrive-home-page .wp-block-post-content {
            max-width: none !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        body.autodrive-home-page .entry-content > *,
        body.autodrive-home-page .wp-block-post-content > * {
            max-width: none !important;
            margin-block-start: 0 !important;
            margin-block-end: 0 !important;
        }
        .ad-home {
            width: 100vw;
            max-width: none;
            min-height: 100vh;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
            color: #09245f;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #ffffff;
        }
        .ad-nav {
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 22px;
            border-bottom: 1px solid #e8edf7;
            background: rgba(255,255,255,.96);
        }
        .ad-logo {
            color: #063b8e;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0;
        }
        .ad-menu {
            display: flex;
            align-items: center;
            gap: 36px;
            font-size: 13px;
            font-weight: 700;
            color: #5b6680;
        }
        .ad-menu a {
            color: #5b6680;
            text-decoration: none;
            position: relative;
        }
        .ad-menu a.is-active {
            color: #063b8e;
        }
        .ad-menu a.is-active::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -10px;
            height: 3px;
            border-radius: 999px;
            background: #063b8e;
        }
        .ad-user {
            color: #063b8e;
            font-size: 13px;
            font-weight: 700;
        }
        .ad-hero {
            position: relative;
            min-height: calc(100vh - 64px);
            padding: 160px 22px 54px;
            overflow: hidden;
            background:
                linear-gradient(90deg, rgba(236,244,255,.94) 0%, rgba(236,244,255,.78) 38%, rgba(236,244,255,.28) 68%),
                url("https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1800&q=80") center/cover no-repeat;
        }
        .ad-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,.18);
            pointer-events: none;
        }
        .ad-hero-content {
            position: relative;
            z-index: 1;
            max-width: 620px;
        }
        .ad-hero h1 {
            margin: 0 0 22px;
            color: #05317d;
            font-size: 44px;
            line-height: 1.14;
            font-weight: 900;
            letter-spacing: 0;
        }
        .ad-hero p {
            max-width: 560px;
            margin: 0;
            color: #334155;
            font-size: 16px;
            line-height: 1.7;
        }
        .ad-search {
            position: relative;
            z-index: 2;
            max-width: 900px;
            margin-top: 82px;
            padding: 18px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr 150px;
            gap: 0;
            border-radius: 10px;
            background: rgba(255,255,255,.96);
            box-shadow: 0 18px 45px rgba(15, 23, 42, .2);
        }
        .ad-field {
            position: relative;
            min-width: 0;
            padding: 0 16px;
            border-right: 1px solid #e5eaf4;
        }
        .ad-field label {
            display: block;
            margin-bottom: 5px;
            color: #6b7280;
            font-size: 11px;
            line-height: 1.2;
            font-weight: 900;
            text-transform: uppercase;
        }
        .ad-field .dashicons {
            position: absolute;
            left: 14px;
            bottom: 8px;
            width: 18px;
            height: 18px;
            color: #94a3b8;
            cursor: pointer;
        }
        .ad-field input,
        .ad-field select {
            width: 100%;
            height: 34px;
            padding: 0 8px 0 28px;
            border: 0;
            background: transparent;
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
            outline: none;
        }
        .ad-field select {
            appearance: auto;
        }
        .ad-submit {
            border: 0;
            border-radius: 4px;
            background: #063b8e;
            color: #ffffff;
            font-size: 16px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(6, 59, 142, .24);
        }
        .ad-submit:hover {
            background: #0750bd;
        }
        .ad-results {
            padding: 34px 22px 12px;
            background: #ffffff;
        }
        .ad-results h2 {
            margin: 0 0 18px;
            color: #09245f;
            font-size: 24px;
            letter-spacing: 0;
        }
        .ad-car-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }
        .ad-car-card {
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
        }
        .ad-car-card img {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            background: #e2e8f0;
        }
        .ad-car-body {
            padding: 14px;
        }
        .ad-car-body h3 {
            margin: 0 0 8px;
            color: #0f172a;
            font-size: 16px;
            letter-spacing: 0;
        }
        .ad-car-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 10px;
        }
        .ad-car-meta span {
            padding: 4px 8px;
            border-radius: 999px;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 12px;
            font-weight: 800;
        }
        .ad-car-price {
            color: #063b8e;
            font-size: 15px;
            font-weight: 900;
        }
        .ad-empty {
            padding: 18px;
            border: 1px dashed #bfdbfe;
            border-radius: 10px;
            color: #475569;
            background: #f8fbff;
            font-weight: 700;
        }
        @media (max-width: 980px) {
            .ad-menu { display: none; }
            .ad-hero { min-height: calc(100vh - 64px); padding-top: 96px; }
            .ad-search { grid-template-columns: 1fr 1fr; gap: 14px; }
            .ad-field { border: 1px solid #e5eaf4; border-radius: 8px; padding: 10px 14px; }
            .ad-submit { min-height: 50px; grid-column: 1 / -1; }
            .ad-car-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .ad-nav { padding: 0 14px; }
            .ad-user { max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: right; }
            .ad-hero { padding: 68px 14px 28px; }
            .ad-hero h1 { font-size: 32px; }
            .ad-search { grid-template-columns: 1fr; margin-top: 36px; }
            .ad-car-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="ad-home">
        <nav class="ad-nav">
            <div class="ad-logo">AutoDrive</div>
            <div class="ad-menu">
                <a class="is-active" href="<?php echo esc_url(remove_query_arg(array('autodrive_page', 'autodrive_search', 'ngay_nhan', 'ngay_tra', 'phan_khuc', 'hang_xe', 'vi_tri_do'))); ?>">Trang chủ</a>
                <a href="<?php echo esc_url(add_query_arg('autodrive_page', 'cars', remove_query_arg(array('autodrive_search', 'ngay_nhan', 'ngay_tra', 'phan_khuc', 'hang_xe', 'vi_tri_do')))); ?>">Thuê xe</a>
                <a href="<?php echo esc_url(add_query_arg('autodrive_page', 'history', remove_query_arg(array('autodrive_search', 'ngay_nhan', 'ngay_tra', 'phan_khuc', 'hang_xe', 'vi_tri_do')))); ?>">Lịch sử</a>
            </div>
            <div class="ad-user">Xin Chào, <?php echo esc_html($current_user->display_name ? $current_user->display_name : 'User'); ?></div>
        </nav>

        <section class="ad-hero">
            <div class="ad-hero-content">
                <h1>Trải nghiệm tương lai của sự di chuyển.</h1>
                <p>Thuê xe tự lái cao cấp, an toàn và tiện lợi. Tận hưởng chuyến đi hoàn hảo mà không cần cầm lái.</p>
            </div>

            <form class="ad-search" method="get" action="">
                <input type="hidden" name="autodrive_search" value="1">

                <div class="ad-field">
                    <label for="ad_ngay_nhan">Ngày nhận</label>
                    <span class="dashicons dashicons-calendar-alt" onclick="qltxOpenDatePicker('ad_ngay_nhan')"></span>
                    <input type="date" id="ad_ngay_nhan" name="ngay_nhan" min="<?php echo esc_attr($today); ?>" value="<?php echo esc_attr($ngay_nhan); ?>">
                </div>

                <div class="ad-field">
                    <label for="ad_ngay_tra">Ngày trả</label>
                    <span class="dashicons dashicons-calendar-alt" onclick="qltxOpenDatePicker('ad_ngay_tra')"></span>
                    <input type="date" id="ad_ngay_tra" name="ngay_tra" min="<?php echo esc_attr($today); ?>" value="<?php echo esc_attr($ngay_tra); ?>">
                </div>

                <div class="ad-field">
                    <label for="ad_phan_khuc">Phân khúc</label>
                    <span class="dashicons dashicons-car"></span>
                    <select id="ad_phan_khuc" name="phan_khuc">
                        <option value="">Tất cả phân khúc</option>
                        <?php foreach ($phan_khuc_options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($phan_khuc, $option); ?>><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ad-field">
                    <label for="ad_hang_xe">Hãng xe</label>
                    <span class="dashicons dashicons-tag"></span>
                    <select id="ad_hang_xe" name="hang_xe">
                        <option value="">Tất cả hãng xe</option>
                        <?php foreach ($hang_xe_options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($hang_xe, $option); ?>><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ad-field">
                    <label for="ad_vi_tri_do">Vị trí đỗ</label>
                    <span class="dashicons dashicons-location"></span>
                    <select id="ad_vi_tri_do" name="vi_tri_do">
                        <option value="">Tất cả vị trí</option>
                        <?php foreach ($vi_tri_do_options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($vi_tri_do, $option); ?>><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="ad-submit">Tìm Xe</button>
            </form>
        </section>

        <?php if ($has_search) : ?>
            <section class="ad-results">
                <h2>Xe phù hợp</h2>
                <?php if ($list_xe) : ?>
                    <div class="ad-car-grid">
                        <?php foreach ($list_xe as $xe) :
                            $thumb_url = !empty($xe->hinh_anh) ? $xe->hinh_anh : 'https://placehold.co/600x450?text=AutoDrive';
                            ?>
                            <article class="ad-car-card">
                                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($xe->ten_xe); ?>">
                                <div class="ad-car-body">
                                    <h3><?php echo esc_html($xe->ten_xe); ?></h3>
                                    <div class="ad-car-meta">
                                        <span><?php echo esc_html($xe->hang_xe ? $xe->hang_xe : 'AutoDrive'); ?></span>
                                        <span><?php echo esc_html($xe->loai_xe ? $xe->loai_xe : 'Chưa phân khúc'); ?></span>
                                        <span><?php echo esc_html($xe->vi_tri_do ? $xe->vi_tri_do : 'Chưa có vị trí đỗ'); ?></span>
                                    </div>
                                    <div class="ad-car-price"><?php echo number_format(floatval($xe->gia_thue), 0, ',', '.'); ?> đ/ngày</div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="ad-empty">Không tìm thấy xe phù hợp với bộ lọc hiện tại.</div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>

    <script>
    (function() {
        document.body.classList.add('autodrive-home-page');

        const pickup = document.getElementById('ad_ngay_nhan');
        const dropoff = document.getElementById('ad_ngay_tra');
        const form = document.querySelector('.ad-search');

        function addDays(dateValue, days) {
            const date = new Date(dateValue + 'T00:00:00');
            date.setDate(date.getDate() + days);
            return date.toISOString().slice(0, 10);
        }

        function syncDropoffMin() {
            if (!pickup || !dropoff) return;
            if (pickup.value) {
                dropoff.min = addDays(pickup.value, 1);
                if (dropoff.value && dropoff.value <= pickup.value) {
                    dropoff.value = dropoff.min;
                }
            }
        }

        window.qltxOpenDatePicker = function(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            } else {
                input.focus();
            }
        };

        if (pickup) pickup.addEventListener('change', syncDropoffMin);
        if (form) {
            form.addEventListener('submit', function(event) {
                syncDropoffMin();
                if (pickup.value && dropoff.value && dropoff.value <= pickup.value) {
                    event.preventDefault();
                    alert('Ngày trả phải lớn hơn ngày nhận.');
                    qltxOpenDatePicker('ad_ngay_tra');
                }
            });
        }
        syncDropoffMin();
    })();
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('qltx_home', 'qltx_giao_dien_home');
