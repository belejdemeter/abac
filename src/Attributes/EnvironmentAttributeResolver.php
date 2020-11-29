<?php

namespace Core\Acl\Attributes;

use Carbon\Carbon;
use Core\Acl\Contracts\AttributeResolverInterface;
use Core\Acl\Request;
use Illuminate\Http\Request as HttpRequest;

class EnvironmentAttributeResolver implements AttributeResolverInterface
{
    /** @var string */
    public $category = 'environment';

    /** @var HttpRequest */
    protected $http;

    /**
     * ResourceAttributeResolver constructor.
     * @param HttpRequest $http
     */
    public function __construct(HttpRequest $http)
    {
        $this->http = $http;
    }

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
            'request' => $this->http->all(),
        ];
        return array_merge($default, $request->get($this->category));
    }
}