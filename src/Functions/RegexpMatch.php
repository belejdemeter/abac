<?php

namespace Core\Acl\Functions;

class RegexpMatch extends AbstractFunction
{
    /**
     * @param mixed|null $expected
     * @param mixed|null $value
     * @return string|bool
     */
    protected function handle($expected = null, $value = null)
    {
        return preg_match('/'.$expected.'/i', $value);
    }
}