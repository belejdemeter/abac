<?php

namespace Core\Acl\Contracts;

interface CombiningAlgorithm
{
    public function evaluate(array $decisions);
}