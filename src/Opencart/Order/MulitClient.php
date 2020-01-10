<?php
namespace Yituo\Opencart\Order;

use DOMElement;
use Psr\Http\Message\RequestInterface;
use phpQuery as pq;
use Yituo\Opencart\MultiBaseClient;

class MulitClient extends MultiBaseClient
{
    /**
     * 获取所有订单
     * @return array
     */
    public function getOrders($params, $fn = null) {
        $result = $this->multiRequest(array_map(function ($param) {
            $allowParams = ['start_ordered', 'end_ordered', 'limit', 'offset', 'status'];
            $param = array_intersect_key($param, array_flip($allowParams));
            $param = http_build_query($param);

            return $this->makeRequest('Order', $param, [], "post");
        }, $params), $fn);

        if (!$fn) {
            return $result;
        }
    }

    /**
     * 根据订单号列表批量获取订单详情
     * @param $uniqueKeys
     * @param null $fn
     */
    public function getOrdersByList($uniqueKeys, $fn = null) {
        $result = $this->multiRequest(array_map(function ($uniqueKey) {

            return $this->makeRequest('Order.Get', 'unique_key=' . $uniqueKey, [], "post");
        }, $uniqueKeys), $fn);

        if (!$fn) {
            return $result;
        }
    }
}