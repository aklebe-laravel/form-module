## Form

A module for [Mercy Scaffold Application](https://github.com/aklebe-laravel/mercy-scaffold.git)
(or any based on it like [Jumble Sale](https://github.com/aklebe-laravel/jumble-sale.git)).

This module will provide frontend forms with the following features

1) easy configuration of forms
2) validations
3) load/save models and their relations
4) works great together with DataTable-Module

### Console

Create the form files and (if not exists) the eloquent model class.

```
php artisan form:make {form_name} {module_name?}
```

See readme ```DeployEnv``` to create datatable and form classes at once.
