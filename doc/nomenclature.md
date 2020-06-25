# Nomenclature

## Table of Contents

- [A](#a)
    - [AST][ast]
- [M](#m)
    - [Mutagenesis][mutagenesis]
    - [Mutant][mutant]
    - [Mutation][mutation]
    - [Mutator][mutator]
- [S](#s)
    - [Subject][subject]
- [T](#t)
    - [Tracer][tracer]
    - [Trace][trace]


## A

### AST

Acronym for [Abstract Syntax Tree][ast-definition]. It is a tree representation of the abstract
syntactic structure of code. It is what Infection parses the code into in order to operate on it.


## M

### Mutagenesis

Process of creating a mutant from the original program.


### Mutant

New program that differs from the original by applying a mutation.


### Mutation

The result of applying a mutator to the AST of a subject and represents a change to be applied.


### Mutator

Define a possible transformation, which applied to the AST of a subject will result in a mutation.

In the Mutation Testing literature, mutators are also known as "mutant operator",
"mutagenic operator", "mutagen" and "mutation rule".


## S

### Subject

An addressable piece of code to be targeted for mutation testing.


## T

### Tracer

A tool responsible for creating a bound, a [_trace_][trace], between a piece of source code and the
test source which executes it. The tracer can work one way, i.e. find the corresponding test(s) for
a given piece of source code. It may also work the other way around or both ways.


### Trace

Artifact produced by a tracer: provides the piece of source code and its associated tests.


<hr />

[ast]: #ast
[ast-definition]: https://en.wikipedia.org/wiki/Abstract_syntax_tree
[mutagenesis]: #mutagenesis
[mutant]: #mutant
[mutation]: #mutation
[mutator]: #mutator
[subject]: #subject
[tracer]: #tracer
[trace]: #trace
