<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadManager
{
    private string $uploadsDir;
    private SluggerInterface $slugger;
    private Filesystem $filesystem;

    public function __construct(string $uploadsDir, SluggerInterface $slugger)
    {
        $this->uploadsDir = rtrim($uploadsDir, '/');
        $this->slugger = $slugger;
        $this->filesystem = new Filesystem();
    }

    public function upload(UploadedFile $file, string $subdir): string
    {
        $normalizedSubdir = trim($subdir, '/');
        $targetDir = $this->uploadsDir.'/'.$normalizedSubdir;
        $this->filesystem->mkdir($targetDir, 0775);

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $newFilename = $safeFilename.'-'.uniqid('', true).'.'.$extension;

        $file->move($targetDir, $newFilename);

        return '/uploads/'.$normalizedSubdir.'/'.$newFilename;
    }

    public function remove(?string $publicPath): void
    {
        if (!$publicPath) {
            return;
        }

        if (!str_starts_with($publicPath, '/uploads/')) {
            return;
        }

        $relativePath = ltrim(substr($publicPath, strlen('/uploads/')), '/');
        $absolutePath = $this->uploadsDir.'/'.$relativePath;

        if (is_file($absolutePath)) {
            $this->filesystem->remove($absolutePath);
        }
    }
}
