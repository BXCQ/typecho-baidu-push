# Typecho百度推送插件

一个用于自动将Typecho博客文章推送到百度收录的插件。

## 功能特点

- 自动推送新发布的文章
- 支持手动批量推送
- 支持多种URL格式
- 自动控制推送频率
- 详细的推送日志记录

## 安装说明

1. 下载插件文件：
   - `Plugin.php`
   - `baidu-push.php`

2. 在Typecho的插件目录下创建文件夹：
   ```
   usr/plugins/BaiduPush/
   ├── Plugin.php
   └── baidu-push.php
   ```

3. 设置文件权限：
   ```bash
   chmod -R 755 usr/plugins/BaiduPush/
   chmod 666 usr/plugins/BaiduPush/baidu-push.log
   chmod 666 usr/plugins/BaiduPush/url-list.txt
   ```

4. 在Typecho后台启用插件

5. 配置插件参数：
   - 网站地址
   - 百度推送Token

## 使用方法

### 自动推送
- 新文章发布时自动推送
- 文章更新时自动推送
- 推送结果记录在日志文件中

### 手动推送
```
# 生成URL列表
https://你的域名/usr/plugins/BaiduPush/baidu-push.php?action=generate&token=你的令牌

# 执行推送
https://你的域名/usr/plugins/BaiduPush/baidu-push.php?action=push&token=你的令牌
```

### 查看推送结果
- 检查日志文件：`usr/plugins/BaiduPush/baidu-push.log`
- 在百度站长平台查看收录情况

## 文件说明

### 日志文件
- 位置：`usr/plugins/BaiduPush/baidu-push.log`
- 内容：推送记录
- 格式：`时间 推送结果`
- 示例：
  ```
  2024-01-01 12:00:00 文章推送成功: https://example.com/post.html
  ```

### URL列表文件
- 位置：`usr/plugins/BaiduPush/url-list.txt`
- 内容：待推送URL
- 格式：每行一个URL
- 示例：
  ```
  https://example.com/post1.html
  https://example.com/post2.html
  ```

## 常见问题

### 推送后未显示收录
可能原因：
- 百度处理延迟（通常需要几分钟到几小时）
- URL格式不正确
- 网站未通过百度验证
- 网站内容质量不符合收录标准

解决方案：
1. 等待一段时间
2. 检查URL格式
3. 确认网站验证状态
4. 确保内容质量

### 文件权限问题
如果日志或URL列表文件未生成：
1. 检查文件权限
2. 检查目录权限
3. 检查PHP配置
4. 检查磁盘空间

### 推送失败
常见原因：
- 网络连接问题
- 接口限制
- 配额超限
- Token错误

## 更新日志

### v1.0.0
- 支持自动推送
- 支持手动推送
- 支持多种URL格式
- 添加日志记录
- 添加配额管理

## 参考资源
- [百度站长平台](https://ziyuan.baidu.com/)
- [Typecho文档](http://docs.typecho.org/)
- [项目GitHub仓库](https://github.com/BXCQ/typecho-baidu-push) 
