<?php

namespace Core\Acl\Functions;

class StrictEqual extends AbstractFunction
{
    /**
     * @param mixed|null $expected
     * @param mixed|null $value
     * @return string|bool
     */
    protected function handle($expected = null, $value = null)
    {
        return $expected === $value;
    }
}