<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\String\Inflector;

final class EnglishInflector implements InflectorInterface
{
    private const PLURAL_MAP = [['a', 1, \true, \true, ['on', 'um']], ['ea', 2, \true, \true, 'a'], ['secivres', 8, \true, \true, 'service'], ['eci', 3, \false, \true, 'ouse'], ['esee', 4, \false, \true, 'oose'], ['i', 1, \true, \true, 'us'], ['nem', 3, \true, \true, 'man'], ['nerdlihc', 8, \true, \true, 'child'], ['nexo', 4, \false, \false, 'ox'], ['seci', 4, \false, \true, ['ex', 'ix', 'ice']], ['seifles', 7, \true, \true, 'selfie'], ['seibmoz', 7, \true, \true, 'zombie'], ['seivom', 6, \true, \true, 'movie'], ['sesutcep', 8, \true, \true, 'pectus'], ['teef', 4, \true, \true, 'foot'], ['eseeg', 5, \true, \true, 'goose'], ['hteet', 5, \true, \true, 'tooth'], ['swen', 4, \true, \true, 'news'], ['seires', 6, \true, \true, 'series'], ['sei', 3, \false, \true, 'y'], ['sess', 4, \true, \false, 'ss'], ['ses', 3, \true, \true, ['s', 'se', 'sis']], ['sevit', 5, \true, \true, 'tive'], ['sevird', 6, \false, \true, 'drive'], ['sevi', 4, \false, \true, 'ife'], ['sevom', 5, \true, \true, 'move'], ['sev', 3, \true, \true, ['f', 've', 'ff']], ['sexa', 4, \false, \false, ['ax', 'axe', 'axis']], ['sex', 3, \true, \false, 'x'], ['sezz', 4, \true, \false, 'z'], ['suae', 4, \false, \true, 'eau'], ['see', 3, \true, \true, 'ee'], ['segd', 4, \true, \true, 'dge'], ['se', 2, \true, \true, ['', 'e']], ['s', 1, \true, \true, ''], ['xuae', 4, \false, \true, 'eau'], ['elpoep', 6, \true, \true, 'person']];
    private const SINGULAR_MAP = [['airetirc', 8, \false, \false, 'criterion'], ['aluben', 6, \false, \false, 'nebulae'], ['dlihc', 5, \true, \true, 'children'], ['eci', 3, \false, \true, 'ices'], ['ecivres', 7, \true, \true, 'services'], ['efi', 3, \false, \true, 'ives'], ['eifles', 6, \true, \true, 'selfies'], ['eivom', 5, \true, \true, 'movies'], ['esuol', 5, \false, \true, 'lice'], ['esuom', 5, \false, \true, 'mice'], ['esoo', 4, \false, \true, 'eese'], ['es', 2, \true, \true, 'ses'], ['esoog', 5, \true, \true, 'geese'], ['ev', 2, \true, \true, 'ves'], ['evird', 5, \false, \true, 'drives'], ['evit', 4, \true, \true, 'tives'], ['evom', 4, \true, \true, 'moves'], ['ffats', 5, \true, \true, 'staves'], ['ff', 2, \true, \true, 'ffs'], ['f', 1, \true, \true, ['fs', 'ves']], ['hc', 2, \true, \true, 'ches'], ['hs', 2, \true, \true, 'shes'], ['htoot', 5, \true, \true, 'teeth'], ['mu', 2, \true, \true, 'a'], ['nam', 3, \true, \true, 'men'], ['nosrep', 6, \true, \true, ['persons', 'people']], ['noi', 3, \true, \true, 'ions'], ['nop', 3, \true, \true, 'pons'], ['nos', 3, \true, \true, 'sons'], ['no', 2, \true, \true, 'a'], ['ohce', 4, \true, \true, 'echoes'], ['oreh', 4, \true, \true, 'heroes'], ['salta', 5, \true, \true, 'atlases'], ['siri', 4, \true, \true, 'irises'], ['sis', 3, \true, \true, 'ses'], ['ss', 2, \true, \false, 'sses'], ['suballys', 8, \true, \true, 'syllabi'], ['sub', 3, \true, \true, 'buses'], ['suc', 3, \true, \true, 'cuses'], ['sutcep', 6, \true, \true, 'pectuses'], ['su', 2, \true, \true, 'i'], ['swen', 4, \true, \true, 'news'], ['toof', 4, \true, \true, 'feet'], ['uae', 3, \false, \true, ['eaus', 'eaux']], ['xo', 2, \false, \false, 'oxen'], ['xaoh', 4, \true, \false, 'hoaxes'], ['xedni', 5, \false, \true, ['indicies', 'indexes']], ['xo', 2, \false, \true, 'oxes'], ['x', 1, \true, \false, ['cies', 'xes']], ['xi', 2, \false, \true, 'ices'], ['y', 1, \false, \true, 'ies'], ['ziuq', 4, \true, \false, 'quizzes'], ['z', 1, \true, \true, 'zes']];
    private const UNINFLECTED = ['', 'atad', 'reed', 'kcabdeef', 'hsif', 'ofni', 'esoom', 'seires', 'peehs', 'seiceps'];
    public function singularize(string $plural) : array
    {
        $pluralRev = \strrev($plural);
        $lowerPluralRev = \strtolower($pluralRev);
        $pluralLength = \strlen($lowerPluralRev);
        if (\in_array($lowerPluralRev, self::UNINFLECTED, \true)) {
            return [$plural];
        }
        foreach (self::PLURAL_MAP as $map) {
            $suffix = $map[0];
            $suffixLength = $map[1];
            $j = 0;
            while ($suffix[$j] === $lowerPluralRev[$j]) {
                ++$j;
                if ($j === $suffixLength) {
                    if ($j < $pluralLength) {
                        $nextIsVocal = \false !== \strpos('aeiou', $lowerPluralRev[$j]);
                        if (!$map[2] && $nextIsVocal) {
                            break;
                        }
                        if (!$map[3] && !$nextIsVocal) {
                            break;
                        }
                    }
                    $newBase = \substr($plural, 0, $pluralLength - $suffixLength);
                    $newSuffix = $map[4];
                    $firstUpper = \ctype_upper($pluralRev[$j - 1]);
                    if (\is_array($newSuffix)) {
                        $singulars = [];
                        foreach ($newSuffix as $newSuffixEntry) {
                            $singulars[] = $newBase . ($firstUpper ? \ucfirst($newSuffixEntry) : $newSuffixEntry);
                        }
                        return $singulars;
                    }
                    return [$newBase . ($firstUpper ? \ucfirst($newSuffix) : $newSuffix)];
                }
                if ($j === $pluralLength) {
                    break;
                }
            }
        }
        return [$plural];
    }
    public function pluralize(string $singular) : array
    {
        $singularRev = \strrev($singular);
        $lowerSingularRev = \strtolower($singularRev);
        $singularLength = \strlen($lowerSingularRev);
        if (\in_array($lowerSingularRev, self::UNINFLECTED, \true)) {
            return [$singular];
        }
        foreach (self::SINGULAR_MAP as $map) {
            $suffix = $map[0];
            $suffixLength = $map[1];
            $j = 0;
            while ($suffix[$j] === $lowerSingularRev[$j]) {
                ++$j;
                if ($j === $suffixLength) {
                    if ($j < $singularLength) {
                        $nextIsVocal = \false !== \strpos('aeiou', $lowerSingularRev[$j]);
                        if (!$map[2] && $nextIsVocal) {
                            break;
                        }
                        if (!$map[3] && !$nextIsVocal) {
                            break;
                        }
                    }
                    $newBase = \substr($singular, 0, $singularLength - $suffixLength);
                    $newSuffix = $map[4];
                    $firstUpper = \ctype_upper($singularRev[$j - 1]);
                    if (\is_array($newSuffix)) {
                        $plurals = [];
                        foreach ($newSuffix as $newSuffixEntry) {
                            $plurals[] = $newBase . ($firstUpper ? \ucfirst($newSuffixEntry) : $newSuffixEntry);
                        }
                        return $plurals;
                    }
                    return [$newBase . ($firstUpper ? \ucfirst($newSuffix) : $newSuffix)];
                }
                if ($j === $singularLength) {
                    break;
                }
            }
        }
        return [$singular . 's'];
    }
}
