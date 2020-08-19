<?php


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Request must be POST, recieved {$_SERVER["REQUEST_METHOD"]}");
}

if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== "on") {
    die("Request must can be sent only over HTTPS");
}


$tokens = ['Qcm8gwN9FQWrkmmPvYY5afKYVX9ZN6KU'];


class Filesystem 
{

    function get_data_storage_tokens(): array
    {
        return (array) unserialize(file_get_contents($this->get_data_storage_directory() . "/tokens.php.bin"));
    }

    function get_data_storage_name(): string
    {
        return $this->get_data_storage_tokens()[0];
    }

    function get_data_storage_directory(): string 
    {
        return __DIR__ . "/data_storage";
    }

    function rename_data_storage(): void
    {
        $tokens = $this->get_data_storage_tokens();
        $old_token = $this->get_data_storage_name();
        $new_token = bin2hex(random_bytes(5)); // generates a secure random string

        array_unshift($tokens, $new_token);
        
        file_put_contents(
            $this->get_data_storage_directory() . "/tokens.php.bin", 
            serialize((object) $tokens));
        rename(
            $this->get_data_storage_directory() . "/{$old_token}", 
            $this->get_data_storage_directory() . "/{$new_token}");
    }

}
