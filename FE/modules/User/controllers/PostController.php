<?php

class User_PostController extends Zend_Controller_Action {

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
            $username = $this->Plugins->get("username", "", false);
            $phone = $this->Plugins->get("phone", "", false);
            $email = $this->Plugins->get("email", "", false);
            $desc = $this->Plugins->get("desc", "", false);
            $refer = $this->Plugins->get("refer", "", false);
            $company = $this->Plugins->get("company", "", false);
            $position = $this->Plugins->get("position", "", false);
            $industry = $this->Plugins->get("industry", "", false);
            $education = $this->Plugins->get("education", "", false);
            $country = $this->Plugins->get("country", "", false);
            $city = $this->Plugins->get("city", "", false);

            $error = [];
            if (!$name) {
                $error['name'] = '+ Fullname is required';
            }
            if (!$username) {
                $error['username'] = '+ Username is required';
            }
            if (!$phone) {
                $error['phone'] = '+ Phone number is required';
            }

            $check = $this->Model->get($this->tb_post, "WHERE (`email`='{$email}' OR `phone`='{$phone}' OR `user`='{$username}') AND `ID`<>'{$customer['ID']}'");

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
                $data = array(
                    'name' => $name,
                    'user' => $username,
                    'phone' => $phone,
                    'email' => $email,
                    'desc' => $desc,
                    'refer' => $refer,
                    'company' => $company,
                    'position' => $position,
                    'industry' => $industry,
                    'education' => $education,
                    'country' => $country,
                    'date' => date("Y-m-d H:i:s")
                );
                $this->Model->update($this->tb_post, $data, "`ID`='{$customer['ID']}'");

                $customer = array_merge($customer, $data);

                $_SESSION['cp__customer'] = $customer;
                die(json_encode([
                    'error' => false,
                    'msg' => 'Congratulations on your successful updated'
                ]));
            }
        } else {
            unset($_SESSION['cp__customer']);
        }
    }

    public function updateprofileAction()
    {
        $customer = $_SESSION['cp__customer'];

        $username = $this->Plugins->get("user", "", false);
        $name = $this->Plugins->get("name", "", false);
        $phone = $this->Plugins->get("phone", "", false);
        $email = $this->Plugins->get("email", "", false);
        $address = $this->Plugins->get("address", "", false);

        $check = $this->Model->get(
            $this->tb_post,
            "WHERE (`email`='{$email}' OR `user`='{$username}') AND `ID`<>'{$customer['ID']}'"
        );

        if ($check) {
            $error['type'] = 'Email or Username is exist!';
        }

        if ($error) {
            http_response_code(401);
            header('Content-Type: application/json');

            echo json_encode([
                'status' => 'error',
                'message' => implode(',<br>', $error),
            ]);

            exit;
        }

        $data = [
            'name' => $name,
            'user' => $username,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'date' => date("Y-m-d H:i:s"),
        ];

        $this->Model->update($this->tb_post, $data, "`ID`='{$customer['ID']}'");

        $customer = array_merge($customer, $data);
        unset($customer['password']);

        $_SESSION['cp__customer'] = $customer;
    }
}
