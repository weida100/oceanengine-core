<?php
declare(strict_types=1);
/**
 * Author: Weida
 * Date: 2023/11/5 23:17
 * Email: sgenmi@gmail.com
 */

namespace Weida\OceanengineCore;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Weida\Oauth2Core\Contract\HttpClientInterface;
use Weida\OceanengineCore\Contract\AccessTokenInterface;
use Weida\OceanengineCore\Contract\ApiInterface;
use Weida\OceanengineCore\Contract\WithAccessTokenClientInterface;

class WithAccessTokenClient implements WithAccessTokenClientInterface
{
    private AccessTokenInterface $accessToken;
    private Client $client;
    public function __construct(HttpClientInterface $httpClient,AccessTokenInterface $accessToken)
    {
        $this->client = $httpClient->getClient();
        $this->accessToken = $accessToken;
    }

    /**
     * @param string $method
     * @param string|ApiInterface $uri
     * @param array $options
     * @return ResponseInterface
     * @throws Throwable
     * @author Weida
     */
    public function request(string $method, string|ApiInterface $uri, array $options = []): ResponseInterface
    {
        $method = strtoupper($method);
        return match ($method) {
            'GET' => $this->get($uri, $options),
            'POST' => $this->post($uri, $options),
            default => throw new \InvalidArgumentException(sprintf("%s not supported", $method)),
        };
    }

    /**
     * @param string|ApiInterface $uri
     * @param array $options
     * @return ResponseInterface
     * @throws Throwable
     * @author Weida
     */
    public function get(string|ApiInterface $uri, array $options = []): ResponseInterface
    {
        if($uri instanceof ApiInterface){
            $options['query'] = $uri->getParams();
            $uri = $uri->getUrl();
        }else{
            if(!isset($options['query']) && $options){
                $options['query']= $options;
            }
        }
        $options['headers']['Access-Token'] = $this->accessToken->getToken();
        return $this->client->get($uri,$options);
    }

    /**
     * @param string|ApiInterface $uri
     * @param array $options
     * @return ResponseInterface
     * @throws Throwable
     * @author Weida
     */
    public function post(string|ApiInterface $uri, array $options = []): ResponseInterface
    {
        if($uri instanceof ApiInterface){
            $options['body'] = json_encode($uri->getParams());
            $uri = $uri->getUrl();
        }else{
            if(isset($options['body'])){
                if(is_array($options['body'])){
                    $options['body'] = json_encode($options['body']);
                }
            }
        }
        $options['headers']['Access-Token'] = $this->accessToken->getToken();
        $options['headers']['Content-Type'] = 'application/json';
        return $this->client->get($uri,$options);
    }

    /**
     * @param string|ApiInterface $uri
     * @param array $postData
     * @return ResponseInterface
     * @throws Throwable
     * @author Weida
     */
    public function postJson(string |ApiInterface $uri, array $postData = []): ResponseInterface
    {
        if($uri instanceof ApiInterface){
            if(!empty($postData)){
                $options['body'] = json_encode($postData);
            }else{
                $options['body'] = json_encode($uri->getParams());
            }
            $uri = $uri->getUrl();
        }else{
            $options['body'] = json_encode($postData);
        }
        $options['headers']['Access-Token'] = $this->accessToken->getToken();
        $options['headers']['Content-Type'] = 'application/json';
        return $this->client->get($uri,$options);
    }

}