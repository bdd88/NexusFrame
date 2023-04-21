<?php

use NexusFrame\Validate\AbstractConfig;

use function PHPSTORM_META\map;

class ConcreteConfig extends AbstractConfig
{
    public function validate(): bool
    {
        try {
            $this->assertExists(array(
                'stuff', 'otherStuff', 'derp' => ['lerp']
            ));

            $this->assertTypes(array(
                'stuff' => 'string',
                'otherStuff' => 'integer',
                'something' => 'string',
                'derp' => [
                    'lerp' => 'boolean'
                ]
            ));

            $this->assertValues(array(
                'stuff' => 'TRUE',
                'otherStuff' => 5,
                'something' => 'anything',
                'derp' => [
                    'lerp' => FALSE
                ]
            ));

        } catch (Exception $e) {
            echo $e->getMessage();
            return FALSE;
        }
        return TRUE;
    }

    public function badAssertType(): void
    {
        $this->assertType('string', 'otherStuff');
    }

    public function badAssertValue(): void
    {
        $this->assertValue(TRUE, 'lerp', 'derp');
    }
}