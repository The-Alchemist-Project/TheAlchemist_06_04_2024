<?php

class Social_IndexController extends Zend_Controller_Action {

    var $tb_post = "tb_social_post";
    var $tb_social_like = "tb_social_like";
    var $tb_social_comment = "tb_social_comment";
    var $tb_file = "tb_social_files";

    public function init() {
        $type = $this->_request->getParam('type', 'all');
        $this->view->type = $type;
        if (!$_SESSION['cp__customer'])
            $this->_redirect(BASE_URL . "/{$this->language}/login");
    }

    public function indexAction() {

    }

    public function headerAction() {
        $dateSearch = date('Y-m-d', strtotime(date("Y-m-d") . ' -5 day'));

        $tbPosts = $this
                ->Model
                ->queryAll("SELECT `a`.`id`,`a`.`coin_ids`,`a`.`customer_id`,`a`.`view_id`
                FROM `tb_product_post` as `a`
                WHERE `a`. `ID` >0 AND `customer_id` IS NULL");
        $whereStandar = ["`a`.`date` >= '{$dateSearch}'"];
        if ($tbPosts) {
            $whereIds = [];
            foreach ($tbPosts as $a) {
                $whereIds[] = "FIND_IN_SET('{$a['id']}',`a`.`type_ids`)";
            }
            if ($whereIds) {
                $whereStandar[] = "(" . implode(' OR ', $whereIds) . ")";
            }
        }
        $this->view->postsymbols = $this
                ->Model
                ->queryAll("SELECT `a`.*,`a`.`id` as `ID`
                FROM `stock_price` as `a`
                WHERE `a`.`rsi` > 0
                AND  `a`.`type_ids` IS NOT NULL
                AND " . implode(' AND ', $whereStandar) . "
            ORDER BY `date` DESC LIMIT 100");
    }

    public function contentAction() {
        $posts = $this
                ->Model
                ->queryAll("SELECT *
                FROM `tb_social_post`
                ORDER BY `date_post` DESC LIMIT 100");

        $this->view->posts = $posts;
    }

    public function mobileAction() {
        $posts = $this
                ->Model
                ->queryAll("SELECT *
                FROM `tb_social_post`
                ORDER BY `date_post` DESC LIMIT 100");

        $this->view->posts = $posts;
    }

    public function topAction() {
        $groupId = $this->_request->get('group_id');
        $this->view->groupId = $groupId;
    }

    public function leftAction() {

    }

    public function moreAction() {

    }

    public function itemsAction() {
        $customer = $_SESSION['cp__customer'];
        $type = $this->_request->getParam('type', 'all');

        $groupId = $this->_request->get('group_id');

        if ($groupId) {
            $posts = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`b`.`name` as `customer_name`,`b`.`img`
                FROM `tb_social_post` as `a`
                LEFT JOIN `customer` as `b`
                ON `a`.`user_post`=`b`.`ID`
                LEFT JOIN `tb_social_groups` as `c`
                    ON `a`.`group_id`=`c`.`ID`
                WHERE `type` = 'GROUP'
                AND `group_id`='{$groupId}'
                AND (
                    FIND_IN_SET('{$customer['ID']}',`c`.`member_ids`)
                        OR `c`.`user_post`='{$customer['ID']}'
                    OR `c`.`user_edit`='{$customer['ID']}'
                    )
                ORDER BY `a`.`date_post` DESC LIMIT 100");
        } elseif ($type == 'group') {
            $posts = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`b`.`name` as `customer_name`,`b`.`img`
                FROM `tb_social_post` as `a`
                LEFT JOIN `customer` as `b`
                ON `a`.`user_post`=`b`.`ID`
                WHERE `type` = 'GROUP'
                ORDER BY `a`.`date_post` DESC LIMIT 100");
        } else {
            $posts = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`b`.`name` as `customer_name`,`b`.`img`
                FROM `tb_social_post` as `a`
                LEFT JOIN `customer` as `b`
                ON `a`.`user_post`=`b`.`ID`
                ORDER BY `a`.`date_post` DESC LIMIT 100");
        }


        if ($posts) {
            $arrPosts = [];
            foreach ($posts as $a) {
                $a['comments'] = [];
                $a['liked'] = false;
                $arrPosts[$a['ID']] = $a;
            }
            $comments = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`b`.`name` as `customer_name`,`b`.`img`
                    FROM `tb_social_comment` as `a`
                        LEFT JOIN `customer` as `b`ON `a`.`user_post`=`b`.`ID`
                        WHERE `post_id` IN (" . implode(',', array_keys($arrPosts)) . ")
                        ORDER BY `a`.`date_post`");

            $arrComments = [];
            if ($comments) {
                foreach ($comments as $a) {
                    $a['liked'] = false;
                    $arrComments[$a['ID']] = $a;
                }
            }

            //Check like
            $likes = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`b`.`name` as `customer_name`,`b`.`img`
                    FROM `tb_social_like` as `a`
                        LEFT JOIN `customer` as `b`ON `a`.`user_post`=`b`.`ID`
                        WHERE `post_id` IN (" . implode(',', array_keys($arrPosts)) . ")
                        ORDER BY `a`.`date_post`");

            if ($likes) {
                foreach ($likes as $a) {
                    if ($a['type'] == 'CONTENT') {
                        $arrPosts[$a['post_id']]['liked'] = true;
                    } elseif ($a['type'] == 'COMMENT') {
                        $arrComments[$a['sub_id']]['liked'] = true;
                    }
                }
            }

            if ($arrComments) {
                foreach ($arrComments as $a) {
                    $arrPosts[$a['post_id']]['comments'][] = $a;
                }
            }


            $files = $this
                    ->Model
                    ->queryAll("SELECT `a`.*
                    FROM `tb_social_files` as `a`
                        LEFT JOIN `customer` as `b` ON `a`.`user_post`=`b`.`ID`
                        WHERE `a`.`post_id` IN (" . implode(',', array_keys($arrPosts)) . ")
                        ORDER BY `a`.`date_post`");

            foreach ($files as $a) {
                $arrPosts[$a['post_id']]['files'][] = $a;
            }

            $posts = array_values($arrPosts);
        }

        $this->view->posts = $posts;
        $this->view->type = $type;
    }

}
