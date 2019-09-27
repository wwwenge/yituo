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
// 替换缓存
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
$app->order->getOrders();
$app->order->getOrderDetail($uniqueKey);
$app->order->updateOrder($order_item_id, $status, $add_comment, $atobarai_status, $delivery_company_id, $tracking_number);
```


## 并发请求
> categories操作
```php
$app->mulit_categories->addCategories([['name' => 'bbb','parent_id' => '0'], ['name' => 'cccc','parent_id' => '0']]);
$app->mulit_categories->deleteCategories[['id' => "1921361"], ['id' => "1921361"]]);
var_dump($app->mulit_categories->getCategories());
print_r($app->mulit_categories->sortCategories([
    [ 'id' => 1941924, 
      'list_order' => 1, 
      'children' => [
            ['id' => 1941927, 'list_order' => 1], 
            ['id' => 1941928, 'list_order' => 2]
      ]
     ], 
     [ 'id' => 1941925, 
       'list_order' => 2, 
       'children' => [
                 ['id' => 1941929, 'list_order' => 1], 
                 ['id' => 1941930, 'list_order' => 2]
       ]
     ],
     [ 'id' => 1941926, 
       'list_order' => 3
     ]
  ]
));

```
> items操作
```php
var_dump($app->mulit_items->getIds());
var_dump($app->mulit_items->getItems([['item_id' => '23124163'], ['item_id' => '23124163']]));
$app->mulit_items->getItems([['item_id' => '23124163'], ['item_id' => '23124163']], function(GuzzleHttp\Psr7\Response $response, $index) {
    var_dump((string)$response->getBody());
});
print_r($app->mulit_items->getAvailableIds());
print_r($app->mulit_items->createItemIds(100));
print_r($app->mulit_items->searchItems('fakeproduct'));
$app->mulit_items->sortItems(['item_ids' => ["23120451", "23061078", "23086207", "23086212", "23085510"]]);
$app->mulit_items->deleteItems(['item_ids' => ["23123894", "23120451"]]);
var_dump($app->mulit_items->uploadImages(['H:/2.jpg', 'H:/3.jpg']));

$app->mulit_items->addItems([array (
    'item' =>
        array (
            'variations' =>
                array (
                    0 =>
                        array (
                            'id' => '',
                            'name' => 'ブラック/M',
                            'stock' => '1000',
                        ),
                    1 =>
                        array (
                            'id' => '',
                            'name' => 'ブラック/L',
                            'stock' => '1000',
                        ),
                ),
            'visible' => true,
            'stock' => 0,
            'price' => '1111',
            'update_price' => NULL,
            'item_tax_type' => 'standard',
            'detail' => '',
            'images' =>
                array (
                ),
            'name' => 'MulitTest6',
            'top_of_list' => true,
            'apps' =>
                array (
                    'category' =>
                        array (
                            'enabled' =>
                                array (
                                    0 =>
                                        array (
                                            'id' => '1921179',
                                            'parents' =>
                                                array (
                                                ),
                                        ),
                                ),
                        ),
                    'label' =>
                        array (
                        ),
                    'quantity_limit' =>
                        array (
                        ),
                    'sale' =>
                        array (
                            'discount_rate' => 0,
                        ),
                    'shipping_fee' =>
                        array (
                            'enabled' =>
                                array (
                                ),
                        ),
                    'subscription' =>
                        array (
                        ),
                    'digital' =>
                        array (
                        ),
                    'club_t' =>
                        array (
                        ),
                    'sp_case' =>
                        array (
                        ),
                    'sales_period' =>
                        array (
                        ),
                    'pre_order' =>
                        array (
                        ),
                ),
        ),
), array (
    'item' =>
        array (
            'variations' =>
                array (
                    0 =>
                        array (
                            'id' => '',
                            'name' => 'ブラック/M',
                            'stock' => '1000',
                        ),
                    1 =>
                        array (
                            'id' => '',
                            'name' => 'ブラック/L',
                            'stock' => '1000',
                        ),
                ),
            'visible' => true,
            'stock' => 0,
            'price' => '1111',
            'update_price' => NULL,
            'item_tax_type' => 'standard',
            'detail' => '',
            'images' =>
                array (
                ),
            'name' => 'MulitTest7',
            'top_of_list' => true,
            'apps' =>
                array (
                    'category' =>
                        array (
                            'enabled' =>
                                array (
                                    0 =>
                                        array (
                                            'id' => '1921179',
                                            'parents' =>
                                                array (
                                                ),
                                        ),
                                ),
                        ),
                    'label' =>
                        array (
                        ),
                    'quantity_limit' =>
                        array (
                        ),
                    'sale' =>
                        array (
                            'discount_rate' => 0,
                        ),
                    'shipping_fee' =>
                        array (
                            'enabled' =>
                                array (
                                ),
                        ),
                    'subscription' =>
                        array (
                        ),
                    'digital' =>
                        array (
                        ),
                    'club_t' =>
                        array (
                        ),
                    'sp_case' =>
                        array (
                        ),
                    'sales_period' =>
                        array (
                        ),
                    'pre_order' =>
                        array (
                        ),
                ),
        ),
)]);

