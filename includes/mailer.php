<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../vendor/autoload.php";   // adjust if your vendor folder is elsewhere
require_once __DIR__ . "/../config/mail_config.php";

/**
 * Emails a doctor about a newly scheduled appointment.
 * Returns true on success, false on failure. It never throws, so a mail
 * problem can't break the scheduling itself.
 *
 * Deliberately leaves out the reason for the visit: it's medical
 * information and email isn't a secure channel.
 *
 * Layout: a fluid, table-based email. The card is up to 640px wide on a
 * desktop and shrinks to fit any phone. Inline styles are the base (works
 * everywhere); the <style> block adds the mobile tweaks for apps that
 * support media queries (Gmail, Apple Mail, most phone mail apps).
 */
function send_appointment_notification(array $d): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->Timeout    = 10;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($d['doctor_email'], $d['doctor_name']);

        $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

        // One detail row. The label and value cells switch from side-by-side
        // (desktop) to stacked (phone) through the .stack class below.
        $row = function (string $label, string $value, bool $last = false) use ($e): string {
            $border = $last ? '' : 'border-bottom:1px solid #E6E3F2;';
            return "
                <tr>
                    <td class=\"stack lbl\" style=\"padding:16px 0 16px 0;{$border}font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#9A93B3;width:34%;vertical-align:top;font-family:Arial,Helvetica,sans-serif;\">{$e($label)}</td>
                    <td class=\"stack val\" style=\"padding:16px 0 16px 0;{$border}font-size:16px;line-height:1.45;font-weight:600;color:#1B1730;vertical-align:top;font-family:Arial,Helvetica,sans-serif;\">{$value}</td>
                </tr>";
        };

        $patientHtml = $e($d['patient_name'])
            . "<br><span style=\"font-family:Consolas,Menlo,monospace;font-size:13px;font-weight:500;color:#675F82;\">{$e($d['patient_code'])}</span>";

        $rows = $row('Patient', $patientHtml)
              . $row('Date', $e($d['date_label']))
              . $row('Time', $e($d['time_label']))
              . $row('Duration', $e($d['duration']) . ' minutes')
              . $row('Scheduled by', $e($d['scheduled_by']), true);

        $preheader = "{$d['patient_name']} on {$d['date_label']} at {$d['time_label']}";

        $mail->isHTML(true);
        $mail->Subject = 'New appointment: ' . $d['date_label'] . ' at ' . $d['time_label'];
        $mail->Body = "<!DOCTYPE html>
<html lang=\"en\">
<head>
<meta charset=\"UTF-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
<meta name=\"x-apple-disable-message-reformatting\">
<title>New Appointment</title>
<style>
    body, table, td { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
    img { border:0; }
    @media only screen and (max-width:600px) {
        .outer-pad { padding:12px 10px !important; }
        .card-pad  { padding-left:20px !important; padding-right:20px !important; }
        .title     { font-size:22px !important; }
        .detail-pad { padding-left:16px !important; padding-right:16px !important; }
        .stack     { display:block !important; width:100% !important; box-sizing:border-box; }
        .lbl       { padding-bottom:2px !important; border-bottom:none !important; }
        .val       { padding-top:0 !important; }
    }
</style>
</head>
<body style=\"margin:0;padding:0;background-color:#F3F1FB;width:100%;\">

<!-- Preheader: the grey preview text shown in the inbox list -->
<div style=\"display:none;max-height:0;overflow:hidden;opacity:0;font-size:1px;line-height:1px;color:#F3F1FB;\">
    {$e($preheader)}
</div>

<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:#F3F1FB;width:100%;\">
<tr><td align=\"center\" class=\"outer-pad\" style=\"padding:40px 20px;\">

    <!-- Fluid card: full width up to 640px -->
    <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%;max-width:640px;background-color:#ffffff;border:1px solid #E6E3F2;border-radius:16px;\">

        <!-- Accent bar -->
        <tr><td style=\"background-color:#4F3FBF;height:6px;font-size:0;line-height:0;border-radius:16px 16px 0 0;\">&nbsp;</td></tr>

        <!-- Heading -->
        <tr><td class=\"card-pad\" style=\"padding:36px 44px 8px 44px;font-family:Arial,Helvetica,sans-serif;\">
            <span style=\"display:inline-block;background-color:#EFECFC;color:#3E30A0;font-size:12px;font-weight:700;padding:6px 14px;border-radius:999px;\">New appointment</span>
            <h1 class=\"title\" style=\"margin:18px 0 12px 0;font-family:Georgia,'Times New Roman',serif;font-size:28px;line-height:1.25;font-weight:600;color:#1B1730;\">A new appointment was added to your schedule</h1>
            <p style=\"margin:0;font-size:16px;line-height:1.6;color:#675F82;\">Hello {$e($d['doctor_name'])}, here are the details:</p>
        </td></tr>

        <!-- Details -->
        <tr><td class=\"card-pad\" style=\"padding:22px 44px 8px 44px;font-family:Arial,Helvetica,sans-serif;\">
            <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:#FBFAFF;border:1px solid #E6E3F2;border-radius:12px;\">
                <tr><td class=\"detail-pad\" style=\"padding:6px 24px;\">
                    <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                        {$rows}
                    </table>
                </td></tr>
            </table>
        </td></tr>

        <!-- Footer note -->
        <tr><td class=\"card-pad\" style=\"padding:22px 44px 36px 44px;font-family:Arial,Helvetica,sans-serif;\">
            <p style=\"margin:0;font-size:14px;line-height:1.6;color:#675F82;\">Log in to the EHR system to view the full details of this appointment.</p>
        </td></tr>

    </table>

    <!-- Outer footer -->
    <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%;max-width:640px;\">
        <tr><td align=\"center\" style=\"padding:22px 12px 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#9A93B3;\">
            This is an automated message from the clinic's EHR system. Please do not reply.<br>
            Visit details are kept inside the system and are not included in this email.
        </td></tr>
    </table>

</td></tr>
</table>
</body>
</html>";

        $mail->AltBody = "New appointment: {$d['patient_name']} ({$d['patient_code']}) on {$d['date_label']} at {$d['time_label']} ({$d['duration']} min). Scheduled by {$d['scheduled_by']}. Log in to the EHR system to view details.";

        $mail->send();
        return true;
    } catch (Exception $ex) {
        error_log('Appointment email failed: ' . $mail->ErrorInfo);
        return false;
    }
}