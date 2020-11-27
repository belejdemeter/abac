<?php

namespace Core\Acl\Algorithm;

use Core\Acl\Contracts\CombiningAlgorithm;
use Core\Acl\Contracts\Effect;

class DenyOverrides implements CombiningAlgorithm
{
    /**
     * @param array $decisions
     * @return string
     */
    public function evaluate(array $decisions)
    {
        $atLeastOneErrorD = false;
        $atLeastOneErrorP = false;
        $atLeastOneErrorDP = false;
        $atLeastOnePermit = false;

        foreach ($decisions as $decision) {

            if ($decision == Effect::DENY) {
                return Effect::DENY;
            }
            if ($decision == Effect::PERMIT) {
                $atLeastOnePermit = true;
                continue;
            }
            if ($decision == Effect::NOT_APPLICABLE) {
                continue;
            }
            if ($decision == Effect::INDETERMINATE_D) {
                $atLeastOneErrorD = true;
                continue;
            }
            if ($decision == Effect::INDETERMINATE_P) {
                $atLeastOneErrorP = true;
                continue;
            }
            if ($decision == Effect::INDETERMINATE_DP) {
                $atLeastOneErrorDP = true;
                continue;
            }
        }

        if ($atLeastOneErrorDP) {
            return Effect::INDETERMINATE_DP;
        }
        if ($atLeastOneErrorD && ($atLeastOneErrorP || $atLeastOnePermit)) {
            return Effect::INDETERMINATE_DP;
        }
        if ($atLeastOneErrorD) {
            return Effect::INDETERMINATE_D;
        }
        if ($atLeastOnePermit) {
            return Effect::PERMIT;
        }
        if ($atLeastOneErrorP) {
            return Effect::INDETERMINATE_P;
        }
        return Effect::NOT_APPLICABLE;
    }
}