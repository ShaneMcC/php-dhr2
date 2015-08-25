# Domain-based HTTP Redirect Ruleset (DHR2) in PHP
This is a PHP Implementation of https://github.com/tellnes/dhr2 (See `Spec.md`)

## Usage

Simplest usage is to check this out to the default vhost, then set everything to redirect to index.php.
(For apache, copy `.htaccess.example` to `.htaccess`)

Any domain pointed at the server that doesn't have it's own dedicated vhost will then be subject to DHR2 checks.

You can also create a `noRedirect.php` file that will include page content to serve if no redirect is found (Be aware obviously that this can't include references to any local files in the default vhost as the htaccess will stop them working.)
