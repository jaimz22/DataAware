<?php


namespace VertigoLabs\Tests\DataAware;


use VertigoLabs\DataAware\DataAware;
use VertigoLabs\DataAware\DataAwareInterface;

abstract class TestDataAware implements DataAwareInterface
{
    use DataAware;

}
