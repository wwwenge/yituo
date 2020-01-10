<?php

namespace Yituo\Core;


use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Yituo\Core\Contracts\AccessTokenInterface;
use Yituo\Core\Traits\HttpRequests;

class BaseClient
{
    use HttpRequests { request as performRequest; multiRequest as performMultiRequest; }
    /**
     * @var \Yituo\Core\ServiceContainer
     */
    protected $app;

    /**
     * BaseClient constructor.
     *
     * @param \Yituo\Core\ServiceContainer                    $app
     * @param \Yituo\Core\Contracts\AccessTokenInterface|null $accessToken
     */
    
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }

    public function httpPost(string $url, array $data = [], array $query = [])
    {
        $options = ['form_params' => $data];
        if ($query) {
            $options['query'] = $query;
        }

        return $this->request($url, 'POST', $options);
    }

    public function httpGet(string $url, array $query = []) {
        return $this->request($url, 'GET', ['query' => $query]);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array  $options
     * @param bool   $returnRaw
     *
     * @return \Psr\Http\Message\ResponseInterface|\Yituo\Core\Support\Collection|array|object|string
     *
     * @throws \Yituo\Core\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $url, string $method = 'GET', array $options = [], $returnRaw = false)
    {
        if (empty($this->middlewares)) {
            $this->registerHttpMiddlewares();
        }

        $response = $this->performRequest($url, $method, $options);

        return $returnRaw ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
    }

    /**
     * 注册 Guzzle 中间件.
     */
    protected function registerHttpMiddlewares()
    {
    }

    /**
     * 过滤请求无效的参数
     * @desc 只能过滤一维的无效参数
     *
     * @param array $allow
     * @param array $options
     * @return \Closure
     */
    public function filterOptions($allow, ...$options) {
        $mergeOptions = [];
        $this->mergeOptions($mergeOptions, $options);
        return array_intersect_key($mergeOptions, array_flip($allow));
    }

    function mergeOptions(array &$array1, $options) {
        if (is_array($options)) {
            foreach($options as $key => $value) {
                if(is_array($value)) {
                    $this->mergeOptions($array1, $value);
                } else {
                    $array1[$key] = $value;
                }
            }
        }
    }
}