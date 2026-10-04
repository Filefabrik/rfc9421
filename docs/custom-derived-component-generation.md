# Modify derived component generation if need

@notice under construction page

Maybe you have special derived components to sign, or you want to change the generating Modus.

`// Normalized to lowercase and the default port is omitted. This could
// also be in X-Original-Host rather than Host header for verification.`
You have set a header value "X-Original-Host" and you want to sign it as @authority so you have to add or change the
generation method from

```php
<?php 
$arr  = [
    '@authority' => fn(RequestInterface $message) => $this->message->getUri()->getHost(),
    //.. others
];

?>
```

to:

```php
<?php 
$arr  = [
    '@authority' => fn(RequestInterface $message) => $message->getHeaderLine('X-Original-Host'),
    //.. others
];
?>
```

#### globally

##### Set the all methods

```php
<?php 
$arr  = [
    '@authority' => fn(RequestInterface $message) => $message->getHeaderLine('X-Original-Host'),
    //.. others
];
PrepareComponents::setDefaultDerivedStack($arr);
?>
```

#### per instance

##### Set the all methods

```php
<?php 
$arr  = [
    '@authority' => fn(RequestInterface $message) => $message->getHeaderLine('X-Original-Host'),
    //.. others
];
$prepareInstance = new PrepareComponents(new Request('post', 'https://your.com'), new SignatureParams());
$prepareInstance->setDerivedMapper($arr);
?>
```
