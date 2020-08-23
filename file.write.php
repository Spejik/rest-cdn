<?php

// PURPOSE:
//  Write into a file 
//  request params:
//   - string "namespace"
//   - string "file_name"
//   - string "file_data"
//   - string "token"

require __DIR__ . "/lib/inc.php";
ALLOW_ONLY_POST();

// change this to increase file size limit
const MAX_FILE_SIZE = 1024 * 1024 * 2;
const FILE_KEEP_DAYS = 24;



$input = json_decode(file_get_contents("php://input"));

if ($input->token     && in_array($input->token, $tokens) &&
    isset($input->namespace) &&
    $input->file_name && !empty($input->file_name) &&
    $input->file_data && !empty($input->file_data))
{
    if (mb_strlen($input->file_data, '8bit') > MAX_FILE_SIZE) {
        echo json_encode([
            "success" => false, 
            "errors" => [ "sizeof(file_data) is greater than {$MAX_FILE_SIZE} bytes" ]
            ]);
    }

    $upload = new WriteFile(
        (string) $input->namespace, 
        (string) $input->file_name, 
        (string) $input->file_data);

    if ($upload->upload_file())
        echo json_encode([
            "success" => true, 
            "file_location" => "{$upload->get_file_location()}",
            "expires" => $upload->expires
            ]);
    else 
        echo json_encode([
            "success" => false, 
            "errors" => [ "file exists" ]
            ]);
}
else
{
    $response = [ "success" => false, "errors" => [] ];
    if (!$input->token && !in_array($input->token, $tokens))
        array_push($response["errors"], "token invalid");

    if (!isset($input->namespace))
        array_push($response["errors"], "namespace invalid");

    if (!$input->file_name && empty($input->file_name))
        array_push($response["errors"], "file_name invalid");

    if (!$input->file_data && empty($input->file_data))
        array_push($response["errors"], "file_data invalid");

    echo json_encode($response);
}


class WriteFile
{
    private $fs;
    private $fsi;

    private $__namespace;
    private $__name;
    private $__data;
    public $expires;
    

    function __construct(string $namespace, string $name, string $data)
    {
        $this->fs  = new Filesystem();
        $this->fsi = new FilesystemIndex();

        $this->expires = new DateTime();
        $this->expires->add(new DateInterval("P" . FILE_KEEP_DAYS . "D"));

        $this->__namespace = $this->fs->get_namespace_string($namespace);
        $this->__name = filter_var($name, FILTER_SANITIZE_URL);
        $this->__data = $data;

        if ($this->__namespace === "")
            $this->__namespace = $this->fs->get_namespace_string("global");
    }


    // save the file into a file
    function upload_file(): bool
    {
        // rename data_storage to increase security
        $this->fs->rename_data_storage();
        // delete any files that expired
        $this->fsi->delete_files_in_data_storage_if_expired();
        $file = $this->fs->get_datastorage_token_namespace_path($this->__namespace) . $this->__name;

        file_put_contents($file, $this->__data);

        $this->fsi->add_index_field($this->__namespace, $this->__name, $this->expires);
        return true;
    }

    function get_file_location(): string
    {
        return "{$this->__namespace}/{$this->__name}";
    }

}