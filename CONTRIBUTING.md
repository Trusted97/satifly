# 🧩 Contributing to Satifly

Thanks for your interest in contributing to **Satifly** — the modern web interface and Dockerized runtime for [Composer Satis](https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md). We’re thrilled to have you join the community!

- - -

## 🧱 Project Overview

Satifly provides a simple web UI and API for managing private PHP packages with **Satis**, powered by **Symfony**, **Caddy**, and **FrankenPHP**.

This project aims to simplify hosting and managing Composer repositories while remaining **lightweight, configurable, and production-ready**.

- - -

## 🚀 Getting Started (Development Setup)

### 1\. Clone the repository

```
git clone https://github.com/Trusted97/satifly.git
cd satifly
```

### 2\. Build the Docker environment

```
make build
```

### 3\. Start the development stack

```
make up
```

This will start Satifly with:

*   PHP (FrankenPHP worker mode)
*   Caddy web server (HTTP/3 + HTTPS)
*   Symfony web UI accessible at [https://localhost](https://localhost)

- - -

## 🧩 Development Guidelines

### Code Style

We follow:

*   **PSR-12** for PHP code
*   **Symfony Coding Standards** for structure and naming
*   **Twig best practices** for templates

Before submitting a pull request, ensure code style consistency:

```
make style
```

- - -

### Commit Messages

Use **conventional commits** for clarity:

```
feat: add webhook configuration to UI
fix: resolve null homepage issue in SatisConfigType
docs: improve README installation section
refactor: move config transformer to dedicated service
```

- - -

### Pull Request Process

1.  Fork the repository and create your branch
2.  Ensure all tests and checks pass locally:

    ```
    make test
    ```

3.  Update documentation if you changed features or behaviors
4.  Open a PR with a clear title and description

CI (GitHub Actions) will run:

*   PHPUnit tests
*   Linting and syntax validation

Your PR will be reviewed and merged once all checks pass ✅

- - -

## 🧪 Testing

Satifly uses **PHPUnit** for backend testing and **Panther** for browser tests.

Run the test suite:

```
make test
```

- - -

## 🐳 Docker Tips

Rebuild containers after dependency updates:

```
make rebuild
```

Clean up unused images and volumes:

```
make clean
```

Restart the stack:

```
make down && make up
```

- - -

## 💬 Submitting Issues

If you find a bug or have a feature request, please open an issue with:

*   A **clear description** of the problem or idea
*   Steps to reproduce (if applicable)
*   Your environment (PHP version, Docker version, OS)
*   Logs or screenshots when relevant

We label issues with:

*   `bug`
*   `feature`
*   `enhancement`
*   `docs`
*   `good first issue`

- - -

## 🔒 Security

If you discover a security vulnerability, **do not open a public issue**. Instead, please email contact the maintainers directly.

- - -

## ❤️ Thank You

Your contributions — big or small — help make **Satifly** faster, safer, and more flexible for the PHP community. We truly appreciate your time, ideas, and code!

_Happy coding! 🎉_
