<?php
namespace Kenvix\curl;

/**
 * Kenvix cURL类
 * @version 6.0 @ 2018-08-22
 * @copyright (c) Kenvix
 * @see https://kenvix.com
 * Class wcurl
 */
class curl {
    /**
     * curl句柄
     * @var resource
     */
    public $conn;

    /**
     * 暂存的抓取结果
     * @var array|null
     */
    protected $response;

    /**
     * 内部重定向次数计数器
     * @var int
     */
    protected $redirectCounter = 0;

    /**
     * 构造函数，返回curl指针实例
     * @param string $file 网络文件 如果要使用POST提交文件，在文件路径前面加上@
     * @param array $head 可选，HTTP头
     * @throws \Exception
     */
    public function __construct($file = '', array $head = array()) {
        if (!function_exists('curl_exec')) {
            throw new \RuntimeException('Your php does not support curl!!!', -1);
        }
        $this->conn = curl_init();
        if($this->conn === FALSE) throw new \RuntimeException('failed to initialize curl', -1);
        $this->init($file, $head);
    }

    /**
     * 当wcurl类被当成字符串时的操作: 显示调试信息
     * @return string 返回值
     */
    public function __toString() {
        return "Curl request builder (redirected $redirectCounter times)";
    }

    /**
     * 设置自定义的 Method 来代替"GET"或"HEAD"
     * @param $method
     * @return $this
     */
    public function setRequestMethod($method) {
        $this->set(CURLOPT_CUSTOMREQUEST, $method);
        return $this;
    }

    /**
     * 设置一个cURL传输选项
     * @param string $option 需要设置的选项
     * @param string $value  将设置在option选项上的值
     * @return $this
     */
    public function set($option, $value) {
        curl_setopt($this->conn, $option, $value);
        return $this;
    }

    /**
     * 通过数组批量设置cURL传输选项
     * @param array $option 需要设置的选项
     * @return $this
     */
    public function setAll($option) {
        curl_setopt_array($this->conn, $option);
        return $this;
    }

    public function execRaw() {
        return curl_exec($this->conn);
    }

    /**
     * 执行curl并返回结果（不含Headers）
     * @return string 返回值
     * @param bool $follow 是否跟随重定向
     */
    public function exec($follow = true) {
        $this->response = self::separateHeadersAndContent($this->execRaw());
        if($follow) {
            while(!empty($this->response['header']['Location'])) {
                $this->redirectCounter++;
                $this->response = self::separateHeadersAndContent($this->setUrl($this->response['header']['Location'])->cleanResponse()->execRaw());
            }
        }
        return $this->response['content'];
    }

    /**
     * 获取文件内容（不含Headers）
     * @return string 获取的内容
     * @param bool $follow 是否跟随重定向
     */
    public function get($follow = true) {
        return $this->exec($follow);
    }

    /**
     * POST 提交数据并获取返回获取的内容（不含Headers）
     * @param $data array|string 提交的数据
     * @param bool $follow 是否跟随重定向
     * @return string 获取的内容
     */
    public function post($data, $follow = true) {
        $this->set(CURLOPT_POST, 1);
        $this->set(CURLOPT_POSTFIELDS, self::buildFields($data));
        return $this->exec($follow);
    }

    /**
     * PUT 并获取返回获取的内容（不含Headers）
     * @param $data array|string 可选。提交的数据
     * @param bool $follow 是否跟随重定向
     * @return string 获取的内容
     */
    public function put($data = null, $follow = true) {
        $this->set(CURLOPT_CUSTOMREQUEST, 'PUT');
        if(!is_null($data)) $this->set(CURLOPT_POSTFIELDS, self::buildFields($data));
        return $this->exec($follow);
    }

    /**
     * DELETE 并获取返回获取的内容（不含Headers）
     * @param $data array|string 可选。提交的数据
     * @param bool $follow 是否跟随重定向
     * @return string 获取的内容
     */
    public function delete($data = null, $follow = true) {
        $this->set(CURLOPT_CUSTOMREQUEST, 'DELETE');
        if(!is_null($data)) $this->set(CURLOPT_POSTFIELDS, self::buildFields($data));
        return $this->exec($follow);
    }

