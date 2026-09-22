# ZohoBooks API - PHP SDK library
[**ZohoBooks**][1] PHP library

This package is an upgrade of [**official package**][2]

#### Most important feature is switch from old auth  to Oauth
### Install

    composer require ahmedd-ibrahim/zohobooks

#### Added feature to automatically refresh access token
#### Added feature to support multiple Zoho regions
### Usage
<pre>
use Ahmedd\ZohoBooks\Api;
use Ahmedd\ZohoBooks\TokenManager;

$tokenManager = new TokenManager(
    env('ZOHO_CLIENT_ID'),
    env('ZOHO_CLIENT_SECRET'),
    env('ZOHO_REFRESH_TOKEN'),
    null, // default path: storage/app/zoho_token.json
    'eu'  // set your region: us, eu, in, au, jp, ca, cn, sa - default is 'us'
);

$api = new Api($tokenManager);
$api->setOrganizationId(env('ZOHO_ORG_ID'));

$api->contacts()->getList(['contact_name_contains'=>'John']);
</pre>


## TODO

### 0.x versions - full API with array processing
- [ ] Implement all API v3 methods: https://www.zoho.com/books/api/v3/
- [ ] Cover code with unit tests
- [x] Setup CI (Travis)

### 1.x version - ORM-like layer implementation
- [ ] Implement ORM layer for API
- [ ] Integrations with popular frameworks

[1]: https://www.zoho.com/books/
[2]: https://github.com/opsway/zohobooks-api
