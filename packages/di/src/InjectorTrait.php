<?php

declare(strict_types=1);

namespace IfCastle\DI;

trait InjectorTrait
{
    public function injectDependencies(array $dependencies, DependencyInterface $self): static
    {
        foreach ($self->getDependencyDescriptors() as $descriptor) {
            $property               = $descriptor->getDependencyProperty();

            if (isset($this->$property)) {
                continue;
            }

            $this->$property        = $dependencies[$property] ?? null;
        }

        return $this;
    }

    public function initializeAfterInject(): static
    {
        return $this;
    }
}
