<?php
/**
 *
 */
namespace Application\Model;

class Order extends AbstractModel
{
    /**
     * @return string
     */
    protected function getTableName()
    {
        return 'order';
    }

    /**
     * @return array
     */
    protected function getColumns() {
        return [
            ['s' => 'status'],
            ['i' => 'amount'],
        ];
    }

    /**
     * @param array $data
     * @param int $amount
     * @return int
     */
    public function createOrder($data, $amount) {
        $this->mysqlConnection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
        $this->mysqlConnection->query("INSERT INTO `{$this->getTableName()}` (`status`, `amount`) VALUES ('Новый', {$amount})");
        $orderId = $this->mysqlConnection->insert_id;
        foreach ($data as $value) {
            $this->mysqlConnection->query("INSERT INTO `orderproducts` (`orderId`, `productId`, `price`, `quantity`) VALUES ({$orderId}, {$value['productId']}, {$value['price']}, {$value['quantity']})");
        }
        $this->mysqlConnection->commit();

        return $orderId;
    }
}