<?php

namespace Clearsh\LaravelCollectionExt\Exceptions;

class LicenseException extends \RuntimeException
{
    public function __construct(string $message = 'Licença inválida. Contate o suporte.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
