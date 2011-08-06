<?php
/*
 *@name mraiur_db
 *@version 0.1
 *@author Nikolai Ivanov : mraiur@nikolai-ivanov.com
 */

class mraiur_db
{
	protected static $DB_NAME = false;
	protected static $DB_USER = false;
	protected static $DB_PASSWORD = false;
	protected static $DB_HOST = false;
	public static $Connection = null;
	protected static $Reference;
	
	public function __construct( $paramUser, $paramPassword, $paramName, $paramHost = "localhost" )
	{
		self::$DB_NAME = $paramName;
		self::$DB_USER = $paramUser;
		self::$DB_PASSWORD = $paramPassword;
		self::$DB_HOST = $paramHost;
	}
	
	public static function getConnection()
	{
		if( self::$Connection == false)
		{
			$Connection = self::Connect();
		}
		else
		{
			$Connection = $this->getInstance()->Connection;	
		}
		//echo gettype( $Connection );
		return $Connection;
	}
	
	public static function init()
	{
		$this->getInstance()->Query("SELECT 1");
	}
	
	public static function Test()
	{
		print_r($this->getInstance()->Query("SHOW TABLES"));	
	}
	
	public static function execute( $paramString = '', $paramReturnID = false )
	{
		if( $paramString != "" )
		{
			if(self::$Connection === null)
			{
				self::Connect();
			}
			$result = mysql_query( $paramString );
			if( mysql_errno() != "" )
			{
				//echo "MySQL error: ".mysql_error()." | Number: ".mysql_errno();
				//SystemERROR::MSG( "MySQL error: ".mysql_error()." | Number: ".mysql_errno() );
				if( config::$debug == true)
				{
					echo '<div style="border:solid #000000 1px; background-color:#FFFFFF; color:#000000;>"';
					echo '<div style="font-weight:bold; font-size:10px;">MySql ERROR : '.mysql_error().'</div>';
					echo '<div style="font-weight:bold; font-size:10px;">MySql ERROR Number : '.mysql_errno().'</div>';
					echo '<div>File: '.dirname(__FILE__).'</div>';
					echo '<div><pre>'.$paramString.'</pre></div>';
					echo "</div>";
				}
				else
				{
					echo "ERROR!";
				}
				return false;
			}
			elseif( $paramReturnID == false )
			{
				return $result;
			}
			elseif( $paramReturnID == true )
			{
				$ID = mysql_insert_id();
				return $ID;
			}
		}
	}
	/**
	 *@param String Query for execution
	 *@param String Return type assoc by default OR array
	 */
	public static function Query( $paramString = "", $paramReturnType = "assoc")
	{
		if( $paramString != "" )
		{
			$result = self::execute( $paramString );
			if( gettype($result) == "boolean" )
			{
				//return SystemERROR::MSG( "MySQL failed query: ".$paramString);
				return "MySQL failed query: ".$paramString;
			}
			else
			{	
				if( $paramReturnType == "assoc" )
				{
					return mysql_fetch_assoc( $result );
				}
				elseif( $paramReturnType == "array" )
				{
					return mysql_fetch_array( $result ) ;
				}
			}
		}	
	}
	
	public static function QueryAll( $paramString = "", $paramReturnType = "assoc", $paramArrayKey = false)
	{
		if( $paramString != "")
		{
			$result = self::execute( $paramString );
			
			$ReturnArray = array();
			$cnt = 0;
			if( gettype($result) == "array")
			{
				echo "";
			}
			else
			{
				if( mysql_affected_rows() > -1)
				{
					while( $Row = ( ( $paramReturnType == "assoc")?mysql_fetch_assoc($result):mysql_fetch_array($result) ) )
					{
						if( $paramArrayKey == false)
						{
							$ReturnArray[$cnt] = $Row;
						}
						else
						{
							$ReturnArray[$Row[$paramArrayKey]] = $Row;
						}
						$cnt++;
					}
				}
				elseif( config::$debug == true)
				{
					echo '<div style="border:solid #000000 1px; background-color:#FFFFFF; color:#000000;>"';
					echo '<div style="font-weight:bold; font-size:10px;">mysql retreave error </div>';
					echo '<div>File: '.dirname(__FILE__).'</div>';
					echo '<div><pre>'.$paramString.'</pre></div>';
					echo "</div>";
				}				
			}
			return $ReturnArray;
		}
	}
	private function fetch($paramResult, $paramReturnType = "assoc")
	{
		if( mysql_affected_rows() > -1)
		{
			if( $paramReturnType == "assoc" )
			{
				return mysql_fetch_assoc( $paramResult );	
			}
			elseif( $paramReturnType == "array" )
			{
				return mysql_fetch_array( $paramResult );
			}
		}
		elseif( config::$debug == true)
		{
			echo '<div style="border:solid #000000 1px; background-color:#FFFFFF; color:#000000;>"';
			echo '<div style="font-weight:bold; font-size:10px;">mysql retreave error </div>';
			echo '<div>File: '.dirname(__FILE__).'</div>';
			echo '<div><pre>'.$paramString.'</pre></div>';
			echo "</div>";
		}
	}
	
	public function getColumns( $paramDbName )
	{
		$query = "SHOW COLUMNS FROM ".$paramDbName;
		$result = $this->execute( $query );
		return $result;
	}
	
	public function hasField($paramName, $paramDbName, $AutoAdd = true, $paramType = "")
	{
		$columns = $this->getColumns($paramDbName);
		$fields = array();
		while( $field = mysql_fetch_assoc($columns) )
		{
			$fields[$field['Field']] = 1;
		}
		$check = $paramName;
		if( gettype($paramName) == "string" )
		{
			if( isset($fields[$paramName]) )
			{
				return true;
			}
			else
			{
				if( $AutoAdd == true )
				{
					$alter = "ALTER TABLE ".$paramDbName." ADD COLUMN $paramName $paramType";
					$this->execute($alter);
				}
				return false;
			}
		}
		elseif( gettype($paramName) == "array")
		{
			$has_false = false;
			foreach($paramName as $value)
			{
				if( !isset( $fields[$value] ) )
				{
					
				}
			}
		}
		return $has_field;
	}
	
	######################## ---- SINGLETON ---- ########################
	public static function Connect()
	{
		self::$Connection = mysql_connect( self::$DB_HOST, self::$DB_USER, self::$DB_PASSWORD );
		mysql_select_db( self::$DB_NAME );
		mysql_query( "SET NAMES UTF8" );
	}
	
	public static function getInstance()
	{
		if(!is_object( self::$Reference ) )
		{
			self::$Reference = new DB();
		}
		return self::$Reference;
	}
	
	public function __set( $paramName, $paramValue)
	{
		if( $this->$paramName === false)
		{
			$this->$paramName = $paramValue;
		}
	}
	
	public function __get( $paramName )
	{
		return $this->$paramName;	
	}
}
?>