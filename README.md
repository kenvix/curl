# Kenvix/curl
A very lite and high compatibility but powerful curl class(especially you're processing http headers and cookies). No dependence.
# Usage
## Basical HTTP request
### Get
```php
$curl = new \Kenvix\curl\curl("https://bing.com/");
$curl->get(); //string response
```
Or:
```php
\Kenvix\curl\curl::xget("https://bing.com/");
```
You can also:
```php
echo new \Kenvix\curl\curl("https://bing.com/");
```
### POST
```php
$curl = new \Kenvix\curl\curl("https://bing.com/");
$curl->post([  //something to post
    'a' => 'foo',
    'b' => 'excited'
]); //string response
```
### HEAD
```php
$curl = new \Kenvix\curl\curl("https://www.bing.com/");
$curl->head(); //array response
```
Response:
```
array (size=9)
  'Cache-Control' => string 'private, max-age=0' (length=18)
  'Transfer-Encoding' => string 'chunked' (length=7)
  'Content-Type' => string 'text/html; charset=utf-8' (length=24)
  'Vary' => string 'Accept-Encoding' (length=15)
...
```
### Get Cookies
```php
$curl = new \Kenvix\curl\curl("https://www.bing.com/");
$curl->head();
$curl->getCookies(); //array
```
Response:
```
Array
(
    [SRCHD] => Array
        (
            [key] => SRCHD
            [value] => AF=NOFORM
            [domain] => .bing.com
            [expires] => Sat, 22-Aug-2020 10:56:24 GMT
            [path] => /
        )

    [_EDGE_V] => Array
        (
            [key] => _EDGE_V
            [value] => 1
            [path] => /
            [httponly] => 1
            [expires] => Mon, 16-Sep-2019 10:56:25 GMT
            [domain] => bing.com
        )
...
```
### Customize request headers
```php
$curl = new \Kenvix\curl\curl("https://www.bing.com/",array('User-Agent: chrome', 'Referer: https://kenvix.com'));
```
or
```php
$curl->setHeaders(array('User-Agent: chrome', 'Referer: https://kenvix.com'));
```
### Other
```php
$curl->put($data);
$curl->delete($data);
```
### Do not follow redirect 
**NOTICE:** CURLOPT_FOLLOWLOCATION HAS ALREADY BEEN SET TO false!!!     
get() post() head() delete() put() provide a parameter to allow you to do this.
```php
$curl->get(false);
$curl->post([...], false);
```
## Useful Methods
### addCookie($ck)
Add a cookie:    
```php
$curl->addCookie('yes=sir');
```
Add cookies:
```php
$curl->addCookie([
    'a' => 'foo',
    'b' => 'excited'
]);
```
### getHTTPCode()
returns http code
### getRedirectNum()
returns 
## Common Methods
### execRaw()
returns raw result
### readCookies(array $setcookie)
`STATIC`   
parse cookies into array
### parseCookieKeyValue($str)
`STATIC`    
### getConnection()
Returns curl connection
### setRequestMethod($method)
A custom request method to use instead of "GET" or "HEAD" when doing a HTTP request. 