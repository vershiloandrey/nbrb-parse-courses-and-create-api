<?

class DB
{
	private static $pdo;
	private $debug;
	private $count;

	private static $pdo_class;

	public function __construct($debug = false)
	{
		$this->connect_db();
		$this->debug = $debug;
		$this->count = 0;
		self::$pdo_class = $this;
	}


	private function connect_db()
	{
		$host = DB_HOST;
		$username = DB_USER;
		$password = DB_PASS;
		$dbname = DB_NAME;

	    $dsn = "mysql:host=$host;dbname=$dbname";
	    $opt = [
	        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	        PDO::ATTR_EMULATE_PREPARES   => false,
	        PDO::MYSQL_ATTR_LOCAL_INFILE => true,
	        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
	    ];
	  
	    self::$pdo = new PDO($dsn, $username, $password, $opt);

	}


	/*Получить массив*/
	public function fetchAll($query)
	{
		try
		{
			if(self::$pdo != null)
			{
	
				$res = self::$pdo->query($query)->fetchAll();

				if(count($res) == 1)
					return $res[0];
				else
					return $res;
			}
			else
				return false;
		}
		catch (Exception $e)
		{
			return $this->returnResult($query);
		}
	}

	
	/*Получить обьект*/
	public function query($query)
	{
		try
		{
			if(self::$pdo != null) {

				$res = self::$pdo->query($query);

				return $res;
			} else
				return false;
		}
		catch (Exception $e)
		{
			return $this->returnResult($query);
		}
	}

	/*Выполнить запрос и вернуть количество затронутых строк*/
	public function exec($query)
	{
		try
		{
			if(self::$pdo != null) {

				$this->stmt = $res = self::$pdo->exec($query);

				return $res;
			} else
				return null;
		}
		catch (Exception $e)
		{
			return $this->returnResult($query);
		}
	}

	public static function getPDO()
	{
		return self::$pdo_class;
	}

	private function returnResult($query)
	{
	    if($this->debug) {
	        $bt = debug_backtrace();
	        $this->dump($bt);
		}
		$arr = self::$pdo->errorInfo();
		die($arr[2] .': '. $query);
	}

}
?>