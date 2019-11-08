<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\BaiduAi\ImageSearch;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface{
    public function register(Container $app)
    {
        $app['images_search_client'] = function ($app) {
            return new MulitClient($app);
        };
    }
}