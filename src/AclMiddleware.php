<?php

namespace Core\Acl;

use Closure;
use Core\Acl\Request as AccessRequest;
use Core\Acl\Contracts\Effect;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Carbon\Carbon;

class AclMiddleware
{

    /**
     * Policy enforcement point
     * @var PolicyEnforcementPoint
     */
    private $pep;

    /**
     * AclMiddleware constructor.
     * @param PolicyEnforcementPoint $pep
     */
    public function __construct(PolicyEnforcementPoint $pep)
    {
        $this->pep = $pep;
    }

    /**
     * Handle incoming request.
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @param string|null $action
     * @return mixed
     */
    public function handle($request, Closure $next, $action = null, $model = null)
    {
        $action = [
            'id' => !is_null($action) ? $action : $this->resolveAction($request)
        ];
        $environment = $this->resolveEnvironment($request);
        $resource    = $this->resolveResource($request);
        $subject     = $this->resolveSubject($request);

        $access_request  = new AccessRequest(compact('action','environment', 'resource', 'subject'));

        /** @var Response */
        $response = $this->pep->request($access_request);

        if ((string) $response == Effect::PERMIT) {
            return $next($request);
        }

        throw new AccessDeniedHttpException('Access denied.');
    }


    public function resolveSubject($http_request)
    {
        return $http_request->user();
    }

    /**
     * Get actions handled by controller
     * GET      /photos                 index       photos.index
     * GET      /photos/create          create      photos.create
     * POST     /photos                 store       photos.store
     * GET      /photos/{photo}         show        photos.show
     * GET      /photos/{photo}/edit    edit        photos.edit
     * PUT      /photos/{photo}         update      photos.update
     * DELETE   /photos/{photo}         destroy     photos.destroy
     * @param \Illuminate\Http\Request $http_request
     * @return string
     */
    private function resolveAction(\Illuminate\Http\Request $http_request)
    {
        $route = $http_request->route();
        $method = $http_request->method();
        $segments = $http_request->segments();

        $action = null;

        if ($method == 'GET') {
            // show - this is the default...
            $action = 'show';
            // index
            if (count($segments) == 1) {
                $action = 'index';
            } else {
                // edit
                if (in_array('edit', $segments)) {
                    $action = 'edit';
                }
                // create
                if (in_array('create', $segments)) {
                    $action = 'create';
                }

            }
        }
        if ($method == 'POST') {
            $action = 'store';
        }
        if ($method == 'PUT' || $method == 'PATCH') {
            $action = 'update';
        }
        if ($method == 'DELETE') {
            $action = 'delete';
        }

        return $action;
    }

    /**
     * @param \Illuminate\Http\Request $http_request
     * @return array
     */
    private function resolveEnvironment(\Illuminate\Http\Request $http_request)
    {
        return [];
    }

    /**
     * @param $http_request
     * @return array|null[]
     */
    private function resolveResource($http_request)
    {
        $segments = $http_request->segments();
        $resource_type = null;
        $resource_uuid = null;

        if (isset($segments[0])) {
            $resource_type = $segments[0];
        }

        if (isset($segments[1]) && preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $segments[1])) {
            $resource_uuid = $segments[1];
        }

        return [
            'model' => $resource_type,
            'uuid' => $resource_uuid,
        ];
    }
}