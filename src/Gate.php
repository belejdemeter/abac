<?php

namespace Core\Acl;

use Core\Acl\Contracts\Effect;
use Core\Acl\Request as AccessRequest;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Gate as Contract;
use Illuminate\Contracts\Auth\Guard;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Gate implements Contract
{
    /** @var \Core\Acl\Contracts\PolicyEnforcementPoint */
    private $pep;

    /** @var Guard */
    private $auth;

    /**
     * Gate constructor.
     * @param Contracts\PolicyEnforcementPoint $pep
     * @param Guard $auth
     */
    public function __construct(Contracts\PolicyEnforcementPoint $pep, Guard $auth)
    {
        $this->auth = $auth;
        $this->pep = $pep;
    }

    /**
     * @inheritDoc
     */
    public function has($ability)
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @inheritDoc
     */
    public function define($ability, $callback)
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @inheritDoc
     */
    public function resource($name, $class, array $abilities = null)
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @param string $class
     * @param string $policy
     * @return Gate|void
     */
    public function policy($class, $policy)
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @inheritDoc
     */
    public function before(callable $callback)
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @inheritDoc
     */
    public function after(callable $callback)
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @param string $ability
     * @param array $arguments
     * @return bool
     */
    public function allows($ability, $arguments = [])
    {
        $response = $this->request($ability, $arguments);
        return $response->permit();
    }

    /**
     * @param string $ability
     * @param array $arguments
     * @return bool
     */
    public function denies($ability, $arguments = [])
    {
        $response = $this->request($ability, $arguments);
        return $response->deny();
    }

    /**
     * @param iterable|string $abilities
     * @param array $arguments
     * @return bool
     */
    public function check($abilities, $arguments = [])
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @inheritDoc
     */
    public function any($abilities, $arguments = [])
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @param string $ability
     * @param array $arguments
     * @return Response|void
     */
    public function authorize($ability, $arguments = [])
    {
        $response = $this->request($ability, $arguments);
        if ((string)$response != Effect::PERMIT) {
            throw new AccessDeniedHttpException('Access denied.');
        }
    }

    /**
     * @param string $ability
     * @param array $arguments
     * @return Response
     */
    public function inspect($ability, $arguments = [])
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @inheritDoc
     */
    public function raw($ability, $arguments = [])
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @inheritDoc
     */
    public function getPolicyFor($class)
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @inheritDoc
     */
    public function forUser($user)
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @inheritDoc
     */
    public function abilities()
    {
        throw new \Exception('Not supported by ABAC');
    }

    /**
     * @param $ability
     * @param mixed $arguments
     * @return \Core\Acl\Response
     */
    protected function request($ability, $arguments = [])
    {
        $request = new AccessRequest([
            'action' => [
                'id' => $ability
            ],
            'resource' => $arguments,
            'subject' => !$this->auth->guest() ? $this->auth->user() : null
        ]);
        return $this->pep->request($request);
    }
}