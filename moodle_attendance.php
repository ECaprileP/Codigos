<?php

function get_moodle_attendance($courseid) {
    global $DB;

    $sql = "SELECT att.id, att.name, sess.sessdate, log.statusset, u.firstname, u.lastname, u.email
            FROM {attendance} att
            JOIN {attendance_sessions} sess ON att.id = sess.attendanceid
            JOIN {attendance_log} log ON sess.id = log.sessionid
            JOIN {user} u ON log.studentid = u.id
            WHERE att.course = ?";
    return $DB->get_records_sql($sql, array($courseid));
}

function format_timestamp($timestamp) {
    return date("Y-m-d H:i:s", $timestamp);
}
?>
