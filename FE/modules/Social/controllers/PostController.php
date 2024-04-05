<?php

class Social_PostController extends Zend_Controller_Action {

    var $tb_post = "tb_social_post";
    var $tb_social_like = "tb_social_like";
    var $tb_social_comment = "tb_social_comment";
    var $tb_file = "tb_social_files";

    public function addAction() {
        $customer = $_SESSION['cp__customer'];

        $content = $this->_request->getParam('content');
        $groupId = $this->_request->getParam('group_id');
        $isCheckUrl = $this->_request->getParam('isCheckUrl');
        $url = $this->_request->getParam('url');
        $files = $_FILES["files"];
        $target_dir = "files/social/" . $customer['ID'];
        $error = true;
        if ($content) {
            $error = false;
        }
        if ($isCheckUrl && $url) {
            $error = false;
        }


        if ($files) {
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $fNames = $files['name'];
            foreach ($fNames as &$f) {
                if (!$f) {
                    continue;
                }
                $target_file = $target_dir . '/' . basename($f);
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    $mes = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                    $error = true;
                }
                $f = $target_file;
            }
            unset($f);
        }


        if (!$error) {
            $group = $this
                    ->Model
                    ->getOne('tb_social_groups', "WHERE `ID` = '$groupId'");

            $data = [
                'content' => $content,
                'group_id' => $groupId ? $groupId : 0,
                'group_name' => $group ? $group['title'] : null,
                'type' => $groupId ? 'GROUP' : 'ALL',
                'url' => $isCheckUrl ? $url : null,
                'user_post' => $customer['ID'],
                'date_post' => date("Y-m-d H:i:s")
            ];
            $this->Model->insert($this->tb_post, $data);
            $post = $this
                    ->Model
                    ->getOne($this->tb_post, "WHERE `date_post` = '{$data['date_post']}' AND `user_post` = '{$customer['ID']}' ORDER BY `ID` DESC");
            $postId = $post['ID'];

            $dataFiles = [];
            if ($fNames) {
                foreach ($fNames as $k => $f) {
                    $dataFiles = [
                        'post_id' => $postId,
                        'user_post' => $customer['ID'],
                        'date_post' => date('Y-m-d H:i:s'),
                        'name' => $files['name'][$k],
                        'size' => $files['size'][$k],
                        'type' => $files['type'][$k],
                        'full_path' => $f
                    ];
                    move_uploaded_file($files["tmp_name"][$k], $f);
                    $this->Model->insert($this->tb_file, $dataFiles);
                }
            }
        }
        $this->view->error = $error;
        $this->view->error = $mes;
        if ($groupId) {
            $this->_redirect(BASE_URL . "/{$this->language}/social?group_id=" . $groupId);
        } else {
            $this->_redirect(BASE_URL . "/{$this->language}/social");
        }

