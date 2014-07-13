<?php

require_once("config.php");

class Database {

    public $connection;

	function __construct(){
		$this->DbConnect();
	}
	
	function DbConnect(){
		$hostName = DB_HOSTNAME;
		$databaseName = DB_DATABASE;
		$username = DB_USERNAME;
		$password = DB_PASSWORD;
		
		$this->connection = new mysqli($hostName, $username, $password, $databaseName);
		$this->connection->set_charset("utf8");
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		} 
	}

    function DBEscapeString($str){
        return $this->connection->real_escape_string($str);
    }

	
	function DbGetOne($csql) {
		if (!isset($this->connection)) {
            $this->DbConnect();
        }
		if ($r1 = $this->connection->query($csql)){
            $res = 0;
			$row = mysqli_fetch_assoc($r1);
			if (count($row) > 0){
				foreach($row as $k => $v){
					$res = $v;
				}
			}
			return $res;
		} else {
			printf("<p>Error retrieving stored procedure result set:%d (%s) %s\n",   mysqli_errno($this->connection),mysqli_sqlstate($this->connection),mysqli_error($this->connection));
			$this->connection->close();
			exit();	
		}
	}
	
	function DbGetFirstElem($csql) {
		if (!isset($this->connection)) {
            $this->DbConnect();
        }
		if ($r1 = $this->connection->query($csql)){
            $res = NULL;
            $row = mysqli_fetch_row($r1);
			if (count($row) > 0){
				$res = $row[0];
			}
			return $res;
		} else {
			printf("<p>Error retrieving stored procedure result set:%d (%s) %s\n",   mysqli_errno($this->connection),mysqli_sqlstate($this->connection),mysqli_error($this->connection));
			$this->connection->close();
			exit();	
		}
	}

    function DbGetAll($csql) {
        $res = Array();
        if (!isset($this->connection)) {
            $this->DbConnect();
        }
        if ($r1 = $this->connection->query($csql)){
            $i = 0;
            while ($row = $r1->fetch_row()) {
                $res[$i++] = $row[0];
            }
        }
        else {
            $backtracel = "";
            foreach(debug_backtrace() as $k=>$v){
                if($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require"){
                    $backtracel .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]<br />";
                }else{
                    $backtracel .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]<br />";
                }
            }
            printf("<p>Error retrieving stored procedure result set:%d (%s) %s\n\n$backtracel",   mysqli_errno($this->connection),mysqli_sqlstate($this->connection),mysqli_error($this->connection));
            $this->connection->close();
            exit();
        }
        return $res;
    }


    /*
    function DbGetAll($csql) {
        $res = Array();
        if (!isset($this->connection)) {
            $this->DbConnect();
        }
        if ($r1 = $this->connection->query($csql)){
            $i = 0;
            while ($row = $r1->fetch_object()) {
                $res[$i] = Array();
                foreach ($row as $k => $v) {
                    $res[$i][$k] = $v;
                }
                $i++;
            }
        } else {
            $backtracel = "";
            foreach(debug_backtrace() as $k=>$v){
                if($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require"){
                    $backtracel .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]<br />";
                }else{
                    $backtracel .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]<br />";
                }
            }
            printf("<p>Error retrieving stored procedure result set:%d (%s) %s\n\n$backtracel",   mysqli_errno($this->connection),mysqli_sqlstate($this->connection),mysqli_error($this->connection));
            $this->connection->close();
            exit();
        }
        return $res;
    }
    */
	function DbGetRow($csql) {

		if (!isset($this->connection)) {
            $this->DbConnect();
        }

		if ($r1 = $this->connection->query($csql)){
			$res = mysqli_fetch_assoc($r1);
			return $res;
		} else {
			$backtracel = "";
			foreach(debug_backtrace() as $k=>$v){
				if($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require"){
					$backtracel .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]<br />";
				}else{
					$backtracel .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]<br />";
				}
			}
			printf("<p>Error retrieving stored procedure result set:%d (%s) %s<br><br>$backtracel",   mysqli_errno($this->connection),mysqli_sqlstate($this->connection),mysqli_error($this->connection));
			
			$this->connection->close();
			exit();	
		}
	}
	
	function DbQuery($csql) {
		if (!isset($this->connection)) {
            $this->DbConnect();
        }
        $query_result = $this->connection->query($csql);
        if ($query_result) {
            return $query_result;
        }
        else 	{
            printf("<p>Error performing Query:%d (%s) %s\n",   mysqli_errno($this->connection),mysqli_sqlstate($this->connection),mysqli_error($this->connection));
            $this->connection->close();
            exit();
        }

	}
	
	function DbCall($csql) {
		$res = Array();
		if (!isset($this->connection)) $this->DbConnect();
		if ($r1 = $this->connection->query($csql)){
			$resultMulti[] = mysqli_fetch_assoc($r1);
			do {
				
				if (($result = $this->connection->store_result()) == true)
				{
					$resultMulti[] = mysqli_fetch_assoc($result);
					$errnoMulti[] = $this->connection->errno;
					
					if(is_object($result)) {
						$result->free_result();
					}
				}
			} while($this->connection->next_result());
			
			$res = $resultMulti;
			return $res;
		} else {
			printf("<p>Error retrieving stored procedure result set:%d (%s) %s\n",   mysqli_errno($this->connection),mysqli_sqlstate($this->connection),mysqli_error($this->connection));
			$this->connection->close();
			exit();	
		}	
	}
	
	function DbCallAll($csql) {
		$res = Array();
		if (!isset($this->connection)) $this->DbConnect();
		if ($r1 = $this->connection->query($csql)){
			$i = 0;
			while ($row = $r1->fetch_object()) {
				$res[$i] = Array();
				foreach ($row as $k => $v) {
					$res[$i][$k] = $v;
				}
				$i++;
			}
		} else {
			$backtracel = "";
			foreach(debug_backtrace() as $k=>$v){
				if($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require"){
					$backtracel .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]<br />";
				}else{
					$backtracel .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]<br />";
				}
			}
			printf("<p>Error retrieving stored procedure result set:%d (%s) %s\n\n$backtracel",   mysqli_errno($this->connection),mysqli_sqlstate($this->connection),mysqli_error($this->connection));
			$this->DbDisconnect();
			exit();	
		}
		mysqli_free_result($r1);
		$this->DbDisconnect();
		return $res;
	}
	
	function DbCallRow($csql) {
		$res = Array();
		if (!isset($this->connection)) $this->DbConnect();
		if ($r1 = $this->connection->query($csql)){
			$res = mysqli_fetch_assoc($r1);
			$this->DbDisconnect();
			return $res;
		} else {
			printf("<p>Error retrieving stored procedure result set:%d (%s) %s\n",   mysqli_errno($this->connection),mysqli_sqlstate($this->connection),mysqli_error($this->connection));
			$this->connection->close();
			exit();	
		}	
	}
	
	function DbDisconnect() {
		if(isset($this->connection)){
			mysqli_close($this->connection);
		}	
	}

}

?>