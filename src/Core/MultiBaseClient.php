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

class MultiBaseClient
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
    protected $baseUri = 'https://admin.thebase.in';

    /**
     * @var array
     */
    protected $poolOptions;

    public $headers = ['user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36'];

    /**
     * 缓存前缀
     * @var string
     */
    protected $cachePrefix = 'yituo.kernel.cookies.';

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
        $options['cookies'] = unserialize($this->getCookieJar());

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
     * 获取缓存的Cookies
     * @return object
     */
    public function getCookieJar() {
        if (!$this->getCache()->has($this->getCacheKey())) {
            return $this->login();
        }

        return $this->getCache()->get($this->getCacheKey());

        throw new RuntimeException("Failed to get cookies.");
    }

    /**
     * 获取登录缓存
     * @return \GuzzleHttp\Cookie\CookieJar
     */
    public function login() {
        $this->registerHttpMiddlewares();
        $response = $this->performRequest('users/login');
        $params = [];

        if($response->getStatusCode() == 200) {
            $document = pq::newDocumentHTML($response->getBody());
            $document->find("#userLoginForm input")->each(function(DOMElement $element) use(&$params) {
                $params[$element->getAttribute("name")] = $element->getAttribute("value");
            });

            $params['data[User][mail_address]'] = $this->app['config']->get('oauth.username');
            $params['data[User][password]']     = $this->app['config']->get('oauth.password');

            $response = $this->performRequest($document->find("#userLoginForm")->attr('action'), 'post', ['form_params' => $params]);

            if(!$this->checkIsLogin($response)) {
                throw new RuntimeException('Failed to get cookies. Login fail');
            }

            return $this->setCookies($this->getCookies());
        }
    }

    public function checkIsLogin($response) {
        return stripos($this->getCurrentUrl($response), 'shop_id') !== false;
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
        return $this->cachePrefix.$this->app['config']->get('oauth.shop_name');
    }

    /**
     * 存储Cookies
     * @param \GuzzleHttp\Cookie\CookieJar
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Yituo\Core\Exceptions\RuntimeException
     */
    public function setCookies(\GuzzleHttp\Cookie\CookieJar $cookies) {
        $this->getCache()->set($this->getCacheKey(), serialize($cookies), $this->expires);

        if (!$this->getCache()->has($this->getCacheKey())) {
            throw new RuntimeException('Failed to cache cookies.');
        }

        return serialize($cookies);
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

    public function getOutput() {
        return $this->output;
    }

    /**
     * 注册 Guzzle 中间件.
     */
    protected function registerHttpMiddlewares()
    {
        // retry
        $this->pushMiddleware($this->retryMiddleware(), 'retry');
        $this->pushMiddleware($this->effectiveUrlMiddleware(), 'effective');
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