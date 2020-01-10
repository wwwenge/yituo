<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\Opencart;

use Yituo\Core\ServiceContainer;

/**
 * Class Application.
 *
* @property \Yituo\Opencart\Category\Client                            $category
* @property \Yituo\Opencart\Category\MulitClient                       $multi_categories
* @property \Yituo\Opencart\Items\Client                               $items
* @property \Yituo\Opencart\Items\MultiClient                          $mulit_items
* @property \Yituo\Opencart\Order\Client                               $order
* @property \Yituo\Opencart\Order\MulitClient                          $mulit_orders
*/
class Application extends ServiceContainer {
    protected $providers = [
        Items\ServiceProvider::class,
        Category\ServiceProvider::class,
        Order\ServiceProvider::class,
    ];
}