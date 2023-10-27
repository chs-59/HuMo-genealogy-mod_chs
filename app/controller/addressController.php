<?php
require_once  __DIR__ . "/../model/address.php";

include_once(__DIR__ . "/../../include/person_cls.php");
include_once(__DIR__ . "/../../include/show_sources.php");
include_once(__DIR__ . "/../../include/show_picture.php");

class addressController
{
    private $db_functions, $user;

    public function __construct($db_functions, $user)
    {
        $this->db_functions = $db_functions;
        $this->user = $user;
    }

    public function detail()
    {
        $model = new Address($this->db_functions);

        $authorised = $model->getAddressAuthorised($this->user);
        $address = $model->getById($_GET["id"]);
        $address_sources = $model->getAddressSources($_GET["id"]);
        $address_connected_persons = $model->getAddressConnectedPersons($_GET["id"]);

        $data = array(
            "authorised" => $authorised,
            "address" => $address,
            "address_sources" => $address_sources,
            "address_connected_persons" => $address_connected_persons,
            "title" => __('Address')
        );
        return $data;
    }
}