<?php 
namespace Core\Acl\Exception;

class SyntaxError extends \RuntimeException
{
	public function error_message() {
		return 'urn:oasis:names:tc:xacml:1.0:status:syntax-error';
	}
}