# ![logo](/examples/assets/instagram.png) Instagram PHP [![Latest Stable Version](https://poser.pugx.org/mgp25/instagram-php/v/stable)](https://packagist.org/packages/mgp25/instagram-php) [![Total Downloads](https://poser.pugx.org/mgp25/instagram-php/downloads)](https://packagist.org/packages/mgp25/instagram-php) ![compatible](https://img.shields.io/badge/PHP%207-Compatible-brightgreen.svg) [![License](https://poser.pugx.org/mgp25/instagram-php/license)](https://packagist.org/packages/mgp25/instagram-php)

This is Instagram's private API. It has all the features the Instagram app has, including media upload.

**Read the [wiki](https://github.com/mgp25/Instagram-API/wiki)** and previous issues before opening a new one! Maybe your issue is already answered.

**Frequently Asked Questions:** [F.A.Q.](https://github.com/mgp25/Instagram-API/wiki/FAQ)

**Do you like this project? Support it by donating**
- ![Paypal](https://raw.githubusercontent.com/reek/anti-adblock-killer/gh-pages/images/paypal.png) Paypal: [Donate](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5ATYY8H9MC96E)
- ![btc](https://raw.githubusercontent.com/reek/anti-adblock-killer/gh-pages/images/bitcoin.png) Bitcoin: 1DCEpC9wYXeUGXS58qSsqKzyy7HLTTXNYe 

----------
## Installation

### Via Composer

```sh
composer require mgp25/instagram-php
```

```php
require __DIR__.'/../vendor/autoload.php';

$ig = new \InstagramAPI\Instagram();
```

If you want to test new and possibly unstable code that is in the master branch, and which hasn't yet been released, then you can use master instead (at your own risk):

```sh
composer require mgp25/instagram-php dev-master
```


### Don't have Composer?

You can download it [here](https://getcomposer.org/download/).

## Examples

All examples can be found [here](https://github.com/mgp25/Instagram-API/tree/master/examples).

## Code of Conduct

This project adheres to the Contributor Covenant [code of conduct](CODE_OF_CONDUCT.md).
By participating, you are expected to uphold this code.
Please report any unacceptable behavior.

## Contributing

If you would like to contribute to this project, please feel free to submit a pull request.

Before you do, take a look at the [contributing guide](https://github.com/mgp25/Instagram-API/blob/master/CONTRIBUTING.md).

## Motivations

After legal measures, Facebook, WhatsApp and Instagram blocked my accounts. In order to use Instagram
 on my phone I needed a new phone, as they banned my UDID, so that is basically why I made this API.

### What is Instagram?
According to [the company](https://instagram.com/about/faq/):

> "Instagram is a fun and quirky way to share your life with friends through a series of pictures. Snap a photo with your mobile phone, then choose a filter to transform the image into a memory to keep around forever. We're building Instagram to allow you to experience moments in your friends' lives through pictures as they happen. We imagine a world more connected through photos."

# License

MIT

# Terms and conditions

- You will NOT use this API for marketing purposes (spam, botting, harassment, massive bulk messaging...).
- We do NOT give support to anyone who wants to use this API to send spam or commit other crimes.
- We reserve the right to block any user of this repository that does not meet these conditions.

## Legal

This code is in no way affiliated with, authorized, maintained, sponsored or endorsed by Instagram or any of its affiliates or subsidiaries. This is an independent and unofficial API. Use at your own risk.
