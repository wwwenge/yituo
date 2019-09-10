<?php
namespace Yituo\TheBase\Category;

use DOMElement;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use phpQuery as pq;
use Yituo\Core\MultiBaseClient;

class MulitClient extends MultiBaseClient
{
    /**
     * $categories = [[
            'name' => 'Test',
            'parent_id' => '0'
     * ],
     *
     * ]
    */
    public function addCategories($categories) {
        return $this->multiRequest(array_map(function ($product) {
            return $this->makeRequest('item_category/item_category_ajax/add', http_build_query($product));
        }, $categories));
    }

    public function deleteCategories($categories) {
        return $this->multiRequest(array_map(function ($params) {
            return $this->makeRequest('item_category/item_category_ajax/delete', http_build_query($params));
        }, $categories));
    }

    public function getCategories() {
        return current($this->multiRequest([$this->makeRequest('shop_admin/api/item_categories/', null, [], "get")]));
    }

}