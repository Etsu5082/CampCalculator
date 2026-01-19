<?php
/**
 * シンプルなルータークラス
 */
class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, string $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, string $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, string $handler): void
    {
        // パスパラメータを正規表現に変換 {id} -> (\d+)
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>\d+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // nginx対応: クエリパラメータからルートを取得、なければREQUEST_URIから
        if (isset($_GET['route'])) {
            $uri = '/' . ltrim($_GET['route'], '/');
        } else {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            // index.phpを除去
            $uri = preg_replace('#/index\.php#', '', $uri);
            if ($uri === '' || $uri === false) {
                $uri = '/';
            }
        }

        // PUT/DELETEのオーバーライド
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // JSONリクエストの場合
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (isset($input['_method'])) {
                $method = strtoupper($input['_method']);
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // パスパラメータを抽出
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // ハンドラー実行
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // マッチしない場合は404
        $this->notFound();
    }

    private function executeHandler(string $handler, array $params): void
    {
        [$controllerName, $method] = explode('@', $handler);

        if (!class_exists($controllerName)) {
            $this->notFound();
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            $this->notFound();
            return;
        }

        $controller->$method($params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        if ($this->isApiRequest()) {
            Response::json(['success' => false, 'error' => 'Not Found'], 404);
        } else {
            include VIEWS_PATH . '/errors/404.php';
        }
    }

    private function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'];
        return strpos($uri, '/api/') === 0;
    }
}
