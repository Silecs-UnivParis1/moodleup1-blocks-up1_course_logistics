<?php
/**
 * @package    block_up1_course_logistics
 * @copyright  2021 Silecs & Université Paris1 Panthéon-Sorbonne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class block_up1_course_logistics extends block_base {
	
	public $blockname = null;
	
	public $courseupdate = false;
	
	public $hasstudentrole = false;
	
	public $mycourse = null;
	
	/**
     * Does the block have a global settings.
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }
	
	public function init() {
	global $COURSE, $USER;
		
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
	
	public function get_content() {
		 if ($this->content !== null) {
			global $PAGE;
			$PAGE->requires->js('/blocks/up1_course_logistics/javascript/up1courselogistics.js');
			return $this->content;
		}

		$this->content =  new stdClass;
		
		$format = course_get_format($this->page->course);
        $this->mycourse = $format->get_course();
        
        $info_date = $this->get_info_course_dates();
		
		if ($this->courseupdate) {
			global $OUTPUT, $COURSE;
			$iconeslink = $OUTPUT->pix_icon('t/expanded', '', 'moodle', ['class' => 'hidden']) . $OUTPUT->pix_icon('t/collapsed', '', 'moodle');
			
			$opencourse = html_writer::tag('div', get_string('statusopen', $this->blockname), ['class' => 'teacher-open-bloc']);
			$infodate = $this->get_info_course_dates();
			$infos = html_writer::tag('div', $opencourse . $infodate, ['class' => 'teacher-space-bloc']);			
			$inscrits = $this->get_info_registered();		
			$teacherlist =  $this->get_course_teachers_list_active($iconeslink);
			$manageenrol = $this->get_list_manage_enrol($iconeslink);
			$infos .= html_writer::tag('div', $inscrits . $teacherlist . $manageenrol, ['class' => 'teacher-enrol-bloc']);
			$this->content->text = $infos;
			
		} elseif ($this->hasstudentrole) {
			$infos = $this->get_info_composante();
			$infos .= $this->get_course_teachers_list();
			$labelnumcourse = get_string('numcours', $this->blockname) . ' : ' . $this->mycourse->id;
			$infos .= html_writer::tag('div', $labelnumcourse, ['class' => 'student-label-courseid']);
			$infos .= $this->get_info_course_dates();	
			$infos .= $this->get_course_register_list();
			$infos .= get_config($this->blockname,'studenthelp');
			$this->content->text = $infos;
		}
		return $this->content;
	}
	
	/**
	 * Construit pour le bloc version étudiante
	 * Utilise une fonction de local_up1_metadata
	 * @return string html
	 */
	private function get_info_composante() {
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
	private function get_info_course_dates() {
		global $OUTPUT;
		$startdate = usergetdate($this->mycourse->startdate);
        $enddate = usergetdate($this->mycourse->enddate);
		$info = get_string('debut', $this->blockname) . ' : ' . $startdate['mday'].'/'.$startdate['mon'].'/'.$startdate['year']
			. ' - ' . get_string('fin', $this->blockname) . ' : ' . $enddate['mday'].'/'.$enddate['mon'].'/'.$enddate['year'];
		$classname = $this->courseupdate ? 'teacher-info-date' : 'student-info-date';
		$action = '';
		if ($classname == 'teacher-info-date') {
			$url = new moodle_url('/local/crswizard/update/index.php', ['id' => $this->mycourse->id, 'direct' => 'index']);
			$action = html_writer::link($url, $OUTPUT->pix_icon('t/edit', '', 'moodle'));
		}
		return html_writer::tag('div', $info . $action, ['class' => $classname]);	
	}
	
	/**
	 * Renvoie la liste des enseignants du cours en format html.
	 * Utilisé dans la version étudiante du bloc
	 * @return string
	 */
	private function get_course_teachers_list() {
		$info = html_writer::tag('div',get_string('enseignants', $this->blockname), ['class' => 'student-label-teachers']);
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
	private function get_course_teachers_list_active($iconeslink) {
		$info = html_writer::tag('div', $iconeslink . get_string('listteachers', $this->blockname), 
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
	private function get_course_register_list() {
		$info = html_writer::tag('span', get_string('acces', $this->blockname) . ' : ', ['class' => 'student-label-acces']);
		$courseformatter = new courselist_format('list');
		$info .= $courseformatter->format_icons($this->mycourse, 'icons');
		return html_writer::tag('div', $info, ['class' => 'student-info-acces']);
	}
	
	/**
	 * Construit la liste dynamique des actions permettant de gérer les inscriptions
	 * Utilisé dans la version enseignante du bloc
	 * @param string $iconeslink html
	 * @return string HTML
	 */
	private function get_list_manage_enrol($iconeslink) {
		global $OUTPUT;
		$infos = html_writer::tag('div', $iconeslink . get_string('manual:manage', 'enrol_manual'), 
			['class' => 'teacher-label-manage', 'id' => 'teacher-label-manage', 'onclick' => 'togglecollapseall("teacher-label-manage");']
		);
		$urlcohort = new moodle_url('/local/crswizard/update/index.php', ['id' => $this->mycourse->id, 'direct' => 'cohort']);
		$items = html_writer::tag('li', html_writer::link($urlcohort, get_string('enrolstudents', $this->blockname)));
		$items .=html_writer::tag('li', html_writer::link('http//google.com', get_string('findapogee', $this->blockname)));
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
	private function get_all_teachers() {
		global $DB, $COURSE;
		$allteachers = [];
		$context = context_course::instance($COURSE->id);
		$roles = ['responsable_epi', 'editingteacher', 'teacher'];
		foreach ($roles as $sortname) {
			$role = $DB->get_record('role', ['shortname'=> $sortname]);
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
	private function get_info_registered() {
		global $DB, $COURSE;
		$context = context_course::instance($COURSE->id);
		$linkinscrits = html_writer::link(new moodle_url('/user/index.php', ['id' => $COURSE->id]), 'inscrits');
		$nbinscrits = $DB->count_records_sql("SELECT COUNT(DISTINCT userid) FROM {role_assignments} WHERE contextid = ?", [$context->id]);
		$inscrits = html_writer::tag('div', "Nombre d'" . $linkinscrits . ' : ' . $nbinscrits, ['class' => 'teacher-label-nbinscrits']);
		return $inscrits;
	}
	
	/**
     * return the input string followed by a newline (<br />) if not empty, or empty string otherwise.
     * @param type $text
     * @return string
     */
    private function br($text) {
        if ($text) {
            return $text . '<br />';
        } else {
            return '';
        }
    }
	
	function applicable_formats() {
        return array('course' => true,
                     'all' => false);
    }
    
    function hide_header() {
        return false;
    }
    
    public function instance_allow_multiple() {
        return false;
    }
}
