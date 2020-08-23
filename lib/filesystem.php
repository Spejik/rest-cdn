<?php

// PURPOSE:
//  Class Filesystem
//  for manipulating data_storage
//  for changing data storage token


class Filesystem 
{
    public $tokens_file = "tokens.bin.php";


    // Get an array of all tokens 
    // token 0 is latest
    function get_datastorage_tokens(): array
    {
        return 
        (array) unserialize(
            file_get_contents(
                $this->get_datastorage_path() . $this->tokens_file));
    }


    // Get the folder where data is actually stored
    function get_datastorage_token(): string
    {
        return $this->get_datastorage_tokens()[0];
    }


    // Get the name of the folder where data is getting stored to
    function get_datastorage_path(): string 
    {
        return realpath(__DIR__ . "/../" . "data_storage/");
    }


    // {datastorage}/{token}/
    function get_datastorage_token_path(): string
    {
        return $this->get_datastorage_path() . $this->get_datastorage_token() . DIRECTORY_SEPARATOR;
    }



    // @{namespace}/{file_name}
    function get_namespace_filename_string(string $namespace, string $file_name): string
    {
        return  $this->get_namespace_string($namespace) . DIRECTORY_SEPARATOR . $this->get_filename_string($file_name);
    }


    // {data_storage}/{token}/@{namespace}/
    function get_datastorage_token_namespace_path(string $namespace): string
    {
        return $this->get_datastorage_token_path() . $this->get_namespace_string($namespace) . DIRECTORY_SEPARATOR;
    }


    // {data_storage}/{token}/@{namespace}/{file_name}
    function get_datastorage_token_namespace_filename_path(string $namespace, string $file_name): string
    {
        return $this->get_datastorage_token_namespace_path($namespace) . $this->get_filename_string($file_name);
    }


    
    
    // Get whether namespace exists in data_storage or not
    function get_namespace_exists(string $namespace): string
    {
        return file_exists($this->get_datastorage_token_namespace_path($namespace));
    }


    // Get sanitized namespace with @ in front of it
    function get_namespace_string(string $namespace): string 
    {
        return "@" . getAlphanumeric(filter_var($namespace, FILTER_SANITIZE_URL));
    }


    // Get sanitized file name
    function get_filename_string(string $file_name): string
    {
        return filter_var($file_name, FILTER_SANITIZE_URL);
    }


    // Rename the (sub)folder (default "_____") where data is stored
    function rename_data_storage(): void
    {
        $tokens = $this->get_datastorage_tokens();
        $old_token = $this->get_datastorage_token();
        $new_token = bin2hex(random_bytes(32)); // generates a secure random string (32 bytes)

        array_unshift($tokens, $new_token);
        
        file_put_contents(
            $this->get_datastorage_path() . $this->tokens_file, 
            serialize((array) $tokens));
        rename(
            $this->get_datastorage_path() . DIRECTORY_SEPARATOR . "$old_token", 
            $this->get_datastorage_path() . DIRECTORY_SEPARATOR . "$new_token");
    }

}

