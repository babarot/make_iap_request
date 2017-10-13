Please rewrite the necessary information.

```php
$res = make_iap_request(
    'https://myserver.example.com', # your backend application URL
    '657424576728-3t5uiqg5ktqj5hqk3j45btq5uq98faos.apps.googleusercontent.com', # Client ID
    './gcp-project-14a614b2955c.json' # Service account file
);
```

then, run the following commands:

```console
$ php ./composer.phar install
$ php ./make_iap_request.php
```
