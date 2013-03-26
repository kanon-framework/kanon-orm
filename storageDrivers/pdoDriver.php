<?php

#require_once dirname(__FILE__).'/../storageDriver.php';

class pdoDriver extends storageDriver{
    public function getDataTypeSql($type, $size, $unsigned, $notNull){
        $nn = $notNull?' NOT NULL':'';
        $u = $unsigned?' UNSIGNED':'';
        switch ($type){
            case modelProperty::TYPE_VARCHAR:
                switch ($this->_databaseType){
                    case 'sqlite':
                        return 'VARCHAR';
                    default:
                        return 'VARCHAR('.$size.')';
                }
            case modelProperty::TYPE_INTEGER:
                // $size - display width of integer
                switch ($this->_databaseType){
                    case 'sqlite':
                        return 'INTEGER'.$nn;
                    default:
                        if ($size > 10){
                            return 'BIGINT('.$size.')'.$u.$nn; // BIGINT is an extension to the SQL
                        }elseif ($size <= 3){
                            return 'TINYINT('.$size.')'.$u.$nn; // TINYINT is an extension to the SQL
                        }else{
                            return 'INT('.$size.')'.$u.$nn;
                        }
                }
            case modelProperty::TYPE_FLOAT:
                switch ($this->_databaseType){
                    case 'sqlite':
                        return 'FLOAT'.$nn;
                    default:
                        return 'FLOAT'.$u.$nn;
                }
            case modelProperty::TYPE_DOUBLE:
                switch ($this->_databaseType){
                    case 'sqlite':
                        return 'DOUBLE'.$nn;
                    default:
                        return 'DOUBLE'.$u.$nn;
                }
            case modelProperty::TYPE_BOOLEAN:
                switch ($this->_databaseType){
                    case 'sqlite':
                        return 'INTEGER'.$nn;
                    default:
                        return 'TINYINT(1)'.$u.$nn;
                }
        }
    }
    protected function _makeConnection(){
        try{
            $this->_connection = new PDO($this->get('dsn'), $this->get('username'), $this->get('password'));
            $this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(Exception $e){
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
    public function free($result){
        unset($result);
    }
    public function internalQuery($sql){
        return $this->getConnection()->query($sql);
        ;
    }
    /**
     * Execute an SQL statement and return the number of affected rows
     * @param string $sql
     */
    public function execute($sql){
        try{
            $result = $this->getConnection()->exec($sql);
        }catch(PDOException $e){
            $result = false;
            $this->_repairCollection($e);
        }
        return $result;
    }
    /**
     *
     * @param PDOException $errorInfo
     */
    protected function _repairCollection($errorInfo){
        $errorCode = $this->getConnection()->errorCode();
        $errorInfo = $this->getConnection()->errorInfo();
        switch ($errorCode){
            case 'HY000': // General error
                switch ($errorInfo[1]){
                    case 1: // no such table (Storage allocation failure)
                        return $this->_createCollection();
                        break;
                }
                break;
        }
        return false;
    }
    /**
     * Executes an SQL statement, returning a result set
     * @param string $sql
     */
    public function query($sql){
        try{
            $result = $this->getConnection()->query($sql);
        }catch(PDOException $e){
            $result = false;
            if ($this->_autoRepair){
                //$this->disableAutoRepair();
                $this->enableServiceMode(); // don't throw exceptions
                $repaired = $this->_repairCollection($e); // CREATE/ALTER
                $this->disableServiceMode();
                if ($repaired){
                    return $this->query($sql);
                }
                //$this->enableAutoRepair();
            }else{
                if (!$this->_serviceMode){
                    throw $e;
                }
            }
        }
        return $result;
    }
    public function fetch($resultSet){
        return $resultSet->fetch();
    }
    public function fetchColumn($resultSet, $columnNumber = 0){
        if (is_object($resultSet)){
            return $resultSet->fetchColumn($columnNumber);
        }
        return false;
    }
    public function rowCount($resultSet){
        return $resultSet->rowCount();
    }
    public function quoteField($string){
        return '"'.$string.'"';
    }
    public function quote($string){
        return $this->getConnection()->quote($string);
    }
    public function lastInsertId(){
        $this->getConnection()->lastInsertId();
    }
}