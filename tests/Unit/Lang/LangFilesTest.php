<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\App;

class LangFilesTest extends BaseTranslationTest
{
    public function testAllLangFilesWellFormatted()
    {
        foreach ($this->locales as $locale) {
            foreach ($this->getAllLangFilenames() as $langFile) {
                $this->assertInternalType('array', Lang::get($langFile));
            }
        }
    }

    /**
    * Tests for lang entries that are empty strings.
    * If tests are run with --verbose, displays wich keys have empty values.
    *
    * @return void
    */
    public function testNoEmptyStrings()
    {
        $emptyEntries = [];
        foreach ($this->getAllLangPaths() as $path) {
            foreach ($this->locales as $locale) {
                App::setLocale($locale);
                $value = Lang::get($path);
                if ($value === "") {
                    $fullPath = $locale . "/" . $path;
                    array_push($emptyEntries, $fullPath);
                }
            }
        }
        if (!empty($emptyEntries)) {
            print_r("\n");
            print_r("The following lang entries are empty strings:\n");
            print_r($emptyEntries);
            print_r("\n");
        }
        $this->assertEmpty($emptyEntries);
    }

    /**
    * Tests for lang entries that are set to values that obviously indicate
    * a missing translation, like 'TRANSLATION NEEDED'.
    * If tests are run with --verbose, displays wich keys have these values.
    *
    * @return void
    */
    public function testNoTranslationNeeded()
    {
        $checks = ['TRANSLATION NEEDED', 'TRANSLATION', 'TRANSLATION_NEEDED'];
        $translationNeeded = [];
        foreach ($this->getAllLangPaths() as $path) {
            foreach ($this->locales as $locale) {
                App::setLocale($locale);
                $value = Lang::get($path);
                if (in_array($value, $checks)) {
                    $fullPath = $locale . "/" . $path;
                    array_push($translationNeeded, $fullPath);
                }
            }
        }
        if (!empty($translationNeeded)) {
            print_r("\n");
            print_r("Translation needed for the following keys:\n");
            print_r($translationNeeded);
            print_r("\n");
        }
        $this->assertEmpty($translationNeeded);
    }

    /**
    * Contains lang keys that are expected to be missing in a particular language
    *
    * @var array
    */
    protected $permittedMissing = [
        'en' => [
            'validation.attributes.name'
        ],
        'fr' => []
    ];

    /**
    * Tests for lang entries that are an empty array instead of a string,
    * or that are present in one language but not another. Ignores keys
    * in $this->permittedMissing.
    *
    * If tests are run with --verbose, displays wich keys are missing.
    *
    * @return void
    */
    public function testNoMissingStrings()
    {
        $missingEntries = [];
        foreach ($this->locales as $locale) {
            $missingEntries[$locale] = [];
        }
        foreach ($this->getAllLangPaths() as $path) {
            foreach ($this->locales as $locale) {
                App::setLocale($locale);
                if (!Lang::has($path) && !in_array($path, $this->permittedMissing[$locale])) {
                    array_push($missingEntries[$locale], $path);
                }
            }
        }
        $allMissingEntries = [];
        foreach ($this->locales as $locale) {
            if (!empty($missingEntries[$locale])) {
                print_r("\n");
                print_r("The following lang entries are missing in $locale\n");
                print_r($missingEntries[$locale]);
                print_r("\n");
            }
            $allMissingEntries = array_merge($allMissingEntries, $missingEntries[$locale]);
        }

        $this->assertEmpty($allMissingEntries);
    }

    /**
    * The list of keys that are expected to have identical values in multiple languages.
    * If tests are run with --verbose, displays wich keys have identical values.
    *
    * @var array
    */
    protected $permittedEqual = [
        'applicant/applicant_profile.about_section.gc_link'
    ];
    /**
    * Tests lang files for identical values in multiple languages.
    *
    * If tests are run with --verbose, displays wich keys have identical values.
    *
    * @return void
    */
    public function testAllLangValuesDifferentInFrenchAndEnglish()
    {
        $identicalEntries = [];
        foreach ($this->getAllLangPaths() as $path) {
            $prevValues = [];
            foreach ($this->locales as $locale) {
                App::setLocale($locale);
                $value = Lang::get($path);
                if (strpos($path, '.id')) {
                    // exclude from results
                } elseif (strpos($path, '.type')) {
                    // exclude from results
                } elseif (in_array($value, $this->permittedEqual)) {
                    // exclude from results
                } elseif (in_array($path, $this->permittedEqual)) {
                    // exclude from results
                } elseif (Lang::has($path) && in_array($value, $prevValues)) {
                    array_push($identicalEntries, $path);
                }
                array_push($prevValues, $value);
            }
        }
        $identicalEntries = array_unique($identicalEntries);
        if (!empty($identicalEntries)) {
            print_r("\n");
            print_r("The following lang entries are identical in multiple languages:\n");
            print_r($identicalEntries);
            print_r("\n");
        }
        $this->assertEmpty($identicalEntries);
    }
}