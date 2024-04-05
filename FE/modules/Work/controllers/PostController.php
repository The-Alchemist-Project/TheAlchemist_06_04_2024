<?php

class Work_PostController extends Zend_Controller_Action {

    var $tb_post = "customer";

    public function changeAction() {

        $customer = $_SESSION['cp__customer'];

        $img = $_FILES["img"];
        $target_dir = "files/customer/" . $customer['ID'];
        $target_file = $target_dir . '/' . basename($img["name"]);
        $fileOld = $target_dir . '/' . basename($customer["img"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $mes = "";

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Check if image file is a actual image or fake image
        if (isset($_POST["submit"])) {
            $check = getimagesize($img["tmp_name"]);
            if ($check !== false) {
                $mes = "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                $mes = "File is not an image.";
                $uploadOk = 0;
            }
        }

        // Check if file already exists
        if (file_exists($fileOld)) {
            @unlink($fileOld);
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $mes = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $mes = "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($img["tmp_name"], $target_file)) {
                $mes = "The file " . htmlspecialchars(basename($img["name"])) . " has been uploaded.";
                $this->Model->update($this->tb_post, array('img' => $img['name']), "ID='{$customer['ID']}'");
                $_SESSION['cp__customer']['img'] = $img["name"];
                $this->_redirect(BASE_URL . "/{$this->language}/user");
            } else {
                $mes = "Sorry, there was an error uploading your file.";
            }
        }


        die($mes);
    }

    public function editinfoAction() {
        $customer = $_SESSION['cp__customer'];
        if ($this->isPost) {
            $name = $this->Plugins->get("fullname", "", false);
            $phone = $this->Plugins->get("phone", "", false);
            $email = $this->Plugins->get("email", "", false);

            $error = [];
            if (!$name) {
                $error['name'] = '+ Fullname is required';
            }
            if (!$phone) {
                $error['phone'] = '+ Phone number is required';
            }

            $check = $this->Model->get($this->tb_post, "WHERE (`email`='{$email}' OR `phone`='{$phone}') AND `ID`<>'{$customer['ID']}'");

            if ($check) {
                $error['type'] = '+ Email or Phone, Username is exist!';
            }


            if ($error) {
                die(json_encode([
                    'error' => true,
                    'msg' => implode(',<br>', $error)
                ]));
            }

            if (!$error) {
                $this->Model->update($this->tb_post, array(
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $email,
                    'date' => date("Y-m-d H:i:s")
                        ), "`ID`='{$customer['ID']}'");
                die(json_encode([
                    'error' => false,
                    'msg' => 'Congratulations on your successful updated'
                ]));
            }
        } else {
            unset($_SESSION['cp__customer']);
        }
    }

}
