<?php

namespace Core\Acl\Algorithm;

use Core\Acl\Contracts\CombiningAlgorithm;
use Core\Acl\Contracts\Effect;

class PermitUnlessDeny implements CombiningAlgorithm
{
    /**
     * @param array $decisions
     * @return string
     */
    public function evaluate(array $decisions)
    {
        foreach ($decisions as $decision) {
            if ($decision == Effect::DENY) {
                return Effect::DENY;
            }
        }
        return Effect::PERMIT;
    }
}