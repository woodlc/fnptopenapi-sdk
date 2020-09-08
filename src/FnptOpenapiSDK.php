<?php
/**
 *
 * Created by PhpStorm.
 * User: lc
 * Date: 2020-09-07 15:38
 */

namespace woodlc\FnptOpenSdk;

include "Common.php";

class FnptOpenapiSDK
{
    //私有属性，用于保存实例
    private static $instance;

    private $apiHost = '';
    private $appId = '';
    private $secretKey = '';
    private $accessToken = '';
    protected $cache = null;


    const API_HOST_DEBUG = 'http://isv-pt.alta.elenet.me';  //联调环境的url:
    const API_HOST = 'https://pt.ele.me';   //正式环境
    const ACCESS_TOKEN_PATH = '/openapi/isv/getauthtoken';  //授权页获取凭证接口(AuthToken)      http://isv-pt.alta.elenet.me/docs#/devdoc_token
    const OAUTH_PATH = '/static/open/oauth-pc/';    //授权页url生成
    const TOKEN_PATH = '/openapi/isv/gettoken'; //接口请求获取凭证接口(Token)
    const GET_AMOUNT_PATH = '/openapi/isv/getamount';   //查询余额接口

    const GET_SHOP_LIST_PATH = '/openapi/isv/getshoplist';   //查询门店列表接口
    const EDIT_SHOP_PATH = '/openapi/isv/modifyshopinfo';   //修改门店信息接口
    const ADD_SHOP_PATH = '/openapi/isv/createshop';   //创建门店&新增门店接口
    const GET_GOODS_CATEGORY_PATH = '/openapi/isv/getshopscategorylist';   //获取商户品类列表接口

    const UPLOAD_FILE_PATH = '/openapi/isv/uploadfile';   //用户上传图片接口

    const ORDER_CANCEL_PRICE_PATH = '/openapi/isv/getcancelprice';   //订单预取消接口    取消前调用这个接口, 获取取消价格
    const ORDER_CANCEL_MESSAGE_PATH = '/openapi/isv/getordercancelmessage';   //获取取消原因列表接口
    const ORDER_CANCEL_PATH = '/openapi/isv/cancelorder';   //订单取消接口
    const GET_AVAILAVLE_PRODUCT_LIST = '/openapi/isv/getavailableproductlist';   //询标品接口
    const GET_ORDER_PRICE = '/openapi/isv/getorderprice';   //询价接口
    const GET_CREATE_ORDER = '/openapi/isv/createorder';   //创建订单接口

    const GET_GOODS_PACKAGE_PATH = '/openapi/isv/getgoodsinsurancepackage';   //获取货损险套餐列表接口
    const GET_INSUREDPER_INFO_PATH = '/openapi/isv/getinsuredpersoninfo';   //获取投保人信息接口
    const PREINSURANCE_PATH = '/openapi/isv/preinsurance';   //核保接口
    const ADD_TIPS_PATH = '/openapi/isv/addtip';   //订单加调度费接口
    const GET_ORDER_DETAIL_PATH = '/openapi/isv/getorderdetail';   //查询订单详情接口
    const GET_KNIGHT_INFO_PATH = '/openapi/isv/getknightinfo';   //查询骑手信息接口


