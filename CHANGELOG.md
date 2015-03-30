# Version 1.1.1

## Bugfixes

* Added request uri normalization

## Features

* None

# Version 1.1.0

## Bugfixes

* None

## Features

* Added implementations for different basic HTTP authentication mechanisms
* Updated build process
* Added ircmaxell/password-compat as a fallback for PHP 5.4

# Version 1.0.0

## Bugfixes

* None

## Features

* Switched to stable dependencies due to version 1.0.0 release

# Version 0.2.3

## Bugfixes

* None

## Features

* Applied new coding conventions
* Updated dependencies

# Version 0.2.2

## Bugfixes

* None

## Features

* Add Response::redirect() method to simplify redirects
* Add Request::hasParam() method to allow query whether a param exists in the request or not

# Version 0.2.1

## Bugfixes

* Bugfix for invalid cookie handling allows one cookie per name, but multiple cookies are allowed, e. g. Set-Cookie

## Features

* None

# Version 0.2.0

## Bugfixes

* None

## Features

* Moved to appserver-io organisation
* Refactored namespaces

# Version 0.1.4

## Bugfixes

* minor fix for getBodyContent on zero length in req and res classes

## Features

* None

# Version 0.1.2

## Bugfixes

* fixed #79 (https://github.com/appserver-io/http/issues/79)

## Features

* None

# Version 0.1.2

## Bugfixes

* fixed #76 (https://github.com/appserver-io/http/issues/76)

## Features

* None


# Version 0.1.1

## Bugfixes

* None

## Features

* Refactoring ANT PHPUnit execution process
* Composer integration by optimizing folder structure (move bootstrap.php + phpunit.xml.dist => phpunit.xml)
* Switch to new appserver-io/build build- and deployment environment
