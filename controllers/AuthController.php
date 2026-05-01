<?php
defined('CAMPUSLINK') or die('Direct access not permitted.');

class AuthController extends BaseController {

    // ============================================================
    // STUDENT LOGIN
    // Students log in with their Gmail (personal_email)
    // ============================================================
    public function login(): void {
        if (Auth::isLoggedIn()) {
            $this->redirect('user/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $email    = strtolower(clean($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';

            if (!$email || !$password) {
                Session::setFlash('error', 'Email and password are required.');
                $this->redirect('login');
            }

            $db   = DB::getInstance();
            $user = $db->row(
                "SELECT * FROM users
                 WHERE personal_email = ? OR school_email = ?
                 LIMIT 1",
                [$email, $email]
            );

            if (!$user || !password_verify($password, $user['password'])) {
                Session::setFlash('error', 'Invalid email or password.');
                $this->redirect('login');
            }

            if (($user['status'] ?? '') === 'blacklisted') {
                Session::setFlash('error',
                    'Your account has been suspended. Contact support.'
                );
                $this->redirect('login');
            }

            if (($user['email_verified'] ?? 0) == 0) {
                Session::set('pending_verify_email', $user['personal_email']);
                Session::setFlash('warning',
                    'Please verify your email first. ' .
                    'Check your Gmail inbox for the verification link.'
                );
                $this->redirect('verify-email');
            }

            // Login success
            Session::regenerate();

            // Clear any existing vendor/admin sessions first
            Session::delete('vendor_logged_in');
            Session::delete('vendor_id');
            Session::delete('vendor_name');
            Session::delete('vendor_email');
            Session::delete('vendor_business');
            Session::delete('vendor_type');
            Session::delete('vendor_plan');
            Session::delete('vendor_status');
            Session::delete('admin_logged_in');
            Session::delete('admin_id');
            Session::delete('admin_name');
            Session::delete('admin_email');
            Session::delete('admin_role');

            Session::set('user_logged_in', true);
            Session::set('user_id',        (int)$user['id']);
            Session::set('user_name',      $user['full_name']);
            Session::set('user_email',     $user['personal_email']);
            Session::set('user_role',      'user');

            Session::setFlash('success',
                'Welcome back, ' . $user['full_name'] . '!'
            );

            $redirectTarget = '';
            if (!empty($_GET['redirect'])) {
                $redirectTarget = trim($_GET['redirect']);
                $parts = parse_url($redirectTarget);
                if (isset($parts['scheme']) || isset($parts['host'])) {
                    $redirectTarget = '';
                } else {
                    $path     = $parts['path'] ?? '';
                    $query    = isset($parts['query']) ? '?' . $parts['query'] : '';
                    $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
                    $redirectTarget = ltrim($path, '/') . $query . $fragment;
                }
            }

            $intended = $redirectTarget ?: Session::get('intended_url');
            Session::delete('intended_url');
            $this->redirect($intended ?: 'user/dashboard');
        }

        $this->render('auth/login');
    }

    // ============================================================
    // STUDENT REGISTRATION
    // School email = identity proof (stored, not emailed)
    // Personal Gmail = login + all email communications
    // ============================================================
    public function register(): void {
        if (Auth::isLoggedIn()) {
            $this->redirect('user/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $firstName     = clean($_POST['first_name']     ?? '');
            $lastName      = clean($_POST['last_name']      ?? '');
            $fullName      = trim($firstName . ' ' . $lastName);
            $personalEmail = strtolower(clean($_POST['personal_email'] ?? ''));
            $schoolEmail   = strtolower(clean($_POST['school_email']   ?? ''));
            $phone         = clean($_POST['phone']           ?? '');
            $password      = $_POST['password']              ?? '';
            $confirm       = $_POST['password_confirmation'] ?? '';
            $matric        = clean($_POST['matric_number']   ?? '');

            // Required field check
            if (!$firstName || !$lastName || !$personalEmail ||
                !$schoolEmail || !$phone || !$password) {
                Session::setFlash('error', 'Please fill in all required fields.');
                $_SESSION['form_old'] = $_POST;
                $this->redirect('register');
            }

            // Validate Gmail
            if (!filter_var($personalEmail, FILTER_VALIDATE_EMAIL)) {
                Session::setFlash('error', 'Please enter a valid Gmail address.');
                $_SESSION['form_old'] = $_POST;
                $this->redirect('register');
            }

            // Validate school email
            if (!filter_var($schoolEmail, FILTER_VALIDATE_EMAIL)) {
                Session::setFlash('error',
                    'Please enter a valid school email address.'
                );
                $_SESSION['form_old'] = $_POST;
                $this->redirect('register');
            }

            // Enforce school email domain
            $schoolDomain   = strtolower(
                substr($schoolEmail, strpos($schoolEmail, '@') + 1)
            );
            $allowedDomain  = strtolower(SCHOOL_EMAIL_DOMAIN);
            if ($schoolDomain !== $allowedDomain) {
                Session::setFlash('error',
                    'School email must be an @' . SCHOOL_EMAIL_DOMAIN .
                    ' address. Personal Gmail goes in the Gmail field above.'
                );
                $_SESSION['form_old'] = $_POST;
                $this->redirect('register');
            }

            // Gmail and school email must be different
            if ($personalEmail === $schoolEmail) {
                Session::setFlash('error',
                    'Your Gmail and school email must be different addresses.'
                );
                $_SESSION['form_old'] = $_POST;
                $this->redirect('register');
            }

            // Password checks
            if ($password !== $confirm) {
                Session::setFlash('error', 'Passwords do not match.');
                $_SESSION['form_old'] = $_POST;
                $this->redirect('register');
            }

            if (strlen($password) < 8) {
                Session::setFlash('error',
                    'Password must be at least 8 characters long.'
                );
                $_SESSION['form_old'] = $_POST;
                $this->redirect('register');
            }

            $db = DB::getInstance();

            // Check Gmail not already taken
            $gmailExists = (int)$db->value(
                "SELECT COUNT(*) FROM users WHERE personal_email = ?",
                [$personalEmail]
            );
            if ($gmailExists > 0) {
                Session::setFlash('error',
                    'This Gmail is already registered. Please log in.'
                );
                $_SESSION['form_old'] = $_POST;
                $this->redirect('register');
            }

            // Check school email not already taken
            $schoolExists = (int)$db->value(
                "SELECT COUNT(*) FROM users WHERE school_email = ?",
                [$schoolEmail]
            );
            if ($schoolExists > 0) {
                Session::setFlash('error',
                    'This school email is already registered.'
                );
                $_SESSION['form_old'] = $_POST;
                $this->redirect('register');
            }

            // Check phone not already taken
            $phoneExists = (int)$db->value(
                "SELECT COUNT(*) FROM users WHERE phone = ?",
                [$phone]
            );
            if ($phoneExists > 0) {
                Session::setFlash('error',
                    'This phone number is already registered.'
                );
                $_SESSION['form_old'] = $_POST;
                $this->redirect('register');
            }

            // Generate verification token
            $token       = bin2hex(random_bytes(32));
            $tokenExpiry = date('Y-m-d H:i:s', time() + EMAIL_VERIFY_EXPIRY);
            $userId     = 0;

            // Insert user
            try {
                $userId = $db->insert('users', [
                    'full_name'          => $fullName,
                    'school_email'       => $schoolEmail,
                    'personal_email'     => $personalEmail,
                    'phone'              => $phone,
                    'matric_number'      => $matric,
                    'password'           => password_hash(
                        $password,
                        PASSWORD_BCRYPT,
                        ['cost' => BCRYPT_COST]
                    ),
                    'email_verified'     => 0,
                    'status'             => 'inactive',
                    'email_verify_token' => $token,
                    'token_expires_at'   => $tokenExpiry,
                    'terms_accepted'     => 1,
                    'terms_version'      => TERMS_VERSION,
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                ]);

                if (!$userId) {
                    throw new Exception('Could not create user account.');
                }

                // Force correct verification state — overrides any trigger or hook
                $db->execute(
                    "UPDATE users
                     SET email_verified     = 0,
                         status             = 'inactive',
                         email_verify_token = ?,
                         token_expires_at   = ?
                     WHERE id = ?",
                    [$token, $tokenExpiry, $userId]
                );
            } catch (Exception $e) {
                Session::setFlash('error',
                    'Registration failed. Error: ' . $e->getMessage()
                );
                $this->redirect('register');
            }

            // Send verification email to Gmail ONLY
            $emailSent = false;
            try {
                $mailer    = new Mailer();
                $emailSent = $mailer->sendEmailVerification(
                    $personalEmail,
                    $fullName,
                    $token
                );
            } catch (Exception $e) {
                $emailSent = false;
            }

            // Clear form old data
            unset($_SESSION['form_old']);

            Session::set('pending_verify_email', $personalEmail);
            Session::set('pending_user_id',      (int)$userId);

            if ($emailSent) {
                Session::setFlash('success',
                    'Account created! A verification link has been sent to ' .
                    $personalEmail .
                    '. Check your Gmail inbox and click the link to activate.'
                );
            } else {
                Session::setFlash('warning',
                    'Account created but we could not send the verification email. ' .
                    'Contact support at ' . SUPPORT_EMAIL . ' with your Gmail ' .
                    'address to verify manually.'
                );
            }

            $this->redirect('verify-email');
        }

        $this->render('auth/register');
    }

    // ============================================================
    // EMAIL VERIFICATION
    // ============================================================
    public function verifyEmail(): void {
        $token = trim($_GET['token'] ?? '');

        if ($token) {
            $db   = DB::getInstance();
            $user = $db->row(
                "SELECT * FROM users
                 WHERE email_verify_token = ?
                 AND token_expires_at > NOW()
                 LIMIT 1",
                [$token]
            );

            if (!$user) {
                $this->render('auth/verify-email', [
                    'status' => 'invalid',
                    'email'  => Session::get('pending_verify_email', ''),
                ]);
                return;
            }

            $db->execute(
                "UPDATE users
                 SET email_verified     = 1,
                     status             = 'active',
                     email_verify_token = NULL,
                     token_expires_at   = NULL,
                     updated_at         = NOW()
                 WHERE id = ?",
                [$user['id']]
            );

           Session::set('pending_verify_email', '');
Session::set('pending_user_id', '');
            
            Session::setFlash('success',
                '✅ Email verified! Your account is now active. Welcome to CampusLink!'
            );
            $this->redirect('login');
        }

        $pendingEmail = Session::get('pending_verify_email', '');
        $this->render('auth/verify-email', [
            'status' => 'pending',
            'email'  => $pendingEmail,
        ]);
    }

    // ============================================================
    // FORGOT PASSWORD
    // Users enter their Gmail to reset password
    // ============================================================
    public function forgotPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $email = strtolower(clean($_POST['email'] ?? ''));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Session::setFlash('error',
                    'Please enter a valid email address.'
                );
                $this->redirect('forgot-password');
            }

            $db     = DB::getInstance();
            $token  = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY);

