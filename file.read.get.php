<?php

// PURPOSE:
//  Get contents of a file (GET)
//  Only works for files in @global
//  request params:
//   - string "name"

require __DIR__ . "/lib/inc.php";



if ($_GET["name"] && !empty($_GET["name"])) {
    $fs = new Filesystem();
    $fsi = new FilesystemIndex();

     // delete any files that expired
    $fsi->delete_files_in_data_storage_if_expired();

    $file_name = filter_var($_GET["name"], FILTER_SANITIZE_URL);
    $file_loc = $fs->get_namespace_filename_string("global", $file_name);

    if ($fsi->file_exists_on_disk("global", $file_name))
        echo file_get_contents($fs->get_datastorage_token_namespace_filename_path("global", $file_name));
    else
        if ($fsi->file_exists_in_index("global", $file_name))
            echo "file expired";
        else 
            echo "file unknown";
} else {
    echo "file name invalid";
}
