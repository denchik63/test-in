<?php
/**
 *
 */

namespace Application;

class Application
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * Application constructor.
     */
    private function __construct() {
        $this->config = include __DIR__ . '/../../config/config.php';
    }

    /**
     * @return Application
     */
    public static function init() {
        return new Application();
    }

    /**
     *
     */
    public function handleRequest() {
        $requestUri = $_SERVER['REQUEST_URI'];

        $content = '';
        $path = parse_url($requestUri, PHP_URL_PATH);

        if (isset($this->config['route'][$path])) {
            $controllerClassName = $this->config['route'][$path]['controller'];
            $controller = new $controllerClassName([
                'config' => $this->config,
            ]);
            $action = $this->config['route'][$path]['action'];
            if (method_exists($controller, $action)) {
                $content = $controller->$action();
            }
        }

        if (!$content) {
            $content = $this->notFound();
        }

        echo $content;
        die;
    }

    /**
     * @return string
     */
    private function notFound() {
        header("HTTP/1.1 404 Page not found");
        return '<h1 style="text-align: center;">404 Страница не найдена. Перейти на <a href="/">главную</a></h1>';
    }
}