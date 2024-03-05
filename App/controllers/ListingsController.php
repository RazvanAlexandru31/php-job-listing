<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Session;
use Framework\Validation;
use Framework\Ownership;

class ListingsController
{
    protected $db;

    public function __construct()
    {
        $config = require basePath('config/db.php');
        $this->db = new Database($config);
    }

    public function index()
    {
        $listing = $this->db->query("SELECT * FROM listings ORDER BY created_at DESC")->fetchAll();
        loadview('listings/index', ['listing' => $listing]);
    }


    public function create()
    {
        loadView('listings/create');
    }

    public function show($params)
    {

        $id = $params['id'] ?? '';
        // inspect($id);
        $data = ['id' => $id];
        $listing = $this->db->query("SELECT * FROM listings WHERE id = :id", $data)->fetch();
        // inspect($listing);

        // check if listing exists
        if (!$listing) {
            ErrorController::notFound('Listings not found');
            return;
        }

        loadView('listings/show', ['listing' => $listing]);
    }

    public function store()
    {
        $allowedFields = [
            'title', 'description',
            'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'
        ];

        $newListingsData = array_intersect_key($_POST, array_flip($allowedFields));
        $newListingsData['user_id'] = Session::get('user')['id'];

        $newListingsData = array_map('sanitize', $newListingsData);

        $requiredFields = ['title', 'description', 'email', 'city', 'salary'];

        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty($newListingsData[$field]) && !Validation::string($newListingsData[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        };

        if (!empty($errors)) {
            // Reload view with erros
            loadView('listings/create', ['errors' => $errors]);
        } else {
            // Submit data
            $fields = [];
            foreach ($newListingsData as $field => $value) {
                $fields[] = $field;
            }
            $fields = implode(', ', $fields);

            $values = [];
            foreach ($newListingsData as $field => $value) {
                // convert empty strings to null
                if ($value === '') {
                    $newListingsData[$field] == null;
                }
                $values[] = ':' . $field;
            }
            $values = implode(', ', $values);
        }

        $sql = "INSERT INTO listings ({$fields}) VALUES ({$values})";
        $this->db->query($sql, $newListingsData);

        header('Location: /listings');
    }

    public function destroy($params)
    {
        $id = $params['id'] ?? '';
        $data = ['id' => $id];

        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $data)->fetch();

        // Check of listing exists
        if (!$listing) {
            ErrorController::notFound('Listing not found.');
            return;
        }

        // if (Session::get('user')['id'] !== $listing['user_id']) {
        //     $_SESSION['error_message'] = 'You are not authorized to delete this listing.';
        //     return header('Location: /listings/ ' . $listing['id']);
        // }

        if (!Ownership::isOwner($listing['user_id'])) {
            $_SESSION['error_message'] = 'You are not authorized to delete this listing.';
            return header('Location: /listings/ ' . $listing['id']);
        }


        $this->db->query('DELETE FROM listings WHERE id = :id', $params);

        $_SESSION['success_message'] = 'Listing deleted successfully.';

        header('Location: /listings');
    }


    public function edit($params)
    {
        $id = $params['id'] ?? '';
        $data = ['id' => $id];

        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $data)->fetch();
        // inspectAndDie($listing);

        if (!Ownership::isOwner($listing['user_id'])) {
            $_SESSION['error_message'] = 'You are not the owner of this listing.';
            header('Location: /listings/' . $listing['id']);
        }

        if (!$listing) {
            ErrorController::notFound('Listing now found.');
        }



        loadView('listings/edit', ['listing' => $listing]);
    }


    public function update($params)
    {
        $id = $params['id'];
        $data = ['id' => $id];

        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $data)->fetch();

        if (!$listing) {
            ErrorController::notFound('Listings not found.');
            return;
        }

        if (!Ownership::isOwner($listing['user_id'])) {
            $_SESSION['error_message'] = 'You are not the owner of this listing.';
            header('Location: /listings/' . $listing['id']);
        }

        $allowedFields = [
            'title', 'description',
            'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'
        ];

        $updatedValues = [];

        $updatedValues = array_intersect_key($_POST, array_flip($allowedFields));
        $updatedValues = array_map('sanitize', $updatedValues);

        $requiredFields = ['title', 'description', 'email', 'city', 'salary'];

        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty($updatedValues[$field]) || !Validation::string($updatedValues[$field])) {
                $errors[$field] = ucfirst($field) . ' is required.';
            }
        }

        if (!empty($errors)) {
            loadView(
                '/listings/edit',
                [
                    'listing' => $listing,
                    'errors' => $errors
                ]
            );
        } else {
            $updatedFields = [];
            foreach (array_keys($updatedValues) as $field) {
                $updatedFields[] = $field . ' =' . ' :' . $field;
            }

            $updatedFields = implode(', ', $updatedFields);
            // inspectAndDie($insertValues);

            $updatedValues['id'] = $id;

            $this->db->query("UPDATE listings SET $updatedFields WHERE id = :id", $updatedValues);

            $_SESSION['success_message'] = 'Listing updated successfully.';
            header('Location: /listings/'  . $id);
        }
    }

    public function search()
    {
        // inspectAndDie($_GET);

        $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
        $location = isset($_GET['location']) ? trim($_GET['location']) : '';

        $query = "SELECT * FROM listings WHERE (title LIKE :keywords) AND (city LIKE :location)";
        $params = [
            'keywords' => '%' . $keywords . '%',
            'location' => '%' . $location . '%'
        ];
        $listing = $this->db->query($query, $params)->fetchAll();

        // inspectAndDie($listing);
        loadView('/listings/index', ['listing' => $listing]);
    }
}
