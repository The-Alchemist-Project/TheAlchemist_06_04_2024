<?php

class Sub_IndexController extends Zend_Controller_Action {

    var $tb_user = "tb_user";
    var $tb_visit = "tb_visit_count";
    var $tb_counter = "tb_counter";
    var $tb_online = "tb_online";

    public function init() {
        $moduleTitle = $this->_request->getParam('moduleTitle');
        $this->view->moduleTitle = $moduleTitle;
        if (isset($_SESSION['online'])) {
            $test = $this->Model->queryOne("SELECT * FROM tb_stats WHERE ip='" . $_SERVER["REMOTE_ADDR"] . "' AND date > '" . date("Y-m-d H:i:s", time() - 30 * 60) . "'");
            if (!$test) {
                $this->Model->insert("tb_stats", array(
                    'ip' => $_SERVER["REMOTE_ADDR"],
                    'date' => date('Y-m-d H:i:s'),
                    'browser' => $_SERVER["HTTP_USER_AGENT"]
                ));
                $_SESSION['online'] = 'yes';
            }
        } else {
            $this->Model->insert("tb_stats", array(
                'ip' => $_SERVER["REMOTE_ADDR"],
                'date' => date('Y-m-d H:i:s'),
                'browser' => $_SERVER["HTTP_USER_AGENT"]
            ));
            $_SESSION['online'] = 'yes';
            $_SESSION[$_SERVER["REMOTE_ADDR"]] = $_SERVER["REMOTE_ADDR"];
        }
        if ($_SERVER["HTTP_USER_AGENT"] == 'TurnitinBot/2.1 (http://www.turnitin.com/robot/crawlerinfo.html)' || $_SERVER["REMOTE_ADDR"] == '38.111.147.83') {
            $this->_redirect("https://www.google.com.vn/");
        }
        if ($_SERVER["HTTP_USER_AGENT"] == 'coccoc/1.0 (http://help.coccoc.com/)' || $_SERVER["REMOTE_ADDR"] == '112.78.5.161') {
            $this->_redirect("https://www.google.com.vn/");
        }
        if ($_SERVER["HTTP_USER_AGENT"] == 'Mozilla/5.0 (compatible; AhrefsBot/4.0; +http://ahrefs.com/robot/)' || $_SERVER["REMOTE_ADDR"] == '173.199.120.67') {
            $this->_redirect("https://www.google.com.vn/");
        }
        if ($_SERVER["REMOTE_ADDR"] == '176.9.16.210' || $_SERVER["REMOTE_ADDR"] == '74.111.23.38' || $_SERVER["REMOTE_ADDR"] == '208.115.113.82') {
            $this->_redirect("https://www.google.com.vn/");
        }
        if ($_SERVER["REMOTE_ADDR"] == '91.121.24.97' || $_SERVER["REMOTE_ADDR"] == '173.199.120.99' || $_SERVER["REMOTE_ADDR"] == '66.249.77.47' || $_SERVER["REMOTE_ADDR"] == '65.55.52.88') {
            $this->_redirect("https://www.google.com.vn/");
        }
        if ($_SERVER["REMOTE_ADDR"] == '173.199.116.91') {
            $this->_redirect("https://www.google.com.vn/");
        }
    }

    public function indexAction() {
        $time = $this->Plugins->time();
    }

    public function shareAction() {

    }

    public function quickmenuAction() {

    }

    public function newsAction() {
        $this->view->news = $this
                ->Model
                ->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`,
                        IF('{$this->language}'='vn',`a`.`quote_vn`,`a`.`quote_en`) as `quote`,
                        IF('{$this->language}'='vn',`a`.`content_vn`,`a`.`content_en`) as `content`
                    FROM `tb_news_post` as `a`
                    WHERE  `a`.`ID`<>'0' AND `a`.`hot`<>'0' ORDER BY `a`.`date` DESC  LIMIT 5");
    }

    public function subshareAction() {
        $info = $this->Model->getOne("tb_contact", "WHERE `lang`='vn'");
        $this->view->info = $info;
    }

    public function headerAction() {
        $this->view->moduleTitle1 = $this->_request->getParam('moduleTitle');
        $meta['desc'] = $this->_request->getParam('meta_desc');
        $meta['key'] = $this->_request->getParam('meta_key');
        $this->view->meta = $meta;

        $this->view->about = $this
                ->Model
                ->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`
                    FROM `tb_about_post` as `a`
                    WHERE `parent_id`='0' ORDER BY `a`.`ord`");
        $this->view->bussiness = $this
                ->Model
                ->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`
                    FROM `tb_bussiness_cat` as `a`
                    ORDER BY `a`.`ord`");
        $this->view->news = $this
                ->Model
                ->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`
                    FROM `tb_news_cat` as `a`
                     ORDER BY `a`.`ord`");
        $this->view->project = $this
                ->Model
                ->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`
                    FROM `tb_project_cat` as `a` ORDER BY `a`.`ord`");
        $this->view->personnels = $this
                ->Model
                ->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`
                    FROM `tb_personnel_cat` as `a`
                    ORDER BY `a`.`ord`");
        $this->view->shareholder = $this
                ->Model
                ->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`
                    FROM `tb_shareholder_cat` as `a`
                    ORDER BY `a`.`ord`");
    }

    public function rightAction() {
        $moduleTitle = $this->_request->getParam('moduleTitle');
        $this->view->moduleTitle = $moduleTitle;
        $this->view->hots = $this
                ->Model
                ->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`,
                        IF('{$this->language}'='vn',`a`.`quote_vn`,`a`.`quote_en`) as `quote`,
                        IF('{$this->language}'='vn',`a`.`content_vn`,`a`.`content_en`) as `content`
                    FROM `tb_news_post` as `a`
                    WHERE  `a`.`ID`<>'0' ORDER BY `a`.`date` DESC  LIMIT 3");
        $db = 'tb_project_post';
        if ($moduleTitle == 'index') {
            $db = 'tb_bussiness_post';
        }
        $this->view->projects = $this
                ->Model
                ->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`
                    FROM `{$db}` as `a`
                    WHERE  `a`.`hot`<>'0' ORDER BY `a`.`date` DESC  LIMIT 4");
        $this->view->link = $this
                ->Model
                ->queryAll("SELECT `a`.* FROM `tb_link` as `a`
                    ORDER BY `a`.`ord`");
    }

    public function projectAction() {
        $moduleTitle = $this->_request->getParam('moduleTitle');
        $this->view->moduleTitle = $moduleTitle;
        $db = 'tb_project_post';
        if ($moduleTitle == 'index') {
            $db = 'tb_bussiness_post';
        }
        $this->view->projects = $this
                ->Model
                ->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`
                    FROM `{$db}` as `a`
                    WHERE  `a`.`hot`<>'0' ORDER BY `a`.`date` DESC  LIMIT 4");
    }

