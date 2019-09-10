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
    use HttpRequests { request as performRequest; }
    /**
     * @var \Yituo\Core\ServiceContainer
     */
    protected $app;

    /**
     * @var \Yituo\TheBase\OAuth\Provider\BaseProvider
     */
    protected $token;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * BaseClient constructor.
     *
     * @param \Yituo\Core\ServiceContainer                    $app
     * @param \Yituo\Core\Contracts\AccessTokenInterface|null $accessToken
     */
    
    public function __construct(ServiceContainer $app, $token = null)
    {
        $this->app = $app;
        $this->token = $token ? $token : $this->app['oauth'];
    }

    public function httpPost(string $url, array $data = [])
    {
        return $this->request($url, 'POST', ['form_params' => $data]);
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
        // retry
        $this->pushMiddleware($this->retryMiddleware(), 'retry');
        // access token
        $this->pushMiddleware($this->accessTokenMiddleware(), 'access_token');
        // log
//        $this->pushMiddleware($this->logMiddleware(), 'log');
    }

    /**
     * Return retry middleware.
     *
     * @return \Closure
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null,
            $exception = null
        ) {
            if ($exception instanceof ConnectException || $exception instanceof RequestException) {
                return true;
            }

            // Limit the number of retries to 2
            if ($retries < $this->app->config->get('http.max_retries', 3) && $response && $body = $response->getBody()) {
                // Retry on server errors
                $response = json_decode($body, true);
                if(json_last_error() == JSON_ERROR_NONE) {
                    if (!empty($response['error']) && in_array($response['error'], ['invalid_request'], true)) {
                        $this->token->refresh();
//                    $this->app['logger']->debug('Retrying with refreshed access token.');

                        return true;
                    }
                }
            }

            return false;
        }, function () {
            return abs($this->app->config->get('http.retry_delay', 500));
        });
    }


    /**
     * 在header添加access token.
     *
     * @return \Closure
     */
    protected function accessTokenMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if ($this->token && stripos($this->app->config->get('http.base_uri'), $request->getUri()->getHost()) !== false) {
                    $request = $this->token->applyToHeader($request, $options);
                }

                return $handler($request, $options);
            };
        };
    }

    /**
     * 过滤请求无效的参数
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