## Form Module

A module for the [Mercy Scaffold](https://github.com/aklebe-laravel/mercy-scaffold).

Frontend forms (Livewire) for custom objects or Eloquent models and their relations:

- easy configuration of form elements (text, select, multi select, switches, tabs, ...)
- validation
- loads and saves models and their relations
- events to change the elements before rendering (`InitFormElements`, `FinalFormElements`, `BeforeRenderForm`)
- works best together with the [DataTable module](https://github.com/aklebe-laravel/data-table-module)

## Installation

Add the module to `requires` in the project's `setup.yaml`, see
[Require modules and themes](https://github.com/aklebe-laravel/deploy-env-module#require-modules-and-themes).

## Console

Create the form classes of a module, inclusive the data table and (if missing) the Eloquent model:

```
php artisan form:make {form_name} {module_name?}
```

See also [Create forms and datatables](https://github.com/aklebe-laravel/deploy-env-module#create-forms-and-datatables).

## License

MIT, see [LICENSE.md](LICENSE.md).
