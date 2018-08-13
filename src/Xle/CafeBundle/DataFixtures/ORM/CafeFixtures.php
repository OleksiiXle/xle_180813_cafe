<?php
namespace Xle\CafeBundle\DataFixtures\ORM;

use Xle\CafeBundle\Entity\Cafe;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class CafeFixtures extends AbstractFixture implements ContainerAwareInterface {
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {

        $cafeNew = new Cafe();
        $cafeNew->setGooglePlaceId('2167f0a947ba9a6c6e51213098b90175510d2a5b');
        $cafeNew->setTitle('Наама Бэй');
        $cafeNew->setAddress(' вулиця Ромен Роллана, 12, Харків');
        $cafeNew->setLat(50.011548);
        $cafeNew->setLng(36.2206864);
        $cafeNew->setRaiting(3);
        $cafeNew->setReview('Не плохая кухня и булочки');
        $manager->persist($cafeNew);
        $this->addReference('cafe1', $cafeNew);

        $cafeNew = new Cafe();
        $cafeNew->setGooglePlaceId('520314261fb28b6381b25ca65d347664f4d7901f');
        $cafeNew->setTitle('Де Густо');
        $cafeNew->setAddress('вулиця Космічна, 16, Харків');
        $cafeNew->setLat(50.0154221);
        $cafeNew->setLng(36.2216894);
        $cafeNew->setRaiting(4);
        $cafeNew->setReview('Прекрасное обслуживание');
        $manager->persist($cafeNew);
        $this->addReference('cafe2', $cafeNew);


        $cafeNew = new Cafe();
        $cafeNew->setGooglePlaceId('9683661d455a392b566c8282782b8c955f6b914c');
        $cafeNew->setTitle('Кулиничи');
        $cafeNew->setAddress('вулиця Близнюківська, Харків');
        $cafeNew->setLat(50.0155658);
        $cafeNew->setLng(36.2130984);
        $cafeNew->setRaiting(1);
        $cafeNew->setReview('Кулиничи - они и в Африке Кулиничи');
        $manager->persist($cafeNew);
        $this->addReference('cafe3', $cafeNew);

        $cafeNew = new Cafe();
        $cafeNew->setGooglePlaceId('a421c049d9328cd6818f9b3e3f36fa9fef37df67');
        $cafeNew->setTitle('Diner Club');
        $cafeNew->setAddress('Unnamed Road, Харків');
        $cafeNew->setLat(50.0159278);
        $cafeNew->setLng(36.2201183);
        $manager->persist($cafeNew);
        $this->addReference('cafe4', $cafeNew);

        $cafeNew = new Cafe();
        $cafeNew->setGooglePlaceId('3a8174ddbddde18a79ee9b28df81bbf6fe2d7364');
        $cafeNew->setTitle(' Beach House Cafe');
        $cafeNew->setAddress('9A, проспект Науки, 9А, Харків');
        $cafeNew->setLat(50.0135794);
        $cafeNew->setLng(36.2246302);
        $manager->persist($cafeNew);
        $this->addReference('cafe5', $cafeNew);


        $manager->flush();
    }
}
