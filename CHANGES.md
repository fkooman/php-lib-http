# Release History

## 1.3.1 (...)
- allow the `description` parameter from `UnauthorizedException` to be
  missing

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
