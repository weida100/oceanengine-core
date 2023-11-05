<?php
declare(strict_types=1);
/**
 * Author: Weida
 * Date: 2023/11/5 23:17
 * Email: sgenmi@gmail.com
 */

namespace Weida\OceanengineCore;

use Psr\Http\Message\ResponseInterface;
use Weida\OceanengineCore\Contract\WithAccessTokenClientInterface;

class WithAccessTokenClient implements WithAccessTokenClientInterface
{

    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        // TODO: Implement request() method.
    }

    public function get(string $uri, array $options = []): ResponseInterface
    {
        // TODO: Implement get() method.
    }

    public function post(string $uri, array $options = []): ResponseInterface
    {
        // TODO: Implement post() method.
    }

    public function postJson(string $uri, array $options = []): ResponseInterface
    {
        // TODO: Implement postJson() method.
    }
}