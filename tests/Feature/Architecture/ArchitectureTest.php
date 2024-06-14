<?php

test('Not debugging statements are left in our code.')
    ->expect(['dd', 'ray', 'dump', 'var_dump', 'rd', 'ddd'])
    ->not()
    ->toBeUsed();
