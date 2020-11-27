<?php

namespace Core\Acl\Attributes;

use Core\Acl\Contracts\AttributeResolverInterface;
use Core\Acl\Request;

class ActionAttributeResolver implements AttributeResolverInterface
{
    /** @var string */
    public $category = 'action';

    /**
     * Get action attribute
     * @param Request $request
     * @return string
     */
    public function getAttributes(Request $request)
    {
        return $request->get('action');
    }
}