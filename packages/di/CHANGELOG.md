# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.8.0] - 2024-11-23

### Added
* [Zero configuration](./README.md/#zero-configuration-principle).
  (*Ability to inject dependencies without modifying the code in the dependent class*).
* [Support for the concept of `environment`/`scope`](./README.md/#environment-and-scope-concepts) for dependency lookup.
* [Support parent/child containers](./README.md/#container-inheritance).
* [Constructor injection](./README.md/#initialization-through-a-constructor) of dependencies
* [Injection of dependencies into properties](./README.md/#initialization-through-a-method)
* Injecting configuration values as a Dependency
* [Lazy loading](./README.md/#lazy-loading) of dependencies
* [Auto dereferencing a `WeakReference` inside the container](./README.md/#dereferencing-a-weakreference)
* [Handling circular dependencies](./README.md/#circular-dependencies)
* [Support php-attributes for describing dependencies](./README.md/#special-attributes)
* [Custom dependency providers](./README.md/#custom-attributes-and-providers)
* [Custom descriptor providers](./README.md/#descriptor-provider)
