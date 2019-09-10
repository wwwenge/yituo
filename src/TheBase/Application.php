<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\TheBase;

use Yituo\Core\ServiceContainer;

/**
 * Class Application.
 *
* @property \Yituo\TheBase\Order\Client                               $order
* @property \Yituo\TheBase\Items\Client                               $items
* @property \Yituo\TheBase\Items\MulitClient                          $mulit_items
* @property \Yituo\TheBase\Category\MulitClient                       $mulit_categories
* @property \Yituo\TheBase\Order\MulitClient                          $mulit_orders
* @property \Yituo\TheBase\Category\Client                            $category
* @property \Yituo\TheBase\DeliveryCompany\Client                     $delivery_company
* @property \Yituo\TheBase\ItemCategories\Client                      $item_categories
* @property \Yituo\TheBase\Savings\Client                             $savings
* @property \Yituo\TheBase\User\Client                                $user
* @property \Yituo\TheBase\OAuth\Provider\BaseProvider                $oauth
*/
class Application extends ServiceContainer {
    protected $providers = [
        OAuth\ServiceProvider::class,
        Category\ServiceProvider::class,
        DeliveryCompany\ServiceProvider::class,
        ItemCategories\ServiceProvider::class,
        Savings\ServiceProvider::class,
        User\ServiceProvider::class,
        Items\ServiceProvider::class,
//        Mulit\Items\ServiceProvider::class,
        Order\ServiceProvider::class
    ];
}