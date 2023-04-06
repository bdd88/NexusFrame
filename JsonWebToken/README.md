## JsonWebToken
A library for generating and validating tokens based on the JSON Web Token (JWT) standard.

### Composer Installation
Run the following command in the root of your project directory:
```
composer require bdd88/jsonwebtoken
```

### Supported algorithms
+ HS256
+ HS384
+ HS512
+ RS256
+ RS384
+ RS512

### Examples

#### Setup
Generate RSA keys seperately and supply them to the JwtFactory in PEM format.
The composer autoloader is used in this example.
```
require './vendor/autoload.php';
$factory = new \Bdd88\JsonWebToken\JwtFactory($publicKey, $privateKey);
```

#### Generating
See above for valid algorithms. Payload data below is just an example.
The JWT object will return the token string when cast as a string.
```
$header = array('alg' => 'RS256', 'typ' => 'JWT');
$payload = array('username' => 'guy', 'enabled' => TRUE);
$jwt = $factory->generate($header, $payload);
$jwtString = (string) $jwt;
```

#### Importing
Import a token string back into a JWT object for validation.
```
$jwt = $factory->import($jwtString);
```

#### Validating
The validate method will return a bool value.
```
$tokenValid = $jwt->validate();
```