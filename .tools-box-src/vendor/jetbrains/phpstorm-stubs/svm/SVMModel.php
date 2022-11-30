<?php






class SVMModel
{







public function checkProbabilityModel(): bool {}









public function __construct(string $filename = '') {}








public function getLabels(): array {}








public function getNrClass(): int {}








public function getSvmType(): int {}








public function getSvrProbability(): float {}








public function load(string $filename): bool {}










public function predict_probability(array $data): float {}










public function predict(array $data): float {}








public function save(string $filename): bool {}
}