$app->mulit_items->editItems([array (
    'item_id' => '23124163',
    'item' =>
        array (
            'variations' =>
                array (
                    0 =>
                        array (
                            'id' => '',
                            'name' => 'ブラック/XL',
                            'stock' => '1000',
                        ),
                    1 =>
                        array (
                            'id' => '',
                            'name' => 'ブラック/L',
                            'stock' => '1000',
                        ),
                ),
            'visible' => true,
            'stock' => 0,
            'price' => '1111',
            'update_price' => NULL,
            'item_tax_type' => 'standard',
            'detail' => '',
            'images' =>
                array (
                ),
            'name' => 'MulitTest6',
            'top_of_list' => true,
            'apps' =>
                array (
                    'category' =>
                        array (
                            'enabled' =>
                                array (
                                    0 =>
                                        array (
                                            'id' => '1921179',
                                            'parents' =>
                                                array (
                                                ),
                                        ),
                                ),
                        ),
                    'label' =>
                        array (
                        ),
                    'quantity_limit' =>
                        array (
                        ),
                    'sale' =>
                        array (
                            'discount_rate' => 0,
                        ),
                    'shipping_fee' =>
                        array (
                            'enabled' =>
                                array (
                                ),
                        ),
                    'subscription' =>
                        array (
                        ),
                    'digital' =>
                        array (
                        ),
                    'club_t' =>
                        array (
                        ),
                    'sp_case' =>
                        array (
                        ),
                    'sales_period' =>
                        array (
                        ),
                    'pre_order' =>
                        array (
                        ),
                ),
        ),
), array (
    'item_id' => '23124164',
    'item' =>
        array (
            'variations' =>
                array (
                    0 =>
                        array (
                            'id' => '',
                            'name' => 'ブラック/M',
                            'stock' => '1000',
                        ),
                    1 =>
                        array (
                            'id' => '',
                            'name' => 'ブラック/L',
                            'stock' => '1000',
                        ),
                ),
            'visible' => true,
            'stock' => 0,
            'price' => '1111',
            'update_price' => NULL,
            'item_tax_type' => 'standard',
            'detail' => '',
            'images' =>
                array (
                ),
            'name' => 'MulitTest7',
            'top_of_list' => true,
            'apps' =>
                array (
                    'category' =>
                        array (
                            'enabled' =>
                                array (
                                    0 =>
                                        array (
                                            'id' => '1921179',
                                            'parents' =>
                                                array (
                                                ),
                                        ),
                                ),
                        ),
                    'label' =>
                        array (
                        ),
                    'quantity_limit' =>
                        array (
                        ),
                    'sale' =>
                        array (
                            'discount_rate' => 0,
                        ),
                    'shipping_fee' =>
                        array (
                            'enabled' =>
                                array (
                                ),
                        ),
                    'subscription' =>
                        array (
                        ),
                    'digital' =>
                        array (
                        ),
                    'club_t' =>
                        array (
                        ),
                    'sp_case' =>
                        array (
                        ),
                    'sales_period' =>
                        array (
                        ),
                    'pre_order' =>
                        array (
                        ),
                ),
        ),
)]);
```

> orders操作
```php
var_dump($app->mulit_orders->getOrdersId(['limit' => 50,
        'words' => "",
        'order_by' => "ordered_desc",
        'status' => [],
        'payment' => [],
        'order_type' => 'all'
        ]));
var_dump($app->mulit_orders->getOrders(['unique_keys' => ['0B14AF0F2EEC413C']]));


```