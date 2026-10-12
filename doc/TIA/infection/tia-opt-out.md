# Deciding whether Infection needs its own TIA opt-out

A developer may want to disable automatic TIA for Infection without changing ordinary
PHPUnit runs.

Infection's `phpUnit` configuration has no dedicated TIA setting. CLI recording and history
opt-outs are available and work. Whether Infection needs a dedicated setting remains open;
there is no Behat scenario for such a setting.

Respecting [explicit XML opt-outs](xml-opt-outs.md) is a separate, reproduced gap.
