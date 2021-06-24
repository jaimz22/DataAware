<?php
namespace VertigoLabs\DataAware\Exceptions;

use Exception;

class DataNotFoundNoDefaultException extends Exception
{
    public function __construct($key, $context)
    {
        parent::__construct(sprintf('No data item with the key "%s" can be found in %s. Check your key name or provide a default value.', $key, $context));
    }
}
