<?php
namespace Yituo\TheBase\Items;


use DOMElement;
use Psr\Http\Message\RequestInterface;
use phpQuery as pq;
use Yituo\Core\MultiBaseClient;

class MulitClient extends MultiBaseClient
{
    public $fakeProduct = ['item' => [ 'variations' => [],
                                        'visible' => false,
                                        'stock' => '1000',
                                        'price' => '1000',
                                        'item_tax_type' => 'standard',
                                        'detail' => '',
                                        'images' => [],
                                        'name' => 'FakeProduct',
                                        'top_of_list' => false,
                                        'apps' => [ 'category' => ['enabled' => []],
                                            'label' => [],
                                            'quantity_limit' => [],
                                            'sale' => ['discount_rate' => 0],
                                            'shipping_fee' => ['enabled' => []],
                                            'subscription' => [],
                                            'digital' => [],
                                            'club_t' => [],
                                            'sp_case' => [],
                                            'sales_period' => [],
                                            'pre_order' => []
                                        ]
                                    ]
                            ];

    public function getIds() {
        return current($this->multiRequest([$this->makeRequest('shop_admin/api/items/ids/', null, [], "get")]));
    }

    public function createItemIds($qty = 1) {
        $products = [];
        for($i = 0; $i < $qty; $i++) {
            array_push($products, $this->fakeProduct);
        }

        return $this->addItems($products, function(\GuzzleHttp\Psr7\Response $response, $index) {});
    }

    public function getAvailableIds() {
        return current($this->searchItems("fakeproduct"));
    }

    public function searchItems($keyword) {
        return $this->multiRequest([$this->makeRequest(sprintf('shop_admin/api/items/search/?keyword=%s', $keyword), null, [], "get")]);
    }

    public function getItems($products, $fn=null) {
        $result = $this->multiRequest(array_map(function ($product) {
            $itemID = $product['item_id'];
            unset($product['item_id']);
            return $this->makeRequest(sprintf('shop_admin/api/items/view/%s', $itemID), null, [], "get");
        }, $products), $fn);

        if (!$fn) {
            return $result;
        }
    }

    public function addItems($products, $fn=null) {
        return $this->multiRequest(array_map(function ($product) {
            return $this->makeRequest('shop_admin/api/items/add', json_encode($product));
        }, $products), $fn);

        if (!$fn) {
            return $result;
        }
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