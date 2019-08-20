<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\TheBase\User;


use Yituo\Core\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取当前用户信息
     * @return array
     */
    public function getUser() {
        return $this->httpGet('/1/users/me/');
    }
}