    /**
     * HEAD 并以数组返回抓取到的Headers
     * @param bool $follow 是否跟随重定向
     * @return mixed
     */
    public function head($follow = true) {
        $this->setMethodToHead()->exec($follow);
        return $this->response['header'];
    }


    /**
     * 获取之前运行过的抓取结果
     * @return null|array
     */
    public function getResponse() {
        return $this->response;
    }

    public function cleanResponse() {
        $this->response = null;
        return $this;
    }

    /**
     * 获取重定向次数
     * @return int
     */
    public function getRedirectNum() {
        return $this->redirectCounter;
    }

    /**
     * 获取HTTP码
     * @return string
     */
    public function getHTTPCode() {
        return $this->getInfo(CURLINFO_HTTP_CODE);
    }

    /**
     * 获取HTTP头
     * @return string
     */
    public function getHeaders() {
        return $this->response['header'];
    }

    /**
     * 获取请求结果
     * @return mixed
     */
    public function getContent() {
        return $this->response['content'];
    }

    /**
     * 设置请求模式为HEAD
     * @return $this
     */
    public function setMethodToHead() {
        $this->set(CURLOPT_NOBODY, true);
        return $this;
    }

    /**
     * 分离普通请求得到的Headers和内容
     * @param $r string curl返回的原始数据
     * @return array
     */
    public static function separateHeadersAndContent($r) {
        $token  = strtok($r, "\n");
        $return = array('header' => array());
        $pos    = strlen($token) + 2;
        while(($length = strlen($token = strtok("\n"))) > 1) {
            list($key, $value) = explode(": ", $token);
            if($key == 'Set-Cookie')
                $return['header'][$key][] = $value;
            else
                $return['header'][$key] = trim($value);
            $pos += $length + 1;
        }
        if($token == "\r")
            $pos++;
        $return['content'] = substr($r, $pos);
        return $return;
    }

    /**
     * 添加一些Cookies，在访问的时候会携带它们
     * @param string|array $ck Cookies，数组或cookies字符串
     * @return $this
     */
    public function addCookie($ck) {
        if (is_array($ck)) {
            $r = '';
            foreach ($ck as $key => $value) {
                $r .= "{$key}={$value}; ";
            }
        } else {
            $r = $ck;
        }
        $this->set(CURLOPT_COOKIE, $r);
        return $this;
    }

    /**
     * 从已经获取到的网页获取其所有Cookies
     * @param array $setcookie SET-COOKIE HEADER ARRAY
     * @return array Cookies
     */
    public static function readCookies(array $setcookie) {
        $return = array();
        foreach ($setcookie as $id => $cookieEntry) {
            $data = explode("; ", $cookieEntry);
            if(isset($data[0])) {
                list($key, $value) = self::parseCookieKeyValue($data[0]);
                $return[$key]['key'] = $key;
                $return[$key]['value'] = $value;
                if(isset($data[1])) {
                    array_shift($data);
                    foreach ($data as $cookieOpt) {
                        if(strpos($cookieOpt, '=') !== false) {
                            list($optKey, $optValue) = self::parseCookieKeyValue($cookieOpt);
                            $return[$key][$optKey] = $optValue;
                        } else {
                            $return[$key][$cookieOpt] = true;
                        }
                    }
                }
            }
        }
        return $return;
    }

    private static function parseCookieKeyValue($str) {
        $pos   = strpos($str, '=');
        return array(
            substr($str, 0, $pos),
            substr($str, $pos + 1)
        );
    }

    /**
     * GET/POST获取网页返回的所有Cookies
     * @return array|bool Cookies
     */
    public function getCookies() {
        if(!isset($this->response['header']['Set-Cookie']))
            return false;
        return self::readCookies($this->response['header']['Set-Cookie']);
    }

    /**
     * 获取一个cURL连接资源句柄的信息
     * @param string $opt 要获取的信息，参见 http://cn2.php.net/manual/zh/function.curl-getinfo.php
     * @return string 信息
     */
    public function getInfo($opt) {
        return curl_getinfo($this->conn, $opt);
    }

