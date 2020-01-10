<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\Opencart\Items;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Yituo\Opencart\Items\Client;
use Yituo\Opencart\Items\MultiClient;

class ServiceProvider implements ServiceProviderInterface {
    public function register(Container $app) {
        $app['items'] = function($app) {
            return new Client($app);
        };

        $app['mulit_items'] = function($app) {
            return new MultiClient($app);
        };

    }
}