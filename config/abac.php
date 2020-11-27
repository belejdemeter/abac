<?php

use Core\Acl\Attributes\ActionAttributeResolver;
use Core\Acl\Attributes\EnvironmentAttributeResolver;
use Core\Acl\Attributes\ResourceAttributeResolver;
use Core\Acl\Attributes\SubjectAttributeResolver;
use Core\Acl\Strategies\DenyBiasedStrategy;

return [
    /** The policy storage driver. */
    'repository' => \Core\Acl\Repository\FileRepository::class,

    /** The Root level access strategy. */
    'default_strategy' => DenyBiasedStrategy::class,

    /** Automatic attribute resolvers. */
    'resolvers' => [
        'action'        => ActionAttributeResolver::class,
        'environment'   => EnvironmentAttributeResolver::class,
        'resource'      => ResourceAttributeResolver::class,
        'subject'       => SubjectAttributeResolver::class,
    ],

    /** Aliases for models */
    'resource_map' => [
        'user' => App\User::class
    ]
];