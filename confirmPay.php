<?php

require_once "vendor/econea/nusoap/src/nusoap.php";


$namespace = "ConfirmPaySoap";
$server = new soap_server();

$server->configureWSDL("ConfirmPay", $namespace);

$server->wsdl->schemaTargetNamespace = $namespace;

$server->wsdl->addComplexType(
    "ConfirmPay",
    "complexType",
    "struct",
    "all",
    "",
    array(
        "confirm_code" => array("name" => "confirm_code", "type" => "xsd:string"),
        "cod_session" => array("name" => "cod_session", "type" => "xsd:string")
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
        "confirm_ref" => array("name" => "confirm_ref", "type" => "xsd:string")
    )
);

$server->register(
    "ConfirmPayService",
    array("Pay" => "tns:ConfirmPay"),
    array("Pay" => "tns:response"),
    $namespace,
    false,
    "rpc",
    "encoded",
    "Confirm Pay"
);

function ConfirmPayService($request)
{

    require_once "./config/database.php";
    require_once "./models/Wallet.php";

    $wl = new Wallet();

    $response = $wl->confirm_pay($request['cod_session'], $request['confirm_code']);

    return array(
        "success" => $response['success'],
        "code_error" => $response['code_error'],
        "message_error" => $response['message_error'],
        "confirm_ref" => $response['confirm_ref']
    );
}

$POST_DATA = file_get_contents("php://input");

$server->service($POST_DATA);
exit();