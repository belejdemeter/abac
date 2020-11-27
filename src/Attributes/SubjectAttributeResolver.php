<?php

namespace Core\Acl\Attributes;

use Core\Acl\Contracts\AttributeResolverInterface;
use Core\Acl\Request;

class SubjectAttributeResolver implements AttributeResolverInterface
{
    /** @var string */
    public $category = 'subject';

    /**
     * @param Request $request
     * @return mixed
     */
    public function getAttributes(Request $request)
    {
        return $request->get($this->category);
    }
}