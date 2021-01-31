<?php

namespace Core\Acl\Functions;

class AnyOf extends AbstractFunction
{
    /**
     * @param mixed|null $expected
     * @param mixed|null $value
     * @return string|bool
     */
    protected function handle($expected = null, $value = null)
    {
        if (!is_array($value)) $value = [$value];
        if (!is_array($expected)) $expected = [$expected];
        return count(array_intersect($expected, $value)) > 0;
    }
}