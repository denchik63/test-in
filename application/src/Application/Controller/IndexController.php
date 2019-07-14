<?php
/**
 *
 */
namespace Application\Controller;

use Application\Model\Order;
use Application\Model\Product;
use Application\Model\Token;

class IndexController extends AbstractController
{
    /**
     * @return string
     */
    public function auth()
    {
        $success = false;
        $token = null;
        $message = 'Ошибка запроса';

        if (!empty($_POST['login']) && !empty($_POST['pass'])) {
            $login = $_POST['login'];
            $pass = $_POST['pass'];
            $config = $this->params['config'];
            if ($config['auth']['login'] == $login && $config['auth']['pass'] == $pass) {

                try {
                    /**
                     * @var Token $tokenModel
                     */
                    $tokenModel = $this->getModel(Token::class);
                    $token = $tokenModel->getToken();
                    $message = '';
                    $success = true;
                } catch (\Exception $ex) {
                    $message = $ex->getMessage() . ' ' . $ex->getTraceAsString();
                }
            } else {
                $message = 'Ошибка авторизации';
            }
        }

        return $this->prepareJsonResult([
            'success' => $success,
            'token' => $token,
            'message' => $message,
        ]);
    }

    /**
     * @return string
     */
    public function generateProducts()
    {
        $success = false;
        $message = 'Ошибка аутентификации';

        if ($this->checkToken()) {
            try {
                /**
                 * @var Product $productModel
                 */
                $productModel = $this->getModel(Product::class);
                $productCount = $productModel->count();
                if ($productCount >= 20) {
                    $message = 'Товары уже сгенерированы';
                } else {
                    $productModel->generate();
                    $success = true;
                    $message = 'Товары успешно сгенерированы';
                }
            } catch (\Exception $ex) {
                $message = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }
        }

        return $this->prepareJsonResult([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * @return string
     */
    public function getAllProducts()
    {
        $success = false;
        $message = 'Ошибка аутентификации';
        $products = [];

        if ($this->checkToken()) {
            try {

                /**
                 * @var Product $productModel
                 */
                $productModel = $this->getModel(Product::class);
                $products = $productModel->fetch();
                $success = true;
                $message = '';
            } catch (\Exception $ex) {
                $message = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }
        }

        return $this->prepareJsonResult([
            'success' => $success,
            'result' => [
                'products' => $products,
            ],
            'message' => $message,
        ]);
    }

    /**
     * @return string
     */
    public function createOrder()
    {
        $orderId = null;
        $success = false;
        $message = 'Ошибка аутентификации';

        if ($this->checkToken()) {
            try {

                /**
                 * @var Order $orderModel
                 */
                $orderModel = $this->getModel(Order::class);
                $data = explode(',', $_GET['data']) ?? [];
                if (empty($data)) {
                    throw new \Exception('Ошибка запроса, данные не найдены');
                }

                $productIds = [];
                foreach ($data as &$value) {
                    list($productId, $quantity) = explode(':', $value);
                    $value = [
                        'productId' => (int)$productId,
                        'quantity' => (int)$quantity,
                    ];
                    $productIds[] = $productId;
                }

                /**
                 * @var Product $productModel
                 */
                $productModel = $this->getModel(Product::class);
                $products = $productModel->findProductsByIds($productIds);
                foreach ($data as &$value) {
                    foreach ($products as $product) {
                        if ($product['id'] == $value['productId']) {
                            $value['price'] = $product['price'];
                        }
                    }
                }

                $amount = 0;
                foreach ($data as $value) {
                    $amount += ($value['quantity'] * $value['price']);
                }

                $orderId = $orderModel->createOrder($data, $amount);
                $success = true;
                $message = '';
            } catch (\Exception $ex) {
                $message = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }
        }

        return $this->prepareJsonResult([
            'success' => $success,
            'result' => [
                'orderId' => $orderId,
            ],
            'message' => $message,
        ]);
    }

    /**
     * @return string
     */
    public function payOrder()
    {
        $orderId = null;
        $success = false;
        $message = 'Ошибка аутентификации';

        if ($this->checkToken()) {
            try {
                $orderId = (int)$_GET['orderId'] ?? 0;
                $amount = (int)$_GET['amount'] ?? 0;

                /**
                 * @var Order $orderModel
                 */
                $orderModel = $this->getModel(Order::class);
                $order = $orderModel->find($orderId);
                if ($order) {
                    if (isset($order['amount']) && $order['amount'] == $amount) {
                        if (isset($order['status']) && $order['status'] == 'Оплачено') {
                            $message = "Заказ уже оплачен";
                        } else {
                            $curl = curl_init('https://ya.ru');
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                            curl_exec($curl);

                            if (curl_errno($curl) || curl_getinfo($curl, CURLINFO_RESPONSE_CODE) !== 200) {
                                $message = "Ошибка запроса оплаты " . curl_error($curl);
                            } else {
                                $orderModel->update([
                                    'id' => $orderId,
                                    'status' => 'Оплачено',
                                ]);
                                $success = true;
                                $message = '';
                            }
                        }
                    } else {
                        $message = "Неверная сумма заказа";
                    }
                } else {
                    $message = "Заказ с id={$orderId} не найден";
                }
            } catch (\Exception $ex) {
                $message = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }
        }

        return $this->prepareJsonResult([
            'success' => $success,
            'message' => $message,
        ]);
    }
}