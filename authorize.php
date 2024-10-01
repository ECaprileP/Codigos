<?php
require_once('../../config.php');

$client_id = 'eN77ZNRS8qKi7gB9gjWpg';
$redirect_uri = $CFG->wwwroot . '/blocks/attendance_export/oauth_callback.php';

$url = "https://zoom.us/oauth/authorize?response_type=code&client_id={$client_id}&redirect_uri=" . urlencode($redirect_uri);

header('Location: ' . $url);
exit;
?>

