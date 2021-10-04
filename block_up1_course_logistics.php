<?php
/**
 * @package    block_up1_course_logistics
 * @copyright  2021 Silecs & Université Paris1 Panthéon-Sorbonne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class block_up1_course_logistics extends block_base
{
    public $blockname = null;
    
    public $courseupdate = false;
    
    public $hasstudentrole = false;
    
    public $mycourse = null;
    
    /**
     * Does the block have a global settings.
     *
     * @return bool
     */
    public function has_config()
    {
        return true;
    }
    
    public function init()
    {
        global $COURSE, $USER, $DB;
        
        $title = 'titlestudent';
        $context = context_course::instance($COURSE->id);
        if (has_capability('moodle/course:update', $context)) {
            $this->courseupdate = true;
            $title = 'titleteacher';
        } else {
            $studentrole = $DB->get_record('role', ['shortname'=> 'student']);
            if ($studentrole) {
                $this->hasstudentrole = user_has_role_assignment($USER->id, $studentrole->id, $context->id);
            }
        }
        $this->blockname = get_class($this);
        $this->title = get_string($title, $this->blockname);
    }
    
    public function get_content()
    {
        global $PAGE;
        if ($this->content !== null) {
            return $this->content;
        }
        
        $PAGE->requires->js('/blocks/up1_course_logistics/javascript/up1courselogistics.js');
        
        $this->content =  new stdClass;
        
        $format = course_get_format($this->page->course);
        $this->mycourse = $format->get_course();
        
        if ($this->courseupdate) {
            global $OUTPUT;
            $iconeslink = $OUTPUT->pix_icon('t/expanded', '', 'moodle', ['class' => 'hidden']) . $OUTPUT->pix_icon('t/collapsed', '', 'moodle');
            
            $infos = $this->get_info_courseopen();
            $inscrits = $this->get_info_registered();
            $teacherlist =  $this->get_course_teachers_list_active($iconeslink);
            $manageenrol = $this->get_list_manage_enrol($iconeslink);
            $acces = $this->get_course_register_list_teacher();
            $infos .= html_writer::tag('div', $inscrits . $teacherlist . $manageenrol . $acces, ['class' => 'teacher-enrol-bloc']);
            $infos .= $this->get_teacher_help_block();
            $this->content->text = $infos;
        } elseif ($this->hasstudentrole) {
            $infos = $this->get_info_composante();
            $infos .= $this->get_course_teachers_list();
            $labelnumcourse = get_string('numcours', $this->blockname) . ' : ' . $this->mycourse->id;
            $infos .= html_writer::tag('div', $labelnumcourse, ['class' => 'student-label-courseid']);
            $infos .= $this->get_info_course_dates();
            $infos .= $this->get_course_register_list();
            $infos .= get_config($this->blockname, 'studenthelp');
            $this->content->text = $infos;
        }
        return $this->content;
    }
    
    /**
     * Construit pour le bloc version étudiante
     * Utilise une fonction de local_up1_metadata
     * @return string html
     */
    private function get_info_composante()
    {
        $info = html_writer::tag('div', get_string('composante', $this->blockname), ['class' => 'student-label-composante'])
            . $this->br(up1_meta_get_list($this->mycourse->id, 'up1composante', false, ' / ', false))
            . $this->br(up1_meta_get_list($this->mycourse->id, 'up1mention', false, ' / ', true))
            . $this->br(up1_meta_get_list($this->mycourse->id, 'up1niveau', false, ' / ', true));
        return html_writer::tag('div', $info, ['class' => 'student-info-composante']);
    }
    
    /**
     * Construit la ligne concernant les dates de début et fin du cours.
     * @return string html
     */
    private function get_info_course_dates()
    {
        global $OUTPUT;
        $startdate = usergetdate($this->mycourse->startdate);
        $enddate = usergetdate($this->mycourse->enddate);
        $info = get_string('debut', $this->blockname) . ' : ' . $startdate['mday'].'/'.$startdate['mon'].'/'.$startdate['year']
            . ($this->mycourse->enddate == 0 ? '' : ' - ' . get_string('fin', $this->blockname) . ' : ' . $enddate['mday'].'/'.$enddate['mon'].'/'.$enddate['year']);
        $classname = $this->courseupdate ? 'teacher-info-date' : 'student-info-date';
        $action = '';
        if ($classname == 'teacher-info-date') {
            $url = new moodle_url('/local/crswizard/update/index.php', ['id' => $this->mycourse->id, 'direct' => 'index']);
            $action = html_writer::tag('span', " ") . html_writer::link($url, $OUTPUT->pix_icon('t/edit', '', 'moodle'));
        }
        return html_writer::tag('div', $info . $action, ['class' => $classname]);
    }
    
    /**
     * Renvoie la liste des enseignants du cours en format html.
     * Utilisé dans la version étudiante du bloc
     * @return string
     */
    private function get_course_teachers_list()
    {
        $info = html_writer::tag('div', get_string('enseignants', $this->blockname), ['class' => 'student-label-teachers']);
        $listehtml = '';
        $teachers = $this->get_all_teachers();
        if ($teachers) {
            foreach ($teachers as $teacher) {
                $listehtml .= html_writer::tag('li', $teacher);
            }
        }
        $info .= html_writer::tag('ul', $listehtml, ['class' => 'student-list-teachers']);
        return html_writer::tag('div', $info, ['class' => 'student-info-teachers']);
    }
    
    /**
     * Renvoie la liste dynamique des enseignants du cours en format html.
     * Utilisé dans la version enseignante du bloc
     * @param string $iconeslink html
     * @return string HTML
     */
    private function get_course_teachers_list_active($iconeslink)
    {
        $info = html_writer::tag(
            'div',
            $iconeslink . get_string('listteachers', $this->blockname),
            ['class' => 'teacher-label-teachers','id' => 'teacher-label-teachers', 'onclick' => 'togglecollapseall("teacher-label-teachers");']
        );
        $listehtml = '';
        $teachers = $this->get_all_teachers();
        if ($teachers) {
            foreach ($teachers as $teacher) {
                $listehtml .= html_writer::tag('li', $teacher);
            }
        }
        $info .= html_writer::tag('ul', $listehtml, ['class' => 'teacher-list-teachers hidden', 'id' => 'bloc-teacher-label-teachers']);
        return html_writer::tag('div', $info, ['class' => 'teacher-info-teachers']);
    }
    
    /**
     * Construit la liste des types d'accès du cours pour la version étudiante
     * Utilise le plugin local_up1_courselist
     * @return string html
     */
    private function get_course_register_list()
    {
        $info = html_writer::tag('span', get_string('acces', $this->blockname) . ' : ', ['class' => 'student-label-acces']);
        $courseformatter = new courselist_format('list');
        $info .= $courseformatter->format_icons($this->mycourse, 'icons');
        return html_writer::tag('div', $info, ['class' => 'student-info-acces']);
    }
    
    /**
     * Construit la liste des méthodes d'inscription du cours pour la vue enseignante
     * @return string html
     */
    private function get_course_register_list_teacher()
    {
        global $OUTPUT;
        $listeicones = ['manual' => 'i/user', 'cohort' => 'i/cohort', 'self' => 'i/key', 'guest' => 't/unlocked'];
        $info = html_writer::tag('span', get_string('acces', $this->blockname) . ' : ', ['class' => 'teacher-label-acces']);
        $instances = \enrol_get_instances($this->mycourse->id, true);
        $plugins = \enrol_get_plugins(true);
        $info .= html_writer::start_tag('span');
        foreach ($instances as $instance) {
            if (!isset($plugins[$instance->enrol])) {
                continue;
            }
            $plugin = $plugins[$instance->enrol];
            $name = $plugin->get_instance_name($instance);
            if (isset($listeicones[$instance->enrol])) {
                $info .= $OUTPUT->pix_icon($listeicones[$instance->enrol], $name, 'moodle', ['title' => $name]);
            } else {
                $info .= $OUTPUT->pix_icon('i/item', $name, 'moodle', ['title' => $name]);
            }
        }
        $info .= html_writer::end_tag('span');
        return html_writer::tag('div', $info, ['class' => 'teacher-info-acces']);
    }
    
    /**
     * Construit la liste dynamique des actions permettant de gérer les inscriptions
     * Utilisé dans la version enseignante du bloc
     * @param string $iconeslink html
     * @return string HTML
     */
    private function get_list_manage_enrol($iconeslink)
    {
        global $OUTPUT;
        $infos = html_writer::tag(
            'div',
            $iconeslink . get_string('manual:manage', 'enrol_manual'),
            ['class' => 'teacher-label-manage', 'id' => 'teacher-label-manage', 'onclick' => 'togglecollapseall("teacher-label-manage");']
        );
        $urlcohort = new moodle_url('/local/crswizard/update/index.php', ['id' => $this->mycourse->id, 'direct' => 'cohort']);
        $items = html_writer::tag('li', html_writer::link($urlcohort, get_string('enrolstudents', $this->blockname)));
        $items .=html_writer::tag('li', html_writer::link(get_config($this->blockname, 'urlapogee'), get_string('findapogee', $this->blockname)));
        $urlteacher = new moodle_url('/local/crswizard/update/index.php', ['id' => $this->mycourse->id, 'direct' => 'teacher']);
        $items .=html_writer::tag('li', html_writer::link($urlteacher, get_string('enrolteachers', $this->blockname)));
        $urlfreeacces = new moodle_url('/local/crswizard/update/index.php', ['id' => $this->mycourse->id, 'direct' => 'key']);
        $items .=html_writer::tag('li', html_writer::link($urlfreeacces, get_string('freeacces', $this->blockname)));
        $infos .= html_writer::tag('ul', $items, ['class' => 'teacher-manage-enrol-list hidden', 'id' => 'bloc-teacher-label-manage']);
        return html_writer::tag('div', $infos, ['class' => 'teacher-manage-enrol-bloc']);
    }
    
    /**
     * Renvoie tous les contributeurs enseignants du cours, classé par ordre de pouvroir
     * @return array
     */
    private function get_all_teachers()
    {
        global $DB, $COURSE;
        $allteachers = [];
        $context = context_course::instance($COURSE->id);
        $roles = ['responsable_epi', 'editingteacher', 'teacher'];
        foreach ($roles as $shortname) {
            $role = $DB->get_record('role', ['shortname'=> $shortname]);
            if ($role) {
                $teachers = get_role_users($role->id, $context);
                if ($teachers) {
                    foreach ($teachers as $teacher) {
                        if (!isset($allteachers[$teacher->id])) {
                            $allteachers[$teacher->id] = fullname($teacher);
                        }
                    }
                }
            }
        }
        return $allteachers;
    }
    
    /**
     * Construit la ligne d'information au sujet des inscrits du cours
     * @return string html
     */
    private function get_info_registered()
    {
        global $DB, $COURSE;
        $context = context_course::instance($COURSE->id);
        $linkinscrits = html_writer::link(new moodle_url('/user/index.php', ['id' => $COURSE->id]), 'inscrits');
        $nbinscrits = $DB->count_records_sql("SELECT COUNT(DISTINCT userid) FROM {role_assignments} WHERE contextid = ?", [$context->id]);
        $inscrits = html_writer::tag('div', "Nombre d'" . $linkinscrits . ' : ' . $nbinscrits, ['class' => 'teacher-label-nbinscrits']);
        return $inscrits;
    }
    
    /**
     * Construit le bloc Assistance pour le vue enseignante
     * @return string html
     */
    private function get_teacher_help_block()
    {
        $bloc = html_writer::tag('div', get_string('assistance', $this->blockname), ['class' => 'teacher-label-assistance']);
        $labelnumcourse = get_string('numcourstogive', $this->blockname) . ' : ' . $this->mycourse->id;
        $bloc .= html_writer::tag('div', $labelnumcourse, ['class' => 'teacher-label-courseid']);
        $bloc .= get_config($this->blockname, 'teacherhelp');
        return $bloc;
    }
    
    /**
     * Construit la ligne fonctionnalité ouvrir le cours pour la vue enseignante
     * @return string html
     */
    private function get_info_courseopen()
    {
        global $COURSE, $OUTPUT;
        
        $status = [ 0 => 'statusclosed', 1 => 'statusopen'];
        $actions = [0 => 'opencourse', 1 => 'closecourse'];
        $isvisible = $this->mycourse->visible;
        $label = html_writer::tag('span', get_string($status[$isvisible], $this->blockname),
            ['class' => 'teacher-open-label' . ' ' . $status[$isvisible]]);
        $action = '';
        
        $archiveyear = $this->get_course_year();
        $context = context_course::instance($COURSE->id);
        if (! $archiveyear || has_capability('block/course_opennow:openarchived', $context)) {
            $buttonname = get_string($actions[$isvisible], $this->blockname);
            $action = sprintf('<form action="%s" method="post">', new moodle_url('/blocks/up1_course_logistics/open.php'))
                 . sprintf('<input type="hidden" value="%d" name="courseid" />', $COURSE->id)
                 . sprintf('<input type="hidden" value="%s" name="sesskey" />', sesskey())
                 . sprintf('<input type="hidden" value="%d" name="visible" />', $isvisible)
                 . sprintf('<button type="submit" name="datenow" value="open">%s</button>', $buttonname)
                 .'</form>';
        }
        
        $opencourse = html_writer::tag('div', $label . $action, ['class' => 'teacher-open-bloc']);
        $infodate = $this->get_info_course_dates();
        return html_writer::tag('div', $opencourse . $infodate, ['class' => 'teacher-space-bloc']);
    }
    
    /**
     * renvoie l'année scolaire du cours courant s'il est archivé,
     * d'après la catégorie ancestrale, champ idnumber
     * @param array $dates
     * @return string or null
     */
    private function get_course_year()
    {
        if (up1_meta_get_text($this->page->course->id, 'up1datearchivage') == 0) {
            return null;
        }
        $cat = core_course_category::get($this->page->course->category);
        if (preg_match('@^\d:(\d{4}-\d{4})/@', $cat->idnumber, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }
    
    /**
     * return the input string followed by a newline (<br />) if not empty, or empty string otherwise.
     * @param type $text
     * @return string
     */
    private function br($text)
    {
        if ($text) {
            return $text . '<br />';
        } else {
            return '';
        }
    }
    
    public function applicable_formats()
    {
        return array('course' => true,
                     'all' => false);
    }
    
    public function hide_header()
    {
        return false;
    }
    
    public function instance_allow_multiple()
    {
        return false;
    }
}
