#!/usr/bin/env bash
# Bootstrap a full TYPO3 dev instance around this extension for manual testing.
# Usage from a fresh clone: ./Build/dev-setup.sh
# Re-running is safe: existing installation, site and content are left untouched.
set -euo pipefail
cd "$(dirname "$0")/.."

BACKEND_USER='admin'
BACKEND_PASSWORD='Hyphenator14!'
SITE_URL='https://typo3-hyphenator.ddev.site'

if [ ! -f .ddev/config.yaml ]; then
    ddev config --project-type=typo3 --docroot=public --create-docroot --php-version=8.2
fi
ddev start

ddev composer install --no-progress --no-interaction

if ! ddev exec test -f config/system/settings.php; then
    ddev exec vendor/bin/typo3 setup \
        --driver=mysqli --host=db --port=3306 --dbname=db --username=db --password=db \
        --admin-username="${BACKEND_USER}" --admin-user-password="${BACKEND_PASSWORD}" \
        --admin-email=hallo@straschek.io --project-name='Hyphenator Dev' \
        --server-type=other --force --no-interaction
    # enable deprecation logging (limited to the LOG deprecations block)
    ddev exec sed -i "/'deprecations' =>/,/^                ],$/ s/'disabled' => true/'disabled' => false/" \
        config/system/settings.php
fi

if [ ! -f config/sites/main/config.yaml ]; then
    mkdir -p config/sites/main
    cat > config/sites/main/config.yaml <<YAML
rootPageId: 1
base: '${SITE_URL}/'
websiteTitle: 'Hyphenator Dev'
languages:
  - title: Deutsch
    enabled: true
    languageId: 0
    base: /
    locale: de_DE.UTF-8
    navigationTitle: Deutsch
    flag: de
YAML

    ddev mutagen sync >/dev/null 2>&1 || true
    ddev mysql <<'SQL'
INSERT IGNORE INTO pages (uid, pid, title, doktype, is_siteroot, slug, tstamp, crdate, sorting)
VALUES (1, 0, 'Home', 1, 1, '/', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 256);

INSERT IGNORE INTO sys_template (uid, pid, title, root, clear, include_static_file, config, tstamp, crdate, sorting)
VALUES (1, 1, 'Main', 1, 3, 'EXT:fluid_styled_content/Configuration/TypoScript/',
        'page = PAGE\npage.10 < styles.content.get', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 256);

INSERT IGNORE INTO tt_content (uid, pid, CType, header, bodytext, colPos, tstamp, crdate, sorting)
VALUES (1, 1, 'text', 'Donaudampfschifffahrt voraus',
        '<p>Die Donaudampfschifffahrt und die Straßenbahn fahren. (Donaudampfschifffahrt) gehört der Arbeitgeberin.</p>',
        0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 256);

INSERT IGNORE INTO tx_hyphenator_term (uid, pid, `from`, `to`, tstamp, crdate)
VALUES (1, 0, 'Donaudampfschifffahrt', 'Donau|dampf|schiff|fahrt', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
       (2, 0, 'Straßenbahn', 'Straßen|bahn', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
       (3, 0, 'Arbeit', 'Ar|beit', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
SQL
fi

ddev exec vendor/bin/typo3 cache:flush

echo ""
echo "Frontend: ${SITE_URL}/"
echo "Backend:  ${SITE_URL}/typo3 (${BACKEND_USER} / ${BACKEND_PASSWORD})"
echo "Tests:    ddev composer test | Code style: ddev composer cs"
