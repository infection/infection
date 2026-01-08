# Container Architecture

This document explains Infection's dependency injection container and its auto-wiring strategy.

## Overview

The `Container` class is a simple DI container that uses factory closures to create services. It supports:

- Explicit factory registration for services
- Auto-wiring for services without registered factories
- Service caching (singleton pattern)

## Auto-wiring: The Nuance

The container can auto-wire services by analyzing constructor signatures and resolving dependencies by **type**. However, auto-wiring by type does not guarantee correct **intent**.

### The Problem

Consider a service that depends on `EventDispatcherInterface`. Auto-wiring will:

1. Look for a registered factory for `EventDispatcherInterface`
2. If not found, try to instantiate `EventDispatcherInterface` directly (fails - it's an interface)
3. Or pick whatever implementation happens to be registered

This can lead to subtle bugs where:

- The wrong implementation is injected
- A shared instance is used where a fresh one was needed
- Configuration-specific instances are replaced with defaults

### Real Examples from This Codebase

| Service | Why It Cannot Be Auto-wired |
|---------|---------------------------|
| `StaticAnalysisConfigLocator` | Takes a `string` path argument |
| `InitialStaticAnalysisRunConsoleLoggerSubscriberFactory` | Takes `bool` arguments |
| `SubscriberRegisterer` | Depends on `EventDispatcher` interface |
| `InitialTestsRunner` | Depends on `EventDispatcher` interface |
| `ConfigurationFactory` | Depends on `CiDetectorInterface` |

## The Allowlist Model

Rather than auto-wire everything and exclude problematic services, we use an **allowlist** of services proven safe to auto-wire.

### Allowlist Criteria

A service may be added to the allowlist if **all** conditions are met:

1. **Concrete dependencies only** - No interface dependencies
2. **No primitive arguments** - No strings, bools, ints, or arrays in constructor
3. **Stateless or context-independent** - Auto-wired instance behaves correctly in all contexts
4. **Integration-tested** - Proven to work via `test_service_can_be_auto_wired`

### Current Allowlist

These services are explicitly allowed to be auto-wired (no factory needed):

- `StaticAnalysisToolFactory`
- `MutantCodeFactory`
- `MutantCodePrinter`
- `FileMutationGenerator`
- `SourceCollectorFactory`

See `ContainerTest::provideAutoWireableServices()` for the authoritative list.

## Test Enforcement

Two tests enforce this model:

### `test_service_can_be_auto_wired`

Verifies that allowlisted services **work** without factories. If this test fails, either:

- The service gained a new dependency that breaks auto-wiring
- The service should be removed from the allowlist

### `test_factory_is_essential`

Verifies that each factory is **necessary**. When a factory is removed:

- If the service still works and doesn't break others, the factory was redundant
- Factories for allowlisted services should be rejected (prevents boilerplate creep)

## Decision Tree: Adding a New Service

```
Does the service have interface dependencies?
  YES -> Add a factory (cannot auto-wire interfaces)
  NO  -> Continue

Does the service have primitive constructor arguments?
  YES -> Add a factory (cannot auto-wire primitives)
  NO  -> Continue

Does the service need a specific instance/configuration?
  YES -> Add a factory (auto-wiring might inject wrong instance)
  NO  -> Continue

Is it proven safe via integration tests?
  YES -> Add to allowlist, no factory needed
  NO  -> Add a factory until proven safe
```

## Builder Pattern

For complex service construction, use the Builder pattern. See `IndexXmlCoverageParserBuilder` for an example. Builders:

- Encapsulate complex instantiation logic
- Are testable in isolation
- Reduce Container complexity
- Can be extracted via `/extract-container-builder` skill

## Historical Context

This architecture evolved from discovering that "auto-wireable" and "correctly auto-wireable" are different:

1. Initial goal: Remove redundant factories to reduce boilerplate
2. Discovery: Some services auto-wire but with wrong dependencies
3. Solution: Allowlist model with strict criteria and test enforcement

The allowlist is intentionally conservative. When in doubt, add a factory.
