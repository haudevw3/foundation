<?php

// class Example
// {
//     use Macroable;

//     public function bar()
//     {
//         return 'bar';
//     }
// }

// Example::macro('foo', function () {
//     return 'foo';
// });

// $example = new Example;
// $example->bar();
// $example->foo();
// Example::foo();

#==========#

// class Mixin
// {
//     public function bar()
//     {
//         return function () {
//             return 'bar';
//         };
//     }
// }

// class Example1
// {
//     use Macroable;
// }

// Example1::mixin(new Mixin);
// Example1::bar();
// $example1 = new Example1;
// $example1->bar();