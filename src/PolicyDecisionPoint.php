<?php

namespace Core\Acl;

use Core\Acl\Contracts\PolicyDecisionPoint as Contract;
use Core\Acl\Contracts\PolicyInformationPoint as PIP;
use Core\Acl\Contracts\PolicyRepository;
use Core\Acl\Exception\SyntaxError;
use Core\Acl\Policy\Policy;
use Core\Acl\Policy\PolicySet;

class PolicyDecisionPoint implements Contract
{

    /**
     * Policy repo
     * @var PolicyRepository
     */
    protected $repository;


    /**
     * PIP
     * @var PIP
     */
    protected $pip;


    /**
     * Class Constructor
     * @param PolicyRepository $repository
     * @param PIP $pip
     */
    public function __construct(PolicyRepository $repository, PIP $pip)
    {
        $this->repository = $repository;
        $this->pip = $pip;
    }


    /**
     * Returns the response context
     * @param Request $request
     * @return string [«разрешить», «запретить», «не применимо», «не определено»]
     */
    public function evaluate(Request $request)
    {
        $root_policy = $this->requestPolicies();
        $attributes = $this->pip->requestAttributes($request);

        // Fill request with additional attributes received from PIP...
        foreach ($attributes as $category => $values) {
            foreach ($values as $key => $value) $request->set("$category.$key", $value);
        }

        return $root_policy->evaluate($request);
    }


    /**
     * The <PolicySet> element contains a set of <Policy> or other <PolicySet> elements
     * and a specified procedure for combining the results of their evaluation.
     * It is the standard means for combining separate policies into a single combined policy.
     * @return PolicySet
     */
    public function requestPolicies()
    {
        $data = $this->repository->fetch();
        if ($data instanceof PolicySet) {
            return $data;
        } elseif ($data instanceof Policy) {
            return $this->combine($data);
        } elseif (is_iterable($data)) {
            return $this->combine($data);
        } else {
            throw new SyntaxError('Invalid policy structure');
        }
    }

    /**
     * @param mixed $policies
     * @return PolicySet
     */
    protected function combine($policies)
    {
        // The default root policy combing algorithm is deny-override.
        $algorithm = new Algorithm\DenyOverrides;

        if ($policies instanceof Policy || $policies instanceof PolicySet) {
            $children = [$policies];
        } else {
            $children = $policies;
        }

        return new PolicySet([
            'target' => null,
            'algorithm' => $algorithm,
            'policies' => $children,
        ]);
    }


    /**
     * @todo 7.17 Exception handling.
     * XACML specifies behavior for the PDP in the following situations.
     * 7.17.1 Unsupported functionality
     * 7.17.2 Syntax and type errors
     * 7.17.3 Missing attributes
     */
    private function handleException()
    {
    }

}