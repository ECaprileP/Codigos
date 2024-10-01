<?php
class block_attendance_export extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_attendance_export');
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = $this->generate_button();
        $this->content->footer = '';

        return $this->content;
    }

    private function generate_button() {
        global $COURSE, $PAGE;

        // Obtener el contexto del curso actual
        $context = context_course::instance($COURSE->id);
        if (!has_capability('block/attendance_export:download', $context)) {
            return get_string('nopermission', 'block_attendance_export');
        }

        // Generar la URL para la descarga del archivo de asistencia
        $url = new moodle_url('/blocks/attendance_export/download.php', array('courseid' => $COURSE->id));
        return html_writer::link($url, get_string('download_excel', 'block_attendance_export'), ['class' => 'btn btn-primary']);
    }
}
?>

