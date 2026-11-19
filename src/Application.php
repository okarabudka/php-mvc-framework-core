<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore;

use Okarabudka\PhpMvcCore\Db\Database;
use Okarabudka\PhpMvcCore\Exception\HttpException;
use Throwable;

/**
 * Front controller and service container. The current instance is reachable as Application::$app.
 *
 * @phpstan-type Config array{userClass: class-string<UserModel>, db: array{dsn?: string, user?: string, password?: string}}
 */
class Application
{
    public const string EVENT_BEFORE_REQUEST = 'beforeRequest';
    public const string EVENT_AFTER_REQUEST = 'afterRequest';

    public static string $ROOT_DIR;
    public static Application $app;

    public string $layout = 'main';
    /** @var class-string<UserModel> */
    public string $userClass;
    public View $view;
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;
    public ?Controller $controller = null;
    public Database $db;
    public ?UserModel $user = null;

    /** @var array<string, list<callable(): void>> */
    protected array $eventListeners = [];

    /**
     * @param Config $config
     */
    public function __construct(string $rootPath, array $config, ?Request $request = null)
    {
        $this->userClass = $config['userClass'];

        self::$ROOT_DIR = $rootPath;
        self::$app = $this;

        $this->request = $request ?? new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->view = new View();
        $this->router = new Router($this->request, $this->response);
        $this->db = new Database($config['db']);

        $primaryValue = $this->session->get('user');
        if (is_scalar($primaryValue)) {
            $user = new $this->userClass();
            $this->user = $user->findOne([$user->primaryKey() => $primaryValue]);
        }
    }

    public static function isGuest(): bool
    {
        return self::$app->user === null;
    }

    public function run(): void
    {
        $this->triggerEvent(self::EVENT_BEFORE_REQUEST);

        try {
            echo $this->router->resolve();
        } catch (Throwable $e) {
            if ($e instanceof HttpException) {
                $this->response->setStatusCode($e->getCode());
            } else {
                $this->response->setStatusCode(500);
                error_log((string) $e);
            }
            echo $this->view->renderView('_error', [
                'exception' => $e,
            ]);
        }

        $this->triggerEvent(self::EVENT_AFTER_REQUEST);
    }

    /**
     * @param callable(): void $callback
     */
    public function on(string $eventName, callable $callback): void
    {
        $this->eventListeners[$eventName][] = $callback;
    }

    public function triggerEvent(string $eventName): void
    {
        foreach ($this->eventListeners[$eventName] ?? [] as $callback) {
            $callback();
        }
    }

    public function login(UserModel $user): bool
    {
        $this->user = $user;
        $this->session->set('user', $user->{$user->primaryKey()});

        return true;
    }

    public function logout(): void
    {
        $this->user = null;
        $this->session->unset('user');
    }

    public function getController(): ?Controller
    {
        return $this->controller;
    }

    public function setController(Controller $controller): void
    {
        $this->controller = $controller;
    }
}
