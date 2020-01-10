<?php
namespace Yituo\Opencart\Items;

use DOMElement;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use phpQuery as pq;
use Yituo\Opencart\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取产品总数
     */
    public function getTotal(): array {
        return $this->httpGet('', ['r' => 'Item.getTotal']);
    }

    /**
     * 获取所有产品
     * @param  array $options
     * @return array
     */
    public function getItems(...$options): array {
        $allowParams = ['order', 'sort', 'limit', 'offset', 'max_image_no', 'image_size'];
        $params = $this->filterOptions($allowParams, $options);
        return $this->httpPost('', $params, ['r' => 'Item']);
    }
    
    /**
     * 获取产品信息
     * @param int $item_id
     * @return array
     */
    public function getItem(int $itemId) {
        return $this->httpGet('', ['item_id' => $itemId, 'r' => 'Item.Get']);
    }

    /**
     * 添加产品
     * @param array $options
     * @return array
     */
    public function addItem($options) {
        $allowParams = ['shop_product_id', 'title', 'detail', 'price', 'special', 'stock', 'visible', 'list_order', 'variations', 'images', 'categories'];
        $params = array_intersect_key($options, array_flip($allowParams));

        return $this->httpPost('', $options, ['r' => 'Item.Add']);
    }

    /**
     * 编辑产品
     * @param int $item_id
     * @param array $options
     * @return array
     */
    public function editItem(int $item_id, $options) {
        $allowParams = ['item_id', 'title', 'detail', 'price', 'special', 'stock', 'visible', 'list_order', 'variations', 'images', 'categories'];
        $options['item_id'] = $item_id;
        $options = array_intersect_key($options, array_flip($allowParams));

        return $this->httpPost('', $options, ['r' => 'Item.Edit']);
    }

    /**
     * 删除产品
     * @param int $item_id
     * @return array
     */
    public function deleteItem(int $item_id) {
        return $this->httpPost('', ['item_id' => $item_id], ['r' => 'Item.Delete']);
    }

    /**
     * 获取产品基本信息列表
     * @param string $order
     * @param string $sort
     * @return array
     */
    public function getSimpleItems($order = 'list_order', $sort = 'asc'):array {
        return $this->httpPost('', ['order' => $order, 'sort' => $sort], ['r' => 'Item.GetSimpleItems']);
    }
}