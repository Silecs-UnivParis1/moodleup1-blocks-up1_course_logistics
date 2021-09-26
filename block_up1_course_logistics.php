<?php
/**
 * @package    block_up1_course_logistics
 * @copyright  2021 Silecs & Université Paris1 Panthéon-Sorbonne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class block_up1_course_logistics extends block_base {
	
	public $blockname = null;
	
	public $courseupdate = false;
	
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
		global $COURSE;
		
		$title = 'titlestudent';
		$context = context_course::instance($COURSE->id);
		if (has_capability('moodle/course:update', $context)) {
			$this->courseupdate = true;
			$title = 'titleteacher';
		}
		$this->blockname = get_class($this);
		$this->title = get_string($title, $this->blockname);
	}
	
	public function get_content() {
		 if ($this->content !== null) {
		  return $this->content;
		}

		$this->content =  new stdClass;
		
		$format = course_get_format($this->page->course);
        $this->mycourse = $format->get_course();
        
        $info_date = $this->get_info_course_dates();
		
		if (!$this->courseupdate) {
			$this->content->text = 'En cours de construction';
		} else {
			$infos = $this->get_info_composante();
			$infos .= $this->get_course_teachers_list();
			$labelnumcourse = get_string('numcours', $this->blockname) . ' : ' . $this->mycourse->id;
			$infos .= html_writer::tag('div', $labelnumcourse, ['class' => 'student-label-courseid']);
			$infos .= $this->get_info_course_dates();	
			$infos .= $this->get_course_register_list();	
			$this->content->text = $infos;
			
           // . $this->br($dispdate .' '.  $courseformatter->format_icons($course, 'icons'));
		}
	 
		return $this->content;
		
		
	}
	
	private function get_info_composante() {
        $info = html_writer::tag('div', get_string('composante', $this->blockname), ['class' => 'student-label-composante'])
            . $this->br(up1_meta_get_list($this->mycourse->id, 'up1composante', false, ' / ', false))
            . $this->br(up1_meta_get_list($this->mycourse->id, 'up1mention', false, ' / ', true))
            . $this->br(up1_meta_get_list($this->mycourse->id, 'up1niveau', false, ' / ', true));
        return html_writer::tag('div', $info, ['class' => 'student-info-composante']);
	}
	
	private function get_info_course_dates() {
		$startdate = usergetdate($this->mycourse->startdate);
        $enddate = usergetdate($this->mycourse->enddate);
		$info = get_string('debut', $this->blockname) . ' : ' . $startdate['mday'].'/'.$startdate['mon'].'/'.$startdate['year']
			. ' - ' . get_string('fin', $this->blockname) . ' : ' . $enddate['mday'].'/'.$enddate['mon'].'/'.$enddate['year'];
		$classname = $this->courseupdate ? 'teacher-info-date' : 'student-info-date';
		return html_writer::tag('div', $info, ['class' => $classname]);	
	}
	
	private function get_course_teachers_list() {
		$info = html_writer::tag('div',get_string('enseignants', $this->blockname), ['class' => 'student-label-teachers']);
		$courseformatter = new courselist_format('list');
		$info .= $courseformatter->format_teachers($this->mycourse, 'student-list-teachers', 3);
		return html_writer::tag('div', $info, ['class' => 'student-info-teachers']);
	}
	
	private function get_course_register_list() {
		$info = html_writer::tag('span', get_string('acces', $this->blockname) . ' : ', ['class' => 'student-label-acces']);
		$courseformatter = new courselist_format('list');
		$info .= $courseformatter->format_icons($this->mycourse, 'icons');
		return html_writer::tag('div', $info, ['class' => 'student-info-acces']);
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
