<?php

namespace Core\Acl\Repository;

use Core\Acl\Contracts\PolicyRepository;
use Core\Acl\Io\ArrayParser;
use Core\Acl\Policy\Policy;
use Core\Acl\Policy\PolicySet;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;

class FileRepository implements PolicyRepository
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $path = 'policies';

    /** @var ArrayParser */
    private $parser;

    /**
     * FileRepository constructor.
     * @param Filesystem $filesystem
     * @param ArrayParser $parser
     */
    public function __construct(Filesystem $filesystem, ArrayParser $parser)
    {
        $this->filesystem = $filesystem;
        $this->parser = $parser;
    }

    /**
     * @return array
     * @throws FileNotFoundException
     */
    public function fetch()
    {
        $policies = $this->filesystem->files($this->path);

        /** @var PolicySet[] $output */
        $output = [];
        foreach ($policies as $file_path) {
            $contents = $this->filesystem->get($file_path);
            $data = json_decode($contents, true);
            $output[] = $this->parser->parse($data);
        }
        return $output;
    }

    /**
     * @param string $id
     * @return PolicySet|Policy
     * @throws FileNotFoundException
     */
    public function load(string $id)
    {
        $contents = $this->filesystem->get($this->getPath($id));
        $data = json_decode($contents, true);
        return $this->parser->parse($data);
    }

    /**
     * @param string $id
     * @param string|array $data
     */
    public function store(string $id, $data)
    {
        if (is_array($data)) $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->filesystem->put($this->getPath($id), $data);
    }

    /**
     * @param string $id
     * @param string|array $data
     */
    public function update(string $id, $data)
    {
        if (is_array($data)) $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->filesystem->put($this->getPath($id), $data);
    }

    /**
     * @param string $id
     */
    public function delete(string $id)
    {
        $this->filesystem->delete($this->getPath($id));
    }

    /**
     * @param string $id
     * @return string
     */
    private function getPath(string $id)
    {
        return $this->path.'/'.$id.'.json';
    }
}