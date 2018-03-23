This PR:

- [x] Does not impose any limits on the Infection itself
- [x] Adds a mandatory memory limit for mutation sub-processes
- [x] Adds an option in the config file
- [ ] Adds a question during initial configuration
- [ ] Adds a command-line option `--memory-limit=`
- [ ] Covered by tests (?)
- [ ] Doc PR: ?

Fixes #247

### Rationale

Infection is a command line program. Command line programs are expected to behave. Because they are expected to behave, there are usually no particular limits in terms of memory consumption. Command line programs are responsible for the commands they launch. So must be Infection. 

Currently, there are no limits on how much memory a mutation process may consume other than by time. Specifically, there is no memory limit set in Debian and Ubuntu for PHP CLI. Since the main function of Infection is to cause unexpected bugs in all kind of software it runs, including worst samples, there must be at least some limits in place to make sure new bugs do not cause more damage than they should.

There's a default time limit of 20 seconds. Current programs have no problem consuming many gigabytes of RAM in the allotted time limit. Therefore, it is not enough to have a time limit only.

#### What's wrong with excessive memory consumption?

Since a faulty mutant will usually consume actual memory, it may come to a point where will be no memory left not only for other programs but for the system. In case of Linux that will force [OOM Killer](https://linux-mm.org/OOM_Killer) to kill some other process basically at random. It won't kill anything important, but typical user process, including an IDE and browsers, are at risk. Other OS have comparable measures.

Needless to say that a process killed by OOM Killer will not have a chance to save anything to disc. Therefore, a user will be surprised with a sudden loss of data, unfinished work, etc.

### Pros and cons

What does this change bring to the table?

- The program will be more reliable, more predictable and safer to use.
- The program will run faster. Infection from the master branch takes 1:43 to inspect itself, where Infection from this PR spends only 53 seconds on the same task. That's twice as fast!
- Increased usability, and, consequently, better adoption rates.

Who is going to benefit from this change?

- All new users, who are used to 128Mb limit, common to most web apps. They would not have to read any warnings, and everything will work out of the box not trying to smash their OS.
- The general populace of software engineers caring about work being done, bugs being fixed, and so on, without a need to fidget with their configs, adjusting them especially for her majesty Infection.
- All users caring about speed. They wouldn't have to wait until their tests hit physical limits of their tech because a test will err as soon as it requests more than a configured amount of RAM.


Who will be at a disadvantage?

- Those infrequent software engineers who happen to need more than 128Mb of RAM for their tests to pass. They would need to make a different selection during initial setup, just once.
- Some contributors to this project who want to reject this proposal outright in anguish. Sorry, folks, this isn't about personal likes or dislikes, it's about logically sound arguments towards to a greater success of this project.

### Methodology

The memory limit is enforced for all mutation sub-processes, be it a PHPUnit test runner, or PhpSpec's. Memory limit is introduced by altering a known temporary `php.ini` to include a directive as the very last line to enable the limit at the right time.

- Memory limit can be selected during initial configuration and with a command line option.
- The memory limit defaults to 128Mb, which is an accepted default for most web apps. 
- If there is a limit configured in `phpunit.xml`, it will be suggested as the default. If there's a limit configured in `php.ini`,  it will also be suggested as the default but with lesser precedence.
- Memory limit can be altered later on from the configuration.
- Memory limit can be changed on the command line for a one-time run.
- If there is a memory limit already defined in `phpunit.xml`, it will be overridden with the configured default. If we tell a user that there is limit they can configure, we must oblige and follow our promise. All lines with `<ini name="memory_limit" value="*"/>` will be removed.

### What it does not do?

This PR only adds a memory limit for PHP subprocesses. If a mutation happens to trigger a program to run ImageMagic command to create the greatest image of all times, there will be consequences this PR cannot guard against.

### Alternatives

>:warning: Warning: you are running Infection without a memory limit (`memory_limit=-1`). This can cause various issues if your machine runs out of memory. 

What about alternatives? All alternatives presume that there will be the above warning added that will...

- Ask a user to set a memory limit in the global `php.ini`. This is unacceptable because nobody should be forced to change `php.ini` if a program can find a way to work without a change.

- Suggest a user set a memory limit on the command line with `php -dmemory_limit=128M infection.phar`. This is unsatisfactory because in this case the memory limit will be applied not only to mutations process but to mutation itself. Mutation is a well-written CLI command, it does not require any memory limits by itself.

- Enforce memory limits with `ulimit`, `softlimit`, and similar tools. This is undesirable because it will require a constant care from a user.

- Another option is to add 'badness' points to the mutations processes by writing to `/proc/<pid>/oomadj`. This is Linux-specific and will not help users on other platforms. This also won't make Infection faster or more reliable because it won't have a choice on killed processes.

- Yet another option is to monitor a derivative of a mutation process memory usage growth. This will be very OS specific and bear other drawbacks compared to imposing a straight-on memory limit. 

All alternatives are worse than this proposal because...
 
- They try to impose limits on the Infection itself. There's no evidence this is necessary. 
- They fail to deliver a better usability and bear no promise of enhanced adoption rates.
- They require more effort from the user, and require more care, on the contrary with this proposal.
- They make Infection more difficult to operate than it is now.

### What next?

Since there was a heated discussion on how to proceed with a similar proposal, I will not move forward with more work on this PR beyond current PoC unless there is a consensus. I see no point in spending hours and hours only to have your work thrown into a bin. Please tell me if there's something in this proposal, or if this proposal is not worthy and got to be rejected.
