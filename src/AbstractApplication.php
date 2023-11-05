<?php
declare(strict_types=1);
/**
 * Author: Weida
 * Date: 2023/11/4 23:26
 * Email: sgenmi@gmail.com
 */

namespace Weida\OceanengineCore;

use Weida\Oauth2Core\Config;
use Weida\Oauth2Core\Contract\ConfigInterface;
use Weida\OceanengineCore\Contract\AccessTokenInterface;

class Application
{
    private Oauth2 $oauth2;
    private ConfigInterface $config;
    private AccessTokenInterface $accessToken;

    public function __construct(array $config)
    {
        $this->setConfig(new Config($config));
    }

    /**
     * @return ConfigInterface
     * @author Weida
     */
    public function getConfig():ConfigInterface {
        return $this->config;
    }

    /**
     * @param ConfigInterface $config
     * @return $this
     * @author Weida
     */
    public function setConfig(ConfigInterface $config):static {
        $this->config = $config;
        return $this;
    }

    /**
     * @return Oauth2
     * @author Weida
     */
    public function getOauth2():Oauth2{
        if(empty($this->oauth2)){
            $this->oauth2 = new Oauth2($this->getConfig());
        }
        return $this->oauth2;
    }

    public function getAccessToken():AccessTokenInterface {
        if(empty($this->accessToken)){
            $this->accessToken = new AccessToken(
              $this->getConfig(),

            );
        }
    }

}