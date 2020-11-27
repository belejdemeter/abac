<?php 

namespace Core\Acl\Strategies;

interface PolicyEnforcementStrategy
{
	public function getDecision($decision);
}