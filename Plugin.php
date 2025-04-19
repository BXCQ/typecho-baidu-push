<?php
/**
 * Typecho百度推送插件
 * 
 * @package BaiduPush
 * @author Xuan
 * @version 1.0.0
 * @link https://github.com/BXCQ/typecho-baidu-push
 * @description 自动将Typecho博客文章推送到百度收录，支持自动推送和手动推送两种模式。
 */

class BaiduPush_Plugin implements Typecho_Plugin_Interface
{
    private $api = 'http://data.zz.baidu.com/urls?site=你的网站&token=你的token';
    public $token = '你的访问令牌';
    private $maxDailySubmit = 500;    // 每日最大提交数量
    private $batchSize = 50;          // 单批次提交数量
    private $logFile;                 // 日志文件路径
    private $urlFile;                 // URL列表文件路径

    public function __construct()
    {
        // 设置日志和URL列表文件路径
        $this->logFile = __DIR__ . '/baidu-push.log';
        $this->urlFile = __DIR__ . '/url-list.txt';
    }

    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('BaiduPush_Plugin', 'pushPost');
        Typecho_Plugin::factory('Widget_Contents_Page_Edit')->finishPublish = array('BaiduPush_Plugin', 'pushPost');
        return _t('插件启用成功');
    }

    public static function deactivate()
    {
        return _t('插件禁用成功');
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $site = new Typecho_Widget_Helper_Form_Element_Text('site', null, '', _t('网站地址'), _t('请输入您的网站地址，例如：https://www.example.com'));
        $form->addInput($site);

        $token = new Typecho_Widget_Helper_Form_Element_Text('token', null, '', _t('百度推送Token'), _t('请输入您的百度推送Token'));
        $form->addInput($token);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function pushPost($contents, $class)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $plugin = new self();
        
        // 获取文章URL
        $url = $options->siteUrl . $contents['pathinfo'];
        
        // 推送单个URL
        $result = $plugin->pushBatch(array($url));
        
        if ($result['success']) {
            $plugin->log('文章推送成功: ' . $url);
        } else {
            $plugin->log('文章推送失败: ' . $url . ' - ' . $result['message']);
        }
    }

    public function generateUrls()
    {
        $db = Typecho_Db::get();
        $select = $db->select('cid', 'slug', 'categories.slug as category')
            ->from('table.contents')
            ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
            ->join('table.categories', 'table.relationships.mid = table.categories.mid')
            ->where('table.contents.type = ?', 'post')
            ->where('table.contents.status = ?', 'publish');

        $rows = $db->fetchAll($select);
        $urls = array();

        foreach ($rows as $row) {
            // 支持所有URL格式
            $urls[] = $this->getSiteUrl() . '/' . $row['slug'] . '.html';
            $urls[] = $this->getSiteUrl() . '/' . $row['category'] . '/' . $row['slug'] . '.html';
            $urls[] = $this->getSiteUrl() . '/archives/' . $row['slug'] . '.html';
            $urls[] = $this->getSiteUrl() . '/archives/' . $row['cid'] . '/';
        }

        file_put_contents($this->urlFile, implode("\n", array_unique($urls)));
        $this->log('生成URL列表完成，共' . count($urls) . '个URL');
    }

    public function pushUrls()
    {
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

    private function pushBatch($urls)
    {
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

    private function getSiteUrl()
    {
        $options = Typecho_Widget::widget('Widget_Options');
        return rtrim($options->siteUrl, '/');
    }

    private function log($message)
    {
        $log = date('Y-m-d H:i:s') . ' ' . $message . "\n";
        file_put_contents($this->logFile, $log, FILE_APPEND);
    }
} 
