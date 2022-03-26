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

    public function __construct(String $projectDir)
    {
        $this->projectDir = $projectDir;
        $this->filesystem = new Filesystem();
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
        $baseBundleFile = sprintf('%s.php', $this->bundleName);
        $baseBundleFilePath = sprintf('%s/%s', $this->fullPathToBundleFolder, $baseBundleFile);

        $this->filesystem->touch($baseBundleFilePath);

        $baseFileContent = sprintf(
            "<?php
namespace %s;
            
use Symfony\Component\HttpKernel\Bundle\Bundle;
            
class %s extends Bundle
{}", $namespace, $this->bundleName);

        $this->filesystem->appendToFile($baseBundleFilePath, $baseFileContent);

        return $this->filesystem->exists($baseBundleFilePath);
    }

}