    //公有方法，用于获取实例
    public static function getInstance()
    {
        //判断实例有无创建，没有的话创建实例并返回，有的话直接返回
        if(!(self::$instance instanceof self)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setConfig($config = [])
    {
        $this->appId = $config['app_id'];
        $this->secretKey = $config['secret_key'];
        $this->apiHost = $config['debug'] === true ? self::API_HOST_DEBUG : self::API_HOST;
        $config['cache'] = isset($config['cache']) ? $config['cache'] : [];
        $this->cache = new Cache($config['cache']);
    }


    /**
     * 授权页获取凭证接口
     * @return string
     */
    public function getAuthToken()
    {
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time()
        ];
        $para = [
            'appid' => $this->appId,
            'time' => $data['time']
        ];
        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::ACCESS_TOKEN_PATH ;
        $res = Common::httpRequest($url,$data);
        return $this->hlandData($res);
    }


    /**
     * 接口请求获取凭证接口(Token)
     * @param $user_id  账号id
     * @param int $refresh 0:不需要重新生成 1:需要重新生成   默认:0
     * @return mixed
     */
    public function getToken($user_id,$refresh = 0)
    {
        $token_data = $this->cache->get('FnptOpenSdk_token_'.$user_id);
        if($refresh == 1 || !$token_data){
            $data = [
                'appid' => $this->appId,
                'secret_key' => $this->secretKey,
                'time' => time()
            ];
            $para = [
                'appid' => $this->appId,
                'time' => $data['time'],
                'user_id' => $user_id,
                'refresh' => $refresh
            ];
            $data['sign'] = $this->getSign($data,$para);
            $data['para'] = Common::jsonEncode($para);
            $url = $this->apiHost . self::TOKEN_PATH ;
            $res = Common::httpRequest($url,$data);
            $res = $this->hlandData($res);
            $token_data = json_decode($res,true);
            if(!empty($token_data['token'])){
                $this->cache->set('FnptOpenSdk_token_'.$user_id,$token_data,$token_data['token_expire_at']-time());
                return $token_data['token'];
            }
            return false;
        }
        return $token_data['token'];
    }

    /**************************业务类接口***********************************/

    /**
     * 查询余额接口
     * @param $user_id
     * @return mixed
     */
    public function getAmount($user_id)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = [
            'user_id' => $user_id
        ];
        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::GET_AMOUNT_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }


