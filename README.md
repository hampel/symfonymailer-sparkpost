# Symfony Mailer SparkPost Driver

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hampel/symfonymailer-sparkpost.svg?style=flat-square)](https://packagist.org/packages/hampel/symfonymailer-sparkpost)
[![Total Downloads](https://img.shields.io/packagist/dt/hampel/symfonymailer-sparkpost.svg?style=flat-square)](https://packagist.org/packages/hampel/symfonymailer-sparkpost)
[![Open Issues](https://img.shields.io/github/issues-raw/hampel/symfonymailer-sparkpost.svg?style=flat-square)](https://github.com/hampel/symfonymailer-sparkpost/issues)
[![License](https://img.shields.io/packagist/l/hampel/symfonymailer-sparkpost.svg?style=flat-square)](https://packagist.org/packages/hampel/symfonymailer-sparkpost)

By [Simon Hampel](mailto:simon@hampelgroup.com)

## Description

Standalone implementation of Symfony Mailer SparkPost Driver based on https://github.com/gam6itko/sparkpost-mailer - but
using GuzzleHttp as the HTTP client rather than Symfony HTTP Client.

## Installation

You can install the package via composer:

```bash
composer require hampel/symfonymailer-sparkpost
```

## Usage

The SparkPost options available are defined in the API: 
[SparkPost options](https://developers.sparkpost.com/api/transmissions/#header-request-body)

```php

$sparkpostOptions = [
	'options' => [
		'open_tracking' => false,
		'click_tracking' => true,
		'transactional' => true,
	],
];

$transport = new SparkPostApiTransport(
	'MYSPARKPOSTAPIKEY', 
	new GuzzleHttp\Client
);

new SparkPostEmail();
$email->setOptions([
    'click_tracking' => false,
    'open_tracking' => true,
    'transactional' => true,
]);
$email->setCampaignId('my-campaign');
$email->from('webmaster@example.com');
$email->to('me@example.com');
$email->subject('My subject');
$email->text(...);
$email->html(...);

$result = $transport->send($email);

```
