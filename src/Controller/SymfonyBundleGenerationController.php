<?php

namespace tomdkd\SymfonyBundleGenerationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;

class SymfonyBundleGenerationController extends AbstractController
{
    private $projectDir;

    public function __construct(String $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function generateLocalBundleFolder()
    {
        $filesystem      = new Filesystem();
        $localBundlesDir = sprintf('%s/test_folder', $this->projectDir);

        if (!$filesystem->exists($localBundlesDir)) {
            $filesystem->mkdir($localBundlesDir);
        }

        return true;
    }

}
