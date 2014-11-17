<?php

namespace Codeception\Module;

use Codeception\Module;

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
}
