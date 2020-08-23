<?php

// PURPOSE:
//  Create a namespace by 
//   - creating a folder starting with "@" in data_storage
//  request params:
//   - string "namespace"
//   - string "token"

require __DIR__ . "/lib/inc.php";
ALLOW_ONLY_POST();



$input = json_decode(file_get_contents("php://input"));

if ($input->token     && in_array($input->token, $tokens) &&
    $input->namespace && !empty($input->namespace))
{
    if (CreateNamespace($input->namespace))
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



function CreateNamespace(string $namespace): bool
{
    $fs = new Filesystem();
    $fsi = new FilesystemIndex();
    
    // rename data_storage to increase security
    $fs->rename_data_storage();
    // delete any files that expired
    $fsi->delete_files_in_data_storage_if_expired();

    $path = $fs->get_data_storage_namespace_path($namespace);

    // return false if namespace exists
    if ($fs->get_namespace_exists($namespace))
        return false;

    mkdir($path);
    return true;
}