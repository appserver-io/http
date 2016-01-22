# Version 2.0.0

## Bugfixes

* None

## Features

* Refatoring authentication module to work with latest HTTP library

# Version 1.1.8

## Bugfixes

* Fixed [#944](https://github.com/appserver-io/appserver/issues/944) - 404 when filename contains a + char

## Features

* None

# Version 1.1.7

## Bugfixes

* Add missing filter for URI path elements, resulting in multiple XSS vulnerabilities

## Features

* None

# Version 1.1.6

## Bugfixes

* Fixed invalid index if no user credentials are passed for basic authentication

## Features

* None

# Version 1.1.5

## Bugfixes

* Fixed bug with fixed zero HTTP content length for status codes 3xx

## Features

* None

# Version 1.1.4

## Bugfixes

* None

## Features

* Added constant header value for content-type text/html

# Version 1.1.3

## Bugfixes

* Fixed invalid cookie initialization for invalid cookie strings

## Features

* None

# Version 1.1.2

## Bugfixes

* None

## Features

* Refactored Content-Length handling in response
* Added Http Headernames to HttpProtocol dictionary

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
