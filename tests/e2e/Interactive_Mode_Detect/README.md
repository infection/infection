# Better detect non-interactive mode

Non-interactive mode detection [methods used by Symfony's Application](https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Console/Application.php#L870) are not exhaustive, which leads to a possibility for Infection to stuck in a state where it waits for user input indefinitely. All while `sebastian/environment` offers [an additional detection method ](https://github.com/sebastianbergmann/environment/blob/master/src/Console.php#L93-L95) which can see thought when running Infection with [chronic, from moreutils](https://joeyh.name/code/moreutils/).


