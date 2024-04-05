<?php

class Register_IndexController extends Zend_Controller_Action {

    var $tb_post = "customer";
    var $tb_cat = "customer_type";
    var $tb_config = "customer_config";

    public function init() {

    }

    public function indexAction() {
        if ($this->isPost) {
            $name = $this->Plugins->get("name", "", false);
            $username = $this->Plugins->get("username", "", false);
            $password = $this->Plugins->get("password", "", false);
            $repassword = $this->Plugins->get("re-password", "", false);
            $phone = $this->Plugins->get("phone", "", false);
            $email = $this->Plugins->get("email", "", false);
            $refer = $this->Plugins->get("refer", "", false);
            $company = $this->Plugins->get("company", "", false);
            $position = $this->Plugins->get("position", "", false);
            $industry = $this->Plugins->get("industry", "", false);
            $education = $this->Plugins->get("education", "", false);
            $country = $this->Plugins->get("country", "", false);
            $city = $this->Plugins->get("city", "", false);
            $desc = $this->Plugins->get("desc", "", false);

            // Added by CBT - today
            if (!empty($email) && empty($username)) {
                // Create unique username based on email
                // remove all illegal characters from email
                $username = explode("@", $email)[0];
                $username = preg_replace("/[^a-zA-Z0-9]+/", "", $username);

                $userCount = $this->Model->getTotal($this->tb_post, "WHERE user = '" . $username . "'");
                if ($userCount > 0) {
                    $username .= $userCount;
                }
            }

            $error = [];
            if (!$name) {
                $error['name'] = '+ Fullname is required';
            }
            if (!$username) {
                $error['username'] = '+ Username is required';
            }
            /*if (!$phone) {
                $error['phone'] = '+ Phone number is required';
            }*/
            if (!$password) {
                $error['password'] = '+ Password is required';
            }
            if ($password != $repassword) {
                $error['re-password'] = '+ password  not true';
            }

//            if (!$is_accept) {
//                $error['is_accept'] = '+ Bạn phải chọn đồng ý';
//            }


            $query = "WHERE `user`='{$username}' OR `email`='{$email}'";
            if ($phone) {
                $query .= " OR `phone`='{$phone}'";
            }

            $check = $this->Model->get($this->tb_post, $query);

            if ($check) {
                $error['type'] = 'Your account has been registered';
            }


            if ($error) {
                // Added by CBT - 28/11/2022
                if ($this->isAjax) {
                    http_response_code(400);
                    header('Content-Type: application/json');

                    echo json_encode([
                        'status' => 'error',
                        'message' => array_values($error)[0],
                        'errors' => $error,
                    ]);

                    exit();
                }

                $this->view->error = $error;
            }

            if (!$error) {
                $this->Model->insert($this->tb_post, array(
                    'user' => $username,
                    'name' => $name,
                    'password' => md5($password),
                    'phone' => $phone,
                    'email' => $email,
                    'refer' => $refer,
                    'company' => $company,
                    'position' => $position,
                    'industry' => $industry,
                    'education' => $education,
                    'country' => $country,
                    'desc' => $desc,
                    'city' => $city,
                    'status' => '1',
                    'date' => date("Y-m-d H:i:s")
                ));

                // Added by CBT - 28/11/2022
                if ($this->isAjax) {
                    http_response_code(200);
                    header('Content-Type: application/json');

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Congratulations on your successful registration. Please wait for confirmation from the supplier',
                    ]);

                    exit();
                }

                $this->view->success = 'Congratulations on your successful registration. Please wait for confirmation from the supplier';
            }
        } else {
            // unset($_SESSION['cp__customer']);
        }
    }

}
