<?php

class Profile_IndexController extends Zend_Controller_Action {

    var $tb_post = "tb_product_post";
    var $tb_cat = "tb_product_cat";
    var $tb_config = "tb_personnel_config";
    var $tb_product = "tb_product_post";

    public function init() {
        if (!$_SESSION['cp__customer'])
            $this->_redirect(BASE_URL . "/{$this->language}/login");

        $tab = $this->Plugins->get("tab", '');
        $this->view->tab = $tab;
        $this->view->coins = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`");
    }

    public function indexAction() {

    }

    public function headerAction() {
        $this->view->moduleTitle1 = $this->_request->getParam('moduleTitle');
        $meta['desc'] = $this->_request->getParam('meta_desc');
        $meta['key'] = $this->_request->getParam('meta_key');
        $this->view->meta = $meta;
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

    }

    public function moreAction() {

    }

    public function itemsAction() {
        $customer = $_SESSION['cp__customer'];
        $type = $this->Plugins->get("type", 'week');
        $detailId = $this->Plugins->get("detail_id", 0);

        $wheres = [];
        $wheres[] = "`customer_id`='{$customer['ID']}'";

        $options = [];
        $dataDates = [];
        $year = date('Y');
        if ($type == 'month') {
            for ($i = date('n'); $i > 0; $i--) {
                $options[] = [
                    'ID' => $i,
                    'title' => "Tháng {$i}/" . $year
                ];
            }
            if (!$detailId) {
                $detailId = date('n');
            }
            $wheres[] = "MONTH(`date`)='{$detailId}' AND YEAR(`date`)='{$year}'";
        } else {
            $wNow = date('W');
            $i = 0;
            $dd = date("w") - 1;
            $dateFirst = date("Y-m-d", strtotime(date("Y-m-d") . " -{$dd} days"));
            $dateEnd = date("Y-m-d", strtotime($dateFirst . " +6 days"));

            for ($wNow; $wNow >= 1; $wNow--) {
                $options[] = [
                    'ID' => $wNow,
                    'title' => "Tuần thứ {$wNow}" . " (" . date("d/m/Y", strtotime($dateFirst)) . " -> " . date("d/m/Y", strtotime($dateEnd)) . ")"
                ];

                $dataDates[$wNow] = [
                    'start' => $dateFirst,
                    'end' => $dateEnd,
                ];

                $dateFirst = date("Y-m-d", strtotime($dateFirst . " -7 days"));
                $dateEnd = date("Y-m-d", strtotime($dateEnd . " -7 days"));

                $i++;
            }



            if (!$detailId) {
                $detailId = date('W');
            }

            $dT = $dataDates[$detailId];
            $wheres[] = "`date`>='{$dT['start']}' AND `date`<='{$dT['end']}'";
        }

        if ($_REQUEST['sql']) {
            debug($wheres);
        }

        $posts = $this->Model->queryAll("SELECT `a`.*
                            FROM `stock_histories` as `a`
                                WHERE " . implode(" AND ", $wheres) . "
                                ORDER BY date DESC");

        $arrPosts = ['won' => 0, 'waiting' => 0, 'fail' => 0, 'profit' => 0, 'profitAvg' => 0];
        $dataChartCols = [
            'won' => ['Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0],
            'fail' => ['Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0],
            'waiting' => ['Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0],
            'processing' => ['Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0],
        ];
        if ($posts) {
            foreach ($posts as $a) {
                $d = date('D', strtotime($a['date']));
                if ($a['status'] == 'WON') {
                    $arrPosts['won'] += 1;
                    $dataChartCols['won'][$d] += 1;
                    $arrPosts['profit'] += $a['profit'];
                } elseif ($a['status'] == 'FAIL') {
                    $arrPosts['fail'] += 1;
                    $arrPosts['profit'] -= $a['profit'];
                    $dataChartCols['fail'][$d] += 1;
                } elseif ($a['status'] == 'WAITING' || $a['status'] == 'DOING') {
                    $arrPosts['watch'] += 1;
                    $dataChartCols['watch'][$d] += 1;
                    $dataChartCols['processing'][$d] += 1;
                    $arrPosts['processing'] += 1;
                } elseif ($a['status'] == 'WAITING') {
                    $arrPosts['waiting'] += 1;
                    $dataChartCols['waiting'][$d] += 1;
                }
                $arrPosts['total'] += 1;
            }
            if ($arrPosts['won'])
                $arrPosts['profitAvg'] = round($arrPosts['profit'] / ($arrPosts['won'] + $arrPosts['fail']), 1);
        }

//        $dataChartCols['won']['Sun'] = max($dataChartCols['won']) + 1;
//        $dataChartCols['fail']['Sun'] = max($dataChartCols['fail']) + 1;
//        $dataChartCols['waiting']['Sun'] = max($dataChartCols['waiting']) + 1;
//        $dataChartCols['processing']['Sun'] = max($dataChartCols['processing']) + 1;

        $this->view->dataChartCols = $dataChartCols;
        $this->view->post = $arrPosts;
        $this->view->type = $type;
        $this->view->options = $options;
        $this->view->detailId = $detailId;
    }

    public function cronAction() {
        die('11111');
    }

}
