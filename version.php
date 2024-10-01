<?php
defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2024062624; // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2018120300; // Requires this Moodle version (3.6.2)
$plugin->component = 'block_attendance_export'; // Full name of the plugin (used for diagnostics)
$plugin->dependencies = array(
    'mod_attendance' => ANY_VERSION
);
?>

