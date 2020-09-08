<?php
/**
 * 公共函数类
 */

namespace woodlc\FnptOpenSdk;


class Common
{
    /**
     * http请求函数
     * @param $url
     * @param null $data
     * @return mixed
     */
    public static function httpRequest($url, $data = null)
    {

        self::SaveLog('*************************请求消息********************');
        self::SaveLog($data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('multipart/form-data'));
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($curl);
        curl_close($curl);
        self::SaveLog('*************************返回消息********************');
        self::SaveLog(self::unicode2Chinese($output));
        return $output;
    }

    /**
     * php中unicode转中文
     * @param $str
     * @return null|string|string[]
     */
    public static function unicode2Chinese($str)
    {
        return preg_replace_callback("#\\\u([0-9a-f]{4})#i",
            function ($r) {return iconv('UCS-2BE', 'UTF-8', pack('H4', $r[1]));},
            $str);
    }

    /**
     * 数组转json函数
     * @param array $arr
     * @param int $option
     * @return null|string|string[]
     */
    public static function jsonEncode($arr = array(), $option = '')
    {
        if ($option == 'JSON_UNESCAPED_UNICODE') {
            if (version_compare(PHP_VERSION,'5.4.0','>=')) {
                return json_encode($arr, JSON_UNESCAPED_UNICODE);
            } else {
                return Common::decodeUnicode(json_encode($arr));
            }
        } else if ($option) {
            return json_encode($arr, $option);
        } else {
            return json_encode($arr);
        }
    }

    /**
     * 解码unicode
     * @param $string
     * @return null|string|string[]
     */
    public static function decodeUnicode($string)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function(
            '$matches',
            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
        ), $string);
    }


    /**
     * 格式化参数格式化成url参数
     * @param $values
     * @return string
     */
    public static function toUrlParams($values)
    {
        $buff = '';
        foreach ($values as $k => $v) {
//            if ($k != 'sign' && $v != '' && !is_array($v)) {
            if (!is_array($v)) {
                $buff .= $k . '=' . $v . '&';
            }
        }
        return trim($buff, '&');
    }


    /**
     * 记录日志
     * @param $data
     * @param string $file
     */
    public static function SaveLog($data,$file = '')
    {
        if($file == ''){
            $file = dirname(__DIR__).'/logs/'.date('Ym').'/'.date('d').'.log';
        }
        $dir_path = dirname($file);
        if(!is_dir($dir_path)){
            @mkdir($dir_path,755,true);
        }
        if(is_object($data)){
            $content = json_encode(objectToArray($data),JSON_UNESCAPED_UNICODE);
        }elseif (is_array($data)){
            $content = json_encode($data,JSON_UNESCAPED_UNICODE);
        }else{
            $content = $data;
        }
        file_put_contents($file,date('Y-m-d H:i:s')."\t".$content.PHP_EOL,FILE_APPEND); //通知记录
    }

    /**
     * 获取文件后缀
     * @param $file
     * @return bool|string
     */
    public static function get_extension($file)
    {
        return substr($file, strrpos($file, '.')+1);
    }

}