# Native validation runtime

`build-runtime.sh` builds a standalone PHP8.6 ZTS prefix from pinned TrueAsync PHP,
async and server revisions. It never replaces the runner's system PHP. CI installs
Ubuntu24 development libraries; PHP's async extension is built in, server0.16 is
built shared against the installed headers. HTTP1/TLS/WebSocket support matches this
framework suite. HTTP2/3 remain covered by the server repository's dedicated CI.

All native builds use at most four jobs. Runtime cache key includes the complete build
script (source revisions and module/configuration flags); no cross-runtime vendor
cache is restored. On both hit and miss the workflow inspects extensions, checks the
server version and runs Composer check-platform-reqs, then the full PHPUnit suite.
Composer2.9.4 is downloaded with its published SHA256 checksum. Dependency resolution
remains unlocked because this monorepo does not commit composer.lock.

Local reproduction uses a clean Ubuntu24 container, the same dependency list, and
CI_RUNTIME_PREFIX/CI_RUNTIME_SOURCE pointing to writable isolated directories. A
successful binary build alone is not passing CI: install dependencies under its PHP
and run Composer test. Cache restore and hosted publication are separately observable.

Package publication remains gated by validate. Its tool revision is fixed; branch
pushes target refs/heads and tags target refs/tags, preserving annotated tags after
filtering. The token is supplied by a credential helper, not a remote URL or log line.
The current matrix still lacks trueasync-engine and trueasync-web-server; their
IFCastle destination repositories did not resolve during the2026-10-08 audit. Creating
and configuring those destinations is a separate publication task.
