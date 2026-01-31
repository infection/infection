# TeamCity Logger

Outputs mutation results as [TeamCity service messages][tc-service-messages] for integration
with JetBrains IDEs and TeamCity CI. Used by the [Infection plugin][infection-plugin].


## Service Message Format

TeamCity service messages follow this format:

```
##teamcity[messageName key='value']
```

The logger maps mutation results to test reporting messages.


## Implementation Notes

The official [TeamCity service messages documentation][tc-service-messages] describes the
on-premise service format, which differs from how the JetBrains SDK interprets these messages
in IDE plugins.

The key difference: use `nodeId`/`parentNodeId` instead of `flowId` for proper hierarchy
rendering in IDE plugins.

**Official documentation example (flow-based):**

```
# TEST SUITE A
##teamcity[testSuiteStarted name='Test Suite A']

	# TEST_1_A
	##teamcity[testStarted name='Test 1.A']
	##teamcity[flowStarted flowId='mainFlow-1a']

		# Nested TEST_1_A_1
		##teamcity[testStarted name='Test 1.A, Subtest 1']
		##teamcity[flowStarted flowId='subFlow1-1a' parent='mainFlow-1a']
      	    # Testing
		##teamcity[flowFinished flowId='subFlow1-1a']
		##teamcity[testFinished name='Test 1.A, Subtest 1' duration='1000']

		# Nested TEST_1_A_2
		##teamcity[testStarted name='Test 1.A, Subtest 2']
    	##teamcity[flowStarted flowId='subFlow2-1a' parent='mainFlow-1a']
    	    # Testing
		##teamcity[flowFinished flowId='subFlow2-1a']
    	##teamcity[testFinished name='Test 1.A, Subtest 2' duration='1000']

	##teamcity[flowFinished flowId='mainFlow-1a']
	##teamcity[testFinished name='Test 1.A' duration='3000']

##teamcity[testSuiteFinished name='Test Suite A']
```

**Infection implementation (node-based):**

```
# TEST SUITE A
##teamcity[testSuiteStarted name='Test Suite A' nodeId='TSA']

	# TEST_1_A
	##teamcity[testStarted name='Test 1.A' nodeId='T1A' parentNodeId='TSA']

		# Nested TEST_1_A_1
		##teamcity[testStarted name='Test 1.A, Subtest 1' nodeId='T1A1' parentNodeId='T1A']
      	    # Testing
		##teamcity[testFinished name='Test 1.A, Subtest 1' duration='1000' nodeId='T1A1' parentNodeId='T1A']

		# Nested TEST_1_A_2
		##teamcity[testStarted name='Test 1.A, Subtest 2' nodeId='T1A2' parentNodeId='T1A']
    	    # Testing
    	##teamcity[testFinished name='Test 1.A, Subtest 2' nodeId='T1A2' parentNodeId='T1A' duration='1000']

	##teamcity[testFinished name='Test 1.A' nodeId='T1A' parentNodeId='TSA' duration='3000']

##teamcity[testSuiteFinished name='Test Suite A' nodeId='TSA']
```


## Terminology Mapping

| TeamCity     | Infection                            |
|--------------|--------------------------------------|
| Test Suite   | Source file                          |
| Test         | Mutation                             |
| Test passed  | Mutation covered                     |
| Test failed  | Mutation escaped                     |
| Test ignored | Mutation ignored/skipped/not covered |

| TeamCity      | Infection                           |
|---------------|-------------------------------------|
| Test Suite ID | Source file canonical absolute path |
| Test ID       | mutation ID = Mutant hash           |


[infection-plugin]: https://github.com/j-plugins/infection-plugin
[tc-service-messages]: https://www.jetbrains.com/help/teamcity/service-messages.html
