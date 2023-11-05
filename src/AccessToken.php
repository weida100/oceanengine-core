<?php
declare(strict_types=1);
/**
 * Author: Weida
 * Date: 2023/11/5 10:09
 * Email: sgenmi@gmail.com
 */

namespace Weida\OceanengineCore;

use RuntimeException;
use Psr\SimpleCache\CacheInterface;
use Weida\Oauth2Core\Contract\ConfigInterface;
use Weida\Oauth2Core\Contract\HttpClientInterface;
use Weida\OceanengineCore\Contract\AccessTokenInterface;

class AccessToken implements AccessTokenInterface
{
    private int $clientId;
    private string $clientSecret;
    private int $uid;
    private string $refreshToken;
    private string $accessToken='';
    private ?CacheInterface $cache;
    private ?HttpClientInterface $httpClient;
    private $callback;
    public function __construct(
        int $clientId,string $clientSecret,int $uid, string $refreshToken,
        ?CacheInterface $cache=null, ?HttpClientInterface $httpClient=null,?callable $callback=null
    )
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->uid = $uid;
        $this->refreshToken = $refreshToken;
        $this->cache = $cache;
        $this->httpClient = $httpClient;
        $this->callback = $callback;
    }


    public function getToken(bool $isRefresh = false): string
    {
        if(!empty($this->accessToken)){
            return $this->accessToken;
        }
        if(!$isRefresh){
            $token = $this->cache->get($this->getCacheKey());
            if (!empty($token)) {
                return $token;
            }
        }
        $url = "https://ad.oceanengine.com/open_api/oauth2/refresh_token/";

        $res = $this->httpClient->request('POST',$url,[
            'headers'=>[
                'Content-Type'=>'application/json'
            ],
            'body'=>json_encode([
                'app_id'=> $this->clientId,
                'secret'=>$this->clientSecret,
                'grant_type'=>'refresh_token',
                'refresh_token'=>$this->refreshToken
            ])
        ]);

        if($resp->getStatusCode()!=200){
            throw new RuntimeException('Request access_token exception');
        }
        $arr = json_decode($resp->getBody()->getContents(),true);

        if (empty($arr['data']['access_token'])) {
            throw new RuntimeException('Failed to get access_token: ' . json_encode($arr, JSON_UNESCAPED_UNICODE));
        }
        //走刷新流程，这里刷新和其他一般的oauth2不太一样。存在同时刷新access_token和refresh_token,
        //如果用于保存refresh_token,这里走回调处理
        if($this->callback && is_callable($this->callback)){
            try {
                call_user_func($this->callback,...[
                    'clientId'=>$this->clientId,
                    'uid'=>$this->uid,
                    'accessToken'=>$arr['data']['access_token'],
                    'accessTokenExpiresIn'=>(int)$arr['data']['expires_in'],
                    'refreshToken'=>$arr['data']['refresh_token'],
                    'refreshTokenExpiresIn'=>(int)$arr['data']['refresh_token_expires_in']
                ]);
            }catch (\Throwable $e){
            }
        }
        $this->cache->set($this->getCacheKey(), $arr['data']['access_token'], intval($arr['data']['expires_in'])-10);
        return $arr['data']['access_token'];
    }

    public function setToken(string $accessToken): static
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function expiresTime(): int
    {
        return  $this->cache->ttl($this->getCacheKey());
    }

    public function getParams(): array
    {
        return [
            'client_id'=>$this->clientId,
            'secret'=>$this->clientSecret,
            'refresh_token'=>$this->refreshToken,
            'uid'=>$this->uid,
            'cache'=>$this->cache,
            'httpClient'=>$this->httpClient
        ];
    }

    public function getCacheKey(): string
    {
        if(!$this->uid){
            throw new RuntimeException('uid not fund');
        }
        if(empty($this->cacheKey)){
            $this->cacheKey = sprintf("access_token:%s:%s", $this->clientId,$this->uid);
        }
        return $this->cacheKey;
    }

    public function setCacheKey(string $key): static
    {
        $this->cacheKey = $key;
        return $this;
    }

    public function saveCache(int $uid,string $accessToken,int $expiresIn):bool {
        $this->uid = $uid;
        $this->cache->set($this->getCacheKey(), $accessToken, $expiresIn-10);
        return true;
    }
}