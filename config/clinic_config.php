<?php
/**
 * config/clinic_config.php
 * ------------------------------------------------------------
 * Clinic opening hours, in 24-hour HH:MM format. Appointments can
 * only be scheduled inside this window, and a visit must FINISH by
 * closing time (so with a 30-minute visit and a 21:00 close, the
 * latest start is 20:30).
 *
 * To change the hours, edit these two lines — nothing else needs
 * to be touched.
 */
define('CLINIC_OPEN_TIME',  '06:00');  // 6:00 AM
define('CLINIC_CLOSE_TIME', '21:00');  // 9:00 PM

// Printed at the top of exported PDF reports. Change this to your clinic's
// actual name.
define('CLINIC_NAME', 'EHR Clinic');