<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$courses = [
    ['id'=> 1, 'name' => 'JS'],
    ['id'=> 2, 'name' => 'PHP']
];

$users = [
    'mike',
    'mishel',
    'adel',
    'keks',
    'kamila'
];

$content = file_get_contents('/Users/kulikovroman/code-study/php-hexlet/hexlet-slim-example/users.json', true);
$usersInFile = json_decode($content, true);

$app->get('/users/{id}', function ($request, $response, $args) use ($usersInFile) {
    var_dump($usersInFile);
    if ($usersInFile['name'] !== $args['id']) {
        return $this->get('renderer')->render($response->withStatus(404), 'users/404.html');
//        return $response->withStatus(404);
    }
    $params = ['id' => $usersInFile['name'], 'nickname' => 'user-' . $usersInFile['name']];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->get('/users1/new', function ($request, $response) {
    $params = [
        'user' => ['name' => '', 'email' => '', 'password' => '', 'passwordConfirmation' => '', 'city' => ''],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
});

$app->post('/users1/new', function ($request, $response) {
    //$validator = new Validator();
    $user = $request->getParsedBodyParam('user');
    file_put_contents('./users.json', json_encode($user));
    /*$errors = $validator->validate($user);
    if (count($errors) === 0) {
        //$repo->save($user);
        return $response->withRedirect('/users', 302);
    }*/
    $params = [
        'user' => $user,
        //'errors' => $errors
    ];
    return $response->withStatus(302)->withHeader('Location', '/users');
    //return $this->get('renderer')->render($response, "users/new.phtml", $params);
});



$app->get('/users', function ($request, $response) use ($users) {
    $term = $request->getQueryParam('term');
    $filteredUsers = array_filter($users, fn($user) => str_contains($user, $term));
    $params = ['users' => $filteredUsers];
    return $this->get('renderer')->render($response, 'courses/index.phtml', $params);
});

/*
$app->get('/courses', function ($request, $response) use ($courses) {
    $params = [
        'courses' => $courses
    ];
    return $this->get('renderer')->render($response, 'courses/index.phtml', $params);
});
*/

$app->get('/', function ($request, $response) {
    $response2 = $response->withStatus(204);
    return $response2;
});

$app->get('/companies', function ($request, $response) {
    return $response->write('GET /companies');
});

$app->post('/companies', function ($request, $response) {
    return $response->write('POST /companies');
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    return $response->write("Course id: {$id}");
});

$app->run();
