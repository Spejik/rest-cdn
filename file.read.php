<?php

// PURPOSE:
//  Get contents of a file (POST)
//  request params:
//   - string "namespace"
//   - string "file_name"
//   - string "token"

require __DIR__ . "/lib/inc.php";
ALLOW_ONLY_POST();


$input = json_decode(file_get_contents("php://input"));

if ($input->token && in_array($input->token, $tokens) &&
    isset($input->namespace) &&
    $input->file_name && !empty($input->file_name))
{
    $get = new Get(
        (string) $input->namespace, 
        (string) $input->file_name);

    echo $get->get_data();
}
else
{
    $response = ["success" => false, "errors" => []];
    if (!$input->token && !in_array($input->token, $tokens))
        array_push($response["errors"], "token invalid");

    if (!isset($input->namespace))
        array_push($response["errors"], "namespace invalid");

    if (!$input->file_name && empty($input->file_name))
        array_push($response["errors"], "file_name invalid");

    echo json_encode($response);
}


class Get 
{
    private $fs;
    private $fsi;
    
    private $__namespace;
    private $__name;

    function __construct(string $namespace, string $name)
    {
        $this->fs  = new Filesystem();
        $this->fsi = new FilesystemIndex();

        $this->__namespace = $this->fs->get_namespace_string($namespace);
        $this->__name = filter_var($name, FILTER_SANITIZE_URL);
        
        if ($this->__namespace === "")
            $this->__namespace = $this->fs->get_namespace_string("global");
    }


    function get_data()
    {
        // delete any files that expired
        $this->fsi->delete_files_in_data_storage_if_expired();

        $file = $this->fs->get_datastorage_token_namespace_path($this->__namespace) . $this->__name;

        return @file_get_contents($file);
    }
}