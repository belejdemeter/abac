<?php 
namespace Core\Acl\Exception;

class ProcessingError extends \RuntimeException
{
	public function error_message() {
		return 'urn:oasis:names:tc:xacml:1.0:status:processing-error';
	}
}