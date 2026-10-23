<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Pipeline;

use IfCastle\Exceptions\ClientException;
use IfCastle\RestApi\Rest;
use IfCastle\ServiceManager\AsServiceMethod;
use IfCastle\TypeDefinitions\FromEnv;

use function Async\delay;

#[Rest('/pipeline')]
final class PipelineService
{
    public const string FAILURE     = 'PipelineService::fail() failed on purpose';

    /**
     * Returns the id it was called with after waiting $delay milliseconds,
     * so concurrent calls suspend inside the pipeline and interleave.
     *
     * @return array<string, string>
     */
    #[AsServiceMethod]
    #[Rest('/echo/{id}/{delay}', methods: Rest::GET)]
    public function echo(string $id, int $delay): array
    {
        if ($delay > 0) {
            delay($delay);
        }

        return ['id' => $id];
    }

    #[AsServiceMethod]
    #[Rest('/injected-path', methods: Rest::GET)]
    public function injectedPath(#[FromEnv(key: RequestPathRecorder::KEY, fromRequestEnv: true)] string $path = ''): string
    {
        return $path;
    }

    #[AsServiceMethod]
    #[Rest('/limit', methods: Rest::GET)]
    public function limit(?int $limit = 20): int
    {
        return $limit ?? -1;
    }

    #[AsServiceMethod]
    #[Rest('/text/{value}', methods: Rest::GET)]
    public function text(string $value): string
    {
        return $value;
    }

    #[AsServiceMethod]
    #[Rest('/sum', methods: Rest::POST)]
    public function sum(int $a, int $b): int
    {
        return $a + $b;
    }

    /**
     * Joins a plain form field with one that may come in the "json" form field.
     */
    #[AsServiceMethod]
    #[Rest('/form', methods: Rest::POST)]
    public function form(string $title, string $note = ''): string
    {
        return $title . '|' . $note;
    }

    #[AsServiceMethod]
    #[Rest('/fail-visibly', methods: Rest::GET)]
    public function failVisibly(): string
    {
        throw new ClientException(self::FAILURE);
    }

    #[AsServiceMethod]
    #[Rest('/fail', methods: Rest::GET)]
    public function fail(): string
    {
        throw new \RuntimeException(self::FAILURE);
    }
}
