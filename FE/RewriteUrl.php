<?php

//Zend_Controller_Dispatcher_Standard
$module = strtolower($request->getModuleName());
$controller = strtolower($request->getControllerName());
$action = strtolower($request->getActionName());
$language = $request->getParam("language");
$path = strtolower($_SERVER['REQUEST_URI']);

//Secruity for ADMIN
if ($module == "admin" && $controller != "login") {
    if (!isset($_SESSION['cp__user'])) {
        $request->setModuleName("Admin");
        $request->setControllerName("login");
        $request->setActionName("index");
    }
    return true;
}

//if (!$module || $controller == "h4" || $controller == "home" || $controller == "h4psar") {
//    if (!isset($_SESSION['cp__customer'])) {
//        $request->setModuleName("Knowledge");
//        $request->setControllerName("index");
//        $request->setActionName("index");
//    }
//}
//Rewrite Url
switch ($module) {
    case 'system':
        if ($controller == "gioi-thieu") {
            $request->setModuleName("About");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } elseif ($controller == "video") {
            $request->setModuleName("Video");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } elseif ($controller == "social") {
            $request->setModuleName("Social");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } elseif ($controller == "trade") {
            $request->setModuleName("Trade");
		} elseif ($controller == "TradeProfessional") {
            $request->setModuleName("TradeProfessional");
        } elseif ($controller == "trade2") {
            $request->setModuleName("Trade2");
        } elseif ($controller == "tradebasic") {
            $request->setModuleName("TradeBasic");
        } elseif ($controller == "predictions") {
            $request->setModuleName("Predictions");
        } elseif ($controller == "history") {
            $request->setModuleName("History");
        } elseif ($controller == "tim-kiem") {
            $request->setModuleName("Search");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } elseif ($controller == "thu-vien-anh") {
            $request->setModuleName("Gallery");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } elseif ($controller == "thu-vien") {
            $request->setModuleName("Library");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } else if ($controller == "trang-chu") {
            $request->setModuleName("Home");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } else if ($controller == "lien-he") {
            $request->setModuleName("Contact");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } else if ($controller == "dang-ky") {
            $request->setModuleName("Register");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } else if ($controller == "dang-nhap") {
            $request->setModuleName("Login");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } elseif ($controller == "knowledge") {
            $request->setModuleName("Knowledge");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } elseif ($controller == "tin-tuc") {
            $request->setModuleName("News");
            $request->setControllerName("index");
            $request->setActionName("index");

            if (preg_match("/p(\d+)_?/i", $action, $match)) {
                $_REQUEST['ID'] = $match[1];
            } else if (preg_match("/c(\d+)_?/i", $action, $match)) {
                $_REQUEST['C'] = $match[1];
            }
        } else {
            $request->setControllerName("index");
            $request->setActionName("index");
        }
        break;
    case 'TradeProfessional':
        $request->setModuleName("TradeProfessional");
        $request->setControllerName("index");
        $request->setActionName("index");
        if (preg_match("/p(\d+)_?/i", $controller, $match)) {
            $_REQUEST['ID'] = $match[1];
        } else if (preg_match("/c(\d+)_?/i", $controller, $match)) {
            $_REQUEST['C'] = $match[1];
        }
        break;
   case 'knowledge':
        $request->setModuleName("Knowledge");
        $request->setControllerName("index");
        $request->setActionName("index");
        if (preg_match("/p(\d+)_?/i", $controller, $match)) {
            $_REQUEST['ID'] = $match[1];
        } else if (preg_match("/c(\d+)_?/i", $controller, $match)) {
            $_REQUEST['C'] = $match[1];
        }
        break;
    case 'news':
        $request->setModuleName("News");
        $request->setControllerName("index");
        $request->setActionName("index");
        if (preg_match("/p(\d+)_?/i", $controller, $match)) {
            $_REQUEST['ID'] = $match[1];
        } else if (preg_match("/c(\d+)_?/i", $controller, $match)) {
            $_REQUEST['C'] = $match[1];
        }
        break;
    case 'about':
        $request->setControllerName("index");
        $request->setActionName("index");
        if (preg_match("/p(\d+)_?/i", $controller, $match)) {
            $_REQUEST['ID'] = $match[1];
        } else if (preg_match("/c(\d+)_?/i", $controller, $match)) {
            $_REQUEST['C'] = $match[1];
        }
        break;
    case 'project':
        $request->setControllerName("index");
        $request->setActionName("index");
        if (preg_match("/p(\d+)_?/i", $controller, $match)) {
            $_REQUEST['ID'] = $match[1];
        } else if (preg_match("/c(\d+)_?/i", $controller, $match)) {
            $_REQUEST['C'] = $match[1];
        }
        break;
    case 'services':
        $request->setControllerName("index");
        $request->setActionName("index");
        if (preg_match("/p(\d+)_?/i", $controller, $match)) {
            $_REQUEST['ID'] = $match[1];
        } else if (preg_match("/c(\d+)_?/i", $controller, $match)) {
            $_REQUEST['C'] = $match[1];
        }
        break;
    case 'search':
        $request->setControllerName("index");
        $request->setActionName("index");
        if (preg_match("/p(\d+)_?/i", $controller, $match)) {
            $_REQUEST['ID'] = $match[1];
        } else if (preg_match("/c(\d+)_?/i", $controller, $match)) {
            $_REQUEST['C'] = $match[1];
        }
        break;
    case 'contact':
        $request->setControllerName("index");
        $request->setActionName("index");
        if (preg_match("/p(\d+)_?/i", $controller, $match)) {
            $_REQUEST['ID'] = $match[1];
        } else if (preg_match("/c(\d+)_?/i", $controller, $match)) {
            $_REQUEST['C'] = $match[1];
        }
        break;
    case 'report':
        $request->setModuleName("Report");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'cron':
        $request->setModuleName("Cron");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
	case 'cron2':
        $request->setModuleName("Cron2");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'cronh4':
        $request->setModuleName("Cronh4");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'cronh4psar':
        $request->setModuleName("Cronh4psar");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'send':
        $request->setModuleName("Send");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'sendh4':
        $request->setModuleName("Sendh4");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'sendh4psar':
        $request->setModuleName("Sendh4psar");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'processd1':
        $request->setModuleName("Processd1");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'processh4':
        $request->setModuleName("Processh4");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'rsi':
        $request->setModuleName("Rsi");
        break;
    case 'rsi14':
        $request->setModuleName("Rsi14");
        break;
    case 'rsih4':
        $request->setModuleName("Rsih4");
        break;
    case 'rsih414':
        $request->setModuleName("Rsih414");
        break;
    case 'adx14':
        $request->setModuleName("Adx14");
        break;
    case 'adxh414':
        $request->setModuleName("Adxh414");
        break;
    case 'ema':
        $request->setModuleName("Ema");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'emah4':
        $request->setModuleName("Emah4");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'ema89h4':
        $request->setModuleName("Ema89h4");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'h4':
        $request->setModuleName("H4");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'trade':
        $request->setModuleName("Trade");
        break;
    case 'trade2':
        $request->setModuleName("Trade2");
        break;
    case 'chartpatterns':
        $request->setModuleName("ChartPatterns");
        break;
    case 'TradeProfesional':
        $request->setModuleName("TradeProfesional");
        break;
    case 'tradebasic':
        $request->setModuleName("TradeBasic");
        break;
    case 'predictions':
        $request->setModuleName("Predictions");
        break;
    case 'aipredictions':
        $request->setModuleName("Aipredictions");
        break;
    case 'aipredictionsdashboard':
        $request->setModuleName("Aipredictionsdashboard");
        break;    
    case 'history':
        $request->setModuleName("History");
        break;
    case 'knowledge':
        $request->setModuleName("Knowledge");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'social':
        $request->setModuleName("Social");
        $request->setControllerName($controller);
        $request->setActionName($action);
        break;
    case 'group':
        $request->setModuleName("Group");
        break;
    case 'user':
        $request->setModuleName("User");
        break;
    case 'work':
        $request->setModuleName("Work");
        break;
    case 'profile':
        $request->setModuleName("Profile");
        break;
    case 'h4psar':
        $request->setModuleName("H4psar");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'd1psar':
        $request->setModuleName("D1psar");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'login':
        $request->setModuleName("Login");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'register':
        $request->setModuleName("Register");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'test':
        $request->setModuleName("Test");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
	case 'aipredictions':
        $request->setModuleName("Aipredictions");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'check':
        $request->setModuleName("Check");
        $request->setControllerName("index");
        $action = $request->getParam('controller');
        $request->setActionName($action);
        break;
    case 'senh4cron':
        $request->setModuleName("Sendh4cron");
        $request->setControllerName("index");
        $request->setActionName("cron");
        $_REQUEST['C'] = $controller;
        break;
    case 'sendd1psar':
        $request->setModuleName("Sendd1psar");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'crond1psar':
        $request->setModuleName("Crond1psar");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'logout':
        $request->setModuleName("Logout");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    case 'heatmaprsi':
        $request->setModuleName("Heatmaprsi");
        $request->setControllerName("index");
        $request->setActionName("index");
        $_REQUEST['C'] = $controller;
        break;
    default:
}
?>