# XAO-PHP: A Modern PHP Framework

Welcome to the modernized XAO-PHP, a framework designed for building dynamic, responsive web applications with a focus on simplicity and performance. Originally conceived over 20 years ago as an XML-centric platform, XAO-PHP has been completely revitalized to meet the demands of modern web development.

This new iteration of XAO-PHP is being rebuilt with the goal of integrating smoothly with Object-Relational Mappers (ORMs) and HTMX on the front end.

## Key Features

*   **Modern PHP:** Built on a foundation of modern PHP (8.0+), leveraging features like PSR-4 autoloading, namespaces, and strict typing.
*   **XML Core:** While modernized, the framework retains its powerful XML core, providing a robust and flexible way to manage and transform data.

## Future Goals

*   **ORM-Ready:** The framework is being designed to integrate smoothly with your favorite ORM, allowing you to work with your database in an object-oriented way.
*   **HTMX-Friendly:** The framework is being built to complement HTMX, making it easy to create dynamic, interactive user interfaces without writing complex JavaScript.

## Getting Started

To get started with XAO-PHP, you'll need to have Composer installed. Then, you can clone this repository and install the dependencies:

```bash
git clone https://github.com/tezza1971/XAO-PHP.git
cd XAO-PHP
composer install
```

Once the dependencies are installed, you can run the example application by starting a PHP web server in the `public` directory:

```bash
cd public
php -S localhost:8000
```

Then, open your browser and navigate to `http://localhost:8000`.

## Project Structure

*   `public/`: The web server entry point. All of your application's public assets should be here.
*   `src/`: The core framework source code.
*   `docs/`: Documentation for the framework.
*   `schema/`: XML schema files.
*   `templates/`: XSLT templates for transforming XML to HTML.

## A New Vision

The goal of this modernized framework is to provide a lean, fast, and enjoyable development experience. By combining the strengths of a classic XML-based architecture with the latest in PHP, ORM, and front-end technologies, XAO-PHP is being rebuilt to help you create the next generation of web applications.
