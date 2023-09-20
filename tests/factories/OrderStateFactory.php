<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @method $this withColor(string $color)
 * @method $this withDeleted(int $deleted)
 * @method $this withDelivery(int $delivery)
 * @method $this withHidden(int $hidden)
 * @method $this withIdLang(int $idLang)
 * @method $this withIdOrderState(int $idOrderState)
 * @method $this withInvoice(int $invoice)
 * @method $this withLang(int|Lang|LangFactory $lang)
 * @method $this withLogable(int $logable)
 * @method $this withModuleName(string $moduleName)
 * @method $this withName(string $name)
 * @method $this withOrderState(int|OrderState|OrderStateFactory $orderState)
 * @method $this withPaid(int $paid)
 * @method $this withPdfDelivery(int $pdfDelivery)
 * @method $this withPdfInvoice(int $pdfInvoice)
 * @method $this withSendEmail(int $sendEmail)
 * @method $this withShipped(int $shipped)
 * @method $this withTemplate(string $template)
 * @method $this withUnremovable(int $unremovable)
 */
final class OrderStateFactory extends AbstractPsObjectModelFactory
{
    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withColor('#34209E')
            ->withDeleted(0)
            ->withDelivery(0)
            ->withHidden(0)
            ->withLogable(0)
            ->withPaid(0)
            ->withPdfDelivery(0)
            ->withPdfInvoice(0)
            ->withShipped(0)
            ->withUnremovable(0)
            ->withLang(1);
    }

    protected function getObjectModelClass(): string
    {
        return OrderState::class;
    }
}
