<?php
/**
 * CampusLink - Static & Legal Pages Controller
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Controller.php';

class PageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function about(): void
    {
        $this->view('pages/about', ['pageTitle' => 'About CampusLink - ' . SITE_NAME]);
    }

    public function contact(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $name    = Sanitizer::text($this->post('name', ''), 100);
            $email   = Sanitizer::email($this->post('email', ''));
            $subject = Sanitizer::text($this->post('subject', ''), 200);
            $message = Sanitizer::textarea($this->post('message', ''), 2000);

            if (empty($name) || empty($email) || empty($message)) {
                $this->view('pages/contact', [
                    'pageTitle' => 'Contact Us - ' . SITE_NAME,
                    'error'     => 'Please fill in all required fields.',
                    'old'       => compact('name','email','subject','message'),
                    'csrfField' => CSRF::field(),
                ]);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->view('pages/contact', [
                    'pageTitle' => 'Contact Us - ' . SITE_NAME,
                    'error'     => 'Please enter a valid email address.',
                    'old'       => compact('name','email','subject','message'),
                    'csrfField' => CSRF::field(),
                ]);
                return;
            }

            // Rate limit contact form
            if (!RateLimiter::check('contact', getClientIP(), 5, 3600)) {
                $this->view('pages/contact', [
                    'pageTitle' => 'Contact Us - ' . SITE_NAME,
                    'error'     => 'Too many submissions. Please try again later.',
                    'csrfField' => CSRF::field(),
                ]);
                return;
            }

            // Send to admin email
            $mailer = new Mailer();
            $html = "
            <p><strong>From:</strong> $name ($email)</p>
            <p><strong>Subject:</strong> $subject</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br(e($message)) . "</p>
            ";
            $mailer->send(ADMIN_EMAIL, 'CampusLink Admin', "Contact Form: $subject", $html);

            $this->view('pages/contact', [
                'pageTitle' => 'Contact Us - ' . SITE_NAME,
                'success'   => 'Your message has been sent. We will get back to you shortly.',
                'csrfField' => CSRF::field(),
            ]);
            return;
        }

        $this->view('pages/contact', [
            'pageTitle' => 'Contact Us - ' . SITE_NAME,
            'csrfField' => CSRF::field(),
        ]);
    }

    public function howItWorks(): void
    {
        $this->view('pages/how-it-works', ['pageTitle' => 'How It Works - ' . SITE_NAME]);
    }

    public function generalTerms(): void
    {
        $this->view('pages/general-terms', ['pageTitle' => 'General Terms & Conditions - ' . SITE_NAME]);
    }

    public function userTerms(): void
    {
        $this->view('pages/user-terms', ['pageTitle' => 'User Terms & Conditions - ' . SITE_NAME]);
    }

    public function vendorTerms(): void
    {
        $this->view('pages/vendor-terms', ['pageTitle' => 'Vendor Terms & Conditions - ' . SITE_NAME]);
    }

    public function privacyPolicy(): void
    {
        $this->view('pages/privacy-policy', ['pageTitle' => 'Privacy Policy - ' . SITE_NAME]);
    }

    public function refundPolicy(): void
    {
        $this->view('pages/refund-policy', ['pageTitle' => 'Refund Policy - ' . SITE_NAME]);
    }

    public function suspensionPolicy(): void
    {
        $this->view('pages/suspension-policy', ['pageTitle' => 'Suspension Policy - ' . SITE_NAME]);
    }

    public function complaintResolution(): void
    {
        $this->view('pages/complaint-resolution', ['pageTitle' => 'Complaint Resolution - ' . SITE_NAME]);
    }

    public function dataRetention(): void
    {
        $this->view('pages/data-retention', ['pageTitle' => 'Data Retention Policy - ' . SITE_NAME]);
    }

    public function error(int $code = 404): void
    {
        $code = (int)($this->get('code', $code));
        $messages = [
            400 => 'Bad Request',
            403 => 'Access Forbidden',
            404 => 'Page Not Found',
            500 => 'Internal Server Error',
        ];

        http_response_code($code);
        $this->view('pages/error', [
            'pageTitle' => "$code - " . ($messages[$code] ?? 'Error') . ' | ' . SITE_NAME,
            'code'      => $code,
            'message'   => $messages[$code] ?? 'An error occurred.',
        ]);
    }
}