<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\BaiduAi;

use Yituo\Core\ServiceContainer;

/**
 * Class Application.
 *
* @property \Yituo\BaiduAi\ImageSearch\MulitClient                               $imageSearch
*/
class Application extends ServiceContainer {
    protected $providers = [
        ImageSearch\ServiceProvider::class,
    ];
}