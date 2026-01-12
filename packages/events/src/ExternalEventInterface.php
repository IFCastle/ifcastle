<?php

declare(strict_types=1);

namespace IfCastle\Events;

interface ExternalEventInterface extends EventInterface
{
    /**
     * @var string
     */
    final public const string EVENT_TOPICS = 'topics';

    /**
     * @var string
     */
    final public const string EVENT_PRODUCER = 'producer';

    /**
     * @return string[]
     */
    public function getEventTopics(): array;

    public function setEventTopics(array $eventTopics): static;

    public function addEventTopic(string $eventTopic): static;

    public function getEventProducer(): ?string;

    public function setEventProducer(string $eventProducer): static;
}
