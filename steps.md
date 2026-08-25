1. Introduce the TestFramework contract.
2. Make the Static Analysis into a TestFramework contract.
3. PRIORITY (parallel) Move the memory limit to the test frameworks (beware, not trivial, see src/TestFramework/PhpUnit/Adapter/MEMORY_LIMITER.md).
4. Introduce the new TestFramework Factory (for test framework adapters only).
5. Migrate the PHPUnitAdapter from TestFrameworkAdapter to TestFramework.
6. Update the TestFrameworkFactory with new arguments.
7. Remove the special case of PHPUnit in the Factory.
8. Move StaticAnalysisToolFactory to Factory.
9. Remove unnecessary parts of TestFrameworkAdapter in the PHPUnitAdapter
10. Remove StaticAnalysisToolAdapter.
11. Migrate InitialTestsRunProcessFactory & InitialStaticAnalysisProcessFactory
12. Migrate InitialTestsFailed & InitialStaticAnalysisRunFailed
13. PRIORITY (parallel) Introduce a CombinedTestFramework to rework MutantProcessContainer.
14. Introduce a proper CombinedTestFramework (but still missing a proper wiring).
15. (parallel) Solve the dependency of the initial tests & SA logger on TestFramework
16. Fixed the wiring of CombinedTestFramework.
17. Remove SA logger
18. Remove StaticAnalysisTestFramework
19. Move the migrated PHPStan & Mago adapters under Test/Framework
20. Introduce a Mutant contract.
21. Fix MutantProcess; various stuff to break down here:
    - Move the resolution of the detection status to the test framework
    - microtime -> Clock
22. Remove dependency on Configuration.
23.
