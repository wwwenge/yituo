<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\TheBase\Savings;

use Yituo\Core\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取所有Saving
     * @param array $options
     * @return array
     */
    public function getSavings(...$options) {
        $allowParams = ['start_created', 'end_created', 'limit', 'offset'];
        return $this->httpGet('/1/savings', $this->filterOptions($allowParams, $options));
    }
}