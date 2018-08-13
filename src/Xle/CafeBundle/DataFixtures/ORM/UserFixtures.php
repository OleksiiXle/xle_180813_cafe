<?php
namespace Xle\CafeBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UserFixtures extends AbstractFixture implements ContainerAwareInterface {
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $passwordEncoder = $this->container->get('security.password_encoder');

        $cafeAdmin = new User();
        $cafeAdmin->setFullName('Administrator');
        $cafeAdmin->setUsername('admin');
        $cafeAdmin->setEmail('gastronome@cafe.com');
        $cafeAdmin->setRoles(['ROLE_ADMIN']);
        $encodedPassword = $passwordEncoder->encodePassword($cafeAdmin, 'admin');
        $cafeAdmin->setPassword($encodedPassword);
        $manager->persist($cafeAdmin);
        $this->addReference('admin', $cafeAdmin);

        $cafeUser = new User();
        $cafeUser->setFullName('Simple User');
        $cafeUser->setUsername('user');
        $cafeUser->setEmail('hungry@cafe.com');
        $cafeUser->setRoles(['ROLE_USER']);
        $encodedPassword = $passwordEncoder->encodePassword($cafeUser, 'user');
        $cafeUser->setPassword($encodedPassword);
        $manager->persist($cafeUser);
        $this->addReference('user', $cafeUser);

        $manager->flush();
    }
}
