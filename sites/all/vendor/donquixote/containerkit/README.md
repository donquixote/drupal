[![Build Status](https://secure.travis-ci.org/donquixote/containerkit.png)](https://travis-ci.org/donquixote/containerkit)

# containerkit

A very minimal base class for a PHP dependency injection container with magic `__get()`, as described in http://dqxtech.net/blog/2014-06-13/simple-do-it-yourself-php-service-container

Nothing in here is auto-generated. The methods that instantiate services need to be written manually in a class extending the parent container.

Providing an alternative implementation for a service only works by extending the container.

It is recommended to add `@property` tags in the class docblock of your container class.


## ContainerBase

This is the most basic container base class. Usually you only need this one.


## SettableContainer

This one has a `__set()` method in addition to the `__get()` method. Setting only works for services / values that are not already initialized.

The `__set()` allows to override specific services with actual objects, thus providing a cheap alternative to extending the container.


## Stubbable container

This container is designed for a special way to deal with circular dependencies, using stubs.

Usually this is not needed. You should either avoid circular dependencies (recommended, if you can), or use a proxy somewhere in the circle.
