<?php

namespace Xle\CafeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Xle\CafeBundle\Entity\Cafe;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Cafe controller.
 *
 */
class CafeController extends Controller {
    public $raiting =  [
        0 => 'Не установлен',
        1 => 'Ни какое',
        2 => 'Ниже среднего',
        3 => 'Так-себе',
        4 => 'Очень даже ничего',
        5 => 'Фэн-шуй',
    ];

    /**
     * Главный вид
     */
    public function listAction() {
        return $this->render('cafe/listMain.html.twig');
    }

    /**
     * Аяксом возвращает вид грида кафе c карты
    Array
    (
    [0] => Array
    (
    [id] => 2167f0a947ba9a6c6e51213098b90175510d2a5b
    [name] => Наама Бэй
    [lat] => 50.011548
    [lng] => 36.2206864
    )

    [1] => Array
    (
    [id] => 45ee6aed5298730abed9e33ea3a7f3e846fbf321
    [name] => Витамин
    [lat] => 50.015692
    [lng] => 36.2225943
    )

     *
     */
    public function mapAction(Request $request, $cafe_json) {
        $mapCafes = json_decode($cafe_json, true);
        //-- массив гугл-идентификаторов кафешек с карты
        $mapCafesIds = [];
        foreach ($mapCafes as $cafe){
            $mapCafesIds[] = $cafe['id'];
        }
        //-- получаем кафе из бд, по совпадению гугл-идентификатора с массивом
        $em = $this->getDoctrine()->getManager();
        $cafesEntities = (array) $em->getRepository('XleCafeBundle:Cafe')->findByGmKeyInArray($mapCafesIds);
        //-- преобразуем его для дальнейшего удобного использования
        $cafes = [];
        foreach ($cafesEntities as $key => $cafe){
            $cafes[$cafe->getGooglePlaceId()] = [
                'id' => $cafe->getId(),
                'title' => $cafe->getTitle(),
                'raiting' =>  $cafe->getRaitingTxt(),
                'review' => $cafe->getReview(),
                'status' => $cafe->getStatusTxt(),
                'address' => $cafe->getAddress(),
            ];
        }
        //-- формируем массив кафешек с карты с учетом наличия информации в бд для передачи во вьюху
        $ret = [];
        foreach ($mapCafes as $mp){
            $ret[]=[
              'id' =>  $mp['id'],
              'name' =>  $mp['name'],
              'address' =>  $mp['address'],
              'raiting' =>  ((isset($cafes[$mp['id']])) ? $cafes[$mp['id']]['raiting'] : '') ,
              'db' =>  ((isset($cafes[$mp['id']])) ? 'Уже сохранено' : '') ,
              'lat' => $mp['lat'],
              'lng' => $mp['lng'],
           ];
        }
        return $this->render('cafe/listMap.html.twig', [
            'cafes' => $ret,
        ]);
    }

    /**
     * Аяксом возвращает вид грида кафе из БД
     */
    public function dbAction() {
        $em = $this->getDoctrine()->getManager();
        $cafes = $em->getRepository('XleCafeBundle:Cafe')->findAll();
        return $this->render('cafe/listDB.html.twig', array(
            'cafes' => $cafes,
        ));
    }


    /**
     * ajax send cafe JSON.
     */
    public function infoAction($cafe_id) {
        $result = [
            'status' => false,
            'data' => 'error'
        ];
        try {
            $em = $this->getDoctrine()->getManager();
            $cafe = $em->getRepository(Cafe::class)->find($cafe_id);
            if (isset($cafe)){
                $result['data'] = [
                    'id' => $cafe->getId(),
                    'google_place_id' => $cafe->getGooglePlaceId(),
                    'title' => $cafe->getTitle(),
                    'raiting' =>  (!empty($cafe->getRaiting()))
                        ? $this->raiting[$cafe->getRaiting()]
                        : 'Не установлен',
                    'review' => $cafe->getReview(),
                    'status' => (($cafe->getRaiting() > 0 || !empty($cafe->getReview())) ? 'Оценено' : 'Не оценено'),
                    'address' => $cafe->getAddress(),
                    'lat' => $cafe->getLat(),
                    'lang' => $cafe->getLng(),
                ];
                $result['status'] = true;
            }
        } catch (\Exception $e){
            $result['data'] = $e->getMessage();
        }
        return new Response( json_encode($result), 200 );

    }

