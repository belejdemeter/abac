<?php

namespace Core\Acl\Functions;

class AllOf extends AbstractFunction
{
    /**
     * @param mixed|null $expected
     * @param mixed|null $value
     * @return string|bool
     */
    protected function handle($expected = null, $value = null)
    {
        $array1 = array_unique($expected);
        $array2 = array_unique($value);
        return count(array_diff($array1, $array2)) == 0;
    }
}