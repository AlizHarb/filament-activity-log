<?php

declare(strict_types=1);

/**
 * @param  array<string, mixed>  $translations
 * @return array<string, string>
 */
function flattenActivityTranslations(array $translations, string $prefix = ''): array
{
    $flattened = [];

    foreach ($translations as $key => $value) {
        $path = $prefix === '' ? $key : "{$prefix}.{$key}";

        if (is_array($value)) {
            $flattened += flattenActivityTranslations($value, $path);

            continue;
        }

        $flattened[$path] = (string) $value;
    }

    return $flattened;
}

/**
 * @return list<string>
 */
function activityTranslationPlaceholders(string $value): array
{
    preg_match_all('/(?<!:):([A-Za-z_][A-Za-z0-9_]*)/', $value, $matches);

    $placeholders = array_values(array_unique($matches[1]));
    sort($placeholders);

    return $placeholders;
}

it('keeps every supported locale complete and placeholder compatible', function () {
    $languagePath = dirname(__DIR__, 2).'/resources/lang';
    $english = flattenActivityTranslations(require "{$languagePath}/en/activity.php");
    $localeFiles = glob("{$languagePath}/*/activity.php");

    expect($localeFiles)->not->toBeFalse();

    foreach ($localeFiles as $localeFile) {
        $locale = basename(dirname($localeFile));
        $translations = flattenActivityTranslations(require $localeFile);
        $englishKeys = array_keys($english);
        $translationKeys = array_keys($translations);
        sort($englishKeys);
        sort($translationKeys);

        expect($translationKeys, "Translation keys differ for [{$locale}].")
            ->toBe($englishKeys);

        foreach ($english as $key => $value) {
            expect(trim($translations[$key]), "Translation [{$locale}.{$key}] is empty.")
                ->not->toBe('')
                ->and(
                    activityTranslationPlaceholders($translations[$key]),
                    "Translation placeholders differ for [{$locale}.{$key}].",
                )
                ->toBe(activityTranslationPlaceholders($value));
        }
    }
});
