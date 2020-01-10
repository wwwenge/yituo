<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\Opencart\Category;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface{
    public function register(Container $app)
    {
        $app['category'] = function ($app) {
            return new Client($app);
        };

        $app['multi_categories'] = function ($app) {
            return new MulitClient($app);
        };
    }
}