## FnptOpenapiSDK
[![issue](https://img.shields.io/github/issues/woodlc/FnptOpenapiSDK.svg)](https://github.com/woodlc/FnptOpenapiSDK/issues)
[![star](https://img.shields.io/github/stars/woodlc/FnptOpenapiSDK.svg)](https://github.com/woodlc/FnptOpenapiSDK)
[![fork](https://img.shields.io/github/forks/woodlc/FnptOpenapiSDK.svg)](https://github.com/woodlc/FnptOpenapiSDK)
[![license](https://img.shields.io/github/license/woodlc/FnptOpenapiSDK.svg)](https://github.com/woodlc/FnptOpenapiSDK/issues/blob/master/LICENSE)

> A element fengniao php SDK. 一个蜂鸟跑腿 服务商 php开发包。

### 功能介绍

* 本项目为蜂鸟跑腿 开放平台api封装库，使用php语言实现，封装为composer包。

### 安装方法

1. 下载发行版

2. 使用composer安装（推荐）
* 请在项目根目录执行以下命令（请使用版本较新的composer，并设置好镜像源）

```
composer require woodlc/fnptopenapi-sdk
```


### 使用方法

* 以下为示例代码
```
/**
 * 使用案例
 * 注意：实际项目若使用composer安装的库，请先引入自动加载脚本（require __DIR__ . '/vender/autoload.php';）。另外需安装redis扩展并开启redis服务
 */
use woodlc\FnptOpenSdk\FnptOpenapiSDK;

// 设置中国时区（个别接口涉及时间数据）
date_default_timezone_set('PRC');

// 实例化Api对象
$fn = FnptOpenapiSDK::getInstance();

//配置
$config = [
    'app_id' => '',
    'secret_key' => '',
    'debug' => true //是否调试
];
$fn->setConfig($config);

$user_id = '';  //用户id
//$token = $fn->getToken($user_id,0);
//$list = $fn->getAmount($user_id);   //查询余额接口
//$list = $fn->getShopList($user_id);   //查询门店列表接口
//$list = $fn->editShopInfo($user_id,[]);   //修改门店信息接口
//$list = $fn->addShop($user_id,[]);   //创建门店&新增门店接口
//$list = $fn->getGoodsCategory($user_id);   //获取商户品类列表接口
//$list = $fn->upload_file($user_id,'');   //用户上传图片接口
//$list = $fn->getCancelPrice($user_id,'','','');   //订单预取消接口
//$list = $fn->getOrderCancelMessage($user_id,'');   //获取取消原因列表接口
//$list = $fn->CancelOrder($user_id,'','','','');   //订单取消接口
//$list = $fn->getAvailableProductList($user_id,'','','','');   //询标品接口
//$list = $fn->getOrderPrice($user_id,'',[]); //询价接口
//$list = $fn->createOrder($user_id,'',[]); //创建订单接口
//$list = $fn->getGoodsSinsuranceList($user_id);  //获取货损险套餐列表接口
//$list = $fn->getSinsuranceInfo($user_id);  //获取投保人信息接口
//$list = $fn->PreinSurance($user_id,[]);  //核保接口
//$list = $fn->addTip($user_id,'','','');  //订单加调度费接口
//$list = $fn->getOrderDetail($user_id,'');  //查询订单详情接口
//$list = $fn->getKnightInfo($user_id,'');  //查询骑手信息接口

```

### 仓库地址

[Github](https://github.com/woodlc/FnptOpenapiSDK "FnptOpenapiSDK")<br>

### 协议

[MIT](https://github.com/woodlc/FnptOpenapiSDK/blob/master/LICENSE "MIT")<br>