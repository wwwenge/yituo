<?php

namespace Yituo\Opencart;

use Yituo\Core\BaseClient AS CoreBaseClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Yituo\Core\Contracts\AccessTokenInterface;
use Yituo\Core\Traits\HttpRequests;
use Yituo\Core\ServiceContainer;

class BaseClient extends CoreBaseClient
{
    public $baseUri;

    public function __construct(ServiceContainer $app)
    {
        parent::__construct($app);
        $baseUri = $this->app->config->get('http.base_uri');

//        $baseUri .= '/api/public/';
        $baseUri .= '/kenza1/api/public/';
        $this->baseUri = $baseUri;
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