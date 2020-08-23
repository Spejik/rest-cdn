<?php

// PURPOSE:
//  Class FilesystemIndex
//  for manipulating data index



class FilesystemIndex 
{
    private $fs;
    public $index_file = "index.bin.php";

    function __construct()
    {
        $this->fs = new Filesystem();
    }


    /**
     * Get data index file content
     */
    function get_data_storage_index(): array
    {
        return 
        (array) unserialize(
            file_get_contents(
                $this->fs->get_datastorage_path() . $this->index_file));
    }


    /**
     * Get files in data_storage/[...@{namespace}]/...
     */
    function get_data_storage_files_recursive(): array 
    {
        $rii = 
        new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->fs->get_datastorage_token_path()));

        $files = []; 
        
        foreach ($rii as $file) {
            if ($file->isDir()){ 
                continue;
            }
        
            $files[] = $file->getPathname(); 
        }

        return $files;
    }


    /**
     * Delete ANY files in data_storage if date of creation is beyond expiration date
     */
    function delete_files_in_data_storage_if_expired(): void
    {
        $files = $this->get_data_storage_files_recursive();
        $index = $this->get_data_storage_index();

        foreach ($files as $file) {
            if (in_array($file, $index))
            {
                $metadata = $index[$file];
                $date = new DateTime();

                // If difference between NOW and CREATED is bigger than the time when file expires
                // delete it from disk, but keep it in index, so we can show "file deleted" message
                if ($date->getTimestamp() - $metadata["created"]->getTimestamp() >= $metadata["expires"]->getTimestamp())
                    unlink($file);
            }
        }
    }


    /**
     * Returns if file exists on disk.. how unexpected
     */
    function file_exists_on_disk(string $namespace, string $file): bool
    {
        return file_exists(
            $this->fs->get_datastorage_token_path() . $this->fs->get_namespace_filename_string($namespace, $file));
    }


    /**
     * Returns if specific file is in data index
     */
    function file_exists_in_index(string $namespace, string $file): bool
    {
        $files = $this->get_data_storage_files_recursive();
        return in_array($this->fs->get_namespace_filename_string($namespace, $file), $files);
    }


    /**
     * Add a field to data index
     */
    function add_index_field(string $namespace, string $file_name, DateTime $expires): void
    {
        $index = $this->get_data_storage_index();
        $file = $this->fs->get_namespace_filename_string($namespace, $file_name);
        $index[$file] = 
        [
            "created" => new DateTime(),
            "expires" => $expires,
            "namespace" => $namespace,
            "file_name" => $file_name,
        ];

        file_put_contents(
            $this->fs->get_datastorage_path() . $this->index_file, 
            serialize((array) $index));
    }
}