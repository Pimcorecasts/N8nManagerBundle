# N8nManagerBundle
n8n Manager Bundle for Pimcore

## Configure

add environment variables:
```env
# .env file
N8N_API_KEY=your-api-key
N8N_HOST=host-incl-port-if-necessary
```

add bundle in bundles.php
```php
return [
    ...
    \Pimcorecasts\Bundle\N8nManager\N8nManagerBundle::class => ['all' => true],
    ...
];
```
