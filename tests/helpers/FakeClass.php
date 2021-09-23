<?php

namespace SPatompong\Retrier\Tests\helpers;

class FakeClass
{
    public static function fakeStaticMethod(): string
    {
        return 'static';
    }

    public function fakePublicMethod(): string
    {
        return 'public';
    }
}
