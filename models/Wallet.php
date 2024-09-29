<?php
class Wallet extends Database
{
    public $client;
    private $wallet;

    /**
     * Method recharge Wallet
     * @param mixed $doc
     * @param mixed $movil
     * @param mixed $value
     * @return array
     */
    public function rechargeWallet($doc, $movil, $value)
    {
        try {
            $this->searchClient($doc, $movil);

            if (empty($this->client)) {
                return ['success' => true, "code_error" => 02, "message_error" => "Client not found"];
            } else {
                $con = parent::conection();
                $query_exist = $con->prepare("SELECT * FROM wallets WHERE client_id=? ORDER BY id DESC limit 1;");
                $query_exist->bindValue(1, $this->client->id);
                $query_exist->execute();

                $stmt = $con->prepare('INSERT INTO wallets(id, client_id, `add`, subtract, confirm_code, confirm_ref, amount, cod_session)
                VALUES(NULL,?, ?, ?, ?, ?, ?, ?);');
                $stmt->bindValue(1, $this->client->id);
                $stmt->bindValue(2, $value);
                $stmt->bindValue(3, 0);
                $stmt->bindValue(4, 0);
                $stmt->bindValue(5, 0, );
                $stmt->bindValue(7, 0);

                if ($query_exist->rowCount() > 0) {
                    $data = $query_exist->fetch(PDO::FETCH_OBJ);
                    $stmt->bindValue(6, $data->amount + $value);
                    $stmt->execute();
                } else {
                    $stmt->bindValue(6, $value);
                    $stmt->execute();
                }

                return ['success' => true, "code_error" => 00, "message_error" => ""];
            }
        } catch (PDOException $e) {
            return ['success' => false, "code_error" => $e->getCode(), "message_error" => $e->getMessage()];
        }

    }

    /**
     * Method pay
     * @param mixed $value
     * @param mixed $confirm_se
     * @param mixed $confirm_code
     * @return array
     */
    public function pay($value, $confirm_se, $confirm_code)
    {
        try {
            $this->searchWallet();

            if (empty($this->wallet)) {
                return [
                    'success' => false,
                    "code_error" => 02,
                    "message_error" => "You do not have a registered wallet, recharge wallet",
                    "confirm_code" => 0,
                    "confirm_ref" => 0
                ];
            }

            if ($this->wallet->amount < $value) {
                return [
                    'success' => false,
                    "code_error" => 03,
                    "message_error" => "Your balance is insufficient",
                    "confirm_code" => 0,
                    "confirm_ref" => 0
                ];
            }


            $con = parent::conection();
            $stmt = $con->prepare('INSERT INTO wallets(id, client_id, `add`, subtract, confirm_code, confirm_ref, amount, cod_session)
                VALUES(NULL,?, ?, ?, ?, ?, ?,?);');
            $stmt->bindValue(1, $this->client->id);
            $stmt->bindValue(2, 0);
            $stmt->bindValue(3, $value);
            $stmt->bindValue(4, $confirm_code);
            $stmt->bindValue(5, 0);
            $stmt->bindValue(6, $value);
            $stmt->bindValue(7, $confirm_se);
            $stmt->execute();
            return [
                'success' => true,
                "code_error" => 00,
                "message_error" => "",
                "confirm_code" => $confirm_code,
                "confirm_ref" => $confirm_se
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                "code_error" => $e->getCode(),
                "message_error" => $e->getMessage(),
                "confirm_code" => 0,
                "confirm_ref" => 0
            ];
        }
    }

    /**
     * Method confirm pay
     * @param mixed $confirm_se
     * @param mixed $confirm_code
     * @return array
     */
    public function confirm_pay($confirm_se, $confirm_code)
    {
        try {
            $con = parent::conection();
            $query_exist = $con->prepare("SELECT * FROM wallets WHERE confirm_code=? AND cod_session=?;");
            $query_exist->bindValue(1, $confirm_code);
            $query_exist->bindValue(2, $confirm_se);
            $query_exist->execute();

            if ($query_exist->rowCount() > 0) {
                $info = $query_exist->fetch(PDO::FETCH_OBJ);
                return $this->confirmPay($info->id, $info->amount - $info->subtract);
            } else {
                return ['success' => false, "code_error" => 404, "message_error" => 'Codes not Found'];
            }
        } catch (PDOException $e) {
            return ['success' => false, "code_error" => $e->getCode(), "message_error" => $e->getMessage()];
        }
    }

    /**
     * Method search Client
     * @param mixed $doc
     * @param mixed $movil
     * @return array
     */
    function searchClient($doc, $movil)
    {
        try {
            $con = parent::conection();
            $query_exist = $con->prepare("SELECT * FROM clients WHERE document=? AND movil=?;");
            $query_exist->bindValue(1, $doc);
            $query_exist->bindValue(2, $movil);
            $query_exist->execute();
            $this->client = $query_exist->fetch(PDO::FETCH_OBJ);

        } catch (PDOException $e) {
            return ['success' => false, "code_error" => $e->getCode(), "message_error" => $e->getMessage()];
        }

    }

    /**
     * Method confirmed Pay
     * @param mixed $id
     * @param mixed $amount
     * @return array
     */
    function confirmPay($id, $amount)
    {
        $confirm_ref = substr(strtotime(date('Y-m-d H:i:s')), 4, 10);
        try {
            $con = parent::conection();
            $query_exist = $con->prepare("UPDATE wallets SET confirm_ref=?, amount=? WHERE id=?;");
            $query_exist->bindValue(1, $confirm_ref);
            $query_exist->bindValue(2, $amount);
            $query_exist->bindValue(3, $id);
            $query_exist->execute();

            return [
                'success' => true,
                "code_error" => 00,
                "message_error" => '',
                "confirm_ref" => $confirm_ref
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                "code_error" => $e->getCode(),
                "message_error" => $e->getMessage(),
                "confirm_ref" => ''
            ];
        }
    }

    /**
     * Method search Wallet exist
     * @return void
     */
    function searchWallet()
    {
        $con = parent::conection();

        $exist_wallet = $con->prepare('SELECT * FROM wallets WHERE client_id=?;');
        $exist_wallet->bindValue(1, $this->client->id);
        $exist_wallet->execute();
        $this->wallet = $exist_wallet->fetch(PDO::FETCH_OBJ);
    }
}