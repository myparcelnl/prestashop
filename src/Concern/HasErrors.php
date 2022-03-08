<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Concern;

use Exception;
use Gett\MyparcelBE\Logger\ApiLogger;
use InvalidArgumentException;
use MyParcelBE;

trait HasErrors
{
    /**
     * @var string[]
     */
    private $errors = [];

    /**
     * @param  string|\Exception $error
     *
     * @return void
     */
    protected function addError($error): void
    {
        $exceptionMessage = null;

        if ($error instanceof Exception) {
            $exceptionMessage = $error->getMessage();

            if (_PS_MODE_DEV_) {
                $exceptionMessage .= sprintf(' (Thrown at %s:%s)', $error->getFile(), $error->getLine());
            }

            ApiLogger::addLog($error, ApiLogger::DEBUG);
        }

        if (is_string($error)) {
            $exceptionMessage = MyParcelBE::getModule()
                ->l($error, 'adminlabelcontroller');
        }

        if (! $exceptionMessage) {
            throw new InvalidArgumentException('Either $e or $text must be passed.');
        }

        $this->errors[] = $exceptionMessage;
    }

    /**
     * @return array
     */
    protected function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    protected function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
