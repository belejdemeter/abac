<?php

namespace Core\Acl\Contracts;

use Core\Acl\Request;

interface PolicyDecisionPoint
{
    /**
     * Returns the response context
     * @param Request $request
     * @return string
     */
    public function evaluate(Request $request);
}