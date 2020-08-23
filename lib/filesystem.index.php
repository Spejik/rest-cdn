<?php

// PURPOSE:
//  Class FilesystemIndex
//  for manipulating data index



class FilesystemIndex 
{
    private $fs;
    public $index_file = DIRECTORY_SEPARATOR . "index.bin.php";

    function __construct()
    {
        $this->fs = new Filesystem();
    }


    // Get data storage index file
    function get_data_storage_index(): array
    {
        return 
        (array) unserialize(
            file_get_contents(
                $this->fs->get_data_storage_directory() . $this->index_file));
    }


    // Get files in data_storage/(@namespace)/...
    function get_data_storage_files_recursive(): array 
    {
        $rii = 
        new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->fs->get_data_storage_path()));

        $files = []; 
        
        foreach ($rii as $file) {
            if ($file->isDir()){ 
                continue;
            }
        
            $files[] = $file->getPathname(); 
        }

        return $files;
    }


    // Delete ANY files in data_storage if date of creation is beyond expiration date
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


    // Returns if file exists on disk.. how unexpected
    function file_exists_on_disk(string $file): bool
    {
        return file_exists($this->fs->get_data_storage_path() . $file);
    }


    // Returns if specific file is in index.bin.php
    function file_exists_in_index(string $file): bool
    {
        $files = $this->get_data_storage_files_recursive();
        return (in_array($file, $files));
    }



    function add_index_field(string $namespace, string $file_name, DateTime $expires): void
    {
        $index = $this->get_data_storage_index();
        $index[$this->fs->get_namespace_file_name_string($namespace, $file_name)] 
            = [
                "created" => new DateTime(),
                "expires" => $expires,
                "namespace" => $namespace,
                "file_name" => $file_name,
        ];

        file_put_contents(
            $this->fs->get_data_storage_directory() . $this->index_file, 
            serialize((array) $index));
    }
}