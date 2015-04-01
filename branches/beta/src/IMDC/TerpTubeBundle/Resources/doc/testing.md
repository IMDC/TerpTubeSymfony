# Testing

Things to do first:

* Install [PHPUnit][1]
* Read about [testing in Symfony][2]
* Create a user with credentials:
    * username: test
    * password: test

## Before running tests

**Note:** Tests should be run before any JavaScript tests.

### Functional

Check the static variables defined at the top of the class. For example:

```php
// ForumControllerTest.php
...
class ForumControllerTest extends WebTestCase
{
    private static $groupId = 4;
    private static $mediaIds = array(4, 1); // shuffle for order check
...
```

`$groupId` and `$mediaIds` are used for certain tests. The records must exist in the database. Adjust as needed.

### Unit

nothing

## Current state of tests

* So far they are basic and just test generic use cases
* 99% of them only check for correct responses, i.e. test cases for correct responses to bad input are needed
* Of course more still need to be written
* At some point in the future a schema will be made dedicated to just tests

## Notes

* Running tests sequentially that use RabbitMqBundle's producer service, like in MyFilesControllerTest.php,
cause a segfault (for me at least) when the second test tries to start

[1]: https://phpunit.de/manual/current/en/installation.html
[2]: http://symfony.com/doc/current/book/testing.html
