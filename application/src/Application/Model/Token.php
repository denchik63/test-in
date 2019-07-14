<?php
/**
 *
 */
namespace Application\Model;

class Token extends AbstractModel
{
    /**
     * @return string
     */
    public function getToken() {
        $token = $this->generateToken();

        $expired = new \DateTime();
        $expired->modify('+3 month');

        $this->insert([
            'token' => $token,
            'expired' => $expired->format('Y-m-d'),
        ]);

        return $token;
    }

    /**
     * @param string $token
     * @return string
     */
    public function checkToken($token) {
        $result = false;
        $foundedToken = $this->findByToken($token);

        if (!empty($foundedToken) && !empty($foundedToken['token']) && $foundedToken['token'] === $token && !empty($foundedToken['expired'])) {
            $currentDate = new \DateTime();
            $expired = \DateTime::createFromFormat('Y-m-d', $foundedToken['expired']);
            if ($expired && $expired->getTimestamp() > $currentDate->getTimestamp()) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @param string $token
     * @return array
     */
    private function findByToken($token) {
        $result = [];

        $queryResult = $this->mysqlConnection->query("SELECT * FROM `{$this->getTableName()}` WHERE token='{$token}'");
        if ($queryResult) {
            $result = $queryResult->fetch_assoc();
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getTableName()
    {
        return 'token';
    }

    /**
     * @return array
     */
    protected function getColumns() {
        return [
            ['s' => 'token'],
            ['s' => 'expired'],
        ];
    }

    /**
     * @return string
     */
    private function generateToken() {
        $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $hashSize = 64;
        $size = strlen($chars) - 1;
        $token = null;

        while ($hashSize--) {
            $token .= $chars[rand(0, $size)];
        }

        return $token;
    }
}