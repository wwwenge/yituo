<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\TheBase\OAuth;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Yituo\TheBase\OAuth\Provider\BaseProvider;

/**
+--------+                               +---------------+
|        |--(A)- Authorization Request ->|   Resource    |
|        |                               |     Owner     |
|        |<-(B)-- Authorization Grant ---|               |
|        |                               +---------------+
|        |
|        |                               +---------------+
|        |--(C)-- Authorization Grant -->| Authorization |
| Client |                               |     Server    |
|        |<-(D)----- Access Token -------|               |
|        |                               +---------------+
|        |
|        |                               +---------------+
|        |--(E)----- Access Token ------>|    Resource   |
|        |                               |     Server    |
|        |<-(F)--- Protected Resource ---|               |
+--------+                               +---------------+
 *
 （A）用户打开客户端以后，客户端要求用户给予授权。
（B）用户同意给予客户端授权。
（C）客户端使用上一步获得的授权，向认证服务器申请令牌。
（D）认证服务器对客户端进行认证以后，确认无误，同意发放令牌。
（E）客户端使用令牌，向资源服务器申请获取资源。
（F）资源服务器确认令牌无误，同意向客户端开放资源。
 */
class ServiceProvider implements ServiceProviderInterface {
    public function register(Container $app)
    {
        $app['oauth'] = function ($app) {
            $provider = new BaseProvider($app, [
                'clientId'             => $app['config']['client_id'],
                'clientSecret'         => $app['config']['client_secret'],
                'redirectUri'          => $app['config']->get('oauth.redirect_uri', ''),
                'scope'                 => $app['config']->get('oauth.scope', '') ?? implode(" ", $app['config']->get('oauth.scope', '')),
                'state'                 => ''
            ], ['cookies' => true]);
            return $provider;
        };
    }
}