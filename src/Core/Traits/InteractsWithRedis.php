<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\Core\Traits;


use Predis\Client;
use Yituo\Core\ServiceContainer;

trait InteractsWithRedis {
    /**
     * @var \Predis\Client
     */
    protected $redis;

    /**
     * 获取Redis对象实例 单例模式
     *
     * @return \Predis\Client
     *
     */
    public function getRedis()
    {
        if ($this->redis) {
            return $this->redis;
        }

        if (property_exists($this, 'app') && $this->app instanceof ServiceContainer && isset($this->app['redis'])) {
            $this->setRedis($this->app['redis']);
            return $this->redis;
        }

        return $this->redis = $this->createDefaultRedis();
    }

    /**
     * 设置Redis对象实例
     *
     * @param \Predis\Client $redis
     *
     * @return $this
     *
     */
    public function setRedis($redis) {
        $this->redis = $redis;

        return $this;
    }

    /**
     * 建立redis对象实例
     * @return \Predis\Client
     */
    protected function createDefaultRedis()
    {
        return new Client();
    }
}