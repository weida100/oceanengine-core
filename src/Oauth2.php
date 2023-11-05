<?php
declare(strict_types=1);
/**
 * Author: Weida
 * Date: 2023/11/4 23:28
 * Email: sgenmi@gmail.com
 */

namespace Weida\OceanengineCore\Contract;

use RuntimeException;
use Throwable;
use Weida\Oauth2Core\AbstractApplication;
use Weida\Oauth2Core\Contract\UserInterface;
use Weida\Oauth2Core\User;

class Oauth2 extends AbstractApplication
{
    /**
     * @return string
     * @author Weida
     */
    protected function getAuthUrl(): string
    {
        $params=[
            'appid'=>$this->getConfig()->get('client_id'),
            'redirect_uri'=>$this->getConfig()->get('redirect'),
            'response_type'=>'code',
            'scope'=>implode(',',$this->scopes),
            'state'=> $this->state,
        ];

        if($this->getConfig()->get('app_type')=="ad"){
            return sprintf("https://ad.oceanengine.com/openapi/audit/oauth.html?%s",http_build_query($params));
        }
        return sprintf('https://qianchuan.jinritemai.com/openapi/qc/audit/oauth.html?%s',http_build_query($params));
    }

    /**
     * @param string $code
     * @return string
     * @author Weida
     */
    protected function getTokenUrl(string $code): string
    {
        return 'https://ad.oceanengine.com/open_api/oauth2/access_token';
    }

    /**
     * @param string $accessToken
     * @return string
     * @author Weida
     */
    protected function getUserInfoUrl(string $accessToken): string
    {
        return 'https://ad.oceanengine.com/open_api/2/user/info/';
    }

    /**
     * @param string $accessToken
     * @return UserInterface
     * @throws Throwable
     * @author Weida
     */
    public function userFromToken(string $accessToken): UserInterface
    {
        $url = $this->getUserInfoUrl('');
        $resp = $this->getHttpClient()->request('GET',$url,[
            'headers'=>[
                'Access-Token'=>$accessToken
            ]
        ]);
        if($resp->getStatusCode()!=200){
            throw new RuntimeException('Request userinfo exception');
        }
        $arr = json_decode($resp->getBody()->getContents(),true);
        if (empty($arr['id'])) {
            throw new RuntimeException('Failed to get userinfo: ' . json_encode($arr, JSON_UNESCAPED_UNICODE));
        }
        return new User([
            'uid'=>$arr['id'],
            'nickname'=>$arr['display_name'],
            'email'=>$arr['email']??'',
        ]);
    }

    /**
     * @param string $code
     * @return UserInterface
     * @throws Throwable
     * @author Weida
     */
    public function userFromCode(string $code): UserInterface
    {
        $tokenArr = $this->tokenFromCode($code);
        return $this->userFromToken($tokenArr['access_token']);
    }

    /**
     * @param string $code
     * @return array
     * @throws Throwable
     * @author Weida
     */
    public function tokenFromCode(string $code):array{
        $url =  $this->getTokenUrl('');
        $params=[
            'app_id'=>$this->getConfig()->get('client_id'),
            'secret'=>$this->getConfig()->get('client_secret'),
            'auth_code'=>$code
        ];
        $resp = $this->getHttpClient()->request('POST',$url,[
            'headers'=>[
                'Content-Type'=>'application/json'
            ],
            'body'=>json_encode($params)
        ]);
        if($resp->getStatusCode()!=200){
            throw new RuntimeException('Request access_token exception');
        }
        $arr = json_decode($resp->getBody()->getContents(),true);
        if (empty($arr['access_token'])) {
            throw new RuntimeException('Failed to get access_token: ' . json_encode($arr, JSON_UNESCAPED_UNICODE));
        }
        return $arr;
    }


}