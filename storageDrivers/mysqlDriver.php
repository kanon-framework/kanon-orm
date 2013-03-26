<?php

#require_once dirname(__FILE__).'/../storageDriver.php';

class mysqlDriver extends storageDriver{
    public function getDataTypeSql($type, $size, $unsigned, $notNull){
        $nn = $notNull?' NOT NULL':'';
        $u = $unsigned?' UNSIGNED':'';
        switch ($type){
            case modelProperty::TYPE_TEXT:
                return 'TEXT'.$nn; //('.$size.')
                break;
            case modelProperty::TYPE_VARCHAR:
                return 'VARCHAR('.$size.')'.$nn;
                break;
            case modelProperty::TYPE_INTEGER:
                if ($size > 10){
                    return 'BIGINT('.$size.')'.$u.$nn; // BIGINT is an extension to the SQL
                }elseif ($size <= 3){
                    return 'TINYINT('.$size.')'.$u.$nn; // TINYINT is an extension to the SQL
                }else{
                    return 'INT('.$size.')'.$u.$nn;
                }
                break;
            case modelProperty::TYPE_FLOAT:
                return 'FLOAT'.$u.$nn;
                break;
            case modelProperty::TYPE_DOUBLE:
                return 'DOUBLE'.$u.$nn;
                break;
            case modelProperty::TYPE_BOOLEAN:
                return 'TINYINT(1)'.$u.$nn;
                break;
        }
    }
    protected function _repairCollection($errorInfo){
        var_dump($errorInfo);
    }
    public function quoteFieldName($fieldName){
        return '`'.$fieldName.'`';
    }
    public function free($result){
        return mysql_free_result($result);
    }
    public function internalQuery($sql){
        if (isset($_COOKIE['debug'])){
            echo $sql."<br />";
        }
        $time = microtime(true);

        $result = mysql_query($sql, $this->getConnection());

        profiler::getInstance()->addSql($sql, $time);
        return $result;
    }
    public function quoteField($string){
        return '`'.$string.'`';
    }
    protected function _makeConnection(){
        if ($host = $this->get('host')){
            if (!$port = $this->get('port')){
                $port = 3307;
            }
            $host = $host.':'.$port;
        }else{
            if ($this->get('unix_socket')){
                $host = ':'.$this->get('unix_socket');
            }
        }
        if ($host){
            try{
                $this->_connection = mysql_connect($host, $this->get('username'), $this->get('password'), true);
            }catch(Exception $e){
                throw new Exception($e->getMessage().' ('.$host.')', $e->getCode());
            }
        }
        if ($dbname = $this->get('dbname')){
            mysql_select_db($dbname, $this->_connection);
        }
        if ($charset = $this->get('charset')){
            $this->internalQuery("SET NAMES ".$charset);
        }
    }
    /**
     * Execute an SQL statement and return the number of affected rows
     * @param string $sql
     */
    public function execute($sql){
        if (isset($_COOKIE['debug'])){
            echo $sql;
        }
        $result = $this->query($sql);
        return mysql_affected_rows($this->getConnection());
    }
    /**
     * Executes an SQL statement, returning a result set
     * @param string $sql
     */
    public function query($sql){
        $result = $this->internalQuery($sql);
        if (!$result){
            /* foreach (debug_backtrace() as $d){
              var_dump($d['file'].':'.$d['line']);
              } */
            $errorNumber = mysql_errno($this->getConnection());
            // Error: 1146 SQLSTATE: 42S02 (ER_NO_SUCH_TABLE)
            // Message: Table '%s.%s' doesn't exist
            if ($errorNumber == 1146){
                //mysql_query("SET sql_mode='ANSI'", $this->getConnection());
                //echo ' _createCollection() ';
                $this->_createCollection();
                //$result = mysql_query($sql, $this->getConnection());
            }
            if ($errorNumber == 1054){
                // Mysql Error #1054 - Unknown column
                $this->_updateCollection();
            }
            //$errorNumber = mysql_errno($this->getConnection());
            if (!$result){
                throw new Exception(
                        'Mysql Error #'.$errorNumber.
                        ' - '.mysql_error($this->getConnection()).
                        ' SQL:'.htmlspecialchars($sql)
                );
            }
        }
        return $result;
    }
    public function fetch($resultSet){
        return mysql_fetch_assoc($resultSet);
    }
    public function fetchColumn($resultSet, $columnNumber = 0){
        return mysql_result($resultSet, 0, $columnNumber);
    }
    public function rowCount($resultSet){
        return mysql_num_rows($resultSet);
    }
    public function quote($string){
        return "'".mysql_real_escape_string($string, $this->getConnection())."'";
    }
    public function lastInsertId(){
        $id = mysql_insert_id($this->getConnection());
        if (isset($_COOKIE['debug'])){
            echo ' mysql_insert_id '.$id.' ';
        }
        return $id;
    }
}
