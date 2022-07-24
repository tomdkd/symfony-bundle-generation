<?php

namespace Tomdkd\SymfonyBundleGeneratorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;

class SymfonyBundleGeneratorController extends AbstractController
{
    private string $projectDir;
    private Filesystem $filesystem;
    private string $bundleName;
    private string $bundleFolderName;
    private string $fullPathToBundleFolder;
    private string $namespace;

    public function __construct(String $projectDir)
    {
        $this->projectDir = $projectDir;
        $this->filesystem = new Filesystem();
    }

    /**
     * Get bundle folder name.
     *
     * @return string
     */
    public function getBundleFolderName(): string
    {
        return $this->bundleFolderName;
    }

    /**
     * Generate a folder in project dir who store all bundles that haven't official
     * version yet or bundles you want to overload.
     *
     * @return bool
     */
    public function generateLocalBundleFolder(): bool
    {
        $localBundlesDir = sprintf('%s/local_bundles', $this->projectDir);

        if (!$this->filesystem->exists($localBundlesDir)) {
            $this->filesystem->mkdir($localBundlesDir);
            return $this->filesystem->exists($localBundlesDir);
        }

        return true;
    }

    /**
     * Generate the bundle folder.
     * It convert bundle name in Pascal Case by changing before capital letters into -.
     *
     * @param String $bundleName
     * @return bool
     */
    public function generateBundleFolder(String $bundleName): bool
    {
        $this->bundleName = $bundleName;
        $this->bundleFolderName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $this->bundleName));
        $this->fullPathToBundleFolder = sprintf('%s/local_bundles/%s', $this->projectDir, $this->bundleFolderName);
        $this->filesystem->mkdir($this->fullPathToBundleFolder);
        $this->filesystem->mkdir(sprintf('%s/src', $this->fullPathToBundleFolder));

        return $this->filesystem->exists($this->fullPathToBundleFolder);
    }

    /**
     * Generate the file used by symfony to register the bundle.
     *
     * @param String $namespace
     * @return bool
     */
    public function generateBaseBundleFile(String $namespace): bool
    {
        $this->namespace = $namespace;
        $baseBundleFile = sprintf('%s.php', $this->bundleName);
        $baseBundleFilePath = sprintf('%s/src/%s', $this->fullPathToBundleFolder, $baseBundleFile);

        $this->filesystem->touch($baseBundleFilePath);

        $baseFileContent = sprintf(
            "<?php
namespace %s;
            
use Symfony\Component\HttpKernel\Bundle\Bundle;
            
class %s extends Bundle
{}", $this->namespace, $this->bundleName);

        $this->filesystem->appendToFile($baseBundleFilePath, $baseFileContent);

        return $this->filesystem->exists($baseBundleFilePath);
    }

    /**
     * Add a line in the config/bundles.php to add your new bundle
     * by its namespace.
     *
     * @return bool
     */
    public function activateBundle(): bool
    {
        $bundleFilePath = sprintf('%s/config/bundles.php', $this->projectDir);
        $bundlesFile = file($bundleFilePath);
        $lastAlias = (count($bundlesFile) - 1);
        $beforeLastContent = $bundlesFile[(count($bundlesFile) - 2)];

        if (!strpos($beforeLastContent, ',')) {
            $bundlesFile[(count($bundlesFile) - 2)] = sprintf("     %s,\n", trim($beforeLastContent));
        }

        $bundlesFile[$lastAlias] = sprintf("    %s\%s::class => ['all' => true]\n", $this->namespace, $this->bundleName);
        $bundlesFile[] = "];\n";

        file_put_contents($bundleFilePath, '');

        foreach ($bundlesFile as $line) {
            file_put_contents($bundleFilePath, $line, FILE_APPEND);
        }

        return true;
    }

    /**
     * Check if bundle name contains only alphabetic caracters.
     *
     * @param String $bundleName
     * @return bool
     */
    public function validateBundleName(String $bundleName): bool
    {
        return preg_match('/[A-Za-z]/', $bundleName);
    }

    /**
     * Convert bundle name to PascalCase
     *
     * @param String $bundleName
     * @return string
     */
    public function formatBundleName(String $bundleName): string
    {
        $stringExploded = explode(' ', $bundleName);

        foreach ($stringExploded as $id => $word) {
            $stringExploded[$id] = ucfirst($word);
        }

        return implode('', $stringExploded);
    }

}
