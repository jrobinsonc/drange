# DRange

[![Build Status](https://travis-ci.org/jrobinsonc/drange.svg?branch=master)](https://travis-ci.org/jrobinsonc/drange)
[![Dependency Status](https://www.versioneye.com/user/projects/595b9c66368b0800412a1095/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/595b9c66368b0800412a1095)

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
