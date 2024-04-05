<?php

class TradeBasic_IndexController extends Zend_Controller_Action {
    public function indexAction() {
        $plugins = $this->Plugins;
        $models = $this->Model;
        $view = $this->view;
        $table = 'candle_pattern';
        /*
         * Study: Rsi - 7, Rsi - 14....
         * Tabs: Telther, Bitcoin...
         * Types: 1D, H4, H1
         */
        $study = $plugins->get('study', 'RSI7');
//        $tab = $plugins->get('tab', 'USDT');
//        $type = $plugins->get('type' , '1D');
//        $list = $plugins->get('list' , 'st');
        $symbol = $plugins->get('symbol', '');  
        $postId = $plugins->get('ID', 0);
        $postCurrent = [];
        if ($postId) {
            $postCurrent = $models->getOne($table, "WHERE `ID` = {$postId}");
        }
        
        
        
        $wherePost = "`{$study}` > 70 OR `{$study}` < 30";
//        $rightPosts = $models->queryAll("SELECT * FROM `{$table}` WHERE " . $wherePost);
//        $leftPostsUp = $models->queryAll("SELECT * FROM `{$table}` WHERE " . $wherePost . " AND `date_find` = NOW() ORDER BY `{$study}` DESC LIMIT 5");
//        $leftPostsDown = $models->queryAll("SELECT * FROM `{$table}` WHERE " . $wherePost . " AND `date_find` = NOW() ORDER BY `{$study}` ASC LIMIT 5");
        $rightPosts = $models->queryAll("SELECT * FROM `{$table}` WHERE " . $wherePost . ' ORDER BY `date` DESC');
        $leftPostsUp = $models->queryAll("SELECT * FROM `{$table}` WHERE (" . $wherePost . ") AND DATE (CURDATE() - INTERVAL WEEKDAY (CURDATE()) DAY) = DATE (`date` - INTERVAL WEEKDAY (`date`) DAY) ORDER BY `{$study}` DESC LIMIT 5");
        $leftPostsDown = $models->queryAll("SELECT * FROM `{$table}` WHERE (" . $wherePost . ") AND DATE (CURDATE() - INTERVAL WEEKDAY (CURDATE()) DAY) = DATE (`date` - INTERVAL WEEKDAY (`date`) DAY) ORDER BY `{$study}` ASC LIMIT 5");
        
        if ($rightPosts && !$postCurrent) {
            $postCurrent = $rightPosts[0];
        }
        
        if (!$symbol && $postCurrent) {
            $symbol = $postCurrent['symbol'];
        }
        
//        $postsHighLight = [];
//        if ($leftPostsUp) {
//            $postsHighLight = array_merge($postsHighLight, $leftPostsUp);
//        }
//        
//        if ($leftPostsDown) {
//            $postsHighLight = array_merge($postsHighLight, $leftPostsDown);
//        }

        $view->rightPosts = $rightPosts;
        $view->leftPostsUp = $leftPostsUp;
        $view->leftPostsDown = $leftPostsDown;
//        $view->highlightpost = $postsHighLight;
        $view->symbol = $symbol;
        $view->postCurrent = $postCurrent;
    }

}
