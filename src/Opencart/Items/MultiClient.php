<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/9 0009
 * Time: 11:35
 */
namespace Yituo\Opencart\Items;


use DOMElement;
use Psr\Http\Message\RequestInterface;
use phpQuery as pq;
use Yituo\Opencart\MultiBaseClient;
use GuzzleHttp\Psr7\Request;

class MultiClient extends MultiBaseClient
{
    public function getItemsByList($itemIds, $fn = null) {
        $result = $this->multiRequest(array_map(function ($itemId) {

            return $this->makeRequest('Item.Get', 'item_id=' . $itemId, [], "post");
        }, $itemIds), $fn);

        if (!$fn) {
            return $result;
        }
    }

    public function getItems($params, $fn = null) {
        $result = $this->multiRequest(array_map(function ($param) {
            $allowParams = ['order', 'sort', 'limit', 'offset', 'max_image_no', 'image_size'];
            $param = $this->filterOptions($allowParams, $param);
            $param = http_build_query($param);

            return $this->makeRequest('Item', $param, [], "post");
        }, $params), $fn);

        if (!$fn) {
            return $result;
        }
    }

    public function addItems($params, $fn = null) {
        $result = $this->multiRequest(array_map(function ($param) {
            $allowParams = ['shop_product_id', 'title', 'detail', 'price', 'special', 'stock', 'visible', 'list_order', 'variations', 'images', 'categories'];

            $param = array_intersect_key($param, array_flip($allowParams));

            $param = http_build_query($param);

            return $this->makeRequest('Item.Add', $param);
        }, $params), $fn);

        if (!$fn) {
            return $result;
        }
    }

    public function editItems($params, $fn = null) {
        $result = $this->multiRequest(array_map(function ($param) {
            $allowParams = ['item_id', 'title', 'detail', 'price', 'special', 'stock', 'visible', 'list_order', 'variations', 'images', 'categories'];

            $param = array_intersect_key($param, array_flip($allowParams));

            $param = http_build_query($param);

            return $this->makeRequest('Item.Edit', $param);
        }, $params), $fn);

        if (!$fn) {
            return $result;
        }
    }

    public function deleteItems($params, $fn = null) {
        $result = $this->multiRequest(array_map(function ($param) {
            return $this->makeRequest('Item.Delete', 'item_id=' . $param);
        }, $params), $fn);

        if (!$fn) {
            return $result;
        }
    }

    public function sortItems($params, $fn = null) {
        $result = $this->multiRequest(array_map(function ($param) {
            $allowParams = ['item_id', 'list_order'];

            $param = array_intersect_key($param, array_flip($allowParams));

            $param = http_build_query($param);

            return $this->makeRequest('Item.Sort', $param, [], 'post');
        }, $params), $fn);

        if (!$fn) {
            return $result;
        }
    }
}
