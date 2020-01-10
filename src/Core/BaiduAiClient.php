<?php

namespace Yituo\Core;


use DOMElement;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Middleware;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Yituo\Core\Contracts\AccessTokenInterface;
use Yituo\Core\Traits\HttpRequests;
use phpQuery as pq;
use Yituo\Core\Traits\InteractsWithCache;

class BaiduAiClient
{
    /**
     * 公用缓存类
     * @var \Yituo\Core\Traits\InteractsWithCache
     */
    use InteractsWithCache;

    use HttpRequests { request as performRequest; multiRequest as performMultiRequest; }

    /**
     * @var \Yituo\Core\ServiceContainer
     */
    protected $app;

    /**
     * @var string
     */
    protected $baseUri = 'https://aip.baidubce.com';

    protected $accessToken;
    /**
     * @var array
     */
    protected $poolOptions;

    public $headers = ['user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36'];

    /**
     * 缓存前缀
     * @var string
     */
    protected $cachePrefix = 'yituo.kernel.baiduai.token.';

    /**
     * cookies过期时间
     * @var int
     */
    protected $expires = 5400;

    /**
     * response
     * @var int
     */
    protected $output = [];

    /**
     * MultiBaseClient constructor.
     *
     * @param \Yituo\Core\ServiceContainer        $app
     */
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }

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

    /**
     * 获取缓存的token
     * @return object
     */
    public function getToken() {
        if (!$this->getCache()->has($this->getCacheKey())) {
            return $this->getAccessToken();
        }

        return $this->getCache()->get($this->getCacheKey());

        throw new RuntimeException("Failed to get cookies.");
    }

    /**
     * 获取token
     * @return \GuzzleHttp\Cookie\CookieJar
     */
    public function getAccessToken() {
        $this->registerHttpMiddlewares();
        $response = $this->performRequest('/oauth/2.0/token?grant_type=client_credentials&client_id=zVjC0WITUCWWTYhGMtdfV77C&client_secret=uft0uUAwUlGLgtrZUFG0cZoIvIfvAhIM');

        if($response->getStatusCode() == 200) {
            return $this->setAccessToken($response->getBody()->getContents());
        }
    }

    public function getCurrentUrl($response) {
        $header = $response->getHeader("X-GUZZLE-EFFECTIVE-URL");
        return end($header);
    }

    /**
     * 获取缓存名字
     * @return string
     */
    protected function getCacheKey() {
        return $this->cachePrefix.'baiduai';
    }

    /**
     * 存储Cookies
     * @param \GuzzleHttp\Cookie\CookieJar
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Yituo\Core\Exceptions\RuntimeException
     */
    public function setAccessToken($accessToken) {
        $this->getCache()->set($this->getCacheKey(), $accessToken, $this->expires);

        if (!$this->getCache()->has($this->getCacheKey())) {
            throw new RuntimeException('Failed to cache access token.');
        }

        return $accessToken;
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

        $this->accessToken = json_decode($this->getToken())->access_token;
//        return new Request($method, sprintf("%s?access_token=%s", $endpoint, $this->accessToken), $headers, $body);
        return new Request($method, $endpoint, $headers, $body);
    }

    public function handleAddSuccess () {
        return function(Response $response, $index) {
            if($response->getHeader('Content-disposition')) {
                $body = $response->getBody();
                $this->output[$index] = array_map('str_getcsv', str_getcsv(mb_convert_encoding($body, "UTF-8", "Shift-JIS"),"\n"));
            } else {
                $this->output[$index] = (string)$response->getBody();
            }
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

    /**
     * 注册 Guzzle 中间件.
     */
    protected function registerHttpMiddlewares()
    {
        // retry
        $this->pushMiddleware($this->userAgentMiddleware(), 'useragent');
        $this->pushMiddleware($this->retryMiddleware(), 'retry');
        $this->pushMiddleware($this->effectiveUrlMiddleware(), 'effective');
        $this->pushMiddleware($this->accessTokenMiddleware(), 'accesstoken');
    }

    protected function userAgentMiddleware() {
        return Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('User-Agent', $this->headers['user-agent']);
        });
    }

    protected function accessTokenMiddleware() {
        return Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withUri($request->getUri()->withQuery(sprintf("access_token=%s", $this->accessToken)));
        });
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

            return false;
        }, function () {
            return abs($this->app->config->get('http.retry_delay', 500));
        });
    }

    public function effectiveUrlMiddleware() {
        return function (callable $handler) {
            return function (
                RequestInterface $request,
                array $options
            ) use ($handler) {
                $promise = $handler($request, $options);
                return $promise->then(
                    function (ResponseInterface $response) use ($request, $options) {
                        return $response->withHeader('X-GUZZLE-EFFECTIVE-URL', $request->getUri()->__toString());
                    }
                );
            };
        };
    }

}