<?php

class Client extends Database
{

    /**
     * Method Insert Client
     * 
     * @param mixed $doc
     * @param mixed $name
     * @param mixed $email
     * @param mixed $movil
     * @return array
     */
    public function insertClient($doc, $name, $email, $movil)
    {
        try {
            $con = parent::conection();
            $stmt = $con->prepare("INSERT INTO clients(id, document, name, email, movil)
                VALUES(null,?, ?, ?, ?);");
            $stmt->bindValue(1, $doc);
            $stmt->bindValue(2, $name);
            $stmt->bindValue(3, $email);
            $stmt->bindValue(4, $movil);
            $stmt->execute();
            return [ 'success' => true, "code_error" => 00, "message_error" => ""];
        } catch (PDOException $e) {
            return [ 'success' => false, "code_error" => $e->getCode(), "message_error" => $e->getMessage()];
        }
    }
}
