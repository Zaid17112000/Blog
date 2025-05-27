<?php
    require_once __DIR__ . '/../../vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..'); // Path to .env
    /** 
     * Dotenv\Dotenv: Refers to the Dotenv class from the vlucas/phpdotenv library, used to load environment variables from a .env file. 
     * createImmutable: Creates an immutable instance of the Dotenv class, meaning the environment variables cannot be modified after loading (enhancing security).
    */
    $dotenv->load();
    /**
     * $dotenv->load(): Reads the .env file and loads its key-value pairs into PHP’s environment variables (accessible via $_ENV or getenv()).
     * How it works: The .env file contains lines like KEY=VALUE (e.g., DB_HOST=localhost), which are parsed and made available as environment variables.
    */

    // Optional: Validate required variables
    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'JWT_SECRET']);
    /**
     * $dotenv->required([...]): Ensures that the specified environment variables are defined in the .env file and are not empty.
     * Behavior: If any of these variables are missing or empty in the .env file, the phpdotenv library throws a Dotenv\Exception\ValidationException, halting execution.
     */