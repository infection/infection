#Code coverage on function parameters

* Github ticket: https://github.com/infection/infection/issues/101

##Summary
Values in the function signature are not detected as covered.

##Full ticket
| Question    | Answer
| ------------| ---------------
| Infection version | 0.7.0
| Test Framework version | PHPUnit/ 6.5.-
| PHP version | 7.1.12
| Platform    | MacOS
| Github Repo | https://github.com/BackEndTea/Array-Meta


<details>
 <summary>Output with issue</summary>
 Given the following code

```php
    public function search($value, bool $strict = false)
    {
        if (($return =  \array_search($value, $this->items, $strict)) === false) {
            throw ValueNotFoundException::valueNotFound($value);
        }
        return $return;
    }

```

And the following test

```php
    public function testSearchDefaultsToNonStrictSearch()
    {
        $array = ['0', '1', '2', '3'];
        $meta = new ArrayMeta($array);
        $key = $meta->search(3);
        $this->assertSame(3, $key);
    }
```

I expected the mutation of $strict to true to be covered. 

However it tells me the mutation was not covered, as the code coverage doesn't look at the function signature, but only to the body of the function. So mutating default values always says it was not covered

</details>