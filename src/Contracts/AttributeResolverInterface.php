<?php

namespace Core\Acl\Contracts;

use ArrayAccess;
use Core\Acl\Request;

interface AttributeResolverInterface
{
    /**
     * The PDP requests any additional subject, resource, action, environment
     * and other categories attributes from the context handler.
     * @param Request $request
     * @return iterable|null
     */
    public function getAttributes(Request $request);
}