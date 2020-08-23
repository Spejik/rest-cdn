<?php

// PURPOSE:
//  Delete a namespace from 
//   1. data_storage
//   2. data index
//  request params:
//   - string "namespace"
//   - string "token"

require __DIR__ . "/lib/inc.php";
ALLOW_ONLY_POST();



$input = json_decode(file_get_contents("php://input"));

if ($input->token     && in_array($input->token, $tokens) &&
    $input->namespace && !empty($input->namespace))
{
    if (DeleteNamespace($input->namespace))
        echo json_encode(["success" => true]);
    else 
        echo json_encode(["success" => false]);
}
else
{
    $response = ["success" => false, "errors" => []];
    if (!$input->token && !in_array($input->token, $tokens))
        array_push($response["errors"], "token invalid");

    if (!$input->namespace && empty($input->namespace))
        array_push($response["errors"], "namespace invalid");


    echo json_encode($response);
}



function DeleteNamespace(string $namespace): bool
{
    $fs = new Filesystem();
    $fsi = new FilesystemIndex();

    // rename data_storage to increase security
    $fs->rename_data_storage();
    // delete any files that expired
    $fsi->delete_files_in_data_storage_if_expired();

    $path = $fs->get_data_storage_namespace_path($namespace);
    
    // return false if namespace doesnt exist
    if (!$fs->get_namespace_exists($namespace))
        return false;


    $iterator = new RecursiveDirectoryIterator(
        $path, 
        RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($iterator,
                RecursiveIteratorIterator::CHILD_FIRST);


    // remove file or directory
    foreach ($files as $file) {
        if ($file->isDir())
            rmdir($file->getRealPath());
        else 
            unlink($file->getRealPath());
    }


    rmdir($path);
    return true;
}