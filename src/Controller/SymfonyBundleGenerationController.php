<?php

namespace tomdkd\SymfonyBundleGenerationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;

class SymfonyBundleGenerationController extends AbstractController
{
    private $projectDir;
    private $filesystem;
    private $bundleName;
    private $bundleFolderName;
    private $fullPathToBundleFolder;
    private $namespace;

    public function __construct(String $projectDir)
    {
        $this->projectDir = $projectDir;
        $this->filesystem = new Filesystem();
    }

    public function getBundleFolderName(): string
    {
        return $this->bundleFolderName;
    }

    public function generateLocalBundleFolder(): bool
    {
        $localBundlesDir = sprintf('%s/local_bundles', $this->projectDir);

        if (!$this->filesystem->exists($localBundlesDir)) {
            $this->filesystem->mkdir($localBundlesDir);
            return $this->filesystem->exists($localBundlesDir);
        }

        return true;
    }

    public function generateBundleFolder(String $bundleName): bool
    {
        $this->bundleName = $bundleName;
        $this->bundleFolderName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $this->bundleName));
        $this->fullPathToBundleFolder = sprintf('%s/local_bundles/%s', $this->projectDir, $this->bundleFolderName);
        $this->filesystem->mkdir($this->fullPathToBundleFolder);

        return $this->filesystem->exists($this->fullPathToBundleFolder);
    }

    public function generateBaseBundleFile(String $namespace): bool
    {
        $this->namespace = $namespace;
        $baseBundleFile = sprintf('%s.php', $this->bundleName);
        $baseBundleFilePath = sprintf('%s/%s', $this->fullPathToBundleFolder, $baseBundleFile);

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

    public function overloadBundle(): bool
    {
        return true;
    }

}
