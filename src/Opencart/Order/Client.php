<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\Opencart\Order;

use Yituo\Opencart\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取订单总数
     */
    public function getTotal($options) {
        $allowParams = ['start_ordered', 'end_ordered', 'status'];
        $params = array_intersect_key($options, array_flip($allowParams));

        return $this->httpPost('', $params, ['r' => 'Order.getTotal']);
    }

    /**
     * 获取所有订单
     * @ref https://域名/api/public/?r=order
     * @param  array $orderDate[start_date, end_date, limit, offset, status]
     * @param  array $options
     * @return array
     */
    public function getOrders($options): array {
        $allowParams = ['start_ordered', 'end_ordered', 'limit', 'offset', 'status'];
        $params = array_intersect_key($options, array_flip($allowParams));

        return $this->httpPost('', $params, ['r' => 'Order']);
    }

    /**
     * 获取订单详情
     * @param  string $uniqueKey
     * @return array
     */
    public function getOrderDetail(string $uniqueKey): array {
        return $this->httpGet('', ['r' => 'Order.Get', 'unique_key' => $uniqueKey]);
    }

    /**
     * 获取运输方式
     * @return array
     */
    public function getShippingList(): array {
        return $this->httpGet('', ['r' => 'Order.getShippingList']);
    }

    /**
     * 更新订单状态
     * @param $unique_key
     * @param mixed ...$options
     * @return array
     */
    public function editStatus($unique_key, ...$options): array {
        $allowParams = ['status', 'comment', 'notify'];
        $params = array_intersect_key($options, array_flip($allowParams));
        $params['unique_key'] = $unique_key;
        return $this->httpPost('', $params, ['r' => 'Order.editStatus']);
    }

    /**
     * @param mixed ...$options
     * @return array
     */
    public function dispatched(...$options): array {
        $allowParams = ['order_product_id', 'status', 'shipping_method', 'track_number', 'comment'];
        $params = array_intersect_key($options, array_flip($allowParams));

        return $this->httpPost('', $params, ['r' => 'Order.editOrderProductStatus']);
    }
}