<?php

namespace Egg\Serializer;

use Egg\Orm\Entity\Generic as GenericEntity;
use Egg\Orm\EntitySet\Generic as GenericEntitySet;
use Egg\Serializer\Generic as GenericSerializer;

class GenericTest extends \Egg\Test
{
    public function testSerializeEntity()
    {
        $data = [
            'id'    => 27,
            'name'  => 'test',
        ];

        $entity = new GenericEntity;
        $entity->hydrate($data);

        $serializer = new GenericSerializer();
        $result = $serializer->serialize($entity);

        $this->assertEquals($data, $result);
    }

    public function testSerializeEntitySet()
    {
        $data = [
            0 => [
                'id'    => 27,
                'name'  => 'test',
            ],
            1 => [
                'id'    => 28,
                'name'  => 'test2',
            ],
        ];

        $entity0 = new GenericEntity;
        $entity0->hydrate($data[0]);
        $entity1 = new GenericEntity;
        $entity1->hydrate($data[1]);
        $entitySet = new GenericEntitySet;
        $entitySet[] = $entity0;
        $entitySet[] = $entity1;

        $serializer = new GenericSerializer;
        $result = $serializer->serialize($entitySet);

        $this->assertEquals($data, $result);
    }
}