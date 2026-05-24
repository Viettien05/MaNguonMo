<?php
if (!defined('ABSPATH')) exit;

function qltx_giao_dien_auth() {
    global $wpdb;
    $message = '';

    if (isset($_POST['qltx_action'])) {
        if ($_POST['qltx_action'] === 'login') {
            $username = sanitize_user($_POST['log_username']);
            $password = $_POST['log_password'];

            $user = wp_signon(array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => true
            ), false);

            if (is_wp_error($user)) {
                $message = '<div class="qltx-auth-alert qltx-auth-alert-error">Sai tài khoản hoặc mật khẩu, vui lòng kiểm tra lại.</div>';
            } else {
                $message = '<div class="qltx-auth-alert qltx-auth-alert-success">Đăng nhập thành công! Đang tải lại trang...</div>';
                echo "<script>setTimeout(function(){ window.location.href = '" . esc_url(get_permalink()) . "'; }, 1000);</script>";
            }
        }

        if ($_POST['qltx_action'] === 'register') {
            $username = sanitize_user($_POST['reg_username']);
            $email = sanitize_email($_POST['reg_email']);
            $password = $_POST['reg_password'];
            $password_confirm = $_POST['reg_password_confirm'];
            $fullname = sanitize_text_field($_POST['reg_fullname']);
            $sdt = sanitize_text_field($_POST['reg_sdt']);

            if (username_exists($username)) {
                $message = '<div class="qltx-auth-alert qltx-auth-alert-error">Tên đăng nhập này đã tồn tại.</div>';
            } elseif (email_exists($email)) {
                $message = '<div class="qltx-auth-alert qltx-auth-alert-error">Địa chỉ email này đã được đăng ký.</div>';
            } elseif (strlen($password) < 6) {
                $message = '<div class="qltx-auth-alert qltx-auth-alert-error">Mật khẩu phải từ 6 ký tự trở lên.</div>';
            } elseif ($password !== $password_confirm) {
                $message = '<div class="qltx-auth-alert qltx-auth-alert-error">Mật khẩu nhập lại không khớp.</div>';
            } else {
                $user_id = wp_create_user($username, $password, $email);

                if (!is_wp_error($user_id)) {
                    wp_update_user(array(
                        'ID'           => $user_id,
                        'display_name' => $fullname,
                        'first_name'   => $fullname,
                        'nickname'     => $fullname
                    ));

                    $inserted = $wpdb->insert(
                        'tbl_khachhang',
                        array(
                            'ho_ten'        => $fullname,
                            'email'         => $email,
                            'so_dien_thoai' => $sdt,
                            'user_id'       => $user_id
                        )
                    );

                    if ($inserted === false) {
                        $message = '<div class="qltx-auth-alert qltx-auth-alert-error">Tài khoản đã tạo nhưng chưa lưu được thông tin khách hàng. Lỗi database: ' . esc_html($wpdb->last_error) . '</div>';
                    } else {
                        $message = '<div class="qltx-auth-alert qltx-auth-alert-success">Đăng ký thành công! Đang tự động đăng nhập...</div>';
                        wp_signon(array('user_login' => $username, 'user_password' => $password, 'remember' => true), false);
                        echo "<script>setTimeout(function(){ window.location.href = '" . esc_url(get_permalink()) . "'; }, 1000);</script>";
                    }
                } else {
                    $message = '<div class="qltx-auth-alert qltx-auth-alert-error">Lỗi hệ thống: ' . esc_html($user_id->get_error_message()) . '</div>';
                }
            }
        }
    }

    if (is_user_logged_in() && function_exists('qltx_giao_dien_home')) {
        return qltx_giao_dien_home();
    }

    wp_enqueue_style('dashicons');
    ob_start();
    ?>
    <style>
        .qltx-auth-shell {
            max-width: 520px;
            margin: 36px auto;
            padding: 18px;
            border-radius: 24px;
            background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 52%, #ffffff 100%);
            box-shadow: 0 24px 60px rgba(37, 99, 235, 0.18);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .qltx-auth-card {
            padding: 28px;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 12px 32px rgba(30, 64, 175, 0.12);
            color: #0f172a;
        }
        .qltx-auth-heading {
            text-align: center;
            margin-bottom: 20px;
        }
        .qltx-auth-heading h2 {
            margin: 0 0 6px;
            color: #075985;
            font-size: 24px;
            line-height: 1.25;
            font-weight: 800;
            letter-spacing: 0;
        }
        .qltx-auth-heading p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }
        .qltx-auth-alert {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
        }
        .qltx-auth-alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .qltx-auth-alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .qltx-auth-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 6px;
            margin-bottom: 24px;
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            border-radius: 16px;
        }
        .qltx-auth-tab {
            border: 0;
            border-radius: 12px;
            padding: 12px 10px;
            background: transparent;
            color: #0369a1;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: all .2s ease;
        }
        .qltx-auth-tab.is-active {
            background: #ffffff;
            color: #0284c7;
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.2);
        }
        .qltx-auth-form {
            margin: 0;
        }
        .qltx-auth-field {
            margin-bottom: 14px;
        }
        .qltx-auth-field label {
            display: block;
            margin-bottom: 7px;
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
        }
        .qltx-auth-field input {
            width: 100%;
            min-height: 44px;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #bae6fd;
            border-radius: 12px;
            background: #f8fbff;
            color: #0f172a;
            font-size: 14px;
            box-shadow: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .qltx-auth-field input:focus {
            border-color: #38bdf8;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.18);
            outline: none;
        }
        .qltx-password-field {
            position: relative;
        }
        .qltx-password-field input {
            padding-left: 42px;
        }
        .qltx-toggle-password {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #0284c7;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
            z-index: 2;
        }
        .qltx-toggle-password .dashicons {
            font-size: 18px;
            width: 18px;
            height: 18px;
        }
        .qltx-auth-submit {
            width: 100%;
            min-height: 46px;
            margin-top: 6px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            color: #ffffff;
            font-size: 15px;
            font-weight: 800;
            text-align: center;
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
            text-shadow: none;
            cursor: pointer;
        }
        .qltx-auth-submit:hover {
            filter: brightness(1.03);
        }
        .qltx-auth-logged-in {
            text-align: center;
            padding: 12px 0 4px;
        }
        .qltx-auth-avatar {
            width: 64px;
            height: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            border-radius: 50%;
            background: #e0f2fe;
            color: #0284c7;
        }
        .qltx-auth-avatar .dashicons {
            font-size: 34px;
            width: 34px;
            height: 34px;
        }
        .qltx-auth-logout {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 18px;
            border-radius: 12px;
            background: #0284c7;
            color: #ffffff;
            text-decoration: none;
            font-weight: 800;
        }
        @media (max-width: 560px) {
            .qltx-auth-shell {
                margin: 20px 12px;
                padding: 10px;
                border-radius: 18px;
            }
            .qltx-auth-card {
                padding: 20px;
                border-radius: 16px;
            }
        }
    </style>

    <div class="qltx-auth-shell">
        <div class="qltx-auth-card">
            <div class="qltx-auth-heading">
                <h2>Quản lý thuê xe tự lái</h2>
                <p>Đăng nhập hoặc tạo tài khoản khách hàng</p>
            </div>

            <?php echo $message; ?>

            <?php if (is_user_logged_in()) : ?>
                <?php echo function_exists('qltx_giao_dien_home') ? qltx_giao_dien_home() : ''; ?>
            <?php else : ?>
                <div class="qltx-auth-tabs">
                    <button type="button" id="btn-tab-login" class="qltx-auth-tab is-active" onclick="doiTab('login')">Đăng nhập</button>
                    <button type="button" id="btn-tab-register" class="qltx-auth-tab" onclick="doiTab('register')">Đăng ký</button>
                </div>

                <form id="form-qltx-login" class="qltx-auth-form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                    <input type="hidden" name="qltx_action" value="login">
                    <div class="qltx-auth-field">
                        <label for="log_username">Tên tài khoản hoặc Email</label>
                        <input type="text" id="log_username" name="log_username" required>
                    </div>
                    <div class="qltx-auth-field">
                        <label for="log_password">Mật khẩu</label>
                        <div class="qltx-password-field">
                            <button type="button" class="qltx-toggle-password" onclick="qltxTogglePassword('log_password', this)" aria-label="Hiện hoặc ẩn mật khẩu">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            <input type="password" id="log_password" name="log_password" required>
                        </div>
                    </div>
                    <button type="submit" class="qltx-auth-submit">Đăng nhập hệ thống</button>
                </form>

                <form id="form-qltx-register" class="qltx-auth-form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" style="display: none;">
                    <input type="hidden" name="qltx_action" value="register">
                    <div class="qltx-auth-field">
                        <label for="reg_username">Tên đăng nhập</label>
                        <input type="text" id="reg_username" name="reg_username" required>
                    </div>
                    <div class="qltx-auth-field">
                        <label for="reg_email">Địa chỉ Email</label>
                        <input type="email" id="reg_email" name="reg_email" required>
                    </div>
                    <div class="qltx-auth-field">
                        <label for="reg_fullname">Họ và tên</label>
                        <input type="text" id="reg_fullname" name="reg_fullname" placeholder="Ví dụ: Nguyễn Văn A" required>
                    </div>
                    <div class="qltx-auth-field">
                        <label for="reg_sdt">Số điện thoại</label>
                        <input type="text" id="reg_sdt" name="reg_sdt" placeholder="Ví dụ: 0945..." required>
                    </div>
                    <div class="qltx-auth-field">
                        <label for="reg_password">Mật khẩu</label>
                        <div class="qltx-password-field">
                            <button type="button" class="qltx-toggle-password" onclick="qltxTogglePassword('reg_password', this)" aria-label="Hiện hoặc ẩn mật khẩu">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            <input type="password" id="reg_password" name="reg_password" required>
                        </div>
                    </div>
                    <div class="qltx-auth-field">
                        <label for="reg_password_confirm">Nhập lại mật khẩu</label>
                        <div class="qltx-password-field">
                            <button type="button" class="qltx-toggle-password" onclick="qltxTogglePassword('reg_password_confirm', this)" aria-label="Hiện hoặc ẩn mật khẩu">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            <input type="password" id="reg_password_confirm" name="reg_password_confirm" required>
                        </div>
                    </div>
                    <button type="submit" class="qltx-auth-submit">Tạo tài khoản mới</button>
                </form>

                <script>
                function qltxTogglePassword(inputId, button) {
                    const input = document.getElementById(inputId);
                    const icon = button.querySelector('.dashicons');

                    if (!input || !icon) {
                        return;
                    }

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('dashicons-visibility');
                        icon.classList.add('dashicons-hidden');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('dashicons-hidden');
                        icon.classList.add('dashicons-visibility');
                    }
                }

                function doiTab(tabName) {
                    const formLogin = document.getElementById('form-qltx-login');
                    const formRegister = document.getElementById('form-qltx-register');
                    const tabLogin = document.getElementById('btn-tab-login');
                    const tabRegister = document.getElementById('btn-tab-register');

                    if (tabName === 'login') {
                        formLogin.style.display = 'block';
                        formRegister.style.display = 'none';
                        tabLogin.classList.add('is-active');
                        tabRegister.classList.remove('is-active');
                    } else {
                        formLogin.style.display = 'none';
                        formRegister.style.display = 'block';
                        tabLogin.classList.remove('is-active');
                        tabRegister.classList.add('is-active');
                    }
                }
                </script>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('qltx_auth', 'qltx_giao_dien_auth');