        die("Post OK");
    }

    public function editAction() {
        $customer = $_SESSION['cp__customer'];

        $content = $this->_request->getParam('content');
        $postId = $this->_request->get('ID_POST');
        $isCheckUrl = $this->_request->getParam('isCheckUrl');
        $url = $this->_request->getParam('url');
        $error = true;
        if ($content) {
            $error = false;
        }

        $post = $this
                ->Model
                ->get($this->tb_post, "WHERE `ID` = '{$postId}' AND `user_post` = '{$customer['ID']}'");

        if (!$post) {
            $error = true;
        }


        if (!$error) {
            $data = [
                'content' => $content,
                'url' => $isCheckUrl ? $url : null,
                'user_edit' => $customer['ID'],
                'date_edit' => date("Y-m-d H:i:s")
            ];
            $this->Model->update($this->tb_post, $data, "`ID` = '{$postId}'");
        }
        $this->view->error = $error;
        $this->_redirect(BASE_URL . "/{$this->language}/social");
        die("Post OK");
    }

    public function addcommentAction() {
        $customer = $_SESSION['cp__customer'];

        $content = $this->_request->getParam('content');
        $error = true;
        if ($content) {
            $error = false;
        }

        $postId = $this->_request->getParam('id_content');

        if ($postId) {
            $error = false;
        }

        $post = $post = $this
                ->Model
                ->getOne($this->tb_post, "WHERE `ID` = '{$postId}'");

        if (!$post) {
            $error = true;
        }

        if (!$error) {
            $data = [
                'post_id' => $postId,
                'type' => 'CONTENT',
                'content' => $content,
                'user_post' => $customer['ID'],
                'date_post' => date("Y-m-d H:i:s")
            ];
            $this->Model->insert($this->tb_social_comment, $data);

            $this->Model->update($this->tb_post, [
                'num_like' => (double) $post['num_comment'] + 0
                    ], "`ID` = '{$postId}'");
        }
        $this->view->error = $error;
        $this->_redirect(BASE_URL . "/{$this->language}/social");
        die("Post OK");
    }

    public function editcommentAction() {
        $customer = $_SESSION['cp__customer'];

        $content = $this->_request->getParam('content');
        $error = true;
        if ($content) {
            $error = false;
        }

        $postId = $this->_request->get('ID_CC');
        $error = true;
        if ($content) {
            $error = false;
        }

        if ($postId) {
            $error = false;
        }

        $post = $this
                ->Model
                ->get($this->tb_social_comment, "WHERE `ID` = '{$postId}' AND `user_post` = '{$customer['ID']}'");

        if (!$post) {
            $error = true;
        }


        if (!$error) {
            $data = [
                'content' => $content,
                'user_edit' => $customer['ID'],
                'date_edit' => date("Y-m-d H:i:s")
            ];
            $this->Model->update($this->tb_social_comment, $data, "`ID` = '{$postId}'");
        }
        $this->view->error = $error;
        $this->_redirect(BASE_URL . "/{$this->language}/social");
        die("Post OK");
    }

    public function deleteAction() {
        $customer = $_SESSION['cp__customer'];
        $id = $this->Plugins->getNum("ID", "0");
        $post = $this->Model->getOne($this->tb_post, "WHERE ID = '$id' AND `user_post` = '{$customer['ID']}'");

        if ($post) {
            $this->Model->delete($this->tb_post, "ID = '$id'");
            $this->Model->delete($this->tb_social_like, "`post_id` = '$id'");
            $this->Model->delete($this->tb_social_comment, "`post_id` = '$id'");
        }

        $this->_redirect(BASE_URL . "/{$this->language}/social");
        die("");

//        if (isset($_REQUEST['ajax'])) {
//            $this->_redirect("{$this->view->controllerUrl}/cat/?ajax");
//        } else {
//            $this->_redirect(BASE_URL . "/{$this->language}/social");
//        }
    }

    public function deletecommentAction() {
        $customer = $_SESSION['cp__customer'];
        $id = $this->Plugins->getNum("ID_CC", "0");
        $comment = $this->Model->getOne($this->tb_social_comment, "WHERE `ID` = '$id' AND `user_post` = '{$customer['ID']}'");

        if ($comment) {
            $this->Model->delete($this->tb_social_comment, "`ID` = '$id'");
            $post = $this->Model->getOne($this->tb_post, "WHERE `ID` = '{$comment['post_id']}'");
            if ($post) {
                $this->Model->update($this->tb_post, [
                    'num_comment' => $post['num_comment'] > 1 ? $post['num_comment'] - 1 : 0
                        ], "ID = '{$comment['post_id']}'");
            }
        }

        $this->_redirect(BASE_URL . "/{$this->language}/social");
    }

    public function likeAction() {

        $customer = $_SESSION['cp__customer'];

        $postId = $this->_request->getParam('id');

        $error = true;
        if ($postId) {
            $error = false;
        }

        $post = $this
                ->Model
                ->getOne($this->tb_post, "WHERE `ID` = '{$postId}'");

        if (!$post) {
            $error = true;
        }
        if (!$error) {
            $check = $this
                    ->Model
                    ->getOne($this->tb_social_like, "WHERE `post_id` = '{$postId}' AND type = 'CONTENT'");
            if ($check) {
                $this->Model->delete($this->tb_social_like, "`ID` = '{$check['ID']}'");
                $this->Model->update($this->tb_post, [
                    'num_like' => $post['num_like'] - 1 > 0 ? $post['num_like'] - 1 : 0
                        ], "`ID` = '{$postId}'");
            } else {
                $data = [
                    'post_id' => $postId,
                    'type' => 'CONTENT',
                    'user_post' => $customer['ID'],
                    'date_post' => date("Y-m-d H:i:s")
                ];
                $this->Model->insert($this->tb_social_like, $data);
                $this->Model->update($this->tb_post, [
                    'num_like' => 1
                        ], "`ID` = '{$postId}'");
            }
        }
        $this->view->error = $error;
        //$this->_redirect(BASE_URL . "/{$this->language}/social");
        die(json_encode([
            'error' => $error
        ]));
    }

    public function likecommentAction() {

        $customer = $_SESSION['cp__customer'];

        $postId = $this->_request->getParam('id');

        $error = true;
        if ($postId) {
            $error = false;
        }

        $post = $this
                ->Model
                ->getOne($this->tb_social_comment, "WHERE `ID` = '{$postId}'");

        if (!$post) {
            $error = true;
        }
        if (!$error) {
            $check = $this
                    ->Model
                    ->getOne($this->tb_social_like, "WHERE `post_id` = '{$post['post_id']}' AND `sub_id` = '{$postId}' AND type = 'COMMENT'");
            if ($check) {
                $this->Model->delete($this->tb_social_like, "`ID` = '{$check['ID']}'");
                $this->Model->update($this->tb_social_comment, [
                    'num_like' => $post['num_like'] - 1 > 0 ? $post['num_like'] - 1 : 0
                        ], "`ID` = '{$postId}'");
            } else {
                $data = [
                    'post_id' => $post['post_id'],
                    'type' => 'COMMENT',
                    'sub_id' => $postId,
                    'user_post' => $customer['ID'],
                    'date_post' => date("Y-m-d H:i:s")
                ];
                $this->Model->insert($this->tb_social_like, $data);
                $this->Model->update($this->tb_social_comment, [
                    'num_like' => 1
                        ], "`ID` = '{$postId}'");
            }
        }
        $this->view->error = $error;
        //$this->_redirect(BASE_URL . "/{$this->language}/social");
        die(json_encode([
            'error' => $error
        ]));
    }

}
