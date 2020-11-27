<?php

namespace Core\Acl\Attributes;

use Carbon\Carbon;
use Core\Acl\Contracts\AttributeResolverInterface;
use Core\Acl\Request;

class EnvironmentAttributeResolver implements AttributeResolverInterface
{
    /** @var string */
    public $category = 'environment';

    /**
     * @param Request $request
     * @return iterable|null
     */
    public function getAttributes(Request $request)
    {
        $default = [
            'service_id' => getenv('SERVICE_ID'),
            'time' => Carbon::now()->format('H:i:s'),
            'date' => Carbon::today()->format('Y-m-d'),
            'datetime' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
        return array_merge($default, $request->get($this->category));
    }
}