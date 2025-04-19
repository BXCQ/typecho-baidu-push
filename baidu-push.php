<?php
/**
 * Typecho Baidu Push Plugin
 * 
 * @package BaiduPush
 * @author Xuan
 * @version 1.0.0
 * @link https://github.com/BXCQ/typecho-baidu-push
 */

class BaiduPush {
    private $api = 'http://data.zz.baidu.com/urls?site=你的网站&token=你的token';
    private $token = '你的访问令牌';
    private $maxDailySubmit = 500;    // 每日最大提交数量
    private $batchSize = 50;          // 单批次提交数量
    private $logFile = 'baidu-push.log';
    private $urlFile = 'urls.txt';

    public function __construct() {
        if (!extension_loaded('curl')) {
            die('需要开启curl扩展');
        }
    }

    public function generateUrls() {
        $db = Typecho_Db::get();
        $select = $db->select('slug', 'categories.slug as category')
            ->from('table.contents')
            ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
            ->join('table.categories', 'table.relationships.mid = table.categories.mid')
            ->where('table.contents.type = ?', 'post')
            ->where('table.contents.status = ?', 'publish');

        $rows = $db->fetchAll($select);
        $urls = array();

        foreach ($rows as $row) {
            // 支持多种URL格式
            $urls[] = $this->getSiteUrl() . '/' . $row['slug'] . '.html';
            $urls[] = $this->getSiteUrl() . '/' . $row['category'] . '/' . $row['slug'] . '.html';
            $urls[] = $this->getSiteUrl() . '/archives/' . $row['slug'] . '.html';
        }

        file_put_contents($this->urlFile, implode("\n", array_unique($urls)));
        $this->log('生成URL列表完成，共' . count($urls) . '个URL');
    }

    public function pushUrls() {
        if (!file_exists($this->urlFile)) {
            die('请先生成URL列表');
        }

        $urls = file($this->urlFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $total = count($urls);
        $batches = array_chunk($urls, $this->batchSize);
        $success = 0;
        $failed = 0;

        foreach ($batches as $batch) {
            $result = $this->pushBatch($batch);
            if ($result['success']) {
                $success += $result['count'];
            } else {
                $failed += count($batch);
                $this->log('推送失败: ' . $result['message']);
                break;
            }
            sleep(1); // 批次间暂停1秒
        }

        $this->log("推送完成：成功{$success}个，失败{$failed}个，总计{$total}个URL");
    }

    private function pushBatch($urls) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode("\n", $urls));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $result = json_decode($response, true);
            if (isset($result['success'])) {
                return array('success' => true, 'count' => $result['success']);
            }
        }

        return array('success' => false, 'message' => $response);
    }

    private function getSiteUrl() {
        $options = Typecho_Widget::widget('Widget_Options');
        return rtrim($options->siteUrl, '/');
    }

    private function log($message) {
        $log = date('Y-m-d H:i:s') . ' ' . $message . "\n";
        file_put_contents($this->logFile, $log, FILE_APPEND);
    }
}

// 处理请求
if (isset($_GET['action']) && isset($_GET['token'])) {
    $push = new BaiduPush();
    
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
