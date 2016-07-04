<?php
namespace FractalManager\Adapter;

use FractalManager\Exception\RuntimeException;
use League\Flysystem\FilesystemInterface;
use Staticus\Config\ConfigInterface;

class GetRandomFileFromDirAdapter implements AdapterInterface
{
    const DEFAULT_SIZE = 1024;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $config;

    public function __construct(FilesystemInterface $filesystem, ConfigInterface $config)
    {
        $this->config = $config->get('staticus.fractal_templates');
        $this->filesystem = $filesystem;
    }

    public function generate($query)
    {
        $files = $this->filesystem->listContents($this->config['directory']);
        array_filter($files, [$this, 'filterFiles']);
        if (empty($files)) {
            throw new RuntimeException('Cannot generate fractal image: template folder is empty');
        }
        mt_srand((double)microtime() * 1000000);
        mt_rand(0, 1);
        $random_file = mt_rand(0, count($files) - 1);
        $resource = imagecreatefromjpeg(DIRECTORY_SEPARATOR . $files[$random_file]['path']);
        if (!is_resource($resource)) {
            throw new RuntimeException('Cannot create image from the fractal template: ' . $files[$random_file]['basename']);
        }

        return $resource;

        // this variant will not work because of the different resource type
        // return $this->filesystem->readStream($files[$random_file]['path']);
    }
    protected function filterFiles($file)
    {
        return ($this->config['extension'] === $file['extension']);
    }
}