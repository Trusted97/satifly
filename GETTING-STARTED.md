# 🚀 Getting Started with Satifly

**Satifly** is a lightweight web interface and runtime for [Composer Satis](https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md), making it easy to host and manage private PHP package repositories.

It runs on modern PHP technologies like [FrankenPHP](https://frankenphp.dev) and [Caddy](https://caddyserver.com/), giving you fast performance, automatic HTTPS, and support for HTTP/3 — all with a single Docker setup.

- - -

## 🧱 Prerequisites

*   [Docker Compose](https://docs.docker.com/compose/install/) v2.10 or newer
*   `make` installed (optional but recommended)

- - -

## 📦 Installation

1.  Clone the repository:

    ```
    git clone https://github.com/Trusted97/satifly
    cd satifly
    ```

2.  Build the Docker images:

    ```
    make build
    ```

3.  Start the stack:

    ```
    make up
    ```

4.  Visit [https://localhost](https://localhost) and accept the self-signed certificate.

To stop the stack:

```
make down
```

For a smoother developer experience (shortcuts for Docker, Composer, and Xdebug), check the [Makefile documentation](docs/makefile.md).

- - -

## ⚙️ Configuration

Satifly manages your `satis.json` file automatically through its web interface. You can either use your existing configuration or create one directly in the browser.

### Option 1: Existing Satis Configuration

Place your `satis.json` file in the project root:

```
/project-root/satis.json
```

### Option 2: Initialize via Makefile

Use the built-in helper command:

```
make satis-init
```

### Option 3: Configure via Web UI

Open `/admin/satis/config` and fill in repository, package, and archive settings directly in your browser.

- - -

## 🔐 Authentication (Optional)

To enable basic authentication, edit `config/parameters.yml` and set:

```
admin:
  auth: true
  users:
    - { username: admin, password: secret }
```

After enabling authentication, you’ll need to log in to access the admin area.

- - -

## 🧰 Build Packages

Once your configuration is ready, generate your Satis repository:

```
make satis-build
```

This command builds all defined packages and saves them in the output directory (default: `public/`).

- - -

## 🔄 Webhooks (Optional)

Satifly supports VCS webhooks to rebuild packages automatically after every push.

Example GitLab webhook URL:

```
[your-satifly-url]/webhook/gitlab
```

This triggers a package build for updated repositories — no need to rebuild everything.

- - -

## ✅ Done!

You now have a private Composer registry running locally with an integrated web admin interface.

Explore `https://localhost/admin` to manage your repositories and enjoy a smoother Satis experience.

💡 Need more details? Check out the [full documentation on GitHub](https://github.com/Trusted97/satifly).
