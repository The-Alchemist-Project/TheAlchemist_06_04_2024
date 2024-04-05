<?php
class Admin_ContactController  extends Zend_Controller_Action {
	var $tb_post =	"tb_contact";
	public function init(){}

	public function indexAction(){
		$data = $this->Model->getOne( $this->tb_post,"WHERE lang='{$_SESSION["cp_lang"]}'");
		if( !$data ){
			$data = array('lang'=>$_SESSION["cp_lang"]);
			$this->Model->insert( $this->tb_post, $data );
		}
		$this->view->info = $data;
		if( $this->isPost ){
			$terms = array(
				"contact_vn",
				"contact_en",
				"footer_vn",
				"footer_en",
				"office_vn",
				"office_en",
				"link",
				"text1",
				"text2",
				"email",
				"yh01",
				"yh02",
				"yh03",
				"hl01",
				"hl02",
				"hl03",
				"skype",
				"titleleft",
				"titleright");
			foreach( $terms as $k ){
			   	if($k == "link" || $k == "text2" || $k == "text1" || $k == "contact_vn"|| $k == "contact_en"|| $k == "footer_vn"|| $k == "footer_en"|| $k == "office_vn"|| $k == "office_en")
					$data[$k] = $this->Plugins->getEditor($k);
				else
					$data[$k] = $this->Plugins->get($k);
			}

			if( !$this->Plugins->isEmail( $data['email'] )){
				$this->view->message = "NOACCEPT_EMAIL";
			}else{
				$this->Model->update($this->tb_post,$data,"lang='{$_SESSION["cp_lang"]}'");
			}
			$this->view->info = $data;
		}
	}

}