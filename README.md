# N8nManagerBundle
n8n Manager Bundle for Pimcore

## Configure

add environment variables:
```env
# .env file
N8N_API_KEY=your-api-key
N8N_HOST=host-incl-port-if-necessary
N8N_WEBHOOK_KEY=custom-webhook-key
```
## Webhook Key
The webhook key is used to authenticate the webhook calls from n8n.
This Bundle uses the Key for "Header Auth" in n8n and send it as "X-Api-Key" header to n8n.


add bundle in bundles.php
```php
return [
    ...
    \Pimcorecasts\Bundle\N8nManager\N8nManagerBundle::class => ['all' => true],
    ...
];
```



## Development
### Frontend
Start Vite continues build while developing
```bash
npm run build --watch
```

Do not forget ro commit the dist folder after building the frontend.