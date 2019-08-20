# 安装


## 环境要求

> - PHP >= 7.0
> - [PHP cURL 扩展](http://php.net/manual/en/book.curl.php)
> - [PHP OpenSSL 扩展](http://php.net/manual/en/book.openssl.php)
> - [PHP fileinfo 拓展](http://php.net/manual/en/book.fileinfo.php)

## 安装

使用 [composer](http://getcomposer.org/):

```shell
$ composer require wengoooo/yituo
```

## 快速开始

> 配置
```php
require_once('vendor/autoload.php');
use Symfony\Component\Cache\Adapter\RedisAdapter;

$config = [
    'client_id' => 'xxxxxxxxxxxxxxxxxxxxxxx',
    'client_secret' => 'xxxxxxxxxxxxxxxxxxxxxxxx',
    'response_type' => 'array',
    'oauth' => [
        'redirect_uri' => $redirect_uri, # 验证跳转uri
        'username' => $username, # 商店登录名
        'password' => $password, # 商店登录密码
        'shop_name' => '19pop' # 商店名字
    ],
    'http' => [
        'max_retries' => 3,
        'retry_delay' => 500,
        'timeout' => 60,
        'debug' => true,
        'base_uri' => 'https://api.thebase.in/',
    ],
];

// 创建 redis 实例
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$app = Yituo\Factory::TheBase($config);
// 创建缓存实例
$cache = new RedisAdapter($redis);
// 替换应用中的缓存
$app->rebind('cache', $cache);

```

> 初始化Access Token
```php
$app->oauth->initializeToken(); 
```

> 获取用户信息
```php
print_r($app->user->getUser());
```

> 目录操作
```php
$app->category->getCategories();
$app->category->editCategory($category_id);
$app->category->deleteCategory($category_id);
```

> Item操作
```php
$app->items->getItems(['limit' => 5]);
$app->items->search($keyword);
$app->items->getItem($item_id);
$app->items->addItem($title, $price, $stock);
$app->items->editItem($item_id);
$app->items->deleteItem($item_id);
$app->items->addImage($item_id, $image_no, $image_url);
$app->items->deleteImage($item_id, $image_no);
$app->items->editStock($item_id, $stock, $variation_id, $variation_stock);
$app->items->deleteVariation($item_id, $variation_id);
```

> item to categories操作
```php
$app->item_categories->getItemToCagegories($item_id);
$app->item_categories->addItemToCagegories($item_id);
$app->item_categories->deleteItemToCagegories($item_category_id);
```

> orders操作
```php
$app->order->getOrders($item_id);
$app->order->getOrderDetail($uniqueKey);
$app->order->updateOrder($order_item_id, $status, $add_comment, $atobarai_status, $delivery_company_id, $tracking_number);
```
