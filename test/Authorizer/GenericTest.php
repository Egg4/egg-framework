<?php

namespace Egg\Authorizer;

use \Egg\Container;
use \Egg\Authorizer\Generic as GenericAuthorizer;
use \Egg\Orm\Repository\Closure as ClosureRepository;

class GenericTest extends \Egg\Test
{
    public function testCreateShouldBeSuccessfull()
    {
        $data = [
            'name'      => 'kitchen',
            'house_id'  => 2,
        ];
        $settings = [
            'house' => [
                'actions' => ['*' => 'deny'],
                'schema.self.resource' => 'house',
                'schema.self.attribute' => 'user_id',
                'schema.reference.resource' => 'user',
                'schema.reference.attribute' => 'id',
            ],
            'room' => [
                'actions' => ['*' => 'user.role=*'],
                'schema.self.resource' => 'room',
                'schema.self.attribute' => 'house_id',
                'schema.reference.resource' => 'house',
                'schema.reference.attribute' => 'id',
            ],
        ];

        $request = \Egg\FactoryTest::createRequest();
        $request = $request->withAttribute('resource', 'rooms');
        $request = $request->withAttribute('authentication', [
            'resource'  => 'user',
            'id'        => 27,
            'role'      => 'admin',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $container = new Container([
            'request'   => $request,
            'response'  => $response,
            'repository' => new Container([
                'house'     => new ClosureRepository(function($action, $arguments) {
                    $this->assertEquals('selectAll', $action);
                    $this->assertEquals(['user_id' => 27], $arguments[0]);
                    return [['id' => 2], ['id' => 3]];
                }),
                'room'     => new ClosureRepository(function($action, $arguments) {
                    $this->assertEquals('selectOne', $action);
                    $this->assertEquals(4, $arguments[0]['id']);
                    $this->assertEquals([2, 3], $arguments[0]['house_id']);
                    return ['id' => 4];
                }),
            ]),
        ]);
        $container['authorizer'] = new Container(function($resource) use ($container, $settings) {
            $authorizer = new GenericAuthorizer($settings[$resource]);
            $authorizer->setContainer($container);
            $authorizer->init();
            return $authorizer;
        });

        $container['authorizer']['room']->authorize('update', [4, $data]);
    }

    public function testSelectShouldBeSuccessfull()
    {
        $settings = [
            'house' => [
                'actions' => ['*' => 'deny'],
                'schema.self.resource' => 'house',
                'schema.self.attribute' => 'user_id',
                'schema.reference.resource' => 'user',
                'schema.reference.attribute' => 'id',
            ],
            'room' => [
                'actions' => ['*' => 'user.role=*'],
                'schema.self.resource' => 'room',
                'schema.self.attribute' => 'house_id',
                'schema.reference.resource' => 'house',
                'schema.reference.attribute' => 'id',
            ],
        ];

        $request = \Egg\FactoryTest::createRequest();
        $request = $request->withAttribute('resource', 'rooms');
        $request = $request->withAttribute('authentication', [
            'resource'  => 'user',
            'id'        => 27,
            'role'      => 'admin',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $container = new Container([
            'request'   => $request,
            'response'  => $response,
            'repository' => new Container([
                'house'     => new ClosureRepository(function($action, $arguments) {
                    $this->assertEquals('selectAll', $action);
                    $this->assertEquals(['user_id' => 27], $arguments[0]);
                    return [['id' => 2], ['id' => 3]];
                }),
            ]),
        ]);
        $container['authorizer'] = new Container(function($resource) use ($container, $settings) {
            $authorizer = new GenericAuthorizer($settings[$resource]);
            $authorizer->setContainer($container);
            $authorizer->init();
            return $authorizer;
        });

        $filterParams = $container['authorizer']['room']->authorize('select', [[], [], []]);
        $this->assertEquals(['house_id' => [2, 3]], $filterParams);
    }
}