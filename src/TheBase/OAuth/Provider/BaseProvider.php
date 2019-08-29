<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\TheBase\OAuth\Provider;


use DOMElement;
use Psr\Http\Message\RequestInterface;
use Yituo\Core\Contracts\AccessTokenInterface;
use Yituo\Core\Exceptions\HttpException;
use Yituo\Core\ServiceContainer;
use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\parse_query;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Pimple\Container;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use phpQuery as pq;
use Yituo\Core\Exceptions\RuntimeException;
use Yituo\Core\Traits\HttpRequests;
use Yituo\Core\Traits\InteractsWithCache;

class BaseProvider extends AbstractProvider
{
    /**
     * 公用缓存类
     * @var \Yituo\Core\Traits\InteractsWithCache
     */
    use InteractsWithCache;

    /**
     * 公用Http请求类
     * @var \Yituo\Core\Traits\InteractsWithCache
     */
    use HttpRequests;
    use BearerAuthorizationTrait;

    protected $scope = '';

    protected $state = '';

    /**
     * 容器集合
     * @var \Yituo\Core\ServiceContainer
     */
    protected $app;

    /**
     * 请求方式
     * @var string
     */
    protected $requestMethod = 'POST';

    /**
     * @var array
     */
    protected $token;

    /**
     * @var int
     */
    protected $safeSeconds = 500;

    /**
     * 令牌名称
     * @var string
     */
    protected $tokenKey = 'access_token';

    /**
     * 刷新令牌名称
     * @var string
     */
    protected $refreshKey = 'refresh_token';

    /**
     * 缓存前缀
     * @var string
     */
    protected $cachePrefix = 'yituo.kernel.access_token.';

    /**
     * The base provider constructor.
     *
     * @param \Yituo\Core\ServiceContainer $app
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(ServiceContainer $app, array $options = [], array $collaborators = []) {
        $this->app = $app;
        parent::__construct($options, ['httpClient' => new Client(['cookies' => true])]);
    }

    /**
     * 获取授权的URI
     *
     * @return string
     */
    public function getBaseAuthorizationUrl() {
        return 'https://api.thebase.in/1/oauth/authorize';
    }

    /**
     * 获取The base 请求访问令牌的URL
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params) {
        return 'https://api.thebase.in/1/oauth/token';
    }

    /**
     * 获取用户信息
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token) {
        return 'https://api.thebase.in/1/users/me';
    }

    /**
     * 设置访问权限
     * @param string $scope
     */
    public function setScope(array $scope): void {
        $this->scope = implode($this->getScopeSeparator(), $scope);
    }

    /**
     * 获取访问权限
     * @param string $state
     */
    public function setState(string $state): void {
        $this->state = $state;
    }

    /**
     * 获取缺省的访问权限
     *
     *
     * @return string[]
     */
    protected function getDefaultScopes(): array {
        return ['read_users', 'read_users_mail', 'read_items', 'read_orders', 'read_savings', 'write_items', 'write_orders'];
    }

    /**
     * 检查授权是否正确
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array|string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data) {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response->getBody()
            );
        }
    }

    /**
     * @param  array $response
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token) {
        return new BaseResourceOwner($response);
    }

    /**
     * 返回多个权限的分隔符
     *
     * @return string Scope separator
     */
    protected function getScopeSeparator() {
        return ' ';
    }


