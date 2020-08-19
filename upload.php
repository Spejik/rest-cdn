<?php


require __DIR__ . "/library.php";

$input = (object) file_get_contents("php://input");
if ($input->token && in_array($input->token, $tokens) &&
    $input->file_name && !empty($input->file_name) &&
    $input->file_data && !empty($input->file_data))
{
    $upload = new Upload($input->file_name, $input->file_data);
    echo json_encode(["success" => true, "file_location" => "{$upload->get_file_name()}"]);
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

    function __construct(string $name, string $data)
    {
        $fs = new Filesystem();
        $this->__name = $name;

        // renames the data storage before anything gets uploaded to it, so we have more security about the data storage
        $fs->rename_data_storage();
        $dir = $fs->get_data_storage_directory() . $fs->get_data_storage_name();

        file_put_contents($dir . $this->get_file_name(), $data);
    }

    function get_file_name(): string
    {
        return filter_var($this->__name, FILTER_SANITIZE_URL);
    }

}