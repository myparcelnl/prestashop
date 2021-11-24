<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks;

use Gett\MyparcelBE\Service\Concern\HasInstance;
use MyParcelBE;
use PrestaShop\PrestaShop\Adapter\Entity\Context;

class RenderService
{
    use HasInstance;

    /**
     * @var \PrestaShop\PrestaShop\Adapter\Entity\Context
     */
    protected $context;

    /**
     * @var \MyParcelBE
     */
    protected $module;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param  \PrestaShop\PrestaShop\Adapter\Entity\Context|null $context
     *
     * @throws \Exception
     */
    public function __construct(Context $context = null)
    {
        $this->module  = MyParcelBE::getModule();
        $this->context = $context ?? Context::getContext();
        $this->twig    = $this->module->get('twig');
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function render(string $template, array $context = []): string
    {
        return $this->twig->render("@Modules/{$this->module->name}/views/templates/$template", $context);
    }

    /**
     * @param  string $method Corresponds to a method name defined on window.MyParcel in main.ts.
     * @param  array  $context
     *
     * @return string
     * @throws \Exception
     */
    protected function renderWithContext(string $method, array $context = []): string
    {
        return $this->render('admin/context.twig', compact('method', 'context'));
    }
}