    /**
     * 从cache中获取Token
     * @param bool $refresh
     * @return \League\OAuth2\Client\Token\AccessToken
     *
     * @throws \Yituo\Core\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Yituo\Core\Exceptions\InvalidArgumentException
     * @throws \Yituo\Core\Exceptions\RuntimeException
     */
    public function getToken($refresh=false) {
        $cacheKey = $this->getCacheKey();
        $cache = $this->getCache();

        if (!$refresh && $cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        throw new RuntimeException("Failed to get access token. You could call initializeToken method to make an access token");
    }

    /**
     * 获取新的Access Token
     * @return \League\OAuth2\Client\Token\AccessToken
     *
     * @throws \Yituo\Core\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Yituo\Core\Exceptions\InvalidArgumentException
     * @throws \Yituo\Core\Exceptions\RuntimeException
     */
    public function refresh() {
        $result = parent::getAccessToken('refresh_token', ['refresh_token' => $this->getToken()->getRefreshToken()]);
        $this->setToken($result);
        return $this;
    }

    /**
     * 存储AccessToken
     * @param \League\OAuth2\Client\Token\AccessToken
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Yituo\Core\Exceptions\RuntimeException
     */
    public function setToken(\League\OAuth2\Client\Token\AccessToken $token) {
         $this->getCache()->set($this->getCacheKey(), $token, $token->getExpires() - $this->safeSeconds);

        if (!$this->getCache()->has($this->getCacheKey())) {
            throw new RuntimeException('Failed to cache access token.');
        }

        return $this;
    }

    /**
     * 获取缓存名字
     * @return string
     */
    protected function getCacheKey() {
        return $this->cachePrefix.$this->app['config']->get('oauth.shop_name');
    }

    /**
     * 获取令牌名字
     * @return string
     */
    public function getTokenKey() {
        return $this->tokenKey;
    }

    /**
     * 请求过程中追加Header参数
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array $requestOptions
     * @return static
     */
    public function applyToHeader(RequestInterface $request, array $requestOptions = []): RequestInterface {
        $accessToken = $this->getToken();
        return $request->withHeader('Authorization', 'Bearer ' . $accessToken->getToken());
    }

    /**
     * 跳转授权页
     */
    public function redirect() {
        parent::authorize(['state' => $this->state, 'scope' => $this->scope]);
    }

    /**
     * 发送请求
     * @param string $method
     * @param string $uri
     * @param array $option
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendRequest($method, $uri, $option =[]) {
        return $this->getHttpClient()->request($method, $uri, $option);
    }

    /**
     * 获取授权的表单信息
     * @return array
     */
    protected function getAuthorizeData() {
        $response = $this->sendRequest('GET', $this->getAuthorizationUrl(['state' => $this->state, 'scope' => $this->scope]));
        $params = array();

        if($response->getStatusCode() == 200) {
            $document = pq::newDocumentHTML($response->getBody());
            $document->find($this->getSelector())->each(function(DOMElement $element) use(&$params) {
                $params[$element->getAttribute("name")] = $element->getAttribute("value");
            });

            return $params;
        }

        throw new RuntimeException("Failed to get authorize form data in url");
    }

    /**
     * 获取用户登录的信息
     * @return array
     */
    protected function getLoginData() {
        return ['data[User][mail_address]' => $this->app['config']->get('oauth.username'),
                'data[User][password]' => $this->app['config']->get('oauth.password'),
                'auth_yes' => '\u30A2\u30D7\u30EA\u3092\u8A8D\u8A3C\u3059\u308B',
            ];
    }

    /**
     * 验证url有没有code
     * @return mixed
     */
    public function verifyURL($url) {
        return strpos($url, 'code');
    }

    /**
     * 获取选择器规则
     * @return string
     */
    protected function getSelector() {
        return '#UserIndexForm input[type="hidden"]';
    }

    /**
     * 获取code
     * @return string
     */
    public function getCode() {
        $response = $this->sendRequest('POST', $this->getBaseAuthorizationUrl(), ['debug' => $this->app['config']->get("http.debug"),'allow_redirects' => false, 'form_params' => array_merge($this->getAuthorizeData(), $this->getLoginData())]);

        if($response->getStatusCode() == 302) {
            $url = current($response->getHeader('Location'));
            if($this->verifyURL($url) !== false) {
                parse_str(parse_url($url, PHP_URL_QUERY),$variable);
                return $variable['code'];
            }
        }

        throw new RuntimeException("Failed to get code in url");
    }

    /**
     * 初始化AccessToken
     * @return string
     */
    public function initializeToken() {
        $code = $this->getCode();
        $result = parent::getAccessToken('authorization_code', ['code' => $code]);
        $this->setToken($result);
        return $this;
    }
}