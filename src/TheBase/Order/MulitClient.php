<?php
namespace Yituo\TheBase\Order;


use DOMElement;
use Psr\Http\Message\RequestInterface;
use phpQuery as pq;
use Yituo\TheBase\MultiBaseClient;

class MulitClient extends MultiBaseClient
{
    /**
     * $params = ['limit' => 50,
        'words' => ""
        'order_by' => "ordered_desc"
        'status' => [],
        'payment' => [],
        'order_type' => 'all'
        ]
    */

    public function getOrdersId($params) {
        return current($this->multiRequest([$this->makeRequest('shop_admin/api/orders/summary', json_encode($params))]));
    }

    /**
     * "unique_keys":["0B14AF0F2EEC413C","467F781FA7318869","D3E62468BA9B66B9","FEBE427732562403","1A961D52583AABF3","8BE006F98751E6AA","0F50F5622E6BB3C0","C3BC7C32D6D3794D","861FCBA7DF818F45","4E6429600A37006C","2D92ABA94D619C2F","00A234C9091482B6","19B3E1FBACCC6C0C","BDDC8B8A341A951E","4D5761446BC93549","408B957896AEE6EF","5C18BC539E1A7610","0D70569EC679EBE5","8A881EAC109A60E2","49555E5842D59A7D","EDCF407AE6A4AE24","89A390B3B4EDCE4A","B00199F79193009A"]
    */
    public function getOrders($params) {
        return current($this->multiRequest([$this->makeRequest('apps/api/csv_lite/orders', json_encode($params))]));
    }

}