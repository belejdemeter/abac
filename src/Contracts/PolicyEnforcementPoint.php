<?php

namespace Core\Acl\Contracts;

use Core\Acl\Request;
use Core\Acl\Response;

interface PolicyEnforcementPoint
{
    /**
     * Send a decision request to PDP and returns the response to the PEP.
     * @param Request $request
     * @return Response
     */
    public function request(Request $request);
}