<?php
class Home_IndexController  extends Zend_Controller_Action {
var $tb_user		=	"tb_user";
	var $tb_visit 		= "tb_visit_count";	
	var $tb_news 		= "tb_news_post";
	var $tb_school 		= "tb_school_post";
	var $tb_specialize 	= "tb_specialize_post";
	var $tb_care 		= "tb_care_post";
	var $tb_pupil 		= "tb_pupil_post";
	var $tb_parent 		= "tb_parent_post";
	var $tb_admission 	= "tb_admission_post";
	
	public function init(){
		$query ="
			SELECT CONCAT('School/?ID=',ID) as link,ID, title, date,quote,CONCAT('school/',img) as imag FROM {$this->tb_school} as a
			UNION
			SELECT CONCAT('News/?ID=',ID) as link,ID, title, date,quote,CONCAT('news/',img) as imag FROM {$this->tb_news} as b 
			UNION
			SELECT CONCAT('Specialize/?ID=',ID) as link,ID, title, date,quote,CONCAT('specialize/',img) as imag FROM {$this->tb_specialize} as c 
			UNION
			SELECT CONCAT('Care/?ID=',ID) as link,ID, title, date,quote,CONCAT('care/',img) as imag FROM {$this->tb_care} as d 
			UNION
			SELECT CONCAT('Admission/?ID=',ID) as link,ID, title, date,quote,CONCAT('admission/',img) as imag FROM {$this->tb_admission} as f
		";
		$this->view->vanban  = $this->Model->queryAll($query." ORDER BY date desc  LIMIT 0,10");
	
	}
	public function indexAction(){
	$time = $this->Plugins->time();
	$this->view->school= $this
			->Model
			->get( "tb_school_post","WHERE lang='{$this->language}' AND status = 1 ORDER BY date DESC LIMIT 0,4");
	$this->view->news= $this
			->Model
			->get( "tb_news_post","WHERE lang='{$this->language}' AND status = 1 ORDER BY date DESC LIMIT 3");
	$this->view->specialize= $this
			->Model
			->get( "tb_specialize_post","WHERE lang='{$this->language}' AND status = 1 ORDER BY date DESC LIMIT 3");
	$this->view->care= $this
			->Model
			->get( "tb_care_post","WHERE lang='{$this->language}' AND status = 1 ORDER BY date DESC LIMIT 4");
	$this->view->pupil= $this
			->Model
			->get( "tb_pupil_post","WHERE lang='{$this->language}' AND status = 1 ORDER BY date DESC LIMIT 4");
	$this->view->parent= $this
			->Model
			->get( "tb_parent_post","WHERE lang='{$this->language}' AND status = 1 ORDER BY date DESC LIMIT 4");
	$this->view->forte= $this
			->Model
			->get( "tb_forte_post","WHERE lang='{$this->language}' AND status = 1 ORDER BY date DESC LIMIT 4");
	$this->view->union= $this
			->Model
			->get( "tb_union_post","WHERE lang='{$this->language}' AND status = 1 ORDER BY date DESC LIMIT 4");
	$this->view->mission= $this
			->Model
			->get( "tb_mission_post","WHERE lang='{$this->language}' AND status = 1 ORDER BY date DESC LIMIT 4");
	$this->view->bestface= $this
			->Model
			->get( "tb_bestface_post","WHERE lang='{$this->language}' AND status = 1 ORDER BY date DESC LIMIT 4");		
  }
}
	
?>