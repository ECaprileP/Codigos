<?php

function get_zoom_meetings($access_token) {
    $url = "https://api.zoom.us/v2/users/me/meetings";
    $headers = [
        "Authorization: Bearer $access_token",
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        die('Error fetching meetings from Zoom');
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Error decoding JSON response from Zoom');
    }

    if (isset($data['code'])) {
        die('Error from Zoom API: ' . $data['message']);
    }

    return $data['meetings'];
}

function get_zoom_attendance($access_token, $meeting_id) {
    $url = "https://api.zoom.us/v2/report/meetings/{$meeting_id}/participants";
    $headers = [
        "Authorization: Bearer $access_token",
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        die('Error fetching participants from Zoom');
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Error decoding JSON response from Zoom');
    }

    if (isset($data['code'])) {
        die('Error from Zoom API: ' . $data['message']);
    }

    return $data['participants'];
}
?>

