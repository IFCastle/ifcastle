# Executor

Executor is a component responsible for executing commands by the service.

```php
interface ExecutorInterface
{
    /**
     * Executes a command.
     *
     * @param array<string, mixed> $parameters
     */
    public function executeCommand(
        string|CommandDescriptorInterface $service,
        ?string                            $command      = null,
        array                              $parameters   = [],
        ?ExecutionContextInterface         $context      = null
    ): mixed;
}
```

This component performs the following tasks:

1. Controls access to the service
2. Normalizes the provided parameters, performing marshaling if necessary
3. Normalizes the result of the call
4. Performs operation logging/tracing

It depends on the following contracts:

* `ServiceTracerInterface`      - service tracing
* `ContainerInterface`          - dependency container, execution environment
* `AccessCheckerInterface`      - access control component
* `TaskRunnerInterface`         - interface that enables background execution
* `ServiceLocatorInterface`     - service locator

`ServiceLocatorInterface`, in turn, is responsible for identifying a service by its name.

The library offers two ready-made Executors:

* `InternalExecutor` - executes commands for all possible services in the system
* `PublicExecutor` - executes commands only for public services