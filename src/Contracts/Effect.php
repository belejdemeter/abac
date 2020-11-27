<?php

namespace Core\Acl\Contracts;

interface Effect
{
    /** Rule effect "PERMIT" */
    const PERMIT = 'Permit';

    /** Rule effect "DENY" */
    const DENY = 'Deny';

    /**
     * Unable to evaluate the requested access.
     * Reasons for such inability include: missing attributes, network errors while retrieving policies,
     * division by zero during policy evaluation, syntax errors in the decision request or in the policy etc.
     */
    const INDETERMINATE = 'Indeterminate';

    /**
     * The PDP does not have any policy that applies to this decision request
     */
    const NOT_APPLICABLE = 'NotApplicable';

    /**
     * An “Indeterminate” from a policy or rule which could have evaluated to “Permit”, but not “Deny”
     */
    const INDETERMINATE_P = 'Indeterminate{P}';

    /**
     * An “Indeterminate” from a policy or rule which could have evaluated to “Deny” but not “Permit”
     */
    const INDETERMINATE_D = 'Indeterminate{D}';

    /**
     * An “Indeterminate” from a policy or rule which could have evaluated to “Deny” or “Permit”
     */
    const INDETERMINATE_DP = 'Indeterminate{DP}';
}