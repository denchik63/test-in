<?php
/**
 *
 */

namespace Application\Model;

abstract class AbstractModel
{
    /**
     * @var \mysqli
     */
    protected $mysqlConnection;

    /**
     * @param \mysqli $mysqlConnection
     */
    public function __construct($mysqlConnection)
    {
        $this->mysqlConnection = $mysqlConnection;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param array $sort
     * @return array
     */
    public function fetch($limit = null, $offset = null, $sort = [])
    {
        $query = "SELECT * FROM `{$this->getTableName()}`";
        foreach ($sort as $fieldName => $order) {
            $query .= " ORDER BY {$fieldName} {$order}";
            break;
        }
        if ($limit !== null) {
            $query .= " LIMIT {$limit}";
        }
        if ($offset !== null) {
            $query .= " OFFSET {$offset}";
        }

        $queryResult = $this->mysqlConnection->query($query);

        $result = [];
        if ($queryResult) {
            $result = $queryResult->fetch_all(MYSQLI_ASSOC);
        }

        return $result;
    }

    /**
     * @return int
     */
    public function count() {
        $queryResult = $this->mysqlConnection->query("SELECT count(id) as count FROM `{$this->getTableName()}`");

        $result = 0;
        if ($queryResult) {
            $queryResult = $queryResult->fetch_assoc();
            if (isset($queryResult['count'])) {
                $result = (int)$queryResult['count'];
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function insert(array $data) {
        $values = implode(', ', array_fill(0, count($data), '?'));
        $separatedColumnNames = implode(', ', $this->getColumnNamesByData($data));
        $query = "INSERT INTO `{$this->getTableName()}` ({$separatedColumnNames}) VALUES ({$values})";
        $stmt = $this->mysqlConnection->prepare($query);
        if (!$stmt) {
            throw new \Exception($this->mysqlConnection->error);
        }
        $this->bindParams($stmt, $data);

        $stmt->execute();
        $this->mysqlConnection->begin_transaction();
        return $stmt->affected_rows;
    }

    /**
     * @param array $data
     * @return int
     */
    public function update($data) {
        $columnNames = $this->getColumnNamesByData($data);
        foreach ($columnNames as &$columnName) {
            $columnName = ($columnName . ' = ?');
        }
        $columnNamesAsString = implode(', ', $columnNames);
        $query = "UPDATE `{$this->getTableName()}` SET {$columnNamesAsString} WHERE id={$data['id']}";
        $stmt = $this->mysqlConnection->prepare($query);
        $this->bindParams($stmt, $data);

        $stmt->execute();

        return $stmt->affected_rows;
    }

    /**
     * @param int $id
     * @return array
     */
    public function find($id) {
        $result = [];

        $queryResult = $this->mysqlConnection->query("SELECT * FROM `{$this->getTableName()}` WHERE id={$id}");
        if ($queryResult) {
            $result = $queryResult->fetch_assoc();
        }

        return $result;
    }

    /**
     * @return string
     */
    abstract protected function getTableName();

    /**
     * @return array
     */
    abstract protected function getColumns();

    /**
     * @param \mysqli_stmt $stmt
     * @param array $data
     */
    private function bindParams(\mysqli_stmt $stmt, $data) {
        $columnValues = [];
        $typesString = '';
        foreach ($this->getColumns() as $column) {
            foreach ($column as $type => $columnName) {
                if ($columnName == 'id') {
                    continue;
                }

                if (isset($data[$columnName])) {
                    $typesString .= $type;
                    $columnValues[] = &$data[$columnName];
                }
            }
        }

        $params = [];
        $params[] = $typesString;
        $params = array_merge($params, $columnValues);

        call_user_func_array(array($stmt, 'bind_param'), $params);
    }

    /**
     * @param array $data
     * @return array
     */
    private function getColumnNamesByData($data) {
        $result = [];

        foreach ($this->getColumns() as $column) {
            foreach ($column as $type => $columnName) {
                if (isset($data[$columnName])) {
                    $result[] = ('`' . $columnName . '`');
                }
            }
        }

        return $result;
    }
}