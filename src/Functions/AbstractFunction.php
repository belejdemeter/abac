<?php

namespace Core\Acl\Functions;

use Core\Acl\Contracts\Target;
use Core\Acl\Request;

abstract class AbstractFunction
{
    public function evaluate(Request $request, ?string $designator, ?string $selector, $value = null)
    {
        $expected = $request->get($designator, null);
        if ($selector != null) {
            $value = $request->get($selector);
        }
        try {
            return $this->handle($expected, $value);
        } catch (\Exception $e) {
            return Target::INDETERMINATE;
        }
    }

    /**
     * @param mixed|null $expected
     * @param mixed|null $value
     * @return string|bool
     */
    abstract protected function handle($expected = null, $value = null);
}