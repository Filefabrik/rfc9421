# HTTP Message Signer and Verify (RFC 9421)

## Standards

### PSR-3: Logger Interface

https://www.php-fig.org/psr/psr-3/

### PSR-7: HTTP message interfaces

https://www.php-fig.org/psr/psr-7/

#### class SignatureParams with Signer\SigCompilerHelper

Set the wanted header-component and derived-component keys into this class.
during header compilation it will create a string like this

```text
sig1=("@method" "@authority" "@path" "content-type" "content-length");
```


#### Under docs the CSV's for 
* component-parameters
* signature-algorithms
* signature-metadata-parameters

can be found
https://www.iana.org/assignments/http-message-signature
