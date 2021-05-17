# Cache Function

Implement a function or class (it can be on the same file - `request.php`) to cache requests made with the existing code, preventing unecessary calls. You may use [this Redis module](https://github.com/phpredis/phpredis) as a cache service. Feel free to change the code within the existing functions, but do not alter their behaviour.

**Context**: Caching requests can be useful to avoid unecessary HTTP calls for the same resources, however, the resources can change during time, so it is important to keep in mind that cache needs to be invalidated at some point.

**Note**: You may use any PHP version and import other libraries, unless they implement cache services for the requests.

