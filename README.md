# MongoDB Active Record [![Build Status](https://travis-ci.org/tatarko/MongoAR.png?branch=master)](https://travis-ci.org/tatarko/MongoAR)

MongoAR is simple library that allows you to use active record pattern on the [MongoDB](http://www.mongodb.org) databases and their tables. It also provides simple yet powerful query builder for simple building of search criteria for `MongoCollection::find()` and `MongoCollection::findOne()` methods.

## Requirements

MongoAR requires to run correctly:

- PHP, version 5.4 or above
- mongo PECL library, version 0.9 or above

## Instalation

### Composer

Simply add a dependency on `tatarko/mongoar` to your project's `composer.json` file if you use [Composer](http://getcomposer.org) to manage the dependencies of your project. Here is a minimal example of a `composer.json` file that just defines a dependency on MongoAR:


	{
		"require": {
			"tatarko/mongoar": "0.*"
		}
	}

### Straight implementation

In case you don't use `Composer` as your dependency manager you are still able to use `MongoAR`. There are only two easy steps  to get `MongoAR` work.

1.  Download [MongoAR.zip](https://github.com/tatarko/MongoAR/archive/master.zip) and put extracted archive into your project's folder.
2. Add following code to your project's root php file (e.g. `index.php`) and remember to change `path/to/` according to relative location of downloaded `MongoAR` folder:

	require_once 'path/to/source/__autoloader.php';

## Documentation

Please, see [Wiki](https://github.com/tatarko/MongoAR/wiki) for online documentation.