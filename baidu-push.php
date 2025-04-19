<?php
/**
 * Typecho百度推送插件 - 推送执行器
 * 
 * @package BaiduPush
 * @author Xuan
 * @version 1.0.0
 * @link https://github.com/BXCQ/typecho-baidu-push
 */

// 引入Typecho配置文件
require_once __DIR__ . '/../../config.inc.php';

// 引入Typecho公共函数
require_once __DIR__ . '/../../var/Typecho/Common.php';

// 引入数据库类
require_once __DIR__ . '/../../var/Typecho/Db.php';

// 引入插件类
require_once __DIR__ . '/Plugin.php';

// 处理手动推送请求
if (isset($_GET['action']) && isset($_GET['token'])) {
    $push = new BaiduPush_Plugin();
    
    if ($_GET['token'] !== $push->token) {
        die('无效的访问令牌');
    }

    switch ($_GET['action']) {
        case 'generate':
            $push->generateUrls();
            echo 'URL列表生成完成';
            break;
        case 'push':
            $push->pushUrls();
            echo 'URL推送完成';
            break;
        default:
            die('无效的操作');
    }
} 
