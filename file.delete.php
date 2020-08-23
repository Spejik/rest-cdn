<?php

// PURPOSE:
//  Delete a file
//  request params:
//   - string "namespace"
//   - string "file_name"
//   - string "token"

require __DIR__ . "/lib/inc.php";
ALLOW_ONLY_POST();



$input = json_decode(file_get_contents("php://input"));

if ($input->token     && in_array($input->token, $tokens) &&
    $input->namespace && !empty($input->namespace) &&
    $input->file_name && !empty($input->file_name))
{
    if (DeleteFile($input->namespace, $input->file_name))
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

    if (!$input->namespace && empty($input->namespace))
        array_push($response["errors"], "file_name invalid");


    echo json_encode($response);
}



function DeleteFile(string $namespace, string $file_name): bool
{
    $fs = new Filesystem();
    $fsi = new FilesystemIndex();

    // rename data_storage to increase security
    $fs->rename_data_storage();
    // delete any files that expired
    $fsi->delete_files_in_data_storage_if_expired();

    $path = $fs->get_namespace_file_name_path($namespace, $file_name);
    
    // return false if namespace or file doesnt exist
    if (!$fs->get_namespace_exists($namespace) || file_exists($path))
        return false;

    unlink($path);
    return true;
}