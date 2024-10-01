<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/attendance_export/PHPExcel/Classes/PHPExcel.php');
require_once($CFG->dirroot.'/mod/attendance/lib.php');  // Ajusta según la ruta real de attendance

// Obtener el ID del curso de los parámetros de la URL
$courseid = required_param('courseid', PARAM_INT);

global $DB, $USER;

// Verifica que el usuario tenga permisos para descargar el archivo
require_login($courseid);
$context = context_course::instance($courseid);
require_capability('block/attendance_export:download', $context);

// Si no hay un token de acceso de Zoom en la sesión, redirige para obtener autorización
session_start();
if (!isset($_SESSION['zoom_access_token'])) {
    $client_id = 'PhNjyc7FQE20EdH9t_zUg'; // Reemplaza con tu Client ID
    $redirect_uri = $CFG->wwwroot . '/blocks/attendance_export/oauth_callback.php';
    $auth_url = "https://zoom.us/oauth/authorize?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}";
    header('Location: ' . $auth_url);
    exit;
}

// Funciones para obtener datos de Zoom y Moodle
require_once($CFG->dirroot.'/blocks/attendance_export/zoom_api.php');
require_once($CFG->dirroot.'/blocks/attendance_export/moodle_attendance.php');

// Obtiene los datos de asistencia de Zoom
$data_zoom = [];
$meetings = get_zoom_meetings($_SESSION['zoom_access_token']);
foreach ($meetings as $meeting) {
    $participants = get_zoom_attendance($_SESSION['zoom_access_token'], $meeting['id']);
    foreach ($participants as $participant) {
        $data_zoom[] = [
            'Nombre' => $participant['name'],
            'Email' => $participant['user_email'],
            'Hora de entrada' => strtotime($participant['join_time']),
            'Hora de salida' => strtotime($participant['leave_time']),
            'Fecha' => strtotime($meeting['start_time']),
            'Asistencia' => 'Zoom'
        ];
    }
}

// Obtiene los datos de asistencia de Moodle
$data_moodle_records = get_moodle_attendance($courseid);
$data_moodle = [];
foreach ($data_moodle_records as $record) {
    $data_moodle[] = [
        'Nombre' => $record->firstname . ' ' . $record->lastname,
        'Email' => $record->email,
        'Hora de entrada' => null,
        'Hora de salida' => null,
        'Fecha' => format_timestamp($record->sessdate),
        'Asistencia' => 'Moodle'
    ];
}

// Combinar los datos de asistencia de Zoom y Moodle
$combined_data = array_merge($data_zoom, $data_moodle);

foreach ($combined_data as &$data) {
    if (isset($data['Hora de entrada']) && $data['Hora de entrada'] !== null) {
        $data['Hora de entrada'] = format_timestamp($data['Hora de entrada']);
    }
    if (isset($data['Hora de salida']) && $data['Hora de salida'] !== null) {
        $data['Hora de salida'] = format_timestamp($data['Hora de salida']);
    }
    if (isset($data['Fecha']) && $data['Fecha'] !== null) {
        $data['Fecha'] = format_timestamp($data['Fecha']);
    }
}

// Crear el archivo Excel
$filename = 'attendance_data.xlsx';
$workbook = new PHPExcel();
$worksheet = $workbook->setActiveSheetIndex(0);

// Escribir los encabezados
$headers = array('Nombre', 'Email', 'Hora de entrada', 'Hora de salida', 'Fecha', 'Asistencia');
$worksheet->fromArray($headers, NULL, 'A1');

// Escribir los datos
$row = 2;
foreach ($combined_data as $data) {
    $worksheet->fromArray(array_values($data), NULL, 'A' . $row);
    $row++;
}

// Enviar el archivo Excel al navegador
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($workbook, 'Excel2007');
$objWriter->save('php://output');
exit;
?>
