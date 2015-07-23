<?php

namespace ClearPDO;

class PDOStatement extends \PDOStatement {

	/**
	 * @param mixed
	 * @return integer
	 */
	protected function getPDOType($value)
	{
		switch (gettype($value)) {
			case 'boolean':
				return PDO::PARAM_BOOL;
			case 'integer':
				return PDO::PARAM_INT;
			case 'resource':
				return PDO::PARAM_LOB;
			case 'NULL':
				return PDO::PARAM_NULL;
			case 'double':
			case 'array':
			case 'object':
			case 'string':
				return PDO::PARAM_STR;
		}

		return PDO::PARAM_STR;
	}

	/**
	 * @param \PDOStatement
	 * @param array
	 */
	public function bindValues(array $data)
	{
		$useQuestionMarkPlaceholders = false;

		foreach (array_keys($data) as $columnName) {
			if (is_numeric($columnName)) {
				$useQuestionMarkPlaceholders = true;
				break;
			}
		}

		foreach ($data as $columnName => $columnValue) {
			if (!$useQuestionMarkPlaceholders && strpos($columnName, ':') !== 0) {
				$columnName = ":$columnName";
			}

			if ($columnValue instanceof DateTime) {
				$columnValue = $columnValue->format('Y-m-d H:i:s');
			}

			$this->bindValue($columnName, $columnValue, $this->getPDOType($columnValue));
		}
	}

}
