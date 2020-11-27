<?php

namespace Core\Acl\Contracts;

interface Target
{
    const MATCH = 'Match';

    const NO_MATCH = 'No match';

    const INDETERMINATE = 'Indeterminate';
}