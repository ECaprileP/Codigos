<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/attendance_export/zoom_api.php');

$client_id = 'PhNjyc7FQE20EdH9t_zUg';  // Reemplaza con tu Client ID
$client_secret = 'BlQOu14ZtOBk1Q1lPdSu44tTdel3qDJJ';  // Reemplaza con tu Client Secret
$redirect_uri = $CFG->wwwroot . '/blocks/attendance_export/oauth_callback.php';  // Asegúrate de que coincida exactamente con la URI registrada en Zoom

// Verificar si el código de autorización está presente en la URL
if (!isset($_GET['code'])) {
    die('Error: No code parameter in callback');
}

$code = $_GET['code']; // Captura el código de autorización

// URL para solicitar el token de acceso
$token_url = 'https://zoom.us/oauth/token';

// Parámetros que enviaremos en la solicitud POST para intercambiar el código por un token de acceso
$post_fields = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirect_uri
];

// Encabezados de la solicitud, incluyendo la autenticación básica (Client ID y Client Secret)
$headers = [
    'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
    'Content-Type: application/x-www-form-urlencoded'
];

// Iniciar cURL para hacer la solicitud a Zoom
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Añadir opciones adicionales de cURL para mejorar la depuración
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Deshabilitar la verificación de SSL (útil para pruebas locales)
curl_setopt($ch, CURLOPT_FAILONERROR, false); // No detener si hay un error HTTP
curl_setopt($ch, CURLOPT_VERBOSE, true);  // Para imprimir detalles de la solicitud
curl_setopt($ch, CURLOPT_HEADER, true);   // Para imprimir los encabezados HTTP en la respuesta

// Ejecutar la solicitud y obtener la respuesta
$response = curl_exec($ch);

// Manejo de errores en la solicitud cURL
if ($response === false) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    die('Error fetching token from Zoom: ' . $error_msg);
}

// Obtener el código de estado HTTP
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Verificar si la respuesta fue exitosa (código HTTP 200)
if ($http_code != 200) {
    die('Error from Zoom API. HTTP Code: ' . $http_code . '. Response: ' . $response);
}

// Cerrar la conexión cURL
curl_close($ch);

// Decodificar la respuesta JSON de Zoom
$data = json_decode($response, true);

// Verificar si hubo un error en la decodificación de JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error decoding JSON response from Zoom: ' . json_last_error_msg());
}

// Verificar si la respuesta contiene un error de la API de Zoom
if (isset($data['error'])) {
    die('Error from Zoom API: ' . $data['error'] . ' - ' . $data['error_description']);
}

// Almacenar los tokens en la sesión o base de datos
session_start();
$_SESSION['zoom_access_token'] = $data['access_token'];
$_SESSION['zoom_refresh_token'] = $data['refresh_token'];
$_SESSION['zoom_token_expires_at'] = time() + $data['expires_in'];  // Guardar el tiempo exacto en que expira el token

// Redirigir de vuelta a la página de éxito o a la funcionalidad deseada
header('Location: ' . $CFG->wwwroot . '/blocks/attendance_export/download.php'); // Cambia la URL a donde quieras redirigir después de recibir el token
exit;

