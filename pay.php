<?php

require_once "vendor/econea/nusoap/src/nusoap.php";


$namespace = "PaySoap";
$server = new soap_server();

$server->configureWSDL("Pay", $namespace);

$server->wsdl->schemaTargetNamespace = $namespace;

$server->wsdl->addComplexType(
    "Pay",
    "complexType",
    "struct",
    "all",
    "",
    array(
        "document" => array("name" => "document", "type" => "xsd:string"),
        "movil" => array("name" => "movil", "type" => "xsd:string"),
        "value" => array("name" => "confirm_ref", "type" => "xsd:string")
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
        "message_error" => array("name" => "message_error", "type" => "xsd:string"),
        "confirm_code" => array("name" => "confirm_code", "type" => "xsd:string"),
        "confirm_ref" => array("name" => "confirm_ref", "type" => "xsd:string")
    )
);

$server->register(
    "PayService",
    array("Pay" => "tns:Pay"),
    array("Pay" => "tns:response"),
    $namespace,
    false,
    "rpc",
    "encoded",
    "Pay"
);

function PayService($request)
{

    require_once "./config/database.php";
    require_once "./models/Wallet.php";

    $wl = new Wallet();

    $wl->searchClient($request['document'], $request['movil']);
    $confirm_code = substr(strtotime(date('Y-m-d H:i:s')), 4, 10);
    $confirm_ref = substr(strtotime(date('Y-m-d H:i:s')), 0, 3);

    $response = $wl->pay($request['value'], $confirm_ref, $confirm_code);

    return array(
        "success" => $response['success'],
        "code_error" => $response['code_error'],
        "message_error" => $response['message_error'],
        "confirm_code" => $response['confirm_code'],
        "confirm_ref" => $response['confirm_ref']
    );
}

$POST_DATA = file_get_contents("php://input");

$server->service($POST_DATA);
exit();