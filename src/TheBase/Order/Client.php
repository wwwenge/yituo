<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\TheBase\Order;

use Yituo\TheBase\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取所有订单
     * @ref https://docs.thebase.in/docs/api/orders/
     * @param  array $orderDate[start_date, end_date]
     * @param  array $options
     * @return array
     */
    public function getOrders(...$options): array {
        $allowParams = ['start_ordered', 'end_ordered', 'limit', 'offset'];

        return $this->httpGet('/1/orders', $this->filterOptions($allowParams, $options));
    }

    /**
     * 获取订单详情
     * @param  string $uniqueKey
     * @return array
     */
    public function getOrderDetail(string $uniqueKey): array {
        return $this->httpGet(sprintf('/1/orders/detail/%s', $uniqueKey));
    }

    /**
     * 更新订单产品信息
     * @param  int $order_item_id
     * @param  string $status
     * @param  string $add_comment
     * @param  string $atobarai_status
     * @param  int $delivery_company_id
     * @param  string $tracking_number
     * @return array
     */
    public function updateOrder(int $order_item_id, string $status, string $add_comment, string $atobarai_status, int $delivery_company_id, string $tracking_number): array {
        return $this->httpPost('/1/orders/edit_status', [
            'order_item_id' => $order_item_id,
            'status' => $status,
            'add_comment' => mb_strlen($add_comment) > 250 ? mb_substr($add_comment, 0, 250) : $add_comment,
            'atobarai_status' => $atobarai_status,
            'delivery_company_id' => $delivery_company_id,
            'tracking_number' => $tracking_number,
        ]);
    }
}