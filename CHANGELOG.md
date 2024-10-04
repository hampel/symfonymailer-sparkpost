CHANGELOG
=========

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
