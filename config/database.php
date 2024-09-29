<?php

class Database{
    protected $dbh;

    /**
     * Method connection DB
     * 
     * @return PDO
     */
    protected function conection(){
        try {
            $conection = $this->dbh =  new PDO("mysql:host=127.0.0.1;dbname=db_name","db_user","db_pw");
            return $conection;
        } catch (Exception $e) {
            print "¡Error!: ".$e->getMessage()."<br/>";
            die();
        }
    }

    
}