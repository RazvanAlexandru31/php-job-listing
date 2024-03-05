<?php

/**
 * Get the base path
 * 
 * @param string $path
 * @return string
 */

function basePath($path = '')
{
    return __DIR__ . '/' . $path;
}


/**
 * Load a view
 * @param string $name
 * @param array $data
 * @return void
 */

function loadView($name, $data = [])
{
    $viewPath =  basePath("App/views/{$name}.view.php");
    if (file_exists($viewPath)) {
        extract($data);
        require $viewPath;
    } else {
        echo "View {$name} not found!";
    }
}


/**
 * Load a partial
 * @param string $name
 * @return void
 */

function loadPartial($name)
{
    $viewPartial =  basePath("App/views/partials/{$name}.php");
    if (file_exists($viewPartial)) {
        require $viewPartial;
    } else {
        echo "Partial {$name} not found.";
    }
}


/**
 * Inspect a value
 * 
 * @param midex $value
 * @return void
 */

function inspect($value)
{
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}

/**
 * Inspect a value and kill the script
 * 
 * @param mixed $value
 * @return void
 */

function inspectAndDie($value)
{
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
    die;
}

/**
 * Format salary
 * @param string $salary
 * @return string formattedSalary
 */

function formatNumber($salary)
{
    return ' &#8364; ' . number_format(floatval($salary));
}

/**
 * Sanitize data
 * 
 * @param string $value
 * 
 * @return string
 */

function sanitize($value)
{
    return filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS);
}
