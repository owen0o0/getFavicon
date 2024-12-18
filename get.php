<?php
/**
 * getFavicon
 * @author    一为
 * @date      2024-12-18
 * @link      https://www.iowen.cn
 * @version   1.2.1
 */

if( !isset($_GET['url'])){
    return http_response_code(404);
}

require "./config.php"; // 配置文件
require "./Favicon.php";

$favicon = new \Jerrybendy\Favicon\Favicon;

$cache_dir  = CACHE_DIR;
$hash_key   = HASH_KEY;
$defaultIco = DEFAULT_ICO;
$expire     = EXPIRE;

// 如果 HASH_KEY == iowen 则生成一个随机字符串，并更新config.php文件
if (HASH_KEY == 'iowen') {
    $hash_key = substr(hash('sha256', uniqid()), 0, 16);
    file_put_contents('./config.php', str_replace('iowen', $hash_key, file_get_contents('./config.php')));
}

/**
 * 设置默认图标
 */
$favicon->setDefaultIcon($defaultIco);

/**
 * 检测URL参数
 */
$url = $_GET['url'];

/*
 * 格式化 URL, 并尝试读取缓存
 */
$formatUrl = $favicon->formatUrl($url);
if ($formatUrl) {
    if ($expire == 0) {
        $favicon->getFavicon($formatUrl, false);
        exit;
    } else {
        $defaultMD5 = md5(file_get_contents($defaultIco));
        $cache      = new Cache($hash_key, $cache_dir);

        /**
         * 2023-02-20
         * 增加刷新缓存参数：refresh=true 如：https://域名?url=www.iowen.cn&refresh=true
         */
        if (!isset($_GET['refresh']) || (isset($_GET['refresh']) && $_GET['refresh'] != 'true')) {
            $data = $cache->get($formatUrl, $defaultMD5, $expire);
            if ($data !== NULL) {
                foreach ($favicon->getHeader() as $header) {
                    @header($header);
                }
                header('X-Cache-Type: IO');
                echo $data;
                exit;
            }
        }

        /**
         * 缓存中没有指定的内容时, 重新获取内容并缓存起来
         */
        $content = $favicon->getFavicon($formatUrl, true);

        $cache->set($formatUrl, $content);

        foreach ($favicon->getHeader() as $header) {
            @header($header);
        }

        echo $content;
        exit;
    }
} else {
    return http_response_code(404);
}

/**
 * 缓存类
 */
class Cache
{
    public $dir = 'cache'; //图标缓存目录

    public $hash_key = 'iowen'; // 哈希密钥

    public function __construct($hash_key, $dir = 'cache')
    {
        $this->hash_key = $hash_key;
        $this->dir      = $dir;
    }

    /**
     * 获取缓存的值, 不存在时返回 null
     *
     * @param string $key      缓存键(URL)
     * @param string $default  默认图片
     * @param int    $expire   过期时间
     * @return mixed
     */
    public function get($key, $default, $expire)
    {
        $host = strtolower(parse_url($key)['host']);
        $hash = substr(hash_hmac('sha256', $host, $this->hash_key), 8, 16);
        $f    = $host . '_' . $hash . '.txt';
        $path = $this->dir . '/' . $f;

        if (is_file($path)) {
            $data = file_get_contents($path);
            if (md5($data) == $default) {
                $expire = 43200; //如果返回默认图标，过期时间为12小时。
            }
            if ((time() - filemtime($path)) > $expire) {
                return null;
            } else {
                return $data;
            }
        } else {
            return null;
        }
    }

    /**
     * 设置缓存
     * 保存图标到缓存目录
     *
     * @param string $key      缓存键(URL)
     * @param string $value    缓存值(图标)
     */
    public function set($key, $value)
    {
        //如果缓存目录不存在则创建
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true) or die('创建缓存目录失败！');
        }

        $host = strtolower(parse_url($key)['host']);
        $hash = substr(hash_hmac('sha256', $host, $this->hash_key), 8, 16);
        $f    = $host . '_' . $hash . '.txt';
        $path = $this->dir . '/' . $f;

        $imgdata = fopen($path, "w") or die("Unable to open file!");
        if (flock($imgdata, LOCK_EX)) {  // 获取排他锁
            fwrite($imgdata, $value);
            flock($imgdata, LOCK_UN);  // 释放锁
        }
        fclose($imgdata);
    }
}
