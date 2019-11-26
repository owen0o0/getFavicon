<?php
/**
 * getFavicon
 * @author    一为
 * @date      2019-05-18
 * @link      https://www.iowen.cn
 * @version   1.0.0
 */

if( !isset($_GET['url'])){
    return http_response_code(404);
}

require "./Favicon.php";

$favicon = new \Jerrybendy\Favicon\Favicon;

/**
 * 默认图标
 */
$favicon->setDefaultIcon('favicon.png');

/**
 * 检测URL参数
 */
$url = $_GET['url'];


/*
 * 格式化 URL, 并尝试读取缓存
 */
$formatUrl = $favicon->formatUrl($url);

if (Cache::get($formatUrl) !== NULL) {

    foreach ($favicon->getHeader() as $header) {
        @header($header);
    }

    echo Cache::get($formatUrl);
    exit;
}

/**
 * 缓存中没有指定的内容时, 重新获取内容并缓存起来
 */
$content = $favicon->getFavicon($formatUrl, TRUE);

Cache::set($formatUrl, $content, 86400);

foreach ($favicon->getHeader() as $header) {
    @header($header);
}

echo $content;
exit;



/**
 * 缓存类
 */
class Cache
{
    /**
     * 获取缓存的值, 不存在时返回 null
     *
     * @param $key
     * @return string
     */
    public static function get($key)
    {
        $dir = 'cache'; //图标缓存目录
       
        //$f = md5( strtolower( $key ) );
        $f = parse_url($key)['host'];

        $a = $dir . '/' . $f . '.txt';
        $t = 2592000; // 缓存有效期30天, 这里单位:秒
        if ( !is_file($a) || (time() - filemtime($a)) > $t ) {
            return null;
        }
        else { 
            return file_get_contents($a);
        }

    }

    /**
     * 设置缓存
     *
     * @param $key
     * @param $value
     * @param $expire
     */
    public static function set($key, $value, $expire)
    {

        $dir = 'cache'; //图标缓存目录
        
        //$f = md5( strtolower( $key ) );
        $f = parse_url($key)['host'];

        $a = $dir . '/' . $f . '.txt';
        
        //如果缓存目录不存在则创建
        if (!is_dir($dir)) mkdir($dir,0777,true) or die('创建缓存目录失败！');

        $t = 2592000; // 缓存有效期30天, 这里单位:秒
        if ( !is_file($a) || (time() - filemtime($a)) > $t ) {
            $imgdata = fopen($a, "w") or die("Unable to open file!");  //w  重写  a追加
            fwrite($imgdata, $value);
            fclose($imgdata); 
        }
        
    }

}