<?php

declare(strict_types=1);

namespace IfCastle\Application\RequestEnvironment;

use IfCastle\DesignPatterns\ExecutionPlan\ExecutionPlan;
use IfCastle\DesignPatterns\ExecutionPlan\PlanExecutorWithFinalAndStageControl;
use IfCastle\DesignPatterns\ExecutionPlan\StagePointer;
use IfCastle\DesignPatterns\ExecutionPlan\WeakStaticClosureExecutor;
use IfCastle\Exceptions\LogicalException;
use IfCastle\Exceptions\UnexpectedValue;
use IfCastle\Protocol\Exceptions\BadRequest;
use IfCastle\Protocol\Exceptions\ParseException;
use IfCastle\TypeDefinitions\Result;
use IfCastle\TypeDefinitions\ResultInterface;

class RequestPlan extends ExecutionPlan implements RequestPlanInterface
{
    public function __construct()
    {
        parent::__construct(
            new WeakStaticClosureExecutor(
                /* @phpstan-ignore-next-line */
                static fn(self $self, mixed $handler, string $stage, RequestEnvironmentInterface $requestEnvironment)
                        => $self->executeHandler($handler, $stage, $requestEnvironment), $this
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

    /**
     * Runs one stage handler.
     *
     * An error before the RESPONSE stage becomes the request result, and the plan goes to RESPONSE
     * to render it the way a service error is rendered. An error the RESPONSE stage cannot render
     * (it has no handlers) and an error at RESPONSE or later are rethrown: the plan still runs
     * FINALLY and then throws it to the caller, which owns the response and the log from then on.
     *
     * @throws \Throwable
     */
    protected function executeHandler(
        mixed                       $handler,
        string                      $stage,
        RequestEnvironmentInterface $requestEnvironment
    ): StagePointer|null {
        if (false === \is_callable($handler)) {
            return null;
        }

        try {
            $result                 = $handler($requestEnvironment);
        } catch (\Throwable $exception) {

            if (false === $this->canRenderError($stage)) {
                throw $exception;
            }

            if ($exception instanceof ParseException) {
                $exception          = new BadRequest(detail: $exception->getMessage(), previous: $exception);
            }

            $requestEnvironment->set(ResultInterface::class, new Result(error: $exception));

            return new StagePointer(goToStage: self::RESPONSE);
        }

        return $result instanceof StagePointer ? $result : null;
    }

    private function canRenderError(string $stage): bool
    {
        return false === \in_array($stage, [self::RESPONSE, self::AFTER_RESPONSE, self::FINALLY], true)
               && $this->stages[self::RESPONSE] !== [];
    }
}
