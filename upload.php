<?php


require __DIR__ . "/library.php";

$input = json_decode(file_get_contents("php://input"));

if ($input->token && in_array($input->token, $tokens) &&
    $input->file_name && !empty($input->file_name) &&
    $input->file_data && !empty($input->file_data))
{
    $upload = new Upload($input->file_name, $input->file_data);

    if ($upload->upload_file())
        echo json_encode(["success" => true, "file_location" => "{$upload->get_file_name()}"]);
    else 
        echo json_encode(["success" => false, "errors" => ["file '{$input->file_name}' exists"]]);
}
else
{
    $response = ["success" => false, "errors" => []];
    if (!$input->token     && !in_array($input->token, $tokens))
        array_push($response["errors"], "token invalid");

    if (!$input->file_name && empty($input->file_name))
        array_push($response["errors"], "file_name invalid");

    if (!$input->file_data && empty($input->file_data))
        array_push($response["errors"], "file_data invalid");

    echo json_encode($response);
}


class Upload 
{
    private $__name;
    private $__data;

    function __construct(string $name, string $data)
    {
        $this->__name = $name;
        $this->__data = $data;
    }

    function upload_file(): bool
    {
        $fs = new Filesystem();
        
        // renames the data storage before anything gets uploaded to it, so we have more security about the data storage
        $fs->rename_data_storage();
        $dir = $fs->get_data_storage_directory() . "/" . $fs->get_data_storage_name() . "/";
        $file = $dir . $this->get_file_name();

        if (file_exists($file))
            return false;

        if (file_put_contents($file, $this->__data) === false) 
            return false;
        return true;
    }

    function get_file_name(): string
    {
        return filter_var($this->__name, FILTER_SANITIZE_URL);
    }

}