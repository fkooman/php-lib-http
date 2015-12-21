# Release History

## 1.5.0 (2015-12-21)
- prefer `HTTP_HOST` in requests to determine the request URL instead of 
  relying on `SERVER_NAME` (`SERVER_NAME` will be removed in 2.0.0)
- **DEPRECATE** `Url::getPort()`, applications SHOULD not use this (
  `Url::getPort()` will be removed in 2.0.0)

## 1.4.0 (2015-12-14)
- implement Response::getStatusCode() and Response::isOkay()

## 1.3.2 (2015-12-11)
- fix running behind a HTTP proxy

## 1.3.1 (2015-11-23)
- allow the `description` parameter from `UnauthorizedException` to be
  missing
- make `QUERY_STRING` optional in `Uri` class as it is not set by default by 
  PHP built in web server

## 1.3.0 (2015-11-17)
- make `Session` implement `SessionInterface` so applications can use
  that to make testing much easier

## 1.2.0 (2015-11-10)
- add `setFile($fileName)` method to `Response` class which sets the
  `X-SendFile` header (for mod_xsendfile)

## 1.1.3 (2015-11-01)
- undo changes of 1.1.2, needs furter investigation to be sure we are
  taking the right approach.

## 1.1.2 (2015-10-30)
- use `rawurldecode` on `PATH_INFO` to match behavior when using php-fpm 
  instead of mod_php.

## 1.1.1 (2015-10-13)
- allow providing raw request body to `Request` object

## 1.1.0 (2015-10-07)
- add `Content-Length` header to responses

## 1.0.1 (2015-09-07)
- fix running the unit test on CentOS 6

## 1.0.0 (2015-09-07)
- initial release
