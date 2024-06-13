## HiOrg API OAuth Starter
This package demonstrates how to access the HiOrg-Server API from a php web application with the
[https://oauth2-client.thephpleague.com/](League/oauth2-client) library.

### Getting started

To try it out, you can download the package:

```bash
git clone URL
cd api-oauth-starter
```

Then copy the config.sample.php:

```bash
cp config.sample.php config.php
```

and enter the configuration for your OAuth2 client in the config.php (please contact support@hiorg-server.de to get an OAuth client).

After that, you can use the built-in PHP web server:


```bash
php -S localhost:8000
```

and open the application in the browser:


```bash
open http://localhost:8000
```
