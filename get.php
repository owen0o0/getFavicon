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


/* ------ 参数设置 ------ */

$defaultIco='favicon.png';   //默认图标路径
$expire = 2592000;           //缓存有效期30天, 单位为:秒，为0时不缓存

/* ------ 参数设置 ------ */



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

if($expire == 0){
    $favicon->getFavicon($formatUrl, false);
    exit;
}
else{
    $defaultMD5 = md5(file_get_contents($defaultIco));
    if (Cache::get($formatUrl,$defaultMD5,$expire) !== NULL) {
        foreach ($favicon->getHeader() as $header) {
            @header($header);
        }
        echo Cache::get($formatUrl,$defaultMD5,$expire);
        exit;
    }

    /**
     * 缓存中没有指定的内容时, 重新获取内容并缓存起来
     */
    $content = $favicon->getFavicon($formatUrl, TRUE);
    
    if( md5($content) == $defaultMD5 ){
        $expire = 43200; //如果返回默认图标，设置过期时间为12小时。Cache::get 方法中需同时修改
    }

    Cache::set($formatUrl, $content, $expire);

    foreach ($favicon->getHeader() as $header) {
        @header($header);
    }

    echo $content;
    exit;
}


/**
 * 缓存类
 */
class Cache
{
    /**
     * 获取缓存的值, 不存在时返回 null
     *
     * @param $key
     * @param $default  默认图片
     * @param $expire   过期时间
     * @return string
     */
    public static function get($key, $default, $expire)
    {
        $dir = 'cache'; //图标缓存目录
       
        //$f = md5( strtolower( $key ) );
        $f = parse_url($key)['host'];

        $a = $dir . '/' . $f . '.txt';

		$data = file_get_contents($a);
    	if( md5($data) == $default ){
        	$expire = 43200; //如果返回默认图标，过期时间为12小时。
    	}
        if ( !is_file($a) || (time() - filemtime($a)) > $expire ) {
            return null;
        }
        else { 
            return $data;
        }

    }

    /**
     * 设置缓存
     *
     * @param $key
     * @param $value
     * @param $expire   过期时间
     */
    public static function set($key, $value, $expire)
    {
        $dir = 'cache'; //图标缓存目录
        
        //$f = md5( strtolower( $key ) );
        $f = parse_url($key)['host'];

        $a = $dir . '/' . $f . '.txt';
        
        //如果缓存目录不存在则创建
        if (!is_dir($dir)) mkdir($dir,0777,true) or die('创建缓存目录失败！');

        if ( !is_file($a) || (time() - filemtime($a)) > $expire ) {
            $imgdata = fopen($a, "w") or die("Unable to open file!");  //w  重写  a追加
            fwrite($imgdata, $value);
            fclose($imgdata); 
        }
        
    }

}