<?php

namespace Core\Acl\Attributes;

use Core\Acl\Contracts\AttributeResolverInterface;
use Core\Acl\Request;
use Illuminate\Contracts\Config\Repository;

class ResourceAttributeResolver implements AttributeResolverInterface
{
    /** @var string */
    public $category = 'resource';

    /** @var Repository */
    protected $config;

    /**
     * ResourceAttributeResolver constructor.
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
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
            $query = new $class;
            $instance = $query::find($id);
            $instance->model = $model;
        } else {
            return $request->get('resource');
        }
    }
}