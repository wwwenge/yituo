<?php

namespace Yituo\Opencart;

use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Yituo\Opencart\BaseClient;

class MultiBaseClient extends BaseClient
{

    public $headers = [];

    /**
     * response
     * @var int
     */
    protected $output = [];

    /**
     * 获取线程池的配置
     * @return array
     */
    public function getPoolOptions() {
        $this->registerHttpMiddlewares();
        $options = ['handler' => $this->getHandlerStack()];
        $options['base_uri'] = $this->baseUri;

        return $options;
    }

    /**
     * 获取并发数
     * @return int
     */
    public function getConcurrency() {
        return $this->app->config->get('mulit_http.concurrency', 5);
    }

    public function addHeader($header) {
        $this->headers = array_merge($this->headers, $header);
    }

    public function getHeaders() {
        return $this->headers;
    }

    /**
     * 请求任务
     * @return Request
     */
    public function requests($requests) {
        foreach($requests as $request) {
            yield $request;
        }
    }

    public function makeRequest($endpoint, $body, $headers = [], $method = 'post') {
        $headers = array_merge($this->getHeaders(), $headers);
        $method = strtolower($method);

        if($method == 'post') {
            switch (gettype($body)) {
                case 'resource':
                    $body = new \GuzzleHttp\Psr7\MultipartStream([['name' => 'file','contents' => $body]]);
                    $headers['Content-Type'] = 'multipart/form-data; boundary=' . $body->getBoundary();
                    break;
                default:
                    $headers['Content-Type'] = 'application/x-www-form-urlencoded';
                    break;
            }

        } else {
            $headers['Content-Type'] = 'text/html';
        }

        //phalapi的server参数
        $endpoint = '?r=' . $endpoint;
        return new Request($method, $endpoint, $headers, $body);
    }

    public function handleAddSuccess () {
        return function(Response $response, $index) {
            $this->output[$index] = (string)$response->getBody();
        };
    }

    public function handleAddFaild () {
        return function($reason, $index) {
            $this->output[$index] = $reason->getMessage();
        };
    }

    public function resetOutput() {
        $this->output = [];
    }

    public function getOutput() {
        return $this->output;
    }
}