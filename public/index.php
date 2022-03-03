<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

session_start();

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);


$app->post('/users', function ($request, $response) {
    $user = $request->getParsedBodyParam('user');
    $user['id'] = time();
    $users = json_decode(file_get_contents('users.json'), true);
    $users[] = $user;
    file_put_contents('users.json', json_encode($users));
    return $response->withRedirect('/users', 302);
});

$app->get('/users', function ($request, $response) {
    $messages = $this->get('flash')->getMessages();
    $users = json_decode(file_get_contents('users.json'), true);
    $params = [
        'users' => $users,
        'flash' => $messages
    ];
    return $this->get('renderer')->render($response, "users/index.phtml", $params);
})->setName('users');

$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => ['nickname' => '', 'email' => '', 'id' => '']
    ];
    $this->get('flash')->addMessage('success', 'User create');
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
})->setName('newUsers');;

$app->get('/users/{id}', function ($request, $response, $args) {
    $users = json_decode(file_get_contents('users.json'), true);
    foreach ($users as $user) {
        if ((string)($user['id']) === $args['id']) {
            $params = [
                'users' => $users
            ];
            return $this->get('renderer')->render($response, 'users/show.phtml', $params);
        }
    }

    return $response->withStatus(404);

});
// Получаем роутер – объект отвечающий за хранение и обработку маршрутов
$router = $app->getRouteCollector()->getRouteParser();


//$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];
//$app->get('/users', function ($request, $response) use ($users) {
//    $term = $request->getQueryParam('term');
//    $filteredUsers = array_filter($users, fn($user) => str_contains($user, $term));
//    $params = [
//        'users' => $filteredUsers
//    ];
//    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
//});

//$app->get('/users/{id}', function ($request, $response, $args) {
//    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];
//    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
//    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
//    // $this в Slim это контейнер зависимостей
//    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
//});


$app->run();