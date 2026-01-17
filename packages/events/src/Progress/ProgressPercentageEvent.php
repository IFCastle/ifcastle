<?php

declare(strict_types=1);

namespace IfCastle\Events\Progress;

use IfCastle\Events\BaseEvent;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableValidatorInterface;

/**
 * ## ProgressPercentageEvent.
 *
 * An event that reflects the state of a process that is X percent complete.
 *
 */
class ProgressPercentageEvent extends BaseEvent implements ProgressPercentageInterface
{
    public function __construct(protected int $percentage, protected string $description = '', int $eventTimestamp = 0)
    {
        parent::__construct(eventName: self::EVENT_PROGRESS, eventTimestamp: $eventTimestamp);
    }

    #[\Override]
    public function isProgressCompleted(): bool
    {
        return $this->getPercentage() >= 100;
    }

    #[\Override]
    public function isProgressProcessing(): bool
    {
        return $this->getPercentage() < 100;
    }

    #[\Override]
    public function getPercentage(): int
    {
        return $this->percentage;
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[\Override]
    public function toArray(?ArraySerializableValidatorInterface $validator = null): array
    {
        return parent::toArray($validator) + [self::PERCENTAGE => $this->percentage];
    }

    #[\Override]
    public static function fromArray(array $array, ?ArraySerializableValidatorInterface $validator = null): static
    {
        return (new static($array[self::PERCENTAGE] ?? 0, $array[self::DESCRIPTION] ?? ''))->constructFromArray($array);
    }
}
