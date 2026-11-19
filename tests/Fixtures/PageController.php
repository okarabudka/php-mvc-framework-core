<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests\Fixtures;

use Okarabudka\PhpMvcCore\Controller;
use Okarabudka\PhpMvcCore\Middlewares\AuthMiddleware;
use Okarabudka\PhpMvcCore\Request;
use Okarabudka\PhpMvcCore\Response;

final class PageController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['secret']));
    }

    public function home(): string
    {
        $this->setLayout('auth');

        return $this->render('home', ['name' => 'controller']);
    }

    public function secret(): string
    {
        return 'secret';
    }

    public function save(Request $request, Response $response): void
    {
        $response->redirect('/saved?' . http_build_query($request->getBody()));
    }
}
