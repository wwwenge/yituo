<?php
namespace Yituo\Opencart\Category;

use DOMElement;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use phpQuery as pq;
use Yituo\Opencart\MultiBaseClient;

class MulitClient extends MultiBaseClient
{
    /**
     * 获取所有目录
     * @return mixed
     */
    public function getCategories() {
        return current($this->multiRequest([$this->makeRequest('Category', null, [], "get")]));
    }

    /**
     * 批量添加目录
     * @param $categories
     */
    public function addCategories($categories) {
        return $this->multiRequest(array_map(function ($params) {
            $allowParams = ['name', 'list_order', 'parent_number'];
            $params = $this->filterOptions($allowParams, $params);

            return $this->makeRequest('Category.Add', http_build_query($params));
        }, $categories));
    }

    /**
     * 批量编辑目录
     * @param $categories
     */
    public function editCategories($categories) {
        return $this->multiRequest(array_map(function ($params) {
            $allowParams = ['category_id', 'list_order', 'name'];
            $params = $this->filterOptions($allowParams, $params);

            return $this->makeRequest('Category.Edit', http_build_query($params));
        }, $categories));
    }

    /**
     * 批量删除目录
     * @param $categories
     */
    public function deleteCategories($categories) {
        return $this->multiRequest(array_map(function ($categoryId) {
            return $this->makeRequest('Category.Delete', http_build_query(['category_id' => $categoryId]));
        }, $categories));
    }

    /**
     * 排序
     * @param $categories
     * @return array|void
     */
    public function sortCategories($categories) {
        $children = [];
        $result = [];
        $parent = array_map(function ($params) use(&$children) {
            if(isset($params['children'])) {
                array_map(function ($params) use(&$children) {
                    array_push($children, $this->makeRequest('Category.Sort', http_build_query($params)));
                }, $params['children']);
            }
            return $this->makeRequest('Category.Sort', http_build_query($params));
        }, $categories);

        if($children) {
            $result = $this->multiRequest($children);
        }

        $result = array_merge_recursive($this->multiRequest($parent), $result);
        return $result;
    }
}