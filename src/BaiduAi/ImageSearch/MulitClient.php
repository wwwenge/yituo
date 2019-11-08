<?php
namespace Yituo\BaiduAi\ImageSearch;

use DOMElement;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use phpQuery as pq;
use Yituo\Core\BaiduAiClient;
use Yituo\Core\MultiBaseClient;

class MulitClient extends BaiduAiClient
{
    /**
     * $categories = [[
            'name' => 'Test',
            'parent_id' => '0'
     * ],
     *
     * ]
    */
    public function addSameHq($images) {
        return $this->multiRequest(array_map(function ($image) {
            return $this->makeRequest('/rest/2.0/realtime_search/same_hq/add', http_build_query($image));
        }, $images));
    }

    public function searchSameHq($images) {
        return $this->multiRequest(array_map(function ($image) {
            return $this->makeRequest('/rest/2.0/realtime_search/same_hq/search', http_build_query($image));
        }, $images));
    }

    public function deleteSameHq($ids) {
        $params = ["cont_sign" => implode(";", $ids)];
        return $this->multiRequest([$this->makeRequest('/rest/2.0/realtime_search/same_hq/delete', http_build_query($params))]);
    }


}