# Typecho Baidu Push Plugin

这是一个用于Typecho博客的百度推送插件，支持自动推送文章URL到百度搜索引擎。

## 需要修改的内容

在 `baidu-push.php` 文件中，需要修改以下内容：

1. 第12行：修改百度推送接口地址
```php
private $api = 'http://data.zz.baidu.com/urls?site=你的网站&token=你的token';
```

2. 第13行：修改访问令牌
```php
private $token = '你的访问令牌';
```

3. 第14-15行：根据需要调整配额设置
```php
private $maxDailySubmit = 500;    // 每日最大提交数量
private $batchSize = 50;          // 单批次提交数量
```

4. 第16行：修改日志文件路径
```php
private $logFile = 'baidu-push.log';
```

## 功能特点

- 支持多种URL格式：
  - `/{slug}.html`
  - `/{category}/{slug}.html`
  - `/archives/{slug}.html`
- 自动分批处理URL
- 配额管理
- 详细的日志记录
- 新文章自动推送

## 使用方法

1. 将 `baidu-push.php` 文件上传到网站根目录
2. 修改配置文件中的必要信息
3. 访问 `https://你的域名/baidu-push.php?action=generate&token=你的令牌` 生成URL列表
4. 访问 `https://你的域名/baidu-push.php?action=push&token=你的令牌` 推送URL

## 注意事项

- 确保服务器支持PHP 5.6+
- 需要开启curl扩展
- 确保有写入权限
- 建议设置合理的每日配额 
