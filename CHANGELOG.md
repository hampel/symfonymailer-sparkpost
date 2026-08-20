CHANGELOG
=========

1.1.4 (2026-08-20)
------------------

* fix bugs in SparkPostApiTransport::handleError - it called getContent(), which does not exist on a
  PSR-7 response, and built an HttpTransportException, which is typed against Symfony's HttpClient
  ResponseInterface and cannot accept a Guzzle response; API errors now throw TransportException, which
  is what implements TransportExceptionInterface
* send the transmission with 'http_errors' => false so that non-2xx responses are handled by
  handleError rather than escaping as a Guzzle ClientException - note that callers who were catching
  GuzzleHttp exceptions from this transport should now catch TransportExceptionInterface
* handleError no longer assumes the response body is JSON, or that a JSON body has an 'errors' key
* treat any 2xx response as success rather than only 200

1.1.3 (2025-08-30)
------------------

* fix bug in ReplyTo headers where we were using the wrong parameter key

1.1.2 (2024-10-04)
------------------

* fix bug in AbstractHttpTransport::doSend where it tries to call appendDebug on the wrong object

1.1.1 (2024-09-09)
------------------

* use from headers in the email object if they are set; fall back to using the sender from the envelope, 
  which will generate a from address of us if necessary

1.1.0 (2024-08-09)
------------------

* add options to SparkPostEmail to set transactional / click tracking / open tracking

1.0.0 (2024-08-09)
------------------

* initial release
