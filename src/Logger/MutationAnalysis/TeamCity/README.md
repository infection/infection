# TeamCity Logger

Outputs mutation results using [TeamCity service messages][tc-service-messages], enabling
integration with JetBrains IDEs and TeamCity CI.

This is used for the [Infection plugin][infection-plugin].

## How It Works

TeamCity service messages are specially formatted text:

```
##teamcity[messageName key='value']
```

The logger uses test reporting messages to represent mutation results.

We encountered several issues during the integration; it appears
the [TeamCity service messages][tc-service-messages] are for their on-promise service and do not
reflect how the TeamCity logs are interpreted by the JetBrains SDK in the plugin (at the time of
writing).

A key difference is regarding the `flowId` usages. The document gives the following examples:

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

Instead, what it should be in our case is:

TODO: to check if parentNode=0 is necessary
TODO: to check if the node/parentNode is necessary on the *Finished message
TODO: check if that double nesting works fine

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

[infection-plugin]: https://github.com/j-plugins/infection-plugin

[tc-service-messages]: https://www.jetbrains.com/help/teamcity/service-messages.html
