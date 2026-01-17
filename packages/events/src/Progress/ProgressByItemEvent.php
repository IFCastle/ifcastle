<?php

declare(strict_types=1);

namespace IfCastle\Events\Progress;

use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableValidatorInterface;
use IfCastle\TypeDefinitions\ResultInterface;

class ProgressByItemEvent extends ProgressPercentageEvent implements ProgressItemInterface
{
    protected int $itemCurrent      = 0;

    protected int $itemTotal        = 0;


    public function __construct(int $current, int $total, string $itemName = '', protected ?ResultInterface $result = null, int $eventTimestamp = 0)
    {
        $percentage                 = 0;

        if ($total > 0) {
            $percentage             = (int) \floor($current / $total * 100);
        }

        parent::__construct(percentage: $percentage, description: $itemName, eventTimestamp: $eventTimestamp);

        $this->itemTotal            = $total;
        $this->itemCurrent          = $current;
    }

    #[\Override]
    public function getProgressItemName(): string
    {
        return $this->description;
    }

    #[\Override]
    public function getProgressItemCurrent(): int
    {
        return $this->itemCurrent;
    }

    #[\Override]
    public function getProgressItemTotal(): int
    {
        return $this->itemTotal;
    }

    #[\Override]
    public function getProgressItemResult(): ?ResultInterface
    {
        return $this->result;
    }

    #[\Override]
    public function toArray(?ArraySerializableValidatorInterface $validator = null): array
    {
        return parent::toArray($validator) + [
            self::ITEM_NAME     => $this->description,
            self::ITEM_CURRENT  => $this->itemCurrent,
            self::ITEM_TOTAL    => $this->itemTotal,
        ];
    }

    #[\Override]
    public static function fromArray(array $array, ?ArraySerializableValidatorInterface $validator = null): static
    {
        return (new static($array[self::ITEM_CURRENT] ?? 0, $array[self::ITEM_TOTAL] ?? 0, $array[self::ITEM_NAME] ?? ''))
            ->constructFromArray($array);
    }
}
