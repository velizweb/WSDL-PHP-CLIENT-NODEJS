<?php

require_once "vendor/econea/nusoap/src/nusoap.php";

$namespace = "RegisterClientSoap";
$server = new soap_server();

$server->configureWSDL("InsertClient", $namespace);

$server->wsdl->schemaTargetNamespace = $namespace;

$server->wsdl->addComplexType(
    "InsertClient",
    "complexType",
    "struct",
    "all",
    "",
    array(
        "document" => array("name" => "document", "type" => "xsd:string"),
        "name" => array("name" => "name", "type" => "xsd:string"),
        "email" => array("name" => "email", "type" => "xsd:string"),
        "movil" => array("name" => "movil", "type" => "xsd:string")
    )
);

$server->wsdl->addComplexType(
    "response",
    "complexType",
    "struct",
    "all",
    "",
    array(
        "success" => array("name" => "success", "type" => "xsd:boolean"),
        "code_error" => array("name" => "code_error", "type" => "xsd:integer"),
        "message_error" => array("name" => "message_error", "type" => "xsd:string")
    )
);

$server->register(
    "InsertClientService",
    array("InsertClient" => "tns:InsertClient"),
    array("InsertClient" => "tns:response"),
    $namespace,
    false,
    "rpc",
    "encoded",
    "Insert Client"
);

function InsertClientService($request)
{

    require_once "./config/database.php";
    require_once "./models/Client.php";

    $client = new Client();

    $response = $client->insertClient($request['document'], $request['name'], $request['email'], $request['movil']);
    
    
    return array(
        "success" => $response['success'],
        "code_error" => $response['code_error'],
        "message_error" => $response['message_error']
    );
}


$POST_DATA = file_get_contents("php://input");


$server->service($POST_DATA);
exit();