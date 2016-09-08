Extending the Model
===================

This bundle provides a complete solution to quickly setup an OAuth2 security layer for your APIs.
But, as all applications are often different, the bundle has a basic database model.
Thanks to an abstraction layer (see the `Model/` directory), it's really easy to deal with various
database abstraction layers, but in the same time to extend each layer. In this chapter, you will see
how to properly extend the model of the FOSOAuthServerBundle to add your business logic.


## Propel ##

If you are using Propel, you just have to copy the [`schema.xml`](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/blob/master/Resources/config/propel/schema.xml)
to the `app/Resources/FOSOAuthServerBundle/config/propel/` directory of your application.

Then, tweak it to fit your needs. When you are done, just rebuild your model classes as usual:

    $ php app/console propel:model:build

To update the dabase, you can rely on the `propel:sql:build` command if you want to rebuild your
whole database (which means loosing data), or rely on the migration commmands:

    $ php app/console propel:migration:generate-diff
    $ php app/console propel:migration:migrate

> **Note:** when you generate a diff with Propel, don't forget to review the generated SQL statements before to execute them (migrate).

[Back to index](index.md)
