<?php

namespace App\Exceptions;

use InvalidArgumentException;

class InvalidUrlException extends InvalidArgumentException
{

    /**
     * @var string
     */
    private $url;

    function __construct($message, $url)
    {
        parent::__construct($message, 500);

        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
