<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Configuration;

use MyParcelNL\PrestaShop\Module\Configuration\Form\AbstractForm;
use MyParcelNL\Sdk\src\Model\BaseModel;

class SettingsMenuItem extends BaseModel
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var class-string<AbstractForm>
     */
    private $form;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $title;

    /**
     * @param  array $data
     */
    public function __construct(array $data = [])
    {
        $this->description = $data['description'] ?? null;
        $this->form        = $data['form'] ?? null;
        $this->icon        = $data['icon'] ?? null;
        $this->name        = $data['name'] ?? null;
        $this->title       = $data['title'] ?? null;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description ?? $this->getTitle();
    }

    /**
     * @return class-string<AbstractForm>
     */
    public function getForm(): string
    {
        return $this->form;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
