<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Exceptions;

use Exception;

final class OpenIDConnectClientException extends Exception
{
    /**
     * Constructs a new OpenIDConnectClientException.
     *
     * This exception is thrown when an error occurs during the OpenID Connect client operations.
     *
     * @param string $message The error message.
     * @param int $code The error code.
     * @param Exception|null $previous The previous exception, if any.
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
