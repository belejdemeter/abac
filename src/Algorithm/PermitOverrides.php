<?php

namespace Core\Acl\Algorithm;

use Core\Acl\Contracts\CombiningAlgorithm;
use Core\Acl\Contracts\Effect;

class PermitOverrides implements CombiningAlgorithm
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
        $atLeastOneDeny = false;

        foreach ($decisions as $decision) {

            if ($decision == Effect::DENY) {
                $atLeastOneDeny = true;
                continue;
            }
            if ($decision == Effect::PERMIT) {
                return Effect::PERMIT;
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
        if ($atLeastOneErrorP && ($atLeastOneErrorD || $atLeastOneDeny)) {
            return Effect::INDETERMINATE_DP;
        }
        if ($atLeastOneErrorP) {
            return Effect::INDETERMINATE_P;
        }
        if ($atLeastOneDeny) {
            return Effect::DENY;
        }
        if ($atLeastOneErrorD) {
            return Effect::INDETERMINATE_D;
        }
        return Effect::NOT_APPLICABLE;
    }
}