    public function bannerAction() {

    }

    public function footerAction() {
        $language = $this->_request->getParam('language');
        $info = $this->Model->getOne("tb_contact", "WHERE `lang`='vn'");

        if ($language == 'en') {
            $info['footer_title'] = $info['footer_en'];
            $info['office_title'] = $info['office_en'];
        } else {
            $info['footer_title'] = $info['footer_vn'];
            $info['office_title'] = $info['office_vn'];
        }
        $this->view->info = $info;

        $p = $this->Model->queryOne("SELECT COUNT(*) as total FROM `tb_stats`");
        $this->view->total = $p['total'];

        $p = $this->Model->queryOne("SELECT COUNT(*) as total FROM `tb_stats` WHERE date >= '" . date("Y-m-d H:i:s", time() - 10 * 30) . "'");
        $this->view->totalc = max(1, $p['total']);

        $p = $this->Model->queryOne("SELECT COUNT(*) as total FROM `tb_stats` WHERE date >= '" . date("Y-m-d", time()) . " 0:0:0'");
        $this->view->totalt = $p['total'];
    }

    public function submenuAction() {
        $id_active = $this->_request->getParam('id_active');
        $this->view->id_active = $id_active;
        $moduleTitle = $this->_request->getParam('moduleTitle');
        $this->view->moduleTitle = $moduleTitle;
        if ($moduleTitle == 'News') {
            $this->view->moduleTitle = 'Activities';
        }
        $language = $this->_request->getParam('language');
        $arrModule = array(
            'About' => array(
                'db' => 'tb_about_post',
                'm_en' => 'About',
                'm_vn' => 'gioi-thieu',
                'cat' => 'C'
            ),
            'Shareholder' => array(
                'db' => 'tb_shareholder_cat',
                'm_en' => 'Shareholder',
                'm_vn' => 'co-dong',
                'cat' => 'C'
            ),
            'News' => array(
                'db' => 'tb_news_cat',
                'm_en' => 'News',
                'm_vn' => 'hoat-dong',
                'cat' => 'C'
            ),
            'Services' => array(
                'db' => 'tb_bussiness_post',
                'm_en' => 'Services',
                'm_vn' => 'linh-vuc-kinh-doanh',
                'cat' => 'p'
            ),
            'Career' => array(
                'db' => 'tb_personnel_cat',
                'm_en' => 'Career',
                'm_vn' => 'nhan-su',
                'cat' => 'C'
            ),
            'Library' => array(
                'db' => 'tb_library_post',
                'm_en' => 'Library',
                'm_vn' => 'thu-vien',
                'cat' => 'C'
            ),
            'Project' => array(
                'db' => 'tb_project_cat',
                'm_en' => 'Project',
                'm_vn' => 'du-an',
                'cat' => 'C'
            ),
            'Gallery' => array(
                'db' => 'tb_gallery_cat',
                'm_en' => 'Gallery',
                'm_vn' => 'thu-vien-anh',
                'cat' => 'C'
            ),
        );
        $cat = $this->Model->queryOne("SELECT * FROM `{$arrModule[$moduleTitle]['db']}`
                    WHERE `ID`='{$id_active}'");
        $idCat = 0;
        if ($cat) {
            if ($cat['parent_id']) {
                $idCat = $cat['parent_id'];
            }
        }
        $this->view->idCat = $idCat;

        $posts = $this
                ->Model
                ->queryAll("SELECT `a`.*,
                        '{$arrModule[$moduleTitle]['cat']}' as `cat`,
                        IF('{$language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`,
                        IF('{$language}'='vn','{$arrModule[$moduleTitle]['m_vn']}','{$arrModule[$moduleTitle]['m_en']}') as `module`
                    FROM `{$arrModule[$moduleTitle]['db']}` as `a`
                    WHERE `a`.`parent_id`='0'
                    ORDER BY `a`.`ord`");

        if ($posts) {
            foreach ($posts as &$a) {
                $a['sub_posts'] = $this
                        ->Model
                        ->queryAll("SELECT `a`.*,
                        '{$arrModule[$moduleTitle]['cat']}' as `cat`,
                        IF('{$language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`,
                        IF('{$language}'='vn','{$arrModule[$moduleTitle]['m_vn']}','{$arrModule[$moduleTitle]['m_en']}') as `module`
                    FROM `{$arrModule[$moduleTitle]['db']}` as `a`
                    WHERE `parent_id`<>'0' AND `parent_id` ='{$a['ID']}'
                    ORDER BY `a`.`ord`");
            }
        }

        $this->view->posts = $posts;
    }

}
