<?php

namespace Core\Acl\Algorithm;

use Core\Acl\Contracts\CombiningAlgorithm;
use Core\Acl\Contracts\Effect;

class FirstApplicable implements CombiningAlgorithm
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
            if ($decision == Effect::PERMIT) {
                return Effect::PERMIT;
            }
            if ($decision == Effect::INDETERMINATE) {
                return Effect::INDETERMINATE;
            }
        }
        return Effect::NOT_APPLICABLE;
    }
}