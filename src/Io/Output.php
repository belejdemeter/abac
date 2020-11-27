<?php

namespace Core\Acl\Io;

use Core\Acl\Policy\Policy;
use Core\Acl\Policy\PolicySet;

class Output
{
    /**
     * @param PolicySet $policy_set
     * @return array
     */
    public function toArray($policy_set)
    {
        $this->walkRecursive($policy_set);
    }

    protected function walkRecursive($node)
    {
        if ($node instanceof PolicySet) {

        }
    }

}