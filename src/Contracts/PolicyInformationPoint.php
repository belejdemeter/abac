<?php

namespace Core\Acl\Contracts;

use Core\Acl\Request;

interface PolicyInformationPoint
{
    /**
     * Set attribute resolver.
     * @param string $key
     * @param string $class
     */
    public function setResolver(string $key, string $class);

    /**
     * Request attributes.
     * @param Request $request
     * @return array
     */
    public function requestAttributes(Request $request);
}