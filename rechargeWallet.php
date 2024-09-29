<?php

require_once "vendor/econea/nusoap/src/nusoap.php";

$namespace = "RechargeWalletSoap";
$server = new soap_server();

$server->configureWSDL("RechargeWallet", $namespace);

$server->wsdl->schemaTargetNamespace = $namespace;

$server->wsdl->addComplexType(
    "RechargeWallet",
    "complexType",
    "struct",
    "all",
    "",
    array(
        "document" => array("name" => "document", "type" => "xsd:string"),
        "movil" => array("name" => "movil", "type" => "xsd:string"),
        "value" => array("name" => "value", "type" => "xsd:decimal")
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
    "RechargeWalletService",
    array("RechargeWalllet" => "tns:RechargeWallet"),
    array("RechargeWalllet" => "tns:response"),
    $namespace,
    false,
    "rpc",
    "encoded",
    "Recharge Wallet"
);

function RechargeWalletService($request)
{
    
    require_once "./config/database.php";
    require_once "./models/Wallet.php";

    $wl= new Wallet();

    $response = $wl->rechargeWallet($request['document'], $request['movil'], $request['value']);
    
    
    return array(
        "success" => $response['success'],
        "code_error" => $response['code_error'],
        "message_error" => $response['message_error']
    );
}

$POST_DATA = file_get_contents("php://input");

$server->service($POST_DATA);
exit();