    /**
     * 查询门店列表接口
     * @param $user_id
     * @param $token
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function getShopList($user_id,$page = 1,$pageSize = 20)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = [
            'user_id' => $user_id,
            'cur_page' => $page,
            'per_page' => $pageSize,
        ];
        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::GET_SHOP_LIST_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }

    /**
     * 修改门店信息接口
     * @param $user_id  用户id
     * @param $token    凭证
     * @param $shopData 业务参数(para):
     * 参数名	类型	是否必须	描述	示例
    user_id	string	y	用户id	12315
    appid	string	y	appid	testappid
    shop_id	string	y	门店id	150008167
    shop_detail_address	string		门牌号	X号楼X层XX号
    shop_poi_address	string	y	经纬度地址,修改了poi地址之后，接入方必须保证该poi地址对应的正确的经度和维度，经度和维度需要和poi地址同时修改	XX省XX市XX区 XX街道 XX号
    shop_name	string	y	商店名	Apollo蜂鸟专送测试餐厅-Alta北京
    shop_phone	string	y	商户手机号	15045631096
    shop_longitude	string	y	经度，和poi地址相对应，需要高德坐标系	117.262604
    shop_latitude	string	y	维度，和poi地址相对应，需要高德坐标系	40.320211
    shop_category	int	y	商户品类	110
     *
     * @return mixed
     */
    public function editShopInfo($user_id,$shopData)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = $shopData;
        $para['user_id'] = $user_id;
        $para['appid'] = $this->appId;
        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::EDIT_SHOP_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }

    /**
     * 查询门店信息接口
     * desc 获取指定门店的详细信息。
     * @param $user_id
     * @param $shop_id
     * @return mixed
     */
    public function getShopDetail($user_id,$shop_id)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = [
            'user_id' => $user_id,
            'shop_id' => $shop_id
        ];
        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::EDIT_SHOP_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }

    /**
     * 创建门店&新增门店接口
     *            这里传入的参数最多，具体参数见下面列出，可能后面会增加参数。同时还涉及到调用我们的图片上传的接口，会返回图片的hash值和url。需要注意，我们这边创建门店和新增门店都是这一个接口，但是在新增门店的时候，我们这边会根据： APPID区分独立/统一结算,  这个接口只针对统一结算的渠道提供新增门店服务 ，独立结算需要在跑腿app上面创建门店之后再来isv绑定。
     * @param $user_id
     * @param $token
     * @param $shopData
     *
     * 参数名	类型	是否必传	描述	示例
    user_id	string	y		12315
    appid	string	y	appid，接入成功之后这边会给出这个标识	testappid
    out_shop_id	string	y	外部门店id，接入方的门店id	150008167
    shop_name	string	y	商店名	蜂鸟专送测试餐厅
    shop_phone	string	y	商户手机号	15045631096
    id_card_need_cert			是否需要验证身份证号码	否，默认false
    shop_poi_address	string	y	经纬度地址	平谷桃花海-天云山玻璃栈道
    shop_detail_address	string	y	具体地址	平谷桃花海-天云山玻璃栈道
    shop_longitude	string	y	商户经度(高德坐标系)	117.262604
    shop_latitude	string	y	商户纬度(高德坐标系)	40.320211
    shop_category	string	y	商户品类	110
    shop_owner_name	string	y	商店拥有者姓名	张三
    shop_owner_idcard	string	y	身份证号	50013445930629842003
    shop_owner_idcard_hash	string	y	调用图片上传身份证后，接口后返回的hash	d7c64022f6458f9aa76968e01f5686c5jpeg
    shop_owner_idcard_url	string	y	返回的url	https://fuss.ar.elenet.me/d/7c/64022f6458f9aa76968e01f5686c5jpeg.jpeg
    su_code	string	y	统一社会信用代码	43566
    business_licence_hash	string	y	营业执照的hash，调用图片上传接口  // TODO： 不一定有	同上
    business_licence_url	string	y	图片接口返回的url	同上
    food_license_pic_hash	string	n	食品安全执照的hash，调用图片上传接口	接入方如果没有这个执照，就不要传这个参数，连key都不要传
    food_license_pic_url	string	n	图片接口返回的url	接入方如果没有这个执照，就不要传这个参数，连key都不要传
     *
     *
     * @return mixed
     */
    public function addShop($user_id,$shopData)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = $shopData;
        $para['user_id'] = $user_id;
        $para['appid'] = $this->appId;
        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::ADD_SHOP_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }


    /**
     * 获取商户品类列表接口
     * @param $user_id
     * @param $token
     * @return mixed
     */
    public function  getGoodsCategory($user_id)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = [];
        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::GET_GOODS_CATEGORY_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }

    /**
     *用户上传图片接口
     * @param $user_id
     * @param $token
     * @param $file
     * @return mixed
     */
    public function upload_file($user_id,$file)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $file_type = Common::get_extension($file);
        $para = [
            'file_type' => $file_type,
            'file_binary' => base64_encode(file_get_contents($file))
        ];
        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::UPLOAD_FILE_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }

    /**
     * 订单预取消接口
     * @param $user_id
     * @param $order_no
     * @param $order_status
     * @param $order_reason_code
     * @return mixed
     */
    public function getCancelPrice($user_id,$order_no,$order_status,$order_reason_code)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = compact('order_no','order_status','order_reason_code','user_id');
        $para['appid'] = $this->appId;

        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::ORDER_CANCEL_PRICE_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }

    /**
     * 获取取消原因列表接口（取消文案的返回）
     * @param $user_id
     * @param $order_no
     * @return mixed
     */
    public function getOrderCancelMessage($user_id,$order_no)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = compact('order_no','user_id');

        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::ORDER_CANCEL_MESSAGE_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }


    /**
     * 订单取消接口
     * @param $user_id
     * @param $order_no 跑腿这边的订单号
     * @param $cancel_charge    预取消接口返回的价格，单位都是分
     * @param $cancel_reason    取消原因列表接口返回的文案，选填，
     * @param $other_reason 其他原因，需要用户手动输入
     * @return mixed
     */
    public function CancelOrder($user_id,$order_no,$cancel_charge,$cancel_reason,$other_reason)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = compact('order_no','user_id','cancel_charge','cancel_reason','other_reason');
        $para['appid'] = $this->appId;

        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::ORDER_CANCEL_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }


    /**
     * 询标品接口
     * @param $user_id
     * @param $shop_id
     * @param $customer_lon 收件人经度
     * @param $customer_lat 收件人纬度
     * @param $expect_fetch_time    期望取货时间戳
     * @return mixed
     */
    public function getAvailableProductList($user_id,$shop_id,$customer_lon,$customer_lat,$expect_fetch_time)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = compact('shop_id','customer_lon','customer_lat','expect_fetch_time');

        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::GET_AVAILAVLE_PRODUCT_LIST ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }

    /**
     * 询价接口
     * @param $user_id
     * @param $shop_id
     * @param $orderData
     *
     * 参数名	类型	是否必传	描述
    user_id	string	y	用户id
    shop_id	string	y	门店id
    coupon_id	string	y	询价指定的优惠券id
    -1: 本次询价不指定优惠券
    0：使用默认的优惠券
    其他有效的coupon_id: 使用指定coupon_id的优惠券
    pk_id	string	n	若指定优惠券id, 则需要传入上次询价返回的pk_id
    product_id	string	y	标品id
    customer_lon	string	y	收件人经度
    customer_lat	string	y	收件人纬度
    expect_fetch_time	string	y	期望取货时间戳
    goods_weight	string	y	货品重量(g)
    goods_price	string	y	货品价格, 单位:分 (注意: 需要和提单时传参的值一致)
    order_tip	int	y	订单小费, 单位:分  (注意: 需要和提单时传参的值一致)
    order_source	string	n	订单来源(美团/饿了么/天猫), 详见下方接口说明的枚举
    insure_busi_order_no	string	n	核保单号，如果有购买货损险，传入核保接口返回的核保单号
     *
     * @return mixed
     */
    public function getOrderPrice($user_id,$shop_id,$orderData)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = $orderData;
        $para['shop_id'] = $shop_id;
        $para['user_id'] = $user_id;

        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::GET_ORDER_PRICE ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }


    /**
     * 创建订单接口
     * @param $user_id
     * @param $shop_id
     * @param $orderData
     *
     * 参数名	类型	是否必传	描述	示例
    user_id	string	y	user_id
    out_order_no	string	y	接入方的订单号, 接入方需要保证每次请求传入的out_order_no不重复	1234567
    coupon_id	string	y	询价指定的优惠券id
    -1: 本次询价不指定优惠券
    0：使用默认的优惠券
    其他有效的coupon_id: 使用指定coupon_id的优惠券	11
    shop_id	string	y	门店id	345
    customer_tel	string	y	收货人手机号	13304940231
    customer_ext_tel	string	n	收货人分机号
    customer_addr	string	y	收货人详细地址	X号楼X层XX号
    customer_poi_addr	string	y	收货人poi地址(高德坐标系)	XX省XX市XX区 XX街道 XX号
    customer_longtitude	string	y	收货人经度(高德坐标系)	参数名定义时单词拼写有误, 实际传参请按照文档为准
    customer_latitude	string	y	收货人纬度(高德坐标系)
    customer_name	string	y	收货人姓名
    total_price	string	y	订单总价(分)
    pay_price	string	y	实付金额(分)
    order_source	string	n	订单来源(美团/饿了么/天猫), 详见下方接口说明的枚举
    product_id	string	y	标品id
    goods_weight	string	y	货品重量(单位:g)
    order_price_detail_json	string	y	询价接口返回的 order_price_detail_json 字段
    order_source_id	string	n	订单来源处的单号
    order_tip	string	n	订单小费, 单位:分  (注意: 需要和提单时传参的值一致)
    expect_fetch_time	string	y	订单期望取货时间戳
    sn	string	n	订单小号
    t_indexid	string	y	询标品接口返回的标品对应的t_indexid
    goods_price	string	n	货品价格 单位:分 (注意: 需要和提单时传参的值一致)
    order_remark	string	n	订单备注, 长度<255字符
    insure_busi_order_no	string	n	核保单号,如果有购买货损险，传入核保接口返回的核保单号
    need_fetch_code	string	n	是否需要收货码，默认：0,需要时传入1。
    predict_duration	int	n	getavailableproductlist 接口返回的 predict_duration 字段, 建议传入(如果不传, 默认为45 , 会导致商户侧展示的预计送达时间不准确)	80
     *
     * @return mixed
     */
    public function createOrder($user_id,$shop_id,$orderData)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = $orderData;
        $para['shop_id'] = $shop_id;
        $para['user_id'] = $user_id;

        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::GET_CREATE_ORDER ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }

    /**
     * 获取货损险套餐列表接口
     * @param $user_id
     * @return mixed
     */
    public function getGoodsSinsuranceList($user_id)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = [
            'appid' => $this->appId,
            'user_id' => $user_id,
        ];
        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::GET_GOODS_PACKAGE_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }

    /**
     * 获取投保人信息接口
     * 该接口仅提供给独立结算的商户，统一结算不能访问该接口会存在冒用他人身份信息的风险。
     * @param $user_id
     * @return mixed
     */
    public function getSinsuranceInfo($user_id)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = [
            'appid' => $this->appId,
            'user_id' => $user_id,
        ];
        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::GET_INSUREDPER_INFO_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }

    /**
     * 核保接口
     * @param $user_id
     * @param $paraData
     *
     * 参数名	类型	是否必传	描述
    person_name	string	y	用户真实姓名
    person_idcard	string	y	身份证
    phone	string	y	手机号码
    insured_plan_id	string	y	选择的货损险套餐编号id，根据getgoodsinsurancepackage获取
    expect_fetch_time	string	y	期望取货时间戳
    goods_weight	string	y	货物重量(单位：kg)
    user_id	string	y	用户id
    appid	string	y	渠道appid
     *
     * @return mixed
     */
    public function PreinSurance($user_id,$paraData)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = $paraData;
        $para['appid'] = $this->appId;
        $para['user_id'] = $user_id;

        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::GET_INSUREDPER_INFO_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }


    /**
     * 订单加调度费接口
     * @param $user_id
     * @param $add_tip_price
     * @param $order_no
     * @param $business_sn
     * @return mixed
     */
    public function addTip($user_id,$add_tip_price,$order_no,$business_sn)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = compact('add_tip_price','order_no','business_sn');
        $para['appid'] = $this->appId;

        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::ADD_TIPS_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }

    /**
     * @param $user_id
     * @param $order_no
     * @return mixed
     */
    public function getOrderDetail($user_id,$order_no)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = compact('user_id','order_no');
        $para['appid'] = $this->appId;

        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::GET_ORDER_DETAIL_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }


    /**
     * 查询骑手信息接口
     * @param $user_id
     * @param $order_no
     * @return mixed
     */
    public function getKnightInfo($user_id,$order_no)
    {
        $token = self::getToken($user_id);
        $data = [
            'appid' => $this->appId,
            'secret_key' => $this->secretKey,
            'time' => time(),
            'user_id' => $user_id,
            'token' => $token
        ];
        $para = compact('user_id','order_no');
        $para['appid'] = $this->appId;

        $data['sign'] = $this->getSign($data,$para);
        $data['para'] = Common::jsonEncode($para);
        $url = $this->apiHost . self::GET_KNIGHT_INFO_PATH ;
        $res = Common::httpRequest($url,$data);
        $result = $this->hlandData($res);
        return $result;
    }


    /********************************************************************/


    /**
     * 授权页url生成
     * @param $auth_token
     * @param $auth_callback_url
     * @param $redirect_url
     * @return string
     */
    public function getOauthUrl($auth_token,$auth_callback_url,$redirect_url)
    {
        $appid = $this->appId;
        $data = compact('auth_token','appid','auth_callback_url','redirect_url');
        $sign = $this->getSign($data);
        foreach ($data as $k => $v)
        {
            $data[$k] = urlencode($v);
        }
        $data['sign'] = urlencode($sign);
        $params = urlencode(Common::toUrlParams($data));
        return $this->apiHost . self::OAUTH_PATH .'#params='.$params;
    }

    /**
     * @param $data     公参
     * @param array $para   业务参数
     * @return string
     */
    private function getSign($data,$para = [])
    {
        $data = array_merge($data,$para);   //数组合并
        ksort($data);
        $data = Common::toUrlParams($data);
        Common::SaveLog('*************************toUrlParams********************');
        Common::SaveLog($data);
        return md5(urlencode($data));
    }

    public function hlandData($data)
    {
        if(!is_array($data)){
            $data = json_decode($data,true);
        }
        if($data['errno'] == 0){
            if(!empty($data['data'])){
                return !is_array($data['data']) ? json_decode($data['data'],true) : $data['data'];
            }
            return true;
        }
        $error = !empty($data['errmsg']) ? $data['errmsg'] : 'Error';
        throw new \Exception($error);
    }
}