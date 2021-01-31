<?php

namespace Core\Acl\Attributes;

use Core\Acl\Contracts\AttributeResolverInterface;
use Core\Acl\Request;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request as HttpRequest;

class ResourceAttributeResolver implements AttributeResolverInterface
{
    /** @var string */
    public $category = 'resource';

    /** @var Repository */
    protected $config;

    /** @var HttpRequest */
    protected $http;

    /**
     * ResourceAttributeResolver constructor.
     * @param Repository $config
     * @param HttpRequest $http
     */
    public function __construct(Repository $config, HttpRequest $http)
    {
        $this->config = $config;
        $this->http = $http;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getAttributes(Request $request)
    {
        $resource_map = $this->config->get('abac.resource_map', []);
        $model = $request->get('resource.model', null);
        $id = $request->get('resource.uuid', null);

        if ($model && array_key_exists($model, $resource_map) && $id) {
            $class = '\\'.$resource_map[$model];
            /** @var Model $instance */
            $instance = new $class;
            $instance->setHidden([]);
            $resource = $instance::query()->find($id);
            if ($resource) {
                $resource->setAttribute('model', $model);
            } else {
                $resource = (object) ['model', $model];
            }
        } else {
            $resource = $request->get('resource');
            // Merge params from http request for get requests (index, show, create).
            foreach ($this->http->all() as $key => $value) {
                data_set($resource, $key, $value);
            }
        }

        return $resource;
    }
}
