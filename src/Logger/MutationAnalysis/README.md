A note about this refactoring.

I think `Infection\Console\OutputFormatter\OutputFormatter` is an ill-named
service. Indeed:

- It does not format messages: it prints a specific message for an action, it is a logger.
- This service has nothing to do with the console. Some of its implementations do yes,
  but those are implementation details, not the contract.

Changes done:

- Rename `Infection\Console\OutputFormatter\OutputFormatter` to `Infection\Logger\MutationAnalysis\MutationAnalysis\Logger`.
- Move the implementations here.
- Rename the implementations to better reflect their purpose.
- Actually, we want more than just the evaluation, we want to log the whole analysis step... Hence.
- Add more methods to it...
  - 


Notes:
 
- `AbstractMutationEvaluationLogger::UNKNOWN_COUNT` seems suspicious.

- TODO: update the nomenclature!
