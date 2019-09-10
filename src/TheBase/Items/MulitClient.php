<?php
namespace Yituo\TheBase\Items;


use DOMElement;
use Psr\Http\Message\RequestInterface;
use phpQuery as pq;
use Yituo\Core\MultiBaseClient;

class MulitClient extends MultiBaseClient
{
    public function getItems() {
        return current($this->multiRequest([$this->makeRequest('shop_admin/api/items/ids/', null, [], "get")]));
    }

    public function getItem($products) {
        return $this->multiRequest(array_map(function ($product) {
            $itemID = $product['item_id'];
            unset($product['item_id']);
            return $this->makeRequest(sprintf('shop_admin/api/items/view/%s', $itemID), null, [], "get");
        }, $products));
    }

    public function addItems($products) {
        return $this->multiRequest(array_map(function ($product) {
            return $this->makeRequest('shop_admin/api/items/add', json_encode($product));
        }, $products));
    }

    public function editItems($products) {
        return $this->multiRequest(array_map(function ($product) {
            $itemID = $product['item_id'];
            unset($product['item_id']);
            return $this->makeRequest(sprintf('shop_admin/api/items/edit/%s', $itemID), json_encode($product));
        }, $products));
    }

    public function deleteItems($products) {
        return current($this->multiRequest([$this->makeRequest('shop_admin/api/items/delete', json_encode($products), ['X-HTTP-Method-Override' => 'DELETE'])]));
    }

    public function sortItems($products) {
        return current($this->multiRequest([$this->makeRequest('shop_admin/api/items/update_order', json_encode($products))]));
    }

    public function uploadImages($products) {
        return $this->multiRequest(array_map(function ($path) {
            return $this->makeRequest('shop_admin/api/item_images/upload', fopen($path, 'r'));
        }, $products));
    }

}