            // Check users by personal Gmail
            $user = $db->row(
                "SELECT * FROM users
                 WHERE personal_email = ? OR school_email = ?
                 LIMIT 1",
                [$email, $email]
            );

            if ($user) {
                $db->execute(
                    "UPDATE users
                     SET reset_token = ?, reset_token_expires = ?
                     WHERE id = ?",
                    [$token, $expiry, $user['id']]
                );
                try {
                    $mailer = new Mailer();
                    // Always send reset to personal Gmail
                    $mailer->sendPasswordReset(
                        $user['personal_email'],
                        $user['full_name'],
                        $token
                    );
                } catch (Exception $e) {}
            } else {
                // Check vendors
                $vendor = $db->row(
                    "SELECT * FROM vendors WHERE working_email = ? LIMIT 1",
                    [$email]
                );
                if ($vendor) {
                    $db->execute(
                        "UPDATE vendors
                         SET reset_token = ?, reset_token_expires = ?
                         WHERE id = ?",
                        [$token, $expiry, $vendor['id']]
                    );
                    try {
                        $mailer = new Mailer();
                        $mailer->sendPasswordReset(
                            $vendor['working_email'],
                            $vendor['full_name'],
                            $token
                        );
                    } catch (Exception $e) {}
                }
            }

            Session::setFlash('success',
                'If this email is registered you will receive a reset link ' .
                'in your Gmail shortly.'
            );
            $this->redirect('forgot-password');
        }

        $this->render('auth/forgot-password');
    }

    // ============================================================
    // RESET PASSWORD
    // ============================================================
    public function resetPassword(): void {
        $token = clean($_GET['token'] ?? '');

        if (empty($token)) {
            Session::setFlash('error', 'Invalid or missing reset link.');
            $this->redirect('forgot-password');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $password = $_POST['password']              ?? '';
            $confirm  = $_POST['password_confirmation'] ?? '';

            if ($password !== $confirm) {
                Session::setFlash('error', 'Passwords do not match.');
                $this->redirect('reset-password?token=' . urlencode($token));
            }

            if (strlen($password) < 8) {
                Session::setFlash('error',
                    'Password must be at least 8 characters.'
                );
                $this->redirect('reset-password?token=' . urlencode($token));
            }

            $db   = DB::getInstance();
            $hash = password_hash(
                $password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]
            );

            $user = $db->row(
                "SELECT * FROM users
                 WHERE reset_token = ? AND reset_token_expires > NOW()
                 LIMIT 1",
                [$token]
            );

            if ($user) {
                $db->execute(
                    "UPDATE users
                     SET password            = ?,
                         reset_token         = NULL,
                         reset_token_expires = NULL,
                         updated_at          = NOW()
                     WHERE id = ?",
                    [$hash, $user['id']]
                );
                Session::setFlash('success',
                    'Password reset successful. Please log in with your Gmail.'
                );
                $this->redirect('login');
            }

            $vendor = $db->row(
                "SELECT * FROM vendors
                 WHERE reset_token = ? AND reset_token_expires > NOW()
                 LIMIT 1",
                [$token]
            );

            if ($vendor) {
                $db->execute(
                    "UPDATE vendors
                     SET password            = ?,
                         reset_token         = NULL,
                         reset_token_expires = NULL
                     WHERE id = ?",
                    [$hash, $vendor['id']]
                );
                Session::setFlash('success',
                    'Password reset successful. Please log in.'
                );
                $this->redirect('vendor/login');
            }

            Session::setFlash('error',
                'This reset link is invalid or has expired. Please request a new one.'
            );
            $this->redirect('forgot-password');
        }

        $this->render('auth/reset-password', ['token' => $token]);
    }

    // ============================================================
    // LOGOUT
    // ============================================================
    public function logout(): void {
        // Clear all user session variables
        Session::delete('user_logged_in');
        Session::delete('user_id');
        Session::delete('user_name');
        Session::delete('user_email');
        Session::delete('user_role');

        // Also clear any vendor session variables that might be lingering
        Session::delete('vendor_logged_in');
        Session::delete('vendor_id');
        Session::delete('vendor_name');
        Session::delete('vendor_email');
        Session::delete('vendor_business');
        Session::delete('vendor_type');
        Session::delete('vendor_plan');
        Session::delete('vendor_status');

        // Clear admin session variables too
        Session::delete('admin_logged_in');
        Session::delete('admin_id');
        Session::delete('admin_name');
        Session::delete('admin_email');
        Session::delete('admin_role');

        Session::setFlash('success', 'You have been logged out successfully.');
        $this->redirect('login');
    }

    // ============================================================
    // VERIFY OTP — placeholder
    // ============================================================
    public function verifyOtp(): void {
        $this->redirect('login');
    }
}
