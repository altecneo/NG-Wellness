<?php
/**
 * contact.php — North Georgia Functional Wellness
 * Receives POST from the contact form, sends email to Julie.
 * Returns JSON: {"success": true} or {"success": false, "error": "..."}
 */

// ── CONFIG ────────────────────────────────────────────────────────
define('TO_EMAIL',   'julie@northgeorgiafunctionalwellness.com');
define('TO_NAME',    'Julie Miles');
define('FROM_EMAIL', 'noreply@northgeorgiafunctionalwellness.com');
define('FROM_NAME',  'North Georgia Functional Wellness Website');
define('SUBJECT',    'New Contact Form Submission — North Georgia Functional Wellness');

// ── HEADERS ───────────────────────────────────────────────────────
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── HONEYPOT — reject bots ─────────────────────────────────────────
if (!empty($_POST['website'])) {
    // Silently succeed so bots don't know they were caught
    echo json_encode(['success' => true]);
    exit;
}

// ── COLLECT AND SANITIZE INPUT ────────────────────────────────────
function clean($value) {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

$fname             = clean($_POST['fname']             ?? '');
$lname             = clean($_POST['lname']             ?? '');
$email             = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone             = clean($_POST['phone']             ?? 'Not provided');
$preferred_contact = clean($_POST['preferred_contact'] ?? '');
$message           = clean($_POST['message']           ?? 'No message provided');

// ── VALIDATE ──────────────────────────────────────────────────────
$errors = [];

if (empty($fname)) $errors[] = 'First name is required.';
if (empty($lname)) $errors[] = 'Last name is required.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

// ── BUILD EMAIL BODY ──────────────────────────────────────────────
$full_name   = $fname . ' ' . $lname;
$submitted   = date('F j, Y \a\t g:i A T');

$body_text = <<<TEXT
New contact form submission from your website.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  CONTACT DETAILS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Name:               {$full_name}
Email:              {$email}
Phone:              {$phone}
Preferred Contact:  {$preferred_contact}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  MESSAGE / HEALTH GOALS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
{$message}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Submitted: {$submitted}
Source:    northgeorgiafunctionalwellness.com/contact
TEXT;

$body_html = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Georgia,serif;max-width:600px;margin:0 auto;background:#FAF8F4;padding:20px;">
  <div style="background:#3D4935;padding:28px 32px;border-radius:4px 4px 0 0;">
    <h2 style="color:white;margin:0;font-size:1.4rem;font-weight:400;">
      New Contact Form Submission
    </h2>
    <p style="color:rgba(255,255,255,0.7);margin:6px 0 0;font-size:0.9rem;">
      North Georgia Functional Wellness
    </p>
  </div>

  <div style="background:white;padding:32px;border:1px solid #E2DDD6;border-top:none;">

    <table style="width:100%;border-collapse:collapse;margin-bottom:28px;">
      <tr>
        <td style="padding:12px 16px;background:#F0EDE6;font-family:Lato,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#3D4935;width:38%;border-bottom:1px solid #E2DDD6;">Name</td>
        <td style="padding:12px 16px;background:#F0EDE6;font-size:0.95rem;color:#2A3024;border-bottom:1px solid #E2DDD6;">{$full_name}</td>
      </tr>
      <tr>
        <td style="padding:12px 16px;font-family:Lato,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#3D4935;border-bottom:1px solid #E2DDD6;">Email</td>
        <td style="padding:12px 16px;border-bottom:1px solid #E2DDD6;"><a href="mailto:{$email}" style="color:#3D4935;">{$email}</a></td>
      </tr>
      <tr>
        <td style="padding:12px 16px;background:#F0EDE6;font-family:Lato,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#3D4935;border-bottom:1px solid #E2DDD6;">Phone</td>
        <td style="padding:12px 16px;background:#F0EDE6;font-size:0.95rem;color:#2A3024;border-bottom:1px solid #E2DDD6;">{$phone}</td>
      </tr>
      <tr>
        <td style="padding:12px 16px;font-family:Lato,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#3D4935;border-bottom:1px solid #E2DDD6;">Preferred Contact</td>
        <td style="padding:12px 16px;font-size:0.95rem;color:#2A3024;border-bottom:1px solid #E2DDD6;">{$preferred_contact}</td>
      </tr>
    </table>

    <div style="background:#F0EDE6;border-left:4px solid #C4A96B;padding:20px 24px;border-radius:2px;">
      <p style="font-family:Lato,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#3D4935;margin:0 0 10px;">Health Goals / Message</p>
      <p style="font-size:0.95rem;color:#2A3024;line-height:1.7;margin:0;white-space:pre-wrap;">{$message}</p>
    </div>

    <div style="margin-top:28px;padding-top:20px;border-top:1px solid #E2DDD6;">
      <a href="mailto:{$email}?subject=Re: Your Inquiry — North Georgia Functional Wellness"
         style="display:inline-block;background:#3D4935;color:white;padding:12px 24px;text-decoration:none;border-radius:2px;font-family:Lato,sans-serif;font-size:12px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;">
        Reply to {$fname}
      </a>
    </div>
  </div>

  <div style="padding:16px 20px;text-align:center;font-size:0.75rem;color:#7A8572;">
    Submitted {$submitted} &nbsp;|&nbsp; northgeorgiafunctionalwellness.com
  </div>
</body>
</html>
HTML;

// ── SEND EMAIL ────────────────────────────────────────────────────
$mail_headers  = "MIME-Version: 1.0\r\n";
$mail_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$mail_headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
$mail_headers .= "Reply-To: {$full_name} <{$email}>\r\n";
$mail_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$sent = mail(TO_EMAIL, SUBJECT, $body_html, $mail_headers);

// ── SEND AUTO-REPLY TO VISITOR ────────────────────────────────────
if ($sent) {
    $reply_subject = "I received your message — North Georgia Functional Wellness";
    $reply_html = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Georgia,serif;max-width:600px;margin:0 auto;background:#FAF8F4;padding:20px;">
  <div style="background:#3D4935;padding:28px 32px;border-radius:4px 4px 0 0;">
    <h2 style="color:white;margin:0;font-size:1.4rem;font-weight:400;">Thank you, {$fname}!</h2>
    <p style="color:rgba(255,255,255,0.7);margin:6px 0 0;font-size:0.9rem;">North Georgia Functional Wellness</p>
  </div>
  <div style="background:white;padding:32px;border:1px solid #E2DDD6;border-top:none;">
    <p style="font-size:1rem;color:#2A3024;line-height:1.8;">
      I received your message and appreciate you reaching out. I personally review every inquiry and will be in touch within <strong>1–2 business days</strong>.
    </p>
    <p style="font-size:0.95rem;color:#4A5444;line-height:1.8;">
      In the meantime, if you have any urgent questions, feel free to reply directly to this email.
    </p>
    <div style="background:#F0EDE6;border-left:4px solid #C4A96B;padding:18px 22px;border-radius:2px;margin:24px 0;">
      <p style="margin:0;font-size:0.9rem;color:#4A5444;line-height:1.7;font-style:italic;">
        "I believe true wellness involves caring for the whole person — body, mind, and spirit."
      </p>
      <p style="margin:8px 0 0;font-size:0.8rem;color:#7A8572;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">— Julie Miles, ND, CHHP, CLT</p>
    </div>
    <p style="font-size:0.9rem;color:#4A5444;">Warmly,<br><strong>Julie Miles, ND, CHHP, CLT</strong><br>North Georgia Functional Wellness</p>
  </div>
  <div style="padding:16px 20px;text-align:center;font-size:0.75rem;color:#7A8572;">
    <a href="https://northgeorgiafunctionalwellness.com" style="color:#3D4935;">northgeorgiafunctionalwellness.com</a>
  </div>
</body>
</html>
HTML;

    $reply_headers  = "MIME-Version: 1.0\r\n";
    $reply_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $reply_headers .= "From: Julie Miles, ND <" . FROM_EMAIL . ">\r\n";
    $reply_headers .= "Reply-To: " . TO_NAME . " <" . TO_EMAIL . ">\r\n";

    mail($email, $reply_subject, $reply_html, $reply_headers);
}

// ── RESPOND TO BROWSER ────────────────────────────────────────────
if ($sent) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Mail server error. Please try again.']);
}
?>
