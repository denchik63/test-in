<?php
/**
 *
 */
namespace Application\Model;

class Product extends AbstractModel
{
    /**
     * @return string
     */
    protected function getTableName()
    {
        return 'product';
    }

    /**
     * @return array
     */
    protected function getColumns() {
        return [
            ['s' => 'name'],
            ['d' => 'price'],
        ];
    }

    /**
     *
     */
    public function generate() {
        for ($i = 0; $i < 20; ++$i) {
            $name = 'Товар '. ($i + 1);
            $price = rand(100, 10000);
            $this->insert([
                'name' => $name,
                'price' => $price,
            ]);
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    public function findProductsByIds(array $ids) {
        $result = [];

        $idsAsString = implode(',', $ids);
        $queryResult = $this->mysqlConnection->query("SELECT * FROM `{$this->getTableName()}` WHERE id IN ({$idsAsString})");
        if ($queryResult) {
            $result = $queryResult->fetch_all(MYSQLI_ASSOC);
        }

        return $result;
    }
}