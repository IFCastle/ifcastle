#!/usr/bin/env bash
set -euo pipefail
# CI's native runtime is independent of the maintainer's local PHP checkout.
: "${CI_RUNTIME_PREFIX:?}" "${CI_RUNTIME_SOURCE:?}"
php_revision=ef90aae98edce6ccd8cf0a198670087d2068af77
async_revision=c95dcf74933ce2ec124e2f158a1b0743fe1478e0
server_revision=19cfc24bb30a24351aaf304237a596f5803e3195

checkout() {
    local repository="$1" revision="$2" destination="$3"
    if test -d "$destination/.git"; then
        test "$(git -C "$destination" remote get-url origin)" = "$repository"
    else
        git init "$destination"
        git -C "$destination" remote add origin "$repository"
    fi
    git -C "$destination" fetch --depth=1 origin "$revision"
    git -C "$destination" checkout --detach FETCH_HEAD
    test "$(git -C "$destination" rev-parse HEAD)" = "$revision"
}
mkdir -p "$CI_RUNTIME_SOURCE" "$CI_RUNTIME_PREFIX/etc/conf.d"
checkout https://github.com/true-async/php-src.git "$php_revision" "$CI_RUNTIME_SOURCE/php"
checkout https://github.com/true-async/php-async.git "$async_revision" "$CI_RUNTIME_SOURCE/php/ext/async"
checkout https://github.com/true-async/server.git "$server_revision" "$CI_RUNTIME_SOURCE/server"
(
    cd "$CI_RUNTIME_SOURCE/php"
    ./buildconf --force
    ./configure --prefix="$CI_RUNTIME_PREFIX" --disable-all --without-pear \
        --enable-zts --enable-async --enable-pdo --with-pdo-mysql --with-pdo-sqlite \
        --enable-mbstring --with-libxml --enable-dom --enable-xml --enable-xmlreader --enable-xmlwriter \
        --enable-simplexml --enable-tokenizer --enable-ctype --enable-filter \
        --enable-phar --enable-fileinfo --enable-session --enable-sockets --enable-posix --enable-pcntl \
        --with-openssl --with-zlib --with-iconv --with-curl --with-zip --with-password-argon2 \
        --with-config-file-path="$CI_RUNTIME_PREFIX/etc" \
        --with-config-file-scan-dir="$CI_RUNTIME_PREFIX/etc/conf.d"
    make -j4
    make install
)
(
    cd "$CI_RUNTIME_SOURCE/server"
    "$CI_RUNTIME_PREFIX/bin/phpize"
    ./configure --with-php-config="$CI_RUNTIME_PREFIX/bin/php-config" \
        --enable-http-server --disable-http2 --disable-http3 --enable-websocket --with-openssl
    make -j4
    make install
)
printf '%s\n' 'extension=true_async_server.so' > "$CI_RUNTIME_PREFIX/etc/conf.d/server.ini"
"$CI_RUNTIME_PREFIX/bin/php" -r '
if (PHP_VERSION_ID < 80600 || !PHP_ZTS || !extension_loaded("true_async")
    || !extension_loaded("true_async_server") || version_compare(phpversion("true_async_server"), "0.16.0", "<")) {
    fwrite(STDERR, "Invalid IFCastle CI runtime\n"); exit(1);
}
'
printf '%s\n' "$php_revision" "$async_revision" "$server_revision" > "$CI_RUNTIME_PREFIX/source-revisions.txt"
