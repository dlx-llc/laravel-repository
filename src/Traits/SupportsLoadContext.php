<?php

namespace Deluxetech\LaRepo\Traits;

use Deluxetech\LaRepo\Contracts\LoadContextContract;

trait SupportsLoadContext
{
    /**
     * The load context.
     *
     * @var LoadContextContract|null
     */
    protected ?LoadContextContract $context = null;

    /** @inheritdoc */
    public function getLoadContext(): ?LoadContextContract
    {
        return $this->context;
    }

    /** @inheritdoc */
    public function setLoadContext(?LoadContextContract $context): static
    {
        $this->context = $context;

        return $this;
    }
}
