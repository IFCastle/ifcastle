<?php

declare(strict_types=1);

namespace IfCastle\Application\RequestEnvironment;

use IfCastle\DesignPatterns\ExecutionPlan\ExecutionPlan;
use IfCastle\DesignPatterns\ExecutionPlan\PlanExecutorWithFinalAndStageControl;
use IfCastle\DesignPatterns\ExecutionPlan\StagePointer;
use IfCastle\DesignPatterns\ExecutionPlan\WeakStaticClosureExecutor;
use IfCastle\Exceptions\ClientAvailableInterface;
use IfCastle\Exceptions\ClientException;
use IfCastle\Exceptions\LogicalException;
use IfCastle\Exceptions\UnexpectedValue;
use IfCastle\Protocol\Exceptions\ParseException;
use IfCastle\TypeDefinitions\ResultInterface;

class RequestPlan extends ExecutionPlan implements RequestPlanInterface
{
    public function __construct()
    {
        parent::__construct(
            new WeakStaticClosureExecutor(
                /* @phpstan-ignore-next-line */
                static fn(self $self, mixed $handler, string $stage, RequestEnvironmentInterface $requestEnvironment)
                        => $self->executeHandler($handler, $requestEnvironment), $this
            ),
            [
                self::RAW_BUILD,
                self::BUILD,
                self::BEFORE_DISPATCH,
                self::DISPATCH,
                self::BEFORE_EXECUTE,
                self::EXECUTE,
                self::RESPONSE,
                self::AFTER_RESPONSE,
                self::FINALLY,
            ],
            new PlanExecutorWithFinalAndStageControl()
        );
    }

    /**
     * @throws UnexpectedValue
     * @throws LogicalException
     */
    #[\Override]
    public function addRawBuildHandler(callable $handler): static
    {
        return $this->addStageHandler(self::RAW_BUILD, $handler);
    }

    /**
     * @throws LogicalException
     * @throws UnexpectedValue
     */
    #[\Override]
    public function addBuildHandler(callable $handler): static
    {
        return $this->addStageHandler(self::BUILD, $handler);
    }

    /**
     * @throws LogicalException
     * @throws UnexpectedValue
     */
    #[\Override]
    public function addBeforeDispatchHandler(callable $handler): static
    {
        return $this->addStageHandler(self::BEFORE_DISPATCH, $handler);
    }

    /**
     * @throws LogicalException
     * @throws UnexpectedValue
     */
    #[\Override]
    public function addDispatchHandler(callable $handler): static
    {
        return $this->addStageHandler(self::DISPATCH, $handler);
    }

    /**
     * @throws UnexpectedValue
     * @throws LogicalException
     */
    #[\Override]
    public function addBeforeHandleHandler(callable $handler): static
    {
        return $this->addStageHandler(self::BEFORE_EXECUTE, $handler);
    }

    /**
     * @throws LogicalException
     * @throws UnexpectedValue
     */
    #[\Override]
    public function addExecuteHandler(callable $handler): static
    {
        return $this->addStageHandler(self::EXECUTE, $handler);
    }

    /**
     * @throws LogicalException
     * @throws UnexpectedValue
     */
    #[\Override]
    public function addResponseHandler(callable $handler): static
    {
        return $this->addStageHandler(self::RESPONSE, $handler);
    }

    /**
     * @throws LogicalException
     * @throws UnexpectedValue
     */
    #[\Override]
    public function addAfterResponseHandler(callable $handler): static
    {
        return $this->addStageHandler(self::AFTER_RESPONSE, $handler);
    }

    /**
     * @throws UnexpectedValue
     * @throws LogicalException
     */
    #[\Override]
    public function addFinallyHandler(callable $handler): static
    {
        return $this->addStageHandler(self::FINALLY, $handler);
    }

    protected function executeHandler(mixed $handler, RequestEnvironmentInterface $requestEnvironment): StagePointer|null
    {
        if (false === \is_callable($handler)) {
            return null;
        }

        try {
            $result                 = $handler($requestEnvironment);

            if ($result instanceof StagePointer) {
                return $result;
            }

        } catch (ParseException|ClientAvailableInterface $exception) {

            if ($exception instanceof ParseException) {
                $exception          = new ClientException('Failed to parse request', [], ['exception' => $exception->toArray()]);
            }

            $requestEnvironment->set(ResultInterface::class, $exception);
            return new StagePointer(finishPlan: true);

        } catch (\Throwable $exception) {
            $requestEnvironment->set(ResultInterface::class, $exception);
            return new StagePointer(finishPlan: true);
        }

        return null;
    }
}
