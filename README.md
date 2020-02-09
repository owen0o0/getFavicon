# getFavicon
获取网站的Favicon图标并显示在你的网页上.
<br/>

### 演示
<a href="https://api.iowen.cn/favicon/" target="_blank">演示地址</a>
<br/>

### 安装使用
+ 上传到网站根目录或者 favicon 文件夹中
+ cache 文件夹给 755 权限
+ 然后访问 http://you.url/favicon/get.php?url=https://www.iowen.cn
<br/>

+ 如果出现获取不了的情况建议删除缓存再试一次
+ 注：文中 faviconw 为 api 文件夹，酌情设置
<br/>

### 伪静态
方便cdn缓存
```
# Nginx规则
rewrite ^/favicon/(.*)\.png$ /favicon/get.php?url=$1;

# Apache 规则
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^favicon/(.*)\.png$ favicon/get.php?url=$1 [L]
</IfModule>
```
调用方法 http://you.url/favicon/www.iowen.cn.png
<br/>

### 感谢
感谢 <a href="https://github.com/jerrybendy/get_favicon" target="_blank">jerrybendy</a> ，此版本只是修复一些 bug 和编写了获取方法。
<br/>