    /**
     * ajax send cafe update form.
     */
    public function info1Action(Request $request, $cafe_id) {
        $user = $this->getUser();
        if ($user == null || !$user->isAdmin()){
            return new Response('Access deny', "403");
        }
        $em = $this->getDoctrine()->getManager();
        $cafe = $em->getRepository(Cafe::class)->find($cafe_id);
        $editForm = $this->createForm('Xle\CafeBundle\Form\CafeShortType', $cafe);
        $editForm->handleRequest($request);
        return $this->render('cafe/editAjax.html.twig', array(
            'cafe' => $cafe,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     *   ajax update one cafe, return JSON.
     *     Array
     *
     */
    public function modifyAction(Request $request)
    {
        $user = $this->getUser();
        if ($user == null || !$user->isAdmin()){
            return new Response('Access deny', "403");
        }
        //  $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        try {
            $cafe_id = $_REQUEST['xle_cafebundle_cafe']['id'];
            $em = $this->getDoctrine()->getManager();
            $cafe = $em->getRepository(Cafe::class)->find($cafe_id);
            $editForm = $this->createForm('Xle\CafeBundle\Form\CafeShortType', $cafe);
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $this->getDoctrine()->getManager()->flush();
                $cafe = $em->getRepository(Cafe::class)->find($cafe_id);
                $result['data'] = [
                    'id' => $cafe->getId(),
                    'google_place_id' => $cafe->getGooglePlaceId(),
                    'title' => $cafe->getTitle(),
                    'raiting' =>  (!empty($cafe->getRaiting()))
                                    ? $this->raiting[$cafe->getRaiting()]
                                    : 'Не установлен',
                    'review' => $cafe->getReview(),
                    'status' => (($cafe->getRaiting() > 0 || !empty($cafe->getReview())) ? 'Оценено' : 'Не оценено'),
                    'address' => $cafe->getAddress(),
                    'lat' => $cafe->getLat(),
                    'lang' => $cafe->getLng(),
                ];
                $result['status'] = true;
            } else {
                $validator = $this->get('validator');
                $errors = $validator->validate($cafe);
                $errStr = 'Ошибка валидации: ';
                foreach ($errors as $key => $error){
                    $errStr .= $error->getMessage() . ' *** ';
                }
                $result['data']= $errStr;
                $result['status'] = false;
            }
        } catch (\Exception $e){
            $result = [
                'status' => false,
                'data' => $e->getMessage()
            ];
        }
        return new Response( json_encode($result), 200 );
    }

    /**
     *   ajax append selected cafies from map, return JSON.
     *
     *     (
    [0] => Array
    (
    [id] => 13ff332f609c5eb2c39984ed297bad4f28186135
    [name] => alquds cafe
    [address] => Харків
    [lat] => 50.0129666
    [lng] => 36.2234752
    )

    [1] => Array
    (
    [id] => 70ad7884df06e63bbf948bed1117d125e7c58685
    [name] => Jam & Coffee
    [address] => 61000, вулиця Клочківська, 191а, Харків
    [lat] => 50.0146674
    [lng] => 36.2150339
    )
     */
    public function appendAction(Request $request) {
        $mapCafies = json_decode($request->getContent(), true);
        try{
            $result['data']=[];
            $em = $this->getDoctrine()->getManager();
            $validator = $this->get('validator');
            foreach ($mapCafies as $newCafe){
                $cafe = new Cafe();
                $cafe->setGooglePlaceId($newCafe['id']);
                $cafe->setTitle($newCafe['name']);
                $cafe->setAddress($newCafe['address']);
                $cafe->setLat($newCafe['lat']);
                $cafe->setLng($newCafe['lng']);
                $errors = $validator->validate($cafe);
                if (count($errors) == 0) {
                    $em->persist($cafe);
                    $em->flush();
                    $result['data'][$newCafe['name']]= 'ok';
                } else {
                    $errStr = 'Ошибка валидации: ';
                    foreach ($errors as $error){
                        $errStr .= $error->getMessage() . ' *** ';
                    }
                    $result['data'][$newCafe['name']]= $errStr;
                }
            }
            $result['status'] = true;
        } catch (\Exception $e){
            $result['data'] = [$e->getMessage()];
            $result['status'] = false;
        }
        return new Response( json_encode($result), 200 );
    }

    /**
     * Deletes a cafe entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $user = $this->getUser();
        if ($user == null || !$user->isAdmin()){
            return new Response('Access deny', "403");
        }
        try {
            $d = $request->getContent();
            $em = $this->getDoctrine()->getManager();
            $cafe = $em->getRepository(Cafe::class)->find($id);
            $em->remove($cafe);
            $em->flush();
            $result = [
                'status' => true,
                'data' => $id
            ];
        } catch (\Exception $e){
            $result = [
                'status' => false,
                'data' => $e->getMessage()
            ];
        }
        return new Response( json_encode($result), 200 );
    }
}
