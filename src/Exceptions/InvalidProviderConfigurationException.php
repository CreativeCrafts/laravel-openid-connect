<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Exceptions;

use DomainException;
use Symfony\Component\HttpFoundation\Response;

final class InvalidProviderConfigurationException extends DomainException
{
    public function __construct(
        string $message = 'Provider configuration not found for',
        int $code = Response::HTTP_NOT_ACCEPTABLE
    ) {
        parent::__construct($message, $code);
    }
}
