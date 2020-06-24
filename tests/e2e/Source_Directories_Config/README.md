# Title

* https://github.com/infection/infection/issues/1264

## Summary

BC break between 0.15 and 0.16. `source.directories` setting inside `infection.json` is not respected. Infection mutates
files from all the folders located in Coverage Report.
