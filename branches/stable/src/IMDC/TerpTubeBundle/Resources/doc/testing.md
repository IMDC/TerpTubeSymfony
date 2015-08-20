# Testing

Things to do first:

* Install [PHPUnit][1]
* Read about [testing in Symfony][2]
* Read about using the [LiipFunctionalTestBundle][3]

## Before running tests

**Note 1:** Symfony tests should be run before any JavaScript tests.

**Note 2:** The LiipFunctionalTestBundle is mainly used for its convinent ability to load doctrine fixtures (`loadFixtures()`).

### Configure

Edit `app/config/parameters.yml` to define the database to use for the test. You must create the database.
```
database_test_name: terptube_symfony_test
```

<br />
In `app/config/config_test.yml`, the `database_test_name` parameter is used to override the `dbname` that doctrine will use.
```
doctrine:
  dbal:
    dbname: %database_test_name%
```

<br />
Define the media upload path when running tests. This path must exist.
```
imdc_terp_tube:
  resource_file:
    upload_path: "uploads/test_media"
```

<br />
Also set the paths where predefined test media files are located and where the logs will be saved.
```
imdc_terp_tube:
  tests:
    files_path: "%kernel.root_dir%/../../test_files"
    logs_path: "%kernel.root_dir%/../../test_logs"
```

### Test Media

Download the test media from the server to your `files_path` as defined in `app/config/config_test.yml`.
```
//TODO
```

### Functional Tests

nothing

### Unit Tests

nothing

## Current state of tests

* So far they are basic and just test generic use cases
* 99% of them only check for correct responses, i.e. test cases for correct responses to bad input are needed
* Of course more still need to be written

## Notes

* <s>Running tests sequentially that use RabbitMqBundle's producer service, like in MyFilesControllerTest.php,
cause a segfault (for me at least) when the second test tries to start</s>

[1]: https://phpunit.de/manual/current/en/installation.html
[2]: http://symfony.com/doc/2.3/book/testing.html
[3]: https://github.com/liip/LiipFunctionalTestBundle#introduction
