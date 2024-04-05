<?php

class Search_IndexController extends Zend_Controller_Action {

    var $tb_news = "tb_news_post";
    var $tb_bussiness = "tb_bussiness_post";
    var $tb_personnel = "tb_personnel_post";
    var $tb_shareholder = "tb_shareholder_post";
    var $tb_library = "tb_library_post";
    var $tb_project = "tb_project_post";
    var $tb_about = "tb_about_post";
    var $tb_gallery = "tb_gallery_post";

    public function init() {
        $info = $this->Model->getOne("tb_contact");
        $this->view->info=$info;
        $meta=array(
            'desc'=>$info['text2'],
            'key'=>$info['text1']
        );
        $this->view->meta=$meta;
        //set page default in admin page
        $this->page = 2;

    }

    public function indexAction() {
        $moduleTitle = $this->_request->getParam('moduleTitle');
        $word = strtoupper($this->Plugins->getWordSearch("srch-term"));
        $module= strtolower($this->Plugins->get("module"));
        $this->view->module=$module;
        $this->view->word=strtolower($word);
        $current = $this->Plugins->getCurrentPage("p");
        $siteLimit = 10;
        $limit = ($current - 1) * $siteLimit . ",$siteLimit";
        if ( $module == 'index' || $module=='default' ||  $module == 'contact' ||  $module==null || $module == 'search' || $module=='') {
            $query = "
			SELECT  'Project' as `module`,
                    `a`.`ID`,
                    `a`.`date`,
                    CONCAT('/p',`a`.`ID`) as `postID`,
                    IF(`a`.`img` IS NOT NULL,CONCAT('project/',`a`.`img`),NULL) as `pic`,
                    IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`,
                    '' as `quote`
                FROM `{$this->tb_project}` as `a`
                    WHERE  upper(CONCAT(' ',`a`.`title_vn`,' ',`a`.`title_en`)) LIKE '%$word%'
			UNION
			SELECT  'News' as `module`,
                    `b`.`ID`,
                    `b`.`date`,
                    CONCAT('/p',`b`.`ID`) as `postID`,
                    IF(`b`.`img` IS NOT NULL,CONCAT('news/',`b`.`img`),NULL) as `pic`,
                    IF('{$this->language}'='vn',`b`.`title_vn`,`b`.`title_en`) as `title`,
                    IF('{$this->language}'='vn',`b`.`quote_vn`,`b`.`quote_en`) as `quote`
                FROM `{$this->tb_news}` as `b`
                    WHERE  upper(CONCAT(' ',`b`.`title_vn`,' ',`b`.`title_en`)) LIKE '%$word%'
			UNION
			SELECT  'Gallery' as `module`,
                    `b`.`ID`,
                    `b`.`date`,
                    CONCAT('/p',`b`.`ID`) as `postID`,
                    IF(`b`.`img` IS NOT NULL,CONCAT('gallery/',`b`.`img`),NULL) as `pic`,
                    IF('{$this->language}'='vn',`b`.`title_vn`,`b`.`title_en`) as `title`,
                    IF('{$this->language}'='vn',`b`.`quote_vn`,`b`.`quote_en`) as `quote`
                FROM `{$this->tb_gallery}` as `b`
                    WHERE  upper(CONCAT(' ',`b`.`title_vn`,' ',`b`.`title_en`)) LIKE '%$word%'
			UNION
			SELECT  'Services' as `module`,
                    `c`.`ID`,
                    `c`.`date`,
                    CONCAT('/p',`c`.`ID`) as `postID`,
                    IF(`c`.`img` IS NOT NULL,CONCAT('bussiness/',`c`.`img`),NULL) as `pic`,
                    IF('{$this->language}'='vn',`c`.`title_vn`,`c`.`title_en`) as `title`,
                    IF('{$this->language}'='vn',`c`.`quote_vn`,`c`.`quote_en`) as `quote`
                FROM `{$this->tb_bussiness}` as `c`
                    WHERE  upper(CONCAT(' ',`c`.`title_vn`,' ',`c`.`title_en`)) LIKE '%$word%'
			UNION
			SELECT  'Career' as `module`,
                    `d`.`ID`,
                    `d`.`date`,
                    CONCAT('/p',`d`.`ID`) as `postID`,
                    IF(`d`.`img` IS NOT NULL,CONCAT('personnel/',`d`.`img`),NULL) as `pic`,
                    IF('{$this->language}'='vn',`d`.`title_vn`,`d`.`title_en`) as `title`,
                    IF('{$this->language}'='vn',`d`.`quote_vn`,`d`.`quote_en`) as `quote`
                FROM `{$this->tb_personnel}` as `d`
                    WHERE  upper(CONCAT(' ',`d`.`title_vn`,' ',`d`.`title_en`)) LIKE '%$word%'
			UNION
			SELECT  'Shareholder' as `module`,
                    `e`.`ID`,
                    `e`.`date`,
                    CONCAT('/p',`e`.`ID`) as `postID`,
                    'NULL' as `pic`,
                    IF('{$this->language}'='vn',`e`.`title_vn`,`e`.`title_en`) as `title`,
                    IF('{$this->language}'='vn',`e`.`quote_vn`,`e`.`quote_en`) as `quote`
                FROM `{$this->tb_shareholder}` as `e`
                    WHERE  upper(CONCAT(' ',`e`.`title_vn`,' ',`e`.`title_en`)) LIKE '%$word%'
			UNION
			SELECT  'Library' as `module`,
                    `f`.`ID`,
                    `f`.`date`,
                    CONCAT('/p',`f`.`ID`) as `postID`,
                    IF(`f`.`img` IS NOT NULL,CONCAT('library/',`f`.`img`),NULL) as `pic`,
                    IF('{$this->language}'='vn',`f`.`title_vn`,`f`.`title_en`) as `title`,
                    IF('{$this->language}'='vn',`f`.`content_vn`,`f`.`content_en`) as `quote`
                FROM `{$this->tb_library}` as `f`
                    WHERE  upper(CONCAT(' ',`f`.`title_vn`,' ',`f`.`title_en`)) LIKE '%$word%'
			UNION
            SELECT  'About' as `module`,
                    `g`.`ID`,
                    `g`.`date`,
                    CONCAT('/C',`g`.`ID`) as `postID`,
                    IF(`g`.`img` IS NOT NULL,CONCAT('about/',`g`.`img`),NULL) as `pic`,
                    IF('{$this->language}'='vn',`g`.`title_vn`,`g`.`title_en`) as `title`,
                    IF('{$this->language}'='vn',`g`.`content_vn`,`g`.`content_en`) as `quote`
                FROM `{$this->tb_about}` as `g`
                    WHERE  upper(CONCAT(' ',`g`.`title_vn`,' ',`g`.`title_en`)) LIKE '%$word%'
            ";
            $query_table = array(
                $this->tb_project,
                $this->tb_news,
                $this->tb_bussiness,
                $this->tb_personnel,
                $this->tb_shareholder,
//                $this->tb_library,
                $this->tb_gallery,
                $this->tb_about);
            //print_r($query_table); die;
        } else {
            $mC = ucfirst($module);
            $mL = strtolower($module);
            $query = "SELECT  '{$mC}' as `module`,
                    `b`.`ID`,
                    `b`.`date`,
                    IF('{$mC}'='About', CONCAT('/C',`b`.`ID`),CONCAT('/p',`b`.`ID`)) as `postID`,
                    IF(`b`.`img` IS NOT NULL,CONCAT('{$mL}/',`b`.`img`),NULL) as `pic`,
                    IF('{$this->language}'='vn',`b`.`title_vn`,`b`.`title_en`) as `title`,
                    IF('{$this->language}'='vn',`b`.`quote_vn`,`b`.`quote_en`) as `quote`
                FROM `tb_{$mL}_post` as `b`
                    WHERE  upper(CONCAT(' ',`b`.`title_vn`,' ',`b`.`title_en`)) LIKE '%$word%'";
            $query_table = array("tb_{$mL}_post");
        }
        $totalPost = 0;
        for ( $i = 0; $i < count($query_table); $i++ ) {
            $totalPost += $this
                    ->Model
                    ->getTotal($query_table[$i], "WHERE upper(CONCAT(' ',`title_vn`,' ',`title_en`)) LIKE '%$word%'");
        }
        $this->view->pageBar = $this
                ->Plugins
                ->getPageBarDiv(($this->language == "vn" ? 'tim-kiem' : 'Search')."/?module={$module}&srch-term={$word}&p=", $current, $totalPost, $siteLimit, 5, false);

        $this->view->posts = $this->Model->queryAll($query . " LIMIT $limit");
        $this->view->total = $totalPost;
        $this->view->currentPage=$current;
        $this->view->totalPage=ceil( $totalPost/$siteLimit );

    }

}
