ReactPHP Caching Proxy Server

This project is a lightweight HTTP caching proxy server built with ReactPHP and Guzzle, designed to intercept and forward HTTP requests to an origin server while caching the responses for improved performance and reduced network load.

Key Features:
Request forwarding using Guzzle to the origin server

Automatic caching of responses based on unique request fingerprints (method + path + query)

X-Cache headers (HIT / MISS) for cache visibility

CLI flag --clear-cache to easily wipe all cached files

Built on ReactPHP for asynchronous, non-blocking I/O performance

https://github.com/Omar132555/Cache-Proxy
