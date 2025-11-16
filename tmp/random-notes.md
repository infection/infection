TestLocation initially looks like this:

TestLocations
    byLine
        47: [TestLocation]
        51: [TestLocation]
        56: [TestLocation]
    byMethod
        __construct: SourceMethodLineRange(43, 47)
        getStartLine: SourceMethodLineRange(49, 52)
        getEndLine: SourceMethodLineRange(54, 57)

TestLocation
    - method: 'Infection\Tests\Differ\ChangedLinesRangeTest::test_it_returns_lines'
    - filePath: null
    - executionTime: null

-> TestLocation is more a "covered by" at this point, this is because with the XML coverage we need
   to aggregate information from multiple sources of data.

-> the naming of "byMethod" seems odd

-> executionTime: we take the test case for some reasons???




<file name="FilesDiffChangedLines.php" path="/Differ">
<class name="Infection\Differ\FilesDiffChangedLines" start="44" executable="8" executed="8" crap="5">
  <namespace name="Infection\Differ"/>
  <method name="__construct" signature="__construct(Infection\Differ\DiffChangedLinesParser $diffChangedLinesParser, Infection\Logger\GitHub\GitDiffFileProvider $diffFileProvider)" start="49" end="51" crap="1" executable="1" executed="1" coverage="100"/>
  <method name="contains" signature="contains(string $fileRealPath, int $mutationStartLine, int $mutationEndLine, ?string $gitDiffBase): bool" start="53" end="67" crap="4" executable="7" executed="7" coverage="100"/>
</class>
<line nr="62">
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;line in one-line range&quot;"/>
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;line in multi-line range in the beginning&quot;"/>
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;line in multi-line range in the middle&quot;"/>
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;line in multi-line range in the end&quot;"/>
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;line in the second range&quot;"/>
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;mutation range in one-line range, around&quot;"/>
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;mutation range in one-line range, before&quot;"/>
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;mutation range in one-line range, after&quot;"/>
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;mutation range in one-line range, inside&quot;"/>
  </line>
  <line nr="66">
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_memoizes_parsed_results"/>
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;not found line in one-line range before&quot;"/>
    <covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;not found line in one-line range after&quot;"/>
  </line>

Mutation affects `DiffChangedLinesParser::parse()`
-> execute all tests covering `DiffChangedLinesParser::parse()`
-> all tests covering `L[53;67]`
-> from the covered data

<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;line in one-line range&quot;"/>
<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;line in multi-line range in the beginning&quot;"/>
<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;line in multi-line range in the middle&quot;"/>
<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;line in multi-line range in the end&quot;"/>
<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;line in the second range&quot;"/>
<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;mutation range in one-line range, around&quot;"/>
<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;mutation range in one-line range, before&quot;"/>
<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;mutation range in one-line range, after&quot;"/>
<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;mutation range in one-line range, inside&quot;"/>
<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_memoizes_parsed_results"/>
<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;not found line in one-line range before&quot;"/>
<covered by="Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff with data set &quot;not found line in one-line range after&quot;"/>

Now there is two issues to distinguish here:

- PHPUnit write each test executed, but in our case we would execute the test method with the entire data provider, not an item of the data provider
=> For an infection PHPUnit report: this could be a big win to save space and make the coverage easier to consume

Transformed/interpreted the above would be

- `Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_finds_line_in_changed_lines_from_diff`
- `Infection\Tests\Differ\FilesDiffChangedLinesTest::test_it_memoizes_parsed_results`
