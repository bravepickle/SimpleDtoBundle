<?php

namespace Mell\Bundle\SimpleDtoBundle\Tests\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DtoManagerTest extends TestCase
{
    /**
     * @param User $entity
     * @param string $dtoType
     * @param string $group
     * @param array $fields
     * @param DtoInterface $expected
     * @dataProvider createDtoProvider
     * @group dtoCreate
     */
    public function testCreateDto(User $entity, $dtoType, $group, array $fields, DtoInterface $expected)
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        $manager = new DtoManager(
            $serializer,
            new EventDispatcher()
        );
        $dto = $manager->createDto($entity, $group, $fields, false);
        $this->assertEquals($expected->getRawData(), $dto->getRawData());
    }

    /**
     * @return array
     */
    public function createDtoProvider()
    {
        return [
            // test common dto fields
            [
                $this->generateUser(['email' => 'mail@email.com']),
                'UserDto',
                DtoInterface::DTO_GROUP_READ,
                [],
                new Dto(
                    $this->generateUser(['email' => 'mail@email.com']), DtoInterface::DTO_GROUP_READ, [
                        'id' => 0,
                        'addressId' => 0,
                        'firstname' => '',
                        'lastname' => '',
                        'active' => true,
                        'roles' => [],
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                'read',
                [],
                new Dto(
                    $this->generateUser([
                        'id' => 1,
                        'addressId' => 1,
                        'email' => 'mail@email.com',
                        'firstname' => 'Ivan',
                        'lastname' => 'Ivanov',
                        'password' => 'password'
                    ]), DtoInterface::DTO_GROUP_READ, [
                        'id' => 1,
                        'addressId' => 1,
                        'firstname' => 'Ivan',
                        'lastname' => 'Ivanov',
                        'active' => true,
                        'roles' => [],
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            // test groups
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                DtoInterface::DTO_GROUP_CREATE,
                [],
                new Dto(
                    $this->generateUser([
                        'id' => 1,
                        'addressId' => 1,
                        'email' => 'mail@email.com',
                        'firstname' => 'Ivan',
                        'lastname' => 'Ivanov',
                        'password' => 'password'
                    ]), DtoInterface::DTO_GROUP_CREATE, [
                        'id' => 1,
                        'addressId' => 1,
                        'firstname' => 'Ivan',
                        'lastname' => 'Ivanov',
                        'password' => 'password',
                        'active' => true,
                        'roles' => [],
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                DtoInterface::DTO_GROUP_UPDATE,
                [],
                new Dto(
                    $this->generateUser([
                        'id' => 1,
                        'addressId' => 1,
                        'email' => 'mail@email.com',
                        'firstname' => 'Ivan',
                        'lastname' => 'Ivanov',
                        'password' => 'password'
                    ]), DtoInterface::DTO_GROUP_UPDATE, [
                        'id' => 1,
                        'addressId' => 1,
                        'firstname' => 'Ivan',
                        'lastname' => 'Ivanov',
                        'password' => 'password',
                        'active' => true,
                        'roles' => [],
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            // test fields
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                DtoInterface::DTO_GROUP_READ,
                ['id', 'email'],
                new Dto(
                    $this->generateUser([
                        'id' => 1,
                        'addressId' => 1,
                        'email' => 'mail@email.com',
                        'firstname' => 'Ivan',
                        'lastname' => 'Ivanov',
                        'password' => 'password'
                    ]), DtoInterface::DTO_GROUP_READ, [
                        'id' => 1,
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                DtoInterface::DTO_GROUP_READ,
                ['id', 'email', 'password'],
                new Dto(
                    $this->generateUser([
                        'id' => 1,
                        'addressId' => 1,
                        'email' => 'mail@email.com',
                        'firstname' => 'Ivan',
                        'lastname' => 'Ivanov',
                        'password' => 'password'
                    ]), DtoInterface::DTO_GROUP_READ, [
                        'id' => 1,
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                DtoInterface::DTO_GROUP_CREATE,
                ['id', 'email', 'password'],
                new Dto(
                    $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                    DtoInterface::DTO_GROUP_CREATE,
                    [
                        'id' => 1,
                        'email' => 'mail@email.com',
                        'password' => 'password',
                    ]
                )
            ],
        ];
    }

    /**
     * @param array $userParams
     * @param array $addressParams
     * @return User
     */
    private function generateUser(array $userParams, array $addressParams = [])
    {
        /** @var User $user */
        $user = $this->bindObjectParams(new User(), $userParams);
        if ($addressParams) {
            /** @var Address $address */
            $address = $this->bindObjectParams(new Address(), $addressParams);
            $user->setAddress($address);
        }

        return $user;
    }

    /**
     * @param object $object
     * @param array $params
     * @return object
     */
    private function bindObjectParams($object, array $params)
    {
        foreach ($params as $param => $value) {
            call_user_func([$object, 'set' . $param], $value);
        }

        return $object;
    }
}
