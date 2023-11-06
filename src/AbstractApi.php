<?php
declare(strict_types=1);
/**
 * Author: Weida
 * Date: 2023/11/6 20:42
 * Email: sgenmi@gmail.com
 */

namespace Weida\OceanengineCore;

use Weida\OceanengineCore\Contract\ApiInterface;

abstract class AbstractApi implements ApiInterface
{
    protected string $url = '';

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getParams(): array
    {
       $vars = get_object_vars($this);
       print_r($vars);
       exit();
    }
}