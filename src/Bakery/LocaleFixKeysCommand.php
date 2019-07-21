<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Support\Repository\Repository;

/**
 * locale:fix-keys command.
 * Fix missing keys in locale translation files.
 *
 * @author Amos Folz
 */
class LocaleFixKeysCommand extends LocaleMissingKeysCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('locale:fix-keys')
             ->setHelp("This command generates missing keys for locale translation files. E.g. running 'locale:fix-keys -b en_US -f es_ES' will compare all es_ES and en_US locale files and populate es_ES with any missing keys from en_US.")
             ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'The base locale used to generate values for any keys that are fixed. ', 'en_US')
             ->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'One or more specific locales to fix. E.g. "fr_FR,es_ES" ', null)
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Do not display confirmation.')
             ->setDescription('Fix locale missing files and key values');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Fixing Locale Keys');

        // The "base" locale to compare other locales against. Defaults to en_US if not set.
        $baseLocale = $input->getOption('base');
        $baseLocaleFileNames = $this->getFilenames($baseLocale);

        // Option -c. Set to compare one or more specific locales.
        $locales = $input->getOption('locale');

        // Get locales to fix
        $localesToFix = $this->getLocales($baseLocale, $locales);

        $this->io->note('Locales to be fixed: |' . implode('|', $localesToFix) . '|');

        if (!$input->getOption('force') && !$this->io->confirm("All translation files for the locales above will be populated using key|values from  | $baseLocale |. Continue?", false)) {
            exit;
        }

        $fixed = [];

        $progressBar = new ProgressBar($output);
        $progressBar->start(count($localesToFix));

        foreach ($localesToFix as $locale) {
            $fixed[$locale] = $this->fixFiles($baseLocale, $locale, $baseLocaleFileNames);
            $progressBar->advance();
        }

        $this->io->newLine(2);

        $filesFixed = $this->getListValues($fixed);
        if (empty($filesFixed)) {
            $this->io->success('No file need fixing');
        } else {
            $this->io->section('Files fixed');
            $this->io->listing($filesFixed);
            $this->io->success('Files fixed successfully');
        }
    }

    /**
     * Build a list of files that were fixed.
     *
     * @param array $array File paths and missing keys.
     *
     * @return array A list of fixed files
     */
    protected function getListValues(array $array): array
    {
        $fixed = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                //We need to loop through it.
                $fixed = array_merge($fixed, $this->getListValues($value));
            } elseif ($value != '0') {
                //It is not an array and not '0', so add it to the list.
                $fixed[] = $value;
            }
        }

        return $fixed;
    }

    /**
     * Iterate over sprinkle locale files and find the difference for two locales.
     *
     * @param string $baseLocale Locale being compared against.
     * @param string $altLocale  Locale to find missing keys for.
     * @param array  $filenames  Sprinkle locale files that will be compared.
     *
     * @return array Filepaths that were fixed.
     */
    protected function fixFiles(string $baseLocale, string $altLocale, array $filenames): array
    {
        $fixed = [];

        foreach ($filenames as $sprinklePath => $files) {
            foreach ($files as $key => $file) {
                $base = $this->parseFile("$sprinklePath/locale/$baseLocale/$file");
                $alt = $this->parseFile("$sprinklePath/locale/$altLocale/$file");
                $filePath = "$sprinklePath/locale/$altLocale/$file";
                $diff = $this->getDifference($base, $alt);
                $missing = $this->arrayFlatten($diff);

                // The files with missing keys.
                if (!empty($missing)) {
                    $fixed[] = $this->fix($base, $alt, $filePath, $missing);
                }
            }
        }

        return $fixed;
    }

    /**
     * Fixes locale files by adding missing keys.
     *
     * @param array  $base
     * @param array  $alt
     * @param string $filePath The path of fixed file.
     * @param array  $missing
     *
     * @return string The path of the fixed file
     */
    protected function fix(array $base, array $alt, string $filePath, array $missing): string
    {
        //If the directory does not exist we need to create it recursively.
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        // Build the respository and then merge in each locale file.
        // Any keys not in the $alt locale will be the original left from the $base locales value.
        $repository = new Repository();
        $repository->mergeItems(null, $base);
        $repository->mergeItems(null, $alt);

        foreach ($missing as $key => $value) {
            if (!$repository->has($key)) {
                if (strpos($key, '@TRANSLATION') !== false) {
                    $val = $repository->get(str_replace('.@TRANSLATION', '', $key));
                    $repository->set($key, $val);
                } else {
                    $repository->set($key, $value);
                }
            }
        }

        // Check if this is an existing locale file with docblock.
        $temp = file_get_contents($filePath);

        if (strpos($temp, '@author') !== false || strpos($temp, '/**') !== false) {
            // Save existing docblock temporarily.
            $start = strpos($temp, '/**');
            $end = strpos(substr($temp, $start), '*/');
            $docblock = file_get_contents($filePath, null, null, $start, $end + 2);

            passthru("echo \<?php > $filePath");

            // We have to add the comment header prior to docblock or php-cs-fixer will overwrite it.
            $this->fixFileWithPhpCs($filePath);

            // Append the docblock after the header comment.
            file_put_contents($filePath, $docblock, FILE_APPEND);
            passthru("echo '\r\n' >> $filePath");
        } else {
            passthru("echo \<?php > $filePath");
        }

        file_put_contents($filePath, var_export($repository->all(), true), FILE_APPEND);

        passthru("echo \; >> $filePath");

        // Final check with php-cs-fixer
        $this->fixFileWithPhpCs($filePath);

        // Insert 'return' into the file.
        file_put_contents($filePath, preg_replace('/\[/', 'return [', file_get_contents($filePath), 1));

        return $filePath;
    }

    /**
     * Fix a file using php-cs-fixer.
     *
     * @param string $file path of file to fix
     */
    public function fixFileWithPhpCs(string $file): void
    {
        // Fix the file with php-cs-fixer
        passthru("php ./app/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix $file --quiet --using-cache no --config ./.php_cs");
    }

    /**
     * {@inheritdoc}
     */
    protected function getLocales(string $baseLocale, ?string $localesToCheck): array
    {
        $configuredLocales = array_diff(array_keys($this->ci->config['site']['locales']['available']), [$baseLocale]);

        // If set, use the locale(s) from the -f option.
        if ($localesToCheck) {
            $locales = explode(',', $localesToCheck);
            foreach ($locales as $key => $value) {
                if (!in_array($value, $configuredLocales)) {
                    $this->io->warning("The |$value| locale was not found in your current configuration. Proceeding may results in a large number of files being created. Are you sure you want to continue?");
                    if (!$this->io->confirm('Continue?', false)) {
                        exit;
                    }
                }
            }

            return $locales;
        } else {
            return $configuredLocales;
        }
    }
}
