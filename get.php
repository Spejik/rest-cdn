<?php


require __DIR__ . "/library.php";


$input = (object) file_get_contents("php://input");

if ($input->token && in_array($input->token, $tokens) &&
    $input->file_name && !empty($input->file_name))
{
    $get = new Get($input->file_name);
    echo $get->get_data();
}
else
{
    $response = ["success" => false, "errors" => []];
    if (!$input->token     && !in_array($input->token, $tokens))
        array_push($response["errors"], "token invalid");

    if (!$input->file_name && empty($input->file_name))
        array_push($response["errors"], "file_name invalid");

    echo json_encode($response);
}


class Get 
{
    private $__name;

    function __construct(string $name)
    {
        $this->__name = $name;
    }


    function get_data()
    {
        $fs = new Filesystem();
        $dir = $fs->get_data_storage_directory() . $fs->get_data_storage_name();

        return file_get_contents($dir . $this->get_file_name());
    }


    function get_file_name()
    {
        return filter_var($this->__name, FILTER_SANITIZE_URL);
    }

}