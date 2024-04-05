<?php
class Contact_IndexController  extends Zend_Controller_Action {
	public function init(){
	}

	public function indexAction(){


		$info = $this->Model->getOne("tb_contact");
		$this->view->info = $info;

		if( !$info['email'] ){
			$this->view->error = "Chưa có thông tin Email liên hệ.";
			return true;
		}

		$email 		= $this->Plugins->get("email","");
		$name  		= $this->Plugins->get("name","");
		$tel  		= $this->Plugins->get("tel","");
		$address  	= $this->Plugins->get("address","");
		$content 	= $this->Plugins->get("content","");
		if( $this->isPost ){
			if( !$this->Plugins->isEmail( $email ) ){
				$this->view->error = "Bạn nhập email chưa đúng";
				$error = true;
			}
			if( $name === "" ){
				$this->view->error = "Bạn phải nhập tên";
				$error = true;
			}
			if( $content === "" ){
				$this->view->error = "Bạn phải nhập nội dung";
				$error = true;
			}
			if( $address === "" ){
				$this->view->error = "Bạn phải nhập tiêu đề";
				$error = true;
			}
			if( $error == true ){
				$this->view->email = $email;
				$this->view->name = $name;
				$this->view->tel = $tel;
				$this->view->address = $address;
				$this->view->content = $content;
				return true;
			}
			//var_dump("['CVA']\n<b>Người gửi:</b> $name\n<b>Số điện thoại:</b> $tel\n\n<b>Nội dung:</b> \n$content\n['CVA']");
			//exit;
			$this->Plugins->sendMail(array(
				subject	=> '['.$info['company'].'] Liên hệ - $address',
				from	=>	$email,
				to		=>	$info['email'],
				content	=>	"['CVA'] \n<b>Người gửi:</b> $name\n<b>Số điện thoại:</b> $tel\n\n<b>Nội dung:</b> \n$content\n ['CVA']"
			));

			$this->view->error = "Thư của bạn đã được gửi. Cảm ơn bạn.";
		}

	}

}
