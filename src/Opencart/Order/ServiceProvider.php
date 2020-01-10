<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\Opencart\Order;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface{
    public function register(Container $app)
    {
        $app['order'] = function ($app) {
            return new Client($app);
        };

        $app['mulit_orders'] = function ($app) {
            return new MulitClient($app);
        };
    }
}