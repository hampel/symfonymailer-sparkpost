# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this package is

`hampel/symfonymailer-sparkpost` — a standalone Symfony Mailer transport for the SparkPost
Transmissions API. It is a port of [gam6itko/sparkpost-mailer](https://github.com/gam6itko/sparkpost-mailer)
(itself modelled on Symfony's own mailer bridges) with one deliberate substitution: **GuzzleHttp
replaces Symfony HTTP Client**.

That substitution is the single most important thing to know. The transport hierarchy still carries
Symfony's shape and authorship, but every response object is **PSR-7**, not
`Symfony\Contracts\HttpClient\ResponseInterface`. Anything ported from upstream that calls
`getContent()` or `toArray()` on a response is wrong here — PSR-7 gives you `getBody()`,
`getStatusCode()`, `getReasonPhrase()` and nothing else. The same applies to Symfony's
`HttpTransportException`: its constructor and `getResponse()` are typed against
`Symfony\Contracts\HttpClient\ResponseInterface`, so it **cannot** be used in this package at all.
Throw `Symfony\Component\Mailer\Exception\TransportException` instead — it is what satisfies
`TransportExceptionInterface`, which is what consumers of a Symfony Mailer transport actually catch.

## Commands

```bash
composer install
vendor/bin/phpunit                              # whole suite
vendor/bin/phpunit --filter testMethodName      # one test
vendor/bin/phpunit tests/Path/To/SomeTest.php   # one file
```

There is currently **no `tests/` directory** — `phpunit.xml` and the `Tests\` dev autoload namespace
are wired up and waiting for one. Any new test goes in `tests/`, namespace `Tests\`, filename
suffix `Test.php` (the suite discovers by suffix).

No static analysis, no CI workflow, and no `.gitattributes`/`export-ignore` in this repo yet.

## Architecture

### Transport layering

Four levels, each adding one thing:

1. `Symfony\...\AbstractTransport` — rate limiting, event dispatch, `send()`.
2. `Transport/AbstractHttpTransport` — injects the Guzzle `ClientInterface`, holds host/port, and
   wraps `doSendHttp()` so both success and `ClientException` append a reason phrase to the
   `SentMessage` debug log.
3. `Transport/AbstractApiTransport` — converts `SentMessage`'s original message to an `Email` via
   `MessageConverter`, then hands off to `doSendApi()`.
4. `Transport/SparkPostApiTransport` — builds the Transmissions payload and POSTs it to
   `https://{host}/api/v1/transmissions/` with the API key in a bare `Authorization` header (no
   `Bearer` prefix — SparkPost wants the raw key).

Host defaults to `api.sparkpost.com`, or `api.{region}.sparkpost.com` when a region is passed.

### Recipients come from the Envelope, never the Email

`buildRecipients()` iterates `$envelope->getRecipients()`, which is To + Cc + Bcc flattened. The
SparkPost payload has no Cc/Bcc concept at this level, so every recipient becomes its own entry.
Note that `AbstractApiTransport::getRecipients()` (which filters Cc/Bcc out) is inherited from the
Symfony lineage but **is not used** by `SparkPostApiTransport`.

Because recipients live on the envelope, `EventListener/SinkEnvelopeListener` can rewrite them all
to `<address>.sink.sparkpostmail.com` for testing without touching the message headers.

### Error handling

The request is sent with Guzzle's `'http_errors' => false`, so a non-2xx response comes back as an
ordinary response object and `handleError()` is the single place that turns it into an exception —
a `TransportException` carrying the HTTP status as its code, the decoded `errors` array (or the raw
body, if it is not JSON) in its message, and the reason phrase in its debug string. **Do not turn
`http_errors` back on**: Guzzle would then throw `ClientException`/`ServerException`, which escape
every `catch (TransportExceptionInterface)` a consumer has written.

`handleError()` must stay defensive about the body — SparkPost is not the only thing that can answer
on that URL, and a proxy or gateway will return HTML. `json_decode()` returning `null`, and a JSON
body with no `errors` key, are both expected inputs.

The `catch (ClientException)` in `AbstractHttpTransport::doSend()` is a backstop for a caller-supplied
client whose middleware throws anyway; it is not the primary error path.

### The `content` short-circuit is the extension mechanism

`SparkPostApiTransport::buildContent()` returns `SparkPostEmail::getContent()` verbatim if it is set,
and only otherwise assembles `from`/`subject`/`text`/`html`/`reply_to`/`attachments` from the MIME
message. That branch is how the two subclasses work — they set a raw content array in their
constructor and the normal MIME body is never consulted:

- `Mime/TemplateEmail` → `['template_id' => …, 'use_draft_template' => …]`
- `Mime/ABTestEmail` → `['ab_test_id' => …]`

Both also override `generateMessageId()` with their own synthetic domain.

`from` prefers the Email's own From header and falls back to the envelope sender (which Symfony will
synthesise if needed). `reply_to` is a **comma-joined string of addresses**, not an array of objects
— SparkPost's API differs from Symfony's model here.

### `SparkPostEmail` and serialisation

`Mime/SparkPostEmail` extends `Symfony\Component\Mime\Email` with the SparkPost-specific top-level
transmission fields — `campaign_id`, `description`, `options` (with `setTransactional()`,
`setClickTracking()`, `setOpenTracking()` convenience setters), plus `metadata` and
`substitution_data` via `HasMetadataTrait` / `HasSubstitutionDataTrait`.

**Adding a property here means updating `__serialize()`/`__unserialize()`.** They serialise a
positional array ending in the parent's payload; a property left out silently vanishes when a
message is queued through Symfony Messenger. The order of the two lists must stay in lockstep.

In the transport, these fields are merged into the payload through `array_filter()`, so empty
arrays, nulls and `false` values are dropped rather than sent.

### Logging

`SparkPostApiTransport::log()` truncates any attachment `data` longer than 100 chars before it
reaches the debug log. Keep that guard in place when touching the payload-building code — the
alternative is base64 blobs in the log.

## Version support

`composer.json` declares `php: >=7.2.5` and `symfony/mailer: ^4.4|^5.0|^6.0`, and the source is
written to match: `private $prop` declarations with docblock types rather than typed properties, no
constructor promotion, no arrow functions. Match that style unless the floor is deliberately raised.

Note the tension: `phpunit ^10` in `require-dev` needs PHP 8.1+, so the dev environment cannot
actually run on the declared floor. Widening or narrowing any constraint is a policy decision — see
the version-support policy referenced in the parent `~/packages/CLAUDE.md`, not a judgement call.

## Releases

`CHANGELOG.md` is hand-maintained, newest first, `x.y.z (YYYY-MM-DD)` heading with bullet points, and
is updated in its own commit before tagging. Simon does his own pushes and tagging.
