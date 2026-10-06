# Modify derived component generation if need

@see a short description why x-original-* headers may the right usage for
you https://stackoverflow.com/questions/57759419/x-original-for-header-whats-its-purpose#57764700

Maybe you have special derived components to sign, or you want to change the generating Modus.

## Example @authority

You have set a header value "X-Original-Host" and you want to sign it as @authority so you have to add or change the
generation method from

In original, it's implemented with the following method

```php
<?php 
$methods  = [
    // ...
    '@authority' => fn(RequestInterface $message) => $this->message->getUri()->getHost(),
    // ...
];

?>
```

Perhaps you have set a header like `X-Original-Host` which holds your real authority.
Then you change the "generation" for the `@authority`
to:
`fn(RequestInterface $message) => $message->getHeaderLine('X-Original-Host')`

### per SigningConfiguration ()

you can configure the derived component value generation per SigningConfiguration object

```php
<?php 
$signingConfiguration = new SigningConfiguration()
    ->signHeader('@method')
    ->signHeader('x-Origin-host')
    //...
    ->withPrepareComponentsMethod('@authority',fn(RequestInterface $message) => $message->getHeaderLine('X-Original-Host'))
    ->withPrepareComponentsMethod('something-special',fn(RequestInterface $message) => 'do something special')
?>
```

### or Set static/globally

When you use only a single Message Signing between known frameworks, set it globally.

```php
<?php 
$arr  = [
    '@authority' => fn(RequestInterface $message) => $message->getHeaderLine('X-Original-Host'),
    //.. others
];
PrepareComponents::setDefaultDerivedStack($arr);
?>
```

note: with `PrepareComponents::setDefaultDerivedStack($arr)` You have to set the full array of methods. The other
methods you find it in class `PrepareComponents` 
