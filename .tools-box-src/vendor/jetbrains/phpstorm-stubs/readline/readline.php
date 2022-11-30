<?php












function readline(?string $prompt): string|false {}



















function readline_info(?string $var_name, $value): mixed {}









function readline_add_history(string $prompt): bool {}






function readline_clear_history(): bool {}







function readline_list_history(): array {}









function readline_read_history(?string $filename): bool {}









function readline_write_history(?string $filename): bool {}










function readline_completion_function(callable $callback): bool {}













function readline_callback_handler_install(string $prompt, callable $callback): bool {}






function readline_callback_read_char(): void {}







function readline_callback_handler_remove(): bool {}






function readline_redisplay(): void {}






function readline_on_new_line(): void {}

define('READLINE_LIB', "readline");


