# DRange

Discontinuous ranges.

```
$drange = new DRange(1, 5); // [ 1-5 ]
$drange->add(6); // [ 1-6 ]
$drange->add(8); // [ 1-6, 8 ]
$drange->add(7); // [ 1-8 ]
$drange->subtract(1, 3); // [ 4-8 ]
```

## Documentation

Soon...

## Installation

Install the latest version with:

```
$ composer require jrdev/drange
```

## License

Licensed under the [MIT licence](https://github.com/jrobinsonc/drange/blob/master/LICENSE).
