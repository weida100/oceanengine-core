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
    /**
     * @var string api地址
     */
    protected string $_url = '';

    /**
     * @return string
     * @author Weida
     */
    public function getUrl(): string
    {
        return $this->_url;
    }

    /**
     * @return array
     * @author Weida
     */
    public function getParams(): array
    {
       $vars = get_object_vars($this);
       unset($vars['_url']);
       return $vars;
    }
}