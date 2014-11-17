<?php

namespace Codeception\Module;

use Codeception\Module;
use Codeception\Exception\Module as ModuleException;

class PropelModule extends Module
{
    /**
     * @var \Pdo
     */
    private $connection;

    public function _initialize()
    {
        $this->connection = \Propel::getConnection();
    }

    /**
     * @param array $settings
     */
    public function _beforeSuite($settings = array())
    {
        $this->connection->beginTransaction();
    }

    public function _afterSuite()
    {
        $this->connection->rollBack();
    }

    /**
     * @param string $table
     * @param array|string $selectColumns
     * @param array $criteria
     * @throws ModuleException
     */
    public function seeInDatabase($table, $selectColumns, array $criteria)
    {
        $criteriaColumns = array_keys($criteria);
        $criteriaConditions = array_values($criteria);

        $where = '';

        for ($i = 0; $i < count($criteriaColumns); $i++) {
            if ($i > 1) {
                $where .= ' AND ';
            }

            $where .= $criteriaColumns[$i] . ' = :' . $i;
        }

        $sql = sprintf(
            'SELECT %s FROM %s WHERE %s',
            is_array($selectColumns) ? implode(', ', $selectColumns) : $selectColumns,
            $table,
            $where
        );
        $query = $this->connection->query($sql);
        $this->debugSection('Query: ', $sql);
        $this->debugSection('Values; ', implode(', ', $criteriaConditions));

        for ($i = 0; $i < count($criteriaConditions); $i++) {
            $query->bindValue(
                (string) $i,
                $criteriaConditions[$i],
                $this->guessParamType($criteriaConditions[$i])
            );
        }
    }

    /**
     * @param mixed $param
     * @return int
     * @throws ModuleException
     */
    protected function guessParamType($param)
    {
        if (!is_scalar($param)) {
            throw new ModuleException(
                __CLASS__,
                'Only scalar parameters are allowed for propel conditions'
            );
        }

        switch ($param) {
            case is_int($param):
                $type = \PDO::PARAM_INT;
                break;
            case is_bool($param):
                $type = \PDO::PARAM_BOOL;
                break;
            case is_float($param):
                $type = \PDO::PARAM_STR;
                break;
            default:
                $type = \PDO::PARAM_STR;
                break;
        }

        return $type;
    }
}
