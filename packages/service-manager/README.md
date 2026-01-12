# Service manager [![PHP Composer](https://github.com/EdmondDantes/type-definitions/actions/workflows/php.yml/badge.svg)](https://github.com/EdmondDantes/service-manager/actions/workflows/php.yml)

Library for organizing the `service layer`.

This architecture is designed for **stateful** applications 
that distribute request execution across different workers.

The library provides a ready-made service layer architecture 
but does not contain a 100% implementation of the contracts.

The following contracts are external and should be implemented in other components:

**Service repository**:

* RepositoryReaderInterface
* ServiceCollectionInterface
* ServiceCollectionWriterInterface

see https://github.com/EdmondDantes/configurator-ini for implementation example.
and https://github.com/EdmondDantes/configuator-toml for TOML syntax.

**Access control**:

* AccessCheckerInterface

**Command bus**:

* CommandDescriptorInterface
* ExecutionContextInterface

**Interceptors**:

* ParameterResolverInterface

**Tracing**:

* ServiceTracerInterface

**Task runner and Workers**:

* TaskRunnerInterface
* WorkerExecutorInterface

See [Architecture](docs/01-architecture.md) for more information.