    /**
     * 返回错误代码
     * @return string 错误代码
     */
    public function errno() {
        return curl_errno($this->conn);
    }

    /**
     * 返回错误信息
     * @return string 错误信息
     */
    public function error() {
        return curl_error($this->conn);
    }

    /**
     * 返回一个带错误代码的curl错误信息
     * @return string 错误信息
     */
    public function errMsg() {
        return '#' . $this->errno() . ' - ' . $this->error();
    }

    /**
     * 运行一个curl函数
     * @param string $func 函数名称，不需要带curl_
     * @param ... 其他传给此函数的参数
     * @return string 此函数的返回值
     */

    public function run($func) {
        $args = array_slice(func_get_args(), 1);
        return call_user_func_array('curl_'.$func, $args);
    }

    /**
     * 关闭并释放cURL资源
     */
    public function close() {
        @curl_close($this->conn);
    }

    /**
     * 静态 HTTP CURL GET 快速用法
     * @param string $url 要抓取的URL
     * @return string 抓取结果
     */
    public static function xget($url) {
        return (new self($url))->exec();
    }

    /**
     * 设置超时时间 单位:毫秒
     * @param int $time 超时时间
     * @return $this
     */
    public function setTimeOut($time) {
        //@see http://www.laruence.com/2014/01/21/2939.html
        $this->set(CURLOPT_NOSIGNAL, 1);
        $this->set(CURLOPT_TIMEOUT_MS , $time);
        return $this;
    }

    /**
     * 设置全部的HTTP Header
     * @param array $head
     * @return $this
     */
    public function setHeaders(array $head) {
        $this->set(CURLOPT_HTTPHEADER, $head);
        return $this;
    }

    /**
     * 设置URL
     * @param string $url 网络文件 如果要使用POST提交文件，在文件路径前面加上@
     * @return $this
     */
    public function setUrl($url) {
        $this->set(CURLOPT_URL, $url);
        return $this;
    }

    /**
     * 设置Referrer(Referer)
     * @param $ref
     * @return $this
     */
    public function setReferrer($ref) {
        $this->set(CURLOPT_REFERER, $ref);
        return $this;
    }

    /**
     * 是否允许自动跟随
     * @param bool $v
     * @return $this
     */
    public function allowAutoReferrer($v) {
        $this->set(CURLOPT_AUTOREFERER, $v);
        return $this;
    }

    /**
     * 重置会话句柄的所有的选项到LIBCURL默认值。要初始化到WCURL默认值，使用init()方法
     * @return $this
     */
    public function reset() {
        if(function_exists('curl_reset')){
            curl_reset($this->conn);
        } else {
            if(is_resource($this->conn)) curl_close($this->conn);
            $this->conn = curl_init();
            $this->cleanResponse();
        }
        return $this;
    }

    /**
     * 初始化wcurl或重置会话句柄的所有的选项到wcurl默认值
     * @param string $file
     * @param array  $head
     * @return $this
     */
    public function init($file = '', array $head = array('User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36')) {
        $this->reset();
        if(!empty($file)) $this->setUrl($file);
        $this->setHeaders($head)->setAll(array(//wcurl默认设定
            CURLOPT_RETURNTRANSFER  => true, //将curl获取的信息以文件流的形式返回，而不是直接输出
            CURLOPT_SSL_VERIFYPEER  => false, //cURL将终止从服务端进行验证
            CURLOPT_FOLLOWLOCATION  => false, //不要PHP CURL跟随重定向，重定向由本类自行处理
            CURLOPT_HEADER          => true //要求CURL输出headers
        ));
        return $this;
    }

    /**
     * 构建用于CURL的POST表单
     * @param string|array $data
     * @return string
     */
    public static function buildFields($data) {
        if (is_array($data)) {
            return http_build_query($data);
        } else {
            return $data;
        }
    }

    /**
     * 获取CURL连接
     * @return resource
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * 销毁类的时候自动释放cURL资源
     */
    public function __destruct() {
        $this->close();
    }
}
