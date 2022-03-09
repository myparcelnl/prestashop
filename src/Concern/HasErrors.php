<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Concern;

use Exception;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Model\Core\Order;
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
     * @param  string            $prefix
     *
     * @return void
     */
    protected function addError($error, string $prefix = ''): void
    {
        $exceptionMessage = null;

        if ($error instanceof Exception) {
            $exceptionMessage = $prefix . $error->getMessage();

            if (_PS_MODE_DEV_) {
                $exceptionMessage .= sprintf(' (Thrown at %s:%s)', $error->getFile(), $error->getLine());
            }

            ApiLogger::addLog($error, ApiLogger::DEBUG);
        }

        if (is_string($error)) {
            $exceptionMessage = $prefix . MyParcelBE::getModule()
                    ->l($error, 'adminlabelcontroller');
        }

        if (! $exceptionMessage) {
            throw new InvalidArgumentException('Either $e or $text must be passed.');
        }

        $this->errors[] = $exceptionMessage;
    }

    /**
     * @param         $errors
     * @param  string $prefix
     *
     * @return void
     */
    protected function addErrors($errors, string $prefix = ''): void
    {
        foreach ($errors as $error) {
            $this->addError($error, $prefix);
        }
    }

    /**
     * Add an error message prefixed by "Order {id}:"
     *
     * @param  string|\Exception                     $error
     * @param  \Gett\MyparcelBE\Model\Core\Order|int $orderOrId
     *
     * @return void
     */
    protected function addOrderError($error, $orderOrId): void
    {
        $orderId = $orderOrId instanceof Order ? $orderOrId->getId() : $orderOrId;
        $prefix  = sprintf(
            '%s #%d: ',
            MyParcelBE::getModule()
                ->l('Order', 'AdminGlobal'),
            $orderId
        );
        $this->addError($error, $prefix);
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
