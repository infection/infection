- Currently, the source collection is happening in `MutationGenerator`, this should be moved out.
- The `SourceCollection` events can be extracted.
- The `Reporter` events can be extracted.
- `Events\Ast` should be moved to the appropriate phase; probably the case of more events.
- to rename "for file" to "for source file"
- to rename "MutationTesting" events to "Mutation Evaluation"
- to be more conscious about cleaning up unnecessary state (e.g. unsetting tracked spans if they no longer need to be tracked)

###

events naming convention choice & problems

"mutation generation":

- applies to a general phase in which we will generate all the mutations
- also applies to a specific source file in which we will generate all the mutations for that source file

How to distinguish the two?

#### Proposal: "phase" label

- `MutationGenerationPhaseWasStarted` (global)
- `MutationGenerationWasStarted` (item)

I don't like it, "phase" can be (English speaking) applied to either:

- mutation generation phase
- mutation generation phase for the source file X

### Proposal: "item" prefix

Does not end up well due to repetition, e.g. for "MutationEvalation":

- `MutationEvaluation` (global)
- `MutationMutationEvaluation` (item) -> sounds awful


### Proposal: using `For` pattern

- `MutationGenerationForSourceFile`
- `MutationEvaluationForMutation`
