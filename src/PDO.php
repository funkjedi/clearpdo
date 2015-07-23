<?php

namespace ClearPDO;

class PDO extends \PDO {

	/**
	 * @param string
	 * @param string
	 * @param string
	 * @param array
	 */
	public function __construct($dsn, $username, $password, array $options = array())
	{
		$options += array(
			PDO::ATTR_ERRMODE                    => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_CASE                       => PDO::CASE_NATURAL,
			PDO::ATTR_DEFAULT_FETCH_MODE         => PDO::FETCH_OBJ,
			PDO::ATTR_EMULATE_PREPARES           => false,
			PDO::ATTR_STRINGIFY_FETCHES          => false,
			PDO::ATTR_ORACLE_NULLS               => PDO::NULL_NATURAL,
			PDO::ATTR_STATEMENT_CLASS            => ['\ClearPDO\PDOStatement'],
			//PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
		);

		parent::__construct($dsn, $username, $password, $options);
	}

	/**
	 * @see https://github.com/illuminate/database/blob/5.1/Connectors/MySqlConnector.php
	 *
	 * @param array
	 * @return \PDO
	 */
	static public function createMysqlConnection(array $config)
	{
		$config += array(
			'unix_socket' => '',
			'host'        => 'localhost',
			'port'        => '',
			'database'    => '',
			'username'    => '',
			'password'    => '',
			'charset'     => 'utf8',
			'collation'   => 'utf8_unicode_ci',
			'options'     => array(),
			'strict'      => false,
		);

		extract($config);


		// Build the appropriate DSN string
		if ($unix_socket) {
			$dsn = "mysql:unix_socket={$unix_socket};dbname={$database}";
		}
		elseif ($port) {
			$dsn = "mysql:host={$host};port={$port};dbname={$database}";
		}
		else {
			$dsn = "mysql:host={$host};dbname={$database}";
		}

		// We need to grab the PDO options that should be used while making the brand
		// new connection instance. The PDO options control various aspects of the
		// connection's behavior, and some might be specified by the developers.
		$connection = new self($dsn, $username, $password, $options);

		if (isset($unix_socket)) {
			$connection->exec("use `{$database}`;");
		}

		// Next we will set the "names" and "collation" on the clients connections so
		// a correct character set will be used by this client. The collation also
		// is set on the server but needs to be set here on this client objects.
		if ($charset) {
			$names = "SET NAMES '$charset'";

			if ($collation) {
				$names = "$names COLLATE '$collation'";
			}

			$connection->prepare($names)->execute();
		}

		// If the "strict" option has been configured for the connection we'll enable
		// strict mode on all of these tables. This enforces some extra rules when
		// using the MySQL database system and is a quicker way to enforce them.
		if ($strict) {
			$connection->prepare("SET SESSION sql_mode='STRICT_ALL_TABLES'")->execute();
		}

		return $connection;
	}

	/**
	 * @param string
	 * @param array
	 * @return array
	 */
	public function select($sql, array $data = array())
	{
		$statement = $this->prepare($sql);
		$statement->bindValues($data);
		$statement->execute();
		return $statement;
	}

	/**
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public function result($sql, array $data = array())
	{
		$statement = $this->select($sql, $data);
		return $statement->fetch();
	}

	/**
	 * @param string
	 * @param array
	 * @return array
	 */
	public function results($sql, array $data = array())
	{
		$statement = $this->select($sql, $data);
		return $statement->fetchAll();
	}

	/**
	 * @param string
	 * @param array
	 * @return array
	 */
	public function lists($sql, array $data = array())
	{
		$statement = $this->select($sql, $data);
		$statement->setFetchMode(PDO::FETCH_NUM);

		$results = array();
		foreach ($statement as $result) {
			if (count($result) === 1) {
				$results[] = $result[0];
			}
			else {
				$results[$result[0]] = $result[1];
			}
		}

		return $results;
	}

	/**
	 * @param string
	 * @param array
	 * @param integer
	 * @return mixed
	 */
	public function column($sql, array $data = array(), $columnNumber = 0)
	{
		$statement = $this->select($sql, $data);
		return $statement->fetchColumn($columnNumber);
	}

	/**
	 * @param string
	 * @param array
	 * @return boolean|int
	 */
	public function insert($tableName, array $data)
	{
		$statement = $this->prepare("INSERT INTO `$tableName` " . $this->getInsertUpdateSetSQL($data));
		$statement->bindValues($data);

		return $statement->execute();
	}

	/**
	 * @param string
	 * @param array
	 * @param array
	 * @return boolean
	 */
	public function update($tableName, array $data, $conditions = '1=1', array $where = array())
	{
		$statement = $this->prepare("UPDATE `$tableName` " . $this->getInsertUpdateSetSQL($data) . " WHERE $conditions");
		$statement->bindValues($data);
		$statement->bindValues($where);

		return $statement->execute();
	}

	/**
	 * @param array
	 * @return string
	 */
	protected function getInsertUpdateSetSQL(array $data)
	{
		$useQuestionMarkPlaceholders = false;

		foreach (array_keys($data) as $columnName) {
			if (is_numeric($columnName)) {
				$useQuestionMarkPlaceholders = true;
				break;
			}
		}

		$placeholders = [];
		foreach ($data as $columnName => $columnValue) {
			if ($useQuestionMarkPlaceholders) {
				$placeholders[] = "`$columnName` = ?";
			}
			else {
				$placeholders[] = "`$columnName` = :$columnName";
			}
		}

		return "`$tableName` SET " . implode(', ', $placeholders);
	}

}
