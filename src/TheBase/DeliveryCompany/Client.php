<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\TheBase\DeliveryCompany;

use Yituo\Core\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取所有配送公司
     * @return array
     */
    public function getDeliveryCompanies() {
        return $this->httpGet('/1/delivery_companies');
    }
}