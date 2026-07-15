# Satifly — The Next-Generation Private PHP Package Registry

![CI Status](https://github.com/Trusted97/satifly/actions/workflows/ci.yaml/badge.svg)
![PHP Version](https://img.shields.io/badge/PHP-8.4-blue?style=flat)
![Symfony Version](https://img.shields.io/badge/Symfony-7.4-green?style=flat)
[![Maintainability](https://qlty.sh/badges/a2bd8d7e-c9c0-48bf-b681-eeb3576bbe35/maintainability.svg)](https://qlty.sh/gh/Trusted97/projects/satifly)

**Satifly** is the easiest and fastest way to host and manage your private Composer packages. Built for modern PHP developers, it combines the power of [Satis](https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md) with the performance of [FrankenPHP](https://frankenphp.dev) and the reliability of [Caddy](https://caddyserver.com/) - all wrapped in a clean, production-ready Docker environment.

- - -

## Documentation

Start with the [docs hub](docs/README.md).

- [Architecture overview](docs/architecture/overview.md)
- [Developer guide](docs/development/guide.md)
- [Docker guide](docs/docker/overview.md)
- [Testing guide](docs/testing/guide.md)
- [Security guide](docs/security/overview.md)
- [Troubleshooting guide](docs/troubleshooting/guide.md)
- [Webhook guide](docs/webhook.md)

- - -

## 💡 Why Satifly?

Managing private Composer packages doesn’t have to be painful. Satifly eliminates manual configuration, provides a beautiful web UI, and automates your build and deployment process - securely and efficiently.

*   🌐 **Full-featured web dashboard** - manage `satis.json` visually
*   🔐 **Authentication** - protect your internal registry with ease
*   ⚡ **FrankenPHP-powered performance** - fast, concurrent builds out of the box
*   🐳 **Zero-effort Docker stack** - ready to run in seconds
*   🔄 **[VCS webhooks](docs/webhook.md)** - automatic package rebuilds on push
*   🚀 **HTTP/3 + Early Hints** - next-gen web delivery built-in

Satifly builds upon the excellent work of [project-satisfy/satisfy](https://github.com/project-satisfy/satisfy), extending it with cutting-edge tooling and developer-first improvements.

- - -

## 🚀 Get Started in Minutes

1.  Install [Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2.  Clone and enter the repository:

```
git clone https://github.com/Trusted97/satifly
cd satifly
```

3.  Build and launch Satifly:

```
make build
make up
make doctor
```

4.  Open [https://localhost](https://localhost) and enjoy your private PHP registry.
5.  Stop everything when you’re done:

```
make down
```

👉 Want advanced Docker, Composer, or Xdebug workflows? Refer to the [developer guide](docs/development/guide.md) and [Makefile reference](docs/makefile.md).

- - -

## ⚙️ Configure It Your Way

### 📁 Manage Your Packages

Use your existing `satis.json` or generate one instantly:

```
make satis-init
```

Prefer a visual experience? Configure everything directly via the web UI at `/admin/satis/config`.

> 📚 For more details on how Satis works (configuration examples, usage, and authentication), refer to the official Composer documentation: [Using Satis](https://composer.github.io/satis/using)

### 🔧 Application Settings

Tune Satifly’s behavior in `config/parameters.yml`.

Example: enable admin authentication

```yaml
admin:
  auth: true
  users:
    - { username: admin, password: secret }
```

## 🧱 Running Satifly

Your Satifly instance serves the web UI from the `public` directory. Access `/admin` to manage repositories and configuration.

Build package definitions anytime with:

```
make satis-build
```

- - -

## ⚡ Core Advantages

*   ✅ **Production-grade by design** - secure, scalable, and battle-tested
*   🐘 **Built with Symfony 7.4 & PHP 8.4** - modern, reliable foundation
*   🚀 **FrankenPHP worker mode** - ultra-fast and memory efficient
*   🔒 **Automatic HTTPS** with Caddy
*   🧩 **Extensible architecture** - customize for your organization
*   👨‍💻 **Dev, CI, and production ready** out of the box

- - -

## 📄 License

Satifly is open source and distributed under the **MIT License**. For details, see the `LICENSE` file.

- - -

## 🤝 Contribute

We welcome contributions from developers and organizations.

Read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.
