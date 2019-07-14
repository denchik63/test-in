<?php
/**
 *
 */
namespace Application\Controller;

use Application\Model\AbstractModel;
use Application\Model\Token;

abstract class AbstractController
{
    /**
     * @var array
     */
    private $models;

    /**
     * @var \mysqli
     */
    private $mysqlConnection;

    /**
     * @var array
     */
    protected $params;

    /**
     * AbstractController constructor.
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->mysqlConnection) {
            mysqli_close($this->mysqlConnection);
        }
    }

    /**
     * @param string $className
     * @return AbstractModel
     */
    protected function getModel($className)
    {
        if (empty($this->models[$className])) {
            $this->models[$className] = new $className($this->getSqlConnection());
        }

        return $this->models[$className];
    }

    /**
     * @param array $result
     * @return string
     */
    protected function prepareJsonResult(array $result)
    {
        header('Content-Type: application/json; charset=utf-8');

        return json_encode($result);
    }

    /**
     * @return string
     */
    protected function checkToken() {
        $token = $_GET['token'] ?? '';

        /**
         * @var Token $tokenModel
         */
        $tokenModel = $this->getModel(Token::class);

        return $tokenModel->checkToken($token);
    }

    /**
     * @return \mysqli
     * @throws \Exception
     */
    private function getSqlConnection()
    {
        if (!$this->mysqlConnection) {
            $config = $this->params['config'];
            if (empty($config['mysql'])) {
                throw new \Exception('No sql config provided');
            }
            $this->mysqlConnection = new \mysqli(
                $config['mysql']['host'],
                $config['mysql']['user'],
                $config['mysql']['pass'],
                $config['mysql']['db']
            );
            if ($this->mysqlConnection->connect_errno) {
                throw  new \Exception('Can not connect to database');
            }
        }

        return $this->mysqlConnection;
    }
}