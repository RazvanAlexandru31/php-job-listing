<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;
use Framework\Session;

class UserController
{
    protected $db;

    public function __construct()
    {
        $config = require basePath('config/db.php');
        $this->db = new Database($config);
    }

    public function login()
    {
        loadView('user/login');
    }

    public function create()
    {
        loadView('user/create');
    }

    public function store()
    {
        // inspectAndDie('store');

        $name = $_POST['name'];
        $email = $_POST['email'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $password = $_POST['password'];
        $password_confirmation = $_POST['password_confirmation'];

        $errors = [];
        if (!Validation::email($email)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (!Validation::string($name, 3, 20)) {
            $errors['name'] = 'Name must be between 3 and 15 characters.';
        }

        if (!Validation::string($password, 8, 40)) {
            $errors['password'] = 'Password must have at least 8 characters.';
        }

        if (!Validation::match($password, $password_confirmation)) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }



        if (!empty($errors)) {
            loadview('user/create', [
                'errors' => $errors,
                'user' =>
                [
                    'name' => $name,
                    'email' => $email,
                    'state' => $state,
                    'city' => $city
                ]
            ]);
            exit;
        }

        $params = ['email' => $email];
        $user = $this->db->query("SELECT * FROM users WHERE email = :email", $params)->fetch();

        if ($user) {
            $errors['email'] = "{$email} is already in use.";
            loadView('user/create', ['errors' => $errors]);
            exit;
        }


        $data = [
            'name' => $name,
            'email' => $email,
            'state' => $state,
            'city' => $city,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        $this->db->query("INSERT INTO users (name, email, state, city, password) VALUES(:name, :email, :state, :city, :password)", $data);

        // Get user ID
        $userID = $this->db->pdo->lastInsertId();

        Session::set(
            'user',
            [
                'id' => $userID,
                'name' => $name,
                'email' => $email
            ]
        );


        header('Location: /');
    }

    public function logout()
    {
        Session::clear('user');
        Session::clearAll();

        // Get cookie and destroy it 
        $params = session_get_cookie_params();
        setcookie('PHPSESSID', '', time() - 3600, $params['path'], $params['domain']);

        header('Location: /');
    }

    public function authenticate()
    {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $errors = [];
        if (!Validation::email($email)) {
            $errors['email'] = 'This does not look like an valid email.';
        }

        if (!Validation::string($password, 6, 50)) {
            $errors['password'] = 'Password must be at least 6 characters.';
        }

        // Check for errors
        if (!empty($errors)) {
            loadView('user/login', ['errors' => $errors]);
            exit;
        }

        // Check for email
        $params = ['email' => $email];

        $user = $this->db->query("SELECT * FROM users WHERE email = :email", $params)->fetch();

        if (!$user) {
            $errors['email'] = 'Incorrect Credentials.';
            loadView('user/login', ['errors' => $errors]);
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            $errors['email'] = 'Incorrect Credentials.';
            loadView('user/login', ['errors' => $errors]);
            exit;
        }

        if ($user && password_verify($password, $user['password'])) {
            Session::set(
                'user',
                [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email']
                ]
            );

            header('Location: /');
        }
    }
}
